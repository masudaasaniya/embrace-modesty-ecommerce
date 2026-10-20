<?php
/**
 * Edit Product
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Edit Product';
$currentPage = 'products';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get product ID
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    showAlert('Product not found', 'danger');
    header('Location: index.php');
    exit;
}

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
$existingCategories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize(isset($_POST['name']) ? $_POST['name'] : '');
    $category = sanitize(isset($_POST['category']) ? $_POST['category'] : '');
    $price = floatval(isset($_POST['price']) ? $_POST['price'] : 0);
    $discount_price = floatval(isset($_POST['discount_price']) ? $_POST['discount_price'] : 0);
    $quantity = intval(isset($_POST['quantity']) ? $_POST['quantity'] : 0);
    $description = sanitize(isset($_POST['description']) ? $_POST['description'] : '');
    $stock_status = isset($_POST['stock_status']) ? $_POST['stock_status'] : 'In Stock';
    
    // Validation
    if (empty($name) || empty($category) || $price <= 0) {
        showAlert('Please fill in all required fields', 'danger');
    } else {
        // Handle image upload
        $image = $product['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image
            if ($product['image'] && file_exists('../../uploads/products/' . $product['image'])) {
                unlink('../../uploads/products/' . $product['image']);
            } elseif ($product['image'] && file_exists('../../uploads/' . $product['image'])) {
                unlink('../../uploads/' . $product['image']);
            }
            
            $uploadDir = '../../uploads/products/';
            $image = uploadFile($_FILES['image'], $uploadDir);
        }
        
        // Check if delete image checkbox is checked
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            if ($product['image'] && file_exists('../../uploads/products/' . $product['image'])) {
                unlink('../../uploads/products/' . $product['image']);
            } elseif ($product['image'] && file_exists('../../uploads/' . $product['image'])) {
                unlink('../../uploads/' . $product['image']);
            }
            $image = null;
        }
        
        // Update product (support both new and legacy products table schemas)
        $updated = false;
        $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, discount_price = ?, quantity = ?, description = ?, image = ?, stock_status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssddisssi", $name, $category, $price, $discount_price, $quantity, $description, $image, $stock_status, $productId);
            $updated = $stmt->execute();
        } else {
            $legacyImage = $image ? $image : '';
            $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, image = ?, stock_status = ?, quantity = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssdssii", $name, $category, $price, $legacyImage, $stock_status, $quantity, $productId);
                $updated = $stmt->execute();
            }
        }

        if ($updated) {
            showAlert('Product updated successfully!');
            header('Location: index.php');
            exit;
        } else {
            showAlert('Error updating product.', 'danger');
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Product</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Edit Product Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category *</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                        <?php echo $product['category'] == $cat['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                            <?php while ($ecat = $existingCategories->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($ecat['category']); ?>" 
                                        <?php echo $product['category'] == $ecat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ecat['category']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (₹) *</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount_price" class="form-label">Discount Price (₹)</label>
                                <input type="number" class="form-control" id="discount_price" name="discount_price" 
                                       step="0.01" min="0" value="<?php echo isset($product['discount_price']) ? $product['discount_price'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       min="0" value="<?php echo $product['quantity']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stock_status" class="form-label">Stock Status</label>
                                <select class="form-select" id="stock_status" name="stock_status">
                                    <option value="In Stock" <?php echo $product['stock_status'] == 'In Stock' ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="Out of Stock" <?php echo $product['stock_status'] == 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="Pre Order" <?php echo $product['stock_status'] == 'Pre Order' ? 'selected' : ''; ?>>Pre Order</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="6"><?php echo htmlspecialchars(isset($product['description']) ? $product['description'] : ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        
                        <?php if ($product['image']): ?>
                        <div class="mt-2">
                            <img id="imagePreview" src="../../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="Current Image" style="max-width: 100%; border-radius: 8px;">
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="delete_image" name="delete_image" value="1">
                            <label class="form-check-label" for="delete_image">
                                Delete current image
                            </label>
                        </div>
                        <?php else: ?>
                        <div class="mt-2">
                            <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; display: none; border-radius: 8px;">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>


