<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Verify reCAPTCHA
    $secret_key = 'YOUR_SECRET_KEY_HERE'; // Replace with your Secret Key
    $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$recaptcha_response";
    $response = file_get_contents($verify_url);
    $response_data = json_decode($response);

    if (!$response_data->success) {
        die("reCAPTCHA verification failed. Please try again.");
    }

    // Check email & password in database
    $stmt = $pdo->prepare("SELECT id, password, verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['verified']) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            echo "Login successful! Redirecting...";
            header("Refresh: 2; URL=dashboard.php");
        } else {
            echo "Please verify your email before logging in.";
        }
    } else {
        echo "Invalid email or password.";
    }
}
?>
