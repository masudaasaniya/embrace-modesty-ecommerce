<?php
session_start();
include("connect.php");
require_once("order_schema_helper.php");

ensureOrderManagementSchema($con);

function checkoutImagePath($imageRaw) {
    $raw = trim($imageRaw);
    if ($raw === '') {
        return 'skaff1.jpg';
    }
    $candidates = array(
        'admin/uploads/products/' . ltrim($raw, '/\\'),
        'admin/uploads/' . ltrim($raw, '/\\'),
        'uploads/products/' . ltrim($raw, '/\\'),
        'uploads/' . ltrim($raw, '/\\'),
        $raw
    );
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    return 'skaff1.jpg';
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$checkoutItems = array();
$total = 0;

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit;
}

// Handle Buy Now direct checkout
if (isset($_GET['buy_now']) && isset($_GET['product_id'])) {
    $buy_now_product_id = intval($_GET['product_id']);
    $user_id = intval($_SESSION['user_id']);
    
    $stmt = $con->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $buy_now_product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $product = $result->fetch_assoc();
$checkoutItems = array(array(
'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => floatval($product['price']),
            'image' => $product['image'],
            'quantity' => 1
        ));
        $total = floatval($product['price']);
        $_SESSION['buy_now_checkout'] = true;
    } else {
        header("Location: shoppage2.php?error=product_not_found");
        exit;
    }
    $stmt->close();
} else {
// Handle selected cart items from cart.php
if (isset($_POST['proceed_selected']) && isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
$selected_product_ids = array_map('intval', $_POST['selected_items']);
    if (empty($selected_product_ids)) {
        header("Location: cart.php?checkout_error=no_selection");
        exit;
    }
    
    $user_id = intval($_SESSION['user_id']);
    $placeholders = str_repeat('?,', count($selected_product_ids) - 1) . '?';
    $stmt = $con->prepare("SELECT c.id, c.product_id, p.name, p.price, p.image, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND c.product_id IN ($placeholders) ORDER BY c.id DESC");
    $types = "i" . str_repeat('i', count($selected_product_ids));
$bind_params = array($types);
    $bind_params[] = $user_id;
    foreach($selected_product_ids as $pid) {
        $bind_params[] = $pid;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $checkoutItems = array();
    $total = 0;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $checkoutItems[] = $row;
            $total += floatval($row['price']) * intval($row['quantity']);
        }
    }
    $stmt->close();
    
    if (empty($checkoutItems)) {
        header("Location: cart.php?checkout_error=no_matching_items");
        exit;
    }
}


}

// Also store in session for process_order.php
$_SESSION['checkout_items'] = array();
$_SESSION['checkout_total'] = $total;

// FALLBACK: Load user's cart items ONLY if $_SESSION['cart'] is empty (safe)
if (empty($_SESSION['cart']) && empty($checkoutItems)) {
    $user_id = intval($_SESSION['user_id']);
    $stmt = $con->prepare("SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image 
                          FROM cart c JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = ? ORDER BY c.id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $checkoutItems[] = $row;
            $total += floatval($row['price']) * intval($row['quantity']);
        }
    }
    $stmt->close();
}

foreach ($checkoutItems as $item) {
    $_SESSION['checkout_items'][] = array(
        'product_id' => intval($item['product_id']),
        'product_name' => $item['name'],
        'price' => floatval($item['price']),
        'quantity' => intval($item['quantity']),
        'image' => $item['image']
    );
}

$prefill = array(
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'city' => '',
    'pincode' => ''
);


