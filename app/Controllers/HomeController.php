<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Home Controller
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $config = require __DIR__ . '/../../config/config.php';
        
        // Get featured products
        $products = $this->db->fetchAll(
            "SELECT p.*, pi.image_url as main_image 
             FROM products p 
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1 
             WHERE p.is_published = 1 
             ORDER BY p.created_at DESC 
             LIMIT 6"
        );
        
        // Get featured master classes
        $masterClasses = $this->db->fetchAll(
            "SELECT * FROM master_classes 
             WHERE is_published = 1 
             ORDER BY created_at DESC 
             LIMIT 6"
        );
        
        $this->view('home/index', [
            'pageTitle' => $config['app']['name'],
            'products' => $products,
            'masterClasses' => $masterClasses,
            'user' => $this->user,
            'isAdmin' => $this->isAdmin
        ]);
    }
    
    public function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404', [
            'pageTitle' => 'Страница не найдена',
            'user' => $this->user
        ]);
    }
}
