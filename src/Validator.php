<?php
class Validator
{
    public static function required(string $value): string
    {
        return trim($value) === '' ? 'Field wajib diisi.' : '';
    }

    public static function email(string $email): string
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? '' : 'Format email tidak valid.';
    }

    public static function nidn(string $nidn): string
    {
        return preg_match('/^\d{10}$/', $nidn) ? '' : 'NIDN harus 10 digit angka.';
    }
}
