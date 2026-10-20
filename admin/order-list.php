<?php
$pageTitle = 'Order List';
$currentPage = 'orders';

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once dirname(__DIR__) . '/order_schema_helper.php';

requireAdminLogin();
ensureOrderManagementSchema($conn);

/* GET ALL ORDERS */
$query = "SELECT id, created_at, 
COALESCE(order_number, CONCAT('EM-', DATE_FORMAT(created_at, '%Y%m%d'), '-', LPAD(id, 6, '0'))) AS order_number, 
full_name, phone, email, address, city, pincode, 
COALESCE(total_price, total, 0.00) AS total, 
payment_method, status 
FROM orders ORDER BY id DESC";

$ordersResult = mysqli_query($conn,$query);

/* ✅ FIXED IMAGE FUNCTION */
if (!function_exists('getOrderItemImagePath')) {
    function getOrderItemImagePath($imageRaw) {

        $imageName = !empty($imageRaw) ? basename(trim($imageRaw)) : '';
        $defaultImage = '../assets/no-image.png';

        if (empty($imageName)) {
            return $defaultImage;
        }

        // STEP 5: Check both uploads locations (root + admin)
        $rootServerPath = __DIR__ . '/../uploads/' . $imageName;
        $rootWebPath = '../uploads/' . $imageName;
        
        $adminServerPath = __DIR__ . '/uploads/' . $imageName;
        $adminWebPath = 'uploads/' . $imageName;

        if (file_exists($rootServerPath)) {
            return $rootWebPath;
        }
        if (file_exists($adminServerPath)) {
            return $adminWebPath;
        }

        return $defaultImage;
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
/* Order List UI Improvements - UI ONLY, NO PHP CHANGES */
:root {
    --order-cell-padding: 16px;
    --order-row-hover: #f9f5f7;
    --product-height: 120px;
    --status-pending: #ffc107;
    --status-confirmed: #17a2b8;
    --status-delivered: #28a745;
}

#ordersTable {
    font-size: 14px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(130,60,73,0.1);
}

#ordersTable thead th {
    background: linear-gradient(135deg, var(--light-bg) 0%, #f8ecef 100%);
    color: var(--primary-color) !important;
    font-weight: 600;
    padding: var(--order-cell-padding) 12px;
    border: none;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#ordersTable tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

#ordersTable tbody tr:hover {
    background: var(--order-row-hover) !important;
    transform: scale(1.002);
    box-shadow: 0 4px 8px rgba(130,60,73,0.08);
}

/* Cell Specific Styling */
.order-id { font-family: monospace; }
.customer-name { font-weight: 600; color: var(--primary-color); }
.contact-info { display: flex; flex-direction: column; gap: 4px; }
.contact-phone { font-weight: 500; }
.contact-email { color: #666; font-size: 13px; }

.address-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    line-height: 1.4;
}
.address-street { 
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.address-location { 
    font-size: 13px; 
    color: #666; 
    font-weight: 500;
}

.products-preview {
    max-height: var(--product-height);
    overflow-y: auto;
    padding-right: 8px;
    scrollbar-width: thin;
    scrollbar-color: #ddd transparent;
}
.products-preview::-webkit-scrollbar {
    width: 6px;
}
.products-preview::-webkit-scrollbar-track { background: transparent; }
.products-preview::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.product-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.2s;
}
.product-item:hover { background: rgba(130,60,73,0.02); padding-left: 4px; }
.product-img {
    width: 60px !important;
    height: 60px !important;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #f0f0f0;
    flex-shrink: 0;
}
.product-details {
    flex: 1;
    min-width: 0;
}
.product-name { 
    font-weight: 600; 
    font-size: 13px; 
    margin: 0 0 2px 0;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.product-qty { 
    font-size: 12px; 
    color: #666;
}

.total-amount { 
    font-size: 16px !important; 
    font-weight: 700 !important;
    color: var(--primary-color) !important;
}

.status-badge-custom {
    font-weight: 600;
    padding: 0.6em 1em;
    border-radius: 20px;
    font-size: 12px;
    min-width: 80px;
    text-align: center;
}
.status-pending { background: var(--status-pending) !important; color: #333 !important; }
.status-confirmed { background: var(--status-confirmed) !important; color: white !important; }
.status-delivered { background: var(--status-delivered) !important; color: white !important; }
.status-shipped { background: #007bff !important; color: white !important; }
.status-cancelled { background: #dc3545 !important; color: white !important; }
.status-accepted { background: var(--status-confirmed) !important; color: white !important; }

.payment-method { font-size: 13px; text-transform: capitalize; }
.order-date { font-size: 13px; color: #666; }
.actions-center { display: flex; justify-content: center; align-items: center; }

/* Invoice Button */
.invoice-btn {
    min-width: 90px;
    padding: 8px 16px;
    font-size: 12px;
    border-radius: 8px;
    transition: all 0.2s;
    font-weight: 600;
}
.invoice-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(130,60,73,0.3);
}

/* Mobile Card Layout (<768px) */
@media (max-width: 768px) {
    #ordersTable.table { 
        border: 0;
        border-radius: 0;
    }
    #ordersTable thead { 
        display: none; 
        position: absolute;
        left: -9999px;
    }
    #ordersTable tbody tr { 
        display: block;
        margin-bottom: 16px;
        padding: 16px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        position: relative;
    }
    #ordersTable tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    #ordersTable td { 
        display: block;
        text-align: right;
        padding: 8px 12px;
        position: relative;
        border: none;
        border-bottom: 1px solid #f5f5f5;
    }
    #ordersTable td:before {
        content: attr(data-label);
        position: absolute;
        left: 12px;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    #ordersTable td[data-label]:after {
        content: "";
        display: block;
        clear: both;
    }
    #ordersTable .products-preview {
        max-height: none;
        overflow: visible;
    }
    #ordersTable .product-item {
        border-bottom: 1px solid #eee;
        margin-bottom: 8px;
    }
    .no-orders-mobile {
        text-align: center;
        padding: 40px 20px;
    }
}

