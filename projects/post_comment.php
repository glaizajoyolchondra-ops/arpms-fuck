<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $project_id = intval($_POST['project_id']);
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    
    if (empty($content)) {
        echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO comments (project_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$project_id, $user_id, $content]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized or invalid request']);
}
?>
