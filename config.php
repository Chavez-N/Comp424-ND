<?php
/**
 * Return a PDO connection or null on failure.
 *
 * @return PDO|null
 */
function getPDOConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $db   = '424Project';
        $user = 'root';
        $pass = '';
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8",
                $user,
                $pass
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            return null;
        }
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
