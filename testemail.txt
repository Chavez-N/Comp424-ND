<?php
// testemail.php - Script to send a test email

$to = "recipient@example.com"; // Change this to the recipient's email address
$subject = "Test Email from testemail.php";
$message = "This is a test email sent from the testemail.php script.";
$headers = "From: sender@example.com\r\n" .
           "Reply-To: sender@example.com\r\n" .
           "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully to $to";
} else {
    echo "Failed to send email";
}
?>
