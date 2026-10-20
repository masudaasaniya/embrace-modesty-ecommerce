<?php
/**
 * Customer Details
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Customer Details';
$currentPage = 'customers';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get customer ID
$customerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get customer data
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$customer = false;
if ($stmt) {
    $stmt->bind_param("i", $customerId);
    if ($stmt->execute()) {
        $customerResult = $stmt->get_result();
        if ($customerResult) {
            $customer = $customerResult->fetch_assoc();
        }
    }
    $stmt->close();
}

if (!$customer) {
    showAlert('Customer not found', 'danger');
    header('Location: index.php');
    exit;
}

// Get customer orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$orders = false;
if (!$stmt) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
}
if ($stmt) {
    $stmt->bind_param("i", $customerId);
    if ($stmt->execute()) {
        $orders = $stmt->get_result();
    }
    $stmt->close();
}
if (!$orders) {
    // Empty result fallback
    $orders = $conn->query("SELECT * FROM orders WHERE 1=0");
}

// Get order statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_orders, SUM(total) as total_spent FROM orders WHERE user_id = ? AND status != 'Cancelled'");
$stats = array('total_orders' => 0, 'total_spent' => 0);
if ($stmt) {
    $stmt->bind_param("i", $customerId);
    if ($stmt->execute()) {
        $statsResult = $stmt->get_result();
        if ($statsResult) {
            $row = $statsResult->fetch_assoc();
            if ($row) {
                $stats = $row;
            }
        }
    }
    $stmt->close();
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Customer Details</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Customers
    </a>
</div>

<!-- Customer Info -->
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-5x" style="color: var(--primary-color);"></i>
                </div>
                <h4><?php echo htmlspecialchars($customer['username']); ?></h4>
                <p class="text-muted">Customer ID: #<?php echo $customer['id']; ?></p>
                <p><?php echo htmlspecialchars(isset($customer['email']) ? $customer['email'] : 'No email'); ?></p>
                <p><?php echo htmlspecialchars(isset($customer['phone']) ? $customer['phone'] : 'No phone'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?php echo number_format(isset($stats['total_orders']) ? $stats['total_orders'] : 0); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-value">Rs.<?php echo number_format(isset($stats['total_spent']) ? $stats['total_spent'] : 0, 2); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Order History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Order History</h5>
            </div>
            <div class="card-body p-0">
                <?php if ($orders->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><a href="../orders/view.php?id=<?php echo $order['id']; ?>">#<?php echo $order['id']; ?></a></td>
                                <td>Rs.<?php echo number_format($order['total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
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
                                <td><?php echo formatDate(isset($order['order_date']) ? $order['order_date'] : (isset($order['created_at']) ? $order['created_at'] : date('Y-m-d H:i:s'))); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <p class="mb-0">No orders yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
