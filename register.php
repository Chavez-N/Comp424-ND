<?php
require 'config.php';
require 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //// ALTERNATIVE TO CAPTCHA and reCAPTCHA
    // Honeypot validation: should be empty
    if (!empty($_POST['website'])) {
        die("Bot detected.");
    }
    // Math problem validation
    $expected_answer = 7;
    if (!isset($_POST['math_answer']) || intval($_POST['math_answer']) !== $expected_answer) {
        die("Incorrect answer to the math problem.");
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(50));

    $stmt = $pdo->prepare("INSERT INTO users (email, password, token) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$email, $password, $token])) {
        $verification_link = "http://yourwebsite.com/verify.php?token=$token";
        sendVerificationEmail($email, $verification_link);
        echo "Registration successful! Check your email to verify your account.";
    } else {
        echo "Error: Email might already be registered.";
    }
}
?>
