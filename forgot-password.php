<?php
// 1) Bootstrap
ini_set('display_errors',1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/config.php';
//require __DIR__ . '/mailer.php';

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
        header('Content-Type: application/json');
        echo json_encode(['email' => $email, 'reset_code' => $code]);
        exit;
    }

    // If user not found, still return success response to avoid user enumeration
    header('Content-Type: application/json');
    echo json_encode(['email' => $email, 'reset_code' => null]);
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
      <form action="forgot-password.php" method="POST" id="forgotPasswordForm">
        <div class="input-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required>
        </div>
        <button type="submit" class="login-btn">Send Reset Code</button>
      </form>
      <div id="status-message" style="margin-top: 1em;"></div>
      <div class="links">
        <a href="login.php">Back to Login</a>
      </div>
    </div>
  </div>
  <script src="emailjs-integration.js"></script>
  <script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', function(event) {
      event.preventDefault();
      const form = event.target;
      const formData = new FormData(form);
      const statusMessage = document.getElementById('status-message');

      fetch(form.action, {
        method: 'POST',
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
        if (data.email && data.reset_code) {
          window.sendPasswordResetEmail(data.email, data.reset_code)
            .then(() => {
              statusMessage.textContent = 'Reset code sent successfully via email.';
              statusMessage.style.color = 'green';
              form.reset();
            })
            .catch(error => {
              statusMessage.textContent = 'Failed to send reset code email.';
              statusMessage.style.color = 'orange';
              console.error('EmailJS error:', error);
            });
        } else {
          statusMessage.textContent = 'If the email exists, a reset code has been sent.';
          statusMessage.style.color = 'green';
          form.reset();
        }
      })
      .catch(error => {
        statusMessage.textContent = 'Failed to send reset code: ' + error.message;
        statusMessage.style.color = 'red';
      });
    });
  </script>
</body>
</html>
