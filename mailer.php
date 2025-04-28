<?php
// Use PHPMailer for sending emails securely
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer autoloader
require_once __DIR__ . '/vendor/autoload.php';


/**
 * Send a verification email with a unique verification link.
 * Debug output is captured to the PHP error log for troubleshooting.
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
        $mail->AuthType   = 'LOGIN';                   // Force LOGIN auth
        $mail->Username   = 'neotide10@gmail.com';     // Your Gmail address
        $mail->Password   = 'YOUR_APP_PASSWORD';       // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Debug settings: log all SMTP traffic to error log
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP (level $level): $str");
        };

        // Optional TLS options (bypass certificate checks if necessary)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
                'allow_self_signed'=> true,
            ],
        ];

        // Sender and recipient
        $mail->setFrom('neotide10@gmail.com', 'Your Website');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $mail->Body    = sprintf(
            '<p>Click the link below to verify your account:</p>' .
            '<p><a href="%s">%s</a></p>',
            htmlspecialchars($verification_link),
            htmlspecialchars($verification_link)
        );

        // Send the message
        $mail->send();
        echo '<p>Verification email sent. Check your inbox.</p>';
    } catch (Exception $e) {
        // Display and log errors
        echo '<h3>Mailer Error (verification):</h3><pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
        error_log('Mailer Error (verification): ' . $mail->ErrorInfo);
    }
}

/**
 * Send a password reset email with a reset code.
 * Debug output is captured to the PHP error log for troubleshooting.
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

        // Debug settings: log all SMTP traffic to error log
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP (level $level): $str");
        };

        // Optional TLS options
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
                'allow_self_signed'=> true,
            ],
        ];

        // Sender and recipient
        $mail->setFrom('neotide10@gmail.com', 'Your Website');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body    = sprintf(
            '<p>Your password reset code is: <strong>%s</strong></p>' .
            '<p>This code will expire in 1 hour.</p>',
            htmlspecialchars($code)
        );

        // Send the message
        $mail->send();
        echo '<p>Password reset email sent. Check your inbox.</p>';
    } catch (Exception $e) {
        // Display and log errors
        echo '<h3>Mailer Error (reset):</h3><pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
        error_log('Mailer Error (reset): ' . $mail->ErrorInfo);
    }
}
?>

