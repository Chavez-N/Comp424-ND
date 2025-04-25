<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    header("Location: login.php?password_reset=1");
    exit();
}
?>
