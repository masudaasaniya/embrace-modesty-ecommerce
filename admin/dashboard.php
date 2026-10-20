<?php
/**
 * Admin Dashboard
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

require_once 'config/database.php';
require_once 'includes/functions.php';

// Check admin login
requireAdminLogin();

// Get dashboard statistics
$totalProducts = getCount('products');
$totalOrders = getCount('orders');
$totalRevenue = getSum('orders', 'total', "status != 'Cancelled'");
$totalCustomers = getCount('user');

// Get low stock products
$lowStockProducts = getLowStockProducts();

// Get recent orders
$recentOrders = getRecentOrders(5);

// Get monthly sales for chart using optimized function
$monthlySalesData = getMonthlySales();
$monthlyLabels = isset($monthlySalesData['labels']) && is_array($monthlySalesData['labels']) ? $monthlySalesData['labels'] : array();
$monthlySales = isset($monthlySalesData['data']) && is_array($monthlySalesData['data']) ? $monthlySalesData['data'] : array();
$hasMonthlySalesData = !empty($monthlySales) && array_sum($monthlySales) > 0;


// Get order status counts (exclude cancelled/rejected for active stats)
$orderStatus = array(
    'pending' => getCount('orders', "status = 'Pending'"),
    'confirmed' => getCount('orders', "status IN ('Confirmed','Accepted','Processing')"),
    'shipped' => getCount('orders', "status = 'Shipped'"),
    'delivered' => getCount('orders', "status = 'Delivered'"),
    'cancelled' => getCount('orders', "status = 'Cancelled'")
);
$activeStatusesCond = "status IN ('Pending','Confirmed','Accepted','Processing','Shipped','Delivered')";
$totalActiveOrders = getCount('orders', $activeStatusesCond);


// Get today's orders
$todayOrders = getTodayOrderStats();
if (!isset($todayOrders['count']) || !isset($todayOrders['total'])) {
    $todayOrders = array('count' => 0, 'total' => 0);
    error_log('Dashboard debug: Today stats returned invalid payload.');
}
if (!is_array($lowStockProducts)) {
    $lowStockProducts = array();
    error_log('Dashboard debug: Low stock products payload was not an array.');
}
if (!is_array($recentOrders)) {
    $recentOrders = array();
    error_log('Dashboard debug: Recent orders payload was not an array.');
}

?>

<?php include 'includes/header.php'; ?>

<!-- Dashboard Content -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-4">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo number_format((int)($totalProducts ?: 0)); ?></div>

                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #823c49 0%, #a55267 100%);">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <a href="modules/products/index.php" class="text-muted small">View Products <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo number_format((int)($totalOrders ?: 0)); ?></div>

                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <a href="modules/orders/index.php" class="text-muted small">View Orders <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">₹<?php echo number_format($totalRevenue, 2); ?></div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
            <a href="modules/reports/index.php" class="text-muted small">View Reports <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value"><?php echo number_format($totalCustomers); ?></div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <a href="modules/customers/index.php" class="text-muted small">View Customers <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>

<!-- Today's Stats and Charts -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-calendar-day me-2"></i> Today's Statistics
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Today's Orders</div>
                    <div class="h4 mb-0"><?php echo isset($todayOrders['count']) ? $todayOrders['count'] : 0; ?></div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Today's Revenue</div>
                    <div class="h4 mb-0">&#8377;<?php echo number_format(isset($todayOrders['total']) ? $todayOrders['total'] : 0, 2); ?></div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h5 mb-1 text-warning"><?php echo $orderStatus['pending']; ?></div>
                        <div class="small text-muted">Pending</div>
                    </div>
                    <div class="col-4">
                        <div class="h5 mb-1 text-info"><?php echo $orderStatus['confirmed'] + $orderStatus['shipped']; ?></div>
                        <div class="small text-muted">Processing</div>
                    </div>
                    <div class="col-4">
                        <div class="h5 mb-1 text-success"><?php echo $orderStatus['delivered']; ?></div>
                        <div class="small text-muted">Delivered</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i> Monthly Sales
            </div>
            <div class="card-body" style="height: 340px; overflow: hidden;">
                <div style="position: relative; height: 100%; width: 100%;">
                    <canvas id="monthlySalesChart" style="display: block; width: 100%; height: 100%;"></canvas>
                    <div id="monthlySalesNoData"
                         class="text-center text-muted small mt-2"
                         style="<?php echo $hasMonthlySalesData ? 'display:none;' : ''; ?>">
                        No data available
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock & Recent Orders -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts</span>
                <span class="badge bg-danger"><?php echo count($lowStockProducts); ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockProducts)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                        <p class="mb-0 mt-2">All products are well stocked!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(isset($product['name']) ? $product['name'] : 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(isset($product['category']) && $product['category'] !== '' ? $product['category'] : 'Uncategorized'); ?></td>
                                    <td>
                                        <?php $stockQty = isset($product['quantity']) ? (int)$product['quantity'] : 0; ?>
                                        <?php if ($stockQty <= 5): ?>
                                            <span class="badge bg-danger"><?php echo $stockQty; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><?php echo $stockQty; ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="modules/products/index.php?filter=low_stock" class="text-primary text-decoration-none">View All Low Stock Products</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-shopping-bag me-2"></i>Recent Orders</span>
                <a href="modules/orders/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentOrders)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x"></i>
                        <p class="mb-0 mt-2">No orders yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="modules/orders/view.php?id=<?php echo $order['id']; ?>">
                                            #<?php echo $order['id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $customerName = 'Guest';
                                        if (isset($order['customer_name']) && $order['customer_name'] !== '') {
                                            $customerName = $order['customer_name'];
                                        } elseif (isset($order['username']) && $order['username'] !== '') {
                                            $customerName = $order['username'];
                                        } elseif (isset($order['full_name']) && $order['full_name'] !== '') {
                                            $customerName = $order['full_name'];
                                        }
                                        echo htmlspecialchars($customerName);
                                        ?>
                                    </td>
                                    <td>&#8377;<?php echo number_format(isset($order['total']) ? (float)$order['total'] : 0, 2); ?></td>
                                    <td>
                                    <?php
                                    $statusClass = '';
                                    if ($order['status'] == 'Pending') $statusClass = 'badge-pending';
                                    elseif ($order['status'] == 'Confirmed') $statusClass = 'badge-confirmed';
                                    elseif ($order['status'] == 'Accepted') $statusClass = 'badge-confirmed';
                                    elseif ($order['status'] == 'Processing') $statusClass = 'badge-confirmed';
                                    elseif ($order['status'] == 'Shipped') $statusClass = 'badge-shipped';
                                    elseif ($order['status'] == 'Delivered') $statusClass = 'badge-delivered';
                                    elseif ($order['status'] == 'Cancelled') $statusClass = 'badge-cancelled';
                                    else $statusClass = 'badge-pending';
                                    ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('monthlySalesChart');
    const noDataEl = document.getElementById('monthlySalesNoData');
    if (!ctx || typeof Chart === 'undefined') {
        if (noDataEl) noDataEl.style.display = 'block';
        return;
    }

    const labelsRaw = <?php echo json_encode($monthlyLabels); ?>;
    const dataRaw = <?php echo json_encode($monthlySales); ?>;
    
    if (!Array.isArray(labelsRaw) || labelsRaw.length === 0) {
        if (noDataEl) noDataEl.style.display = 'block';
        return;
    }
    const labels = labelsRaw;
    let chartData = Array.isArray(dataRaw) ? dataRaw.slice(0, labels.length) : [];
    while (chartData.length < labels.length) {
        chartData.push(0);
    }
    chartData = chartData.map(function(value) {
        const n = Number(value);
        return Number.isFinite(n) && n > 0 ? n : 0;
    });

    const hasRenderableData = chartData.some(function(v) { return v > 0; });
    if (!hasRenderableData) {
        if (noDataEl) noDataEl.style.display = 'block';
        return;
    }
    if (noDataEl) noDataEl.style.display = 'none';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: "Sales",
                data: chartData,
                borderColor: '#823c49',
                backgroundColor: 'rgba(130, 60, 73, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#823c49',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>





