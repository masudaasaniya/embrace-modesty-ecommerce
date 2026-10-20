<?php
/**
 * Add Coupon
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Add Coupon';
$currentPage = 'coupons';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = strtoupper(sanitize(isset($_POST['code']) ? $_POST['code'] : ''));
    $description = sanitize(isset($_POST['description']) ? $_POST['description'] : '');
    $discount_type = isset($_POST['discount_type']) ? $_POST['discount_type'] : 'percentage';
    $discount_value = floatval(isset($_POST['discount_value']) ? $_POST['discount_value'] : 0);
    $min_order_amount = floatval(isset($_POST['min_order_amount']) ? $_POST['min_order_amount'] : 0);
    $max_uses = !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null;
    $expiry_date = sanitize(isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '');
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    
    // Validation
    if (empty($code) || $discount_value <= 0 || empty($expiry_date)) {
        showAlert('Please fill in all required fields', 'danger');
    } else {
        // Check if code exists
        $stmt = $conn->prepare("SELECT id FROM coupons WHERE code = ?");
        if ($stmt) {
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $existing = $stmt->get_result();
            $exists = ($existing && $existing->num_rows > 0);
            $stmt->close();

            if ($exists) {
                showAlert('Coupon code already exists', 'danger');
            } else {
                // Insert coupon
                $stmt = $conn->prepare("INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_uses, expiry_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssdisss", $code, $description, $discount_type, $discount_value, $min_order_amount, $max_uses, $expiry_date, $status);
                    
                    if ($stmt->execute()) {
                        $stmt->close();
                        showAlert('Coupon added successfully!');
                        header('Location: index.php');
                        exit;
                    } else {
                        $stmt->close();
                        showAlert('Error adding coupon.', 'danger');
                    }
                } else {
                    showAlert('Error adding coupon.', 'danger');
                }
            }
        } else {
            showAlert('Error validating coupon code.', 'danger');
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add Coupon</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Coupons
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Add Coupon Form -->
<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label">Coupon Code *</label>
                        <input type="text" class="form-control" id="code" name="code" required placeholder="e.g., SUMMER20" style="text-transform: uppercase;">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount_type" class="form-label">Discount Type *</label>
                                <select class="form-select" id="discount_type" name="discount_type">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount (₹)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount_value" class="form-label">Discount Value *</label>
                                <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="min_order_amount" class="form-label">Minimum Order Amount (₹)</label>
                        <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" step="0.01" min="0" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_uses" class="form-label">Maximum Uses (leave empty for unlimited)</label>
                        <input type="number" class="form-control" id="max_uses" name="max_uses" min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date *</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Coupon
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

