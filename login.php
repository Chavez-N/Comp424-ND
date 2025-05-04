<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Sanitize inputs
    $email    = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Save old input to repopulate on error
    $_SESSION['old_email'] = $email;

    $pdo = getPDOConnection();

    if ($pdo === null) {
        $_SESSION['error'] = "Warning: Backend connection failed.";
    } else {
        // 2) Prepare statement fetching only id and password_hash
        $stmt = $pdo->prepare(
            "SELECT id, password_hash
             FROM users
            WHERE email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3) Verify password against stored hash
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            log_login_attempt($email, true);
            header("Location: dashboard.html");
            exit;
        } else {
            log_login_attempt($email, false);
            $_SESSION['error'] = "Invalid email or password.";
        }
    }

    // On failure, redirect back to login form
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

  <!-- Other head elements -->

  <!-- EmailJS SDK -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

  <!-- Initialize EmailJS -->
  <script type="text/javascript">
    (function(){
      emailjs.init({
        publicKey: 'UvK7p3qdpGga1NnGA',
      });
    })();
  </script>

  <!-- Your integration script -->
  <script type="text/javascript" src="emailjs-integration.js"></script>


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
            value="<?php echo htmlspecialchars($_SESSION['old_email'] ?? ''); unset($_SESSION['old_email']); ?>"
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
        <a href="forgot-password.php">Forgot Username or Password?</a>
      </div>
    </div>
  </div>
</body>
</html>

