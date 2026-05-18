<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Master Class Controller - Handles individual master class pages and purchases
 */
class MasterClassController extends Controller
{
    public function show(string $id): void
    {
        $mcId = (int)$id;

        // Get master class details
        $masterClass = $this->db->fetch(
            "SELECT * FROM master_classes WHERE mc_id = :id",
            ['id' => $mcId]
        );

        if (!$masterClass) {
            http_response_code(404);
            $this->view('errors/404', [
                'pageTitle' => 'Мастер-класс не найден',
                'user' => $this->user
            ]);
            return;
        }

        // Get content tabs
        $contentTabs = $this->db->fetchAll(
            "SELECT * FROM mc_content 
             WHERE mc_id = :id 
             ORDER BY sort_order ASC",
            ['id' => $mcId]
        );

        // Group content by tab name
        $tabs = [];
        foreach ($contentTabs as $content) {
            $tabName = $content['tab_name'];
            if (!isset($tabs[$tabName])) {
                $tabs[$tabName] = [];
            }
            $tabs[$tabName][] = $content;
        }

        // Check if user has access
        $hasAccess = false;
        $accessType = null;
        $accessExpires = null;

        if ($this->user) {
            $access = $this->db->fetch(
                "SELECT oi.access_type, oi.access_expires
                 FROM order_items oi
                 JOIN orders o ON oi.order_id = o.order_id
                 WHERE oi.type = 'mc' 
                   AND oi.item_id = :item_id 
                   AND o.user_id = :user_id
                   AND o.status IN ('new', 'processing', 'shipped', 'completed')
                 ORDER BY oi.access_expires DESC
                 LIMIT 1",
                ['item_id' => $mcId, 'user_id' => $this->user['user_id']]
            );

            if ($access) {
                if ($access['access_type'] === 'permanent') {
                    $hasAccess = true;
                    $accessType = 'permanent';
                } elseif ($access['access_expires']) {
                    $expires = strtotime($access['access_expires']);
                    if ($expires > time()) {
                        $hasAccess = true;
                        $accessType = 'subscription';
                        $accessExpires = $access['access_expires'];
                    }
                }
            }

            // Check if in favorites
            $favorite = $this->db->fetch(
                "SELECT favorite_id FROM favorites 
                 WHERE user_id = :user_id AND type = 'mc' AND item_id = :item_id",
                ['user_id' => $this->user['user_id'], 'item_id' => $mcId]
            );
            $isFavorite = (bool)$favorite;
        } else {
            $isFavorite = false;
        }

        // Get questions for this MC
        $questions = $this->db->fetchAll(
            "SELECT q.*, u.name as user_name
             FROM questions q
             JOIN users u ON q.user_id = u.user_id
             WHERE q.mc_id = :mc_id AND q.status = 'answered'
             ORDER BY q.created_at DESC
             LIMIT 10",
            ['mc_id' => $mcId]
        );

        $this->view('master_class/show', [
            'pageTitle' => htmlspecialchars($masterClass['title']),
            'masterClass' => $masterClass,
            'tabs' => $tabs,
            'hasAccess' => $hasAccess,
            'accessType' => $accessType,
            'accessExpires' => $accessExpires,
            'isFavorite' => $isFavorite ?? false,
            'questions' => $questions,
            'user' => $this->user,
            'isAdmin' => $this->isAdmin
        ]);
    }

    public function buyForm(string $id): void
    {
        $this->requireAuth();

        $mcId = (int)$id;

        $masterClass = $this->db->fetch(
            "SELECT * FROM master_classes WHERE mc_id = :id",
            ['id' => $mcId]
        );

        if (!$masterClass) {
            http_response_code(404);
            $this->view('errors/404', [
                'pageTitle' => 'Мастер-класс не найден',
                'user' => $this->user
            ]);
            return;
        }

        // Check if already has access
        $existingAccess = $this->db->fetch(
            "SELECT oi.access_type, oi.access_expires
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.order_id
             WHERE oi.type = 'mc' 
               AND oi.item_id = :item_id 
               AND o.user_id = :user_id
               AND o.status IN ('new', 'processing', 'shipped', 'completed')",
            ['item_id' => $mcId, 'user_id' => $this->user['user_id']]
        );

        if ($existingAccess) {
            $this->setFlash('error', 'У вас уже есть доступ к этому мастер-классу');
            $this->redirect('/master-class/' . $mcId);
        }

        $this->view('master_class/buy', [
            'pageTitle' => 'Покупка мастер-класса',
            'masterClass' => $masterClass,
            'csrfToken' => $this->csrfToken(),
            'user' => $this->user
        ]);
    }

