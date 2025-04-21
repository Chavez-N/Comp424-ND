<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

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
