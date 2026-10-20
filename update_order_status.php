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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_orders.php");
    exit;
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$orderStatus = isset($_POST['order_status']) ? trim($_POST['order_status']) : '';
$deliveryDate = isset($_POST['delivery_date']) ? trim($_POST['delivery_date']) : '';
$allowed = array('Processing','Approved','Shipped','Delivered','Rejected');

if ($orderId <= 0 || !in_array($orderStatus, $allowed, true)) {
    header("Location: admin_orders.php");
    exit;
}

$hasOrderStatus = columnExists($con, 'orders', 'order_status');
$hasStatus = columnExists($con, 'orders', 'status');
$hasDeliveryDate = columnExists($con, 'orders', 'delivery_date');

if ($hasOrderStatus && $hasStatus) {
    $sql = "UPDATE orders SET order_status = ?, status = ?, delivery_date = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssi", $orderStatus, $orderStatus, $deliveryDate, $orderId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
} elseif ($hasOrderStatus) {
    $sql = "UPDATE orders SET order_status = ?, delivery_date = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $orderStatus, $deliveryDate, $orderId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
} elseif ($hasStatus) {
    $sql = "UPDATE orders SET status = ?, delivery_date = ? WHERE id = ?"; 
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $orderStatus, $deliveryDate, $orderId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

if ($hasDeliveryDate && $orderStatus === 'Delivered') {
    $stmt = mysqli_prepare($con, "UPDATE orders SET delivery_date = CURDATE() WHERE id = ? AND (delivery_date IS NULL OR delivery_date = '')");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $orderId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header("Location: admin_orders.php");
exit;
?>
