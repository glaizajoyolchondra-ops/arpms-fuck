<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $contact_id = $_GET['contact_id'] ?? null;
    $project_id = $_GET['project_id'] ?? null;

    if ($contact_id) {
        // Mark as read
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")->execute([$contact_id, $user_id]);

        $stmt = $pdo->prepare("SELECT m.*, u.full_name as receiver_name 
                               FROM messages m 
                               LEFT JOIN users u ON m.receiver_id = u.user_id 
                               WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) 
                               ORDER BY m.created_at ASC");
        $stmt->execute([$user_id, $contact_id, $contact_id, $user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($project_id) {
        // Mark as read for all project messages sent to current user
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE project_id = ? AND receiver_id = ?")->execute([$project_id, $user_id]);

        $stmt = $pdo->prepare("SELECT m.*, u.full_name as receiver_name 
                               FROM messages m 
                               LEFT JOIN users u ON m.receiver_id = u.user_id 
                               WHERE m.project_id = ? AND (m.sender_id = ? OR m.receiver_id = ?) 
                               ORDER BY m.created_at ASC");
        $stmt->execute([$project_id, $user_id, $user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo json_encode(['error' => 'Missing parameters']);
        exit();
    }

    // Enhance sent messages with "seen by" info
    foreach ($messages as &$msg) {
        if ($msg['sender_id'] == $user_id) {
            if ($msg['batch_id']) {
                $batch_stmt = $pdo->prepare("SELECT u.full_name, m.is_read FROM messages m JOIN users u ON m.receiver_id = u.user_id WHERE m.batch_id = ?");
                $batch_stmt->execute([$msg['batch_id']]);
                $msg['seen_info'] = $batch_stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $msg['seen_info'] = [['full_name' => $msg['receiver_name'], 'is_read' => $msg['is_read']]];
            }
        }
    }

    echo json_encode($messages);
}
