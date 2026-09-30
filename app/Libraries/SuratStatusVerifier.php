<?php

namespace App\Libraries;

final class SuratStatusVerifier
{
    public static function hasValidLookupInput(string $code, string $verifier): bool
    {
        $code = trim($code);

        return $code !== ''
            && strlen($code) <= 32
            && self::isFourDigitVerifier($verifier);
    }

    public static function isFourDigitVerifier(string $verifier): bool
    {
        return preg_match('/^[0-9]{4}$/D', $verifier) === 1;
    }

    public static function matchesPhoneLastFour(string $phoneNumber, string $verifier): bool
    {
        if (! self::isFourDigitVerifier($verifier)) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber);

        return is_string($digits)
            && strlen($digits) >= 4
            && hash_equals(substr($digits, -4), $verifier);
    }
}