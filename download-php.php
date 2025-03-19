<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Validate download token (CSRF protection)
if (!isset($_GET['token']) || !isset($_SESSION['download_token']) || $_GET['token'] !== $_SESSION['download_token']) {
    // Invalid or missing token
    header('HTTP/1.1 403 Forbidden');
    echo '<h1>403 Forbidden</h1><p>Invalid download request.</p>';
    exit;
}

// File path (this should be stored in a secure location outside the web root)
$file_path = 'secure_files/company_confidential_file.txt';

// Check if file exists
if (!file_exists($file_path)) {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>404 Not Found</h1><p>The requested file does not exist.</p>';
    exit;
}

// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Log the download
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$file_name = basename($file_path);
$ip_address = $_SERVER['REMOTE_ADDR'];

// Prepare and execute the statement
$stmt = $conn->prepare("INSERT INTO download_logs (user_id, username, file_name, ip_address, download_time) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isss", $user_id, $username, $file_name, $ip_address);
$stmt->execute();
$stmt->close();

// Regenerate the download token to prevent replay attacks
$_SESSION['download_token'] = bin2hex(random_bytes(32));

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
ob_clean();
flush();

// Read file and output
readfile($file_path);
exit;
?>
