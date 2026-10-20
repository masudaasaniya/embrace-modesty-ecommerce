<!DOCTYPE html>
<?php
session_start();
include("connect.php"); // your DB connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: loginform.php");
    exit();
}

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$result = $con->query("SELECT * FROM users WHERE id='$user_id' LIMIT 1");

if ($result && $result->num_rows == 1) {
    $user = $result->fetch_assoc(); // ✅ Now $user is defined
} else {
    echo "User not found!";
    exit();
}
?>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Account Settings</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #fff; color: #333; }
    .header { background: linear-gradient(to bottom, #f8ecef,#f6e9ea); padding: 20px 50px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { color: #823c49; margin: 0; }
    .nav-links a { text-decoration: none; font-weight:bold; color: #823c49; margin-left: 20px; padding: 8px 16px; border-radius: 6px; transition: background .3s; }
    .container { display: flex; max-width: 1000px; margin: 40px auto; background: #fff; box-shadow: 0 12px 24px rgba(255,105,180,.3); border-radius: 20px; overflow: hidden; }
    .sidebar { width: 200px; background: #823c49; padding: 30px 20px; display: flex; flex-direction: column; gap: 15px; }
    .sidebar a { color: white; text-decoration: none; padding: 10px; border-radius: 8px; font-weight: 600; }
    .sidebar a.active, .sidebar a:hover { background: #ffb6d4; color: #6a003f; }
    .content { flex: 1; padding: 40px 50px; }
    h1.content-title { color: #823c49; margin-top: 0; }
    .profile-info { line-height: 1.8; font-size: 1rem; color:#333; }
    .profile-info strong { color: #823c49; }
  </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<!-- HEADER -->
<div class="header">
  <h1>User - Account</h1>
  <div class="nav-links">
    <a href="homepage2.php">Home</a>
    <a href="shoppage2.php">Shop</a>
    <a href="blogpage.php">Blog</a>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="container">
  <aside class="sidebar">
<h2>Settings</h2>
<a href="myaccount2.php" class="active">Account</a>
<a href="viewpage.php">View Item</a>
<a href="additem.php">Add Item</a>
<a href="orderdetail.php">Order Detail</a>


<a href="updateinfo.php">Edit Info</a>

<a href="logout.php" class="logout-link">Logout</a>
</aside>
  <main class="content">
    <h1 class="content-title">Profile Details</h1>
    <div class="profile-info">
      <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
      <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
      <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
      <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['date']); ?></p>
      <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
      <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
      <p><strong>User Type:</strong> <?php echo htmlspecialchars($user['usertype']); ?></p>
    </div>

    <div style="margin-top: 20px;">
        <a href="edit.php?id=<?php echo urlencode($user['id']); ?>" 
           style="display:inline-block; padding:12px 25px; background:#823c49; color:#fff; border-radius:25px; text-decoration:none; font-weight:700;">
           Edit Profile
        </a>
    </div>
</main>

</div>

</body>
</html>

