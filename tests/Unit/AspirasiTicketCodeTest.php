<?php

use App\Libraries\AspirasiTicketCode;
use PHPUnit\Framework\TestCase;

final class AspirasiTicketCodeTest extends TestCase
{
    public function testGeneratedTicketIsHighEntropyAndHasExpectedFormat(): void
    {
        $first = AspirasiTicketCode::generate();
        $second = AspirasiTicketCode::generate();

        self::assertSame(37, strlen($first));
        self::assertMatchesRegularExpression('/^ASP-[0-9]{8}-[A-F0-9]{24}$/D', $first);
        self::assertTrue(AspirasiTicketCode::isValid($first));
        self::assertNotSame($first, $second);
    }

    public function testTicketValidationRejectsGuessableOrMalformedCodes(): void
    {
        foreach (['', 'ASP-20260930-1234', 'ASP-20260930-ABCDEFGHIJKLMNOPQRSTUVWXextra', 'RW05-20260930-ABC123'] as $code) {
            self::assertFalse(AspirasiTicketCode::isValid($code));
        }
        self::assertTrue(AspirasiTicketCode::isValid(' asp-20260930-abcdef0123456789abcdef01 '));
    }

    public function testLookupUsesPostAndReturnsOnlyNonIdentifyingFields(): void
    {
        $root = dirname(__DIR__, 2);
        $routes = file_get_contents($root . '/app/Config/Routes.php');
        $controller = file_get_contents($root . '/app/Controllers/PublicController.php');
        $view = file_get_contents($root . '/app/Views/public/aspirasi.php');
        self::assertIsString($routes);
        self::assertIsString($controller);
        self::assertIsString($view);
        self::assertStringContainsString("post('aspirasi/status', 'PublicController::cekStatusAspirasi')", $routes);
        self::assertStringContainsString("select('kode_tiket, kategori, status, created_at')", $controller);
        self::assertStringContainsString('where(\'kode_tiket\', $ticketCode)', $controller);
        self::assertStringContainsString('service(\'throttler\')->check($throttleKey, 10, 300)', $controller);
        self::assertStringContainsString('name="kode_tiket"', $view);
        foreach ([
            '$ticketResult[\'nama\']',
            '$ticketResult[\'no_hp\']',
            '$ticketResult[\'rt\']',
            '$ticketResult[\'pesan\']',
            '$ticketResult[\'catatan_admin\']',
        ] as $privateField) {
            self::assertStringNotContainsString($privateField, $view);
        }
    }

    public function testMigrationIsAdditiveNullableUniqueAndReversible(): void
    {
        $migration = file_get_contents(dirname(__DIR__, 2) . '/app/Database/Migrations/2026-09-30-000001_AddAspirasiPublicTicketCode.php');
        self::assertIsString($migration);
        self::assertStringContainsString("'kode_tiket' => [", $migration);
        self::assertStringContainsString("'null' => true", $migration);
        self::assertStringContainsString("addUniqueKey('kode_tiket', 'aspirasi_kode_tiket_unique')", $migration);
        self::assertStringContainsString("dropColumn('aspirasi', 'kode_tiket')", $migration);
        self::assertStringNotContainsString('DROP TABLE', strtoupper($migration));
    }

    public function testAspirasiWorkflowSupportsLegacyAndRequestedIndonesianStatuses(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Controllers/Admin/PanelController.php');
        $adminView = file_get_contents($root . '/app/Views/admin/aspirasi.php');
        $publicView = file_get_contents($root . '/app/Views/public/aspirasi.php');
        self::assertIsString($controller);
        self::assertIsString($adminView);
        self::assertIsString($publicView);

        foreach (['baru', 'diverifikasi', 'diproses', 'selesai', 'ditolak'] as $status) {
            self::assertStringContainsString("'$status'", $controller);
            self::assertStringContainsString("value=\"$status\"", $adminView);
        }
        self::assertStringContainsString("'baru' => 'Diterima'", $publicView);
        self::assertStringContainsString("'ditolak' => 'Ditolak'", $publicView);
    }
}