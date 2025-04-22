<?php
// Start session to store temporary user data
session_start();

// Include configuration and mailer files
require 'config.php';
require 'mailer.php';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot validation to detect bots (field should be empty)
    if (!empty($_POST['website'])) {
        error_log("Bot detected during registration attempt.");
        die("Bot detected.");
    }
    // Simple CAPTCHA: math problem validation
    $expected_answer = 7;
    if (!isset($_POST['math_answer']) || intval($_POST['math_answer']) !== $expected_answer) {
        error_log("Failed CAPTCHA math problem during registration attempt.");
        die("Incorrect answer to the math problem.");
    }

    // Sanitize and validate user inputs to prevent injection attacks
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $birth_date = $_POST['birth_date']; // Assuming date input is valid format
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $security_question_1 = filter_var($_POST['security_question_1'], FILTER_SANITIZE_STRING);
    $security_question_2 = filter_var($_POST['security_question_2'], FILTER_SANITIZE_STRING);
    $security_question_3 = filter_var($_POST['security_question_3'], FILTER_SANITIZE_STRING);

    // Validate password confirmation
    if ($password !== $confirm_password) {
        error_log("Password confirmation mismatch for email: $email");
        die("Passwords do not match.");
    }

    // Password strength validation: minimum 8 characters, at least one uppercase letter and one number
    $password_pattern = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';
    if (!preg_match($password_pattern, $password)) {
        error_log("Weak password attempt for email: $email");
        die("Password must be at least 8 characters long, include at least one uppercase letter and one number.");
    }

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Generate a unique token for email verification
    $token = bin2hex(random_bytes(50));

    // Store user data temporarily in session variables before final registration
    $_SESSION['temp_user'] = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'birth_date' => $birth_date,
        'password_hash' => $password_hash,
        'token' => $token,
        'security_question_1' => $security_question_1,
        'security_question_2' => $security_question_2,
        'security_question_3' => $security_question_3
    ];

    // Output the stored user data for debugging or confirmation
    echo "<h2>Temporary User Data Stored:</h2>";
    echo "<ul>";
    echo "<li>First Name: " . htmlspecialchars($_SESSION['temp_user']['first_name']) . "</li>";
    echo "<li>Last Name: " . htmlspecialchars($_SESSION['temp_user']['last_name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($_SESSION['temp_user']['email']) . "</li>";
    echo "<li>Birth Date: " . htmlspecialchars($_SESSION['temp_user']['birth_date']) . "</li>";
    echo "<li>Security Question 1: " . htmlspecialchars($_SESSION['temp_user']['security_question_1']) . "</li>";
    echo "<li>Security Question 2: " . htmlspecialchars($_SESSION['temp_user']['security_question_2']) . "</li>";
    echo "<li>Security Question 3: " . htmlspecialchars($_SESSION['temp_user']['security_question_3']) . "</li>";
    echo "<li>Token: " . htmlspecialchars($_SESSION['temp_user']['token']) . "</li>";
    echo "</ul>";
}
?>
