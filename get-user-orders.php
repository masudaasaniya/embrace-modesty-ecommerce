<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Login required'));
    exit;
}

include("connect.php");
require_once("order_schema_helper.php");

ensureOrderManagementSchema($con);

$userId = intval($_SESSION['user_id']);

$hasOrderDate = false;
$orderDateCheck = $con->query("SHOW COLUMNS FROM orders LIKE 'order_date'");
if ($orderDateCheck && $orderDateCheck->num_rows > 0) {
    $hasOrderDate = true;
}
$dateExpr = $hasOrderDate ? 'order_date' : 'created_at';

$hasDeliveryDate = false;
$deliveryDateCheck = $con->query("SHOW COLUMNS FROM orders LIKE 'delivery_date'");
if ($deliveryDateCheck && $deliveryDateCheck->num_rows > 0) {
    $hasDeliveryDate = true;
}
$deliveryDateExpr = $hasDeliveryDate ? 'delivery_date' : 'NULL';

$sql = "SELECT 
            id,
            COALESCE(order_number, CONCAT('ORD-', LPAD(id, 6, '0'))) AS order_number,
            COALESCE(total_price, total, 0) AS total_price,
            status,
            payment_status,
            COALESCE(admin_response, '') AS admin_response,
            " . $deliveryDateExpr . " AS delivery_date,
            " . $dateExpr . " AS created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY " . $dateExpr . " DESC, id DESC";

$stmt = $con->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Unable to load orders'));
    exit;
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$statusClassMap = array(
    'Pending' => 'badge-warning',
    'Accepted' => 'badge-primary',
    'Processing' => 'badge-info',
    'Shipped' => 'badge-dark',
    'Delivered' => 'badge-success',
    'Rejected' => 'badge-danger'
);

$orders = array();
while ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    $adminMessage = $row['admin_response'];

    if ($status === 'Accepted' && $adminMessage === '') {
        $adminMessage = '✅ Your order has been accepted.';
    } elseif ($status === 'Rejected' && $adminMessage === '') {
        $adminMessage = '❌ Your order has been rejected.';
    }

        $orders[] = array(
        'id' => intval($row['id']),
        'order_number' => $row['order_number'],
        'total_price' => number_format((float)$row['total_price'], 2, '.', ''),
        'status' => $status,
        'status_class' => isset($statusClassMap[$status]) ? $statusClassMap[$status] : 'badge-secondary',
        'admin_response' => $adminMessage,
        'payment_status' => isset($row['payment_status']) ? $row['payment_status'] : 'Pending',
        'created_at' => $row['created_at'],
        'delivery_date' => $row['delivery_date']
    );

}

$stmt->close();
echo json_encode(array('success' => true, 'orders' => $orders));
