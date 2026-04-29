<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch Projects
$query = "SELECT p.*, d.department_name, u.full_name as coordinator_name 
          FROM research_projects p 
          LEFT JOIN departments d ON p.department_id = d.department_id
          LEFT JOIN users u ON p.coordinator_id = u.user_id";

if ($role === 'researcher') {
    $query .= " JOIN project_team pt ON p.project_id = pt.project_id WHERE pt.user_id = $user_id";
} elseif ($role === 'coordinator') {
    $query .= " WHERE p.coordinator_id = $user_id OR p.created_by = $user_id";
}

$projects = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARPMS - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="dashboard-container">
        <?php foreach($projects as $p): 
            // Fetch team members
            $team_stmt = $pdo->prepare("SELECT u.full_name FROM users u JOIN project_team pt ON u.user_id = pt.user_id WHERE pt.project_id = ?");
            $team_stmt->execute([$p['project_id']]);
            $team = $team_stmt->fetchAll(PDO::FETCH_COLUMN);

            $status_label = 'In Progress';
            $status_class = '';
            if($p['status'] == 'on_track') { $status_label = 'On Track'; }
            elseif($p['status'] == 'delayed') { $status_label = 'Delayed'; }
        ?>
        <div class="project-card-premium">
            <div class="card-top-row">
                <h3 class="card-title-premium"><?php echo htmlspecialchars($p['title']); ?></h3>
                <span class="status-badge-outline"><?php echo $status_label; ?></span>
            </div>

            <p class="card-desc-premium"><?php echo htmlspecialchars($p['description']); ?></p>

            <div class="card-metrics">
                <div class="metric-item">
                    <i class="fa-solid fa-users" style="color: #6B7280;"></i>
                    <span class="metric-label">Researchers:</span>
                    <span class="metric-value"><?php echo count($team); ?></span>
                </div>
                <div class="metric-item">
                    <i class="fa-solid fa-calendar" style="color: #6B7280;"></i>
                    <span class="metric-label">Duration:</span>
                    <span class="metric-value"><?php echo date('n/j/Y', strtotime($p['start_date'])); ?> - <?php echo date('n/j/Y', strtotime($p['end_date'])); ?></span>
                </div>
                <div class="metric-item">
                    <span style="font-weight: 700; color: #6B7280;">₱</span>
                    <span class="metric-label">Budget:</span>
                    <span class="metric-value">₱ <?php echo number_format($p['budget']); ?></span>
                </div>
                <div class="metric-item">
                    <i class="fa-solid fa-building" style="color: #6B7280;"></i>
                    <span class="metric-label">Department:</span>
                    <span class="metric-value"><?php echo htmlspecialchars($p['department_name']); ?></span>
                </div>
            </div>

            <div class="progress-container-premium">
                <div class="progress-header-row">
                    <span class="progress-label-text">Progress:</span>
                    <span class="progress-percent-text"><?php echo $p['progress']; ?>%</span>
                </div>
                <div class="progress-bar-bg-premium">
                    <div class="progress-bar-fill-premium" style="width: <?php echo $p['progress']; ?>%;"></div>
                </div>
            </div>

            <div class="team-container-premium">
                <span class="team-label-text">Research Team:</span>
                <div class="team-pills">
                    <?php foreach($team as $member): ?>
                        <div class="team-pill"><?php echo htmlspecialchars($member); ?></div>
                    <?php endforeach; ?>
                    <?php if($p['coordinator_name']): ?>
                        <div class="team-pill" style="background: #F9FAFB;">Coordinator: Prof. <?php echo htmlspecialchars($p['coordinator_name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-footer-actions">
                <button class="btn-card-action" onclick="location.href='projects/view.php?id=<?php echo $p['project_id']; ?>'">
                    <i class="fa-regular fa-eye"></i> View Details
                </button>
                <button class="btn-card-action" onclick="openEditModal(<?php echo $p['project_id']; ?>)">
                    <i class="fa-regular fa-pen-to-square"></i> Edit
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </main>

    <!-- Modals -->
    <?php include 'projects/modals.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
