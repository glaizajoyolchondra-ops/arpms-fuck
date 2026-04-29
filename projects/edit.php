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
$stmt = $pdo->prepare("SELECT * FROM research_projects WHERE project_id = ?");
$stmt->execute([$project_id]);
$p = $stmt->fetch();

// Fetch departments
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

// Fetch current team
$team_stmt = $pdo->prepare("SELECT u.user_id, u.full_name FROM users u JOIN project_team pt ON u.user_id = pt.user_id WHERE pt.project_id = ?");
$team_stmt->execute([$project_id]);
$current_team = $team_stmt->fetchAll();

// Fetch all researchers for dropdown
$researchers = $pdo->query("SELECT user_id, full_name FROM users WHERE role='researcher' AND status='active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-section { background: white; padding: 24px; border-radius: 8px; border: 1px solid #E5E7EB; margin-bottom: 24px; }
        .form-title { font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 20px; border-bottom: 1px solid #F3F4F6; padding-bottom: 12px; }
        .chip { background: #ECFDF5; color: #059669; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .chip i { cursor: pointer; }
        .selected-researchers { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    </style>
</head>
<body style="background: #F3F4F6;">
    <?php include '../includes/sidebar.php'; ?>
    <div style="margin-left: 240px;">
        <?php include '../includes/header.php'; ?>
    </div>
    
    <main class="main-content">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 24px;">Edit Research Project</h1>
            
            <form action="update_project.php" method="POST">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                
                <div class="form-section">
                    <h3 class="form-title">Basic Information</h3>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Project Title</label>
                        <input type="text" name="title" class="login-input" style="padding-left: 16px;" value="<?php echo htmlspecialchars($p['title']); ?>" required>
                    </div>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Description</label>
                        <textarea name="description" class="login-input" style="padding: 12px; height: 100px;" required><?php echo htmlspecialchars($p['description']); ?></textarea>
                    </div>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Department</label>
                        <select name="department_id" class="login-input" style="padding-left: 16px;" required>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>" <?php echo ($d['department_id'] == $p['department_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-title">Research Team</h3>
                    <div style="display: flex; gap: 10px;">
                        <select id="researcher-add" class="login-input" style="padding-left: 16px; flex: 1;">
                            <option value="">Select researcher to add...</option>
                            <?php foreach($researchers as $r): ?>
                                <option value="<?php echo $r['user_id']; ?>" data-name="<?php echo htmlspecialchars($r['full_name']); ?>">
                                    <?php echo htmlspecialchars($r['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn-view" onclick="addResearcher()">Add</button>
                    </div>
                    <div class="selected-researchers" id="selected-researchers">
                        <?php foreach($current_team as $m): ?>
                        <div class="chip" id="chip-<?php echo $m['user_id']; ?>">
                            <?php echo htmlspecialchars($m['full_name']); ?>
                            <i class="fa-solid fa-xmark" onclick="removeResearcher(<?php echo $m['user_id']; ?>)"></i>
                            <input type="hidden" name="team_members[]" value="<?php echo $m['user_id']; ?>" id="input-<?php echo $m['user_id']; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="hidden-inputs"></div>
                </div>

                <div class="form-section">
                    <h3 class="form-title">Timeline & Budget</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Start Date</label>
                            <input type="date" name="start_date" class="login-input" style="padding-left: 16px;" value="<?php echo $p['start_date']; ?>" required>
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">End Date</label>
                            <input type="date" name="end_date" class="login-input" style="padding-left: 16px;" value="<?php echo $p['end_date']; ?>" required>
                        </div>
                    </div>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Budget (₱)</label>
                        <input type="number" name="budget" class="login-input" style="padding-left: 16px;" value="<?php echo $p['budget']; ?>" required>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
                    <button type="button" class="btn-edit" onclick="history.back()">Cancel</button>
                    <button type="submit" name="save_changes" class="btn-view" style="padding: 12px 32px;">Save Changes</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const selected = new Set(<?php echo json_encode(array_column($current_team, 'user_id')); ?>);
        
        function addResearcher() {
            const select = document.getElementById('researcher-add');
            const id = parseInt(select.value);
            if (!id || selected.has(id)) return;
            
            const name = select.options[select.selectedIndex].getAttribute('data-name');
            selected.add(id);
            
            const chip = document.createElement('div');
            chip.className = 'chip';
            chip.id = `chip-${id}`;
            chip.innerHTML = `${name} <i class="fa-solid fa-xmark" onclick="removeResearcher(${id})"></i><input type="hidden" name="team_members[]" value="${id}" id="input-${id}">`;
            document.getElementById('selected-researchers').appendChild(chip);
            select.value = '';
        }

        function removeResearcher(id) {
            selected.delete(id);
            const chip = document.getElementById(`chip-${id}`);
            if (chip) chip.remove();
            const input = document.getElementById(`input-${id}`);
            if (input) input.remove();
        }
    </script>
</body>
</html>
