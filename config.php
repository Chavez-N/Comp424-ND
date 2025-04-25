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
}
?>
