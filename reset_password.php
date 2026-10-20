<?php
session_start();
include('connect.php');

function ensurePasswordResetTable($con) {
    $sql = "CREATE TABLE IF NOT EXISTS user_password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        token_hash CHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        used TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_email (email),
        INDEX idx_token_hash (token_hash),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return mysqli_query($con, $sql);
}

$error = '';
$sessionError = isset($_SESSION['reset_password_error']) ? $_SESSION['reset_password_error'] : '';
unset($_SESSION['reset_password_error']);
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($token === '') {
    $error = 'Invalid reset link.';
} elseif (!ensurePasswordResetTable($con)) {
    $error = 'Unable to process request right now.';
} else {
    $tokenHash = hash('sha256', $token);
    $stmt = mysqli_prepare($con, "SELECT id, expires_at, used FROM user_password_resets WHERE token_hash = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $tokenHash);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $resetRow = ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);
    } else {
        $resetRow = null;
    }

    if (!$resetRow) {
        $error = 'Invalid reset link.';
    } elseif (intval($resetRow['used']) === 1) {
        $error = 'This reset link has already been used.';
    } elseif (strtotime($resetRow['expires_at']) < time()) {
        $error = 'This reset link has expired. Please request a new one.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('rform2.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .box {
            width: 360px;
            margin: 90px auto;
            padding: 28px;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(130, 60, 73, 0.3);
        }
        h2 {
            text-align: center;
            color: #823c49;
            margin-bottom: 16px;
        }
        input {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 25px;
            margin-bottom: 12px;
        }
        button {
            width: 100%;
            padding: 11px;
            border: 0;
            border-radius: 25px;
            background: #823c49;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }
        button:hover {
            background: #a55267;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .err { color: #b3261e; }
        .link {
            text-align: center;
            margin-top: 12px;
        }
        .link a {
            color: #823c49;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
    <div class="box">
        <h2>Reset Password</h2>

        <?php if ($error !== '') { ?>
            <div class="msg err"><?php echo htmlspecialchars($error); ?></div>
            <div class="link"><a href="forgot_password.php">Request New Link</a></div>
        <?php } else { ?>
            <?php if ($sessionError !== '') { ?>
                <div class="msg err"><?php echo htmlspecialchars($sessionError); ?></div>
            <?php } ?>
            <form method="post" action="update_password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Update Password</button>
            </form>
            <div class="link"><a href="loginform.php">Back to Login</a></div>
        <?php } ?>
    </div>
</body>
</html>
