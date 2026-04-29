<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $department_id = intval($_POST['department_id']);
    $budget = floatval($_POST['budget']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $created_by = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Insert project
        $stmt = $pdo->prepare("INSERT INTO research_projects (title, description, department_id, budget, start_date, end_date, progress, status, created_by) VALUES (?, ?, ?, ?, ?, ?, 0, 'on_track', ?)");
        $stmt->execute([$title, $description, $department_id, $budget, $start_date, $end_date, $created_by]);
        $project_id = $pdo->lastInsertId();
        
        // Insert team members
        if (isset($_POST['team_members'])) {
            $stmt = $pdo->prepare("INSERT INTO project_team (project_id, user_id) VALUES (?, ?)");
            foreach ($_POST['team_members'] as $user_id) {
                $stmt->execute([$project_id, $user_id]);
            }
        }
        
        // Handle Activities
        if (isset($_POST['activities'])) {
            $stmt = $pdo->prepare("INSERT INTO weekly_checklists (project_id, week_number, activity_name, is_completed) VALUES (?, ?, ?, 0)");
            foreach ($_POST['activities'] as $index => $act_name) {
                if (!empty($act_name)) {
                    $week_num = intval($_POST['activity_weeks'][$index] ?? 1);
                    $stmt->execute([$project_id, $week_num, $act_name]);
                }
            }
        }

        // Handle Multiple File Uploads
        if (isset($_FILES['documents'])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $files = $_FILES['documents'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === 0) {
                    $file_name = time() . '_' . $files['name'][$i];
                    $file_path = $upload_dir . $file_name;
                    if (move_uploaded_file($files['tmp_name'][$i], $file_path)) {
                        $stmt = $pdo->prepare("INSERT INTO documents (project_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$project_id, $files['name'][$i], 'uploads/' . $file_name, $created_by]);
                    }
                }
            }
        }

        $pdo->commit();
        header("Location: ../dashboard.php?success=1");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving project: " . $e->getMessage());
    }
}
?>
