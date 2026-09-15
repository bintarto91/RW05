<?php

namespace App\Filters;

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

        if ((string) session()->get('admin_role') === 'kader_kesehatan') {
            $pathSegments = explode('/', trim($request->getUri()->getPath(), '/'));
            $adminSegment = array_search('admin', $pathSegments, true);
            $path = $adminSegment === false
                ? implode('/', $pathSegments)
                : implode('/', array_slice($pathSegments, $adminSegment));
            $healthWorkspacePaths = [
                'admin/kesehatan-dashboard',
                'admin/kesehatan-data',
                'admin/kesehatan-tindak-lanjut',
                'admin/kesehatan-jadwal',
            ];

            if (! in_array($path, $healthWorkspacePaths, true)) {
                return redirect()->to(site_url('admin/kesehatan-dashboard'))
                    ->with('workspace_error', 'Akun kader hanya dapat mengakses ruang kerja kesehatan.');
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
