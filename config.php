<?php
$host = 'localhost';
$db = 'your_database';
$user = 'your_user';
$pass = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ensure users table has reset fields
    $pdo->exec("
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS reset_code VARCHAR(6) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS reset_expires DATETIME DEFAULT NULL
    ");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
