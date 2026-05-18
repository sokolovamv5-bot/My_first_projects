<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class AdminProductController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        
        $products = $this->db->fetchAll(
            "SELECT p.*, c.name as category_name, t.name as type_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.category_id
             LEFT JOIN types t ON p.type_id = t.type_id
             ORDER BY p.created_at DESC"
        );
        
        $this->layout('admin', '', [
            'pageTitle' => 'Товары',
            'contentView' => 'admin/products/index',
            'products' => $products,
            'user' => $this->user,
            'isAdmin' => true
        ]);
    }
    
    public function create(): void
    {
        $this->requireAdmin();
        
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name");
        $types = $this->db->fetchAll("SELECT * FROM types ORDER BY name");
        
        $this->layout('admin', '', [
            'pageTitle' => 'Создать товар',
            'contentView' => 'admin/products/form',
            'categories' => $categories,
            'types' => $types,
            'product' => null,
            'csrfToken' => $this->csrfToken(),
            'user' => $this->user,
            'isAdmin' => true
        ]);
    }
    
    public function store(): void
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
        }
        
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Ошибка безопасности');
            $this->redirect('/admin/products/create');
        }
        
        $data = [
            'name' => trim($_POST['name']),
            'description' => $_POST['description'],
            'price' => (float)$_POST['price'],
            'category_id' => (int)($_POST['category_id']) ?: null,
            'type_id' => (int)($_POST['type_id']) ?: null,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $productId = $this->db->insert('products', $data);
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $this->uploadImage($productId, $_FILES['image'], true);
        }
        
        $this->setFlash('success', 'Товар создан');
        $this->redirect('/admin/products');
    }
    
    public function edit(string $id): void
    {
        $this->requireAdmin();
        
        $product = $this->db->fetch("SELECT * FROM products WHERE product_id = :id", ['id' => (int)$id]);
        
        if (!$product) {
            $this->setFlash('error', 'Товар не найден');
            $this->redirect('/admin/products');
        }
        
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name");
        $types = $this->db->fetchAll("SELECT * FROM types ORDER BY name");
        $images = $this->db->fetchAll("SELECT * FROM product_images WHERE product_id = :id", ['id' => (int)$id]);
        
        $this->layout('admin', '', [
            'pageTitle' => 'Редактировать товар',
            'contentView' => 'admin/products/form',
            'categories' => $categories,
            'types' => $types,
            'product' => $product,
            'images' => $images,
            'csrfToken' => $this->csrfToken(),
            'user' => $this->user,
            'isAdmin' => true
        ]);
    }
    
    public function update(string $id): void
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
        }
        
        $data = [
            'name' => trim($_POST['name']),
            'description' => $_POST['description'],
            'price' => (float)$_POST['price'],
            'category_id' => (int)($_POST['category_id']) ?: null,
            'type_id' => (int)($_POST['type_id']) ?: null,
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'is_published' => isset($_POST['is_published']) ? 1 : 0
        ];
        
        $this->db->update('products', $data, 'product_id = :id', ['id' => (int)$id]);
        
        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $this->uploadImage((int)$id, $_FILES['image']);
        }
        
        $this->setFlash('success', 'Товар обновлён');
        $this->redirect('/admin/products');
    }
    
    public function delete(string $id): void
    {
        $this->requireAdmin();
        
        $this->db->delete('products', 'product_id = :id', ['id' => (int)$id]);
        
        $this->setFlash('success', 'Товар удалён');
        $this->redirect('/admin/products');
    }
    
    private function uploadImage(int $productId, array $file, bool $isMain = false): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return;
        }
        
        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = STORAGE_PATH . '/uploads/products/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->db->insert('product_images', [
                'product_id' => $productId,
                'image_url' => '/storage/uploads/products/' . $filename,
                'is_main' => $isMain ? 1 : 0,
                'sort_order' => 0
            ]);
        }
    }
}
