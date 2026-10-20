<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders - Haya Collection</title>
    <link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .page-wrap { max-width: 1100px; margin: 30px auto; }
        .table td, .table th { vertical-align: middle; }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">My Orders</h3>
        <a href="myaccount.php" class="btn btn-outline-secondary btn-sm">Back to Account</a>
    </div>

    <?php if (isset($_GET['placed']) && $_GET['placed'] == '1'): ?>
        <div class="alert alert-success">✅ Order inserted successfully.</div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order Number</th>
                            <th>Total Price</th>
                            <th>Status</th>
<th>Admin Message</th>
                            <th>Delivery Date</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="ordersBody">
                    <tr><td colspan="6" class="text-center p-4">Loading orders...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text || '').replace(/[&<>"']/g, function(m) { return map[m]; });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const d = new Date(dateString.replace(' ', 'T'));
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleString();
}

function renderOrders(orders) {
    const body = document.getElementById('ordersBody');
    if (!orders || orders.length === 0) {
                body.innerHTML = '<tr><td colspan="6" class="text-center p-4">No orders found.</td></tr>';
        return;
    }

        const rows = orders.map(function(order) {
        return '<tr>' +
            '<td>' + escapeHtml(order.order_number) + '</td>' +
            '<td>Rs. ' + escapeHtml(order.total_price) + '</td>' +
            '<td><span class="badge bg-success">' + escapeHtml(order.payment_status) + '</span></td>' +
            '<td>' + (order.admin_response ? escapeHtml(order.admin_response) : '-') + '</td>' +
            '<td>' + escapeHtml(formatDate(order.delivery_date)) + '</td>' +
            '<td>' + escapeHtml(formatDate(order.created_at)) + '</td>' +
        '</tr>';
    }).join('');

    body.innerHTML = rows;
}

function loadOrders() {
    fetch('get-user-orders.php', { credentials: 'same-origin' })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            var contentType = response.headers.get('content-type');
            if (!contentType || contentType.indexOf('application/json') === -1) {
                throw new Error('Invalid response format');
            }
            return response.json();
        })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.message || 'Unable to load orders');
            }
            renderOrders(data.orders);
        })
        .catch(function(error) {
            console.error('Error loading orders:', error);
            document.getElementById('ordersBody').innerHTML =
                '<tr><td colspan="6" class="text-center p-4 text-danger">Failed to load orders. Please refresh the page.</td></tr>';
        });
}

loadOrders();
setInterval(loadOrders, 5000);
</script>
</body>
</html>
