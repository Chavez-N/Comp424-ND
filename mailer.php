<?php
// Use PHPMailer for sending emails securely
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer autoloader
require __DIR__ . '/vendor/autoload.php';

/**
 * Send a verification email with a unique verification link.
 * Debug output is visible in the browser for troubleshooting.
 *
 * @param string $email Recipient email address
 * @param string $verification_link URL to verify the account
 */
function sendVerificationEmail($email, $verification_link) {
    $mail = new PHPMailer(true);
    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->AuthType   = 'LOGIN';                  // Force LOGIN auth
        $mail->Username   = 'neotide10@gmail.com';    // Your Gmail address
        $mail->Password   = 'YOUR_APP_PASSWORD';      // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Debug settings (visible on page)
        $mail->SMTPDebug   = SMTP::DEBUG_SERVER;     // Full SMTP trace
        $mail->Debugoutput = 'html';                 // HTML formatted debug output

        // Sender and recipient
        $mail->setFrom('neotide10@gmail.com', 'Your Website');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $mail->Body    = sprintf(
            "<p>Click the link below to verify your account:</p><p><a href=\"%s\">%s</a></p>",
            htmlspecialchars($verification_link),
            htmlspecialchars($verification_link)
        );

        // Send the message
        $mail->send();
    } catch (Exception $e) {
        // Display and log errors
        echo '<h3>Mailer Error:</h3><pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
        error_log('Mailer Error (verification): ' . $mail->ErrorInfo);
    }
}

/**
 * Send a password reset email with a reset code.
 * Debug output is visible in the browser for troubleshooting.
 *
 * @param string $email Recipient email address
 * @param string $code One-time reset code
 */
function sendPasswordResetEmail($email, $code) {
    $mail = new PHPMailer(true);
    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->AuthType   = 'LOGIN';
        $mail->Username   = 'neotide10@gmail.com';
        $mail->Password   = 'YOUR_APP_PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Debug settings (visible on page)
        $mail->SMTPDebug   = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = 'html';

        // Sender and recipient
        $mail->setFrom('neotide10@gmail.com', 'Your Website');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body    = sprintf(
            "<p>Your password reset code is: <strong>%s</strong></p><p>This code will expire in 1 hour.</p>",
            htmlspecialchars($code)
        );

        // Send the message
        $mail->send();
    } catch (Exception $e) {
        // Display and log errors
        echo '<h3>Mailer Error:</h3><pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
        error_log('Mailer Error (reset): ' . $mail->ErrorInfo);
    }
}
?>

