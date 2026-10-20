<?php
session_start();
include("connect.php");

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: loginform.php");
    exit;
}

function columnExists($con, $table, $column) {
    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $sql = "SHOW COLUMNS FROM `" . $safeTable . "` LIKE '" . $safeColumn . "'";
    $res = mysqli_query($con, $sql);
    return ($res && mysqli_num_rows($res) > 0);
}

$hasOrderStatus = columnExists($con, 'orders', 'order_status');
$hasPaymentStatus = columnExists($con, 'orders', 'payment_status');
$hasDeliveryDate = columnExists($con, 'orders', 'delivery_date');
$hasOrderDate = columnExists($con, 'orders', 'order_date');
$hasProductImage = columnExists($con, 'order_items', 'product_image');

$orderStatusExpr = $hasOrderStatus ? 'o.order_status' : 'o.status';
$paymentStatusExpr = $hasPaymentStatus ? "o.payment_status" : "CASE WHEN o.payment_method = 'Cash on Delivery' THEN 'COD' ELSE 'Paid' END";
$deliveryDateExpr = $hasDeliveryDate ? 'o.delivery_date' : 'NULL';
$orderDateExpr = $hasOrderDate ? 'o.order_date' : 'o.created_at';
$productImageExpr = $hasProductImage ? 'oi.product_image' : 'p.image';

$sql = "SELECT
            o.id,
            o.user_id,
            COALESCE(o.order_number, CONCAT('ORD-', LPAD(o.id, 6, '0'))) AS order_number,
            COALESCE(o.full_name, CONCAT('User #', o.user_id)) AS customer_name,
            o.phone AS contact,
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
        ORDER BY " . $orderDateExpr . " DESC, o.id DESC, oi.id ASC";

$result = mysqli_query($con, $sql);

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
    <title>Admin Orders - Haya Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f8f9fa; }
        .wrap { max-width:1300px; margin:30px auto; }
        .item-image { width:60px; height:60px; object-fit:cover; border-radius:8px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Admin Order Management</h3>
        <a href="myaccount2.php" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Order No</th>
                            <th>User</th>
                            <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
<th>Delivery Date</th>

                            <th>Order Date</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo intval($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><img class="item-image" src="<?php echo htmlspecialchars(imagePath(isset($row['product_image']) ? $row['product_image'] : '')); ?>" alt="Product"></td>
                            <td><?php echo htmlspecialchars(isset($row['product_name']) ? $row['product_name'] : '-'); ?></td>
                            <td><?php echo intval(isset($row['quantity']) ? $row['quantity'] : 0); ?></td>
                            <td>Rs. <?php echo number_format(floatval(isset($row['price']) ? $row['price'] : 0), 2); ?></td>
                            <td>Rs. <?php echo number_format(floatval($row['total_price']), 2); ?></td>
                            <td><?php echo htmlspecialchars(isset($row['payment_status']) ? $row['payment_status'] : '-'); ?></td>
                            <td><?php echo htmlspecialchars(isset($row['order_status']) ? $row['order_status'] : '-'); ?></td>
<input type="date" name="delivery_date" value="<?php echo htmlspecialchars($row['delivery_date'] ?: ''); ?>">
                            <td><?php echo htmlspecialchars(isset($row['order_date']) ? $row['order_date'] : '-'); ?></td>
                            <td>
                                <form method="post" action="update_order_status.php" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?php echo intval($row['id']); ?>">
                                    <select name="order_status" class="form-select form-select-sm" required>
                                        <?php
                                        $statuses = array('Processing','Approved','Shipped','Delivered','Rejected');
                                        foreach ($statuses as $s):
                                        ?>
                                        <option value="<?php echo $s; ?>" <?php echo (isset($row['order_status']) && $row['order_status'] === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="13" class="text-center p-4">No orders found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
