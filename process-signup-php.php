<?php
// Start session
session_start();

// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Set headers
header('Content-Type: application/json');

// Default response
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// CSRF protection - validate token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $response['message'] = 'Invalid request.';
    echo json_encode($response);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate reCAPTCHA
    $recaptchaSecret = 'YOUR_RECAPTCHA_SECRET_KEY';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptchaSecret.'&response='.$recaptchaResponse);
    $responseData = json_decode($verifyResponse);
    
    if (!$responseData->success) {
        $response['message'] = 'CAPTCHA verification failed. Please try again.';
        echo json_encode($response);
        exit;
    }
    
    // Get and sanitize input
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $birthdate = sanitizeInput($_POST['birthdate'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Get security questions and answers
    $security_question_1 = sanitizeInput($_POST['security_question_1'] ?? '');
    $security_answer_1 = sanitizeInput($_POST['security_answer_1'] ?? '');
    $security_question_2 = sanitizeInput($_POST['security_question_2'] ?? '');
    $security_answer_2 = sanitizeInput($_POST['security_answer_2'] ?? '');
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($birthdate) || 
        empty($username) || empty($password) || empty($confirm_password) ||
        empty($security_question_1) || empty($security_answer_1) || 
        empty($security_question_2) || empty($security_answer_2)) {
        
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }
    
    // Validate password match
    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
        echo json_encode($response);
        exit;
    }
    
    // Validate password strength
    if (!isStrongPassword($password)) {
        $response['message'] = 'Password does not meet security requirements.';
        echo json_encode($response);
        exit;
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['message'] = 'Username is already taken.';
        echo json_encode($response);
        exit;
    }
    $stmt->close();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['message'] = 'Email is already registered.';
        echo json_encode($response);
        exit;
    }
    $stmt->close();
    
    // Generate activation token
    $activation_token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Hash password and security answers
    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $security_answer_1_hash = password_hash(strtolower($security_answer_1), PASSWORD_BCRYPT);
    $security_answer_2_hash = password_hash(strtolower($security_answer_2), PASSWORD_BCRYPT);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, birthdate, username, password_hash, 
                              activation_token, token_expiry, account_status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        
        $status = 'pending';
        $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $birthdate, $username, 
                         $password_hash, $activation_token, $token_expiry);
        $stmt->execute();
        $user_id = $conn->insert_id;
        $stmt->close();
        
        // Insert security questions
        $stmt = $conn->prepare("INSERT INTO security_questions (user_id, question, answer_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $security_question_1, $security_answer_1_hash);
        $stmt->execute();
        
        $stmt->bind_param("iss", $user_id, $security_question_2, $security_answer_2_hash);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Send activation email
        $activation_link = "https://" . $_SERVER['HTTP_HOST'] . "/activate.php?token=" . $activation_token;
        $subject = "Activate Your Account";
        $message = "
        <html>
        <head>
            <title>Account Activation</title>
        </head>
        <body>
            <h2>Thank you for registering!</h2>
            <p>Hello $first_name $last_name,</p>
            <p>Please click the link below to activate your account:</p>
            <p><a href='$activation_link'>Activate Account</a></p>
            <p>This link will expire in 24 hours.</p>
            <p>If you did not register for an account, please ignore this email.</p>
        </body>
        </html>
        ";
        
        // Send email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: noreply@company.com' . "\r\n";
        
        if (mail($email, $subject, $message, $headers)) {
            $response['success'] = true;
            $response['message'] = 'Registration successful! Please check your email to activate your account.';
            $response['redirect'] = 'index.html';
        } else {
            // Email failed, but account was created
            $response['success'] = true;
            $response['message'] = 'Registration successful, but activation email could not be sent. Please contact support.';
            $response['redirect'] = 'index.html';
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response['message'] = 'Registration failed. Please try again later.';
        
        // Log the error
        error_log('Registration error: ' . $e->getMessage());
    }
    
    // Return response
    echo json_encode($response);
    exit;
}
?>
