<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Admin Dashboard Controller
 */
class AdminDashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        // Get statistics
        $stats = [
            'totalOrders' => $this->db->fetch("SELECT COUNT(*) as count FROM orders")['count'],
            'totalRevenue' => $this->db->fetch("SELECT SUM(total_price) as sum FROM orders WHERE status IN ('completed', 'shipped')")['sum'] ?? 0,
            'newOrders' => $this->db->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'new'")['count'],
            'totalProducts' => $this->db->fetch("SELECT COUNT(*) as count FROM products")['count'],
            'totalMasterClasses' => $this->db->fetch("SELECT COUNT(*) as count FROM master_classes WHERE is_published = 1")['count'],
            'unansweredQuestions' => $this->db->fetch("SELECT COUNT(*) as count FROM questions WHERE status = 'new'")['count']
        ];

        // Recent orders
        $recentOrders = $this->db->fetchAll(
            "SELECT o.*, u.name as user_name 
             FROM orders o 
             JOIN users u ON o.user_id = u.user_id 
             ORDER BY o.order_date DESC 
             LIMIT 5"
        );

        $this->layout('admin', '', [
            'pageTitle' => 'Админ-панель',
            'contentView' => 'admin/dashboard/index',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'user' => $this->user,
            'isAdmin' => true
        ]);
    }
}
