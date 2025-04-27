<?php
// Use PHPMailer for sending emails securely
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require __DIR__ . '/vendor/autoload.php';

/*
Instructions to connect mailer.php to Gmail SMTP:

1. Use your actual Gmail email address in the Username field.
2. For the Password field, you need to generate an App Password if you have 2-Step Verification enabled on your Google account.
   - Go to https://myaccount.google.com/security
   - Under "Signing in to Google", select "App Passwords"
   - Generate a new app password for "Mail" and your device
   - Use this 16-character password in the Password field below
3. Ensure "Less secure app access" is disabled (Google is phasing this out).
4. Alternatively, you can implement OAuth2 authentication with PHPMailer for Gmail, but App Passwords are simpler for most cases.
5. Make sure your server allows outbound connections on port 587.

*/

// Function to send verification email with a unique verification link
function sendVerificationEmail($email, $verification_link) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration for sending email via Gmail SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'neotide10@gmail.com'; // Replace with your Gmail address
        $mail->Password = 'zbrvncygthfuvffz'; // Replace with your Gmail App Password
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
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to send password reset email with a reset code
function sendPasswordResetEmail($email, $code) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration for sending email via Gmail SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'neotide10@gmail.com'; // Replace with your Gmail address
        $mail->Password = 'zbrvncygthfuvffz'; // Replace with your Gmail App Password (spaces removed)
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
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
