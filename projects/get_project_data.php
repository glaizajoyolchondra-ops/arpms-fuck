<?php
require_once '../config/database.php';

$project_id = $_GET['id'] ?? null;
if (!$project_id) {
    echo json_encode(['error' => 'No ID provided']);
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM research_projects WHERE project_id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    echo json_encode(['error' => 'Project not found']);
    exit();
}

// Fetch team
$team_stmt = $pdo->prepare("SELECT u.user_id, u.full_name FROM users u JOIN project_team pt ON u.user_id = pt.user_id WHERE pt.project_id = ?");
$team_stmt->execute([$project_id]);
$project['team'] = $team_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch activities (weekly checklists)
// For simplicity, we group them by activity name
$act_stmt = $pdo->prepare("SELECT * FROM weekly_checklists WHERE project_id = ? ORDER BY week_number ASC");
$act_stmt->execute([$project_id]);
$checklists = $act_stmt->fetchAll(PDO::FETCH_ASSOC);

$activities = [];
foreach($checklists as $c) {
    $name = $c['activity_name'];
    if (!isset($activities[$name])) {
        $activities[$name] = [
            'activity_name' => $name,
            'week2' => false,
            'week3' => false,
            'week4' => false,
            'week5' => false,
            'week6' => false,
        ];
    }
    if ($c['week_number'] == 2) $activities[$name]['week2'] = (bool)$c['is_completed'];
    if ($c['week_number'] == 3) $activities[$name]['week3'] = (bool)$c['is_completed'];
    if ($c['week_number'] == 4) $activities[$name]['week4'] = (bool)$c['is_completed'];
    if ($c['week_number'] == 5) $activities[$name]['week5'] = (bool)$c['is_completed'];
    if ($c['week_number'] == 6) $activities[$name]['week6'] = (bool)$c['is_completed'];
}

$project['activities'] = array_values($activities);

header('Content-Type: application/json');
echo json_encode($project);
