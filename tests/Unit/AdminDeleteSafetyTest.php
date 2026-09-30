<?php

use PHPUnit\Framework\TestCase;

final class AdminDeleteSafetyTest extends TestCase
{
    private function panelSource(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/app/Controllers/Admin/PanelController.php');
        self::assertIsString($source);

        return $source;
    }

    public function testIrreversibleHistoryDeletesUseSuperAdminGuard(): void
    {
        $source = $this->panelSource();
        foreach (['letter_application', 'resident_record', 'resident_aspiration', 'finance_transaction'] as $recordType) {
            self::assertStringContainsString("canHardDelete('$recordType'", $source);
        }
        self::assertStringContainsString("session('admin_role') === 'superadmin'", $source);
    }

    public function testExistingStatusColumnsAreUsedToArchiveWhereAvailable(): void
    {
        $source = $this->panelSource();
        self::assertStringContainsString("'archiveStatus' => 'nonaktif'", $source);
        self::assertStringContainsString("'archiveStatus' => 'draft'", $source);
        self::assertStringContainsString("update(['status' => 'nonaktif'])", $source);
        self::assertStringContainsString("update(['status' => 'draft'])", $source);
        self::assertStringContainsString("'status' => 'nonaktif',", $source);
        self::assertStringContainsString('\'session_version\' => (int) ($target[\'session_version\'] ?? 1) + 1', $source);
    }

    public function testHealthParticipantArchiveDoesNotDeleteVisitHistory(): void
    {
        $source = $this->panelSource();
        $actionStart = strpos($source, 'if ($action === \'delete_participant\')');
        $actionEnd = strpos($source, 'if ($this->request->getGet(\'export\') === \'posbindu-xlsx\'', $actionStart);
        self::assertNotFalse($actionStart);
        self::assertNotFalse($actionEnd);
        $action = substr($source, $actionStart, $actionEnd - $actionStart);

        self::assertStringContainsString("update(['status' => 'nonaktif'])", $action);
        self::assertStringNotContainsString("table('kesehatan_kunjungan')->where('peserta_id'", $action);
        self::assertStringNotContainsString('table(\'kesehatan_peserta\')->where(\'id\', $participantId)->delete()', $action);
    }

    public function testResidentReplaceImportShowsWholeDatasetCountAndRequiresConfirmation(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Controllers/Admin/PanelController.php');
        $view = file_get_contents($root . '/app/Views/admin/warga.php');
        self::assertIsString($controller);
        self::assertIsString($view);
        self::assertStringContainsString('$wargaDatasetCount = (int) $db->table(\'warga\')->countAllResults();', $controller);
        self::assertStringContainsString('name="confirm_replace" value="yes"', $view);
        self::assertStringContainsString('$wargaDatasetCount', $view);
    }
}