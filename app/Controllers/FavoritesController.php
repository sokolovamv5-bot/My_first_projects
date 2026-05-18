<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Favorites Controller - Handles user favorites
 */
class FavoritesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $favorites = $this->db->fetchAll(
            "SELECT f.*, 
                    p.name as product_name, p.price as product_price, pi.image_url as product_image,
                    mc.title as mc_title, mc.price_buy as mc_price
             FROM favorites f
             LEFT JOIN products p ON f.type = 'product' AND f.item_id = p.product_id
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
             LEFT JOIN master_classes mc ON f.type = 'mc' AND f.item_id = mc.mc_id
             WHERE f.user_id = :user_id
             ORDER BY f.created_at DESC",
            ['user_id' => $this->user['user_id']]
        );

        $this->view('user/favorites', [
            'pageTitle' => 'Избранное',
            'favorites' => $favorites,
            'user' => $this->user
        ]);
    }

    public function toggle(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(null, ['message' => 'Invalid method'], 405);
        }

        $type = $_POST['type'] ?? '';
        $itemId = (int)($_POST['item_id'] ?? 0);

        if (!in_array($type, ['product', 'mc']) || $itemId <= 0) {
            $this->jsonResponse(null, ['message' => 'Invalid parameters'], 400);
        }

        // Check if already in favorites
        $existing = $this->db->fetch(
            "SELECT favorite_id FROM favorites 
             WHERE user_id = :user_id AND type = :type AND item_id = :item_id",
            ['user_id' => $this->user['user_id'], 'type' => $type, 'item_id' => $itemId]
        );

        if ($existing) {
            // Remove from favorites
            $this->db->delete(
                'favorites',
                'user_id = :user_id AND type = :type AND item_id = :item_id',
                ['user_id' => $this->user['user_id'], 'type' => $type, 'item_id' => $itemId]
            );
            $this->jsonResponse(['action' => 'removed']);
        } else {
            // Add to favorites
            $this->db->insert('favorites', [
                'user_id' => $this->user['user_id'],
                'type' => $type,
                'item_id' => $itemId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $this->jsonResponse(['action' => 'added']);
        }
    }
}