    public function processBuy(string $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Ошибка безопасности. Попробуйте снова.');
            $this->redirect('/');
        }

        $mcId = (int)$id;
        $accessType = $_POST['access_type'] ?? 'permanent';

        $masterClass = $this->db->fetch(
            "SELECT * FROM master_classes WHERE mc_id = :id",
            ['id' => $mcId]
        );

        if (!$masterClass) {
            $this->setFlash('error', 'Мастер-класс не найден');
            $this->redirect('/catalog/master-classes');
        }

        // Determine price and access expiry
        if ($accessType === 'subscription') {
            $price = $masterClass['price_subscribe'];
            $accessExpires = date('Y-m-d H:i:s', strtotime('+'. $masterClass['subscribe_days'] .' days'));
        } else {
            $price = $masterClass['price_buy'];
            $accessExpires = null;
        }

        try {
            $this->db->beginTransaction();

            // Create order
            $orderId = $this->db->insert('orders', [
                'user_id' => $this->user['user_id'],
                'total_price' => $price,
                'status' => 'completed',
                'delivery_address' => $this->user['address'] ?? '',
                'delivery_type' => 'digital',
                'order_date' => date('Y-m-d H:i:s')
            ]);

            // Create order item
            $this->db->insert('order_items', [
                'order_id' => $orderId,
                'type' => 'mc',
                'item_id' => $mcId,
                'quantity' => 1,
                'unit_price' => $price,
                'access_type' => $accessType === 'permanent' ? 'permanent' : 'subscription',
                'access_expires' => $accessExpires
            ]);

            $this->db->commit();

            $this->setFlash('success', 'Мастер-класс успешно приобретён!');
            $this->redirect('/my/master-classes');

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->setFlash('error', 'Произошла ошибка при покупке: ' . $e->getMessage());
            $this->redirect('/master-class/' . $mcId . '/buy');
        }
    }

    public function streamVideo(string $id): void
    {
        $this->requireAuth();

        $mcId = (int)$id;

        // Check access
        $hasAccess = $this->db->fetch(
            "SELECT oi.access_type, oi.access_expires, mc.title
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.order_id
             JOIN master_classes mc ON oi.item_id = mc.mc_id
             WHERE oi.type = 'mc' 
               AND oi.item_id = :item_id 
               AND o.user_id = :user_id
               AND o.status IN ('new', 'processing', 'shipped', 'completed')",
            ['item_id' => $mcId, 'user_id' => $this->user['user_id']]
        );

        if (!$hasAccess) {
            http_response_code(403);
            die('Доступ запрещён');
        }

        // Check subscription expiry
        if ($hasAccess['access_type'] === 'subscription' && $hasAccess['access_expires']) {
            if (strtotime($hasAccess['access_expires']) < time()) {
                http_response_code(403);
                die('Срок доступа истёк');
            }
        }

        // Get video content
        $videoContent = $this->db->fetch(
            "SELECT content_value FROM mc_content 
             WHERE mc_id = :id AND tab_name = 'Видео-урок' AND content_type = 'video'",
            ['id' => $mcId]
        );

        if (!$videoContent || !$videoContent['content_value']) {
            http_response_code(404);
            die('Видео не найдено');
        }

        $videoPath = $videoContent['content_value'];
        $fullPath = STORAGE_PATH . '/uploads/master_classes/videos/' . $videoPath;

        if (!file_exists($fullPath)) {
            http_response_code(404);
            die('Файл видео не найден');
        }

        // Stream video with protection
        header('Content-Type: video/mp4');
        header('Content-Disposition: inline; filename="' . basename($videoPath) . '"');
        header('X-Accel-Redirect: /protected/videos/' . $videoPath);
        header('Cache-Control: private, max-age=0, must-revalidate');
        
        // Add watermark info (email overlay can be added via JS on client side)
        header('X-Watermark: ' . $this->user['email']);

        readfile($fullPath);
        exit;
    }
}
