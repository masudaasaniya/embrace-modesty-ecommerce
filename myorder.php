<?php 
session_start();
include("connect.php");

// Check login
if(!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])){
    die("<p style='text-align:center; color:#823c49; font-weight:bold;'>Please login to view your orders.</p>");
}

$user_id = $_SESSION['user_id'];

// ✅ Cancel order request
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    $con->query("UPDATE orders SET status='Cancelled' 
                 WHERE id='$cancel_id' 
                 AND user_id='$user_id' 
                 AND status='Processing'");
    header("Location: myorder.php"); 
    exit;
}

// Fetch orders from DB (join products)
$sql = "SELECT o.id AS order_id, o.quantity, o.price, o.created_at, o.status,
               p.name AS product_name, p.image
        FROM orders o 
        JOIN products p ON o.product_id = p.id
        WHERE o.user_id = '$user_id'
        ORDER BY o.created_at DESC";

$result = $con->query($sql);

$orders = array();
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $orders[] = array(
            'id' => "ORD" . str_pad($row['order_id'], 6, "0", STR_PAD_LEFT), // custom order ID
            'order_id' => $row['order_id'],
            'item' => $row['product_name'],
            'date' => date("Y-m-d", strtotime($row['created_at'])),
            'status' => $row['status'],
            'delivery_date' => date("Y-m-d", strtotime($row['created_at']." +5 days")), 
            'can_return' => true,
            'tracking' => 'In Transit'
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <style>
    body { font-family: Arial, sans-serif; background:white; margin: 0; padding: 0; }
    .header { background: linear-gradient(to bottom, #f8ecef,#f6e9ea); padding: 20px 50px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { color: #823c49; margin: 0; }
    .nav-links a { text-decoration: none; font-weight: 600; color: #823c49; margin-left: 20px; padding: 8px 16px; border-radius: 6px; transition: background .3s; }
  
    .main-container { display: flex; max-width: 1200px; margin: 40px auto; }
    .sidebar { width: 200px; background-color:#823c49; border-radius: 10px 0 0 10px; }
    .sidebar a { display: block; padding: 20px; color: #ffe0ec; font-weight: bold; text-decoration: none; border-radius: 5px; text-align: center; transition: 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: #823c49; color: white; }
    .content { flex: 1; background: white; padding: 30px; border-radius: 0 10px 10px 0; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { color: #823c49; margin-top: 0; }

    .order-card { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(to bottom, #f8ecef,#f6e9ea); border: 1px solid #f6c0d7; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
    .order-info { flex: 2; }
    .order-actions { flex: 1; text-align: right; }
    .order-info p { margin: 5px 0; color: #333; }
    .status-Delivered { color: green; font-weight: bold; }
    .status-Cancelled { color: red; font-weight: bold; }
    .status-Processing { color: orange; font-weight: bold; }
    .action-btn { display: block; background: #823c49; color: white; padding: 8px 14px; margin: 6px 0; border: none; border-radius: 5px; cursor: pointer; font-size: 0.9rem; text-decoration: none; }
    .disabled-btn { background-color: #ccc !important; cursor: not-allowed; color: #666; }
  </style>
  <script>
    function showTab(tabId) {
      const tabs = document.querySelectorAll('.tab-content');
      const links = document.querySelectorAll('.sidebar a');
      tabs.forEach(t => t.style.display = 'none');
      links.forEach(l => l.classList.remove('active'));
      document.getElementById(tabId).style.display = 'block';
      document.getElementById(tabId + '-link').classList.add('active');
    }
    window.onload = () => showTab('all-orders');
  </script>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
  <div class="header">
    <h1>Order - Detail</h1>
    <div class="nav-links">
      <a href="homepage2.php">Home</a>
      <a href="shoppage2.php">Shop</a>
      <a href="blogpage.php">Blog</a>
      <a href="myaccount.php">👤</a>
    </div>
  </div>

<div class="main-container">
  <!-- Sidebar -->
  <div class="sidebar">
    <a href="account-settings.php" class="active">Account Overview</a>
    <a href="#" id="all-orders-link" onclick="showTab('all-orders')">All Orders</a>
    <a href="#" id="cancelled-link" onclick="showTab('cancelled')">Cancelled</a>
    <a href="#" id="track-link" onclick="showTab('track')">Track Orders</a>
    <a href="#" id="return-link" onclick="showTab('return')">Return</a>
    <a href="#" id="live-link" onclick="showTab('live')">Live Delivery</a>
  </div>

  <!-- Content Area -->
  <div class="content">

    <!-- All Orders -->
    <div class="tab-content" id="all-orders">
      <h2>All Orders</h2>
      <?php if(!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
          <div class="order-card">
            <div class="order-info">
              <p><strong>Item:</strong> <?php echo $order['item']; ?></p>
              <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
              <p><strong>Date:</strong> <?php echo $order['date']; ?></p>
              <p><strong>Status:</strong> <span class="status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></p>
            </div>
            <div class="order-actions">
              <?php if ($order['status'] === 'Processing'): ?>
                <a href="myorder.php?cancel_id=<?php echo $order['order_id']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel Order</a>
              <?php else: ?>
                <a class="action-btn disabled-btn">Cancel Order</a>
              <?php endif; ?>
             
              <?php if ($order['can_return'] && $order['status'] === 'Delivered'): ?>
                <a href="#" class="action-btn">Return</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color:#823c49; font-weight:bold;">No orders found.</p>
      <?php endif; ?>
    </div>

    <!-- Cancelled Orders -->
    <div class="tab-content" id="cancelled" style="display:none">
      <h2>Cancelled Orders</h2>
      <?php foreach ($orders as $order): ?>
        <?php if ($order['status'] === 'Cancelled'): ?>
          <div class="order-card">
            <div class="order-info">
              <p><strong>Item:</strong> <?php echo $order['item']; ?></p>
              <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
              <p><strong>Cancelled On:</strong> <?php echo $order['date']; ?></p>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Track -->
    <div class="tab-content" id="track" style="display:none">
      <h2>Tracking Info</h2>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-info">
            <p><strong>Item:</strong> <?php echo $order['item']; ?></p>
            <p><strong>Tracking:</strong> <?php echo $order['tracking']; ?></p>
          </div>
          
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Return -->
    <div class="tab-content" id="return" style="display:none">
      <h2>Return Items</h2>
      <?php foreach ($orders as $order): ?>
        <?php if ($order['can_return'] && $order['status'] === 'Delivered'): ?>
          <div class="order-card">
            <div class="order-info">
              <p><strong>Item:</strong> <?php echo $order['item']; ?></p>
              <p><strong>Delivered On:</strong> <?php echo $order['delivery_date']; ?></p>
            </div>
            <div class="order-actions">
              <a href="#" class="action-btn">Return</a>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Live Delivery -->
    <div class="tab-content" id="live" style="display:none">
      <h2>Live Delivery Dates</h2>
      <?php foreach ($orders as $order): ?>
        <?php if (!empty($order['delivery_date'])): ?>
          <div class="order-card">
            <div class="order-info">
              <p><strong>Item:</strong> <?php echo $order['item']; ?></p>
              <p><strong>Expected Delivery:</strong> <?php echo $order['delivery_date']; ?></p>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

  </div>
</div>
</body>
</html>

