<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch projects summary counts
$total_stmt = $pdo->query("SELECT COUNT(*) FROM research_projects");
$total_count = $total_stmt->fetchColumn();

$on_track_stmt = $pdo->query("SELECT COUNT(*) FROM research_projects WHERE status = 'on_track'");
$on_track_count = $on_track_stmt->fetchColumn();

$pending_stmt = $pdo->query("SELECT COUNT(*) FROM research_projects WHERE status = 'not_started'");
$pending_count = $pending_stmt->fetchColumn();

$completed_stmt = $pdo->query("SELECT COUNT(*) FROM research_projects WHERE status = 'completed'");
$completed_count = $completed_stmt->fetchColumn();

// Base query for projects
$query = "SELECT p.*, d.department_name 
          FROM research_projects p 
          LEFT JOIN departments d ON p.department_id = d.department_id";

$params = [];
if ($role === 'researcher') {
    $query .= " JOIN project_team pt ON p.project_id = pt.project_id WHERE pt.user_id = ?";
    $params[] = $user_id;
} elseif ($role === 'coordinator') {
    $query .= " WHERE p.coordinator_id = ? OR p.created_by = ?";
    $params[] = $user_id;
    $params[] = $user_id;
}

// Add status filter if present
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $query .= (strpos($query, 'WHERE') === false ? " WHERE " : " AND ") . " p.status = ?";
    $params[] = $_GET['status'];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Projects - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #E5E7EB;">
    <?php include '../includes/header.php'; ?>

    <main class="dashboard-container" style="max-width: 1100px;">
        <div style="margin-bottom: 24px;">
            <button class="btn-modal-cancel" onclick="location.href='../dashboard.php'" style="background: white; border: none; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-chevron-left"></i> Back
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px;">
            <!-- Total Projects Card -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #E5E7EB;">
                <p style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">Total Projects</p>
                <p style="font-size: 36px; font-weight: 700; color: #111827;"><?php echo $total_count; ?></p>
            </div>
            
            <!-- On Track Card with Filter Dropdown (Image 3) -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #E5E7EB; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <p style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">On Track</p>
                        <p style="font-size: 36px; font-weight: 700; color: #2D5BFF;"><?php echo $on_track_count; ?></p>
                    </div>
                    <i class="fa-solid fa-filter" style="color: #6B7280; cursor: pointer;" onclick="toggleStatusFilter()"></i>
                </div>

                <!-- Status Filter Dropdown (Image 3) -->
                <div id="statusFilterDropdown" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #E5E7EB; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); width: 180px; display: none; z-index: 10;">
                    <div style="padding: 4px;">
                        <a href="?status=not_started" style="display: flex; justify-content: space-between; padding: 12px; text-decoration: none; color: #111827; font-size: 14px; font-weight: 500;">
                            <span>Not Started</span>
                            <span style="color: #2D5BFF; font-weight: 700;"><?php echo $pending_count; ?></span>
                        </a>
                        <a href="?status=in_progress" style="display: flex; justify-content: space-between; padding: 12px; border-top: 1px solid #E5E7EB; text-decoration: none; color: #111827; font-size: 14px; font-weight: 500;">
                            <span>In Progress</span>
                            <span style="color: #2D5BFF; font-weight: 700;">1</span> <!-- Mock, should fetch -->
                        </a>
                        <a href="?status=at_risk" style="display: flex; justify-content: space-between; padding: 12px; border-top: 1px solid #E5E7EB; text-decoration: none; color: #111827; font-size: 14px; font-weight: 500;">
                            <span>At risk</span>
                            <span style="color: #2D5BFF; font-weight: 700;">0</span>
                        </a>
                        <a href="?status=delayed" style="display: flex; justify-content: space-between; padding: 12px; border-top: 1px solid #E5E7EB; text-decoration: none; color: #111827; font-size: 14px; font-weight: 500;">
                            <span>Delayed</span>
                            <span style="color: #2D5BFF; font-weight: 700;">1</span>
                        </a>
                        <a href="?status=on_track" style="display: flex; justify-content: space-between; padding: 12px; border-top: 1px solid #E5E7EB; text-decoration: none; color: #111827; font-size: 14px; font-weight: 500;">
                            <span>On Track</span>
                            <span style="color: #2D5BFF; font-weight: 700;">1</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pending Projects Card -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #E5E7EB;">
                <p style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">Pending Projects</p>
                <p style="font-size: 36px; font-weight: 700; color: #FBBF24;"><?php echo $pending_count; ?></p>
            </div>

            <!-- Completed Card -->
            <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #E5E7EB;">
                <p style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">Completed</p>
                <p style="font-size: 36px; font-weight: 700; color: #10B981;"><?php echo $completed_count; ?></p>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 32px; border: 1px solid #E5E7EB;">
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 24px;">All Projects</h2>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #E5E7EB; text-align: left;">
                        <th style="padding: 12px 0; color: #6B7280; font-size: 14px; font-weight: 500;">Project Title</th>
                        <th style="padding: 12px 0; color: #6B7280; font-size: 14px; font-weight: 500;">Department</th>
                        <th style="padding: 12px 0; color: #6B7280; font-size: 14px; font-weight: 500;">Timeline</th>
                        <th style="padding: 12px 0; color: #6B7280; font-size: 14px; font-weight: 500;">Status</th>
                        <th style="padding: 12px 0; color: #6B7280; font-size: 14px; font-weight: 500; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($projects as $p): ?>
                    <tr style="border-bottom: 1px solid #F3F4F6;">
                        <td style="padding: 20px 0; font-weight: 700; font-size: 14px;"><?php echo htmlspecialchars($p['title']); ?></td>
                        <td style="padding: 20px 0; color: #6B7280; font-size: 13px;"><?php echo htmlspecialchars($p['department_name']); ?></td>
                        <td style="padding: 20px 0; color: #6B7280; font-size: 13px;"><?php echo date('M Y', strtotime($p['start_date'])); ?> - <?php echo date('M Y', strtotime($p['end_date'])); ?></td>
                        <td style="padding: 20px 0;">
                            <?php 
                            $status_text = 'In Progress';
                            if($p['status'] === 'on_track') $status_text = 'On Track';
                            elseif($p['status'] === 'delayed') $status_text = 'Delayed';
                            ?>
                            <span style="padding: 6px 16px; border: 1px solid #6B7280; border-radius: 20px; font-size: 12px; font-weight: 500; color: #111827;">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td style="padding: 20px 0; text-align: right;">
                            <i class="fa-regular fa-eye" style="color: #6B7280; margin-right: 16px; cursor: pointer;" onclick="location.href='view.php?id=<?php echo $p['project_id']; ?>'"></i>
                            <i class="fa-regular fa-pen-to-square" style="color: #6B7280; cursor: pointer;" onclick="openEditModal(<?php echo $p['project_id']; ?>)"></i>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include 'modals.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        function toggleStatusFilter() {
            const dropdown = document.getElementById('statusFilterDropdown');
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
