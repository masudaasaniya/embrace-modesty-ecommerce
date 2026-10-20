<?php
/**
 * Settings Management
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Settings';
$currentPage = 'settings';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings = array(
        'site_name' => sanitize(isset($_POST['site_name']) ? $_POST['site_name'] : ''),
        'contact_email' => sanitize(isset($_POST['contact_email']) ? $_POST['contact_email'] : ''),
        'contact_phone' => sanitize(isset($_POST['contact_phone']) ? $_POST['contact_phone'] : ''),
        'contact_address' => sanitize(isset($_POST['contact_address']) ? $_POST['contact_address'] : ''),
        'facebook_url' => sanitize(isset($_POST['facebook_url']) ? $_POST['facebook_url'] : ''),
        'instagram_url' => sanitize(isset($_POST['instagram_url']) ? $_POST['instagram_url'] : ''),
        'twitter_url' => sanitize(isset($_POST['twitter_url']) ? $_POST['twitter_url'] : ''),
        'currency' => sanitize(isset($_POST['currency']) ? $_POST['currency'] : 'INR'),
        'currency_symbol' => sanitize(isset($_POST['currency_symbol']) ? $_POST['currency_symbol'] : '₹'),
        'tax_rate' => floatval(isset($_POST['tax_rate']) ? $_POST['tax_rate'] : 0),
        'shipping_cost' => floatval(isset($_POST['shipping_cost']) ? $_POST['shipping_cost'] : 0),
        'free_shipping_threshold' => floatval(isset($_POST['free_shipping_threshold']) ? $_POST['free_shipping_threshold'] : 0),
        'low_stock_threshold' => intval(isset($_POST['low_stock_threshold']) ? $_POST['low_stock_threshold'] : 10),
        'items_per_page' => intval(isset($_POST['items_per_page']) ? $_POST['items_per_page'] : 20)
    );
    
    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $logo = uploadFile($_FILES['logo'], $uploadDir, array('png', 'jpg', 'jpeg', 'gif'));
        if ($logo) {
            $settings['site_logo'] = $logo;
        }
    }
    
    // Update all settings
    foreach ($settings as $key => $value) {
        updateSetting($key, $value);
    }
    
    showAlert('Settings saved successfully!');
}

// Get current settings
$settings = array();
$result = $conn->query("SELECT setting_key, setting_value FROM settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
}
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Website Settings</h2>
</div>

<!-- Alert Messages -->
<?php displayAlert(); ?>

<!-- Settings Form -->
<form method="POST" enctype="multipart/form-data">
    <div class="row">
        <!-- General Settings -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Website Name</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               value="<?php echo htmlspecialchars(isset($settings['site_name']) ? $settings['site_name'] : 'Embrace Modesty'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Website Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <?php if (!empty($settings['site_logo'])): ?>
                        <div class="mt-2">
                            <img src="../../uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                 alt="Current Logo" style="max-width: 150px;">
                        </div>
                        <?php endif; ?>
                        <small class="text-muted">Recommended size: 200x60 pixels</small>
                    </div>
                </div>
            </div>
            
            <!-- Contact Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                               value="<?php echo htmlspecialchars(isset($settings['contact_email']) ? $settings['contact_email'] : ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                               value="<?php echo htmlspecialchars(isset($settings['contact_phone']) ? $settings['contact_phone'] : ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_address" class="form-label">Address</label>
                        <textarea class="form-control" id="contact_address" name="contact_address" rows="2"><?php echo htmlspecialchars(isset($settings['contact_address']) ? $settings['contact_address'] : ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Social Media -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media Links</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="facebook_url" class="form-label">Facebook</label>
                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                               value="<?php echo htmlspecialchars(isset($settings['facebook_url']) ? $settings['facebook_url'] : ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="instagram_url" class="form-label">Instagram</label>
                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                               value="<?php echo htmlspecialchars(isset($settings['instagram_url']) ? $settings['instagram_url'] : ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="twitter_url" class="form-label">Twitter</label>
                        <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                               value="<?php echo htmlspecialchars(isset($settings['twitter_url']) ? $settings['twitter_url'] : ''); ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Business Settings -->
        <div class="col-md-6">
            <!-- Currency Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-rupee-sign me-2"></i>Currency Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency Code</label>
                                <input type="text" class="form-control" id="currency" name="currency" 
                                       value="<?php echo htmlspecialchars(isset($settings['currency']) ? $settings['currency'] : 'INR'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" 
                                       value="<?php echo htmlspecialchars(isset($settings['currency_symbol']) ? $settings['currency_symbol'] : '₹'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Order Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                        <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                               step="0.01" min="0" value="<?php echo isset($settings['tax_rate']) ? $settings['tax_rate'] : 0; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="shipping_cost" class="form-label">Shipping Cost (₹)</label>
                        <input type="number" class="form-control" id="shipping_cost" name="shipping_cost" 
                               step="0.01" min="0" value="<?php echo isset($settings['shipping_cost']) ? $settings['shipping_cost'] : 0; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="free_shipping_threshold" class="form-label">Free Shipping Threshold (₹)</label>
                        <input type="number" class="form-control" id="free_shipping_threshold" name="free_shipping_threshold" 
                               step="0.01" min="0" value="<?php echo isset($settings['free_shipping_threshold']) ? $settings['free_shipping_threshold'] : 0; ?>">
                        <small class="text-muted">Set to 0 to disable free shipping</small>
                    </div>
                </div>
            </div>
            
            <!-- Inventory Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Inventory Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="low_stock_threshold" class="form-label">Low Stock Alert Threshold</label>
                        <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" 
                               min="0" value="<?php echo isset($settings['low_stock_threshold']) ? $settings['low_stock_threshold'] : 10; ?>">
                        <small class="text-muted">Products below this quantity will show in low stock alerts</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="items_per_page" class="form-label">Items Per Page</label>
                        <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                               min="1" value="<?php echo isset($settings['items_per_page']) ? $settings['items_per_page'] : 20; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-end">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Settings
        </button>
    </div>
</form>

<?php include '../../includes/footer.php'; ?>

