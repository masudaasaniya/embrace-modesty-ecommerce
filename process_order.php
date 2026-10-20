<?php
ob_start();
session_start();
include("connect.php");
require_once("order_schema_helper.php");

function logOrderError($con, $step) {
    error_log("ORDER_ERROR: " . $step . " | " . mysqli_error($con));
}

function detectProductStockColumn($con) {
    $candidates = array('quantity', 'stock', 'qty');
    foreach ($candidates as $column) {
        $safe = mysqli_real_escape_string($con, $column);
        $res = mysqli_query($con, "SHOW COLUMNS FROM products LIKE '" . $safe . "'");
        if ($res && mysqli_num_rows($res) > 0) {
            return $column;
        }
    }
    return '';
}

function aggregateOrderQuantities($items) {
    $aggregated = array();
    foreach ($items as $item) {
        $productId = isset($item['product_id']) ? intval($item['product_id']) : 0;
        $qty = isset($item['quantity']) ? intval($item['quantity']) : 0;
        if ($productId <= 0 || $qty <= 0) {
            continue;
        }
        if (!isset($aggregated[$productId])) {
            $aggregated[$productId] = 0;
        }
        $aggregated[$productId] += $qty;
    }
    return $aggregated;
}

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: checkout.php");
    exit;
}

ensureOrderManagementSchema($con);

// ✅ FIXED INPUT (NO ?? USED)
$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'COD';
$paymentStatus = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : 'Pending';

$paymentStatusField = ($paymentMethod == 'UPI' && $paymentStatus == 'Success') ? 'Paid' : 'Pending';
$orderStatus = ($paymentMethod == 'UPI' && $paymentStatus == 'Success') ? 'Confirmed' : 'Pending';

$couponCode = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
$couponDiscount = isset($_POST['coupon_discount']) ? floatval($_POST['coupon_discount']) : 0;

// Validation
$errors = array();

if ($fullName == '') $errors[] = "Full name required";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required";
if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Valid phone required";
if ($address == '') $errors[] = "Address required";
if ($city == '') $errors[] = "City required";
if (!preg_match('/^\d{6}$/', $pincode)) $errors[] = "Valid pincode required";

if (!empty($errors)) {
    header("Location: checkout.php");
    exit;
}

// SESSION DATA
$checkoutItems = isset($_SESSION['checkout_items']) ? $_SESSION['checkout_items'] : array();
$checkoutSubtotal = isset($_SESSION['checkout_total']) ? $_SESSION['checkout_total'] : 0;

// fallback cart
if (empty($checkoutItems)) {
    $userId = $_SESSION['user_id'];
    $res = mysqli_query($con, "SELECT * FROM cart WHERE user_id = $userId");
    while ($row = mysqli_fetch_assoc($res)) {
        $checkoutItems[] = $row;
        $checkoutSubtotal += $row['price'] * $row['quantity'];
    }
}

if (empty($checkoutItems)) {
    header("Location: cart.php");
    exit;
}

$stockByProduct = aggregateOrderQuantities($checkoutItems);
if (empty($stockByProduct)) {
    header("Location: checkout.php?checkout_error=invalid_items");
    exit;
}

$checkoutTotal = $checkoutSubtotal - $couponDiscount;
if ($checkoutTotal < 0) $checkoutTotal = 0;

ksort($stockByProduct);
$orderFingerprint = md5(
    intval($_SESSION['user_id']) . '|' .
    json_encode($stockByProduct) . '|' .
    number_format($checkoutTotal, 2, '.', '') . '|' .
    $paymentMethod
);
if (
    isset($_SESSION['last_order_fingerprint']) &&
    $_SESSION['last_order_fingerprint'] === $orderFingerprint &&
    isset($_SESSION['last_order_id']) &&
    intval($_SESSION['last_order_id']) > 0 &&
    isset($_SESSION['last_order_fingerprint_time']) &&
    (time() - intval($_SESSION['last_order_fingerprint_time'])) < 600
) {
    header("Location: invoice.php?order_id=" . intval($_SESSION['last_order_id']));
    exit;
}

// TRANSACTION START
mysqli_autocommit($con, false);

$stockColumn = detectProductStockColumn($con);
if ($stockColumn === '') {
    logOrderError($con, "PRODUCT STOCK COLUMN MISSING");
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=stock_column_missing");
    exit;
}

$lockStockStmt = $con->prepare("SELECT id, `" . $stockColumn . "` AS stock_qty FROM products WHERE id = ? FOR UPDATE");
if (!$lockStockStmt) {
    logOrderError($con, "LOCK STOCK PREPARE FAILED");
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=stock_check_failed");
    exit;
}

