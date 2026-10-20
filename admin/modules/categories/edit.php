<?php
/**
 * Edit Category
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Edit Category';
$currentPage = 'categories';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get category ID
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get category data
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    showAlert('Category not found', 'danger');
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize(isset($_POST['name']) ? $_POST['name'] : '');
    $description = sanitize(isset($_POST['description']) ? $_POST['description'] : '');
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    
    // Validation
    if (empty($name)) {
        showAlert('Category name is required', 'danger');
    } else {
        // Generate slug
        $slug = generateSlug($name);
        
        // Check if slug exists (excluding current category)
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->bind_param("si", $slug, $categoryId);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Handle image upload
        $image = $category['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Delete old image
            if ($category['image'] && file_exists($uploadDir . $category['image'])) {
                unlink($uploadDir . $category['image']);
            }
            
            $image = uploadFile($_FILES['image'], $uploadDir);
        }
        
        // Check if delete image checkbox is checked
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            if ($category['image'] && file_exists('../../uploads/categories/' . $category['image'])) {
                unlink('../../uploads/categories/' . $category['image']);
            }
            $image = null;
        }
        
        // Update category
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, image = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $name, $slug, $description, $image, $status, $categoryId);
        
        if ($stmt->execute()) {
            showAlert('Category updated successfully!');
            header('Location: index.php');
            exit;
        } else {
            showAlert('Error updating category.', 'danger');
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Category</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Categories
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Edit Category Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars(isset($category['description']) ? $category['description'] : ''); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Category Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        
                        <?php if ($category['image']): ?>
                        <div class="mt-2">
                            <img id="imagePreview" src="../../uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" 
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
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

