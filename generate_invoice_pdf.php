<?php
session_start();

// Clean ALL buffers
while (ob_get_level()) ob_end_clean();
ob_start();

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include('connect.php');
require_once __DIR__ . '/includes/fpdf_loader.php';

/* ---------- ERROR HANDLER ---------- */
function sendError($msg, $isJson = false) {
    global $con;

    while (ob_get_level()) ob_end_clean();

    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(array('error' => $msg)); // FIXED
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        header('HTTP/1.1 400 Bad Request');
        ?>
        <!DOCTYPE html>
        <html>
        <head><title>Invoice Error</title></head>
        <body>
            <h2>Invoice Error</h2>
            <p><?php echo htmlspecialchars($msg); ?></p>
        </body>
        </html>
        <?php
    }

    if (isset($con)) $con->close();
    exit;
}

/* ---------- GET ORDER DATA ---------- */
function getOrderData($orderId) {
    global $con;

    if ($orderId <= 0) return false;

    $stmt = $con->prepare("SELECT * FROM orders WHERE id = ?");
    if (!$stmt) return false;

    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order) return false;

    // Auth check
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) return false;
    if (!isset($_SESSION['admin_id']) && $order['user_id'] != $_SESSION['user_id']) return false;

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

    $orderItems = array(); // FIXED
    $total = 0;

    while ($item = $itemsResult->fetch_assoc()) {
        $orderItems[] = $item;
        $total += (float)$item['total'];
    }

    $itemsStmt->close();

    return array( // FIXED
        'order_id' => $orderId,
        'order' => $order,
        'items' => $orderItems,
        'total' => $total
    );
}

/* ---------- MAIN ---------- */
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($orderId <= 0) {
    if (isset($_SESSION['last_order_id'])) {
        $orderId = $_SESSION['last_order_id'];
    } else {
        sendError('Invalid order ID');
    }
}

$orderData = getOrderData($orderId);
if (!$orderData) {
    sendError('Order not found or access denied');
}

$order = $orderData['order'];
$orderItems = $orderData['items'];
$total = $orderData['total'];

/* ---------- PDF CLASS ---------- */
class PDFInvoice extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, 'Haya Collection Invoice', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

/* ---------- CREATE PDF ---------- */
$pdf = new PDFInvoice('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Order Info
$pdf->Cell(60, 5, 'Order ID:', 0, 0);
$pdf->Cell(50, 5, '#' . $orderData['order_id'], 0, 1);

$pdf->Cell(60, 5, 'Order Number:', 0, 0);
$pdf->Cell(50, 5, isset($order['order_number']) ? $order['order_number'] : '-', 0, 1);

$pdf->Cell(60, 5, 'Date:', 0, 0);
$pdf->Cell(50, 5, date('d M Y h:i A', strtotime($order['created_at'])), 0, 1);

$pdf->Cell(60, 5, 'Status:', 0, 0);
$pdf->Cell(50, 5, ucfirst($order['status']), 0, 1);

$pdf->Cell(60, 5, 'Payment:', 0, 0);
$pdf->Cell(50, 5, ucfirst($order['payment_method']), 0, 1);

// Customer
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0,8,'Customer Details',0,1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40,5,'Name:',0,0);
$pdf->Cell(0,5,$order['full_name'],0,1);

$pdf->Cell(40,5,'Email:',0,0);
$pdf->Cell(0,5,$order['email'],0,1);

$pdf->Cell(40,5,'Phone:',0,0);
$pdf->Cell(0,5,$order['phone'],0,1);

$pdf->Cell(40,5,'Address:',0,0);
$pdf->Cell(0,5,$order['address'].' '.$order['city'].' '.$order['pincode'],0,1);

// Items
$pdf->Ln(10);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(80,6,'Product',1);
$pdf->Cell(25,6,'Qty',1);
$pdf->Cell(35,6,'Price',1);
$pdf->Cell(35,6,'Total',1,1);

$pdf->SetFont('Arial','',10);
foreach ($orderItems as $item) {
    $pdf->Cell(80,6,$item['product_name'],1);
    $pdf->Cell(25,6,$item['quantity'],1);
    $pdf->Cell(35,6,'Rs '.number_format($item['price'],2),1);
    $pdf->Cell(35,6,'Rs '.number_format($item['total'],2),1,1);
}

// Total
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(140,8,'',0);
$pdf->Cell(35,8,'Grand Total:',1);
$pdf->Cell(25,8,'Rs '.number_format($total,2),1,1);

/* ---------- OUTPUT ---------- */
while (ob_get_level()) ob_end_clean();

$filename = "invoice-" . (isset($order['order_number']) ? $order['order_number'] : $orderId) . ".pdf";

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="invoice.pdf"');

ob_clean();
flush();
$pdf->Output('I', $filename);

$con->close();
exit;
?>