<?php
// Include configuration file for database connection
require __DIR__ . '/config.php';

// Function to log login attempts with timestamp, IP, email, and status (success/failure)
function log_login_attempt($email, $success) {
    $logfile = 'login_attempts.log';
    $status = $success ? 'SUCCESS' : 'FAILURE';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $time = date('Y-m-d H:i:s');
    $log_entry = "[$time] - $ip - $email - $status\n";
    file_put_contents($logfile, $log_entry, FILE_APPEND);
}

// Demo default user credentials for fallback if database is unavailable
$default_email = 'torosyandiran@gmail.com';
$default_password = 'Diran#123';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize email input to prevent injection attacks
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Get PDO database connection
    $pdo = getPDOConnection();

    // Start session to manage user login state
    session_start();

    if ($pdo === null) {
        // No database connection, fallback to default user authentication
        if ($email === $default_email && $password === $default_password) {
            $_SESSION['user_id'] = 0; // Default user id
            log_login_attempt($email, true); // Log successful login
            $_SESSION['message'] = "Login successful! Redirecting...";
            header("Location: dashboard.html");
            exit();
        } else {
            // Log failed login attempt
            log_login_attempt($email, false);
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.html");
            exit();
        }
    } else {
        // Check email and password against database records
        $stmt = $pdo->prepare("SELECT id, password, verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify password and check if email is verified
        if ($user && password_verify($password, $user['password'])) {
            if ($user['verified']) {
                $_SESSION['user_id'] = $user['id']; // Set user session
                log_login_attempt($email, true); // Log successful login
                $_SESSION['message'] = "Login successful! Redirecting...";
                header("Location: dashboard.html");
                exit();
            } else {
                // User email not verified
                log_login_attempt($email, false);
                $_SESSION['error'] = "Please verify your email before logging in.";
                header("Location: login.html");
                exit();
            }
        } else {
            // Invalid credentials
            log_login_attempt($email, false);
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.html");
            exit();
        }
    }
}

// Note: Ensure HTTPS and localhost redirection are handled by server configuration.
?>
