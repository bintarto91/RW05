<?php

use PHPUnit\Framework\TestCase;

final class WargaPaginationTest extends TestCase
{
    public function testWargaListUsesBoundedPagesAndKeepsFilteredExportsUnpaginated(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2) . '/app/Controllers/Admin/PanelController.php');
        self::assertIsString($controller);
        self::assertStringContainsString('$perPage = 25;', $controller);
        self::assertStringContainsString('$this->wargaRows($db, $filters, $perPage, $offset)', $controller);
        self::assertStringContainsString('$this->wargaRows($db, $filters);', $controller);
        self::assertStringContainsString('->limit(max(1, $limit), max(0, $offset))', $controller);
        self::assertStringContainsString('private function wargaSummaryForFilters(', $controller);
    }

    public function testWargaSearchAndPaginationLinksPreserveFilters(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Controllers/Admin/PanelController.php');
        $view = file_get_contents($root . '/app/Views/admin/warga.php');
        self::assertIsString($controller);
        self::assertIsString($view);
        self::assertStringContainsString('\'q\' => substr(trim((string) $this->request->getGet(\'q\')), 0, 80)', $controller);
        self::assertStringContainsString('$builder->like(\'nama_kepala_keluarga\', $filters[\'q\'])', $controller);
        self::assertStringContainsString('\'q\' => $filters[\'q\'] ?? \'\'', $view);
        self::assertStringContainsString('name="q"', $view);
        self::assertStringContainsString('$wargaUrl([\'page\' => $wargaPageNumber + 1])', $view);
    }
}