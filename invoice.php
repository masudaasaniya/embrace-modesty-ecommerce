<?php
session_start();
include("connect.php");

function errorExit($msg) {
    header("Location: my-orders.php?error=" . urlencode($msg));
    exit;
}

function htmlErrorExit($msg) {
    ?>
<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; text-align: center; background: #f8f9fa; }
        .error-box { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #856404; margin-bottom: 10px; }
        p { color: #856404; line-height: 1.6; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>❌ Access Denied</h1>
        <p><?php echo htmlspecialchars($msg); ?></p>
        <p><a href="myaccount.php">← Back to Account</a> | <a href="my-orders.php">My Orders</a></p>
    </div>
</body>
</html>
<?php
    exit;
}

function getOrderData($orderId) {
    global $con;
    
    if ($orderId <= 0) {
        return null;
    }
    
    $stmt = $con->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        return array('error' => 'no_order');
    }
    
    // Items
    $itemsStmt = $con->prepare("
        SELECT oi.*, COALESCE(oi.product_image, p.image) AS image, p.name as product_name 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $itemsStmt->bind_param("i", $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    $orderItems = array();
    $total = 0;
    while ($item = $itemsResult->fetch_assoc()) {
        $orderItems[] = $item;
        $total += floatval($item['total']);
    }
    $itemsStmt->close();
    
    // Auth
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        return array('error' => 'no_session');
    }
    
    if (isset($_SESSION['admin_id'])) {
        return array('order_id' => $orderId, 'order' => $order, 'items' => $orderItems, 'total' => $total);
    }
    
    if ($order['user_id'] != $_SESSION['user_id']) {
        return array('error' => 'unauthorized');
    }
    
    return array('order_id' => $orderId, 'order' => $order, 'items' => $orderItems, 'total' => $total);
}

function invoiceImagePath($imageRaw) {
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

// Main
$orderIdInput = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
$orderId = intval($orderIdInput);

if ($orderId <= 0) {
    if (isset($_SESSION['last_order_id']) && $_SESSION['last_order_id'] > 0) {
        $orderId = $_SESSION['last_order_id'];
    } else {
        htmlErrorExit('Invalid order ID');
    }
}

$orderData = getOrderData($orderId);
if (isset($orderData['error'])) {
    switch($orderData['error']) {
        case 'no_order':
            htmlErrorExit('Order not found');
            break;
        case 'no_session':
            htmlErrorExit('Please login to view invoice');
            break;
        case 'unauthorized':
            htmlErrorExit('Unauthorized - only your orders');
            break;
        default:
            htmlErrorExit('Invalid order ID');
    }
}

$order = $orderData['order'];
$orderItems = $orderData['items'];
$total = $orderData['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($order['order_number']); ?> - Haya Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f3f4; font-family: Arial, sans-serif; }
        .main-container { max-width: 900px; margin: 40px auto; }
        .success-card { border-radius: 20px; box-shadow: 0 15px 35px rgba(122,62,62,0.1); border: none; }
        .success-icon { font-size: 4rem; color: #28a745; }
        .primary-color { color: #7a3e3e; }
        .invoice-header { background: linear-gradient(135deg, #7a3e3e 0%, #623232 100%); color: white; padding: 20px; border-radius: 15px 15px 0 0; }
        .invoice-body { background: white; padding: 30px; }
        .invoice-table th { background: #f8f5f5; color: #7a3e3e; }
        .btn-main { background: #7a3e3e; border-color: #7a3e3e; color: white; }
        .btn-main:hover { background: #623232; }
        .order-details { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .invoice-header { -webkit-print-color-adjust: exact; color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Success Message -->
        <div class="text-center mb-5">
            <div class="success-icon mb-3">✓</div>
            <h1 class="primary-color mb-2">Order Confirmed!</h1>
            <p class="lead text-muted">Download your invoice or print for records.</p>
        </div>

        <!-- Invoice Card -->
        <div class="card success-card">
            <!-- Header -->
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-0">Haya Collection</h2>
                        <p class="mb-0 small">Premium Hijab & Accessories</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h4 class="mb-0">INVOICE</h4>
                        <p class="mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="invoice-body">
                <!-- Order & Payment Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="primary-color mb-2">Order Details</h6>
                        <p><strong>Order ID:</strong> #<?php echo $orderData['order_id']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y h:i A', strtotime($order['created_at'])); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($order['status']); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="primary-color mb-2">Payment Details</h6>
                        <p><strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-<?php echo strtolower($order['payment_status']) == 'success' || strtolower($order['payment_status']) == 'paid' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span></p>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="order-details">
                    <h6 class="primary-color mb-3">Customer Details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Address:</strong></p>
                            <p class="mb-0"><?php echo htmlspecialchars($order['address']); ?></p>
                            <p><?php echo htmlspecialchars($order['city']); ?> - <?php echo htmlspecialchars($order['pincode']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <h6 class="primary-color mb-3">Order Items (<?php echo count($orderItems); ?> items)</h6>
                <?php if (empty($orderItems)): ?>
                    <p class="text-muted">No items in this order.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover invoice-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars(invoiceImagePath($item['image'])); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 12px;">
                                                <div>
                                                    <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                    <small class="text-muted">ID: <?php echo $item['product_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold"><?php echo intval($item['quantity']); ?></td>
                                        <td class="text-end">Rs. <?php echo number_format(floatval($item['price']), 2); ?></td>
                                        <td class="text-end fw-bold primary-color">Rs. <?php echo number_format(floatval($item['total']), 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Grand Total -->
                <div class="row mt-4">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="border-start ps-4 border-3 border-primary">
                            <div class="h4 mb-0 d-flex justify-content-between">
                                <span>Grand Total</span>
                                <span class="primary-color">Rs. <?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-5 no-print">
                    
                    <button onclick="window.print()" class="btn btn-outline-primary btn-lg me-3 mb-2">
                        <i class="fas fa-print me-2"></i>Print Invoice
                    </button>
                    <a href="my-orders.php" class="btn btn-outline-secondary btn-lg mb-2">
                        <i class="fas fa-list me-2"></i>My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
