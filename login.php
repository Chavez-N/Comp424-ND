<?php
session_start();
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email    = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Save old input so we can repopulate
    $_SESSION['old_email'] = $email;

    $pdo = getPDOConnection();
    // Fallback credentials if DB is down
    $default_email    = 'torosyandiran@gmail.com';
    $default_password = 'Diran#123';

    // Authenticate
    $authenticated = false;
    $need_verify   = false;

    if ($pdo === null) {
        if ($email === $default_email && $password === $default_password) {
            $authenticated = true;
        }
    } else {
        $stmt = $pdo->prepare("SELECT id, password, verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if ($user['verified']) {
                $_SESSION['user_id'] = $user['id'];
                log_login_attempt($email, true);
                header("Location: dashboard.html");
                exit;
            } else {
                $need_verify = true;
            }
        }
    }

    if ($authenticated) {
        $_SESSION['user_id'] = ($pdo===null ? 0 : $user['id']);
        log_login_attempt($email, true);
        header("Location: dashboard.html");
        exit;
    } elseif ($need_verify) {
        log_login_attempt($email, false);
        $_SESSION['error'] = "Please verify your email before logging in.";
    } else {
        log_login_attempt($email, false);
        $_SESSION['error'] = "Invalid email or password.";
    }

    // On error, redirect back here to show the form
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Secure Login</title>
  <link href="styles.css" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <h1>Secure Login</h1>
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message"><?php
          echo htmlspecialchars($_SESSION['error']);
          unset($_SESSION['error']);
        ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="input-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            required
            value="<?php
              echo htmlspecialchars($_SESSION['old_email'] ?? '');
              unset($_SESSION['old_email']);
            ?>"
          >
        </div>
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="login-btn">Login</button>
      </form>

      <div class="links">
        <a href="register.html">New User? Sign Up</a><br>
        <a href="forgot-password.html">Forgot Username or Password?</a>
      </div>
    </div>
  </div>
</body>
</html>
