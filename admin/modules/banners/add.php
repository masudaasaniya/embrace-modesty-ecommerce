<?php
/**
 * Add Banner
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Add Banner';
$currentPage = 'banners';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

function normalizeBannerRelativePath($value) {
    $value = str_replace('\\', '/', trim($value));
    $value = ltrim($value, './');
    $value = ltrim($value, '/');

    if ($value === '') {
        return '';
    }
    if (strpos($value, 'uploads/banners/') === 0) {
        return $value;
    }
    if (strpos($value, 'admin/uploads/banners/') === 0) {
        return substr($value, 6);
    }
    if (strpos($value, 'banners/') === 0) {
        return 'uploads/' . $value;
    }

    return 'uploads/banners/' . basename($value);
}

// Check admin login
requireAdminLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize(isset($_POST['title']) ? $_POST['title'] : '');
    $subtitle = sanitize(isset($_POST['subtitle']) ? $_POST['subtitle'] : '');
    $description = sanitize(isset($_POST['description']) ? $_POST['description'] : '');
    $link = sanitize(isset($_POST['link']) ? $_POST['link'] : '');

    $link_text = sanitize(isset($_POST['link_text']) ? $_POST['link_text'] : '');
    $position = intval(isset($_POST['position']) ? $_POST['position'] : 1);
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    
    // Validation
    if (empty($title)) {
        showAlert('Banner title is required', 'danger');
    } else {
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true, true);
            }
            $image = uploadFile($_FILES['image'], $uploadDir, array('jpg', 'jpeg', 'png', 'gif', 'webp'));
            if (!$image) {
                error_log("Banner upload failed for {$_FILES['image']['name']}: check perms on {$uploadDir}");
            }
        } else {
            $uploadErrorCode = (isset($_FILES['image']) && isset($_FILES['image']['error'])) ? $_FILES['image']['error'] : 'no file';
            error_log("Banner upload error: " . $uploadErrorCode);
        }
        
        if (!$image) {
            showAlert('Banner image upload failed. Check file size/perms. Error logged.', 'danger');
        } else {
            $image = normalizeBannerRelativePath($image);
            // Insert banner (optional description)
            if (empty($description)) {
                $stmt = $conn->prepare("INSERT INTO banners (title, subtitle, image, link, link_text, position, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    showAlert('Prepare error (no desc): ' . $conn->error, 'danger');
                    $stmt = false;
                } else {
                    $stmt->bind_param("sssssis", $title, $subtitle, $image, $link, $link_text, $position, $status);
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO banners (title, subtitle, description, image, link, link_text, position, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    showAlert('Prepare error (with desc): ' . $conn->error, 'danger');
                    $stmt = false;
                } else {
                    $stmt->bind_param("ssssssis", $title, $subtitle, $description, $image, $link, $link_text, $position, $status);
                }
            }
            
            if ($stmt) {
                if ($stmt->execute()) {
                    $stmt->close();
                    showAlert('Banner added successfully!');
                    header('Location: index.php');
                    exit;
                } else {
                    $stmt->close();
                    showAlert('Execute error: ' . $stmt->error, 'danger');
                }
            }
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add Banner</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Banners
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Add Banner Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Banner Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtitle" class="form-label">Subtitle</label>
                        <input type="text" class="form-control" id="subtitle" name="subtitle">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter banner description"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="link" class="form-label">Link URL</label>
                        <input type="url" class="form-control" id="link" name="link" placeholder="https://...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="link_text" class="form-label">Button Text</label>
                        <input type="text" class="form-control" id="link_text" name="link_text" placeholder="Shop Now">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="number" class="form-control" id="position" name="position" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Banner Image *</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this, 'imagePreview')" required>
                        <div class="mt-2">
                            <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; display: none; border-radius: 8px;">
                        </div>
                        <small class="text-muted">Recommended size: 1920x600 pixels</small>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Banner
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

