<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #E5E7EB;">
    <?php include '../includes/header.php'; ?>

    <main class="dashboard-container" style="max-width: 800px;">
        <div class="view-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="font-size: 20px; font-weight: 700;">My Profile</h2>
                <button class="btn-modal-submit" onclick="location.href='profile_edit.php'">Edit Profile</button>
            </div>

            <div style="display: flex; gap: 32px; align-items: center; margin-bottom: 32px;">
                <div class="user-avatar-blue" style="width: 100px; height: 100px; font-size: 48px;">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <div>
                    <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 4px;"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <p style="color: var(--text-muted); font-size: 14px; font-weight: 500;"><?php echo ucfirst($user['role']); ?> • <?php echo htmlspecialchars($user['department_name'] ?? 'N/A'); ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                <div>
                    <p style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Email Address</p>
                    <p style="font-size: 15px; font-weight: 500;"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div>
                    <p style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Department</p>
                    <p style="font-size: 15px; font-weight: 500;"><?php echo htmlspecialchars($user['department_name'] ?? 'Not assigned'); ?></p>
                </div>
                <div>
                    <p style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Account Status</p>
                    <span class="status-badge-outline" style="background: #D1FAE5; color: #059669; border: none;"><?php echo ucfirst($user['status']); ?></span>
                </div>
                <div>
                    <p style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Joined Date</p>
                    <p style="font-size: 15px; font-weight: 500;"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
