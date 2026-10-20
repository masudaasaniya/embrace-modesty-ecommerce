<?php
session_start();

if (!isset($_SESSION['last_order_id']) || !isset($_SESSION['last_order_number'])) {
    header("Location: shoppage2.php");
    exit;
}

$orderId = intval($_SESSION['last_order_id']);
$orderNumber = $_SESSION['last_order_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Placed - Embracing Modesty</title>
<link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f8f3f4;font-family:Arial,sans-serif}
.box{max-width:640px;margin:60px auto;background:#fff;border-radius:16px;padding:36px;box-shadow:0 10px 24px rgba(122,62,62,.12)}
.title{color:#7a3e3e;font-weight:700}
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
<div class="box text-center">
    <h2 class="title mb-3">✅ Order inserted successfully.</h2>
    <p class="mb-1"><strong>Order ID:</strong> #<?php echo $orderId; ?></p>
    <p class="mb-3"><strong>Order Number:</strong> <?php echo htmlspecialchars($orderNumber); ?></p>
    <p class="text-muted">Thank you for shopping with Embracing Modesty.</p>
    <div class="mt-4">
        <a href="my-orders.php" class="btn btn-primary">View My Orders</a>
        <a href="shoppage2.php" class="btn btn-outline-secondary">Continue Shopping</a>
    </div>
</div>
</body>
</html>
