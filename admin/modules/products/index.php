<?php
/**
 * Product Management - List
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Products';
$currentPage = 'products';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Get product image first
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product && $product['image']) {
        $imagePath = '../../uploads/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        showAlert('Product deleted successfully!');
    } else {
        showAlert('Error deleting product.', 'danger');
    }
    header('Location: index.php');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $toggleId = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE products SET stock_status = CASE WHEN stock_status = 'In Stock' THEN 'Out of Stock' ELSE 'In Stock' END WHERE id = ?");
    $stmt->bind_param("i", $toggleId);
    $stmt->execute();
    header('Location: index.php');
    exit;
}

// Search and filter
$search = sanitize(isset($_GET['search']) ? $_GET['search'] : '');
$categoryFilter = sanitize(isset($_GET['category']) ? $_GET['category'] : '');
$stockFilter = sanitize(isset($_GET['stock']) ? $_GET['stock'] : '');

// Build query - explicitly select 8 columns to match the 8 table headers
// Table columns: ID, Image, Name, Category, Price, Stock, Status, Actions
$sql = "SELECT id, image, name, category, price, quantity, stock_status, id as action_id FROM products WHERE 1=1";
$params = array();
$types = "";

if ($search) {
    $sql .= " AND name LIKE ?";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $types .= "s";
}

if ($categoryFilter) {
    $sql .= " AND category = ?";
    $params[] = &$categoryFilter;
    $types .= "s";
}

if ($stockFilter == 'low_stock') {
    $lowThreshold = getSetting('low_stock_threshold');
    // Ensure threshold is a valid positive integer, default to 10
    if (empty($lowThreshold) || !is_numeric($lowThreshold) || intval($lowThreshold) < 0) {
        $lowThreshold = 10;
    } else {
        $lowThreshold = intval($lowThreshold);
    }
    $sql .= " AND quantity <= ?";
    $params[] = &$lowThreshold;
    $types .= "i";
} elseif ($stockFilter == 'out_of_stock') {
    $sql .= " AND quantity <= 0";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$products = false;
$queryError = '';
if ($stmt) {
    $paramCount = count($params);
    if ($paramCount == 1) {
        $p1 = $params[0];
        $stmt->bind_param($types, $p1);
    } elseif ($paramCount == 2) {
        $p1 = $params[0];
        $p2 = $params[1];
        $stmt->bind_param($types, $p1, $p2);
    } elseif ($paramCount == 3) {
        $p1 = $params[0];
        $p2 = $params[1];
        $p3 = $params[2];
        $stmt->bind_param($types, $p1, $p2, $p3);
    } elseif ($paramCount == 4) {
        $p1 = $params[0];
        $p2 = $params[1];
        $p3 = $params[2];
        $p4 = $params[3];
        $stmt->bind_param($types, $p1, $p2, $p3, $p4);
    }
    
    if ($stmt->execute()) {
        $products = $stmt->get_result();
        if (!$products) {
            $queryError = $stmt->error;
        }
    } else {
        $queryError = $stmt->error;
    }
    $stmt->close();
} else {
    $queryError = $conn->error;
}

// If query failed or returned false, use fallback
if (!$products || !empty($queryError)) {
    if (!empty($queryError)) {
        showAlert('Query error: ' . htmlspecialchars($queryError) . '. Showing all products instead.', 'warning');
    } else {
        showAlert('Unable to load filtered products. Showing all products instead.', 'warning');
    }
    // Select 8 columns to match table headers
    $products = $conn->query("SELECT id, image, name, category, price, quantity, stock_status, id as action_id FROM products ORDER BY id DESC");
}

// Ensure products is a valid mysqli_result object
if (!($products instanceof mysqli_result)) {
    $products = $conn->query("SELECT id, image, name, category, price, quantity, stock_status, id as action_id FROM products ORDER BY id DESC");
}

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Products</h2>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Product
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                <?php echo $categoryFilter == $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="stock" class="form-label">Stock Status</label>
                <select class="form-select" id="stock" name="stock">
                    <option value="">All</option>
                    <option value="low_stock" <?php echo $stockFilter == 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="out_of_stock" <?php echo $stockFilter == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php
                                    $imageFile = isset($product['image']) ? trim($product['image']) : '';
                                    $placeholderData = "data:image/svg+xml;utf8," . rawurlencode(
                                        "<svg xmlns='http://www.w3.org/2000/svg' width='50' height='50'>" .
                                        "<rect width='50' height='50' fill='#f3f4f6'/>" .
                                        "<text x='25' y='29' text-anchor='middle' font-size='10' fill='#9ca3af' font-family='Arial'>No Img</text>" .
                                        "</svg>"
                                    );
                                    $thumbSrc = $placeholderData;

                                    if ($imageFile !== '') {
                                        $candidates = array(
                                            '../../uploads/products/' . ltrim($imageFile, '/\\'),
                                            '../../uploads/' . ltrim($imageFile, '/\\'),
                                            ltrim($imageFile, '/\\')
                                        );

                                        foreach ($candidates as $candidate) {
                                            if (file_exists($candidate)) {
                                                $thumbSrc = $candidate;
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                <img src="<?php echo htmlspecialchars($thumbSrc); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>₹<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <?php if ($product['quantity'] <= 0): ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif ($product['quantity'] <= 10): ?>
                                    <span class="badge bg-warning"><?php echo $product['quantity']; ?> (Low)</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?php echo $product['quantity']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $product['stock_status'] == 'In Stock' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo htmlspecialchars($product['stock_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?toggle=<?php echo $product['id']; ?>" class="btn btn-sm btn-<?php echo $product['stock_status'] == 'In Stock' ? 'warning' : 'success'; ?>" 
                                   title="<?php echo $product['stock_status'] == 'In Stock' ? 'Mark Out of Stock' : 'Mark In Stock'; ?>">
                                    <i class="fas fa-<?php echo $product['stock_status'] == 'In Stock' ? 'eye-slash' : 'eye'; ?>"></i>
                                </a>
                                <a href="?delete=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete"
                                   onclick="return confirmDelete('Are you sure you want to delete this product?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-box fa-2x text-muted mb-2"></i>
                                <p class="mb-0">No products found</p>
                                <a href="add.php" class="btn btn-primary btn-sm mt-2">Add First Product</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

