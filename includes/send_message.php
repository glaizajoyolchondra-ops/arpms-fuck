<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $receiver_ids = isset($_POST['receiver_ids']) ? $_POST['receiver_ids'] : [$_POST['receiver_id']];
    $content = trim($_POST['content']);
    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : null;

    if (empty($content) || empty($receiver_ids)) {
        echo json_encode(['success' => false, 'message' => 'Empty message or no receivers selected']);
        exit();
    }

    try {
        $pdo->beginTransaction();
        $sender_id = $_SESSION['user_id'];
        $batch_id = count($receiver_ids) > 1 ? uniqid('batch_') : null;
        $sender_name = $_SESSION['full_name'] ?? 'Someone';

        foreach ($receiver_ids as $receiver_id) {
            if (empty($receiver_id)) continue;
            
            // Save message
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content, batch_id, project_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sender_id, $receiver_id, $content, $batch_id, $project_id]);

            // Create notification for receiver
            $msg_snippet = strlen($content) > 50 ? substr($content, 0, 47) . '...' : $content;
            $notif_message = $sender_name . " sent you a message: " . $msg_snippet;
            
            // Link to project view if it's a project message, otherwise teams.php
            if ($project_id) {
                $link = "projects/view.php?id=" . $project_id . "&action=contact";
            } else {
                $link = "teams.php?user_id=" . $sender_id;
            }
            
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
            $notif_stmt->execute([$receiver_id, $notif_message, $link]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
