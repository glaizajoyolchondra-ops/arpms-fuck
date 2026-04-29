<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - ARPMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #E5E7EB;">
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-container" style="max-width: 800px;">
        <div class="view-card">
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 24px;">Settings</h2>

            <div class="form-section-premium">
                <h3>Account Settings</h3>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <p style="font-size: 14px; font-weight: 600;">Change Password</p>
                            <p style="font-size: 12px; color: var(--text-muted);">Update your account password for better security.</p>
                        </div>
                        <button class="btn-modal-cancel">Change</button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <p style="font-size: 14px; font-weight: 600;">Two-Factor Authentication</p>
                            <p style="font-size: 12px; color: var(--text-muted);">Add an extra layer of security to your account.</p>
                        </div>
                        <button class="btn-modal-cancel">Enable</button>
                    </div>
                </div>
            </div>

            <div class="form-section-premium" style="margin-top: 32px;">
                <h3>Notification Settings</h3>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <p style="font-size: 14px; font-weight: 600;">Email Notifications</p>
                            <p style="font-size: 12px; color: var(--text-muted);">Receive updates about your projects via email.</p>
                        </div>
                        <input type="checkbox" checked style="width: 20px; height: 20px;">
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;">
                        <div>
                            <p style="font-size: 14px; font-weight: 600;">Browser Notifications</p>
                            <p style="font-size: 12px; color: var(--text-muted);">Get real-time alerts in your web browser.</p>
                        </div>
                        <input type="checkbox" style="width: 20px; height: 20px;">
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
