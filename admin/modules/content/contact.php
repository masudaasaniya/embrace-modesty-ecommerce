<?php
/**
 * Content Management - Contact Info
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Contact Information';
$currentPage = 'content';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get current content
$stmt = $conn->prepare("SELECT * FROM content_pages WHERE page_key = 'contact'");
$content = false;
if ($stmt) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            $content = $result->fetch_assoc();
        }
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $page_title = sanitize(isset($_POST['page_title']) ? $_POST['page_title'] : '');
    $content_text = isset($_POST['content']) ? $_POST['content'] : '';
    $meta_description = sanitize(isset($_POST['meta_description']) ? $_POST['meta_description'] : '');
    
    if ($content) {
        $stmt = $conn->prepare("UPDATE content_pages SET page_title = ?, content = ?, meta_description = ? WHERE page_key = 'contact'");
        if ($stmt) {
            $stmt->bind_param("sss", $page_title, $content_text, $meta_description);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO content_pages (page_key, page_title, content, meta_description) VALUES ('contact', ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $page_title, $content_text, $meta_description);
        }
    }
    
    if ($stmt && $stmt->execute()) {
        $stmt->close();
        showAlert('Contact information updated successfully!');
        $stmt = $conn->prepare("SELECT * FROM content_pages WHERE page_key = 'contact'");
        if ($stmt) {
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result) {
                    $content = $result->fetch_assoc();
                }
            }
            $stmt->close();
        }
    } else {
        if ($stmt) {
            $stmt->close();
        }
        showAlert('Error updating content.', 'danger');
    }
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Contact Information</h2>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Edit Content Form -->
<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="page_title" class="form-label">Page Title</label>
                <input type="text" class="form-control" id="page_title" name="page_title" 
                       value="<?php echo htmlspecialchars(isset($content['page_title']) ? $content['page_title'] : 'Contact Us'); ?>">
            </div>
            
            <div class="mb-3">
                <label for="content" class="form-label">Contact Information</label>
                <textarea class="form-control" id="content" name="content" rows="10"><?php echo htmlspecialchars(isset($content['content']) ? $content['content'] : ''); ?></textarea>
                <small class="text-muted">You can use HTML tags for formatting</small>
            </div>
            
            <div class="mb-3">
                <label for="meta_description" class="form-label">Meta Description</label>
                <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?php echo htmlspecialchars(isset($content['meta_description']) ? $content['meta_description'] : ''); ?></textarea>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

