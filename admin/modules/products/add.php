<?php
/**
 * Add Product
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Add Product';
$currentPage = 'products';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get categories from database (supports category_name, name, mixed status formats)
$hasCategoryName = false;
$hasName = false;

$check = $conn->query("SHOW COLUMNS FROM categories LIKE 'category_name'");
if ($check && $check->num_rows > 0) {
    $hasCategoryName = true;
}
$check = $conn->query("SHOW COLUMNS FROM categories LIKE 'name'");
if ($check && $check->num_rows > 0) {
    $hasName = true;
}
$categoryExpr = 'name';
if ($hasCategoryName && $hasName) {
    $categoryExpr = "COALESCE(NULLIF(category_name, ''), name)";
} elseif ($hasCategoryName) {
    $categoryExpr = 'category_name';
}

$categories = array();
$categoriesSql = "SELECT id, " . $categoryExpr . " AS category_name FROM categories";
$categoriesSql .= " ORDER BY " . $categoryExpr . " ASC";

$categoriesQuery = $conn->query($categoriesSql);
if ($categoriesQuery) {
    while ($row = $categoriesQuery->fetch_assoc()) {
        $label = isset($row['category_name']) ? trim($row['category_name']) : '';
        if ($label !== '') {
            $categories[] = array(
                'id' => $row['id'],
                'category_name' => $label
            );
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize(isset($_POST['name']) ? $_POST['name'] : '');
    $category_id = intval(isset($_POST['category_id']) ? $_POST['category_id'] : 0);
    $price = floatval(isset($_POST['price']) ? $_POST['price'] : 0);
    $discount_price = floatval(isset($_POST['discount_price']) ? $_POST['discount_price'] : 0);
    $quantity = intval(isset($_POST['quantity']) ? $_POST['quantity'] : 0);
    $description = sanitize(isset($_POST['description']) ? $_POST['description'] : '');
    $stock_status = isset($_POST['stock_status']) ? $_POST['stock_status'] : 'In Stock';

    $category = '';
    foreach ($categories as $catItem) {
        if (intval($catItem['id']) === $category_id) {
            $category = isset($catItem['category_name']) ? $catItem['category_name'] : '';
            break;
        }
    }
    
    // Validation
    if (empty($name) || $category_id <= 0 || empty($category) || $price <= 0) {
        showAlert('Please fill in all required fields', 'danger');
    } else {
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/';
            $image = uploadFile($_FILES['image'], $uploadDir);
        }
        
        // Insert product (support both new and legacy products table schemas)
        $inserted = false;
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, discount_price, quantity, description, image, stock_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssddisss", $name, $category, $price, $discount_price, $quantity, $description, $image, $stock_status);
            $inserted = $stmt->execute();
        } else {
            $legacyImage = $image ? $image : '';
            $stmt = $conn->prepare("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssdssi", $name, $category, $price, $legacyImage, $stock_status, $quantity);
                $inserted = $stmt->execute();
            }
        }

        if ($inserted) {
            showAlert('Product added successfully!');
            header('Location: index.php');
            exit;
        } else {
            showAlert('Error adding product.', 'danger');
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add Product</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Add Product Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category *</label>
                        <select class="form-select" id="category" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo intval($cat['id']); ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (₹) *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount_price" class="form-label">Discount Price (₹)</label>
                                <input type="number" class="form-control" id="discount_price" name="discount_price" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stock_status" class="form-label">Stock Status</label>
                                <select class="form-select" id="stock_status" name="stock_status">
                                    <option value="In Stock">In Stock</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                    <option value="Pre Order">Pre Order</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="6"></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        <div class="mt-2">
                            <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; display: none; border-radius: 8px;">
                        </div>
                        <small class="text-muted">Recommended size: 600x600 pixels</small>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