foreach ($stockByProduct as $productId => $requiredQty) {
    $lockStockStmt->bind_param("i", $productId);
    if (!$lockStockStmt->execute()) {
        $lockStockStmt->close();
        logOrderError($con, "LOCK STOCK EXECUTE FAILED");
        mysqli_rollback($con);
        mysqli_autocommit($con, true);
        header("Location: checkout.php?checkout_error=stock_check_failed");
        exit;
    }

    $stockResult = $lockStockStmt->get_result();
    if (!$stockResult || $stockResult->num_rows === 0) {
        $lockStockStmt->close();
        mysqli_rollback($con);
        mysqli_autocommit($con, true);
        header("Location: checkout.php?checkout_error=product_not_found");
        exit;
    }

    $stockRow = $stockResult->fetch_assoc();
    $availableQty = isset($stockRow['stock_qty']) ? intval($stockRow['stock_qty']) : 0;
    if ($availableQty < $requiredQty) {
        $lockStockStmt->close();
        mysqli_rollback($con);
        mysqli_autocommit($con, true);
        header("Location: checkout.php?checkout_error=insufficient_stock");
        exit;
    }
}
$lockStockStmt->close();

// INSERT ORDER
$stmt = $con->prepare("INSERT INTO orders 
(user_id, full_name, email, phone, address, city, pincode, total_price, payment_method, payment_status, status, created_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$stmt->bind_param("isssssdssss", 
    $_SESSION['user_id'],
    $fullName,
    $email,
    $phone,
    $address,
    $city,
    $pincode,
    $checkoutTotal,
    $paymentMethod,
    $paymentStatusField,
    $orderStatus
);

if (!$stmt->execute()) {
    logOrderError($con, "ORDER INSERT FAILED");
    mysqli_rollback($con);
    exit;
}

$orderId = $stmt->insert_id;
$stmt->close();

// INSERT ITEMS
$itemStmt = $con->prepare("INSERT INTO order_items 
(order_id, product_id, product_name, price, quantity, total) 
VALUES (?, ?, ?, ?, ?, ?)");
if (!$itemStmt) {
    logOrderError($con, "ORDER ITEM PREPARE FAILED");
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=order_item_failed");
    exit;
}

foreach ($checkoutItems as $item) {

    $productId = $item['product_id'];
    $name = isset($item['name']) ? $item['name'] : '';
    $price = $item['price'];
    $qty = $item['quantity'];
    $total = $price * $qty;

    $itemStmt->bind_param("iisdid", $orderId, $productId, $name, $price, $qty, $total);
    if (!$itemStmt->execute()) {
        $itemStmt->close();
        logOrderError($con, "ORDER ITEM INSERT FAILED");
        mysqli_rollback($con);
        mysqli_autocommit($con, true);
        header("Location: checkout.php?checkout_error=order_item_failed");
        exit;
    }
}

$itemStmt->close();

// STOCK UPDATE (transaction-safe and non-negative)
$deductStockStmt = $con->prepare("UPDATE products SET `" . $stockColumn . "` = `" . $stockColumn . "` - ? WHERE id = ? AND `" . $stockColumn . "` >= ?");
if (!$deductStockStmt) {
    logOrderError($con, "DEDUCT STOCK PREPARE FAILED");
    mysqli_rollback($con);
    mysqli_autocommit($con, true);
    header("Location: checkout.php?checkout_error=stock_update_failed");
    exit;
}

foreach ($stockByProduct as $productId => $requiredQty) {
    $deductStockStmt->bind_param("iii", $requiredQty, $productId, $requiredQty);
    if (!$deductStockStmt->execute() || $deductStockStmt->affected_rows !== 1) {
        $deductStockStmt->close();
        logOrderError($con, "DEDUCT STOCK EXECUTE FAILED");
        mysqli_rollback($con);
        mysqli_autocommit($con, true);
        header("Location: checkout.php?checkout_error=insufficient_stock");
        exit;
    }
}
$deductStockStmt->close();

// CLEAR CART
$userId = $_SESSION['user_id'];
$con->query("DELETE FROM cart WHERE user_id = $userId");

// COMMIT
mysqli_commit($con);
mysqli_autocommit($con, true);

// SESSION CLEAN
unset($_SESSION['checkout_items']);
unset($_SESSION['checkout_total']);

$_SESSION['last_order_id'] = $orderId;
$_SESSION['last_order_fingerprint'] = $orderFingerprint;
$_SESSION['last_order_fingerprint_time'] = time();

// ✅ REDIRECT (OLD BEHAVIOR RESTORED)
header("Location: invoice.php?order_id=" . $orderId);
exit;
?>
