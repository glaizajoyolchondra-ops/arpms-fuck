<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = intval($_POST['project_id']);
    
    try {
        $pdo->beginTransaction();

        // If checklist_id is provided, update that specific activity
        if (isset($_POST['checklist_id'])) {
            $checklist_id = intval($_POST['checklist_id']);
            $is_completed = intval($_POST['is_completed']);
            $stmt = $pdo->prepare("UPDATE weekly_checklists SET is_completed = ?, completed_date = ? WHERE checklist_id = ?");
            $stmt->execute([$is_completed, $is_completed ? date('Y-m-d H:i:s') : null, $checklist_id]);
        }

        // Recalculate total progress for the project
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(is_completed) as completed FROM weekly_checklists WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $stats = $stmt->fetch();
        
        $progress = 0;
        if ($stats['total'] > 0) {
            $progress = round(($stats['completed'] / $stats['total']) * 100);
        }

        $stmt = $pdo->prepare("UPDATE research_projects SET progress = ? WHERE project_id = ?");
        $stmt->execute([$progress, $project_id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'progress' => $progress]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
