<?php

use App\Libraries\AdminRegistrationValidator;
use PHPUnit\Framework\TestCase;

final class AdminRegistrationValidatorTest extends TestCase
{
    public function testAcceptsLocalAndInternationalWhatsAppFormats(): void
    {
        self::assertTrue(AdminRegistrationValidator::isValidWhatsApp('0812-2049-7423'));
        self::assertTrue(AdminRegistrationValidator::isValidWhatsApp('+62 812 2049 7423'));
        self::assertTrue(AdminRegistrationValidator::isValidWhatsApp('+1 (415) 555-2671'));
    }

    public function testRejectsMalformedOrOutOfRangeWhatsAppNumbers(): void
    {
        foreach (['', '1234567', '1234567890123456', '++6281220497423', '08abc12345', '08+1234567'] as $phone) {
            self::assertFalse(AdminRegistrationValidator::isValidWhatsApp($phone), $phone);
        }
    }
}