$userId = intval($_SESSION['user_id']);
$stmt = $con->prepare("SELECT name, email, phone, address FROM `user` WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $prefill['full_name'] = isset($user['name']) ? $user['name'] : '';
        $prefill['email'] = isset($user['email']) ? $user['email'] : '';
        $prefill['phone'] = isset($user['phone']) ? $user['phone'] : '';
        $prefill['address'] = isset($user['address']) ? $user['address'] : '';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Haya Collection</title>
<link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f8f3f4;font-family:Arial,sans-serif}
.wrap{max-width:1100px;margin:30px auto}
.card{border:0;border-radius:14px;box-shadow:0 10px 24px rgba(122,62,62,.1)}
.head{color:#7a3e3e;font-weight:700}
.summary-img{width:56px;height:56px;border-radius:8px;object-fit:cover}
.btn-main{background:#7a3e3e;border-color:#7a3e3e}
.btn-main:hover{background:#623232;border-color:#623232}

/* Payment Method Styles */
.payment-method {
    border: 2px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}
.payment-method:hover {
    border-color: #7a3e3e;
}
.payment-method.selected {
    border-color: #7a3e3e;
    background: #fdf5f6;
}
.payment-method input[type="radio"] {
    margin-right: 10px;
}

/* UPI Payment Section */
#upiBox {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}
.upi-qr-code {
    text-align: center;
    margin: 20px 0;
}
.upi-qr-code img {
    max-width: 200px;
    border-radius: 10px;
    border: 3px solid #7a3e3e;
}
.demo-badge {
    background: #ffc107;
    color: #333;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 10px;
}

/* Processing Overlay */
.processing-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.processing-overlay.active {
    display: flex;
}
.processing-box {
    background: white;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
}
.processing-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #7a3e3e;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.success-message {
    color: #28a745;
    font-size: 24px;
    margin-bottom: 10px;
}

/* Error message */
.error-message {
    color: #dc3545;
    font-size: 14px;
    margin-top: 5px;
    display: none;
}

/* UPI Success Modal */
#upiSuccessModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}
#upiSuccessModal.show {
    display: flex !important;
}
.upi-modal-content {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    max-width: 400px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.upi-success-icon {
    font-size: 60px;
    color: #28a745;
    margin-bottom: 15px;
}
.upi-title {
    color: #7a3e3e;
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
}
.upi-redirecting {
    color: #666;
    font-size: 16px;
}
#upiQR {
    border: 3px solid #7a3e3e !important;
    border-radius: 15px !important;
}
</style>
<link rel="stylesheet" href="responsive.css">
</head>
<body>

<?php
$checkout_error = isset($_GET['checkout_error']) ? trim($_GET['checkout_error']) : '';
$error_messages = array(
    'payment_failed' => 'Payment failed. Please check your details and try again or choose Cash on Delivery.',
    'failed' => 'Payment processing failed. Please try again.',
    'empty_cart' => 'Your cart is empty. Please add items to cart before checkout.',
    'invalid' => 'Invalid checkout details. Please fill all required fields.',
    'no_selection' => 'No items selected. Please select items from cart.',
    'no_matching_items' => 'Selected cart items not available. Please check your cart.'
);
$error_message = isset($error_messages[$checkout_error]) ? $error_messages[$checkout_error] : 'Something went wrong. Please try again.';

$alert_type = $checkout_error ? 'danger' : 'info';
?>
<?php if (!empty($checkout_error)): ?>
<div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show mt-4 mb-4 mx-auto" style="max-width: 1100px;" role="alert">
  <i class="fas fa-exclamation-triangle me-2"></i>
<strong>Checkout Error:</strong> <?php echo htmlspecialchars($_GET['checkout_error']); ?>
  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="wrap container">

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-4">
                <h3 class="head mb-3">Checkout Details</h3>
                <form method="post" action="process_order.php" id="checkoutForm" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" required value="<?php echo htmlspecialchars($prefill['full_name']); ?>">
                        <div class="error-message" id="full_name_error">Please enter your full name</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($prefill['email']); ?>">
                            <div class="error-message" id="email_error">Please enter a valid email address</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="text" name="phone" id="phone" class="form-control" required maxlength="10" pattern="\d{10}" value="<?php echo htmlspecialchars($prefill['phone']); ?>">
                            <div class="error-message" id="phone_error">Please enter a valid 10-digit phone number</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <textarea name="address" id="address" class="form-control" rows="3" required><?php echo htmlspecialchars($prefill['address']); ?></textarea>
                        <div class="error-message" id="address_error">Please enter your address</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" id="city" class="form-control" required value="<?php echo htmlspecialchars($prefill['city']); ?>">
                            <div class="error-message" id="city_error">Please enter your city</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode *</label>
<input type="text" name="pincode" id="pincode" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required value="<?php echo htmlspecialchars($prefill['pincode']); ?>">
                            <div class="error-message" id="pincode_error">Please enter a valid 6-digit pincode</div>
                        </div>
                    </div>
                    
                    <!-- Payment Method Section -->
                    <div class="mb-4">
                        <label class="form-label">Payment Method *</label>
                        
                        <!-- Cash on Delivery -->
                        <div class="payment-method" onclick="selectPayment('cod')">
                            <input type="radio" name="payment_method" id="payment_cod" value="Cash on Delivery" checked>
                            <strong>Cash on Delivery (COD)</strong>
                            <p class="mb-0 text-muted small">Pay when you receive your order</p>
                        </div>
                        
                        <!-- UPI Payment -->
<div class="payment-method" onclick="selectPayment('upi')">
                            <input type="radio" name="payment_method" id="payment_upi" value="UPI" onclick="selectPayment('upi')">
                            <strong>UPI Payment</strong>
                            <p class="mb-0 text-muted small">Pay using UPI</p>
                        </div>
                        
                        <!-- UPI Payment Section -->
<div id="upiBox" style="display:none; margin-top:15px;">
       <h4>Scan & Pay</h4>

       <img id="upiQR" src="uploads/upi-qr.png"
            alt="UPI QR"
            style="width:180px; height:180px; object-fit:contain; border:1px solid #ccc; padding:5px;">

       <p style="margin-top:10px;">OR Enter UPI ID:</p>
       <input type="text" name="upi_id" id="upi_id" placeholder="example@upi" class="form-control">
        <button type="button" id="payNowBtn" onclick="handleUPIPayment()" style="display:none;" class="btn btn-main text-white w-100 mt-2">Pay Now</button>
   </div>
                    </div>
                    
                    <!-- Hidden field to track UPI payment status -->
                    <input type="hidden" id="payment_status" name="payment_status" value="Pending">
                    
                    <button type="submit" class="btn btn-main text-white w-100 py-2" id="placeOrderBtn">Place Order</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-4">
Order Summary<?php if (isset($_POST['proceed_selected'])) echo ' (Selected Items)'; ?>
<?php if (!empty($checkoutItems)): ?>
                    <?php foreach ($checkoutItems as $item): ?> 
                        <div class="order-item-row mb-3 p-3 border rounded hover-shadow">
                            <div class="row align-items-center">
                                <div class="col-3 col-md-2">
                                    <img src="<?php echo htmlspecialchars(checkoutImagePath($item['image'])); ?>" class="img-fluid summary-img" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="col-6 col-md-7">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo intval($item['quantity']); ?> | Rs. <?php echo number_format(floatval($item['price']), 2); ?>/unit</small>
                                </div>
                                <div class="col-3 col-md-3 text-md-end fw-bold">
                                    Rs. <?php echo number_format(floatval($item['price']) * intval($item['quantity']), 2); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
<?php else: ?>
                    <p class="text-muted">No items selected. <a href="cart.php">Add to cart</a></p>
<?php endif; ?>

                <!-- Coupon Section --> 
                <div class="mt-3 p-3 bg-light rounded">
                    <label class="form-label small fw-bold">Have a Coupon Code?</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="coupon_code" class="form-control" placeholder="Enter coupon code">
                        <button type="button" class="btn btn-main text-white" onclick="applyCoupon()">Apply</button>
                    </div>
                    <div id="coupon_message" class="small mt-2"></div>
                </div>

                <!-- Discount Display -->
                <div id="discount_row" class="d-flex justify-content-between mt-3 fw-bold text-success" style="display:none;">
                    <span>Discount</span>
                    <span>- Rs. <span id="discount_amount">0</span></span>
                </div>

                <div class="d-flex justify-content-between mt-3 fw-bold">
                    <span>Total</span>
                    <span id="order_total">Rs. <?php echo number_format($total, 2); ?></span>
                </div>
                
                <input type="hidden" id="hidden_total" value="<?php echo $total; ?>">
                <input type="hidden" name="coupon_code" id="applied_coupon_code" value="">
                <input type="hidden" name="coupon_discount" id="applied_coupon_discount" value="0">
            </div>
        </div>
    </div>
</div>

<!-- Processing Overlay -->
<div class="processing-overlay" id="processingOverlay">
    <div class="processing-box">
        <div class="processing-spinner"></div>
        <h4 id="processingText">Processing Payment...</h4>
    </div>
</div>

<!-- UPI Success Modal -->
<div id="upiSuccessModal">
    <div class="upi-modal-content">
        <div class="upi-success-icon">✓</div>
        <div class="upi-title">Payment Successful!</div>
        <div class="upi-redirecting">Redirecting...</div>
    </div>
</div>

<script>
let payment_done = false;

function selectPayment(method) {
    var upiBox = document.getElementById('upiBox');

    if (method === 'upi') {
        upiBox.style.display = 'block';
        document.getElementById('payNowBtn').style.display = 'block';
    } else {
        upiBox.style.display = 'none';
        document.getElementById('payNowBtn').style.display = 'none';
    }
}

function handleUPIPayment() {
    document.getElementById('upiSuccessModal').classList.add('show');
    setTimeout(() => {
        document.getElementById('upiSuccessModal').classList.remove('show');
        payment_done = true;
        document.getElementById("payment_status").value = "Success";
        document.getElementById('placeOrderBtn').disabled = false;
        document.getElementById('placeOrderBtn').scrollIntoView({behavior: 'smooth'});
    }, 2000);
}



function applyCoupon() {
    var couponCode = document.getElementById('coupon_code').value.trim();
    var orderTotal = parseFloat(document.getElementById('hidden_total').value);
    var messageDiv = document.getElementById('coupon_message');
    var discountRow = document.getElementById('discount_row');
    var discountAmount = document.getElementById('discount_amount');
    var totalSpan = document.getElementById('order_total');
    var appliedCouponCode = document.getElementById('applied_coupon_code');
    var appliedCouponDiscount = document.getElementById('applied_coupon_discount');
    
    if (couponCode === '') {
        messageDiv.innerHTML = '<span class="text-danger">Please enter a coupon code</span>';
        return;
    }
    
    messageDiv.innerHTML = '<span class="text-muted">Validating...</span>';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'apply_coupon.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        messageDiv.innerHTML = '<span class="text-success">' + response.message + '</span>';
                        discountRow.style.display = 'flex';
                        discountAmount.innerHTML = response.discount.toFixed(2);
                        totalSpan.innerHTML = 'Rs. ' + response.new_total.toFixed(2);
                        appliedCouponCode.value = response.coupon_code;
                        appliedCouponDiscount.value = response.discount;
                    } else {
                        messageDiv.innerHTML = '<span class="text-danger">' + response.message + '</span>';
                        discountRow.style.display = 'none';
                        totalSpan.innerHTML = 'Rs. ' + orderTotal.toFixed(2);
                        appliedCouponCode.value = '';
                        appliedCouponDiscount.value = '0';
                    }
                } catch(e) {
                    messageDiv.innerHTML = '<span class="text-danger">Error processing response</span>';
                }
            } else {
                messageDiv.innerHTML = '<span class="text-danger">Server error</span>';
            }
        }
    };
    xhr.send('coupon_code=' + encodeURIComponent(couponCode) + '&order_total=' + orderTotal);
}

