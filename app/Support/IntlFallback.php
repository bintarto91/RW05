<?php

if (! class_exists('Locale', false)) {
    class Locale
    {
        private static string $default = 'id_ID';

        public static function getDefault(): string
        {
            return self::$default;
        }

        public static function setDefault($locale): bool
        {
            $locale = trim((string) $locale);
            if ($locale !== '') {
                self::$default = $locale;
            }

            return true;
        }
    }
}
