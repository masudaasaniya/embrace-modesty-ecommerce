<?php
ob_start(); // Fix: Output buffering for clean header redirect
session_start();
include("connect.php");
require_once("order_schema_helper.php");

function logOrderError($con, $step, $sql = '', $params = '') {
    $error = mysqli_error($con);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest';
    $checkout_items_count = isset($_SESSION['checkout_items']) ? count($_SESSION['checkout_items']) : 0;
    $msg = "ORDER_ERROR " . $step . ": " . $error . " | SQL: " . $sql . " | Params: " . $params . " | User: " . $user_id . " | CartItems: " . $checkout_items_count;
    error_log($msg);
    return $error;
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

// Ensure order schema exists
ensureOrderManagementSchema($con);

// ============================================
// VALIDATION
// ============================================

// Sanitize and validate inputs
$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Cash on Delivery';
$paymentStatus = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : 'Pending';

$paymentStatusField = ($paymentMethod === 'UPI' && $paymentStatus === 'Success') ? 'Paid' : 'Pending';

// Validate required fields
$errors = array();

if (empty($fullName)) {
    $errors[] = "Full name is required";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email is required";
}

if (empty($phone) || !preg_match('/^\d{10}$/', $phone)) {
    $errors[] = "Valid 10-digit phone number is required";
}

if (empty($address)) {
    $errors[] = "Address is required";
}

if (empty($city)) {
    $errors[] = "City is required";
}

if(!preg_match('/^[0-9]{6}$/', $pincode)){
    header("Location: checkout.php?checkout_error=Invalid pincode");
    exit;
}

// If UPI is selected, payment must be completed
if ($paymentMethod === 'UPI' && $paymentStatus !== 'Success') {
    $errors[] = "Please complete the UPI payment first";
}

// Get coupon data
$couponCode = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
$couponDiscount = isset($_POST['coupon_discount']) ? floatval($_POST['coupon_discount']) : 0;

// Get checkout items from SESSION as checkout.php sets it (fallback to DB cart)
$checkoutItems = isset($_SESSION['checkout_items']) && is_array($_SESSION['checkout_items']) ? $_SESSION['checkout_items'] : array();
$checkoutSubtotal = isset($_SESSION['checkout_total']) ? floatval($_SESSION['checkout_total']) : 0;

if (empty($checkoutItems)) {
    // Fallback to DB cart
    $userId = intval($_SESSION['user_id']);
    $stmt = $con->prepare("SELECT product_id, name, price, quantity, image FROM cart WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($checkoutItems, $row);
            $checkoutSubtotal += floatval($row['price']) * intval($row['quantity']);
        }
        $stmt->close();
    }
}

if (empty($checkoutItems)) {
    error_log("PROCESS_ORDER: Empty checkout_items session");
    header("Location: cart.php?checkout_error=no_items_selected");
    exit;
}

$checkoutTotal = $checkoutSubtotal - $couponDiscount;
if ($checkoutTotal < 0) $checkoutTotal = 0;

// If there are errors, redirect back
if (!empty($errors) || $checkoutTotal <= 0) {
    $errorMsg = implode(", ", $errors);
    header("Location: checkout.php?checkout_error=" . urlencode($errorMsg));
    exit;
}

// ============================================
// DUPLICATE ORDER PREVENTION
// ============================================

