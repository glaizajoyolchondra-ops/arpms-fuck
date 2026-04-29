<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

$project_id = $_GET['id'] ?? null;
if (!$project_id) {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch Project
$query = "SELECT p.*, d.department_name, u.full_name as coordinator_name 
          FROM research_projects p 
          LEFT JOIN departments d ON p.department_id = d.department_id
          LEFT JOIN users u ON p.coordinator_id = u.user_id
          WHERE p.project_id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$project_id]);
$p = $stmt->fetch();

if (!$p) {
    header("Location: ../dashboard.php");
    exit();
}

// Access Control
if ($_SESSION['role'] === 'researcher') {
    $check_stmt = $pdo->prepare("SELECT 1 FROM project_team WHERE project_id = ? AND user_id = ?");
    $check_stmt->execute([$project_id, $_SESSION['user_id']]);
    if (!$check_stmt->fetch()) {
        header("Location: ../dashboard.php");
        exit();
    }
} elseif ($_SESSION['role'] === 'coordinator') {
    if ($p['coordinator_id'] != $_SESSION['user_id'] && $p['created_by'] != $_SESSION['user_id']) {
        header("Location: ../dashboard.php");
        exit();
    }
}

// Fetch Team
$team_stmt = $pdo->prepare("SELECT u.full_name, u.role, u.email FROM users u JOIN project_team pt ON u.user_id = pt.user_id WHERE pt.project_id = ?");
$team_stmt->execute([$project_id]);
$team = $team_stmt->fetchAll();

// Fetch Activities
$act_stmt = $pdo->prepare("SELECT * FROM weekly_checklists WHERE project_id = ? ORDER BY week_number ASC");
$act_stmt->execute([$project_id]);
$checklists = $act_stmt->fetchAll();

// Fetch Comments
$comm_stmt = $pdo->prepare("SELECT c.*, u.full_name, u.role FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.project_id = ? ORDER BY c.created_at DESC");
$comm_stmt->execute([$project_id]);
$comments = $comm_stmt->fetchAll();

// Fetch Documents
$doc_stmt = $pdo->prepare("SELECT d.*, u.full_name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.uploaded_by = u.user_id WHERE d.project_id = ? ORDER BY d.upload_date DESC");
$doc_stmt->execute([$project_id]);
$documents = $doc_stmt->fetchAll();

