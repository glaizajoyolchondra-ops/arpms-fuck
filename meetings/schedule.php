<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

$project_id = intval($_GET['id'] ?? 0);
if (!$project_id) {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch Project
$stmt = $pdo->prepare("SELECT title FROM research_projects WHERE project_id = ?");
$stmt->execute([$project_id]);
$project_title = $stmt->fetchColumn();

// Fetch Team Members
$stmt = $pdo->prepare("SELECT u.user_id, u.full_name, u.role FROM users u JOIN project_team pt ON u.user_id = pt.user_id WHERE pt.project_id = ?");
$stmt->execute([$project_id]);
$team = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Meeting - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .meeting-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .summary-panel { position: sticky; top: 88px; background: white; padding: 24px; border-radius: 8px; border: 1px solid #E5E7EB; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .form-section { background: white; padding: 24px; border-radius: 8px; border: 1px solid #E5E7EB; margin-bottom: 24px; }
        .attendee-list { max-height: 200px; overflow-y: auto; border: 1px solid #E5E7EB; border-radius: 6px; padding: 12px; margin-top: 12px; }
        .attendee-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #F3F4F6; }
        .attendee-item:last-child { border-bottom: none; }
    </style>
</head>
<body style="background: #F3F4F6;">
    <?php include '../includes/sidebar.php'; ?>
    <div style="margin-left: 240px;">
        <?php include '../includes/header.php'; ?>
    </div>
    
    <main class="main-content">
        <div style="max-width: 1000px; margin: 0 auto;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <a href="../projects/view.php?id=<?php echo $project_id; ?>" style="color: #6B7280; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
                <h1 style="font-size: 24px; font-weight: 700; color: #111827;">Schedule Meeting - <?php echo htmlspecialchars($project_title); ?></h1>
            </div>

            <div class="meeting-grid">
                <div class="left-col">
                    <form action="save_meeting.php" method="POST">
                        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                        
                        <div class="form-section">
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Meeting Title</label>
                            <input type="text" name="title" id="meeting-title-input" class="login-input" style="padding-left: 16px;" placeholder="Enter meeting title..." oninput="updateSummary()">
                        </div>

                        <div class="form-section">
                            <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px;">Select Attendees</h3>
                            <input type="text" class="login-input" style="padding-left: 16px; height: 40px;" placeholder="Search Attendees">
                            <div class="attendee-list">
                                <?php foreach($team as $m): ?>
                                <label class="attendee-item">
                                    <input type="checkbox" name="attendees[]" value="<?php echo $m['user_id']; ?>" class="attendee-checkbox" data-name="<?php echo htmlspecialchars($m['full_name']); ?>" onchange="updateSummary()">
                                    <span style="font-size: 14px;"><?php echo htmlspecialchars($m['full_name']); ?> (<?php echo ucfirst($m['role']); ?>)</span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px;">Meeting Details</h3>
                            <div style="margin-bottom: 16px;">
                                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Meeting Type</label>
                                <select name="type" id="meeting-type-input" class="login-input" style="padding-left: 16px;" onchange="updateSummary()">
                                    <option value="Video Call">Video Call</option>
                                    <option value="In-person">In-person</option>
                                </select>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Date</label>
                                    <input type="date" name="date" id="meeting-date-input" class="login-input" style="padding-left: 16px;" onchange="updateSummary()">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Time</label>
                                    <input type="time" name="time" id="meeting-time-input" class="login-input" style="padding-left: 16px;" onchange="updateSummary()">
                                </div>
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Duration (minutes)</label>
                                <input type="number" name="duration" id="meeting-duration-input" class="login-input" style="padding-left: 16px;" value="60" oninput="updateSummary()">
                            </div>
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Agenda</label>
                                <textarea name="agenda" class="login-input" style="padding: 12px; height: 80px;" placeholder="Enter meeting agenda..."></textarea>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
                            <button type="button" class="btn-edit" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn-view" style="padding: 12px 32px;">Schedule Meeting</button>
                        </div>
                    </form>
                </div>

                <div class="right-col">
                    <div class="summary-panel">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 20px;">Meeting Summary</h3>
                        <div style="display: flex; flex-direction: column; gap: 16px; font-size: 13px;">
                            <div>
                                <span style="color: #6B7280; display: block; margin-bottom: 4px;">Title</span>
                                <span style="font-weight: 600;" id="summary-title">Not set</span>
                            </div>
                            <div>
                                <span style="color: #6B7280; display: block; margin-bottom: 4px;">Attendees</span>
                                <span style="font-weight: 600;" id="attendee-count">0 selected</span>
                            </div>
                            <div>
                                <span style="color: #6B7280; display: block; margin-bottom: 4px;">Date & Time</span>
                                <span style="font-weight: 600;" id="summary-datetime">Not set</span>
                            </div>
                            <div>
                                <span style="color: #6B7280; display: block; margin-bottom: 4px;">Duration</span>
                                <span style="font-weight: 600;" id="summary-duration">60 minutes</span>
                            </div>
                            <div>
                                <span style="color: #6B7280; display: block; margin-bottom: 4px;">Type</span>
                                <span style="font-weight: 600;" id="summary-type">Video Call</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function updateSummary() {
            const title = document.getElementById('meeting-title-input').value;
            const type = document.getElementById('meeting-type-input').value;
            const date = document.getElementById('meeting-date-input').value;
            const time = document.getElementById('meeting-time-input').value;
            const duration = document.getElementById('meeting-duration-input').value;
            
            const selected = document.querySelectorAll('.attendee-checkbox:checked').length;
            
            document.getElementById('summary-title').textContent = title || 'Not set';
            document.getElementById('attendee-count').textContent = selected + ' selected';
            document.getElementById('summary-type').textContent = type;
            document.getElementById('summary-duration').textContent = duration + ' minutes';
            
            if (date && time) {
                document.getElementById('summary-datetime').textContent = date + ' at ' + time;
            } else {
                document.getElementById('summary-datetime').textContent = 'Not set';
            }
        }
    </script>
</body>
</html>
