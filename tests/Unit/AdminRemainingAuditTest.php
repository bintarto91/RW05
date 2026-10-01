<?php

use PHPUnit\Framework\TestCase;

final class AdminRemainingAuditTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testHealthWorkspaceUsesPrivateMetadataAndOneLogoutButton(): void
    {
        $layout = file_get_contents($this->root . '/app/Views/layouts/kesehatan_admin.php');
        self::assertIsString($layout);
        self::assertStringContainsString('<meta name="robots" content="noindex,nofollow,noarchive">', $layout);
        self::assertSame(1, substr_count($layout, '>Logout</button>'));

        $privateFilter = file_get_contents($this->root . '/app/Filters/PrivateResponseFilter.php');
        $filterConfig = file_get_contents($this->root . '/app/Config/Filters.php');
        self::assertIsString($privateFilter);
        self::assertIsString($filterConfig);
        self::assertStringContainsString("setHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')", $privateFilter);
        self::assertStringContainsString("'after' => ['admin', 'admin/*']", $filterConfig);

        foreach (['kesehatan_dashboard.php', 'kesehatan_data.php', 'posbindu_laporan.php', 'kesehatan_tindak_lanjut.php', 'kesehatan_jadwal.php'] as $view) {
            $source = file_get_contents($this->root . '/app/Views/admin/' . $view);
            self::assertIsString($source);
            self::assertStringContainsString("extend('layouts/kesehatan_admin')", $source);
        }
    }

    public function testAdminIdentityAndProgramSummaryAreConsistent(): void
    {
        foreach (['admin.php', 'kesehatan_admin.php'] as $layoutName) {
            $layout = file_get_contents($this->root . '/app/Views/layouts/' . $layoutName);
            self::assertIsString($layout);
            self::assertStringContainsString('rw_site_identity()', $layout);
            self::assertStringContainsString('Desa Citeureup', $layout);
        }

        $controller = file_get_contents($this->root . '/app/Controllers/Admin/PanelController.php');
        self::assertIsString($controller);
        self::assertStringContainsString("\$programAktif . ' program aktif'", $controller);
        self::assertStringNotContainsString("\$layananAktif . ' layanan aktif'", $controller);
    }

    public function testProductionBaseUrlHasSafeHttpsDefault(): void
    {
        $config = file_get_contents($this->root . '/app/Config/App.php');
        self::assertIsString($config);
        self::assertStringContainsString(
            "public string \$baseURL = 'https://rw05citeureup.my.id/';",
            $config
        );
        self::assertStringNotContainsString(
            "public string \$baseURL = 'http://localhost:8080/';",
            $config
        );
        self::assertStringContainsString("['rw05citeureup.my.id', 'www.rw05citeureup.my.id']", $config);
        self::assertStringContainsString("\$this->baseURL = 'https://rw05citeureup.my.id/';", $config);

        $deployment = file_get_contents($this->root . '/.cpanel.yml');
        self::assertIsString($deployment);
        self::assertStringContainsString("app.baseURL = 'https://rw05citeureup.my.id/'", $deployment);
    }

    public function testFinanceFiltersHaveExplicitLabels(): void
    {
        $view = file_get_contents($this->root . '/app/Views/admin/keuangan.php');
        self::assertIsString($view);
        foreach ([
            'financeFilterStart' => 'Dari Tanggal',
            'financeFilterEnd' => 'Sampai Tanggal',
            'financeFilterUnit' => 'Unit Kas',
        ] as $id => $label) {
            self::assertStringContainsString('<label for="' . $id . '">' . $label, $view);
            self::assertStringContainsString('id="' . $id . '"', $view);
        }
    }

    public function testPublicRegistrationKeepsSecurityControlsAndCannotRequestSuperAdmin(): void
    {
        $controller = file_get_contents($this->root . '/app/Controllers/Admin/AuthController.php');
        $helper = file_get_contents($this->root . '/app/Helpers/rw_helper.php');
        $filters = file_get_contents($this->root . '/app/Config/Filters.php');
        $view = file_get_contents($this->root . '/app/Views/admin/register.php');
        self::assertIsString($controller);
        self::assertIsString($helper);
        self::assertIsString($filters);
        self::assertIsString($view);

        self::assertStringContainsString("service('throttler')->check(\$throttleKey, 3, 900)", $controller);
        self::assertStringContainsString("'status' => 'menunggu'", $controller);
        self::assertStringContainsString("'csrf'", $filters);
        self::assertStringContainsString('csrf_field()', $view);
        self::assertStringContainsString('<meta name="robots" content="noindex,nofollow,noarchive">', $view);

        $start = strpos($helper, 'function admin_registration_role_options');
        $end = strpos($helper, "if (! function_exists('pengurus_chart_groups'))", $start);
        self::assertNotFalse($start);
        self::assertNotFalse($end);
        $registrationRoles = substr($helper, $start, $end - $start);
        self::assertStringNotContainsString("'superadmin'", $registrationRoles);
    }
}
