<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Catalog Controller - Handles product and master class catalogs
 */
class CatalogController extends Controller
{
    public function productsIndex(): void
    {
        // Get filters from query string
        $categoryId = $_GET['category'] ?? null;
        $typeId = $_GET['type'] ?? null;
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;
        $sortBy = $_GET['sort'] ?? 'newest';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        // Build WHERE clause
        $where = ['p.is_published = 1'];
        $params = [];

        if ($categoryId) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        if ($typeId) {
            $where[] = 'p.type_id = :type_id';
            $params['type_id'] = $typeId;
        }

        if ($minPrice !== null) {
            $where[] = 'p.price >= :min_price';
            $params['min_price'] = (float)$minPrice;
        }

        if ($maxPrice !== null) {
            $where[] = 'p.price <= :max_price';
            $params['max_price'] = (float)$maxPrice;
        }

        $whereClause = implode(' AND ', $where);

        // Build ORDER BY clause
        $orderBy = 'p.created_at DESC';
        if ($sortBy === 'price_asc') {
            $orderBy = 'p.price ASC';
        } elseif ($sortBy === 'price_desc') {
            $orderBy = 'p.price DESC';
        }

        // Get products
        $products = $this->db->fetchAll(
            "SELECT p.*, pi.image_url as main_image, c.name as category_name, t.name as type_name
             FROM products p
             LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = 1
             LEFT JOIN categories c ON p.category_id = c.category_id
             LEFT JOIN types t ON p.type_id = t.type_id
             WHERE {$whereClause}
             ORDER BY {$orderBy}
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get total count for pagination
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM products p WHERE {$whereClause}",
            $params
        )['count'];
        $totalPages = ceil($totalCount / $perPage);

        // Get categories and types for filters
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name");
        $types = $this->db->fetchAll("SELECT * FROM types ORDER BY name");

        $this->view('catalog/products', [
            'pageTitle' => 'Каталог товаров',
            'products' => $products,
            'categories' => $categories,
            'types' => $types,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => [
                'category' => $categoryId,
                'type' => $typeId,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sortBy
            ],
            'user' => $this->user,
            'isAdmin' => $this->isAdmin
        ]);
    }

    public function mcIndex(): void
    {
        // Get filters from query string
        $difficulty = $_GET['difficulty'] ?? null;
        $technique = $_GET['technique'] ?? null;
        $sortBy = $_GET['sort'] ?? 'newest';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        // Build WHERE clause
        $where = ['is_published = 1'];
        $params = [];

        if ($difficulty) {
            $where[] = 'difficulty = :difficulty';
            $params['difficulty'] = $difficulty;
        }

        if ($technique) {
            $where[] = 'technique = :technique';
            $params['technique'] = $technique;
        }

        $whereClause = implode(' AND ', $where);

        // Build ORDER BY clause
        $orderBy = 'created_at DESC';
        if ($sortBy === 'price_asc') {
            $orderBy = 'price_buy ASC';
        } elseif ($sortBy === 'price_desc') {
            $orderBy = 'price_buy DESC';
        }

        // Get master classes
        $masterClasses = $this->db->fetchAll(
            "SELECT * FROM master_classes
             WHERE {$whereClause}
             ORDER BY {$orderBy}
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get total count for pagination
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM master_classes WHERE {$whereClause}",
            $params
        )['count'];
        $totalPages = ceil($totalCount / $perPage);

        // Get unique techniques for filter
        $techniques = $this->db->fetchAll(
            "SELECT DISTINCT technique FROM master_classes WHERE is_published = 1 AND technique IS NOT NULL"
        );

        $this->view('catalog/master_classes', [
            'pageTitle' => 'Каталог мастер-классов',
            'masterClasses' => $masterClasses,
            'techniques' => $techniques,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => [
                'difficulty' => $difficulty,
                'technique' => $technique,
                'sort' => $sortBy
            ],
            'user' => $this->user,
            'isAdmin' => $this->isAdmin
        ]);
    }
}
