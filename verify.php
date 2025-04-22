<?php
// Include configuration for database connection
require 'config.php';

// Check if verification token is provided in URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Retrieve user with matching token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Update user record to mark as verified and clear token
        $update = $pdo->prepare("UPDATE users SET verified = 1, token = NULL WHERE token = ?");
        $update->execute([$token]);
        echo "Account verified! You can now log in.";
    } else {
        // Invalid or expired token
        echo "Invalid or expired token.";
    }
}
?>
