<?php
session_start();
include("connect.php");

header('Content-Type: application/json');

$response = array(
    'success' => false,
    'message' => '',
    'discount' => 0,
    'new_total' => 0
);

// Check if coupon code is submitted
if (!isset($_POST['coupon_code']) || empty($_POST['coupon_code'])) {
    $response['message'] = 'Please enter a coupon code';
    echo json_encode($response);
    exit;
}

$couponCode = strtoupper(trim($_POST['coupon_code']));
$orderTotal = isset($_POST['order_total']) ? floatval($_POST['order_total']) : 0;

if ($orderTotal <= 0) {
    $response['message'] = 'Invalid order total';
    echo json_encode($response);
    exit;
}

// Validate coupon from database
$stmt = $con->prepare("SELECT * FROM coupons WHERE UPPER(code) = ? LIMIT 1");
if (!$stmt) {
    $response['message'] = 'Database error';
    echo json_encode($response);
    exit;
}

$stmt->bind_param("s", $couponCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $coupon = $result->fetch_assoc();
    
    // Check if coupon is active
    if ($coupon['status'] != 'active') {
        $response['message'] = 'Coupon is not active';
        echo json_encode($response);
        exit;
    }
    
    // Check expiry date
    if (!empty($coupon['expiry_date'])) {
        $expiry = strtotime($coupon['expiry_date']);
        $now = time();
        if ($expiry < $now) {
            $response['message'] = 'Coupon has expired';
            echo json_encode($response);
            exit;
        }
    }
    
    // Check minimum order amount
    $minOrder = floatval($coupon['min_order_amount']);
    if ($orderTotal < $minOrder) {
        $response['message'] = 'Minimum order amount for this coupon is Rs. ' . number_format($minOrder);
        echo json_encode($response);
        exit;
    }
    
    // Check usage limit
    $maxUses = intval($coupon['max_uses']);
    $usedCount = intval($coupon['used_count']);
    if ($maxUses > 0 && $usedCount >= $maxUses) {
        $response['message'] = 'Coupon usage limit exceeded';
        echo json_encode($response);
        exit;
    }
    
    // Calculate discount
    $discount = 0;
    $discountType = isset($coupon['discount_type']) ? $coupon['discount_type'] : 'fixed';
    $discountValue = floatval($coupon['discount_value']);
    
    if ($discountType == 'percentage') {
        $discount = ($orderTotal * $discountValue) / 100;
    } else {
        $discount = $discountValue;
    }
    
    // Don't exceed order total
    if ($discount > $orderTotal) {
        $discount = $orderTotal;
    }
    
    $newTotal = $orderTotal - $discount;
    
    // Store coupon in session
    $_SESSION['applied_coupon'] = array(
        'code' => $coupon['code'],
        'discount' => $discount,
        'coupon_id' => $coupon['id']
    );
    
    $response['success'] = true;
    $response['message'] = 'Coupon applied successfully!';
    $response['discount'] = $discount;
    $response['new_total'] = $newTotal;
    $response['coupon_code'] = $coupon['code'];
    
} else {
    $response['message'] = 'Invalid or expired coupon';
}

$stmt->close();

echo json_encode($response);
exit;
?>

