<?php
/**
 * PHPMailer Email Function
 * Include this file to use PHPMailer for sending emails
 */

function sendEmailWithPHPMailer($toEmail, $username, $otp) {
    $phpmailerDir = __DIR__ . '/PHPMailer';
    
    // Try to load PHPMailer files
    if (file_exists($phpmailerDir . '/Exception.php')) {
        require_once $phpmailerDir . '/Exception.php';
    }
    if (file_exists($phpmailerDir . '/SMTP.php')) {
        require_once $phpmailerDir . '/SMTP.php';
    }
    if (file_exists($phpmailerDir . '/PHPMailer.php')) {
        require_once $phpmailerDir . '/PHPMailer.php';
    }
    
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->Timeout = 15;
            
            // From settings
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail, $username);
            
            // Content
            $mail->isHTML(false);
            $mail->Subject = "Haya Collection - Password Reset OTP";
            $mail->Body = "Hello " . $username . ",\r\n\r\n";
            $mail->Body .= "Your OTP for password reset is: " . $otp . "\r\n";
            $mail->Body .= "This OTP is valid for 10 minutes.\r\n\r\n";
            $mail->Body .= "If you did not request this, please ignore this email.\r\n";
            $mail->Body .= "Thank you,\r\nHaya Collection";
            
            if ($mail->Send()) {
                return true;
            }
        } catch (Exception $e) {
            // PHPMailer failed
            return false;
        }
    }
    
    return false;
}

