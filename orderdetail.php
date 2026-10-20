<?php
session_start();
include("connect.php");
require_once("order_schema_helper.php");

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: loginform.php");
    exit;
}

ensureOrderManagementSchema($con);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['decision'])) {
    $orderId = intval($_POST['order_id']);
    $decision = trim($_POST['decision']);

    if ($orderId > 0 && ($decision === 'Accepted' || $decision === 'Rejected')) {
        $adminResponse = $decision === 'Accepted'
            ? 'Your order has been accepted.'
            : 'Your order has been rejected.';

        $stmt = $con->prepare("UPDATE orders SET status = ?, admin_response = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $decision, $adminResponse, $orderId);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: orderdetail.php");
    exit;
}

$sql = "SELECT id, user_id, order_number, total, status, admin_response, created_at, order_date FROM orders ORDER BY COALESCE(order_date, created_at) DESC, id DESC";
$result = mysqli_query($con, $sql);

$orders = array();
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order Details - Admin</title>
<style>
body { font-family: 'Segoe UI', sans-serif; margin:0; background:#fff; color:#333; }
.header { background: linear-gradient(to bottom, #f8ecef, #f6e9ea); padding:20px 50px; display:flex; justify-content:space-between; align-items:center;}
.header h1 { margin:0; color:#823c49; font-size:24px; }
.nav-links { display:flex; gap:20px; }
.nav-links a { text-decoration:none; font-weight:bold; color:#823c49; padding:8px 16px; border-radius:6px;}
.nav-links a:hover { background:#ffb6d4; color:#6a003f; }
.container { max-width:1200px; margin:40px auto; background:#fff; border-radius:20px; box-shadow:0 12px 24px rgb(255 105 180 / 0.3); display:flex; overflow:hidden; }
.sidebar { background:#823c49; width:200px; padding:30px 20px; display:flex; flex-direction:column; gap:15px; }
.sidebar h2 { color:white; margin-bottom:20px; }
.sidebar a { text-decoration:none; color:white; padding:10px; border-radius:8px; font-weight:600; }
.sidebar a.active, .sidebar a:hover { background:#ffb6d4; color:#6a003f; }
.content { flex:1; padding:40px 30px; }
.content h1 { margin-top:0; color:#823c49; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:10px 12px; border:1px solid #f0c1d7; text-align:left; vertical-align:top; }
th { background:#ffe0ec; color:#823c49; }
tr:nth-child(even){ background:#fff6fa; }
.btn{padding:6px 10px;border:none;border-radius:6px;cursor:pointer;font-weight:600}
.btn-accept{background:#198754;color:#fff}
.btn-reject{background:#dc3545;color:#fff}
.msg{max-width:260px;white-space:normal}
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<div class="header">
<h1>Admin Dashboard</h1>
<div class="nav-links">
<a href="homepage2.php">Home</a>
<a href="shoppage2.php">Shop</a>
<a href="blogpage.php">Blog</a>
</div>
</div>

<div class="container">
<aside class="sidebar">
<h2>Settings</h2>
<a href="myaccount2.php">Account</a>
<a href="viewpage.php">View Item</a>
<a href="additem.php">Add Item</a>
<a href="orderdetail.php" class="active">Order Detail</a>
<a href="edititem.php">Edit Item</a>
<a href="editinfo.php">Edit Info</a>
<a href="logout.php">Logout</a>
</aside>

<main class="content">
<h1>Order Details</h1>

<table>
<thead>
<tr>
<th>Order #</th>
<th>User ID</th>
<th>Date</th>
<th>Total Amount</th>
<th>Items</th>
<th>Status</th>
<th>Admin Message</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if(!empty($orders)): ?>
    <?php foreach($orders as $order): ?>
        <tr>
            <td><?php echo htmlspecialchars($order['order_number'] ? $order['order_number'] : ('#' . $order['id'])); ?></td>
            <td><?php echo intval($order['user_id']); ?></td>
            <td><?php echo htmlspecialchars($order['order_date'] ? $order['order_date'] : $order['created_at']); ?></td>
            <td>Rs.<?php echo number_format(floatval($order['total']), 2); ?></td>
            <td>
                <?php
                $items = array();
                $itemStmt = $con->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ? ORDER BY id ASC");
                if ($itemStmt) {
                    $oid = intval($order['id']);
                    $itemStmt->bind_param("i", $oid);
                    if ($itemStmt->execute()) {
                        $itemRes = $itemStmt->get_result();
                        if ($itemRes) {
                            while ($ir = $itemRes->fetch_assoc()) {
                                $items[] = $ir['product_name'] . ' x' . intval($ir['quantity']);
                            }
                        }
                    }
                    $itemStmt->close();
                }
                echo !empty($items) ? htmlspecialchars(implode(', ', $items)) : '-';
                ?>
            </td>
            <td><?php echo htmlspecialchars($order['status']); ?></td>
            <td class="msg"><?php echo htmlspecialchars($order['admin_response']); ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo intval($order['id']); ?>">
                    <input type="hidden" name="decision" value="Accepted">
                    <button type="submit" class="btn btn-accept">Accept</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo intval($order['id']); ?>">
                    <input type="hidden" name="decision" value="Rejected">
                    <button type="submit" class="btn btn-reject">Reject</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="8">No orders found.</td></tr>
<?php endif; ?>
</tbody>
</table>

</main>
</div>

</body>
</html>
