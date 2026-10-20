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

if (empty($phone) || !preg_match('/^\\d{10}$/', $phone)) {
$errors[] = "Valid 10-digit phone number is required";
}

if (empty($address)) {
$errors[] = "Address is required";
}

if (empty($city)) {
$errors[] = "City is required";
}

if(!preg_match('/^[0-9]{6}$/', $pincode)){
    die("Invalid pincode. Please enter a valid 6 digit pincode.");
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
    // SET PAYMENT STATUS
    // ============================================

// Set payment status based on payment method
if ($paymentMethod === 'UPI' && $paymentStatus === 'Success') {
    $finalPaymentStatus = 'Success';
} else {
    // COD is always Pending until delivery
    $finalPaymentStatus = 'Pending';
}

// ============================================
    // DUPLICATE ORDER PREVENTION (NEW)
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

// Start transaction
mysqli_autocommit($con, false);

$orderStatus = ($paymentMethod === 'UPI' && $paymentStatus === 'Success') ? 'Confirmed' : 'Pending';

$insertSql = "INSERT INTO orders (user_id, full_name, email, phone, address, city, pincode, total, total_price, payment_method, payment_status, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

logOrderError($con, 'BEFORE_ORDERS_PREPARE', $insertSql);
$insertOrder = $con->prepare($insertSql);
if (!$insertOrder) {
    die("PREPARE FAILED: " . $con->error);
}
if (!$insertOrder->bind_param("issssssddsss", $userId, $fullName, $email, $phone, $address, $city, $pincode, $checkoutTotal, $checkoutTotal, $paymentMethod, $paymentStatusField, $orderStatus)) {
    die("BIND FAILED: " . $insertOrder->error);
}
if (!$insertOrder->execute()) {
    die("EXECUTE FAILED: " . $insertOrder->error . " | SQL: " . $insertSql);
}
$orderId = $insertOrder->insert_id;
if ($orderId == 0) {
    die("INSERT ID 0 - no row inserted");
}
// Update order_number
$orderNumber = 'ORD-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
$updateStmt = $con->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
if ($updateStmt) {
    $updateStmt->bind_param("si", $orderNumber, $orderId);
    $updateStmt->execute();
    $updateStmt->close();
}
$insertOrder->close();



// Insert into order_customers table (OPTIONAL - skip if table missing)
if (!$hasError && $orderId > 0) {
    $tableCheck = $con->query("SHOW TABLES LIKE 'order_customers'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        logOrderError($con, 'BEFORE_CUSTOMERS_PREPARE');
        $insertCustomer = $con->prepare("INSERT INTO order_customers (order_id, full_name, email, phone, address, city, pincode, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$insertCustomer) {
            logOrderError($con, 'CUSTOMERS_PREPARE_FAILED');
        } else {
            $insertCustomer->bind_param("issssss", $orderId, $fullName, $email, $phone, $address, $city, $pincode);
            if (!$insertCustomer->execute()) {
                logOrderError($con, 'CUSTOMERS_EXEC_FAILED');
                $hasError = true;
            }
            $insertCustomer->close();
        }
    } else {
        logOrderError($con, 'ORDER_CUSTOMERS_TABLE_MISSING', '', 'Skipped optional table');
    }
}

// Insert order items
if (!$hasError && $orderId > 0) {
    // Check if product_image column exists in order_items
    $hasProductImage = false;
    $productImageCheck = $con->query("SHOW COLUMNS FROM order_items LIKE 'product_image'");
    if ($productImageCheck && $productImageCheck->num_rows > 0) {
        $hasProductImage = true;
    }
    
    // Check if created_at column exists
    $hasOrderItemsCreatedAt = false;
    $orderItemsCreatedAtCheck = $con->query("SHOW COLUMNS FROM order_items LIKE 'created_at'");
    if ($orderItemsCreatedAtCheck && $orderItemsCreatedAtCheck->num_rows > 0) {
        $hasOrderItemsCreatedAt = true;
    }
    
    // Prepare insert statement based on columns
    if ($hasProductImage && $hasOrderItemsCreatedAt) {
        logOrderError($con, 'BEFORE_ORDER_ITEMS_PREPARE', 'hasImage:' . ($hasProductImage?'Y':'N') . ' hasCreatedAt:' . ($hasOrderItemsCreatedAt?'Y':'N'));
        $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$insertItem) logOrderError($con, 'ORDER_ITEMS_PREPARE_FAILED_FULL');
    } elseif ($hasProductImage) {
        $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$insertItem) logOrderError($con, 'ORDER_ITEMS_PREPARE_FAILED_IMAGE');
    } elseif ($hasOrderItemsCreatedAt) {
        $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    } else {
        $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
    }
    
    if (!$insertItem) {
        $hasError = true;
    }
    
    if (!$hasError) {
        foreach ($checkoutItems as $item) {
            $productId = isset($item['product_id']) ? intval($item['product_id']) : 0;
            $productName = isset($item['product_name']) ? $item['product_name'] : '';
            $price = isset($item['price']) ? floatval($item['price']) : 0;
            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
            $productImage = isset($item['image']) && !empty($item['image']) ? $item['image'] : '';

            // Fallback: Get from database
            if (empty($productImage) && $productId > 0) {
                logOrderError($con, 'IMAGE_FROM_DB_FALLBACK', '', "Product ID: $productId");
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
                logOrderError($con, 'IMAGE_INSERT', '', "Image: " . substr($productImage, 0, 50));
            } else {
                $insertItem->bind_param("iisdid", $orderId, $productId, $productName, $price, $quantity, $lineTotal);
                logOrderError($con, 'NO_IMAGE_COLUMN', '', "Product ID: $productId, Image would be: " . substr($productImage, 0, 50));
            }
            
            if (!$insertItem->execute()) {
                $hasError = true;
                break;
            }
        }
        $insertItem->close();
    }
}

