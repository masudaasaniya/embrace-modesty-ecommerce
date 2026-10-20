<?php
/**
 * Reports & Analytics
 * Embrace Modesty E-Commerce
 */

$pageTitle = 'Reports';
$currentPage = 'reports';

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check admin login
requireAdminLogin();

// Get date filters
$reportType = isset($_GET['type']) ? $_GET['type'] : 'daily';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Check if order_date column exists, otherwise use created_at
$dateColumn = 'order_date';
$orderDateCheck = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_date'");
if (!$orderDateCheck || mysqli_num_rows($orderDateCheck) == 0) {
    $dateColumn = 'created_at';
}

// Build date condition
$dateCondition = "DATE($dateColumn) BETWEEN '" . mysqli_real_escape_string($conn, $dateFrom) . "' AND '" . mysqli_real_escape_string($conn, $dateTo) . "'";
$dateConditionWithAlias = "DATE(o.$dateColumn) BETWEEN '" . mysqli_real_escape_string($conn, $dateFrom) . "' AND '" . mysqli_real_escape_string($conn, $dateTo) . "'";

// Debug info
$debugInfo = array();

// Get sales data based on report type
$salesData = array();
$labels = array();

$excludedStatuses = "'Cancelled', 'Rejected'";

if ($reportType == 'daily') {
    // Daily report - show each day
    $sql = "
        SELECT DATE($dateColumn) as date, COUNT(*) as orders, SUM(total) as revenue 
        FROM orders 
        WHERE status NOT IN ($excludedStatuses) AND $dateCondition
        GROUP BY DATE($dateColumn)
        ORDER BY date ASC
    ";
    $debugInfo[] = "Daily SQL: " . $sql;
    
    $stmt = mysqli_query($conn, $sql);
    if (!$stmt) {
        $debugInfo[] = "Error: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($stmt) > 0) {
        while ($row = mysqli_fetch_assoc($stmt)) {
            $labels[] = date('d M', strtotime($row['date']));
            $salesData[] = !empty($row['revenue']) ? floatval($row['revenue']) : 0;
        }
    }
} elseif ($reportType == 'monthly') {
    // Monthly report
    $sql = "
        SELECT DATE_FORMAT($dateColumn, '%Y-%m') as month, COUNT(*) as orders, SUM(total) as revenue 
        FROM orders 
        WHERE status NOT IN ($excludedStatuses) AND $dateCondition
        GROUP BY DATE_FORMAT($dateColumn, '%Y-%m')
        ORDER BY month ASC
    ";
    $debugInfo[] = "Monthly SQL: " . $sql;
    
    $stmt = mysqli_query($conn, $sql);
    if (!$stmt) {
        $debugInfo[] = "Error: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($stmt) > 0) {
        while ($row = mysqli_fetch_assoc($stmt)) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $salesData[] = !empty($row['revenue']) ? floatval($row['revenue']) : 0;
        }
    }
} else {
    // Yearly report
    $sql = "
        SELECT YEAR($dateColumn) as year, COUNT(*) as orders, SUM(total) as revenue 
        FROM orders 
        WHERE status NOT IN ($excludedStatuses) AND $dateCondition
        GROUP BY YEAR($dateColumn)
        ORDER BY year ASC
    ";
    $debugInfo[] = "Yearly SQL: " . $sql;
    
    $stmt = mysqli_query($conn, $sql);
    if (!$stmt) {
        $debugInfo[] = "Error: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($stmt) > 0) {
        while ($row = mysqli_fetch_assoc($stmt)) {
            $labels[] = $row['year'];
            $salesData[] = !empty($row['revenue']) ? floatval($row['revenue']) : 0;
        }
    }
}

// Get summary statistics
$summary = array('total_orders' => 0, 'total_revenue' => 0, 'avg_order_value' => 0);
$summarySql = "
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total), 0) as total_revenue,
        COALESCE(AVG(total), 0) as avg_order_value
    FROM orders 
    WHERE status NOT IN ($excludedStatuses) AND $dateCondition
