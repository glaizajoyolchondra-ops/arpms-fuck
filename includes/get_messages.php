<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id']) && isset($_GET['contact_id'])) {
    $user_id = $_SESSION['user_id'];
    $contact_id = $_GET['contact_id'];

    // Mark as read
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")->execute([$contact_id, $user_id]);

    $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
    $stmt->execute([$user_id, $contact_id, $contact_id, $user_id]);
    $messages = $stmt->fetchAll();

    echo json_encode($messages);
}
