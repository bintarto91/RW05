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

        log_message('warning', 'Login admin gagal. IP: {ip}, username: {username}', [
            'ip' => $clientIp,
            'username' => $username,
        ]);

        return redirect()->to(site_url('admin/login'))
            ->withInput()
            ->with('login_error', 'Username atau password salah.');
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
