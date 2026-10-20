<?php
/**
 * Add Category
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Add Category';
$currentPage = 'categories';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

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
        
        // Check if slug exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $image = uploadFile($_FILES['image'], $uploadDir);
        }
        
        // Insert category
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, image, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $slug, $description, $image, $status);
        
        if ($stmt->execute()) {
            showAlert('Category added successfully!');
            header('Location: index.php');
            exit;
        } else {
            showAlert('Error adding category.', 'danger');
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add Category</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Categories
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Add Category Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Category Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imagePreview')">
                        <div class="mt-2">
                            <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; display: none; border-radius: 8px;">
                        </div>
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
                    <i class="fas fa-save me-2"></i>Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

