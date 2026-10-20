<?php
/**
 * Fixed PDF Invoice Generator
 * Forces download, consistent fonts, bulletproof output buffering
 * Usage: generate_invoice_pdf.php?order_id=27
 */

session_start();

// CRITICAL: Suppress ALL output/errors before anything
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Aggressive early buffer cleaning
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/includes/fpdf_loader.php';

function sendError($msg, $isJson = false) {
    global $con;
    while (ob_get_level()) ob_end_clean();
    
    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $msg]);
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        header('HTTP/1.1 400 Bad Request');
        ?>
<!DOCTYPE html>
<html>
<head><title>Invoice Error</title>
<style>body{font-family:Arial;max-width:500px;margin:50px auto;padding:20px;background:#f8f9fa;border:1px solid #ddd;border-radius:8px;}</style>
</head>
<body>
<h2>❌ Invoice Error</h2>
<p><?php echo htmlspecialchars($msg); ?></p>
<p><a href="my-orders.php">← Back to Orders</a></p>
</body></html>
        <?php
    }
    if (isset($con)) $con->close();
    exit;
}

function getOrderData($orderId) {
    global $con;
    
    if ($orderId <= 0 || !is_numeric($orderId)) {
        return false;
    }
    
    // Order
    $stmt = $con->prepare("SELECT * FROM orders WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if (!$order) return false;
    
    // Auth check
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        return false;
    }
    if (!isset($_SESSION['admin_id']) && $order['user_id'] != $_SESSION['user_id']) {
        return false;
    }
    
    // Items
    $itemsStmt = $con->prepare("
        SELECT oi.*, p.name as product_name 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    if (!$itemsStmt) return false;
    $itemsStmt->bind_param("i", $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    $orderItems = [];
    $total = 0;
    while ($item = $itemsResult->fetch_assoc()) {
        $orderItems[] = $item;
        $total += (float)$item['total'];
    }
    $itemsStmt->close();
    
    return [
        'order_id' => $orderId,
        'order' => $order,
        'items' => $orderItems,
        'total' => $total
    ];
}

// Get order_id
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($orderId <= 0) {
    sendError('Invalid or missing order_id parameter');
}

$orderData = getOrderData($orderId);
if (!$orderData) {
    sendError('Order not found or access denied');
}

$order = $orderData['order'];
$orderItems = $orderData['items'];
$total = $orderData['total'];

// CONSISTENT ARIAL FONTS ONLY (no Helvetica issues)
class PDFInvoice extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, 'Haya Collection Invoice', 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | Thank you for shopping with us!', 0, 0, 'C');
    }
}

// FINAL BUFFER CLEAN & HEADERS CHECK
while (ob_get_level()) {
    ob_end_clean();
}

if (headers_sent($file, $line)) {
    sendError("Cannot send PDF - headers already sent in {$file}:{$line}", true);
}

// PDF HEADERS (CRITICAL FOR DOWNLOAD)
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="invoice-' . $order['order_number'] . '.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
header('Expires: 0');

// Create & Output PDF
$pdf = new PDFInvoice('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Order Info
$pdf->Cell(60, 5, 'Order ID:', 0, 0);
$pdf->Cell(50, 5, '#' . $orderData['order_id'], 0, 1);
$pdf->Cell(60, 5, 'Order Number:', 0, 0);
$pdf->Cell(50, 5, $order['order_number'], 0, 1);
$pdf->Cell(60, 5, 'Date:', 0, 0);
$pdf->Cell(50, 5, date('d M Y h:i A', strtotime($order['created_at'])), 0, 1);
$pdf->Cell(60, 5, 'Status:', 0, 0);
$pdf->Cell(50, 5, ucfirst($order['status']), 0, 1);
$pdf->Cell(60, 5, 'Payment:', 0, 0);
$pdf->Cell(50, 5, ucfirst($order['payment_method']), 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'Customer Details', 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 5, 'Name:', 0, 0);
$pdf->Cell(0, 5, $order['full_name'], 0, 1);
$pdf->Cell(40, 5, 'Email:', 0, 0);
$pdf->Cell(0, 5, $order['email'], 0, 1);
$pdf->Cell(40, 5, 'Phone:', 0, 0);
$pdf->Cell(0, 5, $order['phone'], 0, 1);
$pdf->Cell(40, 5, 'Address:', 0, 0);
$pdf->Cell(0, 5, $order['address'] . ', ' . $order['city'] . ' - ' . $order['pincode'], 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'Order Items', 0, 1, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(80, 6, 'Product', 1, 0, 'C');
$pdf->Cell(25, 6, 'Qty', 1, 0, 'C');
$pdf->Cell(35, 6, 'Price', 1, 0, 'C');
$pdf->Cell(35, 6, 'Total', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);
foreach ($orderItems as $item) {
    $pdf->Cell(80, 6, $item['product_name'], 1, 0, 'L');
    $pdf->Cell(25, 6, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(35, 6, 'Rs. ' . number_format($item['price'], 2), 1, 0, 'R');
    $pdf->Cell(35, 6, 'Rs. ' . number_format($item['total'], 2), 1, 1, 'R');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(140, 8, '', 0, 0);
$pdf->Cell(35, 8, 'Grand Total:', 1, 0, 'R');
$pdf->Cell(25, 8, 'Rs. ' . number_format($total, 2), 1, 1, 'R');

$filename = "invoice-" . $order['order_number'] . ".pdf";
$pdf->Output('D', $filename);

$con->close();
exit;
?>

