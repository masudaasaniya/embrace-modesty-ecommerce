<?php
session_start();
include("connect.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginform.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$loadError = "";
$user = array(
    'id' => $user_id,
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'date' => '',
    'gender' => 'Male',
    'username' => ''
);

// Fetch user data safely using procedural mysqli (supports `user` and `users` table)
$stmt = mysqli_prepare($con, "SELECT id, name, email, phone, address, date, gender, username, password FROM `user` WHERE id=? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if (empty($user['name'])) {
    $stmt = mysqli_prepare($con, "SELECT id, name, email, phone, address, date, gender, username, password FROM `users` WHERE id=? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
}

if (empty($user['name'])) {
    $loadError = "Unable to load profile data.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['full_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $dob = mysqli_real_escape_string($con, $_POST['dob']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $message = "Passwords do not match!";
        } else {
            if (function_exists('password_hash')) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $hashedPassword = md5($password);
            }
        }
    } else {
        $hashedPassword = $user['password'];
    }

    if ($message === "") {
        $update_stmt = mysqli_prepare($con, "UPDATE `user` SET name=?, email=?, phone=?, address=?, date=?, gender=?, username=?, password=? WHERE id=?");
        $updated = false;
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "ssssssssi", $name, $email, $phone, $address, $dob, $gender, $username, $hashedPassword, $user_id);
            $updated = mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }

        if (!$updated) {
            $update_stmt = mysqli_prepare($con, "UPDATE `users` SET name=?, email=?, phone=?, address=?, date=?, gender=?, username=?, password=? WHERE id=?");
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "ssssssssi", $name, $email, $phone, $address, $dob, $gender, $username, $hashedPassword, $user_id);
                $updated = mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
        }

        if ($updated) {
            $message = "Profile updated successfully!";
            // Update user array for form display
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['address'] = $address;
            $user['date'] = $dob;
            $user['gender'] = $gender;
            $user['username'] = $username;
        } else {
            $message = "Error updating profile: " . mysqli_error($con);
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#fff; color:#333; }
        .header { background: linear-gradient(to bottom, #f8ecef,#f6e9ea); padding:20px 50px; display:flex; justify-content:space-between; align-items:center; }
        .header h1 { color:#823c49; margin:0; }
        .nav-links a { text-decoration:none; font-weight:bold; color:#823c49; margin-left:20px; padding:8px 16px; border-radius:6px; transition:background .3s; }
        .container { display:flex; max-width:1000px; margin:40px auto; background:#fff; box-shadow:0 12px 24px rgba(255,105,180,.3); border-radius:20px; overflow:hidden; }
        .sidebar { width:200px; background:#823c49; padding:30px 20px; display:flex; flex-direction:column; gap:15px; }
        .sidebar a {
            color:#333;
            text-decoration:none;
            padding:10px;
            border-radius:8px;
            font-weight:700;
            background:#f7e7ee;
            border:1px solid #e8c8d4;
        }
        .sidebar a.active, .sidebar a:hover { background:#ffb6d4; color:#000; }
        .content { flex:1; padding:40px 50px; }
        h1.content-title { color:#823c49; margin-top:0; }
        .message { background:#ffe0ec; border:1px solid #ff99cc; color:#b3005c; padding:10px; margin-bottom:20px; font-weight:bold; border-radius:6px; }
        form { display:flex; flex-direction:column; }
        label { font-weight:600; color:#823c49; margin-bottom:6px; }
        input, select { padding:10px; margin-bottom:20px; border:1px solid #f0c1d7; border-radius:6px; background:#fff0f7; color:#5a0a44; font-size:1rem; }
        input:focus, select:focus { border-color:#ff3399; outline:none; }
        .btn-submit { background:#823c49; color:#fff; padding:12px 25px; border:none; border-radius:25px; font-size:1rem; font-weight:700; cursor:pointer; }
        .btn-submit:hover { background:#6a003f; }
    </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
    <div class="header">
        <h1>User - Edit Info</h1>
        <div class="nav-links">
            <a href="homepage2.php">Home</a>
            <a href="shoppage2.php">Shop</a>
            <a href="blogpage.php">Blog</a>
        </div>
    </div>

    <div class="container">
        <aside class="sidebar">
            <a href="myaccount.php">Account Overview</a>
            <a href="edit.php" class="active">Edit Info</a>
            <a href="orders.php">My Order</a>
            <a href="cart.php">Cart</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="logoutuser.php">Logout</a>
        </aside>

        <main class="content">
            <h1 class="content-title">Update Profile</h1>

            <?php if (!empty($loadError)): ?>
                <div class="message"><?php echo htmlspecialchars($loadError); ?></div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="post">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="Name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

                <label>Date of Birth</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($user['date']); ?>" required>

                <label>Gender</label>
                <select name="gender" required>
                    <option value="Male" <?php if($user['gender']=='Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if($user['gender']=='Female') echo 'selected'; ?>>Female</option>
                    <option value="Other" <?php if($user['gender']=='Other') echo 'selected'; ?>>Other</option>
                </select>

                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

                <label>New Password</label>
                <input type="password" name="password">

                <label>Confirm New Password</label>
                <input type="password" name="confirm_password">

                <button type="submit" class="btn-submit">Save Changes</button>
            </form>
        </main>
    </div>
</body>
</html>

