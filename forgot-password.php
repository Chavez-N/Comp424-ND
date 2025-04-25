<?php
// 1) Bootstrap
ini_set('display_errors',1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/config.php';
require __DIR__ . '/mailer.php';

// 2) If form submitted, process it
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Look up user
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Generate & store reset code if they exist
    if ($user) {
        $code    = str_pad(rand(0,999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $upd     = $pdo->prepare(
            "UPDATE users SET reset_code = ?, reset_expires = ? WHERE email = ?"
        );
        $upd->execute([$code, $expires, $email]);
        sendPasswordResetEmail($email, $code);
    }

    // Always redirect to the same “check your email” page
    header("Location: password-reset.html?email=" . urlencode($email));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Reset Password</title>
  <link href="styles.css" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <h1>Reset Password</h1>
      <form action="forgot-password.php" method="POST">
        <div class="input-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required>
        </div>
        <button type="submit" class="login-btn">Send Reset Code</button>
      </form>
      <div class="links">
        <a href="login.php">Back to Login</a>
      </div>
    </div>
  </div>
</body>
</html>
