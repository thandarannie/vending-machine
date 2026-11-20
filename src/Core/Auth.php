<?php
class Auth
{
    public static function login(array $user): void
    {
        session_regenerate_id(true); // new session id after login (stops session fixation attacks)

        $_SESSION['user'] = [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user']['role'] ?? null) === 'admin';
    }
}
