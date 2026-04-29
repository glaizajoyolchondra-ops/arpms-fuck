<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $content = trim($_POST['content']);

    if (empty($content) || empty($receiver_id)) {
        echo json_encode(['success' => false, 'message' => 'Empty message or receiver']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Save message
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $content]);

        // Create notification for receiver
        $msg_snippet = strlen($content) > 50 ? substr($content, 0, 47) . '...' : $content;
        $notif_message = $_SESSION['full_name'] . " sent you a message: " . $msg_snippet;
        $link = "teams.php?user_id=" . $sender_id;
        
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
        $notif_stmt->execute([$receiver_id, $notif_message, $link]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
