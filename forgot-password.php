<?php
require 'config.php';
require 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Process email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Generate 6-digit code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store code in database
        $stmt = $pdo->prepare("UPDATE users SET reset_code = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$code, $expires, $email]);
        
        // Send email
        sendPasswordResetEmail($email, $code);
    }
    
    // Always show success message (don't reveal if email exists)
    header("Location: password-reset.html?email=" . urlencode($email));
    exit();
}
?>
