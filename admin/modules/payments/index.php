<?php
/**
 * Payment Management
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Payments';
$currentPage = 'payments';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();



$payments = $conn->query("
SELECT 
    o.id,
    u.username,
    o.total as amount,
    o.payment_method,
    CASE 
        WHEN o.status IN ('Confirmed', 'Shipped', 'Delivered') THEN 'Paid'
        WHEN o.status = 'Pending' THEN 'Pending'
        ELSE 'Unpaid'
    END as payment_status,
    o.status as order_status,
    o.created_at as order_date
FROM orders o
LEFT JOIN user u ON o.user_id = u.id
ORDER BY o.id DESC
");

// ✅ DEBUG CODE (AAHI MUKVU)
if (!$payments) {
    die("SQL ERROR: " . $conn->error);
}

//echo "ROWS: " . $payments->num_rows;
//exit;
if ($payments) {
    error_log("DEBUG Payments: " . $payments->num_rows . " rows found");
}

// Get payment statistics - using direct queries instead of getCount for complex conditions
$totalPaymentsResult = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status != 'Cancelled'");
if ($totalPaymentsResult) {
    $totalPaymentsRow = $totalPaymentsResult->fetch_assoc();
} else {
    $totalPaymentsRow = null;
}
if ($totalPaymentsRow) {
    $totalPayments = intval($totalPaymentsRow['cnt']);
} else {
    $totalPayments = 0;
}

$paidPaymentsResult = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status IN ('Confirmed', 'Shipped', 'Delivered')");
if ($paidPaymentsResult) {
    $paidPaymentsRow = $paidPaymentsResult->fetch_assoc();
} else {
    $paidPaymentsRow = null;
}
if ($paidPaymentsRow) {
    $paidPayments = intval($paidPaymentsRow['cnt']);
} else {
    $paidPayments = 0;
}

$unpaidPaymentsResult = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status = 'Pending'");
if ($unpaidPaymentsResult) {
    $unpaidPaymentsRow = $unpaidPaymentsResult->fetch_assoc();
} else {
    $unpaidPaymentsRow = null;
}
if ($unpaidPaymentsRow) {
    $unpaidPayments = intval($unpaidPaymentsRow['cnt']);
} else {
    $unpaidPayments = 0;
}

$totalRevenueResult = $conn->query("SELECT SUM(total) as sum FROM orders WHERE status != 'Cancelled'");
if ($totalRevenueResult) {
    $totalRevenueRow = $totalRevenueResult->fetch_assoc();
} else {
    $totalRevenueRow = null;
}
if ($totalRevenueRow) {
    $totalRevenue = floatval($totalRevenueRow['sum']);
} else {
    $totalRevenue = 0;
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Payment Records</h2>
</div>

<!-- Payment Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value"><?php echo number_format($totalPayments); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Paid</div>
            <div class="stat-value text-success"><?php echo number_format($paidPayments); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Unpaid</div>
            <div class="stat-value text-warning"><?php echo number_format($unpaidPayments); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
<tbody>
<?php if ($payments && $payments->num_rows > 0): ?>
    <?php while ($row = $payments->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>

        <!-- Customer -->
        <td>
            <?php echo !empty($row['username']) ? htmlspecialchars($row['username']) : 'Guest'; ?>
        </td>

        <!-- Amount -->
        <td>₹<?php echo number_format($row['amount'], 2); ?></td>

        <!-- Payment Method -->
        <td>
            <?php echo !empty($row['payment_method']) ? htmlspecialchars($row['payment_method']) : 'Cash on Delivery'; ?>
        </td>

        <!-- Payment Status -->
        <td>
            <?php 
            $payment_status = $row['payment_status'];
            $statusBadge = ($payment_status == 'Paid') ? 'badge-active' : 'badge-pending';
            ?>
            <span class="badge <?php echo $statusBadge; ?>">
                <?php echo ucfirst($payment_status); ?>
            </span>
        </td>

        <!-- Order Status -->
        <td>
            <?php
            $statusClass = 'badge-pending';
            if ($row['order_status'] == 'Confirmed') $statusClass = 'badge-confirmed';
            elseif ($row['order_status'] == 'Shipped') $statusClass = 'badge-shipped';
            elseif ($row['order_status'] == 'Delivered') $statusClass = 'badge-delivered';
            elseif ($row['order_status'] == 'Cancelled') $statusClass = 'badge-cancelled';
            ?>
            <span class="badge <?php echo $statusClass; ?>">
                <?php echo htmlspecialchars($row['order_status']); ?>
            </span>
        </td>

        <!-- Date -->
        <td><?php echo htmlspecialchars($row['order_date']); ?></td>

        <!-- Actions -->
        <td>
            <a href="../orders/view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i>
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="8" class="text-center py-4">No payment records found</td>
    </tr>
<?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