";
$debugInfo[] = "Summary SQL: " . $summarySql;

$summaryResult = mysqli_query($conn, $summarySql);
if ($summaryResult && mysqli_num_rows($summaryResult) > 0) {
    $summaryRow = mysqli_fetch_assoc($summaryResult);
    if ($summaryRow) {
        $summary['total_orders'] = intval($summaryRow['total_orders']);
        $summary['total_revenue'] = floatval($summaryRow['total_revenue']);
        $summary['avg_order_value'] = floatval($summaryRow['avg_order_value']);
    }
}

// Get top selling products
$topProductsRows = array();
$topProductsSql = "
    SELECT oi.product_name, SUM(oi.quantity) as total_sold, SUM(oi.total) as revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status NOT IN ($excludedStatuses) AND $dateConditionWithAlias
    GROUP BY oi.product_name
    ORDER BY total_sold DESC
    LIMIT 10
";
$debugInfo[] = "Top Products SQL: " . $topProductsSql;

$topProducts = mysqli_query($conn, $topProductsSql);
if ($topProducts) {
    while ($productRow = mysqli_fetch_assoc($topProducts)) {
        $topProductsRows[] = $productRow;
    }
}

// Get order status breakdown
$statusRows = array();
$statusSql = "
    SELECT status, COUNT(*) as count, COALESCE(SUM(total), 0) as revenue
    FROM orders
    WHERE $dateCondition
    GROUP BY status
";
$debugInfo[] = "Status SQL: " . $statusSql;

$statusBreakdown = mysqli_query($conn, $statusSql);
if ($statusBreakdown) {
    while ($statusRow = mysqli_fetch_assoc($statusBreakdown)) {
        $statusRows[] = $statusRow;
    }
}

// If no data, create sample data for testing
$insertSampleData = false;
if (empty($salesData) && empty($topProductsRows) && $summary['total_orders'] == 0) {
    $insertSampleData = true;
}

