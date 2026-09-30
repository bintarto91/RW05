<?php

namespace App\Libraries;

final class AdminRegistrationValidator
{
    public static function isValidWhatsApp(string $phone): bool
    {
        $phone = trim($phone);
        if ($phone === '' || preg_match('/^\+?[0-9][0-9 ().-]*$/D', $phone) !== 1) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return is_string($digits) && strlen($digits) >= 8 && strlen($digits) <= 15;
    }
}