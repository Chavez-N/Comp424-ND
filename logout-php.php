<?php
// Start session
session_start();

// Include database connection for logging
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Log the logout if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Log logout event
    logLogoutEvent($conn, $username);
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params