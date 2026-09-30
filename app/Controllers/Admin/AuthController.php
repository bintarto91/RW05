<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        if (session('admin_id')) {
            return redirect()->to($this->landingUrl((string) session('admin_role')));
        }

        return view('admin/login', [
            'error' => session()->getFlashdata('login_error') ?: '',
        ]);
    }

    public function attemptLogin()
    {
        $username = normalize_admin_username($this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $clientIp = (string) $this->request->getIPAddress();
        $throttleKey = 'admin-login-' . hash('sha256', $clientIp . '|' . $username);

        if (! service('throttler')->check($throttleKey, 5, 300)) {
            log_message('warning', 'Login admin dibatasi karena terlalu banyak percobaan. IP: {ip}, username: {username}', [
                'ip' => $clientIp,
                'username' => $username,
            ]);

            return redirect()->to(site_url('admin/login'))
                ->withInput()
                ->with('login_error', 'Terlalu banyak percobaan login. Tunggu 5 menit lalu coba kembali.');
        }

        try {
            $db = db_connect();
            $db->initialize();
            ensure_admin_users_table($db);

            $admin = $db->table('admin_users')
                ->where('username', $username)
                ->where('status', 'aktif')
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (\Throwable $exception) {
            log_message('error', 'Login admin gagal terhubung ke database: ' . $exception->getMessage());

            return redirect()->to(site_url('admin/login'))
                ->withInput()
                ->with('login_error', 'Database belum dapat dijangkau. Periksa koneksi lokal lalu coba lagi.');
        }

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session()->regenerate(true);
            session()->set([
                'admin_id' => (int) $admin['id'],
                'admin_nama' => $admin['nama'],
                'admin_role' => $admin['role'] ?? 'admin',
                'admin_session_version' => (int) ($admin['session_version'] ?? 1),
            ]);

            log_message('notice', 'Login admin berhasil. ID: {id}, IP: {ip}', [
                'id' => (int) $admin['id'],
                'ip' => $clientIp,
            ]);

            return redirect()->to($this->landingUrl((string) ($admin['role'] ?? 'admin')));
        }

        $pending = $db->table('admin_users')
            ->select('id, password_hash, status')
            ->where('username', $username)
            ->whereIn('status', ['menunggu', 'nonaktif'])
            ->limit(1)
            ->get()
            ->getRowArray();
        if ($pending && password_verify($password, (string) $pending['password_hash'])) {
            $message = ($pending['status'] ?? '') === 'menunggu'
                ? 'Pendaftaran akun masih menunggu persetujuan Super Admin.'
                : 'Akun ini sedang dinonaktifkan. Hubungi Super Admin.';

            return redirect()->to(site_url('admin/login'))->with('login_error', $message);
        }

        log_message('warning', 'Login admin gagal. IP: {ip}, username: {username}', [
            'ip' => $clientIp,
            'username' => $username,
        ]);

        return redirect()->to(site_url('admin/login'))
            ->withInput()
            ->with('login_error', 'Username atau password salah.');
    }

    public function register()
    {
        if (session('admin_id')) {
            return redirect()->to($this->landingUrl((string) session('admin_role')));
        }

        return view('admin/register', [
            'error' => session()->getFlashdata('register_error') ?: '',
            'success' => session()->getFlashdata('register_success') ?: '',
            'roleOptions' => admin_registration_role_options(),
        ]);
    }

    public function submitRegistration()
    {
        $clientIp = (string) $this->request->getIPAddress();
        $throttleKey = 'admin-register-' . hash('sha256', $clientIp);
        if (! service('throttler')->check($throttleKey, 3, 900)) {
            return redirect()->to(site_url('admin/daftar'))
                ->withInput()
                ->with('register_error', 'Terlalu banyak percobaan pendaftaran. Tunggu 15 menit lalu coba kembali.');
        }

        $nama = trim((string) $this->request->getPost('nama'));
        $username = normalize_admin_username($this->request->getPost('username'));
        $noHp = preg_replace('/[^0-9+ -]/', '', trim((string) $this->request->getPost('no_hp')));
        $role = trim((string) $this->request->getPost('role'));
        $catatan = trim((string) $this->request->getPost('catatan_pendaftaran'));
        $password = (string) $this->request->getPost('password');
        $passwordConfirmation = (string) $this->request->getPost('password_confirmation');
        $roleOptions = admin_registration_role_options();

        if ($nama === '' || $username === '' || $noHp === '') {
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Nama, username, dan nomor WhatsApp wajib diisi.');
        }
        if (! preg_match('/^[a-z0-9._-]{3,40}$/', $username)) {
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Username minimal 3 karakter dan hanya boleh memakai huruf, angka, titik, garis, atau underscore.');
        }
        if (! isset($roleOptions[$role])) {
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Pilih peran yang sesuai.');
        }
        if (strlen($password) < 10) {
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Password minimal 10 karakter.');
        }
        if ($password !== $passwordConfirmation) {
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Konfirmasi password belum sama.');
        }

        try {
            $db = db_connect();
            $db->initialize();
            ensure_admin_users_table($db);
            $duplicate = $db->table('admin_users')->where('username', $username)->get()->getRowArray();
            if ($duplicate) {
                return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Username sudah digunakan. Silakan pilih username lain.');
            }

            $db->table('admin_users')->insert([
                'nama' => substr($nama, 0, 120),
                'username' => substr($username, 0, 80),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'status' => 'menunggu',
                'no_hp' => substr($noHp, 0, 30),
                'catatan_pendaftaran' => substr($catatan, 0, 500),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Pendaftaran admin gagal: ' . $exception->getMessage());
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Pendaftaran belum dapat disimpan. Silakan coba kembali.');
        }

        return redirect()->to(site_url('admin/daftar'))
            ->with('register_success', 'Pendaftaran berhasil. Akun dapat dipakai setelah disetujui Super Admin.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('admin/login'));
    }

    private function landingUrl(string $role): string
    {
        return $role === 'kader_kesehatan'
            ? site_url('admin/kesehatan-dashboard')
            : site_url('admin');
    }
}
