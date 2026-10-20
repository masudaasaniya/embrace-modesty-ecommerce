<?php
session_start();
include("connect.php");
require_once("mail_config.php");

$error = "";
$success = "";
$debugOtp = "";

function ensureUserOtpTable($con) {
    $sql = "CREATE TABLE IF NOT EXISTS user_password_otp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        otp_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        verified TINYINT(1) NOT NULL DEFAULT 0,
        used TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return mysqli_query($con, $sql);
}

function smtpRead($fp) {
    $data = '';
    while (!feof($fp)) {
        $line = fgets($fp, 515);
        if ($line === false) {
            break;
        }
        $data .= $line;
        if (strlen($line) < 4) {
            break;
        }
        if (substr($line, 3, 1) === ' ') {
            break;
        }
    }
    return $data;
}

function smtpCodeOk($response, $allowedCodes) {
    $code = intval(substr($response, 0, 3));
    return in_array($code, $allowedCodes);
}

function smtpSendMail($toEmail, $subject, $body, &$errorText) {
    $errorText = '';

    if (!SMTP_ENABLED || SMTP_USERNAME === '' || SMTP_PASSWORD === '') {
        $errorText = 'SMTP not configured.';
        return false;
    }

    $transportHost = SMTP_HOST;
    if (SMTP_SECURE === 'ssl') {
        $transportHost = 'ssl://' . SMTP_HOST;
    }

    $fp = @fsockopen($transportHost, SMTP_PORT, $errno, $errstr, 15);
    if (!$fp) {
        $errorText = 'SMTP connect failed: ' . $errstr;
        return false;
    }

    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(220))) {
        fclose($fp);
        $errorText = 'SMTP greeting failed.';
        return false;
    }

    fwrite($fp, "EHLO localhost\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(250))) {
        fclose($fp);
        $errorText = 'EHLO failed.';
        return false;
    }

    if (SMTP_SECURE === 'tls') {
        fwrite($fp, "STARTTLS\r\n");
        $resp = smtpRead($fp);
        if (!smtpCodeOk($resp, array(220))) {
            fclose($fp);
            $errorText = 'STARTTLS failed.';
            return false;
        }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp);
            $errorText = 'TLS crypto failed.';
            return false;
        }
        fwrite($fp, "EHLO localhost\r\n");
        $resp = smtpRead($fp);
        if (!smtpCodeOk($resp, array(250))) {
            fclose($fp);
            $errorText = 'EHLO after TLS failed.';
            return false;
        }
    }

    fwrite($fp, "AUTH LOGIN\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(334))) {
        fclose($fp);
        $errorText = 'AUTH LOGIN failed.';
        return false;
    }

    fwrite($fp, base64_encode(SMTP_USERNAME) . "\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(334))) {
        fclose($fp);
        $errorText = 'SMTP username rejected.';
        return false;
    }

    fwrite($fp, base64_encode(SMTP_PASSWORD) . "\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(235))) {
        fclose($fp);
        $errorText = 'SMTP password rejected.';
        return false;
    }

    fwrite($fp, "MAIL FROM:<" . SMTP_FROM_EMAIL . ">\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(250))) {
        fclose($fp);
        $errorText = 'MAIL FROM rejected.';
        return false;
    }

    fwrite($fp, "RCPT TO:<" . $toEmail . ">\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(250, 251))) {
        fclose($fp);
        $errorText = 'RCPT TO rejected.';
        return false;
    }

    fwrite($fp, "DATA\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(354))) {
        fclose($fp);
        $errorText = 'DATA command rejected.';
        return false;
    }

    $headers = '';
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "To: <" . $toEmail . ">\r\n";
    $headers .= "\r\n";

    fwrite($fp, $headers . $body . "\r\n.\r\n");
    $resp = smtpRead($fp);
    if (!smtpCodeOk($resp, array(250))) {
        fclose($fp);
        $errorText = 'Message body rejected.';
        return false;
    }

    fwrite($fp, "QUIT\r\n");
    smtpRead($fp);
    fclose($fp);
    return true;
}

