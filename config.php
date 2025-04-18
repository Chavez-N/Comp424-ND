function getPDOConnection() {
    static $pdo = null;

if (!isset($_POST['g-recaptcha-response'])) {
    die("CAPTCHA not submitted. Please try again.");
}


    if ($pdo === null) {
        $host = 'localhost';
        $db = '424Project';
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

