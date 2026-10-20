<?php
session_start();

$error = isset($_SESSION['forgot_password_error']) ? $_SESSION['forgot_password_error'] : '';
$success = isset($_SESSION['forgot_password_success']) ? $_SESSION['forgot_password_success'] : '';
unset($_SESSION['forgot_password_error'], $_SESSION['forgot_password_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
    <link rel="shortcut icon" href="/php%20e-com/favicon.ico">
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
        .ok { color: #1d6f42; }
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
        <h2>Forgot Password</h2>

        <?php if ($error !== '') { ?>
            <div class="msg err"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>
        <?php if ($success !== '') { ?>
            <div class="msg ok"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

<form method="post" action="process-forgot-password.php">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Next</button>
        </form>

        <div class="link"><a href="loginform.php">Back to Login</a></div>
    </div>
</body>
</html>
