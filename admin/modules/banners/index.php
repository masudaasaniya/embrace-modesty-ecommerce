<?php
/**
 * Banner Management
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Banners';
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

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Get banner image
    $stmt = $conn->prepare("SELECT image FROM banners WHERE id = ?");
    $banner = false;
    if ($stmt) {
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                $banner = $result->fetch_assoc();
            }
        }
        $stmt->close();
    }
    
    if ($banner && $banner['image']) {
        $imagePath = '../../' . normalizeBannerRelativePath($banner['image']);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            showAlert('Banner deleted successfully!');
        }
        $stmt->close();
    }
    header('Location: index.php');
    exit;
}

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $toggleId = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE banners SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $toggleId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: index.php');
    exit;
}

// Get all banners
$banners = $conn->query("SELECT * FROM banners ORDER BY position ASC, id DESC");
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Banners</h2>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Banner
    </a>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Banners Grid -->
<div class="row">
    <?php if ($banners && $banners->num_rows > 0): ?>
        <?php while ($banner = $banners->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <?php if ($banner['image']): ?>
                    <?php $bannerImageSrc = '../../' . normalizeBannerRelativePath($banner['image']); ?>
                    <img src="<?php echo htmlspecialchars($bannerImageSrc); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($banner['title']); ?>"
                         style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-image text-muted fa-3x"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($banner['title']); ?></h5>
                    <?php if ($banner['subtitle']): ?>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($banner['subtitle']); ?></p>
                    <?php endif; ?>
                    <?php if ($banner['description']): ?>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($banner['description']); ?></p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge <?php echo $banner['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo ucfirst($banner['status']); ?>
                        </span>
                        <div>
                            <a href="?toggle=<?php echo $banner['id']; ?>" class="btn btn-sm btn-<?php echo $banner['status'] == 'active' ? 'warning' : 'success'; ?>">
                                <i class="fas fa-<?php echo $banner['status'] == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                            </a>
                            <a href="?delete=<?php echo $banner['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirmDelete('Are you sure you want to delete this banner?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-images fa-3x text-muted mb-3"></i>
            <p class="mb-3">No banners found</p>
            <a href="add.php" class="btn btn-primary">Add First Banner</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>

