<?php
/**
 * Content Management - Privacy Policy & Terms
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Privacy & Terms';
$currentPage = 'content';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get current contents
$privacy = false;
$terms = false;
$privacyResult = $conn->query("SELECT * FROM content_pages WHERE page_key = 'privacy'");
if ($privacyResult) {
    $privacy = $privacyResult->fetch_assoc();
}
$termsResult = $conn->query("SELECT * FROM content_pages WHERE page_key = 'terms'");
if ($termsResult) {
    $terms = $termsResult->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $privacy_title = sanitize(isset($_POST['privacy_title']) ? $_POST['privacy_title'] : '');
    $privacy_content = isset($_POST['privacy_content']) ? $_POST['privacy_content'] : '';
    $terms_title = sanitize(isset($_POST['terms_title']) ? $_POST['terms_title'] : '');
    $terms_content = isset($_POST['terms_content']) ? $_POST['terms_content'] : '';
    
    // Update or insert Privacy Policy
    if ($privacy) {
        $stmt = $conn->prepare("UPDATE content_pages SET page_title = ?, content = ? WHERE page_key = 'privacy'");
        if ($stmt) {
            $stmt->bind_param("ss", $privacy_title, $privacy_content);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO content_pages (page_key, page_title, content) VALUES ('privacy', ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $privacy_title, $privacy_content);
        }
    }
    if ($stmt) {
        $stmt->execute();
        $stmt->close();
    }
    
    // Update or insert Terms & Conditions
    if ($terms) {
        $stmt = $conn->prepare("UPDATE content_pages SET page_title = ?, content = ? WHERE page_key = 'terms'");
        if ($stmt) {
            $stmt->bind_param("ss", $terms_title, $terms_content);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO content_pages (page_key, page_title, content) VALUES ('terms', ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $terms_title, $terms_content);
        }
    }
    
    if ($stmt && $stmt->execute()) {
        $stmt->close();
        showAlert('Privacy Policy & Terms updated successfully!');
        $privacy = false;
        $terms = false;
        $privacyResult = $conn->query("SELECT * FROM content_pages WHERE page_key = 'privacy'");
        if ($privacyResult) {
            $privacy = $privacyResult->fetch_assoc();
        }
        $termsResult = $conn->query("SELECT * FROM content_pages WHERE page_key = 'terms'");
        if ($termsResult) {
            $terms = $termsResult->fetch_assoc();
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
    <h2>Privacy Policy & Terms</h2>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Edit Content Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Privacy Policy</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="privacy_title" class="form-label">Page Title</label>
                <input type="text" class="form-control" id="privacy_title" name="privacy_title" 
                       value="<?php echo htmlspecialchars(isset($privacy['page_title']) ? $privacy['page_title'] : 'Privacy Policy'); ?>">
            </div>
            
            <div class="mb-3">
                <label for="privacy_content" class="form-label">Content</label>
                <textarea class="form-control" id="privacy_content" name="privacy_content" rows="10"><?php echo htmlspecialchars(isset($privacy['content']) ? $privacy['content'] : ''); ?></textarea>
                <small class="text-muted">You can use HTML tags for formatting</small>
            </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Terms & Conditions</h5>
    </div>
    <div class="card-body">
            <div class="mb-3">
                <label for="terms_title" class="form-label">Page Title</label>
                <input type="text" class="form-control" id="terms_title" name="terms_title" 
                       value="<?php echo htmlspecialchars(isset($terms['page_title']) ? $terms['page_title'] : 'Terms & Conditions'); ?>">
            </div>
            
            <div class="mb-3">
                <label for="terms_content" class="form-label">Content</label>
                <textarea class="form-control" id="terms_content" name="terms_content" rows="10"><?php echo htmlspecialchars(isset($terms['content']) ? $terms['content'] : ''); ?></textarea>
                <small class="text-muted">You can use HTML tags for formatting</small>
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

