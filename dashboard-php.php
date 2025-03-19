<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: index.html');
    exit;
}

// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Session timeout - log out user after 15 minutes of inactivity
$session_timeout = 15 * 60; // 15 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Log user out
    session_unset();
    session_destroy();
    header('Location: index.html?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Get user data
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$login_count = $_SESSION['login_count'];
$last_login = $_SESSION['last_login'] ? date('F j, Y, g:i a', strtotime($_SESSION['last_login'])) : 'First Login';

// Generate CSRF token for download link
$download_token = bin2hex(random_bytes(32));
$_SESSION['download_token'] = $download_token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .dashboard {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .user-info {
            margin-bottom: 30px;
        }
        
        .download-section {
            text-align: center;
            padding: 20px;
            background-color: #f4f7f9;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .logout-btn {
            background-color: #e74c3c;
            margin-top: 20px;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <h1>Dashboard</h1>
            
            <div class="user-info">
                <h2>Hi, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h2>
                <p>You have logged in <?php echo $login_count; ?> times</p>
                <p>Last login date: <?php echo $last_login; ?></p>
            </div>
            
            <div class="download-section">
                <h3>Download Confidential File</h3>
                <p>Click the button below to download the company confidential file.</p>
                <a href="download.php?token=<?php echo $download_token; ?>" class="btn">Download File</a>
            </div>
            
            <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
    </div>
    
    <script>
        // Prevent browser back button after logout
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, null, window.location.href);
        };
    </script>
</body>
</html>
