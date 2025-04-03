<?php
require 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare("UPDATE users SET verified = 1, token = NULL WHERE token = ?");
        $update->execute([$token]);
        echo "Account verified! You can now log in.";
    } else {
        echo "Invalid or expired token.";
    }
}
?>
