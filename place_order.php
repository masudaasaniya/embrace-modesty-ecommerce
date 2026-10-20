<?php
session_start();
include("connect.php");
require_once("order_schema_helper.php");

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

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

ensureOrderManagementSchema($con);

$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Cash on Delivery';
$paymentStatus = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : 'Pending';

// If UPI payment is successful, set status to Success
if ($paymentMethod === 'UPI' && $paymentStatus === 'Success') {
    $paymentStatus = 'Success';
} else {
    // COD is always pending until delivery
    $paymentStatus = 'Pending';
}

// Coupon data
$couponCode = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
$couponDiscount = isset($_POST['coupon_discount']) ? floatval($_POST['coupon_discount']) : 0;

// Calculate final total (subtotal - discount)
$checkoutItems = isset($_SESSION['checkout_items']) && is_array($_SESSION['checkout_items']) ? $_SESSION['checkout_items'] : array();
$checkoutSubtotal = isset($_SESSION['checkout_total']) ? floatval($_SESSION['checkout_total']) : 0;
$checkoutTotal = $checkoutSubtotal - $couponDiscount;
if ($checkoutTotal < 0) $checkoutTotal = 0;

// Consolidated validation: calculate total first, then single check post-fallback
if (empty($checkoutItems) || $checkoutTotal <= 0) {
header("Location: checkout.php?checkout_error=Invalid total");
    exit;
}

$userId = intval($_SESSION['user_id']);
$adminResponse = '';
$orderId = 0;
$hasError = false;
$hasOrderDate = false;
$stockColumn = '';
$stockByProduct = aggregateOrderQuantities($checkoutItems);

$orderDateCheck = $con->query("SHOW COLUMNS FROM orders LIKE 'order_date'");
if ($orderDateCheck && $orderDateCheck->num_rows > 0) {
    $hasOrderDate = true;
}

mysqli_autocommit($con, false);

if (empty($stockByProduct)) {
    $hasError = true;
}

if (!$hasError) {
    $stockColumn = detectProductStockColumn($con);
    if ($stockColumn === '') {
        $hasError = true;
    }
}

if (!$hasError) {
    $lockStockStmt = $con->prepare("SELECT id, `" . $stockColumn . "` AS stock_qty FROM products WHERE id = ? FOR UPDATE");
    if (!$lockStockStmt) {
        $hasError = true;
    } else {
        foreach ($stockByProduct as $productId => $requiredQty) {
            $lockStockStmt->bind_param("i", $productId);
            if (!$lockStockStmt->execute()) {
                $hasError = true;
                break;
            }
            $stockResult = $lockStockStmt->get_result();
            if (!$stockResult || $stockResult->num_rows === 0) {
                $hasError = true;
                break;
            }
            $stockRow = $stockResult->fetch_assoc();
            $availableQty = isset($stockRow['stock_qty']) ? intval($stockRow['stock_qty']) : 0;
            if ($availableQty < $requiredQty) {
                $hasError = true;
                break;
            }
        }
        $lockStockStmt->close();
    }
}

$insertSql = "INSERT INTO orders (user_id, order_number, full_name, email, phone, address, city, pincode, total_price, payment_method, status, admin_response";
$insertValues = " VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
if ($hasOrderDate) {
    $insertSql .= ", order_date";
    $insertValues .= ", NOW()";
}
$insertSql .= ", created_at)";
$insertValues .= ", NOW())";
$insertOrder = $con->prepare($insertSql . $insertValues);
if (!$insertOrder) {
    $hasError = true;
}

if (!$hasError) {
$insertOrder->bind_param("issssssdsss", $userId, $fullName, $email, $phone, $address, $city, $pincode, $checkoutTotal, $paymentMethod, $paymentStatus, $adminResponse);
    if (!$insertOrder->execute()) {
        $hasError = true;
    } else {
        $orderId = intval($insertOrder->insert_id);
    }
    $insertOrder->close();
}

if (!$hasError && $orderId > 0) {
    $orderNumber = 'EM-' . date('Ymd') . '-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    $updateOrder = $con->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
    if ($updateOrder) {
        $updateOrder->bind_param("si", $orderNumber, $orderId);
        $updateOrder->execute();
        $updateOrder->close();
    }

    $insertCustomer = $con->prepare("INSERT INTO order_customers (order_id, full_name, email, phone, address, city, pincode, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($insertCustomer) {
        $insertCustomer->bind_param("issssss", $orderId, $fullName, $email, $phone, $address, $city, $pincode);
        if (!$insertCustomer->execute()) {
            $hasError = true;
        }
        $insertCustomer->close();
    }
}

if (!$hasError && $orderId > 0) {
    $hasOrderItemsCreatedAt = false;
    $orderItemsCreatedAtCheck = $con->query("SHOW COLUMNS FROM order_items LIKE 'created_at'");
    if ($orderItemsCreatedAtCheck && $orderItemsCreatedAtCheck->num_rows > 0) {
        $hasOrderItemsCreatedAt = true;
    }

    // Check if product_image column exists
    $hasProductImage = false;
    $productImageCheck = $con->query("SHOW COLUMNS FROM order_items LIKE 'product_image'");
    if ($productImageCheck && $productImageCheck->num_rows > 0) {
        $hasProductImage = true;
    }

    if ($hasProductImage) {
        if ($hasOrderItemsCreatedAt) {
            $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        } else {
            $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
        }
    } else {
        if ($hasOrderItemsCreatedAt) {
            $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        } else {
            $insertItem = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
        }
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
}

if (!$hasError && $orderId > 0) {
    $deductStockStmt = $con->prepare("UPDATE products SET `" . $stockColumn . "` = `" . $stockColumn . "` - ? WHERE id = ? AND `" . $stockColumn . "` >= ?");
    if (!$deductStockStmt) {
        $hasError = true;
    } else {
        foreach ($stockByProduct as $productId => $requiredQty) {
            $deductStockStmt->bind_param("iii", $requiredQty, $productId, $requiredQty);
            if (!$deductStockStmt->execute() || $deductStockStmt->affected_rows !== 1) {
                $hasError = true;
                break;
            }
        }
        $deductStockStmt->close();
    }
}

if (!$hasError && $orderId > 0) {
    mysqli_commit($con);
    mysqli_autocommit($con, true);

    // Update coupon usage count if coupon was applied
    if (!empty($couponCode) && $couponDiscount > 0) {
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

    // Remove ordered products from DB cart table if present.
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

    if (isset($_SESSION['checkout_mode']) && $_SESSION['checkout_mode'] === 'cart') {
        $_SESSION['cart'] = array();
    }

    unset($_SESSION['buy_now_product_id']);
    unset($_SESSION['checkout_items']);
    unset($_SESSION['checkout_total']);
    unset($_SESSION['checkout_mode']);

    $_SESSION['last_order_id'] = $orderId;
    $_SESSION['last_order_number'] = $orderNumber;

    if ($paymentMethod === 'UPI') {
        header("Location: generate_invoice.php?order_id=" . $orderId);
    } else {
        header("Location: payment_success.php");
    }
    exit;
}

mysqli_rollback($con);
mysqli_autocommit($con, true);
header("Location: checkout.php?checkout_error=insufficient_stock");
exit;
?>
