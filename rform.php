<?php
session_start();
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name     = mysqli_real_escape_string($con, $_POST["name"]);
    $phone    = mysqli_real_escape_string($con, $_POST["phone"]);
    $email    = mysqli_real_escape_string($con, $_POST["email"]);
    $address  = mysqli_real_escape_string($con, $_POST["address"]);
    $gender   = mysqli_real_escape_string($con, $_POST["gender"]);
    $date     = mysqli_real_escape_string($con, $_POST["date"]);
    $username = mysqli_real_escape_string($con, $_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // 3 Security Questions for password recovery
$sq1 = mysqli_real_escape_string($con, isset($_POST["security_question"]) ? $_POST["security_question"] : '');
$a1_normalized = strtolower(trim(isset($_POST["security_answer"]) ? $_POST["security_answer"] : ''));
$sq2 = mysqli_real_escape_string($con, isset($_POST["security_question2"]) ? $_POST["security_question2"] : '');
$a2_normalized = strtolower(trim(isset($_POST["security_answer2"]) ? $_POST["security_answer2"] : ''));
$sq3 = mysqli_real_escape_string($con, isset($_POST["security_question3"]) ? $_POST["security_question3"] : '');
$a3_normalized = strtolower(trim(isset($_POST["security_answer3"]) ? $_POST["security_answer3"] : ''));
// PHP 5.x compatible password hashing for security answers
if (function_exists('password_hash')) {
    $sq1_hash = !empty($sq1) ? password_hash($a1_normalized, PASSWORD_DEFAULT) : NULL;
    $sq2_hash = !empty($sq2) ? password_hash($a2_normalized, PASSWORD_DEFAULT) : NULL;
    $sq3_hash = !empty($sq3) ? password_hash($a3_normalized, PASSWORD_DEFAULT) : NULL;
} else {
    $sq1_hash = !empty($sq1) ? md5($a1_normalized) : NULL;
    $sq2_hash = !empty($sq2) ? md5($a2_normalized) : NULL;
    $sq3_hash = !empty($sq3) ? md5($a3_normalized) : NULL;
}

    // ===== STRONG SERVER-SIDE VALIDATION =====
    
    // Name validation - only alphabets and spaces
    if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
        echo "<script>alert('Name must contain only alphabets!'); window.history.back();</script>";
        exit();
    }
    
    // Mobile number validation - exactly 10 digits
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        echo "<script>alert('Phone number must contain exactly 10 digits!'); window.history.back();</script>";
        exit();
    }

    // Strong password validation: min 6 chars, 1 uppercase, 1 lowercase, 1 number
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}$/', $password)) {
        echo "<script>alert('Password must contain at least 6 characters, including uppercase, lowercase and number!'); window.history.back();</script>";
        exit();
    }

    // Password match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }
    
    // Validate first security question (required)
    if (empty($sq1)) {
        echo "<script>alert('Please select first security question!'); window.history.back();</script>";
        exit();
    }
    if (strlen($a1_normalized) < 2 || !preg_match("/^[a-zA-Z ]+$/", $a1_normalized)) {
        echo "<script>alert('First security answer must be at least 2 alphabets!'); window.history.back();</script>";
        exit();
    }
    // Optional validation for 2 & 3
    if (!empty($sq2) && (strlen($a2_normalized) < 2 || !preg_match("/^[a-zA-Z ]+$/", $a2_normalized))) {
        echo "<script>alert('Security answer 2 must be valid alphabets!'); window.history.back();</script>";
        exit();
    }
    if (!empty($sq3) && (strlen($a3_normalized) < 2 || !preg_match("/^[a-zA-Z ]+$/", $a3_normalized))) {
        echo "<script>alert('Security answer 3 must be valid alphabets!'); window.history.back();</script>";
        exit();
    }

    // Hash password using MD5 (keep consistent)
    $hashed_password = md5($password); 

    // Duplicate check
    $check = mysqli_query($con, "SELECT id FROM `user` WHERE username='$username' OR email='$email'");

    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Username or Email already exists!'); window.history.back();</script>";
        exit();
    }

    // Insert into user table with 3 security questions/answers
    $insert = mysqli_query($con, "INSERT INTO `user`
    (name, phone, email, address, gender, date, username, password, 
     security_question1, security_answer1_hash,
     security_question2, security_answer2_hash, 
     security_question3, security_answer3_hash)
    VALUES
    ('$name','$phone','$email','$address','$gender','$date','$username','$hashed_password',
     '$sq1', '$sq1_hash', '$sq2', '$sq2_hash', '$sq3', '$sq3_hash')"); 

    if ($insert) {
        echo "<script>alert('Registration Successful! Please Login.');</script>";
        echo "<script>window.location.href='loginform.php';</script>";
        exit();
    } else {
        echo "Database Error: " . mysqli_error($con);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration Form</title>

<style>
body {
    margin: 0; padding: 0;
    font-family: 'Arial', sans-serif;
    background: url('rform2.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #823c49;
}
.container {
    width: 420px;
    margin: 60px auto;
    padding: 30px;
    border-radius: 25px;
    background: #fff;
    box-shadow: 0 8px 30px rgba(130, 60, 73, 0.2);
    position: relative;
    overflow: hidden;
}
.container::before {
    content: '';
    position: absolute;
    top: -100px;
    left: -100px;
    width: 300px; height: 300px;
    background: #823c49;
    border-radius: 50%;
    opacity: 0.2;
}
.container::after {
    content: '';
    position: absolute;
    bottom: -80px;
    right: -80px;
    width: 200px; height: 200px;
    background: #fcdce3;
    border-radius: 50%;
    opacity: 0.3;
}
h2 { text-align: center; margin-bottom: 25px; color: #823c49; }
label { display: block; margin-top: 15px; font-weight: bold; font-size: 14px; }

.input-group {
    position: relative;
    margin-bottom: 12px;
}

input, select {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1px solid #ddd;
    border-radius: 30px;
    font-size: 14px;
    background: #fdfdfd;
    color: #823c49;
    outline: none;
    transition: 0.3s;
    box-sizing: border-box;
}

input:disabled, select:disabled {
    background: #f0f0f0;
    cursor: not-allowed;
    opacity: 0.7;
}

input:focus, select:focus {
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

/* Password strength meter */
.password-strength {
    height: 6px;
    border-radius: 3px;
    margin-top: 6px;
    overflow: hidden;
    transition: 0.3s;
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

.password-requirements {
    font-size: 11px;
    color: #666;
    margin-top: 6px;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 8px;
}

.password-requirements ul {
    margin: 0;
    padding-left: 18px;
}

.password-requirements li {
    margin: 2px 0;
}

.password-requirements li.met {
    color: #28a745;
}

/* Validation icons */
.input-icon {
    position: absolute;
    right: 15px;
    top: 38px;
    font-size: 16px;
}

.input-icon.valid::after {
    content: '✓';
    color: #28a745;
}

.input-icon.invalid::after {
    content: '✗';
    color: #dc3545;
}

/* Success message */
.success-icon {
    color: #28a745;
    margin-left: 5px;
}

button {
    width: 100%;
    padding: 12px;
    margin-top: 25px;
    background: linear-gradient(to right, #f56b8d, #823c49);
    color: white;
    border: none;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 30px;
    transition: 0.3s ease;
}

button:hover {
    background: linear-gradient(to right, #823c49, #f56b8d);
}

button:disabled {
    background: #ccc;
    cursor: not-allowed;
}
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>

<body>
<div class="container">
<h2>Register</h2>

<form method="post" id="registrationForm">

<label>Full Name <span class="success-icon" id="name-icon"></span></label>
<div class="input-group">
    <input type="text" name="name" id="name" required pattern="[A-Za-z ]+" 
           title="Only alphabets allowed" placeholder="Enter your full name">
</div>

<label>Phone Number <span class="success-icon" id="phone-icon"></span></label>
<div class="input-group">
    <input type="tel" name="phone" id="phone" required pattern="[0-9]{10}" 
           maxlength="10" title="Phone number must contain exactly 10 digits"
           placeholder="Enter 10-digit mobile number">
</div>

<label>Email <span class="success-icon" id="email-icon"></span></label>
<div class="input-group">
    <input type="email" name="email" id="email" required 
           placeholder="Enter your email address">
</div>

<label>Address</label>
<div class="input-group">
    <input type="text" name="address" id="address" required 
           placeholder="Enter your address">
</div>

<label>Gender</label>
<select name="gender" id="gender" required>
<option value="">Select</option>
<option value="Male">Male</option>
<option value="Female">Female</option>
<option value="Other">Other</option>
</select>

<label>Date of Birth</label>
<input type="date" name="date" id="date" required>

<label>Username <span class="success-icon" id="username-icon"></span></label>
<div class="input-group">
    <input type="text" name="username" id="username" required 
           placeholder="Choose a username">
</div>

<label>Password <span class="success-icon" id="password-icon"></span></label>
<div class="input-group">
    <input type="password" name="password" id="password" required 
           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}$"
           title="Password must contain at least 6 characters, including uppercase, lowercase and number"
           placeholder="Create a password">
    <div class="password-strength">
        <div class="password-strength-bar" id="strength-bar"></div>
    </div>
    <div class="password-requirements">
        <strong>Password Requirements:</strong>
        <ul>
            <li id="req-length">At least 6 characters</li>
            <li id="req-upper">At least 1 uppercase letter (A-Z)</li>
            <li id="req-lower">At least 1 lowercase letter (a-z)</li>
            <li id="req-number">At least 1 number (0-9)</li>
        </ul>
    </div>
</div>

<label>Confirm Password <span class="success-icon" id="confirm-icon"></span></label>
<div class="input-group">
    <input type="password" name="confirm_password" id="confirm_password" required 
           placeholder="Confirm your password">
</div>

<label>Security Question</label>
<select name="security_question" id="security_question" required>
<option value="">Select a Security Question</option>
<option value="What is your mother's name?">What is your mother's name?</option>
<option value="What is your father's name?">What is your father's name?</option>
<option value="What is your pet's name?">What is your pet's name?</option>
<option value="What is the name of your first school?">What is the name of your first school?</option>
<option value="What is your favorite color?">What is your favorite color?</option>
<option value="What is your birthplace?">What is your birthplace?</option>
<option value="What is your best friend's name?">What is your best friend's name?</option>
</select>

<label>Security Answer <span class="success-icon" id="answer-icon"></span></label>
<div class="input-group">
    <input type="text" name="security_answer" id="security_answer" required 
           pattern="[A-Za-z ]+" title="Only alphabets allowed"
           placeholder="Enter your security answer">
</div>

<label>Security Question 2</label>
<select name="security_question2" id="security_question2">
<option value="">Select a Security Question (Optional)</option>
<option value="What is your mother's name?">What is your mother's name?</option>
<option value="What is your father's name?">What is your father's name?</option>
<option value="What is your pet's name?">What is your pet's name?</option>
<option value="What is the name of your first school?">What is the name of your first school?</option>
<option value="What is your favorite color?">What is your favorite color?</option>
<option value="What is your birthplace?">What is your birthplace?</option>
<option value="What is your best friend's name?">What is your best friend's name?</option>
</select>

<label>Security Answer 2</label>
<div class="input-group">
    <input type="text" name="security_answer2" id="security_answer2" 
           pattern="[A-Za-z ]+" title="Only alphabets allowed"
           placeholder="Enter your security answer (Optional)">
</div>

<label>Security Question 3</label>
<select name="security_question3" id="security_question3">
<option value="">Select a Security Question (Optional)</option>
<option value="What is your mother's name?">What is your mother's name?</option>
<option value="What is your father's name?">What is your father's name?</option>
<option value="What is your pet's name?">What is your pet's name?</option>
<option value="What is the name of your first school?">What is the name of your first school?</option>
<option value="What is your favorite color?">What is your favorite color?</option>
<option value="What is your birthplace?">What is your birthplace?</option>
<option value="What is your best friend's name?">What is your best friend's name?</option>
</select>

<label>Security Answer 3</label>
<div class="input-group">
    <input type="text" name="security_answer3" id="security_answer3" 
           pattern="[A-Za-z ]+" title="Only alphabets allowed"
           placeholder="Enter your security answer (Optional)">
</div>

<button type="submit" id="submitBtn">Register</button>

</form>
</div>

<script>
// ===== STEP-BY-STEP FIELD ENABLE SYSTEM =====
document.addEventListener('DOMContentLoaded', function() {
    
    // Get all form fields
    const form = document.getElementById('registrationForm');
    const fields = form.querySelectorAll('input, select');
    
    // Disable all fields except the first one
    for (let i = 1; i < fields.length; i++) {
        fields[i].disabled = true;
    }
    
    // Enable next field when current field is valid
    fields.forEach((field, index) => {
        
        field.addEventListener('input', function() {
            
            // Check if field is valid
            if (this.checkValidity()) {
                this.classList.remove('invalid');
                this.classList.add('valid');
                
                // Enable next field
                if (index + 1 < fields.length) {
                    fields[index + 1].disabled = false;
                }
            } else {
                this.classList.remove('valid');
                this.classList.add('invalid');
            }
            
        });
        
        // Also handle change event for select fields
        field.addEventListener('change', function() {
            if (this.checkValidity()) {
                this.classList.remove('invalid');
                this.classList.add('valid');
                
                if (index + 1 < fields.length) {
                    fields[index + 1].disabled = false;
                }
            }
        });
        
    });
    
    // ===== PHONE NUMBER: PREVENT NON-NUMERIC INPUT =====
    const phoneInput = document.getElementById('phone');
    
    phoneInput.addEventListener('keypress', function(e) {
        // Allow only digits
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
    
    // Also handle paste event
    phoneInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = e.clipboardData.getData('text');
        const numericText = pastedText.replace(/[^0-9]/g, '');
        document.execCommand('insertText', false, numericText);
    });
    
    // ===== PASSWORD STRENGTH METER =====
    const passwordInput = document.getElementById('password');
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
            } else {
                this.classList.remove('valid');
                this.classList.add('invalid');
            }
        }
    });
    
    // ===== SECURITY ANSWER: PREVENT NUMBERS AND SPECIAL CHARS =====
    const answerInput = document.getElementById('security_answer');
    
    answerInput.addEventListener('keypress', function(e) {
        // Allow only alphabets and spaces
        if (!/[a-zA-Z ]/.test(e.key)) {
            e.preventDefault();
        }
    });
    
    // ===== REAL-TIME VALIDATION ICONS =====
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const usernameInput = document.getElementById('username');
    
    function updateIcon(input, iconId) {
        const icon = document.getElementById(iconId);
        if (input.checkValidity()) {
            icon.innerHTML = '✓';
            icon.style.color = '#28a745';
        } else {
            icon.innerHTML = '';
        }
    }
    
    nameInput.addEventListener('input', function() {
        updateIcon(this, 'name-icon');
    });
    
    phoneInput.addEventListener('input', function() {
        updateIcon(this, 'phone-icon');
    });
    
    emailInput.addEventListener('input', function() {
        updateIcon(this, 'email-icon');
    });
    
    usernameInput.addEventListener('input', function() {
        updateIcon(this, 'username-icon');
    });
    
    passwordInput.addEventListener('input', function() {
        updateIcon(this, 'password-icon');
    });
    
    confirmInput.addEventListener('input', function() {
        updateIcon(this, 'confirm-icon');
    });
    
    answerInput.addEventListener('input', function() {
        updateIcon(this, 'answer-icon');
    });
    
});
</script>

</body>
</html>

