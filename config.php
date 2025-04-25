<?php
function getPDOConnection() {
    static $pdo = null;


    if ($pdo === null) {
        $host = 'localhost';
        $db = '424Project';
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Return null instead of dying on connection failure
            return null;
        }
    }

    return $pdo;
    
    function log_login_attempt(string $email, bool $success): void {
    $status = $success ? 'SUCCESS' : 'FAIL';
    $when   = date('Y-m-d H:i:s');
    
    // Append to a log file in the same directory:
    file_put_contents(
        __DIR__ . '/login_attempts.log',
        "[$when] $email — $status\n",
        FILE_APPEND
    );
    }
}
?>
