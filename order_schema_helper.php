<?php
/**
 * Order schema helper for mixed legacy/new installs.
 */

function ensureOrderManagementSchema($con) {
    if (!$con) {
        return false;
    }

    $createOrders = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(50) DEFAULT NULL,
        full_name VARCHAR(120) DEFAULT NULL,
        email VARCHAR(120) DEFAULT NULL,
        phone VARCHAR(30) DEFAULT NULL,
        address TEXT,
        city VARCHAR(80) DEFAULT NULL,
        pincode VARCHAR(20) DEFAULT NULL,
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        total_price DECIMAL(10,2) DEFAULT NULL,
        payment_method VARCHAR(50) DEFAULT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Pending',
        admin_response TEXT,
        product_id INT DEFAULT NULL,
        quantity INT DEFAULT NULL,
        price DECIMAL(10,2) DEFAULT NULL,
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_user_id (user_id),
        KEY idx_status (status),
        KEY idx_order_number (order_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $con->query($createOrders);

    $createOrderCustomers = "CREATE TABLE IF NOT EXISTS order_customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        full_name VARCHAR(120) NOT NULL,
        email VARCHAR(120) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(80) NOT NULL,
        pincode VARCHAR(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_order_customer_order_id (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $con->query($createOrderCustomers);

    $createOrderItems = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        quantity INT NOT NULL DEFAULT 1,
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_order_id (order_id),
        KEY idx_product_id (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $con->query($createOrderItems);

    ensureOrderColumn($con, 'order_number', "ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) DEFAULT NULL AFTER user_id");
    ensureOrderColumn($con, 'full_name', "ALTER TABLE orders ADD COLUMN full_name VARCHAR(120) DEFAULT NULL AFTER order_number");
    ensureOrderColumn($con, 'email', "ALTER TABLE orders ADD COLUMN email VARCHAR(120) DEFAULT NULL AFTER full_name");
    ensureOrderColumn($con, 'phone', "ALTER TABLE orders ADD COLUMN phone VARCHAR(30) DEFAULT NULL AFTER email");
    ensureOrderColumn($con, 'address', "ALTER TABLE orders ADD COLUMN address TEXT AFTER phone");
    ensureOrderColumn($con, 'city', "ALTER TABLE orders ADD COLUMN city VARCHAR(80) DEFAULT NULL AFTER address");
    ensureOrderColumn($con, 'pincode', "ALTER TABLE orders ADD COLUMN pincode VARCHAR(20) DEFAULT NULL AFTER city");
    ensureOrderColumn($con, 'total', "ALTER TABLE orders ADD COLUMN total DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER order_number");
    ensureOrderColumn($con, 'total_price', "ALTER TABLE orders ADD COLUMN total_price DECIMAL(10,2) DEFAULT NULL AFTER total");
    ensureOrderColumn($con, 'status', "ALTER TABLE orders ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Pending' AFTER payment_method");
    ensureOrderColumn($con, 'admin_response', "ALTER TABLE orders ADD COLUMN admin_response TEXT AFTER status");
    ensureOrderColumn($con, 'payment_method', "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER total_price");
    ensureOrderColumn($con, 'order_date', "ALTER TABLE orders ADD COLUMN order_date DATETIME DEFAULT CURRENT_TIMESTAMP AFTER price");
    ensureOrderColumn($con, 'created_at', "ALTER TABLE orders ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER order_date");
    ensureOrderColumn($con, 'updated_at', "ALTER TABLE orders ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    ensureOrderColumn($con, 'product_id', "ALTER TABLE orders ADD COLUMN product_id INT DEFAULT NULL AFTER admin_response");
    ensureOrderColumn($con, 'quantity', "ALTER TABLE orders ADD COLUMN quantity INT DEFAULT NULL AFTER product_id");
    ensureOrderColumn($con, 'price', "ALTER TABLE orders ADD COLUMN price DECIMAL(10,2) DEFAULT NULL AFTER quantity");
    $con->query("ALTER TABLE orders MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Pending'");

    ensureOrderItemColumn($con, 'total', "ALTER TABLE order_items ADD COLUMN total DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER quantity");
    ensureOrderItemColumn($con, 'created_at', "ALTER TABLE order_items ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER total");

    return true;
}

function ensureOrderColumn($con, $columnName, $alterSql) {
    $safeColumn = mysqli_real_escape_string($con, $columnName);
    $check = $con->query("SHOW COLUMNS FROM orders LIKE '{$safeColumn}'");
    if ($check && $check->num_rows === 0) {
        $con->query($alterSql);
    }
}

function ensureOrderItemColumn($con, $columnName, $alterSql) {
    $safeColumn = mysqli_real_escape_string($con, $columnName);
    $check = $con->query("SHOW COLUMNS FROM order_items LIKE '{$safeColumn}'");
    if ($check && $check->num_rows === 0) {
        $con->query($alterSql);
    }
}
