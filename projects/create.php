<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

// Fetch departments
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

// Fetch researchers
$researchers = $pdo->query("SELECT user_id, full_name FROM users WHERE role='researcher' AND status='active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Project - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .researcher-select { position: relative; }
        .researcher-dropdown { 
            position: absolute; top: 100%; left: 0; right: 0; 
            background: white; border: 1px solid #E5E7EB; border-radius: 6px; 
            max-height: 200px; overflow-y: auto; z-index: 10; display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .researcher-option { 
            padding: 10px 16px; display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px;
        }
        .researcher-option:hover { background: #F3F4F6; }
        .selected-researchers { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .chip { 
            background: #ECFDF5; color: #059669; padding: 4px 12px; border-radius: 16px; 
            font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 6px;
        }
        .chip i { cursor: pointer; }
        .form-section { background: white; padding: 24px; border-radius: 8px; border: 1px solid #E5E7EB; margin-bottom: 24px; }
        .form-title { font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 20px; border-bottom: 1px solid #F3F4F6; padding-bottom: 12px; }
    </style>
</head>
<body style="background: #F3F4F6;">
    <?php include '../includes/sidebar.php'; ?>
    <div style="margin-left: 240px;">
        <?php include '../includes/header.php'; ?>
    </div>
    
    <main class="main-content">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 24px;">Create New Research Project</h1>
            
            <form action="save_project.php" method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <h3 class="form-title">Basic Information</h3>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Project Title</label>
                        <input type="text" name="title" class="login-input" style="padding-left: 16px;" placeholder="Enter project title" required>
                    </div>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Description</label>
                        <textarea name="description" class="login-input" style="padding: 12px; height: 100px;" placeholder="Describe your research project" required></textarea>
                    </div>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Department</label>
                        <select name="department_id" class="login-input" style="padding-left: 16px;" required>
                            <option value="">Select department</option>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-title">Research Team</h3>
                    <div class="researcher-select">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Add Researchers</label>
                        <input type="text" id="researcher-search" class="login-input" style="padding-left: 16px;" placeholder="Search researcher to add" onfocus="showDropdown()" onblur="hideDropdown()">
                        <div class="researcher-dropdown" id="researcher-dropdown">
                            <?php foreach($researchers as $r): ?>
                            <div class="researcher-option" onmousedown="addResearcher(<?php echo $r['user_id']; ?>, '<?php echo htmlspecialchars($r['full_name']); ?>')">
                                <i class="fa-solid fa-user-plus" style="color: #9CA3AF;"></i>
                                <?php echo htmlspecialchars($r['full_name']); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="selected-researchers" id="selected-researchers">
                        <!-- Chips go here -->
                    </div>
                    <div id="hidden-inputs"></div>
                </div>

                <div class="form-section">
                    <h3 class="form-title">Timeline & Budget</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Start Date</label>
                            <input type="date" name="start_date" class="login-input" style="padding-left: 16px;" required>
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">End Date</label>
                            <input type="date" name="end_date" class="login-input" style="padding-left: 16px;" required>
                        </div>
                    </div>
                    <div class="login-input-group">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Budget Requested (₱)</label>
                        <input type="number" name="budget" class="login-input" style="padding-left: 16px;" placeholder="0.00" required>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
                    <button type="button" class="btn-edit" onclick="history.back()">Cancel</button>
                    <button type="submit" class="btn-view" style="padding: 12px 32px;">Submit Proposal</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const selected = new Set();
        
        function showDropdown() {
            document.getElementById('researcher-dropdown').style.display = 'block';
        }
        
        function hideDropdown() {
            setTimeout(() => {
                document.getElementById('researcher-dropdown').style.display = 'none';
            }, 200);
        }

        function addResearcher(id, name) {
            if (selected.has(id)) return;
            selected.add(id);
            
            const chip = document.createElement('div');
            chip.className = 'chip';
            chip.id = `chip-${id}`;
            chip.innerHTML = `${name} <i class="fa-solid fa-xmark" onclick="removeResearcher(${id})"></i>`;
            document.getElementById('selected-researchers').appendChild(chip);
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'team_members[]';
            input.value = id;
            input.id = `input-${id}`;
            document.getElementById('hidden-inputs').appendChild(input);
            
            document.getElementById('researcher-search').value = '';
        }

        function removeResearcher(id) {
            selected.delete(id);
            document.getElementById(`chip-${id}`).remove();
            document.getElementById(`input-${id}`).remove();
        }

        // Filter researchers
        document.getElementById('researcher-search').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.researcher-option').forEach(opt => {
                const text = opt.textContent.toLowerCase();
                opt.style.display = text.includes(query) ? 'flex' : 'none';
            });
        });
    </script>
</body>
</html>
