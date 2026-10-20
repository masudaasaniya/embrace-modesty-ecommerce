<?php
session_start();
include('connect.php');

function redirectToResetWithMessage($token, $message) {
    $_SESSION['reset_password_error'] = $message;
    header('Location: reset_password.php?token=' . urlencode($token));
    exit();
}

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit();
}

$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if ($token === '') {
    header('Location: forgot_password.php');
    exit();
}

if (strlen($newPassword) < 6) {
    redirectToResetWithMessage($token, 'Password must be at least 6 characters.');
}

if ($newPassword !== $confirmPassword) {
    redirectToResetWithMessage($token, 'Passwords do not match.');
}

if (!ensurePasswordResetTable($con)) {
    redirectToResetWithMessage($token, 'Unable to process request right now.');
}

$tokenHash = hash('sha256', $token);
$stmt = mysqli_prepare($con, "SELECT id, user_id, expires_at, used FROM user_password_resets WHERE token_hash = ? LIMIT 1");
if (!$stmt) {
    redirectToResetWithMessage($token, 'Invalid or expired reset link.');
}

mysqli_stmt_bind_param($stmt, 's', $tokenHash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$resetRow = ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if (!$resetRow) {
    redirectToResetWithMessage($token, 'Invalid or expired reset link.');
}

if (intval($resetRow['used']) === 1 || strtotime($resetRow['expires_at']) < time()) {
    redirectToResetWithMessage($token, 'Invalid or expired reset link.');
}

if (!function_exists('password_hash')) {
    redirectToResetWithMessage($token, 'Server does not support secure password hashing.');
}

$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    redirectToResetWithMessage($token, 'Could not secure password. Try again.');
}

$userId = intval($resetRow['user_id']);
$updateUserStmt = mysqli_prepare($con, "UPDATE `user` SET password = ? WHERE id = ?");
if (!$updateUserStmt) {
    redirectToResetWithMessage($token, 'Failed to update password.');
}

mysqli_stmt_bind_param($updateUserStmt, 'si', $passwordHash, $userId);
$updated = mysqli_stmt_execute($updateUserStmt);
mysqli_stmt_close($updateUserStmt);

if (!$updated) {
    redirectToResetWithMessage($token, 'Failed to update password.');
}

$markUsedStmt = mysqli_prepare($con, "UPDATE user_password_resets SET used = 1 WHERE id = ?");
if ($markUsedStmt) {
    $resetId = intval($resetRow['id']);
    mysqli_stmt_bind_param($markUsedStmt, 'i', $resetId);
    mysqli_stmt_execute($markUsedStmt);
    mysqli_stmt_close($markUsedStmt);
}

$cleanupStmt = mysqli_prepare($con, "UPDATE user_password_resets SET used = 1 WHERE user_id = ? AND used = 0");
if ($cleanupStmt) {
    mysqli_stmt_bind_param($cleanupStmt, 'i', $userId);
    mysqli_stmt_execute($cleanupStmt);
    mysqli_stmt_close($cleanupStmt);
}

header('Location: loginform.php?reset=1');
exit();

