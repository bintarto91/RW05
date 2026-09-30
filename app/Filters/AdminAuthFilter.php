<?php

namespace App\Filters;

use App\Libraries\AdminRoleAccess;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('admin_id')) {
            return redirect()->to(site_url('admin/login'));
        }

        try {
            $admin = db_connect()->table('admin_users')
                ->select('id, role, status, session_version')
                ->where('id', (int) session()->get('admin_id'))
                ->get()
                ->getRowArray();
        } catch (\Throwable $exception) {
            log_message('error', 'Admin session validation failed: ' . $exception->getMessage());
            session()->destroy();

            return redirect()->to(site_url('admin/login'));
        }

        if (! $admin || ($admin['status'] ?? '') !== 'aktif'
            || (int) ($admin['session_version'] ?? 1) !== (int) session()->get('admin_session_version')) {
            session()->destroy();

            return redirect()->to(site_url('admin/login'));
        }

        session()->set('admin_role', (string) ($admin['role'] ?? session()->get('admin_role')));

        $role = (string) session()->get('admin_role');
        $requestPath = $request->getUri()->getPath();
        if (! AdminRoleAccess::allows($role, $requestPath)) {
            $landingPath = AdminRoleAccess::landingPath($role);
            if ($landingPath === 'admin/login') {
                session()->destroy();

                return redirect()->to(site_url('admin/login'));
            }

            return redirect()->to(site_url($landingPath))
                ->with('workspace_error', 'Akun Anda tidak memiliki akses ke modul tersebut.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
