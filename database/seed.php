<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'arpms_db';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and Recreate DB for absolute precision
    $pdo->exec("DROP DATABASE IF EXISTS $dbname");
    $pdo->exec("CREATE DATABASE $dbname");
    $pdo->exec("USE $dbname");
    
    // Import Schema
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    // Split SQL by semicolon to execute one by one
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    
    echo "Database re-initialized and seeded with absolute precision!";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
