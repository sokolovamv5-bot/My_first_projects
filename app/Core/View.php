<?php
namespace App\Core;

/**
 * Simple View Helper Class
 */
class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data);
        
        $templatePath = __DIR__ . "/../../resources/views/{$template}.php";
        
        if (!file_exists($templatePath)) {
            throw new \Exception("View not found: {$template}");
        }
        
        include $templatePath;
    }

    public static function renderLayout(string $layout, string $content, array $data = []): void
    {
        extract($data);
        
        $layoutPath = __DIR__ . "/../../resources/views/layouts/{$layout}.php";
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout}");
        }
        
        include $layoutPath;
    }

    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function asset(string $path): string
    {
        $baseUrl = rtrim(getenv('APP_URL') ?: '/', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public static function url(string $path): string
    {
        $baseUrl = rtrim(getenv('APP_URL') ?: '', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public static function formatPrice(float $price): string
    {
        return number_format($price, 0, '.', ' ') . ' ₽';
    }

    public static function formatDate(string $date, string $format = 'd.m.Y'): string
    {
        return date($format, strtotime($date));
    }

    public static function formatDateTime(string $date, string $format = 'd.m.Y H:i'): string
    {
        return date($format, strtotime($date));
    }
}
