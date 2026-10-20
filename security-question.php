<?php
session_start();

// Check if user came from forgot password flow
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['questions']) || !isset($_SESSION['question_step'])) {
    header("Location: forgot_password.php");
    exit();
}

// Get session variables
$email = $_SESSION['reset_email'];
$questions = $_SESSION['questions'];
$step = $_SESSION['question_step'];
$attempts = isset($_SESSION['security_attempts']) ? $_SESSION['security_attempts'] : 0;
$max_attempts = 5;

// Current question
$current_question = isset($questions[$step]) ? $questions[$step] : '';
if (empty($current_question)) {
    header("Location: forgot_password.php");
    exit();
}

$error = isset($_SESSION['security_answer_error']) ? $_SESSION['security_answer_error'] : '';
$success = isset($_SESSION['security_answer_success']) ? $_SESSION['security_answer_success'] : '';
unset($_SESSION['security_answer_error'], $_SESSION['security_answer_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Question Verification</title>
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
        .question-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #823c49;
        }
        .question-label {
            font-weight: bold;
            color: #823c49;
            display: block;
            margin-bottom: 8px;
        }
        .question-text {
            color: #333;
            font-size: 15px;
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
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .err { color: #b3261e; }
        .ok { color: #1d6f42; }
        .attempts {
            text-align: center;
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }
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
        <h2>Security Verification</h2>

        <?php if ($error !== '') { ?>
            <div class="msg err"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>
        <?php if ($success !== '') { ?>
            <div class="msg ok"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>
        
        <?php if ($step >= 3) { ?>
            <div class="msg ok">All security questions verified! Redirecting...</div>
            <?php header("Location: reset-password.php"); exit(); ?>
        <?php } elseif ($attempts >= $max_attempts) { ?>
            <div class="msg err">Too many failed attempts. Please try again later or contact support.</div>
            <div class="link"><a href="forgot_password.php">Start Over</a></div>
        <?php } else { ?>
            
            <div class="attempts">
                Step <?php echo $step + 1; ?> of 3 | Attempt <?php echo $attempts + 1; ?> of <?php echo $max_attempts; ?>
            </div>

            <form method="post" action="verify-security-answer.php">
                <div class="question-box">
                    <span class="question-label">Security Question:</span>
                    <span class="question-text"><?php echo htmlspecialchars($current_question); ?></span>
                </div>
                
                <input type="text" name="security_answer" placeholder="Enter your answer" required autocomplete="off">
                
                <button type="submit" <?php echo ($attempts >= $max_attempts) ? 'disabled' : ''; ?>>Verify Answer</button>
            </form>

            <div class="link"><a href="forgot_password.php">Use different email</a></div>
            
        <?php } ?> 
    </div>
</body>
</html>

