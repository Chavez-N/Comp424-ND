<?php
try {
    // adjust host, dbname, user, pass to match config.php
    $pdo = new PDO('mysql:host=localhost;dbname=424Project;charset=utf8', 'appuser', '424project');
    echo "✅ PDO connected successfully!";
} catch (PDOException $e) {
    die("❌ PDO connection failed: " . $e->getMessage());
}
