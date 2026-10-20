<?php
/**
 * Order Details
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Order Details';
$currentPage = 'orders';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get order ID
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order data
$stmt = $conn->prepare("SELECT o.*, o.delivery_date, u.username, u.email, u.phone 
                        FROM orders o 
                        LEFT JOIN user u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    showAlert('Order not found', 'danger');
    header('Location: index.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$orderItems = $stmt->get_result();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitize($_POST['status']);
    $deliveryDate = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : null;
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, delivery_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $newStatus, $deliveryDate, $orderId);
    
    if ($stmt->execute()) {
        showAlert('Order status and delivery date updated successfully!');
        // Refresh order data
        $stmt = $conn->prepare("SELECT o.*, o.delivery_date, u.username, u.email, u.phone 
                               FROM orders o 
                               LEFT JOIN user u ON o.user_id = u.id 
                               WHERE o.id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
    } else {
        showAlert('Error updating status.', 'danger');
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Order #<?php echo $orderId; ?></h2>
    <div>
        <a href="invoice.php?id=<?php echo $orderId; ?>" class="btn btn-info" target="_blank">
            <i class="fas fa-file-invoice me-2"></i>Print Invoice
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<div class="row">
    <!-- Order Info -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Order Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                        <p><strong>Order Date:</strong> <?php echo formatDateTime(isset($order['order_date']) ? $order['order_date'] : $order['created_at']); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Order Total:</strong> <span class="h5">₹<?php echo number_format($order['total'], 2); ?></span></p>
                        <p>
                            <strong>Status:</strong> 
                            <?php
                            $statusClass = '';
                            if ($order['status'] == 'Pending') $statusClass = 'badge-pending';
                            elseif ($order['status'] == 'Confirmed') $statusClass = 'badge-confirmed';
                            elseif ($order['status'] == 'Shipped') $statusClass = 'badge-shipped';
                            elseif ($order['status'] == 'Delivered') $statusClass = 'badge-delivered';
                            elseif ($order['status'] == 'Cancelled') $statusClass = 'badge-cancelled';
                            else $statusClass = 'badge-pending';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>Order Items</h5>
            </div>
            <div class="card-body p-0">
                <?php if ($orderItems->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $orderItems->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₹<?php echo number_format($item['total'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th>₹<?php echo number_format($order['total'], 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="card-body text-center">
                    <p class="mb-0">Order item details not available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Customer Info & Actions -->
    <div class="col-md-4">
        <!-- Customer Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Details</h5>
            </div>
            <div class="card-body">
                <?php if ($order['username']): ?>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars(isset($order['email']) ? $order['email'] : 'N/A'); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars(isset($order['phone']) ? $order['phone'] : 'N/A'); ?></p>
                <?php else: ?>
                    <p><strong>Guest Customer</strong></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Update Status -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="status" class="form-label">Order Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Confirmed" <?php echo $order['status'] == 'Confirmed' ? 'selected' : ''; ?> >Confirmed</option>
                            <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?> >Shipped</option>
                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?> >Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_date" class="form-label">Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="<?php echo isset($order['delivery_date']) ? $order['delivery_date'] : ''; ?>">
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

