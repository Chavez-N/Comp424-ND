<?php
// Include database connection
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';
$message = '';
$status = 'error';

if (empty($token)) {
    $message = 'Invalid activation link.';
} else {
    // Prepare statement
    $stmt = $conn->prepare("SELECT id, token_expiry FROM users WHERE activation_token = ? AND account_status = 'pending'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token_expiry = strtotime($user['token_expiry']);
        $now = time();
        
        if ($token_expiry > $now) {
            // Token is valid, activate account
            $updateStmt = $conn->prepare("UPDATE users SET account_status = 'active', activation_token = NULL WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            
            if ($updateStmt->execute()) {
                $message = 'Your account has been activated successfully. You can now login.';
                $status = 'success';
            } else {
                $message = 'Failed to activate account. Please try again or contact support.';
            }
            $updateStmt->close();
        } else {
            $message = 'Activation link has expired. Please request a new one.';
        }
    } else {
        $message = 'Invalid activation token or account already activated.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h1>Account Activation</h1>
            
            <div class="<?php echo $status; ?>-message" style="display: block;">
                <?php echo $message; ?>
            </div>
            
            <div class="links" style="text-align: center; margin-top: 20px;">
                <a href="index.html">Return to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
