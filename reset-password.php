<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Sanitize inputs
    $email             = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $code              = $_POST['code'];
    $password          = $_POST['password'];
    $confirm_password  = $_POST['confirm_password'];

    // 2) Validate password confirmation
    if ($password !== $confirm_password) {
        die('Passwords do not match');
    }

    // 3) Get database connection
    $pdo = getPDOConnection();
    if ($pdo === null) {
        die('Database connection error');
    }

    // 4) Verify reset code and expiration
    $stmt = $pdo->prepare(
        "SELECT id, reset_expires
         FROM users
         WHERE email = ?
           AND reset_code = ?"
    );
    $stmt->execute([$email, $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || strtotime($user['reset_expires']) < time()) {
        die('Invalid or expired reset code');
    }

    // 5) Update the password in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $update = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?, reset_code = NULL, reset_expires = NULL
         WHERE id = ?"
    );
    $update->execute([$hashed_password, $user['id']]);

    // 6) Send confirmation email to user
    sendPasswordResetEmail($email, $code);

    // 7) Redirect to login with success flag
    header("Location: login.php?password_reset=1");
    exit();
}
?>
