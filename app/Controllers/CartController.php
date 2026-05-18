<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Cart Controller - Handles shopping cart operations
 */
class CartController extends Controller
{
    public function index(): void
    {
        $cart = $this->getCart();
        $total = $this->calculateTotal($cart);

        $this->view('cart/index', [
            'pageTitle' => 'Корзина',
            'cart' => $cart,
            'total' => $total,
            'user' => $this->user
        ]);
    }

    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        $type = $_POST['type'] ?? 'product';
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($itemId <= 0) {
            $this->setFlash('error', 'Некорректный товар');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        // Validate item exists
        if ($type === 'product') {
            $item = $this->db->fetch(
                "SELECT * FROM products WHERE product_id = :id",
                ['id' => $itemId]
            );
            if (!$item || $item['stock_quantity'] <= 0) {
                $this->setFlash('error', 'Товар недоступен');
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        } else {
            $item = $this->db->fetch(
                "SELECT * FROM master_classes WHERE mc_id = :id",
                ['id' => $itemId]
            );
            if (!$item) {
                $this->setFlash('error', 'Мастер-класс не найден');
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
            $quantity = 1; // MC always quantity 1
        }

        // Add to session cart
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $cartKey = $type . '_' . $itemId;

        if (isset($_SESSION['cart'][$cartKey])) {
            if ($type === 'product') {
                $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
            }
        } else {
            $_SESSION['cart'][$cartKey] = [
                'type' => $type,
                'item_id' => $itemId,
                'name' => $item['name'] ?? $item['title'],
                'price' => $type === 'product' ? $item['price'] : $item['price_buy'],
                'quantity' => $quantity,
                'image' => $this->getItemImage($type, $itemId)
            ];
        }

        $this->setFlash('success', 'Добавлено в корзину');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/cart');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $items = $_POST['items'] ?? [];

        foreach ($items as $cartKey => $quantity) {
            $quantity = max(0, (int)$quantity);
            
            if ($quantity <= 0 && isset($_SESSION['cart'][$cartKey])) {
                unset($_SESSION['cart'][$cartKey]);
            } elseif (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
            }
        }

        $this->setFlash('success', 'Корзина обновлена');
        $this->redirect('/cart');
    }

    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cartKey = $_POST['cart_key'] ?? null;

        if ($cartKey && isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
        }

        $this->setFlash('success', 'Товар удалён из корзины');
        $this->redirect('/cart');
    }

    private function getCart(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['cart'] ?? [];
    }

    private function calculateTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    private function getItemImage(string $type, int $itemId): ?string
    {
        if ($type === 'product') {
            $image = $this->db->fetch(
                "SELECT image_url FROM product_images 
                 WHERE product_id = :id AND is_main = 1",
                ['id' => $itemId]
            );
            return $image ? $image['image_url'] : null;
        }
        return null;
    }
}
