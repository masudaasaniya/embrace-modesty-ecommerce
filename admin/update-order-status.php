<?php
ob_start();
session_start();

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once dirname(__DIR__) . '/order_schema_helper.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Admin login required'));
    exit;
}

ensureOrderManagementSchema($conn);

// Helper to send JSON response cleanly
function sendJson($data, $code = 200) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(array('success' => false, 'message' => 'Method not allowed'), 405);
}

// Validate CSRF token
$token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
    sendJson(array('success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.'), 403);
}

// Validate order ID
$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate status
$allowedStatuses = array('Pending', 'Accepted', 'Processing', 'Shipped', 'Delivered', 'Rejected');
if ($orderId <= 0) {
    sendJson(array('success' => false, 'message' => 'Invalid order ID'), 422);
}

if (!in_array($status, $allowedStatuses, true)) {
    sendJson(array('success' => false, 'message' => 'Invalid status value'), 422);
}

// Set admin response message based on status
$adminResponse = '';
if ($status === 'Accepted') {
    $adminResponse = '✅ Your order has been accepted by the admin.';
} elseif ($status === 'Rejected') {
    $adminResponse = '❌ Your order has been rejected by the admin.';
} elseif ($status === 'Processing') {
    $adminResponse = '📦 Your order is being processed.';
} elseif ($status === 'Shipped') {
    $adminResponse = '🚚 Your order has been shipped.';
} elseif ($status === 'Delivered') {
    $adminResponse = '✅ Your order has been delivered.';
}

// Update order status
$stmt = $conn->prepare("UPDATE orders SET status = ?, admin_response = ?, updated_at = NOW() WHERE id = ?");
if (!$stmt) {
    sendJson(array('success' => false, 'message' => 'Database error: Unable to prepare statement'), 500);
}

$stmt->bind_param("ssi", $status, $adminResponse, $orderId);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    sendJson(array('success' => false, 'message' => 'Failed to update order status'), 500);
}

// Success response
sendJson(array(
    'success' => true,
    'message' => 'Order status updated successfully',
    'status' => $status,
    'admin_response' => $adminResponse
));
