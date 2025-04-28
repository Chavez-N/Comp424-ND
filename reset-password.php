<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';
session_start();

$pdo = getPDOConnection();
if ($pdo === null) {
    die('Database connection error');
}

// Handle initial password reset request (email only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !isset($_POST['code'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Check user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Generate reset code and expiration (1 hour)
        $code = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 3600);

        // Store in DB
        $update = $pdo->prepare(
            "UPDATE users
             SET reset_code = ?, reset_expires = ?
             WHERE id = ?"
        );
        $update->execute([$code, $expires, $user['id']]);

        // Send reset code email
        sendPasswordResetEmail($email, $code);
        echo '<p>An email with your reset code has been sent. Please check your inbox.</p>';
    } else {
        // Don't reveal if email is registered
        echo '<p>If that email is on file, you will receive reset instructions shortly.</p>';
    }

    exit;
}

// Handle reset form submission (code + new password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'], $_POST['password'], $_POST['confirm_password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $code = $_POST['code'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        die('<p>Passwords do not match.</p>');
    }

    // Validate reset code
    $stmt = $pdo->prepare(
        "SELECT id, reset_expires
         FROM users
         WHERE email = ? AND reset_code = ?"
    );
    $stmt->execute([$email, $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || strtotime($user['reset_expires']) < time()) {
        die('<p>Invalid or expired reset code.</p>');
    }

    // Update to new password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $upd = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?, reset_code = NULL, reset_expires = NULL
         WHERE id = ?"
    );
    $upd->execute([$hash, $user['id']]);

    // Optionally send confirmation email
    sendPasswordResetEmail($email, $code);

    header('Location: login.php?password_reset=1');
    exit;
}

// If no POST or missing fields, display the reset request form
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Forgot Password</title>
</head>
<body>
  <h1>Forgot Password</h1>
  <form method="POST" action="">
    <label>Email:<br>
      <input type="email" name="email" required>
    </label><br>
    <button type="submit">Send Reset Code</button>
  </form>
  <hr>
  <h2>Already have a code?</h2>
  <form method="POST" action="">
    <label>Email:<br>
      <input type="email" name="email" required>
    </label><br>
    <label>Reset Code:<br>
      <input type="text" name="code" required>
    </label><br>
    <label>New Password:<br>
      <input type="password" name="password" required>
    </label><br>
    <label>Confirm Password:<br>
      <input type="password" name="confirm_password" required>
    </label><br>
    <button type="submit">Reset Password</button>
  </form>
</body>
</html>
