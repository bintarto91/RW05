<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        if (session('admin_id')) {
            return redirect()->to(site_url('admin'));
        }

        return view('admin/login', [
            'error' => session()->getFlashdata('login_error') ?: '',
        ]);
    }

    public function attemptLogin()
    {
        $username = normalize_admin_username($this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $db = db_connect();
        ensure_admin_users_table($db);

        $admin = $db->table('admin_users')
            ->where('username', $username)
            ->where('status', 'aktif')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session()->regenerate();
            session()->set([
                'admin_id' => (int) $admin['id'],
                'admin_nama' => $admin['nama'],
                'admin_role' => $admin['role'] ?? 'admin',
            ]);

            return redirect()->to(site_url('admin'));
        }

        return redirect()->to(site_url('admin/login'))
            ->withInput()
            ->with('login_error', 'Username atau password salah.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('admin/login'));
    }
}
