<?php

namespace App\Libraries;

final class AspirasiTicketCode
{
    public static function generate(): string
    {
        return 'ASP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(12)));
    }

    public static function isValid(string $code): bool
    {
        return preg_match('/^ASP-[0-9]{8}-[A-F0-9]{24}$/D', strtoupper(trim($code))) === 1;
    }
}