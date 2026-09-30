<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AdminRoleAccess;
use App\Libraries\AdminRegistrationValidator;

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
        $ipThrottleKey = 'admin-login-ip-' . hash('sha256', $clientIp);

        if (strlen($password) > 1024
            || ! service('throttler')->check($ipThrottleKey, 20, 300)
            || ! service('throttler')->check($throttleKey, 5, 300)) {
            log_message('warning', 'Login admin dibatasi karena terlalu banyak percobaan. IP: {ip}, username hash: {username_hash}', [
                'ip' => $clientIp,
                'username_hash' => hash('sha256', $username),
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
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (\Throwable $exception) {
            log_message('error', 'Login admin gagal terhubung ke database: ' . $exception->getMessage());

            return redirect()->to(site_url('admin/login'))
                ->withInput()
                ->with('login_error', 'Database belum dapat dijangkau. Periksa koneksi lokal lalu coba lagi.');
        }

        $passwordHash = $admin['password_hash'] ?? '$2y$10$C6UzMDM.H6dfI/f/IKcEe.ogMWp7LhG9Q7xR7VqP6E4v4XQ1iJ2yK';
        $passwordValid = password_verify($password, $passwordHash);

        if ($admin && $passwordValid && ($admin['status'] ?? '') === 'aktif') {
            if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_DEFAULT)) {
                $db->table('admin_users')->where('id', (int) $admin['id'])->update([
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
            }
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

        if ($admin && $passwordValid && in_array(($admin['status'] ?? ''), ['menunggu', 'nonaktif'], true)) {
            $message = ($admin['status'] ?? '') === 'menunggu'
                ? 'Pendaftaran akun masih menunggu persetujuan Super Admin.'
                : 'Akun ini sedang dinonaktifkan. Hubungi Super Admin.';

            return redirect()->to(site_url('admin/login'))->with('login_error', $message);
        }

        log_message('warning', 'Login admin gagal. IP: {ip}, username hash: {username_hash}', [
            'ip' => $clientIp,
            'username_hash' => hash('sha256', $username),
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
        $rawPhone = trim((string) $this->request->getPost('no_hp'));
        $noHp = preg_replace('/[^0-9+ -]/', '', $rawPhone);
        $role = trim((string) $this->request->getPost('role'));
        $catatan = trim((string) $this->request->getPost('catatan_pendaftaran'));
        $password = (string) $this->request->getPost('password');
        $passwordConfirmation = (string) $this->request->getPost('password_confirmation');
        $roleOptions = admin_registration_role_options();

        if ($nama === '' || $username === '' || $noHp === '') {
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Nama, username, dan nomor WhatsApp wajib diisi.');
        }
        if (! AdminRegistrationValidator::isValidWhatsApp($rawPhone)) {
            return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Masukkan nomor WhatsApp dengan 8 sampai 15 angka.');
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
                return redirect()->to(site_url('admin/daftar'))->withInput()->with('register_error', 'Pendaftaran tidak dapat diproses. Periksa data Anda atau hubungi pengurus RW.');
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
            log_message('notice', 'Pendaftaran admin menunggu persetujuan. ID: {id}, IP: {ip}', [
                'id' => (int) $db->insertID(),
                'ip' => $clientIp,
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
        return site_url(AdminRoleAccess::landingPath($role));
    }
}
