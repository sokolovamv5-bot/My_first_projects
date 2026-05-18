<?php
namespace App\Core;

/**
 * Base Controller Class
 */
abstract class Controller
{
    protected Database $db;
    protected ?array $user = null;
    protected bool $isAdmin = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->initUser();
    }

    protected function initUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            $this->user = $this->db->fetch(
                "SELECT * FROM users WHERE user_id = :id",
                ['id' => $_SESSION['user_id']]
            );
            
            if ($this->user) {
                $this->isAdmin = (bool) $this->user['is_admin'];
            }
        }
    }

    protected function view(string $template, array $data = []): void
    {
        extract($data);
        
        $templatePath = __DIR__ . "/../../resources/views/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new \Exception("View not found: {$template}");
        }
        
        include $templatePath;
    }

    protected function layout(string $layout, string $content, array $data = []): void
    {
        extract($data);
        
        $layoutPath = __DIR__ . "/../../resources/views/layouts/{$layout}.php";
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout}");
        }
        
        include $layoutPath;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function jsonResponse($data = null, $error = null, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'data' => $data,
            'error' => $error
        ]);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!$this->user) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        
        if (!$this->isAdmin) {
            $this->redirect('/');
        }
    }

    protected function csrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrfToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
    }

    protected function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        
        return $flash;
    }
}
