<?php
/**
 * Return a PDO connection or null on failure.
 *
 * @return PDO|null
 */
function getPDOConnection() {
    static $pdo = null;
    if ($pdo === null) {
        /*Verify that the database credentials (host, database name, username, password)
         in config.php match the server's MySQL setup.*/
        $host = '127.0.0.1';
        $db   = '424Project';
        $user = 'appuser';
        $pass = '424project';
        
        $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=$db;charset=utf8",
        $user,
        $pass
        );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            // Optionally comment the next line to display error on screen for debugging
            echo "Database connection failed: " . $e->getMessage();
            return null;
        }
        /*Check the server's error log for any database connection error messages logged by PHP.
        This should help identify and fix the root cause of the "Call to a member function prepare() on null" error.*/ 
    }

    return $pdo;
}

/**
 * Log each login attempt to a flat file.
 *
 * @param string $email    The user email attempted
 * @param bool   $success  Whether the login succeeded
 * @return void
 */
function log_login_attempt(string $email, bool $success): void {
    $status    = $success ? 'SUCCESS' : 'FAIL';
    $timestamp = date('Y-m-d H:i:s');
    $entry     = sprintf("[%s] %s — %s\n", $timestamp, $email, $status);

    file_put_contents(
        __DIR__ . '/login_attempts.log',
        $entry,
        FILE_APPEND
    );
}
