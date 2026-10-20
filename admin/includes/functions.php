<?php
/**
 * Admin Panel Helper Functions
 * Embrace Modesty E-Commerce
 */

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// Get admin data
function getAdminData($admin_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, role FROM admin_users WHERE id = ?");
    if (!$stmt) {
        return array(
            'id' => isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0,
            'username' => isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin',
            'email' => '',
            'role' => isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'admin'
        );
    }
    $stmt->bind_param("i", $admin_id);
    if (!$stmt->execute()) {
        $stmt->close();
        return array(
            'id' => isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0,
            'username' => isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin',
            'email' => '',
            'role' => isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'admin'
        );
    }
    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return array(
            'id' => isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0,
            'username' => isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin',
            'email' => '',
            'role' => isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'admin'
        );
    }
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row) {
        return $row;
    }
    return array(
        'id' => isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0,
        'username' => isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin',
        'email' => '',
        'role' => isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'admin'
    );
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Format price
function formatPrice($price) {
    return 'Rs ' . number_format($price, 2);
}

// Format date
function formatDate($date) {
    return date(DATE_FORMAT, strtotime($date));
}

// Format datetime
function formatDateTime($date) {
    return date(DATETIME_FORMAT, strtotime($date));
}

// Get setting value
function getSetting($key) {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $queryResult = $stmt->get_result();
    if (!$queryResult) {
        $stmt->close();
        return '';
    }
    $result = $queryResult->fetch_assoc();
    $stmt->close();
    return $result ? $result['setting_value'] : '';
}

// Update setting
function updateSetting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ss", $value, $key);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// File upload handler
function uploadFile($file, $targetDir, $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp')) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedTypes)) {
            $newFileName = uniqid() . '.' . $fileExt;
            $targetPath = $targetDir . $newFileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                return $newFileName;
            }
        }
    }
    return null;
}

// Delete file
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Generate slug
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// Get pagination
function getPagination($totalItems, $currentPage, $itemsPerPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $start = ($currentPage - 1) * $itemsPerPage;

    return array(
        'total' => $totalItems,
        'per_page' => $itemsPerPage,
        'current' => $currentPage,
        'total_pages' => $totalPages,
        'start' => $start
    );
}

// Show alert message
function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = array(
        'message' => $message,
        'type' => $type
    );
}

// Display alert
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $class = $alert['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">'
            . $alert['message'] .
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' .
            '</div>';
        unset($_SESSION['alert']);
    }
}

// Get count from table
function getCount($table, $condition = '') {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM $table";
    if ($condition) {
        $sql .= " WHERE $condition";
    }
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Get sum from table
function getSum($table, $field, $condition = '') {
    global $conn;
    $sql = "SELECT SUM($field) as total FROM $table";
    if ($condition) {
        $sql .= " WHERE $condition";
    }
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return isset($row['total']) ? $row['total'] : 0;
}

// Check if table exists
function tableExists($table) {
    global $conn;
    static $cache = array();

    if (isset($cache[$table])) {
        return $cache[$table];
    }

    $safeTable = $conn->real_escape_string($table);
    $result = $conn->query("SHOW TABLES LIKE '" . $safeTable . "'");
    $exists = ($result && $result->num_rows > 0);

    $cache[$table] = $exists;
    return $exists;
}

// Check if a column exists in a table
function tableHasColumn($table, $column) {
    global $conn;
    static $cache = array();

    $cacheKey = $table . '.' . $column;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $safeTable = str_replace('`', '``', $table);
    $safeColumn = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM `" . $safeTable . "` LIKE '" . $safeColumn . "'");
    $exists = ($result && $result->num_rows > 0);

    $cache[$cacheKey] = $exists;
    return $exists;
}

// Build reliable order date expression across schema variants
function getOrderDateExpression($alias = '') {
    $prefix = $alias !== '' ? $alias . '.' : '';
    $hasOrderDate = tableHasColumn('orders', 'order_date');
    $hasCreatedAt = tableHasColumn('orders', 'created_at');

    if ($hasOrderDate && $hasCreatedAt) {
        return "COALESCE(" . $prefix . "order_date, " . $prefix . "created_at)";
    }
    if ($hasOrderDate) {
        return $prefix . "order_date";
    }
    if ($hasCreatedAt) {
        return $prefix . "created_at";
    }

    return "NOW()";
}

// Build reliable order amount expression across schema variants
function getOrderAmountExpression($alias = '') {
    $prefix = $alias !== '' ? $alias . '.' : '';
    $hasTotal = tableHasColumn('orders', 'total');
    $hasTotalPrice = tableHasColumn('orders', 'total_price');
    $idRef = $alias !== '' ? $alias . ".id" : "orders.id";
    $hasOrderItemsTotal = tableExists('order_items')
        && tableHasColumn('order_items', 'order_id')
        && tableHasColumn('order_items', 'price')
        && tableHasColumn('order_items', 'quantity');
    $itemTotalExpr = $hasOrderItemsTotal
        ? "COALESCE((SELECT SUM(oi.price * oi.quantity) FROM order_items oi WHERE oi.order_id = " . $idRef . "), 0)"
        : "0";

    if ($hasTotal && $hasTotalPrice) {
        return "(CASE
                    WHEN " . $prefix . "total IS NOT NULL AND " . $prefix . "total > 0 THEN " . $prefix . "total
                    WHEN " . $prefix . "total_price IS NOT NULL AND " . $prefix . "total_price > 0 THEN " . $prefix . "total_price
                    ELSE " . $itemTotalExpr . "
                END)";
    }
    if ($hasTotal) {
        return "(CASE
                    WHEN " . $prefix . "total IS NOT NULL AND " . $prefix . "total > 0 THEN " . $prefix . "total
                    ELSE " . $itemTotalExpr . "
                END)";
    }
    if ($hasTotalPrice) {
        return "(CASE
                    WHEN " . $prefix . "total_price IS NOT NULL AND " . $prefix . "total_price > 0 THEN " . $prefix . "total_price
                    ELSE " . $itemTotalExpr . "
                END)";
    }

    return "0";
}

