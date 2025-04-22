<?php
// Use PHPMailer for sending emails securely
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'vendor/autoload.php';

// Function to send verification email with a unique verification link
function sendVerificationEmail($email, $verification_link) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration for sending email
        $mail->isSMTP();
        $mail->Host = 'smtp.yourmailserver.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Set sender and recipient
        $mail->setFrom('no-reply@yourwebsite.com', 'Your Website');
        $mail->addAddress($email);

        // Email content settings
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $mail->Body = "Click the link to verify your account: <a href='$verification_link'>$verification_link</a>";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Handle errors in sending email
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to send password reset email with a reset code
function sendPasswordResetEmail($email, $code) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration for sending email
        $mail->isSMTP();
        $mail->Host = 'smtp.yourmailserver.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Set sender and recipient
        $mail->setFrom('no-reply@yourwebsite.com', 'Your Website');
        $mail->addAddress($email);

        // Email content settings
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body = "Your password reset code is: <strong>$code</strong><br><br>
                      This code will expire in 1 hour. If you didn't request this, please ignore this email.";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Handle errors in sending email
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
