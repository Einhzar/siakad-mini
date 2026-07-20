<?php
class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function requireLogin(): void
    {
        self::startSession();
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::startSession();
        if (($_SESSION['role'] ?? '') !== $role) {
            http_response_code(403);
            echo 'Akses ditolak.';
            exit;
        }
    }

    public static function logout(): void
    {
        self::startSession();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