// Progress Calculation
$total_acts = count($checklists);
$completed_acts = count(array_filter($checklists, function($c) { return $c['is_completed']; }));
$progress_pct = $total_acts > 0 ? round(($completed_acts / $total_acts) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($p['title']); ?> - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #E5E7EB;">
    <?php include '../includes/header.php'; ?>

    <main class="dashboard-container" style="max-width: 1100px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 20px; font-weight: 700;"><?php echo htmlspecialchars($p['title']); ?></h1>
            <span class="status-badge-outline" style="background: white;">On Track</span>
        </div>

        <div class="view-grid">
            <div class="view-left">
                <!-- Project Overview -->
                <div class="view-card">
                    <h3>Project Overview</h3>
                    <div style="margin-bottom: 20px;">
                        <p style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Description</p>
                        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.5;"><?php echo htmlspecialchars($p['description']); ?></p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; font-size: 14px;">
                        <div>
                            <p style="margin-bottom: 8px;"><span style="font-weight: 600;">Start Date:</span> <?php echo date('n/j/Y', strtotime($p['start_date'])); ?></p>
                            <p><span style="font-weight: 600;">Department:</span> <?php echo htmlspecialchars($p['department_name']); ?></p>
                        </div>
                        <div>
                            <p style="margin-bottom: 8px;"><span style="font-weight: 600;">End Date:</span> <?php echo date('n/j/Y', strtotime($p['end_date'])); ?></p>
                            <p><span style="font-weight: 600;">Budget:</span> ₱ <?php echo number_format($p['budget']); ?></p>
                        </div>
                    </div>
                    <div style="margin-top: 24px;">
                        <p style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Research Team</p>
                        <div class="team-pills">
                            <?php foreach($team as $m): ?>
                                <div class="team-pill" style="border-color: #10B981; color: #111827;"><?php echo htmlspecialchars($m['full_name']); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Project Timeline -->
                <div class="view-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="margin: 0;">Project Timeline</h3>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 14px; color: var(--text-muted);">Progress:</span>
                            <span style="font-size: 14px; font-weight: 700;"><?php echo $progress_pct; ?>%</span>
                        </div>
                    </div>
                    <div class="progress-bar-bg-premium" style="margin-bottom: 24px;">
                        <div class="progress-bar-fill-premium" style="width: <?php echo $progress_pct; ?>%;"></div>
                    </div>
                    <div class="timeline-table-container">
                        <table class="timeline-table">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Week 2</th>
                                    <th>Week 3</th>
                                    <th>Week 4</th>
                                    <th>Week 5</th>
                                    <th>Week 6</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Group activities by name
                                $grouped = [];
                                foreach($checklists as $c) {
                                    $grouped[$c['activity_name']][$c['week_number']] = $c;
                                }
                                foreach($grouped as $name => $weeks): 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($name); ?></td>
                                    <?php for($i=2; $i<=6; $i++): ?>
                                        <td>
                                            <?php if(isset($weeks[$i])): ?>
                                                <input type="checkbox" class="checkbox-custom" 
                                                       onclick="toggleActivity(<?php echo $weeks[$i]['checklist_id']; ?>, this)"
                                                       <?php echo $weeks[$i]['is_completed'] ? 'checked' : ''; ?>>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Comments -->
                <div class="view-card">
                    <h3>Comments & Discussion</h3>
                    <div id="commentsList">
                        <?php foreach($comments as $c): ?>
                        <div class="comment-box">
                            <div class="comment-avatar"></div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo htmlspecialchars($c['full_name']); ?></span>
                                    <span class="comment-role-badge <?php echo $c['role'] == 'coordinator' ? 'role-coordinator' : 'role-researcher'; ?>">
                                        <?php echo ucfirst($c['role']); ?>
                                    </span>
                                </div>
                                <p class="comment-text"><?php echo htmlspecialchars($c['content']); ?></p>
                                <span class="comment-date"><?php echo date('n/j/Y', strtotime($c['created_at'])); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="comment-input-area">
                        <textarea id="commentText" class="comment-textarea" placeholder="Add a comment or ask a question..."></textarea>
                        <button class="btn-post-comment" onclick="postComment()">Post Comment</button>
                    </div>
                </div>
            </div>

            <div class="view-right">
                <!-- Project Status -->
                <div class="view-card">
                    <h3>Project Status</h3>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px;">Submitted</span>
                            <i class="fa-regular fa-circle-check" style="color: #22C55E; font-size: 18px;"></i>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px;">In Progress</span>
                            <i class="fa-regular fa-circle-check" style="color: #22C55E; font-size: 18px;"></i>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px;">On Track</span>
                            <i class="fa-regular fa-clock" style="color: #6B7280; font-size: 18px;"></i>
                        </div>
                    </div>
                </div>

                <!-- Budget Breakdown -->
                <div class="view-card">
                    <h3>Budget</h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 14px; color: var(--text-muted);">Total Budget</span>
                        <span style="font-size: 14px; font-weight: 700;">₱ <?php echo number_format($p['budget']); ?></span>
                    </div>
                    <p style="font-size: 13px; font-weight: 700; margin-bottom: 12px;">Budget Breakdown</p>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Personnel (60%)</span>
                            <span style="font-weight: 600;">₱ <?php echo number_format($p['budget_personnel'] ?: $p['budget'] * 0.6); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Equipment (25%)</span>
                            <span style="font-weight: 600;">₱ <?php echo number_format($p['budget_equipment'] ?: $p['budget'] * 0.25); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Materials (10%)</span>
                            <span style="font-weight: 600;">₱ <?php echo number_format($p['budget_materials'] ?: $p['budget'] * 0.1); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Other (5%)</span>
                            <span style="font-weight: 600;">₱ <?php echo number_format($p['budget_other'] ?: $p['budget'] * 0.05); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="view-card">
                    <h3>Quick Actions</h3>
                    <button class="quick-action-btn" onclick="openModal('downloadDocumentsModal')">Download Documents</button>
                    <button class="quick-action-btn" onclick="openModal('contactTeamModal')">Contact Team</button>
                    <button class="quick-action-btn" onclick="openModal('scheduleMeetingModal')">Schedule Meeting</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Download Documents Modal (Image 3) -->
    <div class="modal-overlay" id="downloadDocumentsModal" style="display: none; align-items: center; justify-content: center;">
        <div class="modal-content" style="max-width: 700px; width: 100%; border-radius: 12px; padding: 24px; background: #FFFFFF; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="font-size: 18px; font-weight: 700;">Download Documents – <?php echo htmlspecialchars($p['title']); ?></h2>
                <button onclick="closeModal('downloadDocumentsModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6B7280;">&times;</button>
            </div>
            
            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Project Documents</h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php if(empty($documents)): ?>
                    <p style="color: var(--text-muted); font-size: 14px;">No documents uploaded yet.</p>
                <?php else: ?>
                    <?php foreach($documents as $doc): ?>
                    <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center; background: white;">
                        <div>
                            <p style="font-weight: 600; font-size: 15px; margin-bottom: 4px;"><?php echo htmlspecialchars($doc['file_name']); ?></p>
                            <p style="font-size: 13px; color: var(--text-muted);">Uploaded by <?php echo htmlspecialchars($doc['uploaded_by_name'] ?? 'System'); ?> on <?php echo date('n/j/Y', strtotime($doc['upload_date'])); ?></p>
                            <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">2.4 MB</p>
                        </div>
                        <button class="btn-modal-cancel" style="display: flex; align-items: center; gap: 8px;" onclick="location.href='../<?php echo htmlspecialchars($doc['file_path']); ?>'" download>
                            <i class="fa-solid fa-download"></i> Download
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Contact Team Modal (Image 4) -->
    <div class="modal-overlay" id="contactTeamModal" style="display: none; align-items: center; justify-content: center;">
        <div class="modal-content" style="max-width: 900px; width: 100%; border-radius: 12px; padding: 24px; background: #FFFFFF; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-regular fa-calendar" style="color: #6B7280;"></i> Contact Team – <?php echo htmlspecialchars($p['title']); ?>
                </h2>
                <button onclick="closeModal('contactTeamModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6B7280;">&times;</button>
            </div>
            
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 24px;">
                <!-- Left: Attendees -->
                <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px; background: white;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="font-size: 15px; font-weight: 600;">Select Attendees</h3>
                        <span style="font-size: 13px; color: var(--text-muted); cursor: pointer;">See All</span>
                    </div>
                    <div style="position: relative; margin-bottom: 16px;">
                        <input type="text" placeholder="Search Attendees" class="input-premium" style="padding-right: 32px; background: #F9FAFB;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 12px; top: 12px; color: #9CA3AF;"></i>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px; max-height: 250px; overflow-y: auto; margin-bottom: 16px;">
                        <?php foreach($team as $m): ?>
                        <label style="display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #E5E7EB; border-radius: 8px; cursor: pointer; background: white;" class="contact-attendee-item">
                            <input type="checkbox" name="contact_attendees[]" value="<?php echo htmlspecialchars($m['email']); ?>" style="width: 16px; height: 16px; border-radius: 4px; border: 1px solid #D1D5DB; display: none;">
                            <div style="width: 24px; height: 24px; border-radius: 50%; background: #111827; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span style="font-size: 14px; font-weight: 500; flex: 1;"><?php echo htmlspecialchars($m['full_name']); ?></span>
                            <i class="fa-solid fa-check contact-check-icon" style="color: #2D5BFF; display: none;"></i>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn-modal-cancel" id="contactSelectAll" style="flex: 1; padding: 6px; font-size: 13px;">Select All</button>
                        <button type="button" class="btn-modal-cancel" id="contactClearSelection" style="flex: 1; padding: 6px; font-size: 13px;">Clear Selection</button>
                    </div>
                </div>
                
                <!-- Right: Chat Area -->
                <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; background: white;">
                    <div style="flex: 1; background: #F3F4F6; border-radius: 8px; margin-bottom: 16px; border: 1px solid #E5E7EB;">
                        <!-- Chat history would go here -->
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="text" class="input-premium" style="flex: 1; height: 48px; border-radius: 24px; padding: 0 20px;" placeholder="Type your message...">
                        <button style="width: 48px; height: 48px; border-radius: 50%; background: #2D5BFF; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <i class="fa-solid fa-paper-plane" style="font-size: 18px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Meeting Modal (Image 5) -->
    <div class="modal-overlay" id="scheduleMeetingModal" style="display: none; align-items: center; justify-content: center;">
        <div class="modal-content" style="max-width: 900px; width: 100%; border-radius: 12px; padding: 24px; max-height: 90vh; overflow-y: auto; background: #FFFFFF; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-regular fa-calendar" style="color: #6B7280;"></i> Schedule Meeting – <?php echo htmlspecialchars($p['title']); ?>
                </h2>
                <button onclick="closeModal('scheduleMeetingModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6B7280;">&times;</button>
            </div>
            
            <form action="../meetings/save_meeting.php" method="POST">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 300px; gap: 24px;">
                    <!-- Left: Form Details -->
                    <div>
                        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Meeting Details</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Meeting Title</label>
                                <input type="text" name="title" class="input-premium" placeholder="Enter meeting title..." required>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Meeting Type</label>
                                <div style="position: relative;">
                                    <i class="fa-solid fa-video" style="position: absolute; left: 12px; top: 12px; color: #6B7280;"></i>
                                    <select name="type" class="select-premium" style="padding-left: 36px;" required>
                                        <option value="Video Call">Video Call</option>
                                        <option value="In-person">In-person</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Date</label>
                                <input type="date" name="date" class="input-premium" required>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Time</label>
                                <input type="time" name="time" class="input-premium" required>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Duration (minutes)</label>
                                <select name="duration" class="select-premium" required>
                                    <option value="30">30 minutes</option>
                                    <option value="60">1 hour</option>
                                    <option value="90">1.5 hours</option>
                                    <option value="120">2 hours</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Agenda</label>
                            <textarea name="agenda" class="input-premium" style="height: 100px; padding: 12px;" placeholder="Enter meeting agenda..." required></textarea>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Add additional details</label>
                            <div style="border: 2px dashed #D1D5DB; border-radius: 8px; padding: 32px; text-align: center;">
                                <i class="fa-solid fa-arrow-up-from-bracket" style="font-size: 24px; color: #6B7280; margin-bottom: 16px;"></i>
                                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px;">Drag and drop your proposal document here, or click to browse</p>
                                <button type="button" class="btn-modal-cancel">Choose file</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right: Attendees & Summary -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h3 style="font-size: 15px; font-weight: 600;">Select Attendees</h3>
                                <span style="font-size: 13px; color: var(--text-muted); cursor: pointer;">See All</span>
                            </div>
                            <div style="position: relative; margin-bottom: 16px;">
                                <input type="text" placeholder="Search Attendees" class="input-premium" style="padding-right: 32px; background: #F9FAFB;">
                                <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 12px; top: 12px; color: #9CA3AF;"></i>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 12px; max-height: 200px; overflow-y: auto; margin-bottom: 16px;">
                                <?php foreach($team as $m): ?>
                                <label style="display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #E5E7EB; border-radius: 8px; cursor: pointer;">
                                    <input type="checkbox" name="attendees[]" value="<?php echo htmlspecialchars($m['email']); ?>" class="checkbox-custom">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #111827; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <span style="font-size: 14px; font-weight: 500;"><?php echo htmlspecialchars($m['full_name']); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn-modal-cancel" style="flex: 1; padding: 6px; font-size: 13px;">Select All</button>
                                <button type="button" class="btn-modal-cancel" style="flex: 1; padding: 6px; font-size: 13px;">Clear Selection</button>
                            </div>
                        </div>
                        
                        <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px; background: #F9FAFB;">
                            <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 16px;">Meeting Summary</h3>
                            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                                <p><span style="font-weight: 600;">Attendees:</span> 0 selected</p>
                                <p><span style="font-weight: 600;">Date & Time:</span> Not set</p>
                                <p><span style="font-weight: 600;">Duration:</span> 60 minutes</p>
                                <p><span style="font-weight: 600;">Type:</span> Video Call</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #E5E7EB;">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('scheduleMeetingModal')">Cancel</button>
                    <button type="submit" class="btn-modal-submit" style="background: #1D4ED8;">Schedule Meeting</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ... existing scripts ... -->
    <script>
    function toggleActivity(id, checkbox) {
        const formData = new FormData();
        formData.append('checklist_id', id);
        formData.append('is_completed', checkbox.checked ? 1 : 0);
        formData.append('project_id', <?php echo $project_id; ?>);

        fetch('../projects/update_progress.php', {
            method: 'POST',
            body: formData
        }).then(() => location.reload());
    }

    function postComment() {
        const text = document.getElementById('commentText').value;
        if (!text) return;

        const formData = new FormData();
        formData.append('project_id', <?php echo $project_id; ?>);
        formData.append('content', text);

        fetch('../projects/post_comment.php', {
            method: 'POST',
            body: formData
        }).then(() => location.reload());
    }

    // Schedule Meeting Modal Logic
    document.addEventListener('DOMContentLoaded', () => {
        const scheduleForm = document.querySelector('#scheduleMeetingModal form');
        if (scheduleForm) {
            const attendeesCheckboxes = scheduleForm.querySelectorAll('input[name="attendees[]"]');
            const summaryAttendees = document.querySelector('#scheduleMeetingModal .Meeting-Summary-Attendees');
            const dateInput = scheduleForm.querySelector('input[name="date"]');
            const timeInput = scheduleForm.querySelector('input[name="time"]');
            const typeSelect = scheduleForm.querySelector('select[name="type"]');
            const durationSelect = scheduleForm.querySelector('select[name="duration"]');

            const summaryElements = scheduleForm.querySelectorAll('div[style*="background: #F9FAFB;"] p');

            function updateSummary() {
                const selectedCount = Array.from(attendeesCheckboxes).filter(cb => cb.checked).length;
                if (summaryElements.length >= 4) {
                    summaryElements[0].innerHTML = `<span style="font-weight: 600;">Attendees:</span> ${selectedCount} selected`;
                    const dt = dateInput.value && timeInput.value ? `${dateInput.value} at ${timeInput.value}` : 'Not set';
                    summaryElements[1].innerHTML = `<span style="font-weight: 600;">Date & Time:</span> ${dt}`;
                    const durText = durationSelect.options[durationSelect.selectedIndex].text;
                    summaryElements[2].innerHTML = `<span style="font-weight: 600;">Duration:</span> ${durText}`;
                    summaryElements[3].innerHTML = `<span style="font-weight: 600;">Type:</span> ${typeSelect.value}`;
                }
            }

            attendeesCheckboxes.forEach(cb => cb.addEventListener('change', updateSummary));
            dateInput.addEventListener('change', updateSummary);
            timeInput.addEventListener('change', updateSummary);
            typeSelect.addEventListener('change', updateSummary);
            durationSelect.addEventListener('change', updateSummary);

            // Select All / Clear Selection
            const selectAllBtn = scheduleForm.querySelector('button.btn-modal-cancel:nth-child(1)');
            const clearSelectionBtn = scheduleForm.querySelector('button.btn-modal-cancel:nth-child(2)');

            if(selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    attendeesCheckboxes.forEach(cb => cb.checked = true);
                    updateSummary();
                });
            }
            if(clearSelectionBtn) {
                clearSelectionBtn.addEventListener('click', () => {
                    attendeesCheckboxes.forEach(cb => cb.checked = false);
                    updateSummary();
                });
            }

            // AJAX Form Submission
            scheduleForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const fd = new FormData(scheduleForm);
                fetch('../meetings/save_meeting.php', {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        alert('Meeting successfully scheduled! Attendees have been notified.');
                        closeModal('scheduleMeetingModal');
                        scheduleForm.reset();
                        updateSummary();
                    } else {
                        alert('Error scheduling meeting: ' + data.error);
                    }
                })
                .catch(err => {
                    alert('Error scheduling meeting. Please try again.');
                });
            });
        }

        // Contact Team Modal Logic
        const contactModal = document.getElementById('contactTeamModal');
        if (contactModal) {
            const contactItems = contactModal.querySelectorAll('.contact-attendee-item');
            const selectAllBtn = document.getElementById('contactSelectAll');
            const clearBtn = document.getElementById('contactClearSelection');

            function updateContactUI(item, isChecked) {
                const icon = item.querySelector('.contact-check-icon');
                if (isChecked) {
                    item.style.borderColor = '#2D5BFF';
                    item.style.background = '#EEF2FF';
                    if (icon) icon.style.display = 'block';
                } else {
                    item.style.borderColor = '#E5E7EB';
                    item.style.background = 'white';
                    if (icon) icon.style.display = 'none';
                }
            }

            contactItems.forEach(item => {
                const cb = item.querySelector('input[type="checkbox"]');
                if (cb) {
                    cb.addEventListener('change', () => {
                        updateContactUI(item, cb.checked);
                    });
                }
            });

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', () => {
                    contactItems.forEach(item => {
                        const cb = item.querySelector('input[type="checkbox"]');
                        if (cb) {
                            cb.checked = true;
                            updateContactUI(item, true);
                        }
                    });
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    contactItems.forEach(item => {
                        const cb = item.querySelector('input[type="checkbox"]');
                        if (cb) {
                            cb.checked = false;
                            updateContactUI(item, false);
                        }
                    });
                });
            }

            // Chat Mock Logic
            const chatInput = contactModal.querySelector('input[placeholder="Type your message..."]');
            const sendBtn = contactModal.querySelector('.fa-paper-plane').parentElement;
            const chatArea = contactModal.querySelector('div[style*="background: #F3F4F6"]');

            function sendMessage() {
                const msg = chatInput.value.trim();
                const selected = Array.from(contactModal.querySelectorAll('input[type="checkbox"]:checked'));
                
                if (selected.length === 0) {
                    alert('Please select at least one team member to contact.');
                    return;
                }
                if (!msg) return;

                const msgDiv = document.createElement('div');
                msgDiv.style.background = '#2D5BFF';
                msgDiv.style.color = 'white';
                msgDiv.style.padding = '12px 16px';
                msgDiv.style.borderRadius = '16px';
                msgDiv.style.borderBottomRightRadius = '4px';
                msgDiv.style.alignSelf = 'flex-end';
                msgDiv.style.maxWidth = '80%';
                msgDiv.style.marginBottom = '12px';
                msgDiv.style.fontSize = '14px';
                msgDiv.style.marginLeft = 'auto'; // push to right
                msgDiv.textContent = msg;

                if (!chatArea.style.display || chatArea.style.display === 'block') {
                    chatArea.style.display = 'flex';
                    chatArea.style.flexDirection = 'column';
                    chatArea.style.padding = '16px';
                    chatArea.style.overflowY = 'auto';
                }

                chatArea.appendChild(msgDiv);
                chatArea.scrollTop = chatArea.scrollHeight;
                chatInput.value = '';
            }

            if (sendBtn && chatInput) {
                sendBtn.addEventListener('click', sendMessage);
                chatInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') sendMessage();
                });
            }
        }
    });
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