// ============================================
    // STOCK REDUCTION LOGIC (NEW)
    // ============================================

if (!$hasError && $orderId > 0) {
    $stockUpdateError = false;
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
            if ($currentStock < $orderQuantity) {
                logOrderError($con, 'INSUFFICIENT_STOCK', '', "Product ID: $productId, Current: $currentStock, Needed: $orderQuantity");
                $stockUpdateError = true;
                break;
            }
            
            // Update stock
            $newStock = $currentStock - $orderQuantity;
            $updateStockStmt = $con->prepare("UPDATE products SET quantity = ? WHERE id = ?");
            $updateStockStmt->bind_param("ii", $newStock, $productId);
            if (!$updateStockStmt->execute()) {
                logOrderError($con, 'STOCK_UPDATE_FAILED', '', "Product ID: $productId");
                $stockUpdateError = true;
                break;
            }
            $updateStockStmt->close();
        } else {
            logOrderError($con, 'PRODUCT_NOT_FOUND', '', "Product ID: $productId");
            $stockUpdateError = true;
            break;
        }
        $stockStmt->close();
    }
    
    if ($stockUpdateError) {
        $hasError = true;
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
    // Clear coupon session
    unset($_SESSION['applied_coupon']);
}

// Clear cart after successful order
if (!$hasError && $orderId > 0) {
    // Remove ordered products from DB cart table
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
    
    // Clear session cart
    if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'cart') {
        $_SESSION['cart'] = array();
    }
    
    // Clear checkout session
    unset($_SESSION['buy_now_product_id']);
    unset($_SESSION['checkout_items']);
    unset($_SESSION['checkout_total']);
    unset($_SESSION['checkout_mode']);
    
    // Store order info for success page
    $_SESSION['last_order_id'] = $orderId;
    $_SESSION['last_order_number'] = $orderNumber;
    
    // Commit transaction
    mysqli_commit($con);
    mysqli_autocommit($con, true);
    
    // Success log + redirect to NEW invoice.php
    error_log("ORDER_SUCCESS: Order ID $orderId created for user $userId (phone: $phone), total Rs. $checkoutTotal, payment: $paymentMethod");
    
    // Fix: Clean output buffer + redirect
    ob_clean();
    header("Location: invoice.php?order_id=" . $orderId);
    exit;
}

// Rollback on error
mysqli_rollback($con);
mysqli_autocommit($con, true);

die("MYSQL_ERROR: " . mysqli_error($con) . " | Last ID: $orderId | User: $userId");
?>