// Insert sample data if requested
if (isset($_GET['action']) && $_GET['action'] == 'insert_sample') {
    // Insert sample user
    mysqli_query($conn, "INSERT IGNORE INTO user (id, username, email, password) VALUES (1, 'testuser', 'test@example.com', 'test123')");
    
    // Insert sample products
    mysqli_query($conn, "INSERT IGNORE INTO products (id, name, price, image, status) VALUES 
        (1, 'Sample Hijab', 299.00, 'skaff1.jpg', 'active'),
        (2, 'Sample Abaya', 1499.00, 'burkha1.jpg', 'active'),
        (3, 'Sample Cap', 99.00, 'cap1.jpg', 'active')");
    
    // Insert sample orders with varying dates
    $dates = array(
        date('Y-m-d', strtotime('-5 days')),
        date('Y-m-d', strtotime('-3 days')),
        date('Y-m-d', strtotime('-2 days')),
        date('Y-m-d', strtotime('-1 day')),
        date('Y-m-d')
    );
    
    $statuses = array('Pending', 'Pending', 'Accepted', 'Delivered', 'Cancelled');
    
    for ($i = 0; $i < 5; $i++) {
        mysqli_query($conn, "INSERT INTO orders (user_id, order_number, full_name, email, phone, address, city, pincode, total, payment_method, status, order_date, created_at) 
            VALUES (1, 'ORD-SAMPLE-" . ($i+1) . "', 'Customer " . ($i+1) . "', 'customer" . ($i+1) . "@example.com', '9876543210', '123 Test Street', 'Mumbai', '400001', " . (500 + ($i * 300)) . ", 'COD', '" . $statuses[$i] . "', '" . $dates[$i] . "', '" . $dates[$i] . "')");
        
        $orderId = mysqli_insert_id($conn);
        
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) VALUES 
            ($orderId, 1, 'Sample Hijab', " . ($i+1) . ", 299.00, " . (299 * ($i+1)) . "),
            ($orderId, 2, 'Sample Abaya', 1, 1499.00, 1499.00)");
    }
    
    echo "<div class='alert alert-success'>Sample data inserted! <a href='index.php'>Refresh</a></div>";
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Debug Section - Remove in production -->
<?php if (isset($_GET['debug']) && $_GET['debug'] == 1): ?>
<div class="card mb-4 bg-warning">
    <div class="card-body">
        <h5>Debug Information:</h5>
        <pre><?php echo implode("\n", $debugInfo); ?></pre>
        <p><strong>Date Column Used:</strong> <?php echo $dateColumn; ?></p>
        <p><strong>Summary:</strong> Orders: <?php echo $summary['total_orders']; ?>, Revenue: <?php echo $summary['total_revenue']; ?></p>
        <p><strong>Sales Data:</strong> <?php echo count($salesData); ?> records</p>
        <p><strong>Top Products:</strong> <?php echo count($topProductsRows); ?> records</p>
        <p><strong>Status Rows:</strong> <?php echo count($statusRows); ?> records</p>
    </div>
</div>
<?php endif; ?>

<!-- Insert Sample Data Button -->
<?php if ($insertSampleData): ?>
<div class="alert alert-warning mb-4">
    <strong>No data found!</strong> The reports page is not showing any data. 
    <a href="index.php?action=insert_sample" class="btn btn-primary btn-sm">Insert Sample Data</a>
    <a href="index.php?debug=1" class="btn btn-secondary btn-sm">Show Debug Info</a>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Reports & Analytics</h2>
    <div>
        <a href="index.php?debug=1" class="btn btn-info btn-sm me-2">Debug</a>
        <a href="index.php?action=insert_sample" class="btn btn-success btn-sm">Insert Sample Data</a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="type" class="form-label">Report Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="daily" <?php echo $reportType == 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="monthly" <?php echo $reportType == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="yearly" <?php echo $reportType == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?php echo number_format($summary['total_orders']); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹<?php echo number_format($summary['total_revenue'], 2); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Avg Order Value</div>
            <div class="stat-value">₹<?php echo number_format($summary['avg_order_value'], 2); ?></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Chart -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Trend</h5>
            </div>
            <div class="card-body">
                <?php if (count($salesData) > 0): ?>
                    <canvas id="salesChart" height="150"></canvas>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p>No sales data available for the selected period.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Order Status -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Order Status</h5>
            </div>
            <div class="card-body">
                <?php if (count($statusRows) > 0): ?>
                    <?php foreach ($statusRows as $status): 
                        $percentage = $summary['total_orders'] > 0 ? ($status['count'] / $summary['total_orders']) * 100 : 0;
                        $statusClass = '';
                        if ($status['status'] == 'Pending') $statusClass = 'bg-warning';
                        elseif ($status['status'] == 'Accepted' || $status['status'] == 'Confirmed') $statusClass = 'bg-primary';
                        elseif ($status['status'] == 'Processing') $statusClass = 'bg-info';
                        elseif ($status['status'] == 'Shipped') $statusClass = 'bg-dark';
                        elseif ($status['status'] == 'Delivered') $statusClass = 'bg-success';
                        elseif ($status['status'] == 'Cancelled' || $status['status'] == 'Rejected') $statusClass = 'bg-danger';
                        else $statusClass = 'bg-secondary';
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><?php echo htmlspecialchars($status['status']); ?></span>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo $status['count']; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar <?php echo $statusClass; ?>" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No status data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Selling Products</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product</th>
                        <th>Units Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($topProductsRows) > 0): ?>
                        <?php $rank = 1; foreach ($topProductsRows as $product): ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo intval($product['total_sold']); ?></td>
                            <td>₹<?php echo number_format($product['revenue'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                No product data available.<br>
                                <a href="index.php?action=insert_sample" class="btn btn-primary btn-sm mt-2">Insert Sample Data</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if (count($salesData) > 0): ?>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Revenue (₹)',
                data: <?php echo json_encode($salesData); ?>,
                backgroundColor: 'rgba(130, 60, 73, 0.8)',
                borderColor: '#823c49',
                borderWidth: 1
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
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>

