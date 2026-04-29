<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $project_id = intval($_POST['project_id']);
    $title = $_POST['title'];
    $description = $_POST['description'];
    $department_id = intval($_POST['department_id']);
    $coordinator_id = !empty($_POST['coordinator_id']) ? intval($_POST['coordinator_id']) : null;
    $budget_personnel = floatval($_POST['budget_personnel'] ?? 0);
    $budget_equipment = floatval($_POST['budget_equipment'] ?? 0);
    $budget_materials = floatval($_POST['budget_materials'] ?? 0);
    $budget_other = floatval($_POST['budget_other'] ?? 0);
    $total_budget = $budget_personnel + $budget_equipment + $budget_materials + $budget_other;

    try {
        $pdo->beginTransaction();

        // Update project
        $stmt = $pdo->prepare("UPDATE research_projects SET 
            title = ?, 
            description = ?, 
            department_id = ?, 
            coordinator_id = ?,
            budget = ?,
            budget_personnel = ?,
            budget_equipment = ?,
            budget_materials = ?,
            budget_other = ?
            WHERE project_id = ?");
        
        $stmt->execute([
            $title, 
            $description, 
            $department_id, 
            $coordinator_id,
            $total_budget,
            $budget_personnel,
            $budget_equipment,
            $budget_materials,
            $budget_other,
            $project_id
        ]);

        // Update team members
        if (isset($_POST['team_members'])) {
            // Remove old team
            $pdo->prepare("DELETE FROM project_team WHERE project_id = ?")->execute([$project_id]);
            
            $stmt = $pdo->prepare("INSERT INTO project_team (project_id, user_id) VALUES (?, ?)");
            foreach ($_POST['team_members'] as $user_id) {
                $stmt->execute([$project_id, $user_id]);
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
                        $stmt->execute([$project_id, $files['name'][$i], 'uploads/' . $file_name, $_SESSION['user_id']]);
                    }
                }
            }
        }

        // Update activities (weekly checklists)
        if (isset($_POST['activity_names'])) {
            // Remove old activities for this project
            $pdo->prepare("DELETE FROM weekly_checklists WHERE project_id = ?")->execute([$project_id]);
            
            $stmt = $pdo->prepare("INSERT INTO weekly_checklists (project_id, week_number, activity_name, is_completed) VALUES (?, ?, ?, ?)");
            foreach ($_POST['activity_names'] as $index => $name) {
                if (empty(trim($name))) continue;
                
                $weeks = $_POST['activity_weeks'][$index] ?? [];
                
                // We need to insert rows for weeks 1 to 5
                for ($w = 1; $w <= 5; $w++) {
                    $is_completed = in_array($w, $weeks) ? 1 : 0;
                    $stmt->execute([$project_id, $w, $name, $is_completed]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../dashboard.php?updated=1");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error updating project: " . $e->getMessage());
    }
}
?>
