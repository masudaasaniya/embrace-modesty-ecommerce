<?php
session_start();

// Include database connection
include("connect.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if user came from forgot password flow
    if (!isset($_SESSION['reset_email']) || !isset($_SESSION['question_step'])) {
        header("Location: forgot_password.php");
        exit();
    }
    
    $step = $_SESSION['question_step'];
    $attempts = isset($_SESSION['security_attempts']) ? $_SESSION['security_attempts'] : 0;
    $max_attempts = 5;
    
    // Check if max attempts reached
    if ($attempts >= $max_attempts) {
        $_SESSION['security_answer_error'] = "Too many failed attempts. Please try again later.";
        header("Location: security-question.php");
        exit();
    } 
    
    // Get and sanitize the security answer
$security_answer = strtolower(trim(isset($_POST["security_answer"]) ? $_POST["security_answer"] : ''));
    
    if (empty($security_answer)) {
        $_SESSION['security_attempts'] = $attempts + 1;
        $_SESSION['security_answer_error'] = "Please enter your security answer!";
        header("Location: security-question.php");
        exit();
    }
    
    // Dynamic hash field based on current step (0,1,2 -> 1,2,3)
    $hash_field = 'security_answer' . ($step + 1) . '_hash';
    $stmt = $con->prepare("SELECT `$hash_field` FROM `user` WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $_SESSION['reset_email']);
    $stmt->execute();
    $result = $stmt->get_result(); 
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stored_hash = isset($user[$hash_field]) ? $user[$hash_field] : '';
        
        // Verify using password_verify (case-insensitive since normalized)
        // PHP 5.x compatible password verify
        if (function_exists('password_verify')) {
            $is_valid = !empty($stored_hash) && password_verify($security_answer, $stored_hash);
        } else {
            // Fallback: assume MD5 hash for older registrations
            $is_valid = !empty($stored_hash) && md5($security_answer) === $stored_hash;
        }
        
        if ($is_valid) {
            // Correct answer - advance step
            $_SESSION['question_step'] = $step + 1;
            $_SESSION['security_attempts'] = 0; // Reset attempts on correct
            
            if ($_SESSION['question_step'] >= 3) {
                $_SESSION['security_verified'] = true;
                // Clear temporary sessions
                unset($_SESSION['questions'], $_SESSION['question_step'], $_SESSION['security_attempts']);
                header("Location: reset-password.php");
                exit();
            } else {
                header("Location: security-question.php");
                exit();
            } 
            
        } else {
            // Answer is incorrect
            $attempts++;
            $_SESSION['security_attempts'] = $attempts;
            
            if ($attempts >= $max_attempts) {
                $_SESSION['security_answer_error'] = "Too many incorrect attempts. Please try again later.";
                // Reset step on max fail
                $_SESSION['question_step'] = 0;
            } else {
                $_SESSION['security_answer_error'] = "Incorrect answer. Please try again.";
            }
            
            header("Location: security-question.php");
            exit(); 
        }
        
    } else {
        // User not found
        $_SESSION['security_answer_error'] = "User not found. Please try again.";
        header("Location: forgot_password.php");
        exit();
    }
    
    $stmt->close();
    
} else {
    // Not a POST request
    header("Location: forgot_password.php");
    exit();
}
?>

