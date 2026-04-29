<?php
// Fetch departments and researchers for modals
require_once __DIR__ . '/../config/database.php';
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();
$researchers = $pdo->query("SELECT user_id, full_name FROM users WHERE role='researcher' AND status='active'")->fetchAll();
$coordinators = $pdo->query("SELECT user_id, full_name FROM users WHERE role='coordinator' AND status='active'")->fetchAll();
?>

<!-- New Project Modal (Image 5) -->
<div class="modal-overlay" id="newProjectModal" style="display: none;">
    <div class="modal-content-premium">
        <div class="modal-header-premium">
            <div class="modal-title-group">
                <h2>Create New Research Project</h2>
                <p>Fill in the details for your new research project</p>
            </div>
            <button class="close-modal-btn" onclick="closeModal('newProjectModal')">&times;</button>
        </div>
        <form id="newProjectForm" action="projects/save_project.php" method="POST" enctype="multipart/form-data">
            <div class="modal-body-premium">
                <div class="form-section-premium">
                    <h3>Basic Information</h3>
                    <div class="form-group-premium">
                        <label>Project Title</label>
                        <input type="text" name="title" class="input-premium" placeholder="Enter project title" required>
                    </div>
                    <div class="form-group-premium">
                        <label>Description</label>
                        <textarea name="description" class="input-premium" style="height: 100px;" placeholder="Describe your research project" required></textarea>
                    </div>
                    <div class="form-group-premium">
                        <label>Department</label>
                        <select name="department_id" class="select-premium" required>
                            <option value="">Select department</option>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section-premium">
                    <h3>Research Team</h3>
                    <div class="form-group-premium">
                        <label>Add Researchers</label>
                        <select id="new-researcher-select" class="select-premium" onchange="addResearcherToNewProject()">
                            <option value="">Select researcher to add</option>
                            <?php foreach($researchers as $r): ?>
                                <option value="<?php echo $r['user_id']; ?>" data-name="<?php echo htmlspecialchars($r['full_name']); ?>">
                                    <?php echo htmlspecialchars($r['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="team-pills" id="new-project-team-pills">
                        <!-- Chips go here -->
                    </div>
                    <div id="new-project-hidden-team"></div>
                </div>

                <div class="form-section-premium">
                    <h3>Project Timeline</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group-premium">
                            <label>Start Date</label>
                            <div style="position: relative;">
                                <i class="fa-regular fa-calendar" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                                <input type="date" name="start_date" class="input-premium" style="padding-left: 36px;" required>
                            </div>
                        </div>
                        <div class="form-group-premium">
                            <label>End Date</label>
                            <div style="position: relative;">
                                <i class="fa-regular fa-calendar" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                                <input type="date" name="end_date" class="input-premium" style="padding-left: 36px;" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group-premium">
                        <label>Project Activities</label>
                        <div id="new-activities-list">
                            <div style="display: flex; gap: 10px; margin-bottom: 8px;">
                                <input type="text" name="activities[]" class="input-premium" placeholder="Activity name" style="flex: 1;">
                                <input type="number" name="activity_weeks[]" class="input-premium" value="1" style="width: 80px;">
                            </div>
                        </div>
                        <button type="button" class="btn-card-action" style="width: 100%; justify-content: center; margin-top: 8px; border: 1px solid #D1D5DB; background: white;" onclick="addActivityField('new-activities-list')">
                            <i class="fa-solid fa-plus"></i> Add Activity
                        </button>
                    </div>
                </div>

                <div class="form-section-premium">
                    <h3>Budget & Documents</h3>
                    <div class="form-group-premium">
                        <label>Budget Requested</label>
                        <input type="number" name="budget" class="input-premium" placeholder="Enter budget amount" required>
                    </div>
                    <div class="form-group-premium">
                        <label>Upload Proposals & Documents</label>
                        <div style="border: 2px dashed #D1D5DB; border-radius: 12px; padding: 32px; text-align: center; cursor: pointer;" onclick="document.getElementById('proposalFile').click()">
                            <i class="fa-solid fa-upload" style="font-size: 32px; color: #9CA3AF; margin-bottom: 12px;"></i>
                            <p style="font-size: 14px; color: #6B7280; margin-bottom: 16px;">Drag and drop your documents here, or click to browse</p>
                            <button type="button" class="btn-modal-cancel" style="background: white; border-color: #D1D5DB; color: #111827;">Choose Files</button>
                            <input type="file" id="proposalFile" name="documents[]" multiple style="display: none;" onchange="updateFileNames(this, 'new-file-names')">
                        </div>
                        <div id="new-file-names" style="margin-top: 8px; font-size: 13px; color: #4B5563;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-premium">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('newProjectModal')">Cancel</button>
                <button type="submit" class="btn-modal-submit">Submit Proposal</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Project Modal (Image 2) -->
<div class="modal-overlay" id="editProjectModal" style="display: none;">
    <div class="modal-content-premium">
        <div class="modal-header-premium">
            <div class="modal-title-group">
                <h2>Edit Project</h2>
            </div>
            <button class="close-modal-btn" onclick="closeModal('editProjectModal')">&times;</button>
        </div>
        <form id="editProjectForm" action="projects/update_project.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="project_id" id="edit-project-id">
            <div class="modal-body-premium">
                <div class="form-group-premium">
                    <label>Project Title</label>
                    <input type="text" name="title" id="edit-title" class="input-premium" required>
                </div>
                <div class="form-group-premium">
                    <label>Description</label>
                    <textarea name="description" id="edit-description" class="input-premium" style="height: 100px;" required></textarea>
                </div>
                <div class="form-group-premium">
                    <label>Department</label>
                    <select name="department_id" id="edit-department" class="select-premium" required>
                        <?php foreach($departments as $d): ?>
                            <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group-premium">
                    <label>Researcher</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="edit-researcher-search" class="input-premium" placeholder="Add Researcher name" style="flex: 1;">
                        <button type="button" class="btn-modal-submit" style="padding: 8px 24px;" onclick="addResearcherToEditProject()">Add</button>
                    </div>
                    <div class="team-pills" id="edit-project-team-pills" style="margin-top: 12px;">
                        <!-- Chips go here -->
                    </div>
                    <div id="edit-project-hidden-team"></div>
                </div>

                <div style="display: flex; gap: 12px; margin: 24px 0;">
                    <button type="button" class="btn-modal-submit" style="flex: 1; background: #2D5BFF;" onclick="addActivityRow()">Add Activity</button>
                    <button type="button" class="btn-modal-submit" style="flex: 1; background: #2D5BFF;">Add Week</button>
                </div>

                <div class="timeline-table-container">
                    <table class="timeline-table" id="edit-timeline-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Activity</th>
                                <th>Week 2</th>
                                <th>Week 3</th>
                                <th>Week 4</th>
                                <th>Week 5</th>
                                <th>Week 6</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows go here -->
                        </tbody>
                    </table>
                    <div style="padding: 12px; border-top: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
                        <input type="text" id="new-activity-row-name" class="input-premium" placeholder="Enter activity" style="flex: 1;">
                        <input type="checkbox" class="checkbox-custom">
                    </div>
                </div>

                <div class="form-section-premium" style="margin-top: 24px; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
                    <h3 style="margin-bottom: 12px;">Budget</h3>
                    <div class="budget-breakdown-row">
                        <span>Total Budget</span>
                        <input type="text" id="edit-total-budget-display" class="input-premium" style="width: 140px; text-align: right;" value="₱ 0" readonly>
                        <input type="hidden" name="budget" id="edit-total-budget-hidden">
                    </div>
                    <p style="font-size: 13px; font-weight: 700; margin: 16px 0 12px 0;">Budget Breakdown</p>
                    <div class="budget-breakdown-row" style="margin-bottom: 12px;">
                        <input type="text" value="Personnel" class="input-premium" style="width: 55%;" readonly>
                        <div style="position: relative; width: 40%;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600;">₱</span>
                            <input type="number" name="budget_personnel" id="edit-budget-personnel" class="input-premium" style="padding-left: 28px; text-align: right;" oninput="calcTotalBudget()">
                        </div>
                    </div>
                    <div class="budget-breakdown-row" style="margin-bottom: 12px;">
                        <input type="text" value="Equipment" class="input-premium" style="width: 55%;" readonly>
                        <div style="position: relative; width: 40%;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600;">₱</span>
                            <input type="number" name="budget_equipment" id="edit-budget-equipment" class="input-premium" style="padding-left: 28px; text-align: right;" oninput="calcTotalBudget()">
                        </div>
                    </div>
                    <div class="budget-breakdown-row" style="margin-bottom: 12px;">
                        <input type="text" value="Materials" class="input-premium" style="width: 55%;" readonly>
                        <div style="position: relative; width: 40%;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600;">₱</span>
                            <input type="number" name="budget_materials" id="edit-budget-materials" class="input-premium" style="padding-left: 28px; text-align: right;" oninput="calcTotalBudget()">
                        </div>
                    </div>
                    <div class="budget-breakdown-row" style="margin-bottom: 12px;">
                        <input type="text" value="Other" class="input-premium" style="width: 55%;" readonly>
                        <div style="position: relative; width: 40%;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600;">₱</span>
                            <input type="number" name="budget_other" id="edit-budget-other" class="input-premium" style="padding-left: 28px; text-align: right;" oninput="calcTotalBudget()">
                        </div>
                    </div>
                </div>

                <div class="form-group-premium" style="margin-top: 24px;">
                    <label>Coordinator</label>
                    <select name="coordinator_id" id="edit-coordinator" class="select-premium">
                        <option value="">Select Coordinator</option>
                        <?php foreach($coordinators as $c): ?>
                            <option value="<?php echo $c['user_id']; ?>"><?php echo htmlspecialchars($c['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-section-premium" style="margin-top: 24px; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
                    <h3 style="margin-bottom: 12px;">Documents</h3>
                    <div class="form-group-premium">
                        <label>Upload additional files</label>
                        <div style="border: 2px dashed #D1D5DB; border-radius: 12px; padding: 32px; text-align: center; cursor: pointer;" onclick="document.getElementById('editProposalFile').click()">
                            <i class="fa-solid fa-upload" style="font-size: 32px; color: #9CA3AF; margin-bottom: 12px;"></i>
                            <p style="font-size: 14px; color: #6B7280; margin-bottom: 16px;">Drag and drop your documents here, or click to browse</p>
                            <button type="button" class="btn-modal-cancel" style="background: white; border-color: #D1D5DB; color: #111827;">Choose Files</button>
                            <input type="file" id="editProposalFile" name="documents[]" multiple style="display: none;" onchange="updateFileNames(this, 'edit-file-names')">
                        </div>
                        <div id="edit-file-names" style="margin-top: 8px; font-size: 13px; color: #4B5563;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-premium">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('editProjectModal')">Cancel</button>
                <button type="submit" class="btn-modal-submit" style="background: #2D5BFF;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
let newProjectTeam = new Set();
let editProjectTeam = new Set();

function openNewProjectModal() {
    document.getElementById('newProjectModal').style.display = 'flex';
}

function openEditModal(projectId) {
    fetch(`projects/get_project_data.php?id=${projectId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit-project-id').value = data.project_id;
            document.getElementById('edit-title').value = data.title;
            document.getElementById('edit-description').value = data.description;
            document.getElementById('edit-department').value = data.department_id;
            document.getElementById('edit-coordinator').value = data.coordinator_id;
            document.getElementById('edit-budget-personnel').value = data.budget_personnel;
            document.getElementById('edit-budget-equipment').value = data.budget_equipment;
            document.getElementById('edit-budget-materials').value = data.budget_materials;
            document.getElementById('edit-budget-other').value = data.budget_other;
            calcTotalBudget();

            // Clear team pills
            const teamContainer = document.getElementById('edit-project-team-pills');
            const hiddenContainer = document.getElementById('edit-project-hidden-team');
            teamContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';
            editProjectTeam.clear();

            data.team.forEach(m => {
                addResearcherToEditList(m.user_id, m.full_name);
            });

            // Populate activities
            const tbody = document.getElementById('edit-timeline-table').querySelector('tbody');
            tbody.innerHTML = '';
            data.activities.forEach((a, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="text" name="activity_names[${index}]" value="${a.activity_name}" class="input-premium"></td>
                    <td><input type="checkbox" name="activity_weeks[${index}][]" value="2" class="checkbox-custom" ${a.week2 ? 'checked' : ''}></td>
                    <td><input type="checkbox" name="activity_weeks[${index}][]" value="3" class="checkbox-custom" ${a.week3 ? 'checked' : ''}></td>
                    <td><input type="checkbox" name="activity_weeks[${index}][]" value="4" class="checkbox-custom" ${a.week4 ? 'checked' : ''}></td>
                    <td><input type="checkbox" name="activity_weeks[${index}][]" value="5" class="checkbox-custom" ${a.week5 ? 'checked' : ''}></td>
                    <td><input type="checkbox" name="activity_weeks[${index}][]" value="6" class="checkbox-custom" ${a.week6 ? 'checked' : ''}></td>
                `;
                tbody.appendChild(row);
            });

            document.getElementById('editProjectModal').style.display = 'flex';
        });
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function addResearcherToNewProject() {
    const select = document.getElementById('new-researcher-select');
    const id = select.value;
    const name = select.options[select.selectedIndex].getAttribute('data-name');
    if (id && !newProjectTeam.has(id)) {
        newProjectTeam.add(id);
        const pill = document.createElement('div');
        pill.className = 'team-pill';
        pill.innerHTML = `${name} <i class="fa-solid fa-xmark" style="cursor:pointer; margin-left:8px;" onclick="removeResearcherFromNew('${id}', this)"></i>`;
        document.getElementById('new-project-team-pills').appendChild(pill);
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'team_members[]';
        input.value = id;
        document.getElementById('new-project-hidden-team').appendChild(input);
    }
    select.value = '';
}

function removeResearcherFromNew(id, icon) {
    newProjectTeam.delete(id);
    icon.parentElement.remove();
    const inputs = document.getElementById('new-project-hidden-team').querySelectorAll('input');
    inputs.forEach(input => {
        if (input.value == id) input.remove();
    });
}

function addResearcherToEditProject() {
    const searchInput = document.getElementById('edit-researcher-search');
    const query = searchInput.value.toLowerCase();
    if (!query) return;

    // Find researcher by name in the researchers list (from PHP)
    const researchers = <?php echo json_encode($researchers); ?>;
    const found = researchers.find(r => r.full_name.toLowerCase().includes(query));
    
    if (found) {
        addResearcherToEditList(found.user_id, found.full_name);
        searchInput.value = '';
    } else {
        alert('Researcher not found');
    }
}

function addResearcherToEditList(id, name) {
    if (editProjectTeam.has(id)) return;
    editProjectTeam.add(id);
    const pill = document.createElement('div');
    pill.className = 'team-pill';
    pill.innerHTML = `${name} <i class="fa-solid fa-xmark" style="cursor:pointer; margin-left:8px;" onclick="removeResearcherFromEdit('${id}', this)"></i>`;
    document.getElementById('edit-project-team-pills').appendChild(pill);
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'team_members[]';
    input.value = id;
    document.getElementById('edit-project-hidden-team').appendChild(input);
}

function removeResearcherFromEdit(id, icon) {
    editProjectTeam.delete(id);
    icon.parentElement.remove();
    const inputs = document.getElementById('edit-project-hidden-team').querySelectorAll('input');
    inputs.forEach(input => {
        if (input.value == id) input.remove();
    });
}

function addActivityField(containerId) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.style.display = 'flex';
    div.style.gap = '10px';
    div.style.marginBottom = '8px';
    div.innerHTML = `
        <input type="text" name="activities[]" class="input-premium" placeholder="Activity name" style="flex: 1;">
        <input type="number" name="activity_weeks[]" class="input-premium" value="1" style="width: 80px;">
    `;
    container.appendChild(div);
}

function calcTotalBudget() {
    const p = parseFloat(document.getElementById('edit-budget-personnel').value) || 0;
    const e = parseFloat(document.getElementById('edit-budget-equipment').value) || 0;
    const m = parseFloat(document.getElementById('edit-budget-materials').value) || 0;
    const o = parseFloat(document.getElementById('edit-budget-other').value) || 0;
    const total = p + e + m + o;
    document.getElementById('edit-total-budget-display').value = '₱ ' + total.toLocaleString();
    document.getElementById('edit-total-budget-hidden').value = total;
}

function addActivityRow() {
    const tbody = document.getElementById('edit-timeline-table').querySelector('tbody');
    const index = tbody.rows.length;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" name="activity_names[${index}]" class="input-premium" placeholder="Enter activity"></td>
        <td><input type="checkbox" name="activity_weeks[${index}][]" value="2" class="checkbox-custom"></td>
        <td><input type="checkbox" name="activity_weeks[${index}][]" value="3" class="checkbox-custom"></td>
        <td><input type="checkbox" name="activity_weeks[${index}][]" value="4" class="checkbox-custom"></td>
        <td><input type="checkbox" name="activity_weeks[${index}][]" value="5" class="checkbox-custom"></td>
        <td><input type="checkbox" name="activity_weeks[${index}][]" value="6" class="checkbox-custom"></td>
    `;
    tbody.appendChild(row);
}

function updateFileNames(input, displayId) {
    const displayElement = document.getElementById(displayId);
    if (input.files.length > 0) {
        let names = [];
        for (let i = 0; i < input.files.length; i++) {
            names.push(input.files[i].name);
        }
        displayElement.innerText = names.join(', ');
    } else {
        displayElement.innerText = '';
    }
}
</script>
