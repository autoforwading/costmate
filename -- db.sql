-- Create Database
-- CREATE DATABASE IF NOT EXISTS costmate CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE u928163871_costmate;

-- ========================
-- 1️⃣ Shops Table
-- ========================
CREATE TABLE `shops` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50),
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 2️⃣ Categories Table
-- ========================
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 3️⃣ Subcategories Table
-- ========================
CREATE TABLE `subcategories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_subcategories_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 4️⃣ Payment Methods Table
-- ========================
CREATE TABLE `payment_methods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 5️⃣ Purchases Table
-- ========================
CREATE TABLE `purchases` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) DEFAULT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `subcategory_id` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `quantity` INT(11) DEFAULT 1,
  `unit_price` DECIMAL(12,2) DEFAULT 0.00,
  `total_price` DECIMAL(12,2) NOT NULL,
  `payment_method_id` INT(11) DEFAULT NULL,
  `payment_desc` TEXT DEFAULT NULL,
  `purchase_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_shop_id` (`shop_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_subcategory_id` (`subcategory_id`),
  KEY `idx_payment_method_id` (`payment_method_id`),
  KEY `idx_purchase_date` (`purchase_date`),
  CONSTRAINT `fk_purchases_shops` FOREIGN KEY (`shop_id`) REFERENCES `shops`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchases_categories` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchases_subcategories` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchases_payment_methods` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================
-- 6️⃣ Optional: Default Payment Methods
-- ========================
INSERT INTO `payment_methods` (`name`) VALUES
('Cash'), ('Bkash'), ('Nagad'), ('Bank Transfer'), ('Chceck');

-- ========================
-- ✅ All Tables Ready
-- ========================
