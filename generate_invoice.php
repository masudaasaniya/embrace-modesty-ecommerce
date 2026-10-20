<?php
ob_start();
@session_start();
@include("connect.php");

require_once __DIR__ . '/includes/fpdf_loader.php';

if(!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || $_GET['order_id'] <= 0) {
    header("Location: index.php");
    exit;
}
$order_id = (int)$_GET['order_id'];

// Fetch order details
$stmt = $con->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: index.php");
    exit;
}

// Fetch order items
$itemsStmt = $con->prepare("
    SELECT oi.*, COALESCE(oi.product_image, p.image) AS image, p.name as product_name 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");
    $itemsStmt->bind_param("i", $order_id);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$orderItems = array();
$total = 0;
while ($item = $itemsResult->fetch_assoc()) {
    $orderItems[] = $item;\n    $total += floatval(isset($item['total']) ? $item['total'] : 0);\n}
$itemsStmt->close();

// Debug mode
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    echo "<pre>Order ID: $order_id\n";
    echo "Order: " . print_r($order, true) . "\n";
    echo "Items count: " . count($orderItems) . "\n";
    echo "Total: $total\n";
    if (empty($orderItems)) echo "WARNING: No items found!\n";
    exit;
}

// error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Temporarily disabled for debugging

class PDF extends FPDF {
    function Header() {
$this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Haya Collection',0,1,'C');
        $this->Ln(10);
    }
    function Footer() {
        $this->SetY(-15);
$this->SetFont('Arial','I',10);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

// Create PDF
$pdf = new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',20);
$pdf->Cell(0,10,'Haya Collection Invoice',0,1,'C');
$pdf->Cell(0,10,'Invoice Test',0,1);
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(60,5,'Order ID:',0,0);
$pdf->Cell(50,5,'#' . $order_id,0,1);
$pdf->Cell(60,5,'Order Number:',0,0);
$pdf->Cell(50,5, isset($order['order_number']) ? $order['order_number'] : '',0,1);
$pdf->Cell(60,5,'Date:',0,0);
$pdf->Cell(50,5, date('d M Y h:i A', strtotime(isset($order['created_at']) ? $order['created_at'] : 'now')),0,1);
$pdf->Cell(60,5,'Status:',0,0);
$pdf->Cell(50,5, isset($order['status']) ? $order['status'] : '',0,1);
$pdf->Cell(60,5,'Payment:',0,0);
$pdf->Cell(50,5, isset($order['payment_method']) ? $order['payment_method'] : '',0,1);

$pdf->Ln(10);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8,'Customer Details',0,1,'L');
$pdf->SetFont('Arial','',12);
$pdf->Cell(40,5,'Name:',0,0);
$pdf->Cell(0,5,isset($order['full_name']) ? $order['full_name'] : '',0,1);
$pdf->Cell(40,5,'Email:',0,0);
$pdf->Cell(0,5,isset($order['email']) ? $order['email'] : '',0,1);
$pdf->Cell(40,5,'Phone:',0,0);
$pdf->Cell(0,5,isset($order['phone']) ? $order['phone'] : '',0,1);
$pdf->Cell(40,5,'Address:',0,0);
$pdf->Cell(0,5,(isset($order['address']) ? $order['address'] : '') . ', ' . (isset($order['city']) ? $order['city'] : '') . ' - ' . (isset($order['pincode']) ? $order['pincode'] : ''),0,1);

$pdf->Ln(10);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8,'Order Items',0,1,'L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(80,6,'Product',1,0,'C');
$pdf->Cell(25,6,'Qty',1,0,'C');
$pdf->Cell(35,6,'Price',1,0,'C');
$pdf->Cell(35,6,'Total',1,1,'C');

$pdf->SetFont('Arial','',10);
if (!empty($orderItems)) {
    foreach ($orderItems as $item) {
        $pdf->Cell(80,6,(isset($item['product_name']) ? $item['product_name'] : 'N/A'),1,0,'L');
        $pdf->Cell(25,6,(isset($item['quantity']) ? $item['quantity'] : 0),1,0,'C');
        $pdf->Cell(35,6,'Rs. '.number_format((isset($item['price']) ? $item['price'] : 0),2),1,0,'R');
        $pdf->Cell(35,6,'Rs. '.number_format((isset($item['total']) ? $item['total'] : 0),2),1,1,'R');
    }
} else {
    $pdf->Cell(190,10,'No items found for this order',1,1,'C');
}

$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(140,8,'',0,0);
$pdf->Cell(35,8,'Grand Total:',1,0,'R');
$pdf->Cell(25,8,'Rs. '.number_format($total,2),1,1,'R');

if (ob_get_length()) {
    ob_clean();
}
$pdf->Output('D','invoice_' . $order_id . '.pdf');
ob_end_clean();
exit;
?>