/* Extra small mobile */
@media (max-width: 480px) {
    .product-img { width: 50px !important; height: 50px !important; }
    #ordersTable tbody tr { padding: 12px; margin-bottom: 12px; }
    #ordersTable td { padding: 6px 10px; }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
<h2><i class="fas fa-list me-2"></i>All Orders</h2>
</div>

<div class="card">
<div class="card-body p-0">

<div class="table-responsive">
<table id="ordersTable" class="table table-hover mb-0 order-table">

<thead>
<tr>
<th>Order ID</th>
<th>Customer Name</th>
<th>Contact</th>
<th>Address</th>
<th>Total Amount</th>
<th>Status</th>
<th>Payment Method</th>
<th>Order Date</th>

</tr>
</thead>

<tbody>

<?php
if($ordersResult && mysqli_num_rows($ordersResult)>0):

while($order=mysqli_fetch_assoc($ordersResult)):

$orderId=$order['id'];

$itemsQuery = mysqli_query($conn, "
    // STEP 1: Enhanced query with product_id and explicit product_db_image
    SELECT 
        oi.product_id,
        oi.product_name,
        oi.quantity,
        oi.price,
        oi.product_image,
        p.image AS product_db_image
    FROM order_items oi
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = " . intval($orderId));

// STEP 2: SAFE fallback if JOIN returns no rows
if (!$itemsQuery || mysqli_num_rows($itemsQuery) == 0) {
    $itemsQuery = mysqli_query($conn, "
        SELECT product_name, quantity, price, product_image
        FROM order_items
        WHERE order_id = " . intval($orderId)
    );
}

$items = array();
$total = 0;

if ($itemsQuery) {
    while ($row = mysqli_fetch_assoc($itemsQuery)) {
        $items[] = $row;
        $total += floatval($row['price']) * intval($row['quantity']);
    }
}

// FIXED: Group by unique product_name, sum quantity to eliminate duplicates
$groupedItems = array();
foreach ($items as $item) {
    $key = trim($item['product_name']);
    if (empty($key)) continue;
    
    // STEP 3: FIX IMAGE FALLBACK - prioritize oi.product_image -> product_db_image
    $image = !empty($item['product_image']) 
        ? $item['product_image'] 
        : (!empty($item['product_db_image']) ? $item['product_db_image'] : '');
    
    if (!isset($groupedItems[$key])) {
        $groupedItems[$key] = array(
            'product_name' => $key,
            'product_image' => $image,
            'price' => floatval($item['price']),
            'quantity' => 0
        );
    }
    $groupedItems[$key]['quantity'] += intval($item['quantity']);
}
$displayItems = array_slice($groupedItems, 0, 2);
$totalUniqueCount = count($groupedItems);

// fallback to DB total
if ($order['total'] > 0) {
    $total = $order['total'];
}
?>

<tr>

<td class="order-id" data-label="Order ID"><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>

<td class="customer-name" data-label="Customer"><?php echo htmlspecialchars($order['full_name']); ?></td>

<td>
<div class="contact-info">
    <div class="contact-phone"><?php echo htmlspecialchars($order['phone']); ?></div>
    <div class="contact-email"><?php echo htmlspecialchars($order['email']); ?></div>
</div>
</td>

<td class="address-info" data-label="Address">
<div class="address-street"><?php echo htmlspecialchars(substr($order['address'], 0, 60)) . (strlen($order['address']) > 60 ? '...' : ''); ?></div>
<div class="address-location"><?php echo htmlspecialchars($order['city'].' - '.$order['pincode']); ?></div>
</td>



<td class="total-amount" data-label="Total">₹<?php echo number_format($total, 2); ?></td>

<td>
<?php
$status = $order['status'];
$badgeClass = "badge status-badge-custom badge-secondary";

if($status == "Pending") $badgeClass = "badge status-pending";
elseif($status == "Confirmed" || $status == "Accepted") $badgeClass = "badge status-confirmed";
elseif($status == "Shipped") $badgeClass = "badge status-shipped";
elseif($status == "Delivered") $badgeClass = "badge status-delivered";
elseif($status == "Cancelled") $badgeClass = "badge status-cancelled";
?>

<span class="<?php echo $badgeClass; ?>">
    <?php echo htmlspecialchars($status ?: 'Pending'); ?>
</span>
</td>

<td class="payment-method" data-label="Payment"><?php echo htmlspecialchars($order['payment_method']); ?></td>

<td class="order-date" data-label="Date"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>



</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="10" class="text-center py-5 no-orders-mobile" style="background: linear-gradient(135deg, var(--light-bg) 0%, white 100%); border-radius: 12px;">
    <i class="fas fa-inbox fa-3x text-muted mb-3" style="opacity: 0.5;"></i>
    <h5 class="text-muted mb-1">No Orders Found</h5>
    <p class="text-muted mb-0">Orders will appear here once customers start shopping.</p>
</td>
</tr>

<?php endif; ?>

</tbody>
</table>
</div>

</div>
</div>

<?php include 'includes/footer.php'; ?>