<?php
session_start();
include_once("connect.php");

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$redirect = isset($_REQUEST['redirect']) ? trim($_REQUEST['redirect']) : '';
if ($redirect === '') {
    $redirect = 'shoppage2.php';
}

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
$productId = isset($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : 0;

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit;
}
$user_id = intval($_SESSION['user_id']);
if ($action === 'add' && $productId > 0) {
    $stmt = $con->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $update = $con->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $update->bind_param("ii", $user_id, $productId);
        $update->execute();
    } else {
        $insert = $con->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $user_id, $productId);
        $insert->execute();
    }
    header("Location: " . $redirect);
    exit;
}

if ($action === 'remove' && $productId > 0) {
    unset($_SESSION['cart'][$productId]);
    header("Location: " . $redirect);
    exit;
}

if ($action === 'buy_now' && $productId > 0) {
    $_SESSION['buy_now_product_id'] = $productId;
    header("Location: checkout.php?mode=buy_now");
    exit;
}

header("Location: " . $redirect);
exit;
?>