function sendOtpEmail($toEmail, $username, $otp) {
    // Try PHPMailer first
    require_once("phpmailer_func.php");
    if (function_exists('sendEmailWithPHPMailer')) {
        if (sendEmailWithPHPMailer($toEmail, $username, $otp)) {
            return true;
        }
    }
    
    // Fallback to custom SMTP
    $subject = "Embrace Modesty - Password Reset OTP";
    $body = "Hello " . $username . ",\r\n\r\n";
    $body .= "Your OTP for password reset is: " . $otp . "\r\n";
    $body .= "This OTP is valid for 10 minutes.\r\n\r\n";
    $body .= "If you did not request this, please ignore this email.\r\n";
    $body .= "Thank you,\r\nEmbrace Modesty";

    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $smtpError = '';
    if (smtpSendMail($toEmail, $subject, $body, $smtpError)) {
        return true;
    }

    // Final fallback to PHP mail()
    return @mail($toEmail, $subject, $body, $headers);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = isset($_POST["identifier"]) ? trim($_POST["identifier"]) : "";

    if ($identifier === "") {
        $error = "Please enter username or email.";
    } else {
        if (!ensureUserOtpTable($con)) {
            $error = "Unable to process request right now.";
        } else {
            $user = false;
            $stmt = mysqli_prepare($con, "SELECT id, username, email FROM `user` WHERE username = ? OR email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($result && mysqli_num_rows($result) === 1) {
                    $user = mysqli_fetch_assoc($result);
                }
                mysqli_stmt_close($stmt);
            }

            $success = "If account exists, OTP has been sent to your email. Please check inbox/spam folder.";

            if ($user) {
                $otp = strval(mt_rand(100000, 999999));
                $otpHash = hash("sha256", $otp);
                $expiresAt = date("Y-m-d H:i:s", time() + 600);

                $delStmt = mysqli_prepare($con, "DELETE FROM user_password_otp WHERE user_id = ?");
                if ($delStmt) {
                    $uid = intval($user["id"]);
                    mysqli_stmt_bind_param($delStmt, "i", $uid);
                    mysqli_stmt_execute($delStmt);
                    mysqli_stmt_close($delStmt);
                }

                $insStmt = mysqli_prepare($con, "INSERT INTO user_password_otp (user_id, otp_hash, expires_at, verified, used) VALUES (?, ?, ?, 0, 0)");
                if ($insStmt) {
                    $uid = intval($user["id"]);
                    mysqli_stmt_bind_param($insStmt, "iss", $uid, $otpHash, $expiresAt);
                    if (mysqli_stmt_execute($insStmt)) {
                        $requestId = mysqli_insert_id($con);
                        $_SESSION["otp_request_id"] = $requestId;
                        $_SESSION["otp_user_id"] = $uid;

                        if (!empty($user["email"])) {
                            $mailOk = sendOtpEmail($user["email"], $user["username"], $otp);
                            $_SESSION["otp_mail_sent"] = $mailOk ? "1" : "0";
                            if (!$mailOk) {
                                $logLine = date("Y-m-d H:i:s") . " | user_id=" . $uid . " | email=" . $user["email"] . " | otp=" . $otp . "\r\n";
                                @file_put_contents(__DIR__ . "\\otp_mail_fallback.log", $logLine, FILE_APPEND);
                            }
                        } else {
                            $_SESSION["otp_mail_sent"] = "0";
                        }

                        $debugOtp = $otp;
                        $_SESSION["debug_otp_value"] = $otp;
                        header("Location: verify_otp_user.php?request_id=" . urlencode($requestId) . "&sent=1");
                        exit();
                    } else {
                        $error = "Unable to generate OTP.";
                        $success = "";
                    }
                    mysqli_stmt_close($insStmt);
                } else {
                    $error = "Unable to generate OTP.";
                    $success = "";
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
    <title>Forgot Password</title>
    <style>
        body { margin:0; padding:0; font-family:Arial,sans-serif; background:url('rform2.jpg') no-repeat center center fixed; background-size:cover; }
        .box { width:360px; margin:90px auto; padding:28px; border-radius:20px; background:#fff; box-shadow:0 6px 18px rgba(130,60,73,0.3); }
        h2 { text-align:center; color:#823c49; margin-bottom:16px; }
        input { width:100%; box-sizing:border-box; padding:11px; border:1px solid #ccc; border-radius:25px; margin-bottom:12px; }
        button { width:100%; padding:11px; border:0; border-radius:25px; background:#823c49; color:#fff; font-size:15px; cursor:pointer; }
        button:hover { background:#a55267; }
        .msg { text-align:center; margin-bottom:10px; font-size:14px; }
        .err { color:#b3261e; }
        .ok { color:#1d6f42; }
        .link { text-align:center; margin-top:12px; }
        .link a { color:#823c49; text-decoration:none; }
        .link a:hover { text-decoration:underline; }
    </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>
    <div class="box">
        <h2>Forgot Password</h2>
        <?php if ($error !== "") { ?><div class="msg err"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <?php if ($success !== "") { ?><div class="msg ok"><?php echo htmlspecialchars($success); ?></div><?php } ?>
        <?php if ($debugOtp !== "") { ?><div class="msg ok">OTP: <strong><?php echo htmlspecialchars($debugOtp); ?></strong></div><?php } ?>

        <form method="post">
            <input type="text" name="identifier" placeholder="Username or Email" required>
            <button type="submit">Send OTP</button>
        </form>

        <div class="link"><a href="loginform.php">Back to Login</a></div>
    </div>
</body>
</html>

