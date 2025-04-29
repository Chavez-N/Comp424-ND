<?php
require_once __DIR__ . '/config.php';
//require_once __DIR__ . '/mailer.php';
session_start();

// Establish PDO connection
$pdo = getPDOConnection();
if ($pdo === null) {
    die('Database connection error');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email            = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $code             = $_POST['code'];
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        die('Passwords do not match');
    }

    // Verify reset code and expiration
    $stmt = $pdo->prepare(
        "SELECT id, reset_expires FROM users WHERE email = ? AND reset_code = ?"
    );
    $stmt->execute([$email, $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || strtotime($user['reset_expires']) < time()) {
        die('Invalid or expired reset code');
    }

    // Update password and clear reset fields
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $update = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?, reset_code = NULL, reset_expires = NULL
         WHERE id = ?"
    );
    $update->execute([$hashed_password, $user['id']]);

    // Optionally send confirmation email
    // sendPasswordResetEmail($email, $code);

    // Redirect to login with success flag
    header('Location: login.php?password_reset=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - COMP 424 Project</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Set New Password</h1>
            <form action="reset-password.php" method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                <div class="input-group">
                    <label for="code">Verification Code</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="input-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="login-btn">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>
