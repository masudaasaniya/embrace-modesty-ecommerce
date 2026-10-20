<?php
// PRODUCTION-READY FPDF INVOICE GENERATOR
// NO OUTPUT BEFORE PDF - STRICT BUFFER CONTROL

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

while(ob_get_level()) ob_end_clean();
ob_start();

require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/includes/fpdf_loader.php';

session_start();

function sendError($msg, $isJson = false) {
    global $con;
    while(ob_get_level()) ob_end_clean();
    if($isJson) {
        header('Content-Type: application/json');
        echo json_encode(array('error' => $msg));
    } else {
        header('Content-Type: text/html; charset=UTF-8');
    echo '<h2>Invoice Error</h2><p>' . htmlspecialchars($msg) . '</p>';
    }
$con->close();
exit;
}

function getOrderData($orderId) {
    global $con;
    if($orderId <= 0) return false;

    $stmt = $con->prepare('SELECT * FROM orders WHERE id = ?');
    if(!$stmt) return false;
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if(!$order) return false;

    // Public invoice access enabled - no auth check required per user request

    // Order items
    $itemsStmt = $con->prepare('
        SELECT oi.*, p.name as product_name 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ');
    if(!$itemsStmt) return false;
    $itemsStmt->bind_param('i', $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    $orderItems = array();
    $total = 0;
    while($item = $itemsResult->fetch_assoc()) {
        array_push($orderItems, $item);
        $total += (float)$item['total'];
    }
    $itemsStmt->close();

return array(
        'order_id' => $orderId,
        'order' => $order,
        'items' => $orderItems,
        'total' => $total
    );
}

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if($orderId <= 0) {
    if(isset($_SESSION['last_order_id'])) {
        $orderId = $_SESSION['last_order_id'];
    } else {
        sendError('Invalid order ID');
    }
}

$orderData = getOrderData($orderId);
if(!$orderData) {
    sendError('Invoice Error - Order not found');
}

$order = $orderData['order'];
$orderItems = $orderData['items'];
$total = $orderData['total'];

$filename = 'invoice_ORD' . $orderId . '_' . date('YmdHis') . '.pdf';

// Precomputed values for PHP 5.x compatibility
$order_number = isset($order['order_number']) ? $order['order_number'] : ('ORD' . $orderId);
$order_date = isset($order['created_at']) ? $order['created_at'] : date('Y-m-d H:i:s');
$order_status = isset($order['status']) ? $order['status'] : 'Pending';

$customer_name = isset($order['full_name']) ? $order['full_name'] : 'N/A';
$customer_email = isset($order['email']) ? $order['email'] : 'N/A';
$customer_phone = isset($order['phone']) ? $order['phone'] : 'N/A';
$customer_address = (isset($order['address']) ? $order['address'] : '') . 
                    (isset($order['city']) ? (' , ' . $order['city']) : '') . 
                    (isset($order['pincode']) ? (' - ' . $order['pincode']) : '');


header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// STRICT NO OUTPUT - CLEAN BUFFER
if(ob_get_length()) ob_end_clean();

// ENHANCED PDF CLASS
class PDFInvoice extends FPDF {
    function Header() {
        // Store Header
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 8, 'HAYA COLLECTION', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, 'Premium Hijabs & Accessories', 0, 1, 'C');
        $this->Cell(0, 6, 'Phone: +91-XXXXXXXXXX | Email: orders@hayacollection.com', 0, 1, 'C');
        $this->Ln(5);
        
        // Invoice Title
        $this->SetFont('Arial', 'B', 22);
        $this->Cell(0, 12, 'TAX INVOICE', 0, 1, 'C');
        $this->Ln(3);
    }
    
    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 6, 'Thank you for shopping with Haya Collection | GSTIN: 1234567890123456', 0, 1, 'C');
        $this->Cell(0, 6, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// GENERATE PDF
$pdf = new PDFInvoice('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(15, 20, 15);

// INVOICE INFO (Left)
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(95, 8, 'Invoice Details', 1, 0, 'L');
$pdf->Cell(95, 8, 'Order Details', 1, 1, 'L');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(25, 6, 'Invoice #:', 0, 0);
$pdf->Cell(70, 6, $order_number, 0, 1);
$pdf->Cell(25, 6, 'Date:', 0, 0);
$pdf->Cell(70, 6, date('d M Y'), 0, 1);
$pdf->Cell(95, 24, '', 1, 0, 'L'); // Spacer

// ORDER INFO (Right)  
$pdf->SetXY(110, 51);
$pdf->Cell(25, 6, 'Order ID:', 0, 0);
$pdf->Cell(70, 6, '#' . $orderId, 0, 1);
$pdf->Cell(25, 6, 'Date:', 0, 0);
$pdf->Cell(70, 6, $order_date, 0, 1);
$pdf->Cell(25, 6, 'Status:', 0, 0);
$pdf->Cell(70, 6, $order_status, 0, 1);

// CUSTOMER DETAILS
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 8, 'Bill To:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 6, 'Name:', 0, 0);
$pdf->Cell(110, 6, $customer_name, 0, 1);
$pdf->Cell(40, 6, 'Email:', 0, 0);
$pdf->Cell(110, 6, $customer_email, 0, 1);
$pdf->Cell(40, 6, 'Phone:', 0, 0);
$pdf->Cell(110, 6, $customer_phone, 0, 1);
$pdf->Cell(40, 6, 'Address:', 0, 0);
$pdf->Cell(150, 6, $customer_address, 0, 1);

// ITEMS TABLE
$y = $pdf->GetY() + 10;
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(240,240,240);
$pdf->Cell(10, 8, '#', 1, 0, 'C', true);
$pdf->Cell(90, 8, 'Product', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Qty', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Rate', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Total', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$itemNum = 1;
foreach($orderItems as $item) {
    $product_name = isset($item['product_name']) ? $item['product_name'] : 'N/A';
    $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
    $price = isset($item['price']) ? $item['price'] : 0;
    $line_total = isset($item['total']) ? $item['total'] : 0;
    $pdf->Cell(10, 7, $itemNum++, 1, 0, 'C');
    $pdf->Cell(90, 7, $product_name, 1, 0, 'L');
    $pdf->Cell(20, 7, $quantity, 1, 0, 'C');
    $pdf->Cell(35, 7, 'Rs ' . number_format($price, 2), 1, 0, 'R');
    $pdf->Cell(35, 7, 'Rs ' . number_format($line_total, 2), 1, 1, 'R');
}

// TOTALS
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(220,220,220);
$pdf->Cell(140, 10, '', 0, 0);
$pdf->Cell(35, 10, 'Grand Total', 1, 0, 'R', true);
$pdf->Cell(15, 10, 'Rs ' . number_format($total, 2), 1, 1, 'R', true);

ob_end_clean();

$pdf->Output('D', $filename);
ob_end_flush();

$con->close();
exit(0);

