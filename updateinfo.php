<?php
session_start();
include("connect.php");

// Make sure admin/user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user data
$stmt = $con->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();

// Initialize variables
$full_name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];
$address = $user['address'];
$gender = $user['gender'];
$dob = $user['date'];
$username = $user['username'];
$user_type = $user['usertype'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $username = trim($_POST['username']);
    $user_type = $_POST['user_type'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password handling
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $message = "Passwords do not match!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }
    } else {
        $hashedPassword = $user['password'];
    }

    // Update user data if no password error
    if ($message === "") {
        $stmtUpdate = $con->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, gender=?, date=?, username=?, password=?, usertype=? WHERE id=?");
        $stmtUpdate->bind_param("sssssssssi", $full_name, $email, $phone, $address, $gender, $dob, $username, $hashedPassword, $user_type, $user_id);

        if ($stmtUpdate->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile: " . $con->error;
        }
        $stmtUpdate->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Admin Info</title>
  <style>
    /* Keep your existing CSS */
    body { font-family: 'Segoe UI', sans-serif; background: #fff; margin: 0; color: #333; }
    .header { background: linear-gradient(to bottom, #f8ecef,#f6e9ea); padding:20px 50px; display:flex; justify-content:space-between; align-items:center; }
    .nav-links a { text-decoration:none; font-weight:bold; color:#823c49; padding:8px 16px; border-radius:6px; transition:background .3s; }
    .container { max-width:1000px; margin:40px auto; background:#fff; border-radius:20px; box-shadow:0 12px 24px rgba(255,105,180,.3); display:flex; overflow:hidden;}
    .sidebar { background:#823c49; width:200px; padding:30px 20px; display:flex; flex-direction:column; gap:15px; }
    .sidebar a { text-decoration:none; color:white; padding:10px; border-radius:8px; font-weight:600; }
    .sidebar a.active, .sidebar a:hover { background:#ffb6d4; color:#6a003f; }
    .content { flex:1; padding:40px 50px; }
    h1 { color:#823c49; margin-top:0; }
    form { margin-top:30px; }
    label { display:block; margin-bottom:6px; font-weight:600; color:#823c49; }
    input, textarea, select { width:100%; padding:10px; margin-bottom:20px; border:1px solid #f0c1d7; border-radius:6px; background:#fff0f7; color:#5a0a44; font-size:1rem; }
    .btn-submit { background:#823c49; border:none; padding:12px 25px; color:white; font-weight:700; border-radius:25px; cursor:pointer; font-size:1rem; }
    .btn-submit:hover { background:#6a003f; }
    .message { padding:10px; margin-bottom:20px; background:#ffe0ec; border:1px solid #ff99cc; color:#b3005c; font-weight:bold; border-radius:6px; }
  </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
<div class="header">
  <h1>Admin - Edit Info</h1>
  <div class="nav-links">
    <a href="homepage2.php">Home</a>
    <a href="shoppage2.php">Shop</a>
    <a href="blogpage.php">Blog</a>
  </div>
</div>

<div class="container">
  <aside class="sidebar">
    <h2 style="color:white;">Settings</h2>
    <a href="myaccount2.php">Account</a>
    <a href="viewpage.php">View Items</a>
    <a href="additem.php">Add Item</a>
    <a href="edit-info.php" class="active">Edit Info</a>
    <a href="logout.php">Logout</a>
  </aside>

  <main class="content">
    <?php if(!empty($message)) { echo '<div class="message">'.htmlspecialchars($message).'</div>'; } ?>
    <form method="post">
      <label>Full Name</label>
      <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required />

      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />

      <label>Phone</label>
      <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" />

      <label>Address</label>
      <textarea name="address"><?php echo htmlspecialchars($address); ?></textarea>

      <label>Gender</label>
      <select name="gender">
        <option value="Male" <?php if($gender==='Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if($gender==='Female') echo 'selected'; ?>>Female</option>
        <option value="Other" <?php if($gender==='Other') echo 'selected'; ?>>Other</option>
      </select>

      <label>Date of Birth</label>
      <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>" />

      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required />

      <label>New Password (leave blank to keep current)</label>
      <input type="password" name="password" />

      <label>Confirm New Password</label>
      <input type="password" name="confirm_password" />

      <label>User Type</label>
      <select name="user_type">
        <option value="admin" <?php if($user_type==='admin') echo 'selected'; ?>>Admin</option>
        <option value="user" <?php if($user_type==='user') echo 'selected'; ?>>User</option>
      </select>

      <button type="submit" class="btn-submit">Save Changes</button>
    </form>
  </main>
</div>
</body>
</html>

