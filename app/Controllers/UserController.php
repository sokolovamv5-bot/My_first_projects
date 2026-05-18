<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * User Controller - Handles authentication and user profile
 */
class UserController extends Controller
{
    public function showLoginForm(): void
    {
        if ($this->user) {
            $this->redirect('/profile');
        }

        $this->view('user/login', [
            'pageTitle' => 'Вход',
            'csrfToken' => $this->csrfToken()
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Ошибка безопасности. Попробуйте снова.');
            $this->redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Введите email и пароль');
            $this->redirect('/login');
        }

        try {
            if (\App\Core\Auth::attempt($email, $password)) {
                $this->setFlash('success', 'Добро пожаловать!');
                $this->redirect('/profile');
            } else {
                $this->setFlash('error', 'Неверный email или пароль');
                $this->redirect('/login');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/login');
        }
    }

    public function showRegisterForm(): void
    {
        if ($this->user) {
            $this->redirect('/profile');
        }

        $this->view('user/register', [
            'pageTitle' => 'Регистрация',
            'csrfToken' => $this->csrfToken()
        ]);
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Ошибка безопасности. Попробуйте снова.');
            $this->redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $this->setFlash('error', 'Все поля обязательны для заполнения');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Введите корректный email');
            $this->redirect('/register');
        }

        if ($password !== $passwordConfirm) {
            $this->setFlash('error', 'Пароли не совпадают');
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            $this->setFlash('error', 'Пароль должен быть не менее 6 символов');
            $this->redirect('/register');
        }

        try {
            \App\Core\Auth::register($email, $password, $name);
            $this->setFlash('success', 'Регистрация успешна! Добро пожаловать!');
            $this->redirect('/profile');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/register');
        }
    }

    public function logout(): void
    {
        \App\Core\Auth::logout();
        $this->setFlash('success', 'Вы вышли из системы');
        $this->redirect('/');
    }

    public function profile(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateProfile();
            return;
        }

        $this->view('user/profile', [
            'pageTitle' => 'Личный кабинет',
            'user' => $this->user,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    private function updateProfile(): void
    {
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Ошибка безопасности. Попробуйте снова.');
            $this->redirect('/profile');
        }

        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $this->db->update(
            'users',
            ['phone' => $phone, 'address' => $address],
            'user_id = :id',
            ['id' => $this->user['user_id']]
        );

        // Refresh user data
        $this->user = $this->db->fetch(
            "SELECT * FROM users WHERE user_id = :id",
            ['id' => $this->user['user_id']]
        );

        $this->setFlash('success', 'Профиль обновлён');
        $this->redirect('/profile');
    }

    public function myMasterClasses(): void
    {
        $this->requireAuth();

        $masterClasses = $this->db->fetchAll(
            "SELECT mc.*, oi.access_type, oi.access_expires, o.order_date
             FROM master_classes mc
             JOIN order_items oi ON mc.mc_id = oi.item_id AND oi.type = 'mc'
             JOIN orders o ON oi.order_id = o.order_id
             WHERE o.user_id = :user_id
               AND o.status IN ('new', 'processing', 'shipped', 'completed')
             ORDER BY o.order_date DESC",
            ['user_id' => $this->user['user_id']]
        );

        $this->view('user/my_master_classes', [
            'pageTitle' => 'Мои мастер-классы',
            'masterClasses' => $masterClasses,
            'user' => $this->user
        ]);
    }

    public function myOrders(): void
    {
        $this->requireAuth();

        $filter = $_GET['filter'] ?? 'all';

        $where = ['o.user_id = :user_id'];
        $params = ['user_id' => $this->user['user_id']];

        if ($filter === 'products') {
            $where[] = "oi.type = 'product'";
        } elseif ($filter === 'mc') {
            $where[] = "oi.type = 'mc'";
        }

        $whereClause = implode(' AND ', $where);

        $orders = $this->db->fetchAll(
            "SELECT DISTINCT o.*, 
                    GROUP_CONCAT(DISTINCT oi.type) as item_types
             FROM orders o
             JOIN order_items oi ON o.order_id = oi.order_id
             WHERE {$whereClause}
             GROUP BY o.order_id
             ORDER BY o.order_date DESC",
            $params
        );

        $this->view('user/orders', [
            'pageTitle' => 'Мои заказы',
            'orders' => $orders,
            'currentFilter' => $filter,
            'user' => $this->user
        ]);
    }
}
