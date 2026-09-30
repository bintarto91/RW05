<?php

namespace App\Libraries;

final class AdminRoleAccess
{
    private const HEALTH_ROUTES = [
        'kesehatan-dashboard',
        'kesehatan-data',
        'posbindu-laporan',
        'kesehatan-tindak-lanjut',
        'kesehatan-jadwal',
    ];

    private const ROLE_ROUTES = [
        'sekretaris' => [
            'program',
            'kegiatan',
            'layanan',
            'pengajuan-surat',
            'pengurus',
            'aspirasi',
        ],
        'bendahara' => ['keuangan'],
        'operator' => ['layanan', 'pengajuan-surat', 'warga'],
        'kader_kesehatan' => self::HEALTH_ROUTES,
        'nakes' => self::HEALTH_ROUTES,
    ];

    public static function allows(string $role, string $requestPath): bool
    {
        $segments = explode('/', trim($requestPath, '/'));
        $adminPosition = array_search('admin', $segments, true);
        if ($adminPosition === false) {
            return false;
        }

        $route = implode('/', array_slice($segments, $adminPosition + 1));
        $module = explode('/', $route, 2)[0] ?? '';

        if (in_array($role, ['superadmin', 'admin', 'ketua_rw'], true)) {
            return true;
        }

        return in_array($module, self::ROLE_ROUTES[$role] ?? [], true);
    }

    public static function landingPath(string $role): string
    {
        return match ($role) {
            'bendahara' => 'admin/keuangan',
            'sekretaris' => 'admin/pengajuan-surat',
            'operator' => 'admin/warga',
            'kader_kesehatan', 'nakes' => 'admin/kesehatan-dashboard',
            'superadmin', 'admin', 'ketua_rw' => 'admin',
            default => 'admin/login',
        };
    }
}