// Get low stock products
function getLowStockProducts() {
    global $conn;
    $threshold = getSetting('low_stock_threshold');
    if (!is_numeric($threshold) || intval($threshold) <= 0) {
        $threshold = 5;
    } else {
        $threshold = intval($threshold);
    }

    $stockColumn = 'quantity';
    if (tableHasColumn('products', 'quantity')) {
        $stockColumn = 'quantity';
    } elseif (tableHasColumn('products', 'stock')) {
        $stockColumn = 'stock';
    } elseif (tableHasColumn('products', 'qty')) {
        $stockColumn = 'qty';
    }
    $categorySelect = tableHasColumn('products', 'category') ? 'category' : "'' AS category";

    $sql = "SELECT id, name, " . $categorySelect . ", " . $stockColumn . " AS quantity
            FROM products
            WHERE COALESCE(" . $stockColumn . ", 0) <= ?
            ORDER BY " . $stockColumn . " ASC, id DESC
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Dashboard low stock query failed: ' . $conn->error);
        return array();
    }
    $stmt->bind_param("i", $threshold);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return array();
    }
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Get recent orders
function getRecentOrders($limit = 5) {
    global $conn;
    $safeLimit = intval($limit);
    if ($safeLimit <= 0) {
        $safeLimit = 5;
    }

    $dateExpr = getOrderDateExpression('o');
    $amountExpr = getOrderAmountExpression('o');
    $hasUserTable = tableExists('user') && tableHasColumn('orders', 'user_id') && tableHasColumn('user', 'id');
    $hasFullName = tableHasColumn('orders', 'full_name');
    $fullNameExpr = $hasFullName ? "NULLIF(o.full_name, '')" : "NULL";

    if ($hasUserTable) {
        $sql = "SELECT o.*,
                       " . $amountExpr . " AS total,
                       " . $dateExpr . " AS order_datetime,
                       u.username,
                       u.email,
                       COALESCE(NULLIF(u.username, ''), " . $fullNameExpr . ", 'Guest') AS customer_name
                FROM orders o
                LEFT JOIN user u ON o.user_id = u.id
                ORDER BY " . $dateExpr . " DESC
                LIMIT ?";
    } else {
        $sql = "SELECT o.*,
                       " . $amountExpr . " AS total,
                       " . $dateExpr . " AS order_datetime,
                       NULL AS username,
                       NULL AS email,
                       COALESCE(" . $fullNameExpr . ", 'Guest') AS customer_name
                FROM orders o
                ORDER BY " . $dateExpr . " DESC
                LIMIT ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param("i", $safeLimit);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return array();
    }
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Get monthly sales chart data for dashboard (last 30 days)
function getMonthlySales() {
    global $conn;

    $dateExpr = getOrderDateExpression();
    $amountExpr = getOrderAmountExpression();
    $hasStatus = tableHasColumn('orders', 'status');
    $statusFilter = $hasStatus ? " AND (status IS NULL OR status NOT IN ('Cancelled', 'Rejected'))" : "";

    $sql = "SELECT DATE(" . $dateExpr . ") AS sale_day,
                   COALESCE(SUM(" . $amountExpr . "), 0) AS sales
            FROM orders
            WHERE DATE(" . $dateExpr . ") >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)" . $statusFilter . "
            GROUP BY DATE(" . $dateExpr . ")
            ORDER BY sale_day ASC";

    $result = $conn->query($sql);
    if (!$result) {
        return array('labels' => array(), 'data' => array());
    }

    $dbData = array();
    while ($row = $result->fetch_assoc()) {
        $dbData[$row['sale_day']] = floatval($row['sales']);
    }

    // Build fixed last-30-days series so chart always has predictable labels/data.
    $labels = array();
    $data = array();
    for ($i = 29; $i >= 0; $i--) {
        $dayKey = date('Y-m-d', strtotime("-" . $i . " days"));
        $labels[] = date('d M', strtotime($dayKey));
        $data[] = isset($dbData[$dayKey]) ? max(0, (float)$dbData[$dayKey]) : 0;
    }

    return array(
        'labels' => $labels,
        'data' => $data
    );
}

