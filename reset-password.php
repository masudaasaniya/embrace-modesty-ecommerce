<?php
session_start();

// Check if security verified
if (!isset($_SESSION['security_verified']) || $_SESSION['security_verified'] !== true) {
    unset($_SESSION['security_verified']);
    header("Location: forgot_password.php");
    exit();
}

// Keep backward compat (PHP 5.x)
$user_id = isset($_SESSION['reset_user_id']) ? $_SESSION['reset_user_id'] : 0;
$email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : (isset($_SESSION['reset_user_email']) ? $_SESSION['reset_user_email'] : 'unknown');

$error = isset($_SESSION['reset_password_error']) ? $_SESSION['reset_password_error'] : '';
$success = isset($_SESSION['reset_password_success']) ? $_SESSION['reset_password_success'] : '';
if (isset($_SESSION['reset_password_error'])) unset($_SESSION['reset_password_error']);
if (isset($_SESSION['reset_password_success'])) unset($_SESSION['reset_password_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
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
            width: 380px;
            margin: 70px auto;
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
        .info-box {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            font-size: 14px;
            color: #823c49;
        }
        input {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 25px;
            margin-bottom: 8px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }
        input:focus {
            border-color: #823c49;
            box-shadow: 0 0 8px rgba(130, 60, 73, 0.3);
        }
        input.valid {
            border-color: #28a745 !important;
            background: #f0fff4 !important;
        }
        input.invalid {
            border-color: #dc3545 !important;
            background: #fff5f5 !important;
        }
        .success-icon {
            color: #28a745;
            margin-left: 5px;
        }
        .password-strength {
            height: 6px;
            border-radius: 3px;
            margin-top: 6px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
            border-radius: 3px;
        }
        .strength-weak { width: 33%; background: #dc3545; }
        .strength-medium { width: 66%; background: #ffc107; }
        .strength-strong { width: 100%; background: #28a745; }
        .requirements {
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        .requirements ul {
            margin: 0;
            padding-left: 18px;
        }
        .requirements li {
            margin: 2px 0;
        }
        .requirements li.met {
            color: #28a745;
        }
        button {
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 25px;
            background: #823c49;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            margin-top: 15px;
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
            margin-top: 15px;
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
        <?php } ?>
        <?php if ($success !== '') { ?>
            <div class="msg ok"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

        <div class="info-box">
            Resetting password for: <?php echo htmlspecialchars($email); ?>
        </div>

        <form method="post" action="update-password.php" id="resetForm">
            
            <label>New Password <span class="success-icon" id="password-icon"></span></label>
            <input type="password" name="new_password" id="new_password" 
                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}$"
                   title="Password must contain at least 6 characters, including uppercase, lowercase and number"
                   placeholder="Create a strong password" required>
            <div class="password-strength">
                <div class="password-strength-bar" id="strength-bar"></div>
            </div>
            
            <div class="requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li id="req-length">At least 6 characters</li>
                    <li id="req-upper">At least 1 uppercase letter (A-Z)</li>
                    <li id="req-lower">At least 1 lowercase letter (a-z)</li>
                    <li id="req-number">At least 1 number (0-9)</li>
                </ul>
            </div>
            
            <label>Confirm Password <span class="success-icon" id="confirm-icon"></span></label>
            <input type="password" name="confirm_password" id="confirm_password" 
                   placeholder="Confirm your password" required>
            
            <button type="submit" id="submitBtn">Update Password</button>
        </form>

        <div class="link"><a href="forgot_password.php">Start Over</a></div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== PASSWORD STRENGTH METER =====
    const passwordInput = document.getElementById('new_password');
    const strengthBar = document.getElementById('strength-bar');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Check requirements
        const hasLength = password.length >= 6;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        
        // Update requirement indicators
        document.getElementById('req-length').classList.toggle('met', hasLength);
        document.getElementById('req-upper').classList.toggle('met', hasUpper);
        document.getElementById('req-lower').classList.toggle('met', hasLower);
        document.getElementById('req-number').classList.toggle('met', hasNumber);
        
        // Calculate strength
        if (hasLength) strength++;
        if (hasUpper) strength++;
        if (hasLower) strength++;
        if (hasNumber) strength++;
        
        // Update strength bar
        strengthBar.className = 'password-strength-bar';
        if (password.length > 0) {
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 3) {
                strengthBar.classList.add('strength-medium');
            } else if (strength === 4) {
                strengthBar.classList.add('strength-strong');
            }
        }
        
        // Update validation icon
        const icon = document.getElementById('password-icon');
        if (this.checkValidity()) {
            icon.innerHTML = '✓';
            icon.style.color = '#28a745';
        } else {
            icon.innerHTML = '';
        }
    });
    
    // ===== CONFIRM PASSWORD VALIDATION =====
    const confirmInput = document.getElementById('confirm_password');
    
    confirmInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const confirm = this.value;
        
        if (confirm.length > 0) {
            if (password === confirm) {
                this.classList.remove('invalid');
                this.classList.add('valid');
                document.getElementById('confirm-icon').innerHTML = '✓';
                document.getElementById('confirm-icon').style.color = '#28a745';
            } else {
                this.classList.remove('valid');
                this.classList.add('invalid');
                document.getElementById('confirm-icon').innerHTML = '✗';
                document.getElementById('confirm-icon').style.color = '#dc3545';
            }
        } else {
            this.classList.remove('valid', 'invalid');
            document.getElementById('confirm-icon').innerHTML = '';
        }
    });
    
});
</script>
</body>
</html>

