<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /*
    // Verify reCAPTCHA first
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $recaptcha_secret = 'YOUR_RECAPTCHA_SECRET';
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response
    ];
    
    $recaptcha_options = [
        'http' => [
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data)
        ]
    ];
    
    $recaptcha_context = stream_context_create($recaptcha_options);
    $recaptcha_result = json_decode(file_get_contents($recaptcha_url, false, $recaptcha_context));
    
    if (!$recaptcha_result->success) {
        die('reCAPTCHA verification failed');
    }
    */

    // Validate inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $code = $_POST['code'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        die('Passwords do not match');
    }
    
    // Verify code
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE email = ? AND reset_code = ?");
    $stmt->execute([$email, $code]);
    $user = $stmt->fetch();
    
    if (!$user || strtotime($user['reset_expires']) < time()) {
        die('Invalid or expired reset code');
    }
    
    // Update password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expires = NULL WHERE email = ?");
    $stmt->execute([$hashed_password, $email]);
    
    header("Location: login.html?password_reset=1");
    exit();
}
?>
