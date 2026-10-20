<?php
session_start();
include('connect.php');
require_once('mail_config.php');

function setForgotMessage($type, $message) {
    if ($type === 'error') {
        $_SESSION['forgot_password_error'] = $message;
    } else {
        $_SESSION['forgot_password_success'] = $message;
    }
}

function redirectForgot() {
    header('Location: forgot_password.php');
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

function generateSecureToken() {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(32));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }
    return sha1(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true));
}

function buildResetLink($token) {
    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    }

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $basePath = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
    $basePath = rtrim($basePath, '/');

    return $scheme . '://' . $host . $basePath . '/reset_password.php?token=' . urlencode($token);
}

function loadPHPMailerManually() {
    $base = __DIR__;
    
    // Try our custom PHPMailer implementation first (in PHPMailer/ folder)
    $phpmailerClass = $base . '/PHPMailer/PHPMailer.php';
    $smtpClass = $base . '/PHPMailer/SMTP.php';
    $exceptionClass = $base . '/PHPMailer/Exception.php';
    if (file_exists($phpmailerClass) && file_exists($smtpClass) && file_exists($exceptionClass)) {
        require_once($exceptionClass);
        require_once($smtpClass);
        require_once($phpmailerClass);
        return class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
    
    // Try Composer autoload
    $autoload = $base . '/vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once($autoload);
        return class_exists('PHPMailer\PHPMailer\PHPMailer');
    }

    // Try standard PHPMailer location
    $phpmailerClass = $base . '/PHPMailer/src/PHPMailer.php';
    $smtpClass = $base . '/PHPMailer/src/SMTP.php';
    $exceptionClass = $base . '/PHPMailer/src/Exception.php';
    if (file_exists($phpmailerClass) && file_exists($smtpClass) && file_exists($exceptionClass)) {
        require_once($exceptionClass);
        require_once($smtpClass);
        require_once($phpmailerClass);
        return class_exists('PHPMailer\PHPMailer\PHPMailer');
    }

    return false;
}

function sendResetEmail($toEmail, $username, $resetLink) {
    if (!loadPHPMailerManually()) {
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;

        if (defined('SMTP_SECURE') && SMTP_SECURE === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = SMTP_PORT;
        } else {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
        }

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Link';

        $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
        $mail->Body = '<p>Hello ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Click the link below to reset your password. This link will expire in 15 minutes.</p>'
            . '<p><a href="' . $safeLink . '">' . $safeLink . '</a></p>'
            . '<p>If you did not request this, please ignore this email.</p>';
        $mail->AltBody = "Hello " . $username . ",\n\n"
            . "Use this link to reset your password (valid for 15 minutes):\n"
            . $resetLink . "\n\n"
            . "If you did not request this, ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setForgotMessage('error', 'Invalid request.');
    redirectForgot();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setForgotMessage('error', 'Please enter a valid email address.');
    redirectForgot();
}

if (!ensurePasswordResetTable($con)) {
    setForgotMessage('error', 'Unable to process request right now. Please try again.');
    redirectForgot();
}

$stmt = mysqli_prepare($con, "SELECT id, username, email FROM `user` WHERE email = ? LIMIT 1");
if (!$stmt) {
    setForgotMessage('error', 'Unable to process request right now. Please try again.');
    redirectForgot();
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = ($result && mysqli_num_rows($result) === 1) ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

// Generic message to prevent email enumeration.
$genericMessage = 'If this email exists, a password reset link has been sent.';

if (!$user) {
    setForgotMessage('success', $genericMessage);
    redirectForgot();
}

$userId = intval($user['id']);
$username = $user['username'];
$userEmail = $user['email'];
$token = generateSecureToken();
$tokenHash = hash('sha256', $token);
$expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 minutes

$clearStmt = mysqli_prepare($con, "UPDATE user_password_resets SET used = 1 WHERE user_id = ? AND used = 0");
if ($clearStmt) {
    mysqli_stmt_bind_param($clearStmt, 'i', $userId);
    mysqli_stmt_execute($clearStmt);
    mysqli_stmt_close($clearStmt);
}

$insertStmt = mysqli_prepare($con, "INSERT INTO user_password_resets (user_id, email, token_hash, expires_at, used) VALUES (?, ?, ?, ?, 0)");
if (!$insertStmt) {
    setForgotMessage('error', 'Unable to process request right now. Please try again.');
    redirectForgot();
}

mysqli_stmt_bind_param($insertStmt, 'isss', $userId, $userEmail, $tokenHash, $expiresAt);
$saved = mysqli_stmt_execute($insertStmt);
mysqli_stmt_close($insertStmt);

if (!$saved) {
    setForgotMessage('error', 'Unable to process request right now. Please try again.');
    redirectForgot();
}

$resetLink = buildResetLink($token);
$mailSent = false;
if (defined('SMTP_ENABLED') && SMTP_ENABLED === true && SMTP_USERNAME !== '' && SMTP_PASSWORD !== '') {
    $mailSent = sendResetEmail($userEmail, $username, $resetLink);
}

if (!$mailSent) {
    $log = date('Y-m-d H:i:s') . " | email=" . $userEmail . " | link=" . $resetLink . "\r\n";
    @file_put_contents(__DIR__ . '/password_reset_mail.log', $log, FILE_APPEND);
}

setForgotMessage('success', $genericMessage);
redirectForgot();

