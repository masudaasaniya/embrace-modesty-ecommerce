<?php
/**
 * Category Management - List
 * Embrace Modesty E-Commerce - Syntax Fixed
 */

$pageTitle = 'Categories';
$currentPage = 'categories';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Check if category has products
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $countRow = $stmt->get_result()->fetch_assoc();
    $productCount = $countRow['count'];
    
    if ($productCount > 0) {
        showAlert('Cannot delete category with products. Please remove or reassign products first.', 'danger');
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        if (!$stmt) {
            showAlert('Prepare failed: ' . $conn->error, 'danger');
        } else {
            $stmt->bind_param("i", $deleteId);
            if ($stmt->execute()) {
                showAlert('Category deleted successfully!');
            } else {
                showAlert('Error deleting category.', 'danger');
            }
            $stmt->close();
        }
    }
    header('Location: index_fixed.php');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $toggleId = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE categories SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $toggleId);
    $stmt->execute();
    $stmt->close();
    header('Location: index_fixed.php');
    exit;
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Categories</h2>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Category
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Categories Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while ($category = $categories->fetch_assoc()): 
                            // Get product count for this category
                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category = ?");
                            if (!$stmt) {
                                $productCount = 0;
                            } else {
                                $stmt->bind_param("s", $category['name']);
                                $stmt->execute();
                                $countRow = $stmt->get_result()->fetch_assoc();
                                $productCount = $countRow['count'];
                                $stmt->close();
                            }
                        ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td>
                                <?php if ($category['image']): ?>
                                    <img src="../../uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($category['name']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px; border-radius: 8px;">
                                        <i class="fas fa-tags text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                <?php if ($category['description']): ?>
                                    <br><small class="text-muted"><?php echo substr(htmlspecialchars($category['description']), 0, 50); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $productCount; ?> products</span>
                            </td>
                            <td>
                                <?php if ($category['status'] == 'active'): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?toggle=<?php echo $category['id']; ?>" class="btn btn-sm btn-<?php echo $category['status'] == 'active' ? 'warning' : 'success'; ?>" 
                                   title="<?php echo $category['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="fas fa-<?php echo $category['status'] == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                </a>
                                <a href="?delete=<?php echo $category['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirmDelete('Are you sure you want to delete this category?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-tags fa-2x text-muted mb-2"></i>
                                <p class="mb-0">No categories found</p>
                                <a href="add.php" class="btn btn-primary btn-sm mt-2">Add First Category</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

