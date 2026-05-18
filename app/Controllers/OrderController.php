<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Order Controller - Handles checkout and order processing
 */
class OrderController extends Controller
{
    public function showCheckout(): void
    {
        $this->requireAuth();

        // Get cart from session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            $this->setFlash('error', 'Корзина пуста');
            $this->redirect('/cart');
        }

        $total = $this->calculateTotal($cart);

        $this->view('cart/checkout', [
            'pageTitle' => 'Оформление заказа',
            'cart' => $cart,
            'total' => $total,
            'user' => $this->user,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    public function processCheckout(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checkout');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Ошибка безопасности. Попробуйте снова.');
            $this->redirect('/checkout');
        }

        // Get cart from session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            $this->setFlash('error', 'Корзина пуста');
            $this->redirect('/cart');
        }

        // Get delivery info
        $deliveryType = $_POST['delivery_type'] ?? 'courier';
        $deliveryAddress = trim($_POST['delivery_address'] ?? $this->user['address'] ?? '');

        if (empty($deliveryAddress) && $deliveryType !== 'digital') {
            $this->setFlash('error', 'Укажите адрес доставки');
            $this->redirect('/checkout');
        }

        try {
            $this->db->beginTransaction();

            // Calculate total
            $total = $this->calculateTotal($cart);

            // Create order
            $orderId = $this->db->insert('orders', [
                'user_id' => $this->user['user_id'],
                'total_price' => $total,
                'status' => 'new',
                'delivery_address' => $deliveryAddress,
                'delivery_type' => $deliveryType,
                'order_date' => date('Y-m-d H:i:s')
            ]);

            // Create order items
            foreach ($cart as $item) {
                $accessType = null;
                $accessExpires = null;

                // Handle MC access
                if ($item['type'] === 'mc') {
                    // For simplicity, default to permanent access
                    // In real implementation, this should be chosen by user
                    $accessType = 'permanent';
                }

                $this->db->insert('order_items', [
                    'order_id' => $orderId,
                    'type' => $item['type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'access_type' => $accessType,
                    'access_expires' => $accessExpires
                ]);

                // Update stock for products
                if ($item['type'] === 'product') {
                    $this->db->query(
                        "UPDATE products SET stock_quantity = stock_quantity - :qty 
                         WHERE product_id = :id",
                        ['qty' => $item['quantity'], 'id' => $item['item_id']]
                    );
                }
            }

            $this->db->commit();

            // Clear cart
            unset($_SESSION['cart']);

            $this->setFlash('success', 'Заказ успешно оформлен!');
            $this->redirect('/order/success');

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->setFlash('error', 'Произошла ошибка при оформлении заказа: ' . $e->getMessage());
            $this->redirect('/checkout');
        }
    }

    public function successPage(): void
    {
        $this->view('cart/success', [
            'pageTitle' => 'Заказ оформлен',
            'user' => $this->user
        ]);
    }

    private function calculateTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