$userId = intval($_SESSION['user_id']);
$duplicateCheck = $con->prepare("
    SELECT id FROM orders 
    WHERE user_id = ? AND phone = ? AND email = ? AND total = ? 
    AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    LIMIT 1
");
if ($duplicateCheck) {
    $duplicateCheck->bind_param("isss", $userId, $phone, $email, $checkoutTotal);
    $duplicateCheck->execute();
    $dupResult = $duplicateCheck->get_result();
    if ($dupResult && $dupResult->num_rows > 0) {
        $row = $dupResult->fetch_assoc();
        $dupOrderId = isset($row['id']) ? $row['id'] : 0;
        mysqli_autocommit($con, true);
        error_log("DUPLICATE_ORDER_PREVENTED: User $userId, existing order $dupOrderId");
        header("Location: invoice.php?order_id=" . $dupOrderId);
        exit;
    }
    $duplicateCheck->close();
}

// ============================================
// INSERT ORDER INTO DATABASE
// ============================================

$orderId = 0;
$hasError = false;
$orderNumber = '';

// Start transaction
mysqli_autocommit($con, false);

$orderStatus = ($paymentMethod === 'UPI' && $paymentStatus === 'Success') ? 'Confirmed' : 'Pending';

$insertSql = "INSERT INTO orders (user_id, full_name, email, phone, address, city, pincode, total_price, payment_method, payment_status, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$insertOrder = $con->prepare($insertSql);
if (!$insertOrder) {
    logOrderError($con, 'PREPARE_FAILED', $insertSql);
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=Database prepare failed");
    exit;
}

if (!$insertOrder->bind_param("isssssdssss", $userId, $fullName, $email, $phone, $address, $city, $pincode, $checkoutTotal, $paymentMethod, $paymentStatusField, $orderStatus)) {
    logOrderError($con, 'BIND_FAILED');
    $insertOrder->close();
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=Database bind failed");
    exit;
}

if (!$insertOrder->execute()) {
    logOrderError($con, 'EXECUTE_FAILED', $insertSql);
    $insertOrder->close();
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=Order insert failed");
    exit;
}

$orderId = $insertOrder->insert_id;
$insertOrder->close();

if ($orderId == 0) {
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=No order created");
    exit;
}

// Update order_number
$orderNumber = 'EM-' . date('Ymd') . '-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
$updateStmt = $con->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
if ($updateStmt) {
    $updateStmt->bind_param("si", $orderNumber, $orderId);
    $updateStmt->execute();
    $updateStmt->close();
}

// Insert into order_customers table (OPTIONAL - skip if table missing)
$tableCheck = $con->query("SHOW TABLES LIKE 'order_customers'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $insertCustomer = $con->prepare("INSERT INTO order_customers (order_id, full_name, email, phone, address, city, pincode, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($insertCustomer) {
        $insertCustomer->bind_param("issssss", $orderId, $fullName, $email, $phone, $address, $city, $pincode);
        if (!$insertCustomer->execute()) {
            logOrderError($con, 'CUSTOMERS_EXEC_FAILED');
            // Not fatal
        }
        $insertCustomer->close();
    }
}

// Insert order items - SYNTAX FIXED VERSION
$hasProductImage = false;
$productImageCheck = $con->query("SHOW COLUMNS FROM order_items LIKE 'product_image'");
if ($productImageCheck && $productImageCheck->num_rows > 0) {
    $hasProductImage = true;
}

$hasOrderItemsCreatedAt = false;
$orderItemsCreatedAtCheck = $con->query("SHOW COLUMNS FROM order_items LIKE 'created_at'");
if ($orderItemsCreatedAtCheck && $orderItemsCreatedAtCheck->num_rows > 0) {
    $hasOrderItemsCreatedAt = true;
}

if ($hasProductImage && $hasOrderItemsCreatedAt) {
    $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
} elseif ($hasProductImage) {
    $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?, ?)"); 
} elseif ($hasOrderItemsCreatedAt) {
    $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
} else {
    $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
}

if ($insertItem) {
    foreach ($checkoutItems as $item) {
        $productId = isset($item['product_id']) ? intval($item['product_id']) : 0;
        $productName = isset($item['name']) ? $item['name'] : (isset($item['product_name']) ? $item['product_name']) : '';
        $price = isset($item['price']) ? floatval($item['price']) : 0;
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
        $productImage = isset($item['image']) && !empty($item['image']) ? $item['image'] : '';

        // Fallback: Get from database
        if (empty($productImage) && $productId > 0) {
            $productQuery = $con->query("SELECT image FROM products WHERE id = " . $productId);
            if ($productQuery && $productQuery->num_rows > 0) {
                $productRow = $productQuery->fetch_assoc();
                $productImage = isset($productRow['image']) ? $productRow['image'] : '';
            }
        }

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $lineTotal = $price * $quantity;

        if ($hasProductImage) {
            $insertItem->bind_param("iissdid", $orderId, $productId, $productName, $productImage, $price, $quantity, $lineTotal);
        } else {
            $insertItem->bind_param("iisdid", $orderId, $productId, $productName, $price, $quantity, $lineTotal);
        }

        if (!$insertItem->execute()) {
            $hasError = true;
            break;
        }
    }
    $insertItem->close();
}

// ============================================
// STOCK REDUCTION LOGIC
// ============================================

if (!$hasError && $orderId > 0) {
    foreach ($checkoutItems as $item) {
        $productId = isset($item['product_id']) ? intval($item['product_id']) : 0;
        $orderQuantity = isset($item['quantity']) ? intval($item['quantity']) : 0;

        if ($productId <= 0 || $orderQuantity <= 0) {
            continue;
        }

        // Check current stock
        $stockStmt = $con->prepare("SELECT quantity FROM products WHERE id = ?");
        $stockStmt->bind_param("i", $productId);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();

        if ($stockRow = $stockResult->fetch_assoc()) {
            $currentStock = intval($stockRow['quantity']);
            if ($currentStock >= $orderQuantity) {
                $newStock = $currentStock - $orderQuantity;
                $updateStockStmt = $con->prepare("UPDATE products SET quantity = ? WHERE id = ?");
                $updateStockStmt->bind_param("ii", $newStock, $productId);
                $updateStockStmt->execute();
                $updateStockStmt->close();
            }
        }
        $stockStmt->close();
    }
}

// Update coupon usage if applied
if (!$hasError && $orderId > 0 && !empty($couponCode) && $couponDiscount > 0) {
    $updateCoupon = $con->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE UPPER(code) = ?");
    if ($updateCoupon) {
        $upperCouponCode = strtoupper($couponCode);
        $updateCoupon->bind_param("s", $upperCouponCode);
        $updateCoupon->execute();
        $updateCoupon->close();
    }
    unset($_SESSION['applied_coupon']);
}

// Clear cart after successful order
if (!$hasError && $orderId > 0) {
    // Remove from DB cart table
    $cartTableCheck = $con->query("SHOW TABLES LIKE 'cart'");
    if ($cartTableCheck && $cartTableCheck->num_rows > 0) {
        if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'buy_now') {
            if (!empty($checkoutItems) && isset($checkoutItems[0]['product_id'])) {
                $p = intval($checkoutItems[0]['product_id']);
                $del = $con->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                if ($del) {
                    $del->bind_param("ii", $userId, $p);
                    $del->execute();
                    $del->close();
                }
            }
        } else {
            $del = $con->prepare("DELETE FROM cart WHERE user_id = ?");
            if ($del) {
                $del->bind_param("i", $userId);
                $del->execute();
                $del->close();
            }
        }
    }

    // Clear sessions
    if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'cart') {
        $_SESSION['cart'] = array();
    }
    unset($_SESSION['buy_now_id']);
    unset($_SESSION['checkout_items']);
    unset($_SESSION['checkout_total']);
    unset($_SESSION['checkout_mode']);

    $_SESSION['last_order_id'] = $orderId;
    $_SESSION['last_order_number'] = $orderNumber;

    // Commit
    mysqli_commit($con);
    mysqli_autocommit($con, true);

    error_log("ORDER_SUCCESS: Order ID $orderId created for user $userId");

    ob_clean();
    if ($paymentMethod === 'UPI') {
        header("Location: generate_invoice.php?order_id=" . $orderId);
    } else {
        header("Location: payment_success.php?order_id=" . $orderId);
    }
    exit;
}

// Rollback
mysqli_rollback($con);
mysqli_autocommit($con, true);

header("Location: checkout.php?checkout_error=Order processing failed. Please try again.");
exit;
?>