// Get today's order count and revenue
function getTodayOrderStats() {
    global $conn;

    $dateExpr = getOrderDateExpression();
    $amountExpr = getOrderAmountExpression();
    $hasStatus = tableHasColumn('orders', 'status');
    $statusFilter = $hasStatus ? " AND (status IS NULL OR status NOT IN ('Cancelled', 'Rejected'))" : "";

    $sql = "SELECT COUNT(*) AS count,
                   COALESCE(SUM(" . $amountExpr . "), 0) AS total
            FROM orders
            WHERE DATE(" . $dateExpr . ") = CURDATE()" . $statusFilter;

    $result = $conn->query($sql);
    if (!$result) {
        error_log('Dashboard today stats query failed: ' . $conn->error);
        return array('count' => 0, 'total' => 0);
    }

    $row = $result->fetch_assoc();
    if (!$row) {
        return array('count' => 0, 'total' => 0);
    }

    return array(
        'count' => isset($row['count']) ? (int)$row['count'] : 0,
        'total' => isset($row['total']) ? (float)$row['total'] : 0
    );
}


// Generate invoice number
function generateInvoiceNumber($orderId) {
    return 'INV-' . date('Y') . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
}

// Check coupon validity
function isCouponValid($code) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE()");
    if (!$stmt) {
        return array('valid' => false, 'message' => 'Coupon system unavailable');
    }
    $stmt->bind_param("s", $code);
    if (!$stmt->execute()) {
        $stmt->close();
        return array('valid' => false, 'message' => 'Coupon validation failed');
    }
    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return array('valid' => false, 'message' => 'Coupon validation failed');
    }

    if ($result->num_rows === 0) {
        $stmt->close();
        return array('valid' => false, 'message' => 'Coupon not found or expired');
    }

    $coupon = $result->fetch_assoc();
    $stmt->close();

    if ($coupon['max_uses'] && $coupon['used_count'] >= $coupon['max_uses']) {
        return array('valid' => false, 'message' => 'Coupon usage limit reached');
    }

    return array('valid' => true, 'coupon' => $coupon);
}

// Apply coupon discount
function applyCoupon($code, $orderTotal) {
    $couponCheck = isCouponValid($code);

    if (!$couponCheck['valid']) {
        return array('success' => false, 'message' => $couponCheck['message']);
    }

    $coupon = $couponCheck['coupon'];

    if ($orderTotal < $coupon['min_order_amount']) {
        return array('success' => false, 'message' => 'Minimum order amount required: ' . $coupon['min_order_amount']);
    }

    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($orderTotal * $coupon['discount_value']) / 100;
    } else {
        $discount = $coupon['discount_value'];
    }

    return array(
        'success' => true,
        'discount' => $discount,
        'coupon_id' => $coupon['id'],
        'code' => $code
    );
}

// CSRF Token
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

// Export to CSV
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Header
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));

        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}