// Pincode strict validation
document.getElementById("pincode").addEventListener("input", function() {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 6) {
        this.value = this.value.slice(0, 6);
    }
});

// Form validation before submit
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    var paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    // If UPI is selected, check if payment is complete
    if (paymentMethod === 'UPI' && !payment_done) {
        e.preventDefault();
        alert("Please complete the UPI payment first");
        return false;
    }
    
    // Validate required fields
    var isValid = true;
    
    var fullName = document.getElementById('full_name').value.trim();
    if (fullName === '') {
        document.getElementById('full_name_error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('full_name_error').style.display = 'none';
    }
    
    var email = document.getElementById('email').value.trim();
    if (email === '' || !email.includes('@') || !email.includes('.')) {
        document.getElementById('email_error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('email_error').style.display = 'none';
    }
    
    var phone = document.getElementById('phone').value.trim();
    if (phone.length !== 10 || !/^\d+$/.test(phone)) {
        document.getElementById('phone_error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('phone_error').style.display = 'none';
    }
    
    var address = document.getElementById('address').value.trim();
    if (address === '') {
        document.getElementById('address_error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('address_error').style.display = 'none';
    }
    
    var city = document.getElementById('city').value.trim();
    if (city === '') {
        document.getElementById('city_error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('city_error').style.display = 'none';
    }
    
    var pincode = document.getElementById('pincode').value.trim();
    if (pincode.length !== 6 || !/^\d+$/.test(pincode)) {
        document.getElementById('pincode_error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('pincode_error').style.display = 'none';
    }
    
    if (!isValid) {
        e.preventDefault();
        return false;
    }
});

// Payment method radio listeners for Pay Now button
document.querySelectorAll('input[name="payment_method"]').forEach(el => {
    el.addEventListener('change', function () {
        if (this.value === 'UPI') {
            document.getElementById('payNowBtn').style.display = 'block';
        } else {
            document.getElementById('payNowBtn').style.display = 'none';
        }
    });
});

// Default selection handling
window.onload = function() {
    var selected = document.querySelector('input[name="payment_method"]:checked');
    if (selected) {
        selectPayment(selected.value);
    }
};


</script>

<!-- Payment Success Modal HTML -->
<div id="paymentSuccessModal" class="payment-success-modal" style="display: none;">
  <div class="modal-backdrop"></div>
  <div class="modal-card">
    <div class="success-icon">✓</div>
    <h2 class="modal-title">Payment Successful 🎉</h2>
    <p class="modal-desc">Your payment has been confirmed successfully!</p>
    <div class="d-flex gap-2 justify-content-center flex-wrap">
<a href="order_success.php?order_id=<?php echo isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : ''; ?>" class="btn btn-outline-success px-4">
        <i class="fas fa-receipt me-1"></i>View Invoice
      </a>
      <?php if (!isset($_SESSION['last_order_id']) || empty($_SESSION['last_order_id'])): ?>
        <small class="text-muted d-block mt-1">(Order ID not available yet)</small>
      <?php endif; ?>

      <a href="index.php" class="btn btn-success px-4">
        <i class="fas fa-shopping-bag me-1"></i>Continue Shopping
      </a>
    </div>
  </div>
</div>

<style>
.payment-success-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
  animation: fadeInModal 0.4s ease-out forwards;
}

@keyframes fadeInModal {
  to { opacity: 1; }
}
.payment-success-modal[style*='flex'] {
  opacity: 1;
}
.modal-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
}
.modal-card {
  background: white;
  border-radius: 24px;
  padding: 40px 30px;
  max-width: 420px;
  width: 90%;
  text-align: center;
  box-shadow: 0 30px 60px rgba(0,0,0,0.3);
  animation: slideUp 0.4s ease-out;
  position: relative;
}
.success-icon {
  width: 90px;
  height: 90px;
  background: linear-gradient(135deg, #28a745, #20c997);
  border-radius: 50%;
  margin: 0 auto 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: white;
  box-shadow: 0 10px 30px rgba(40,167,69,0.4);
}
.modal-title {
  font-size: 28px;
  font-weight: 700;
  color: #7a3e3e;
  margin-bottom: 12px;
}
.modal-desc {
  color: #666;
  font-size: 16px;
  margin-bottom: 32px;
}
/* Order Item Styles */
.order-item-row {
  transition: all 0.2s ease;
}
.order-item-row:hover {
  box-shadow: 0 4px 12px rgba(122,62,62,0.15);
  transform: translateY(-1px);
}
.hover-shadow:hover {
  box-shadow: 0 4px 12px rgba(122,62,62,0.15) !important;
}

/* Modal Buttons */
.modal-buttons .btn {
  border-radius: 50px;
  font-weight: 600;
  padding: 12px 24px;
  transition: all 0.3s ease;
}
.continue-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(122,62,62,0.4);
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
@media (max-width: 480px) {
  .modal-card { padding: 32px 24px; }
  .modal-title { font-size: 24px; }
}
</style>

<?php include 'includes/global_footer.php'; ?>


</body>
</html>

