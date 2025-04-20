<?php
require 'config.php';
require 'mailer.php';

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
