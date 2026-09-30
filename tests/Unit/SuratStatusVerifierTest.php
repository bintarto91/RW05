<?php

use App\Libraries\SuratStatusVerifier;
use PHPUnit\Framework\TestCase;

final class SuratStatusVerifierTest extends TestCase
{
    public function testLookupRequiresCodeAndExactlyFourAsciiDigits(): void
    {
        self::assertTrue(SuratStatusVerifier::hasValidLookupInput('RW05-20260930-ABC123', '6789'));
        self::assertFalse(SuratStatusVerifier::hasValidLookupInput('', '6789'));
        self::assertFalse(SuratStatusVerifier::hasValidLookupInput(str_repeat('A', 33), '6789'));
        self::assertFalse(SuratStatusVerifier::hasValidLookupInput('RW05-20260930-ABC123', '678'));
        self::assertFalse(SuratStatusVerifier::hasValidLookupInput('RW05-20260930-ABC123', '67890'));
        self::assertFalse(SuratStatusVerifier::hasValidLookupInput('RW05-20260930-ABC123', '67a9'));
        self::assertFalse(SuratStatusVerifier::hasValidLookupInput('RW05-20260930-ABC123', '６７８９'));
    }

    public function testVerifierMatchesOnlyTheLastFourPhoneDigits(): void
    {
        self::assertTrue(SuratStatusVerifier::matchesPhoneLastFour('+62 812-3456-7890', '7890'));
        self::assertFalse(SuratStatusVerifier::matchesPhoneLastFour('+62 812-3456-7890', '7891'));
        self::assertFalse(SuratStatusVerifier::matchesPhoneLastFour('123', '0123'));
        self::assertFalse(SuratStatusVerifier::matchesPhoneLastFour('+62 812-3456-7890', '78a0'));
    }

    public function testStatusFormPostsOnlyCodeAndVerifier(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2) . '/app/Views/public/layanan_online.php');
        self::assertIsString($view);
        $formStart = strpos($view, 'class="online-check-form"');
        self::assertNotFalse($formStart);
        $formOpen = strrpos(substr($view, 0, $formStart), '<form');
        $formEnd = strpos($view, '</form>', $formStart);
        self::assertNotFalse($formOpen);
        self::assertNotFalse($formEnd);
        $form = substr($view, $formOpen, $formEnd - $formOpen);

        self::assertStringContainsString("action=\"<?= site_url('layanan-online/status') ?>\"", $form);
        self::assertStringContainsString('csrf_field()', $form);
        self::assertStringContainsString('name="kode"', $form);
        self::assertStringContainsString('name="verifikasi"', $form);
        self::assertStringNotContainsString('name="nama"', $form);
        self::assertStringNotContainsString('name="rt"', $form);
    }

    public function testStatusResultDoesNotRenderApplicantDetails(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2) . '/app/Views/public/layanan_online.php');
        self::assertIsString($view);
        $resultStart = strpos($view, '<div class="letter-check-result">');
        self::assertNotFalse($resultStart);
        $resultEnd = strpos($view, '</div>', $resultStart);
        self::assertNotFalse($resultEnd);
        $result = substr($view, $resultStart, $resultEnd - $resultStart);

        foreach (["['nama']", "['rt']", "['alamat']", "['no_hp']", "['detail_json']", "['catatan_admin']"] as $privateField) {
            self::assertStringNotContainsString('$lookupRow' . $privateField, $result);
        }
    }

    public function testStatusPageDoesNotReadCodeFromGetQuery(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2) . '/app/Controllers/PublicController.php');
        self::assertIsString($controller);
        $methodStart = strpos($controller, 'public function layananOnline(): string');
        $methodEnd = strpos($controller, 'public function cekStatusLayananOnline()', $methodStart);
        self::assertNotFalse($methodStart);
        self::assertNotFalse($methodEnd);
        $method = substr($controller, $methodStart, $methodEnd - $methodStart);

        self::assertStringNotContainsString("getGet('kode')", $method);
        self::assertStringContainsString("getFlashdata('surat_lookup_code')", $method);
    }

    public function testSuccessfulLookupUsesSessionFlashAndExactRateLimitedQuery(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2) . '/app/Controllers/PublicController.php');
        self::assertIsString($controller);
        $methodStart = strpos($controller, 'public function cekStatusLayananOnline()');
        $methodEnd = strpos($controller, 'public function submitLayananOnline()', $methodStart);
        self::assertNotFalse($methodStart);
        self::assertNotFalse($methodEnd);
        $method = substr($controller, $methodStart, $methodEnd - $methodStart);

        self::assertStringContainsString('service(\'throttler\')->check($throttleKey, 20, 300)', $method);
        self::assertStringContainsString('->where(\'kode_pengajuan\', $lookupCode)', $method);
        self::assertStringContainsString('->with(\'surat_lookup_code\', $lookupCode)', $method);
        self::assertStringNotContainsString('?kode=', $method);
        self::assertStringNotContainsString("like('nama'", $method);
        self::assertStringNotContainsString("orLike('rt'", $method);
    }
}