<?php
namespace App\Core;

/**
 * Authentication Helper Class
 */
class Auth
{
    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM users WHERE user_id = :id",
            ['id' => $_SESSION['user_id']]
        );
    }

    public static function id(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user_id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        if (!self::check()) {
            return false;
        }
        
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT is_admin FROM users WHERE user_id = :id",
            ['id' => $_SESSION['user_id']]
        );
        
        return $user && (bool) $user['is_admin'];
    }

    public static function login(int $userId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['user_id']);
        unset($_SESSION['csrf_token']);
    }

    public static function attempt(string $email, string $password): bool
    {
        $db = Database::getInstance();
        
        $user = $db->fetch(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $email]
        );
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        self::login($user['user_id']);
        return true;
    }

    public static function register(string $email, string $password, string $name): int
    {
        $db = Database::getInstance();
        
        // Check if user exists
        $existing = $db->fetch(
            "SELECT user_id FROM users WHERE email = :email",
            ['email' => $email]
        );
        
        if ($existing) {
            throw new \Exception('User with this email already exists');
        }
        
        // Create new user
        $userId = $db->insert('users', [
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        self::login($userId);
        
        return $userId;
    }
}
