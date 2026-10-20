<?php
session_start();
include('connect.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: loginform.php');
    exit;
}

function columnExists($con, $table, $column) {
    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $sql = "SHOW COLUMNS FROM `" . $safeTable . "` LIKE '" . $safeColumn . "'";
    $res = mysqli_query($con, $sql);
    return ($res && mysqli_num_rows($res) > 0);
}

$userId = intval($_SESSION['user_id']);

$hasOrderStatus = columnExists($con, 'orders', 'order_status');
$hasPaymentStatus = columnExists($con, 'orders', 'payment_status');
$hasDeliveryDate = columnExists($con, 'orders', 'delivery_date');
$hasOrderDate = columnExists($con, 'orders', 'order_date');
$hasProductImage = columnExists($con, 'order_items', 'product_image');

// Use product_image from order_items if available, otherwise from products table
$productImageExpr = $hasProductImage ? 'oi.product_image' : 'p.image';

$orderStatusExpr = $hasOrderStatus ? 'o.order_status' : 'o.status';
$paymentStatusExpr = $hasPaymentStatus ? "o.payment_status" : "CASE WHEN o.payment_method = 'Cash on Delivery' THEN 'COD' ELSE 'Paid' END";
$deliveryDateExpr = $hasDeliveryDate ? 'o.delivery_date' : 'NULL';
$orderDateExpr = $hasOrderDate ? 'o.order_date' : 'o.created_at';

$sql = "SELECT
            o.id,
            COALESCE(o.order_number, CONCAT('ORD-', LPAD(o.id, 6, '0'))) AS order_number,
            COALESCE(o.total_price, o.total, 0) AS total_price,
            " . $paymentStatusExpr . " AS payment_status,
            " . $orderStatusExpr . " AS order_status,
            " . $deliveryDateExpr . " AS delivery_date,
            " . $orderDateExpr . " AS order_date,
            oi.product_name,
            oi.quantity,
            oi.price,
            " . $productImageExpr . " AS product_image
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE o.user_id = ?
        ORDER BY " . $orderDateExpr . " DESC, o.id DESC, oi.id ASC";

$stmt = mysqli_prepare($con, $sql);
$orders = array();
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

function imagePath($raw) {
    $raw = trim((string)$raw);
    if ($raw === '') return 'skaff1.jpg';
    $candidates = array(
        'admin/uploads/products/' . ltrim($raw, '/\\'),
        'admin/uploads/' . ltrim($raw, '/\\'),
        'uploads/products/' . ltrim($raw, '/\\'),
        'uploads/' . ltrim($raw, '/\\'),
        $raw
    );
    foreach ($candidates as $c) {
        if (file_exists($c)) return $c;
    }
    return 'skaff1.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Embrace Modesty</title>
    <link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .page-wrap { max-width: 1250px; margin: 30px auto; }
        .table td, .table th { vertical-align: middle; }
        .item-image { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .orders-status { color:#333; font-weight:600; }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">My Orders</h3>
        <a href="myaccount.php" class="btn btn-outline-secondary btn-sm">Back to Account</a>
    </div>

    <?php if (isset($_GET['placed']) && $_GET['placed'] == '1'): ?>
        <div class="alert alert-success">Order inserted successfully.</div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order No</th>
                            <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Delivery Date</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_number']); ?></td>
                            <td><img class="item-image" src="<?php echo htmlspecialchars(imagePath(isset($row['product_image']) ? $row['product_image'] : '')); ?>" alt="Product"></td>
                            <td><?php echo htmlspecialchars(isset($row['product_name']) ? $row['product_name'] : '-'); ?></td>
                            <td><?php echo intval(isset($row['quantity']) ? $row['quantity'] : 0); ?></td>
                            <td>Rs. <?php echo number_format(floatval(isset($row['price']) ? $row['price'] : 0), 2); ?></td>
                            
                            <td><?php echo htmlspecialchars(isset($row['payment_status']) ? $row['payment_status'] : '-'); ?></td>
                            <td class="orders-status"><?php echo htmlspecialchars(isset($row['order_status']) ? $row['order_status'] : '-'); ?></td>
                            <td><?php echo !empty($row['delivery_date']) ? date("d M Y", strtotime($row['delivery_date'])) : "Not Assigned"; ?></td>
                            <td><?php echo htmlspecialchars(isset($row['order_date']) ? $row['order_date'] : '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center p-4">No orders found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
