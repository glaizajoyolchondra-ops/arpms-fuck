<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $project_id = intval($_POST['project_id']);
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = intval($_POST['duration']);
    $agenda = trim($_POST['agenda']);
    $created_by = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO meetings (project_id, title, meeting_type, meeting_date, meeting_time, duration, agenda, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$project_id, $title, $type, $date, $time, $duration, $agenda, $created_by]);
        $meeting_id = $pdo->lastInsertId();
        
        if (isset($_POST['attendees'])) {
            $stmt = $pdo->prepare("INSERT INTO meeting_attendees (meeting_id, user_id) VALUES (?, ?)");
            $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            
            // attendees[] might be emails (from view.php input value) or user_ids
            $is_email = strpos($_POST['attendees'][0], '@') !== false;
            
            foreach ($_POST['attendees'] as $attendee) {
                if ($is_email) {
                    $u_stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                    $u_stmt->execute([$attendee]);
                    $uid = $u_stmt->fetchColumn();
                } else {
                    $uid = $attendee;
                }
                
                if ($uid) {
                    $stmt->execute([$meeting_id, $uid]);
                    $msg = "You have been scheduled for a meeting: " . $title . " on " . $date;
                    $notif_stmt->execute([$uid, $msg]);
                }
            }
        }
        
        $pdo->commit();
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
        }
        
        header("Location: ../projects/view.php?id=" . $project_id . "&meeting_scheduled=1");
    } catch (Exception $e) {
        $pdo->rollBack();
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit();
        }
        die("Error scheduling meeting: " . $e->getMessage());
    }
} else {
    header("Location: ../dashboard.php");
}
?>
