<?php
/**
 * Admin Login Page
 * Embrace Modesty E-Commerce
 */

require_once 'config/database.php';

$error = '';
$success = '';

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $success = 'Password updated successfully. Please sign in.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        // Define allowed admin credentials
        $validAdmins = array(
            'Admin' => 'Admin@123',
            'aaliya' => 'Aalu@123',
            'amyy' => 'Amy@123'
        );

        // Check if user is in the predefined list
        if (isset($validAdmins[$username]) && $password === $validAdmins[$username]) {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_role'] = 'super_admin';
            $_SESSION['admin_login_time'] = time();

            header('Location: dashboard.php');
            exit();
        }

        // Also check database for additional admins
        $stmt = $conn->prepare("SELECT id, username, password, role FROM admin_users WHERE username = ? AND status = 'active'");

        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $admin = $result->fetch_assoc();

                // Verify password (supports modern hash APIs and legacy plain/md5)
                $validPassword = false;

                if (function_exists('password_get_info') && function_exists('password_verify')) {
                    $passwordInfo = password_get_info($admin['password']);
                    if (isset($passwordInfo['algo']) && $passwordInfo['algo'] !== 0) {
                        $validPassword = password_verify($password, $admin['password']);
                    } else {
                        $validPassword = ($password === $admin['password']) || (md5($password) === $admin['password']);
                    }
                } else {
                    $validPassword = ($password === $admin['password']) || (md5($password) === $admin['password']);
                }

                if ($validPassword) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['admin_login_time'] = time();

                    header('Location: dashboard.php');
                    exit();
                }
            }

            $stmt->close();
        }

        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Embrace Modesty</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23823c49'/%3E%3Ctext x='50%25' y='58%25' dominant-baseline='middle' text-anchor='middle' font-size='34' fill='white' font-family='Arial'%3EEM%3C/text%3E%3C/svg%3E">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6e9ea;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            padding: 24px;
        }
        h1 {
            margin: 0 0 16px;
            font-size: 24px;
            color: #823c49;
            text-align: center;
        }
        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .alert-error {
            background: #fdecef;
            color: #8c2435;
            border: 1px solid #f3bcc7;
        }
        .alert-success {
            background: #e8f7ee;
            color: #1f6d3d;
            border: 1px solid #bde8cc;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #333;
        }
        input {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin-bottom: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            border: 0;
            border-radius: 8px;
            background: #823c49;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }
        button:hover {
            background: #6f303c;
        }
        .text-end {
            text-align: right;
            margin: 4px 0 14px;
        }
        .text-end a {
            color: #823c49;
            font-size: 13px;
            text-decoration: none;
        }
        .text-end a:hover {
            text-decoration: underline;
        }
    </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
    <div class="card">
        <h1>Admin Login</h1>

        <?php if (!empty($error)) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <?php if (!empty($success)) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <form method="post" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <div class="text-end"><a href="forgot_password.php">Forgot Password?</a></div>

            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>

