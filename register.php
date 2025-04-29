<?php
// Start session to store temporary user data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration (getPDOConnection) and mailer (sendVerificationEmail)
require 'config.php';
require 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Honeypot: should remain empty
    if (!empty($_POST['website'])) {
        error_log("Bot detected during registration attempt.");
        die("Bot detected.");
    }

    // 2) Simple CAPTCHA: math problem
    $expected_answer = 7;
    if (!isset($_POST['math_answer']) || intval($_POST['math_answer']) !== $expected_answer) {
        error_log("Failed CAPTCHA math problem during registration attempt.");
        die("Incorrect answer to the math problem.");
    }

    // 3) Sanitize + validate inputs
    $first_name       = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name        = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $email            = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $username         = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $sec1             = filter_var($_POST['security_answer_1'], FILTER_SANITIZE_STRING);
    $sec2             = filter_var($_POST['security_answer_2'], FILTER_SANITIZE_STRING);
    $sec3             = filter_var($_POST['security_answer_3'], FILTER_SANITIZE_STRING);

    // 4) Password confirmation
    if ($password !== $confirm_password) {
        error_log("Password confirmation mismatch for email: $email");
        die("Passwords do not match.");
    }

    // 5) Password strength: at least 8 chars, one uppercase, one number
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        error_log("Weak password attempt for email: $email");
        die("Password must be at least 8 characters long, include at least one uppercase letter and one number.");
    }

    // 6) Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 7) Generate a unique token for email verification
    $token = bin2hex(random_bytes(50));

    // 8) Store temp data in session (optional)
    $_SESSION['temp_user'] = [
        'first_name'         => $first_name,
        'last_name'          => $last_name,
        'email'              => $email,
        'username'           => $username,
        'password_hash'      => $password_hash,
        'token'              => $token,
        'security_answer_1'  => $sec1,
        'security_answer_2'  => $sec2,
        'security_answer_3'  => $sec3
    ];

    // 9) Get PDO connection
    $pdo = getPDOConnection();
    if ($pdo === null) {
        die('❌ Could not connect to the database. Check config.php credentials.');
    }

    // 10) Insert with proper columns
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (
                first_name,
                last_name,
                email,
                username,
                password_hash,
                security_answer_1,
                security_answer_2,
                security_answer_3,
                token
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?
            )"
        );
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $username,
            $password_hash,
            $sec1,
            $sec2,
            $sec3,
            $token
        ]);

        // 11) Confirm insert succeeded
if ($stmt->rowCount() === 1) {
    // Send verification email
    header('Content-Type: application/json');
    echo json_encode(['email' => $email, 'token' => $token]);
    exit;
} else {
    die('❌ Registration ran but no rows were added. Check your table defaults and triggers.');
}
    } catch (PDOException $e) {
        // Display SQL error for debugging
        die('❌ Registration error: ' . $e->getMessage());
    }
}
?>
