<?php
require __DIR__ . '/config.php';

function log_login_attempt($email, $success) {
    $logfile = 'login_attempts.log';
    $status = $success ? 'SUCCESS' : 'FAILURE';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $time = date('Y-m-d H:i:s');
    $log_entry = "[$time] - $ip - $email - $status\n";
    file_put_contents($logfile, $log_entry, FILE_APPEND);
}
//Demo Use::
$default_email = 'torosyandiran@gmail.com';
$default_password = 'Diran#123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $pdo = getPDOConnection();

    session_start();

    if ($pdo === null) {
        // No database connection, fallback to default user
        if ($email === $default_email && $password === $default_password) {
            $_SESSION['user_id'] = 0; // Default user id
            log_login_attempt($email, true);
            $_SESSION['message'] = "Login successful! Redirecting...";
            header("Location: dashboard.html");
            exit();
        } else {
            log_login_attempt($email, false);
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.html");
            exit();
        }
    } else {
        // Check email & password in database
        $stmt = $pdo->prepare("SELECT id, password, verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['verified']) {
                $_SESSION['user_id'] = $user['id'];
                log_login_attempt($email, true);
                $_SESSION['message'] = "Login successful! Redirecting...";
                header("Location: dashboard.html");
                exit();
            } else {
                log_login_attempt($email, false);
                $_SESSION['error'] = "Please verify your email before logging in.";
                header("Location: login.html");
                exit();
            }
        } else {
            log_login_attempt($email, false);
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.html");
            exit();
        }
    }
}

// Note: Ensure HTTPS and localhost redirection are handled by server configuration.
?>
