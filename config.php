<?php
/**
 * Return a PDO connection or null on failure.
 *
 * @return PDO|null
 */
function getPDOConnection() {
    static $pdo = null;
    if ($pdo === null) {
        /* Verify that the database credentials match your MySQL setup. */
        $host = '127.0.0.1';
        $db   = '424Project';
        $user = 'appuser';
        $pass = '424project';

        try {
            // Attempt connection
            $pdo = new PDO(
                "mysql:host=$host;port=3306;dbname=$db;charset=utf8",
                $user,
                $pass
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            echo "Database connection failed: " . $e->getMessage();
            return null;
        }
    }
    return $pdo;
}

/**
 * Log a login attempt to the database.
 *
 * @param string $email The email address attempting login
 * @param bool $success Whether the login was successful
 */
function log_login_attempt($email, $success) {
    $pdo = getPDOConnection();
    if ($pdo === null) {
        error_log("Failed to log login attempt: No database connection.");
        return;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO login_attempts (email, success, attempt_time) VALUES (?, ?, NOW())"
        );
        $stmt->execute([
            $email,
            $success ? 1 : 0
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log login attempt: " . $e->getMessage());
    }
}
?>
