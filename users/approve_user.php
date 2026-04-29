<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
