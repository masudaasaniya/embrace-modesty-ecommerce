<?php
/**
 * Order Management - List
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Orders';
$currentPage = 'orders';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

$hasOrderDate = false;
$orderDateCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_date'");
if ($orderDateCheck && $orderDateCheck->num_rows > 0) {
    $hasOrderDate = true;
}
$dateColumn = $hasOrderDate ? 'o.order_date' : 'o.created_at';

// Search and filters
$search = sanitize(isset($_GET['search']) ? $_GET['search'] : '');
$statusFilter = sanitize(isset($_GET['status']) ? $_GET['status'] : '');
$dateFrom = sanitize(isset($_GET['date_from']) ? $_GET['date_from'] : '');
$dateTo = sanitize(isset($_GET['date_to']) ? $_GET['date_to'] : '');

// Build query
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        LEFT JOIN user u ON o.user_id = u.id 
        WHERE 1=1";
$params = array();
$types = "";

if ($search) {
    $sql .= " AND (o.id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= "sss";
}

if ($statusFilter) {
    $sql .= " AND o.status = ?";
    $params[] = &$statusFilter;
    $types .= "s";
}

if ($dateFrom) {
    $sql .= " AND DATE(" . $dateColumn . ") >= ?";
    $params[] = &$dateFrom;
    $types .= "s";
}

if ($dateTo) {
    $sql .= " AND DATE(" . $dateColumn . ") <= ?";
    $params[] = &$dateTo;
    $types .= "s";
}

$sql .= " ORDER BY " . $dateColumn . " DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $bindParams = array();
        $bindParams[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bindParams[] = &$params[$i];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bindParams);
    }
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = false;
}

// Get status counts
$statusCounts = array(
    'all' => getCount('orders'),
    'Pending' => getCount("orders", "status = 'Pending'"),
    'Confirmed' => getCount("orders", "status = 'Confirmed'"),
    'Shipped' => getCount("orders", "status = 'Shipped'"),
    'Delivered' => getCount("orders", "status = 'Delivered'"),
    'Cancelled' => getCount("orders", "status = 'Cancelled'")
);
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Orders</h2>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter == '' ? 'active' : ''; ?>" href="?">All (<?php echo $statusCounts['all']; ?>)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter == 'Pending' ? 'active' : ''; ?>" href="?status=Pending">Pending (<?php echo $statusCounts['Pending']; ?>)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter == 'Confirmed' ? 'active' : ''; ?>" href="?status=Confirmed">Confirmed (<?php echo $statusCounts['Confirmed']; ?>)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter == 'Shipped' ? 'active' : ''; ?>" href="?status=Shipped">Shipped (<?php echo $statusCounts['Shipped']; ?>)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter == 'Delivered' ? 'active' : ''; ?>" href="?status=Delivered">Delivered (<?php echo $statusCounts['Delivered']; ?>)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter == 'Cancelled' ? 'active' : ''; ?>" href="?status=Cancelled">Cancelled (<?php echo $statusCounts['Cancelled']; ?>)</a>
    </li>
</ul>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <?php if ($statusFilter): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <?php endif; ?>
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Order ID, Customer..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <a href="?" class="btn btn-secondary w-100">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders && $orders->num_rows > 0): ?>
                        <?php while ($order = $orders->fetch_assoc()): 
                            
                            // Fix total if zero or null - calculate from order_items
                            if (isset($order['total']) && floatval($order['total']) <= 0) {
                                $totalStmt = $conn->prepare("SELECT COALESCE(SUM(price * quantity), 0) as total FROM order_items WHERE order_id = ?");
                                if ($totalStmt) {
                                    $totalStmt->bind_param("i", $order['id']);
                                    $totalStmt->execute();
                                    $totalResult = $totalStmt->get_result();
                                    if ($totalRow = $totalResult->fetch_assoc()) {
                                        $order['total'] = $totalRow['total'];
                                    }
                                    $totalStmt->close();
                                }
                            }
                            // Get order items count
                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                            $itemsCount = 1;
                            if ($stmt) {
                                $stmt->bind_param("i", $order['id']);
                                if ($stmt->execute()) {
                                    $itemsResult = $stmt->get_result();
                                    if ($itemsResult) {
                                        $itemsRow = $itemsResult->fetch_assoc();
                                        if ($itemsRow && isset($itemsRow['count'])) {
                                            $itemsCount = $itemsRow['count'];
                                        }
                                    }
                                }
                                $stmt->close();
                            }
                             
                            // If no items in order_items, show 1 (single item orders)
                            if ($itemsCount == 0) $itemsCount = 1;
                        ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td>
                                <?php if ($order['username']): ?>
                                    <strong><?php echo htmlspecialchars($order['username']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars(isset($order['email']) ? $order['email'] : ''); ?></small>
                                <?php else: ?>
                                    <strong>Guest</strong>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $itemsCount; ?> item(s)</td>
                            <td><strong>₹<?php echo number_format($order['total'], 2); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['payment_method']); ?>
                                <br>
                                <span class="badge <?php echo $order['status'] == 'Paid' || $order['status'] == 'Delivered' ? 'badge-active' : 'badge-pending'; ?>">
                                    <?php echo $order['status'] == 'Paid' || in_array($order['status'], array('Confirmed', 'Shipped', 'Delivered')) ? 'Paid' : 'Unpaid'; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                if ($order['status'] == 'Pending') $statusClass = 'badge-pending';
                                elseif ($order['status'] == 'Confirmed') $statusClass = 'badge-confirmed';
                                elseif ($order['status'] == 'Shipped') $statusClass = 'badge-shipped';
                                elseif ($order['status'] == 'Delivered') $statusClass = 'badge-delivered';
                                elseif ($order['status'] == 'Cancelled') $statusClass = 'badge-cancelled';
                                else $statusClass = 'badge-pending';
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo formatDate(isset($order['order_date']) ? $order['order_date'] : (isset($order['created_at']) ? $order['created_at'] : date('Y-m-d H:i:s'))); ?>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" title="Invoice" target="_blank">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

