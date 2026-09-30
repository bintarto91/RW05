<?php

use App\Libraries\AdminRoleAccess;
use PHPUnit\Framework\TestCase;

final class AdminRoleAccessTest extends TestCase
{
    public function testLeadershipAndSuperAdminCanReachOperationalModules(): void
    {
        foreach (['superadmin', 'admin', 'ketua_rw'] as $role) {
            self::assertTrue(AdminRoleAccess::allows($role, '/admin/dashboard'));
            self::assertTrue(AdminRoleAccess::allows($role, '/admin/keuangan'));
            self::assertTrue(AdminRoleAccess::allows($role, '/admin/warga'));
            self::assertTrue(AdminRoleAccess::allows($role, '/admin/kesehatan-data'));
        }
    }

    public function testSekretarisIsLimitedToAdministrationAndCitizenFollowUp(): void
    {
        foreach (['program', 'kegiatan', 'layanan', 'pengajuan-surat', 'pengurus', 'aspirasi'] as $module) {
            self::assertTrue(AdminRoleAccess::allows('sekretaris', '/admin/' . $module));
        }
        foreach (['dashboard', 'keuangan', 'warga', 'kesehatan-data', 'akun', 'import'] as $module) {
            self::assertFalse(AdminRoleAccess::allows('sekretaris', '/admin/' . $module));
        }
    }

    public function testBendaharaAndDataServiceOperatorHaveNarrowScopes(): void
    {
        self::assertTrue(AdminRoleAccess::allows('bendahara', '/admin/keuangan'));
        self::assertFalse(AdminRoleAccess::allows('bendahara', '/admin/warga'));
        self::assertFalse(AdminRoleAccess::allows('bendahara', '/admin/kesehatan-data'));

        self::assertTrue(AdminRoleAccess::allows('operator', '/admin/warga'));
        self::assertTrue(AdminRoleAccess::allows('operator', '/admin/layanan'));
        self::assertTrue(AdminRoleAccess::allows('operator', '/admin/pengajuan-surat'));
        self::assertFalse(AdminRoleAccess::allows('operator', '/admin/keuangan'));
        self::assertFalse(AdminRoleAccess::allows('operator', '/admin/kesehatan-data'));
    }

    public function testHealthRolesCannotOpenOtherModulesByDirectUrl(): void
    {
        foreach (['kader_kesehatan', 'nakes'] as $role) {
            foreach (['kesehatan-dashboard', 'kesehatan-data', 'posbindu-laporan', 'kesehatan-tindak-lanjut', 'kesehatan-jadwal'] as $module) {
                self::assertTrue(AdminRoleAccess::allows($role, '/admin/' . $module));
            }
            self::assertFalse(AdminRoleAccess::allows($role, '/admin/keuangan'));
            self::assertFalse(AdminRoleAccess::allows($role, '/admin/warga'));
        }
    }

    public function testRoleLandingPagesMatchTheirWorkspaces(): void
    {
        self::assertSame('admin', AdminRoleAccess::landingPath('ketua_rw'));
        self::assertSame('admin/pengajuan-surat', AdminRoleAccess::landingPath('sekretaris'));
        self::assertSame('admin/keuangan', AdminRoleAccess::landingPath('bendahara'));
        self::assertSame('admin/warga', AdminRoleAccess::landingPath('operator'));
        self::assertSame('admin/kesehatan-dashboard', AdminRoleAccess::landingPath('nakes'));
        self::assertSame('admin/login', AdminRoleAccess::landingPath('unknown'));
    }
}