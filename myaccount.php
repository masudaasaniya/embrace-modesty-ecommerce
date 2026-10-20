<?php
session_start();
include("connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$user = array();

// Try primary table: user
$stmt = $con->prepare("SELECT id, name, email, phone, address, `date`, gender, username FROM `user` WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fallback table: users
if (empty($user)) {
    $stmt = $con->prepare("SELECT id, name, email, phone, address, `date`, gender, username FROM `users` WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

if (empty($user)) {
    die("User not found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Account Settings</title>

<style>
body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #fff; color: #333; }

.account-page,
.account-page * {
    filter: none !important;
    backdrop-filter: none !important;
}

.header {
    background: linear-gradient(to bottom, #f8ecef,#f6e9ea);
    padding: 20px 50px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 { color: #823c49; margin: 0; }

.nav-links a {
    text-decoration: none;
    font-weight: bold;
    color: #823c49;
    margin-left: 20px;
    padding: 8px 16px;
    border-radius: 6px;
    transition: 0.3s;
}

.nav-links a:hover {
    background: #ffb6d4;
}

.container {
    display: flex;
    max-width: 1000px;
    margin: 40px auto;
    background: #fff;
    box-shadow: 0 12px 24px rgba(255,105,180,.3);
    border-radius: 20px;
    overflow: hidden;
}

.sidebar {
    width: 200px;
    background: #823c49;
    padding: 30px 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.sidebar a {
    color: #333 !important;
    text-decoration: none;
    padding: 10px;
    border-radius: 8px;
    font-weight: 700;
    opacity: 1 !important;
    text-shadow: none !important;
    background: #f7e7ee;
    border: 1px solid #e8c8d4;
}

.sidebar a.active,
.sidebar a:hover {
    background: #ffb6d4;
    color: #000 !important;
}

.content {
    flex: 1;
    padding: 40px 50px;
}

h1.content-title {
    color: #823c49;
    margin-top: 0;
}

.profile-info {
    line-height: 1.8;
    font-size: 1rem;
    color: #333 !important;
    opacity: 1 !important;
    filter: none !important;
    text-shadow: none !important;
}

.profile-info strong {
    color: #000 !important;
    font-weight: 700 !important;
}

.edit-btn {
    display:inline-block;
    padding:12px 25px;
    background:#823c49;
    color:#fff;
    border-radius:25px;
    text-decoration:none;
    font-weight:700;
    margin-top:20px;
}
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>

<body>

<div class="header account-page">
  <h1>User - Account</h1>
  <div class="nav-links">
    <a href="homepage2.php">Home</a>
    <a href="shoppage2.php">Shop</a>
    <a href="blogpage.php">Blog</a>
     <a href="feedback.php">Feedback</a>
  </div>
  </div>
</div>

<div class="container account-page">

  <aside class="sidebar">
    <a href="myaccount.php" class="active">Account Overview</a>
    <a href="edit.php">Edit Info</a>
    <a href="orders.php">My Order</a>
    <a href="cart.php">Cart</a>
    <a href="wishlist.php">Wishlist</a>
    <a href="logoutuser.php">Logout</a>
  </aside>

  <main class="content">
    <h1 class="content-title">Profile Details</h1>

    <div class="profile-info">
      <p><strong>Full Name:</strong> <?php echo htmlspecialchars(isset($user['name']) ? $user['name'] : ''); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars(isset($user['email']) ? $user['email'] : ''); ?></p>
      <p><strong>Phone Number:</strong> <?php echo htmlspecialchars(isset($user['phone']) ? $user['phone'] : ''); ?></p>
      <p><strong>Address:</strong> <?php echo htmlspecialchars(isset($user['address']) ? $user['address'] : ''); ?></p>
      <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars(isset($user['date']) ? $user['date'] : ''); ?></p>
      <p><strong>Gender:</strong> <?php echo htmlspecialchars(isset($user['gender']) ? $user['gender'] : ''); ?></p>
      <p><strong>Username:</strong> <?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : ''); ?></p>
    </div>

    <a href="edit.php?id=<?php echo urlencode(isset($user['id']) ? $user['id'] : $user_id); ?>" class="edit-btn">
       Edit Profile
    </a>

  </main>



</div>

</body>
</html>
