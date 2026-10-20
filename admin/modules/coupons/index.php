<?php
/**
 * Coupon Management
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Coupons';
$currentPage = 'coupons';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            showAlert('Coupon deleted successfully!');
        }
        $stmt->close();
    }
    header('Location: index.php');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $toggleId = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE coupons SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $toggleId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: index.php');
    exit;
}

// Get all coupons
$coupons = $conn->query("SELECT * FROM coupons ORDER BY id DESC");
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Coupons</h2>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Coupon
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Coupons Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Discount</th>
                        <th>Min Order</th>
                        <th>Usage</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($coupons && $coupons->num_rows > 0): ?>
                        <?php while ($coupon = $coupons->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $coupon['id']; ?></td>
                            <td><code><strong><?php echo htmlspecialchars($coupon['code']); ?></strong></code></td>
                            <td><?php echo htmlspecialchars(isset($coupon['description']) ? $coupon['description'] : '-'); ?></td>
                            <td>
                                <?php if ($coupon['discount_type'] == 'percentage'): ?>
                                    <?php echo $coupon['discount_value']; ?>%
                                <?php else: ?>
                                    ₹<?php echo number_format($coupon['discount_value']); ?>
                                <?php endif; ?>
                            </td>
                            <td>₹<?php echo number_format($coupon['min_order_amount']); ?></td>
                            <td>
                                <?php echo $coupon['used_count']; ?>
                                <?php if ($coupon['max_uses']): ?>
                                    / <?php echo $coupon['max_uses']; ?>
                                <?php else: ?>
                                    / ∞
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($coupon['expiry_date']); ?></td>
                            <td>
                                <?php 
                                $isExpired = strtotime($coupon['expiry_date']) < time();
                                $isActive = $coupon['status'] == 'active' && !$isExpired;
                                ?>
                                <span class="badge <?php echo $isActive ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $isExpired ? 'Expired' : ucfirst($coupon['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?toggle=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-<?php echo $coupon['status'] == 'active' ? 'warning' : 'success'; ?>" title="Toggle">
                                    <i class="fas fa-<?php echo $coupon['status'] == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                </a>
                                <a href="?delete=<?php echo $coupon['id']; ?>" 
                                   class="btn btn-sm btn-danger" title="Delete"
                                   onclick="return confirmDelete('Are you sure you want to delete this coupon?');">
                                    <i class="fas fa-trash"></i>
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

