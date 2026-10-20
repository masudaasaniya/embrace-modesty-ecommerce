-- Admin Panel Database Setup for Embrace Modesty E-Commerce
-- Run this SQL in phpMyAdmin to create required tables

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `role` ENUM('super_admin', 'staff') DEFAULT 'staff',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin_users` (`username`, `password`, `email`, `role`, `status`) VALUES

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `image` VARCHAR(255),
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default categories
INSERT INTO `categories` (`name`, `slug`, `description`, `status`) VALUES
('Abayas', 'abayas', 'Modest Abayas and Jilbabs', 'active'),
('Hijabs', 'hijabs', 'Hijabs and Headscarves', 'active'),
('Dresses', 'dresses', 'Modest Dresses', 'active'),
('Accessories', 'accessories', 'Modest Fashion Accessories', 'active'),
('Caps', 'caps', 'Prayer Caps and Hijab Caps', 'active'),
('Pins', 'pins', 'Hijab Pins and Accessories', 'active');

-- 3. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `order_number` VARCHAR(50) DEFAULT NULL,
    `full_name` VARCHAR(120) DEFAULT NULL,
    `email` VARCHAR(120) DEFAULT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `address` TEXT,
    `city` VARCHAR(80) DEFAULT NULL,
    `pincode` VARCHAR(20) DEFAULT NULL,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(10,2) DEFAULT NULL,
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('Pending', 'Accepted', 'Processing', 'Shipped', 'Delivered', 'Rejected', 'Cancelled') DEFAULT 'Pending',
    `admin_response` TEXT,
    `product_id` INT(11) DEFAULT NULL,
    `quantity` INT(11) DEFAULT NULL,
    `price` DECIMAL(10,2) DEFAULT NULL,
    `order_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_orders_user_id` (`user_id`),
    KEY `idx_orders_status` (`status`),
    KEY `idx_orders_order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Order Customers Table
CREATE TABLE IF NOT EXISTS `order_customers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `address` TEXT NOT NULL,
    `city` VARCHAR(80) NOT NULL,
    `pincode` VARCHAR(20) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_customer_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` INT(11) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Banners Table
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `subtitle` VARCHAR(255),
    `image` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255),
    `link_text` VARCHAR(50),
    `position` INT(11) DEFAULT 1,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample banners
INSERT INTO `banners` (`title`, `subtitle`, `image`, `link`, `link_text`, `position`, `status`) VALUES
('New Collection', 'Embrace Modesty with Style', 'banner1.jpg', 'shoppage2.php', 'Shop Now', 1, 'active'),
('Summer Sale', 'Up to 50% Off', 'banner2.jpg', 'shoppage2.php', 'Shop Now', 2, 'active');

-- 7. Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255),
    `discount_type` ENUM('percentage', 'fixed') NOT NULL,
    `discount_value` DECIMAL(10,2) NOT NULL,
    `min_order_amount` DECIMAL(10,2) DEFAULT 0,
    `max_uses` INT(11) DEFAULT NULL,
    `used_count` INT(11) DEFAULT 0,
    `expiry_date` DATE NOT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample coupon
INSERT INTO `coupons` (`code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `expiry_date`, `status`) VALUES
('WELCOME10', 'Welcome discount for new customers', 'percentage', 10, 500, '2025-12-31', 'active');

-- 8. Content Pages Table
CREATE TABLE IF NOT EXISTS `content_pages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `page_key` VARCHAR(50) NOT NULL UNIQUE,
    `page_title` VARCHAR(255) NOT NULL,
    `content` TEXT,
    `meta_description` VARCHAR(255),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default content
INSERT INTO `content_pages` (`page_key`, `page_title`, `content`, `meta_description`) VALUES
('about', 'About Us', '<h2>Welcome to Embrace Modesty</h2><p>We are dedicated to providing modest fashion for modern women.</p>', 'About Embrace Modesty - Modest Clothing Store'),
('contact', 'Contact Us', '<h2>Contact Us</h2><p>Email: info@embracemodesty.com</p><p>Phone: +91 98765 43210</p>', 'Contact Embrace Modesty'),
('faq', 'Frequently Asked Questions', '<h2>FAQ</h2><p><strong>Q: What is your return policy?</strong><br>A: We offer 30-day returns on all unworn items.</p>', 'Frequently Asked Questions'),
('privacy', 'Privacy Policy', '<h2>Privacy Policy</h2><p>We value your privacy and protect your personal information.</p>', 'Privacy Policy'),
('terms', 'Terms & Conditions', '<h2>Terms & Conditions</h2><p>By using our website, you agree to our terms and conditions.</p>', 'Terms and Conditions');

-- 9. Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(50) NOT NULL UNIQUE,
    `setting_value` TEXT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Embrace Modesty'),
('site_logo', 'logo.png'),
('contact_email', 'info@embracemodesty.com'),
('contact_phone', '+91 98765 43210'),
('contact_address', '123 Modest Street, Fashion City, India'),
('facebook_url', 'https://facebook.com/embracemodesty'),
('instagram_url', 'https://instagram.com/embracemodesty'),
('twitter_url', ''),
('currency', 'INR'),
('currency_symbol', 'â‚¹'),
('tax_rate', '0'),
('shipping_cost', '0'),
('free_shipping_threshold', '500'),
('low_stock_threshold', '10'),
('items_per_page', '20');

-- 10. Customers Table (from existing user table, enhanced)
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) DEFAULT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50),
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20),
    `address` TEXT,
    `city` VARCHAR(50),
    `state` VARCHAR(50),
    `pincode` VARCHAR(10),
    `status` ENUM('active', 'blocked') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

