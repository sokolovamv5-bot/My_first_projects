-- Database Schema for "Дом сказочных узоров"
-- Version 2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `house_of_patterns` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `house_of_patterns`;

-- Table: users
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50),
    `address` TEXT,
    `is_admin` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: categories
CREATE TABLE `categories` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: types (product types)
CREATE TABLE `types` (
    `type_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: products
CREATE TABLE `products` (
    `product_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `category_id` INT,
    `type_id` INT,
    `stock_quantity` INT DEFAULT 0,
    `is_published` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE SET NULL,
    FOREIGN KEY (`type_id`) REFERENCES `types`(`type_id`) ON DELETE SET NULL,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_type` (`type_id`),
    INDEX `idx_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: product_images
CREATE TABLE `product_images` (
    `image_id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `image_url` VARCHAR(512) NOT NULL,
    `is_main` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
    INDEX `idx_product` (`product_id`),
    INDEX `idx_main` (`is_main`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: master_classes
CREATE TABLE `master_classes` (
    `mc_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `difficulty` VARCHAR(50) DEFAULT 'Новичок',
    `technique` VARCHAR(100),
    `price_buy` DECIMAL(10,2) NOT NULL,
    `price_subscribe` DECIMAL(10,2) NOT NULL,
    `subscribe_days` INT DEFAULT 30,
    `is_published` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_published` (`is_published`),
    INDEX `idx_difficulty` (`difficulty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: mc_content (master class content tabs)
CREATE TABLE `mc_content` (
    `content_id` INT AUTO_INCREMENT PRIMARY KEY,
    `mc_id` INT NOT NULL,
    `tab_name` VARCHAR(50) NOT NULL,
    `content_type` VARCHAR(20) NOT NULL COMMENT 'text, video, image, gallery',
    `content_value` TEXT,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`mc_id`) REFERENCES `master_classes`(`mc_id`) ON DELETE CASCADE,
    INDEX `idx_mc` (`mc_id`),
    INDEX `idx_tab` (`tab_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: orders
CREATE TABLE `orders` (
    `order_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `order_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `total_price` DECIMAL(10,2) NOT NULL,
    `status` VARCHAR(50) DEFAULT 'new' COMMENT 'new, processing, shipped, completed, cancelled',
    `delivery_address` TEXT,
    `delivery_type` VARCHAR(50),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_date` (`order_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: order_items
CREATE TABLE `order_items` (
    `order_item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `type` VARCHAR(20) NOT NULL COMMENT 'product or mc',
    `item_id` INT NOT NULL,
    `quantity` INT DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `access_type` VARCHAR(20) NULL COMMENT 'permanent or subscription (for MC)',
    `access_expires` DATETIME NULL COMMENT 'subscription expiry date',
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
    INDEX `idx_order` (`order_id`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: favorites
CREATE TABLE `favorites` (
    `favorite_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` VARCHAR(20) NOT NULL COMMENT 'product or mc',
    `item_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_favorite` (`user_id`, `type`, `item_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: questions
CREATE TABLE `questions` (
    `question_id` INT AUTO_INCREMENT PRIMARY KEY,
    `mc_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `answer_text` TEXT NULL,
    `status` VARCHAR(20) DEFAULT 'new' COMMENT 'new, answered',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `answered_at` DATETIME NULL,
    FOREIGN KEY (`mc_id`) REFERENCES `master_classes`(`mc_id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_mc` (`mc_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cart (session-based, but can be persisted)
CREATE TABLE `cart` (
    `cart_id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(128) NOT NULL,
    `user_id` INT NULL,
    `type` VARCHAR(20) NOT NULL COMMENT 'product or mc',
    `item_id` INT NOT NULL,
    `quantity` INT DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_session` (`session_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: edit_mode (for admin content editing)
CREATE TABLE `edit_mode` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `page_key` VARCHAR(100) UNIQUE NOT NULL,
    `element_key` VARCHAR(100) NOT NULL,
    `content` TEXT,
    `is_visible` TINYINT(1) DEFAULT 1,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_element` (`page_key`, `element_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`email`, `password_hash`, `name`, `is_admin`) VALUES
('admin@housepatterns.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор', 1);

-- Insert default categories
INSERT INTO `categories` (`name`) VALUES
('Вязание'),
('Шитьё'),
('Вышивка'),
('Декор');

-- Insert default types
INSERT INTO `types` (`name`) VALUES
('Игрушки'),
('Аксессуары'),
('Сумки'),
('Одежда'),
('Предметы интерьера');
