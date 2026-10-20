<?php
/**
 * Forgot Password - Admin
 * Embrace Modesty E-Commerce
 */

require_once 'config/database.php';

$error = '';
$success = '';
$debugResetLink = '';

function ensurePasswordResetTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS admin_password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        token_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        used TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_admin_id (admin_id),
        INDEX idx_token_hash (token_hash)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return $conn->query($sql);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';

    if ($identifier === '') {
        $error = 'Please enter your username or email.';
    } else {
        if (!ensurePasswordResetTable($conn)) {
            $error = 'Unable to process request right now. Please try again later.';
        } else {
            $admin = false;
            $stmt = $conn->prepare("SELECT id, username, email FROM admin_users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("ss", $identifier, $identifier);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows === 1) {
                        $admin = $result->fetch_assoc();
                    }
                }
                $stmt->close();
            }

            // Keep response generic to avoid username/email enumeration.
            $success = 'If an account exists, a reset link has been generated.';

            if ($admin) {
                $token = sha1(uniqid(mt_rand(), true));
                $tokenHash = hash('sha256', $token);
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $deleteStmt = $conn->prepare("DELETE FROM admin_password_resets WHERE admin_id = ?");
                if ($deleteStmt) {
                    $deleteStmt->bind_param("i", $admin['id']);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                }

                $insertStmt = $conn->prepare("INSERT INTO admin_password_resets (admin_id, token_hash, expires_at, used) VALUES (?, ?, ?, 0)");
                if ($insertStmt) {
                    $insertStmt->bind_param("iss", $admin['id'], $tokenHash, $expiresAt);
                    if ($insertStmt->execute()) {
                        $resetLink = ADMIN_URL . 'reset_password.php?token=' . urlencode($token);
                        $debugResetLink = $resetLink;

                        if (!empty($admin['email'])) {
                            $subject = 'Admin Password Reset';
                            $message = "Hello " . $admin['username'] . ",\n\n";
                            $message .= "Use the link below to reset your password:\n";
                            $message .= $resetLink . "\n\n";
                            $message .= "This link expires in 1 hour.\n";
                            $message .= "If you did not request this, ignore this message.";
                            @mail($admin['email'], $subject, $message);
                        }
                    } else {
                        $error = 'Unable to create reset request.';
                        $success = '';
                    }
                    $insertStmt->close();
                } else {
                    $error = 'Unable to create reset request.';
                    $success = '';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Admin</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23823c49'/%3E%3Ctext x='50%25' y='58%25' dominant-baseline='middle' text-anchor='middle' font-size='34' fill='white' font-family='Arial'%3EEM%3C/text%3E%3C/svg%3E">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6e9ea; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { width: 100%; max-width: 460px; background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); padding: 24px; }
        h1 { margin: 0 0 10px; font-size: 24px; color: #823c49; text-align: center; }
        p { margin: 0 0 16px; font-size: 14px; color: #666; text-align: center; }
        .alert { padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
        .alert-error { background: #fdecef; color: #8c2435; border: 1px solid #f3bcc7; }
        .alert-success { background: #e8f7ee; color: #1f6d3d; border: 1px solid #bde8cc; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #333; }
        input { width: 100%; box-sizing: border-box; padding: 10px; margin-bottom: 14px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; }
        button { width: 100%; padding: 10px; border: 0; border-radius: 8px; background: #823c49; color: #fff; font-size: 15px; cursor: pointer; }
        button:hover { background: #6f303c; }
        .links { margin-top: 12px; text-align: center; }
        .links a { color: #823c49; text-decoration: none; font-size: 13px; }
        .links a:hover { text-decoration: underline; }
        .debug-link { word-break: break-all; font-size: 12px; margin-top: 8px; }
    </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
    <div class="card">
        <h1>Forgot Password</h1>
        <p>Enter your username or email to reset your password.</p>

        <?php if ($error !== '') { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <?php if ($success !== '') { ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($debugResetLink !== '') { ?>
                    <div class="debug-link"><strong>Reset Link:</strong> <a href="<?php echo htmlspecialchars($debugResetLink, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($debugResetLink, ENT_QUOTES, 'UTF-8'); ?></a></div>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="post" action="">
            <label for="identifier">Username or Email</label>
            <input type="text" id="identifier" name="identifier" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <div class="links">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</body>
</html>

