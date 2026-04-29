<?php
require 'config/database.php';
try {
    $pdo->exec("ALTER TABLE messages ADD COLUMN batch_id VARCHAR(50) DEFAULT NULL");
    echo "Added batch_id to messages table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
