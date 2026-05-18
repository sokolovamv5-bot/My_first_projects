<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Product Controller - Handles individual product pages
 */
class ProductController extends Controller
{
    public function show(string $id): void
    {
        $productId = (int)$id;

        // Get product details
        $product = $this->db->fetch(
            "SELECT p.*, c.name as category_name, t.name as type_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.category_id
             LEFT JOIN types t ON p.type_id = t.type_id
             WHERE p.product_id = :id",
            ['id' => $productId]
        );

        if (!$product) {
            http_response_code(404);
            $this->view('errors/404', [
                'pageTitle' => 'Товар не найден',
                'user' => $this->user
            ]);
            return;
        }

        // Get product images
        $images = $this->db->fetchAll(
            "SELECT * FROM product_images 
             WHERE product_id = :id 
             ORDER BY is_main DESC, sort_order ASC",
            ['id' => $productId]
        );

        // Check if in favorites
        $isFavorite = false;
        if ($this->user) {
            $favorite = $this->db->fetch(
                "SELECT favorite_id FROM favorites 
                 WHERE user_id = :user_id AND type = 'product' AND item_id = :item_id",
                ['user_id' => $this->user['user_id'], 'item_id' => $productId]
            );
            $isFavorite = (bool)$favorite;
        }

        $this->view('product/show', [
            'pageTitle' => htmlspecialchars($product['name']),
            'product' => $product,
            'images' => $images,
            'isFavorite' => $isFavorite,
            'user' => $this->user,
            'isAdmin' => $this->isAdmin
        ]);
    }
}
