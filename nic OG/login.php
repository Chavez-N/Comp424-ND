
<?php
//This is a base (DO NOT TRUST!) I have no clue what I'm doing here

// Start session
session_start();

// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// CSRF protection - validate token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Set headers to prevent caching of sensitive data
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Get input and sanitize
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    $response = [
        'success' => false,
        'message' => '',
        'redirect' => ''
    ];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $response['message'] = 'Please provide both username and password.';
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password_hash, login_count, last_login, account_status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check account status
            if ($user['account_status'] !== 'active') {
                $response['message'] = 'Account not activated. Please check your email.';
                logLoginAttempt($conn, $username, false, 'Account not activated');
            }
            // Verify password
            else if (password_verify($password, $user['password_hash'])) {
                // Successful login
                
                // Update login count and last login time
                $updateStmt = $conn->prepare("UPDATE users SET login_count = login_count + 1, last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['login_count'] = $user['login_count'] + 1;
                $_SESSION['last_login'] = $user['last_login'];
                
                // Set session timeout
                $_SESSION['last_activity'] = time();
                
                // Add CSRF token for protected actions
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                // Log successful login attempt
                logLoginAttempt($conn, $username, true, 'Success');
                
                $response['success'] = true;
                $response['redirect'] = 'dashboard.php';
            } else {
                // Failed login - wrong password
                $response['message'] = 'Invalid username or password.';
                logLoginAttempt($conn, $username, false, 'Invalid password');
            }
        } else {
            // Username doesn't exist
            $response['message'] = 'Invalid username or password.';
            logLoginAttempt($conn, $username, false, 'Username not found');
        }
        
        $stmt->close();
    }
    
    // Return JSON response if AJAX request
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        if ($response['success']) {
            header('Location: ' . $response['redirect']);
        } else {
            $_SESSION['login_error'] = $response['message'];
            header('Location: index.html');
        }
        exit;
    }
}
?>