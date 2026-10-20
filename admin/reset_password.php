<?php
/**
 * Reset Password - Admin
 * Embrace Modesty E-Commerce
 */

require_once 'config/database.php';

$error = '';
$success = '';
$token = isset($_REQUEST['token']) ? trim($_REQUEST['token']) : '';
$tokenHash = hash('sha256', $token);
$resetRow = false;

function findResetRow($conn, $tokenHash) {
    $sql = "SELECT r.id, r.admin_id, r.expires_at, r.used, u.username
            FROM admin_password_resets r
            LEFT JOIN admin_users u ON u.id = r.admin_id
            WHERE r.token_hash = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("s", $tokenHash);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $row = false;
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
    }
    $stmt->close();
    return $row;
}

if ($token === '') {
    $error = 'Invalid reset link.';
} else {
    $resetRow = findResetRow($conn, $tokenHash);
    if (!$resetRow) {
        $error = 'Invalid reset link.';
    } else {
        if (intval($resetRow['used']) === 1) {
            $error = 'This reset link has already been used.';
        } elseif (strtotime($resetRow['expires_at']) < time()) {
            $error = 'This reset link has expired.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $error === '') {
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $error = 'Password must include at least one letter and one number.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password and confirm password do not match.';
    } else {
        $passwordToStore = '';
        if (function_exists('password_hash')) {
            $passwordToStore = password_hash($newPassword, PASSWORD_BCRYPT);
        } else {
            $passwordToStore = md5($newPassword);
        }

        if ($passwordToStore === false || $passwordToStore === '') {
            $error = 'Unable to process password reset.';
        } else {
            $updateStmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            if ($updateStmt) {
                $adminId = intval($resetRow['admin_id']);
                $updateStmt->bind_param("si", $passwordToStore, $adminId);
                $passwordUpdated = $updateStmt->execute();
                $updateStmt->close();

                if ($passwordUpdated) {
                    $markStmt = $conn->prepare("UPDATE admin_password_resets SET used = 1 WHERE id = ?");
                    if ($markStmt) {
                        $resetId = intval($resetRow['id']);
                        $markStmt->bind_param("i", $resetId);
                        $markStmt->execute();
                        $markStmt->close();
                    }
                    header('Location: index.php?reset=1');
                    exit;
                } else {
                    $error = 'Failed to update password.';
                }
            } else {
                $error = 'Failed to update password.';
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
    <title>Reset Password - Admin</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23823c49'/%3E%3Ctext x='50%25' y='58%25' dominant-baseline='middle' text-anchor='middle' font-size='34' fill='white' font-family='Arial'%3EEM%3C/text%3E%3C/svg%3E">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6e9ea; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { width: 100%; max-width: 460px; background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); padding: 24px; }
        h1 { margin: 0 0 10px; font-size: 24px; color: #823c49; text-align: center; }
        p { margin: 0 0 16px; font-size: 14px; color: #666; text-align: center; }
        .alert { padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
        .alert-error { background: #fdecef; color: #8c2435; border: 1px solid #f3bcc7; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #333; }
        input { width: 100%; box-sizing: border-box; padding: 10px; margin-bottom: 14px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; }
        button { width: 100%; padding: 10px; border: 0; border-radius: 8px; background: #823c49; color: #fff; font-size: 15px; cursor: pointer; }
        button:hover { background: #6f303c; }
        .links { margin-top: 12px; text-align: center; }
        .links a { color: #823c49; text-decoration: none; font-size: 13px; }
        .links a:hover { text-decoration: underline; }
    </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
    <div class="card">
        <h1>Reset Password</h1>
        <p>Set a new password for your admin account.</p>

        <?php if ($error !== '') { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="links"><a href="forgot_password.php">Request new reset link</a></div>
        <?php } else { ?>
            <form method="post" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit">Update Password</button>
            </form>
            <div class="links"><a href="index.php">Back to Login</a></div>
        <?php } ?>
    </div>
</body>
</html>

