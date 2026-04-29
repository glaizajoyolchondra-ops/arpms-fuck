<?php
require 'config/database.php';
echo "MESSAGES TABLE:\n";
$stmt = $pdo->query('DESCRIBE messages');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nNOTIFICATIONS TABLE:\n";
$stmt = $pdo->query('DESCRIBE notifications');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
