<?php
session_start();
include("connect.php");

$error = "";
$success = "";
$debugOtp = isset($_SESSION["debug_otp_value"]) ? $_SESSION["debug_otp_value"] : "";
$mailSent = isset($_SESSION["otp_mail_sent"]) ? $_SESSION["otp_mail_sent"] : "";

$requestId = isset($_GET["request_id"]) ? intval($_GET["request_id"]) : 0;
if ($requestId <= 0 && isset($_POST["request_id"])) {
    $requestId = intval($_POST["request_id"]);
}

if ($requestId <= 0) {
    $error = "Invalid OTP request.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $error === "") {
    $otp = isset($_POST["otp"]) ? trim($_POST["otp"]) : "";
    if ($otp === "" || !preg_match('/^[0-9]{6}$/', $otp)) {
        $error = "Enter valid 6-digit OTP.";
    } else {
        $stmt = mysqli_prepare($con, "SELECT id, user_id, otp_hash, expires_at, used FROM user_password_otp WHERE id = ? LIMIT 1");
        $row = false;
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $requestId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result && mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
            }
            mysqli_stmt_close($stmt);
        }

        if (!$row) {
            $error = "OTP request not found.";
        } elseif (intval($row["used"]) === 1) {
            $error = "OTP already used.";
        } elseif (strtotime($row["expires_at"]) < time()) {
            $error = "OTP expired. Please request again.";
        } else {
            $otpHash = hash("sha256", $otp);
            if (!hash_equals($row["otp_hash"], $otpHash)) {
                $error = "Invalid OTP.";
            } else {
                $upd = mysqli_prepare($con, "UPDATE user_password_otp SET verified = 1 WHERE id = ?");
                if ($upd) {
                    mysqli_stmt_bind_param($upd, "i", $requestId);
                    mysqli_stmt_execute($upd);
                    mysqli_stmt_close($upd);
                }
                $_SESSION["otp_verified_request_id"] = $requestId;
                $_SESSION["otp_verified_user_id"] = intval($row["user_id"]);
                header("Location: reset_user_password.php?request_id=" . urlencode($requestId));
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body { margin:0; padding:0; font-family:Arial,sans-serif; background:url('rform2.jpg') no-repeat center center fixed; background-size:cover; }
        .box { width:360px; margin:90px auto; padding:28px; border-radius:20px; background:#fff; box-shadow:0 6px 18px rgba(130,60,73,0.3); }
        h2 { text-align:center; color:#823c49; margin-bottom:16px; }
        input { width:100%; box-sizing:border-box; padding:11px; border:1px solid #ccc; border-radius:25px; margin-bottom:12px; text-align:center; letter-spacing:2px; }
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
        <h2>Verify OTP</h2>
        <?php if (isset($_GET["sent"]) && $_GET["sent"] == "1" && $mailSent === "1") { ?><div class="msg ok">OTP sent to your email. Check inbox/spam folder.</div><?php } ?>
        <?php if (isset($_GET["sent"]) && $_GET["sent"] == "1" && $mailSent !== "1") { ?><div class="msg err">Email could not be sent from server. Use OTP shown below.</div><?php } ?>
        <?php if ($debugOtp !== "") { ?><div class="msg ok">OTP: <strong><?php echo htmlspecialchars($debugOtp); ?></strong></div><?php } ?>
        <?php if ($error !== "") { ?><div class="msg err"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <?php if ($success !== "") { ?><div class="msg ok"><?php echo htmlspecialchars($success); ?></div><?php } ?>

        <form method="post">
            <input type="hidden" name="request_id" value="<?php echo intval($requestId); ?>">
            <input type="text" name="otp" maxlength="6" placeholder="Enter 6-digit OTP" required>
            <button type="submit">Verify OTP</button>
        </form>

        <div class="link"><a href="forgot_password_user.php">Resend OTP</a></div>
    </div>
</body>
</html>
<?php
if (isset($_SESSION["debug_otp_value"])) {
    unset($_SESSION["debug_otp_value"]);
}
if (isset($_SESSION["otp_mail_sent"])) {
    unset($_SESSION["otp_mail_sent"]);
}
?>

