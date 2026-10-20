<?php
$pageTitle = 'Orders';
$currentPage = 'orders';

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once dirname(__DIR__) . '/order_schema_helper.php';

requireAdminLogin();
ensureOrderManagementSchema($conn);

$query = "SELECT 
            o.id,
            o.user_id,
            COALESCE(o.order_number, CONCAT('EM-', DATE_FORMAT(o.created_at, '%Y%m%d'), '-', LPAD(o.id, 6, '0'))) AS order_number,
            IF(COALESCE(o.total_price, o.total, 0.00) = 0.00,
               COALESCE((SELECT SUM(price * quantity) FROM order_items oi WHERE oi.order_id = o.id), 0.00),
               COALESCE(o.total_price, o.total, 0.00)) AS total_price,
            o.status,
            COALESCE(o.admin_response, '') AS admin_response,
            o.created_at,
            COALESCE(u.username, CONCAT('User #', o.user_id)) AS customer_name
          FROM orders o
          LEFT JOIN user u ON u.id = o.user_id
          ORDER BY o.created_at DESC, o.id DESC";

$ordersResult = mysqli_query($conn, $query);

if (!$ordersResult) {
    echo "<script>console.error('Query failed: " . addslashes(mysqli_error($conn)) . "');</script>";
} else {
    echo "<script>console.log('Query success: Found " . mysqli_num_rows($ordersResult) . " orders');</script>";
}
$csrfToken = csrfToken();
?>
<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Order Management</h2>
    <small class="text-muted">Auto updates user-side every 5 seconds</small>
</div>

<div id="statusAlert"></div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
<style>
            .status-badge {
                --bs-badge-color: #fff !important;
                --bs-badge-padding-x: 0.65em;
                --bs-badge-padding-y: 0.35em;
                --bs-badge-font-size: 0.75em;
                --bs-badge-font-weight: 700;
                color: var(--bs-badge-color) !important;
                background: var(--bs-badge-bg) !important;
                opacity: 1 !important;
                filter: none !important;
                border-radius: 0.375rem;
            }
            .status-badge.pending { --bs-badge-bg: #d97706 !important; } /* dark amber */
            .status-badge.accepted { --bs-badge-bg: #1d4ed8 !important; } /* dark blue */
            .status-badge.processing { --bs-badge-bg: #059669 !important; } /* emerald */
            .status-badge.confirmed {
                background-color: #198754 !important; /* dark green */
                color: #ffffff !important;
                font-weight: 600 !important;
                padding: 4px 10px !important;
                border-radius: 6px !important;
                display: inline-block !important;
                opacity: 1 !important;
            }
            .status-badge.shipped { --bs-badge-bg: #374151 !important; } /* gray */
            .status-badge.delivered { --bs-badge-bg: #15803d !important; } /* green */
            .status-badge.rejected { --bs-badge-bg: #dc2626 !important; } /* red */
            </style>
            <table class="table table-hover mb-0 data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php if($ordersResult && mysqli_num_rows($ordersResult)>0): ?>
                        <?php while ($order = $ordersResult->fetch_assoc()): ?>
                            <tr id="order-row-<?php echo (int)$order['id']; ?>">
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>Rs. <?php echo number_format((float)$order['total_price'], 2); ?></td>
                                <td>
                                    <span id="status-text-<?php echo (int)$order['id']; ?>" class="badge status-badge <?php echo strtolower($order['status']); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>

                                <td><?php echo formatDateTime($order['created_at']); ?></td>
                                
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td class="text-center py-4">No orders found.</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function badgeClass(status) {
    return 'status-badge ' + status.toLowerCase();
}

function showAlert(message, type) {
    var root = document.getElementById('statusAlert');
    root.innerHTML = '<div class="alert alert-' + type + '">' + message + '</div>';
    setTimeout(function () { root.innerHTML = ''; }, 3000);
}

document.querySelectorAll('.order-status-select').forEach(function (select) {
    select.addEventListener('change', function () {
        var orderId = this.getAttribute('data-order-id');
        var status = this.value;
        var formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', status);
        formData.append('csrf_token', '<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>');

        fetch('update-order-status.php', {
            method: 'POST',
            body: formData
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            var contentType = response.headers.get('content-type');
            if (!contentType || contentType.indexOf('application/json') === -1) {
                throw new Error('Invalid response format');
            }
            return response.json();
        })
        .then(function (data) {
            if (!data.success) {
                throw new Error(data.message || 'Update failed');
            }
            var badge = document.getElementById('status-text-' + orderId);
            badge.className = 'badge ' + badgeClass(data.status);
            badge.textContent = data.status;

            // Admin Response column removed - no UI update needed

            showAlert('Order #' + orderId + ' updated successfully.', 'success');
        })
        .catch(function (error) {
            console.error('Error:', error);
            showAlert(error.message || 'Unable to update order. Please try again.', 'danger');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
