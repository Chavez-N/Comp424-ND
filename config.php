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
            // Uncomment the next line if you need to see the error in-browser
            echo "Database connection failed: " . $e->getMessage();
            return null;
        }
    }

    return $pdo;
}

