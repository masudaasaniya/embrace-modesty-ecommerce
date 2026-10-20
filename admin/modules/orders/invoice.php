<?php
/**
 * Order Invoice
 * Embrace Modesty E-Commerce
 */

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Get order ID
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order data
$stmt = $conn->prepare("SELECT o.*, u.username, u.email, u.phone 
                        FROM orders o 
                        LEFT JOIN user u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderItems = $stmt->get_result();

// Get settings
$siteName = getSetting('site_name');
$contactEmail = getSetting('contact_email');
$contactPhone = getSetting('contact_phone');
$contactAddress = getSetting('contact_address');

// Generate invoice number
$invoiceNumber = generateInvoiceNumber($orderId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoiceNumber; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .invoice-header {
            border-bottom: 2px solid #823c49;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-title {
            color: #823c49;
            font-size: 32px;
            font-weight: bold;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        .company-info {
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #823c49;
        }
        table {
            width: 100%;
        }
        table th {
            background: #f8ecef;
            color: #823c49;
            padding: 12px;
            text-align: left;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .total-row {
            background: #f8ecef;
            font-weight: bold;
            color: #823c49;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<div class="print-btn">
    <button class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print Invoice
    </button>
    <a href="index.php" class="btn btn-secondary ms-2">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="invoice-container">
    <!-- Invoice Header -->
    <div class="invoice-header">
        <div class="row">
            <div class="col-md-6">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">Invoice #: <?php echo $invoiceNumber; ?></div>
                <div class="invoice-number">Date: <?php echo isset($order['order_date']) ? formatDate($order['order_date']) : formatDate($order['created_at']); ?></div>
            </div>
            <div class="col-md-6 company-info">
                <div class="company-name"><?php echo htmlspecialchars($siteName); ?></div>
                <div><?php echo htmlspecialchars($contactEmail); ?></div>
                <div><?php echo htmlspecialchars($contactPhone); ?></div>
                <div><?php echo htmlspecialchars($contactAddress); ?></div>
            </div>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Bill To:</h5>
            <strong>
                <?php if ($order['username']): ?>
                    <?php echo htmlspecialchars(isset($order['username']) ? $order['username'] : ''); ?><br>
                    <?php echo htmlspecialchars(isset($order['email']) ? $order['email'] : ''); ?><br>
                    <?php echo htmlspecialchars(isset($order['phone']) ? $order['phone'] : ''); ?>
                <?php else: ?>
                    Guest Customer
                <?php endif; ?>
            </strong>
        </div>
        <div class="col-md-6 text-md-end">
            <h5>Order Details:</h5>
            <strong>Order ID:</strong> #<?php echo $order['id']; ?><br>
            <strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?><br>
            <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
        </div>
    </div>

    <!-- Order Items -->
    <table class="mb-4">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orderItems->num_rows > 0): ?>
                <?php while ($item = $orderItems->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td class="text-end">₹<?php echo number_format($item['total'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Item details not available</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th class="text-end">₹<?php echo number_format($order['total'], 2); ?></th>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="text-center mt-5 pt-4 border-top">
        <p class="text-muted mb-1">Thank you for your order!</p>
        <p class="text-muted small mb-0">This is a computer-generated invoice and does not require a signature.</p>
    </div>
</div>

</body>
</html>


