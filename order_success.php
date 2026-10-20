<?php
session_start();
include("connect.php");

// [INVOICE_DEBUG] START - Temporary debug logs only
error_log("[INVOICE_DEBUG] Incoming order_id: " . (isset($_GET['order_id']) ? $_GET['order_id'] : 'MISSING') . " | Validated: " . (isset($order_id) ? $order_id : 'INVALID'));

// Safety check per requirements
if (!isset($_GET['order_id']) || empty($_GET['order_id']) || ($order_id = intval($_GET['order_id'])) <= 0) {
    error_log("[INVOICE_DEBUG] order_id invalid/missing: " . (isset($_GET['order_id']) ? $_GET['order_id'] : 'MISSING'));
    header("Location: checkout.php");
    exit();
}

$orderId = $order_id; // Use validated order_id
error_log("[INVOICE_DEBUG] Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO_SESSION') . " | orderId: $orderId");

// Fetch order details
$stmt = $con->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

error_log("[INVOICE_DEBUG] Fetched order: " . ($order ? "FOUND id={$order['id']} user_id={$order['user_id']} order_number=" . ($order['order_number'] ?? 'N/A') : 'NOT_FOUND') . " | Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO_SESSION'));

if (!$order) {
    error_log("[INVOICE_DEBUG] Order not found for id: $orderId");
    header("Location: my-orders.php?error=invalid_order");
    exit;
}

// Check if user owns this order
$sessionUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO_SESSION';
$orderUserId = $order['user_id'];
$authMatch = ($orderUserId == $sessionUserId);
error_log("[INVOICE_DEBUG] Auth check: order.user_id=$orderUserId vs session=$sessionUserId | Match: " . ($authMatch ? 'YES' : 'NO'));

if (!$authMatch) {
    error_log("[INVOICE_DEBUG] Unauthorized access - user mismatch for order_id: $orderId");
    header("Location: my-orders.php?error=unauthorized");
    exit;
}
error_log("[INVOICE_DEBUG] Auth passed - showing order $orderId");
// [INVOICE_DEBUG] END

// Fetch order details
$stmt = $con->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: my-orders.php?error=invalid_order");
    exit;
}

// Check if user owns this order
if ($order['user_id'] != $_SESSION['user_id']) {
    header("Location: my-orders.php?error=unauthorized");
    exit;
}

// Fetch order items
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Haya Collection</title>
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
        .btn-print { background: #7a3e3e; border-color: #7a3e3e; }
        .btn-print:hover { background: #623232; }
        .order-details { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
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
            <h1 class="primary-color mb-2">Payment Successful!</h1>
            <p class="lead text-muted">Thank you for your order. Here's your invoice details.</p>
        </div>

        <!-- Invoice Card -->
        <div class="card success-card">
            <!-- Header -->
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-0">Haya Collection</h2>
                        <p class="mb-0 small">Haya Collection | Online Store</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h4 class="mb-0">INVOICE</h4>
                        <p class="mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="invoice-body">
                <!-- Order Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="primary-color mb-2">Order Details</h6>
                        <p><strong>Order ID:</strong> #<?php echo $orderId; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y h:i A', strtotime($order['created_at'])); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($order['status']); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="primary-color mb-2">Payment Details</h6>
                        <p><strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
<p><strong>Payment Status:</strong> <span class="badge bg-<?php echo strtolower($order['payment_status']) == 'success' || strtolower($order['payment_status']) == 'paid' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span></p>
                        <p><strong>Total Amount:</strong> Rs. <?php echo number_format($total, 2); ?></p>
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
                            <p class="mb-0"><?php echo htmlspecialchars($order['city']); ?> - <?php echo htmlspecialchars($order['pincode']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <h6 class="primary-color mb-3">Order Items</h6>
                <?php if (empty($orderItems)): ?>
                    <p class="text-muted">No items found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                        <?php 
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
$imagePath = isset($item['image']) && !empty($item['image']) ? invoiceImagePath($item['image']) : 'skaff1.jpg'; 
?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 12px;">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </div>
                                        </td>
                                        <td class="text-end"><?php echo intval($item['quantity']); ?></td>
                                        <td class="text-end">Rs. <?php echo number_format(floatval($item['price']), 2); ?></td>
                                        <td class="text-end fw-bold">Rs. <?php echo number_format(floatval($item['total']), 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Totals -->
                <div class="row mt-4">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="border-start ps-3">
                            <div class="d-flex justify-content-between"><strong>Total Amount:</strong> <strong>Rs. <?php echo number_format($total, 2); ?></strong></div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4 no-print">
                    <a href="javascript:window.print()" class="btn btn-main btn-lg text-white btn-print">
                        <i class="me-2">🖨️</i> Print Invoice
                    </a>
                    <a href="generate_invoice_pdf.php?order_id=<?php echo $orderId; ?>" class="btn btn-main btn-lg text-white me-3 mb-2">
                        <i class="me-2">📥</i> Download PDF Invoice
                    </a>
                    <a href="my-orders.php" class="btn btn-outline-secondary ms-2">View Orders</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

