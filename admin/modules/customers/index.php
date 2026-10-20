<?php
/**
 * Customer Management
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Customers';
$currentPage = 'customers';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            showAlert('Customer deleted successfully!');
        } else {
            showAlert('Error deleting customer.', 'danger');
        }
        $stmt->close();
    } else {
        showAlert('Error deleting customer.', 'danger');
    }
    header('Location: index.php');
    exit;
}

// Search
$search = sanitize(isset($_GET['search']) ? $_GET['search'] : '');

// Build query
$sql = "SELECT * FROM user WHERE 1=1";
$params = array();
$types = "";

if ($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= "ss";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
    $bindParams = array();
    $bindParams[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bindParams[] = &$params[$i];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
}
if ($stmt && $stmt->execute()) {
    $customers = $stmt->get_result();
} else {
    $customers = false;
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Customers</h2>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" class="form-control" name="search" 
                       placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Orders</th>
                     

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers && $customers->num_rows > 0): ?>
                        <?php while ($customer = $customers->fetch_assoc()): 
                            // Get order count and total
                            $stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(total) as spent FROM orders WHERE user_id = ? AND status != 'Cancelled'");
                            $orderStats = array('count' => 0, 'spent' => 0);
                            if ($stmt) {
                                $stmt->bind_param("i", $customer['id']);
                                if ($stmt->execute()) {
                                    $orderResult = $stmt->get_result();
                                    if ($orderResult) {
                                        $row = $orderResult->fetch_assoc();
                                        if ($row) {
                                            $orderStats = $row;
                                        }
                                    }
                                }
                                $stmt->close();
                            }
                        ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($customer['username']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars(isset($customer['email']) ? $customer['email'] : 'N/A'); ?></td>
                            <td><?php echo isset($orderStats['count']) ? $orderStats['count'] : 0; ?></td>
                           
                            
                            <td>
                                <a href="view.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?delete=<?php echo $customer['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirmDelete('Are you sure you want to delete this customer?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="mb-0">No customers found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

