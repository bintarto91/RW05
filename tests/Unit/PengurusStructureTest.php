<?php

use PHPUnit\Framework\TestCase;

final class PengurusStructureTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testReferenceContainsEveryOfficialPersonAndRole(): void
    {
        $source = file_get_contents($this->root . '/app/Commands/SyncPengurusReference.php');
        self::assertIsString($source);

        foreach ([
            'Kepala Desa', 'Bpk. H. Sumaryono', 'Bpk. Erno', 'Bpk. Irwan (Ujang Cuek)',
            'Dwi Wahyu Bintarto Prasetyo', 'Ibu Nia Kurniasih', 'Bpk. Wahyu Budiman',
            'Ibu Kartika', 'Bpk. Kurnia', 'Bpk. Dadan Ruhimat', 'Wahyu Dwi Haryono',
            'Bpk. Atang', 'Bpk. Dede (Oding)', 'Ibu Yunita Fitri Rejeki', 'Bpk. Yogi',
            'Bpk. Ibnu Majah', 'Bpk. Usep', 'Bpk. Ust. Usman Ansori', 'Andre Febrian',
            'Acep Kurnia', 'Ibu Nani Maryani', 'Rudi', 'Bpk. Tono',
        ] as $name) {
            self::assertStringContainsString($name, $source);
        }

        foreach ([
            'Unit Pelayanan Digital & Data Warga', 'Bidang Pembangunan & Lingkungan',
            'Bidang Sosial & Kesehatan', 'Bidang Keamanan & Ketertiban',
            'Bidang Pendidikan, Agama & Budaya', 'Bidang Ekonomi, Pemuda & Olahraga',
            'Bidang Humas & Informasi Publik', 'PKK', 'Posyandu', 'Posbindu',
            'Karang Taruna', 'DKM / Keagamaan', 'Linmas / Siskamling',
        ] as $role) {
            self::assertStringContainsString($role, $source);
        }

        self::assertStringContainsString("Administrasi online\\nDatabase warga\\nLayanan surat", $source);
        self::assertStringContainsString('0 data dihapus', $source);
    }

    public function testAdminAndPublicUseOneSharedDiagramAndStandardizedRoles(): void
    {
        $adminView = file_get_contents($this->root . '/app/Views/admin/crud.php');
        $publicView = file_get_contents($this->root . '/app/Views/public/pengurus.php');
        $adminLayout = file_get_contents($this->root . '/app/Views/layouts/admin.php');
        $publicLayout = file_get_contents($this->root . '/app/Views/layouts/public.php');
        $controller = file_get_contents($this->root . '/app/Controllers/Admin/PanelController.php');
        self::assertIsString($adminView);
        self::assertIsString($publicView);
        self::assertIsString($adminLayout);
        self::assertIsString($publicLayout);
        self::assertIsString($controller);

        self::assertStringContainsString("view('components/pengurus_chart'", $adminView);
        self::assertStringContainsString("view('components/pengurus_chart'", $publicView);
        self::assertStringContainsString("assets/org-chart.css", $adminLayout);
        self::assertStringContainsString("assets/org-chart.css", $publicLayout);
        self::assertStringContainsString('pengurus_structure_role_options()', $controller);
        self::assertStringContainsString('Kelompok / Jabatan Struktur', $controller);
    }

    public function testDiagramDeclaresIdentityPeriodAndRelationshipLegend(): void
    {
        $component = file_get_contents($this->root . '/app/Views/components/pengurus_chart.php');
        self::assertIsString($component);
        self::assertStringContainsString('RW 05 LAMAJANG PEUNTAS', $component);
        self::assertStringContainsString('Desa Citeureup', $component);
        self::assertStringContainsString('Kecamatan Dayeuhkolot', $component);
        self::assertStringContainsString('Kabupaten Bandung', $component);
        self::assertStringContainsString('Struktur / Komando', $component);
        self::assertStringContainsString('Koordinasi / Kemitraan', $component);
        self::assertStringNotContainsString('rw-org-card-icon', $component);
        self::assertStringContainsString('rw-org-advisory-left', $component);
        self::assertStringContainsString('rw-org-advisory-right', $component);
        self::assertStringContainsString("\$groupKey === 'pelayanan'", $component);
        self::assertStringContainsString('rw-org-link-after-', $component);
        self::assertStringContainsString('branch-service', $component);
        self::assertStringContainsString('branch-main', $component);
        self::assertStringContainsString('rw-org-main-trunk', $component);
    }

    public function testSummarySeparatesUniquePeopleFromRolePositions(): void
    {
        $view = file_get_contents($this->root . '/app/Views/public/pengurus.php');
        $helper = file_get_contents($this->root . '/app/Helpers/rw_helper.php');
        self::assertIsString($view);
        self::assertIsString($helper);
        self::assertStringContainsString('pengurus_unique_people_count', $helper);
        self::assertStringContainsString('posisi aktif', $view);
        self::assertStringContainsString('Orang aktif', $view);
    }
}
