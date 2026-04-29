<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch users
$query = "SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id ORDER BY u.created_at DESC";
$users = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #E5E7EB;">
    <?php include '../includes/header.php'; ?>

    <div class="modal-overlay" style="display: flex;">
        <div class="modal-content-premium" style="width: 800px; padding: 24px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="font-size: 18px; font-weight: 700;">User Management</h2>
                <a href="../dashboard.php" style="text-decoration: none; color: #111827; font-size: 24px;">&times;</a>
            </div>

            <div style="display: flex; gap: 16px; margin-bottom: 24px;">
                <div style="flex: 1; position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #6B7280;"></i>
                    <input type="text" id="userSearchInput" class="input-premium" placeholder="Search Users..." style="padding-left: 48px; height: 48px; border-radius: 8px;">
                </div>
                <div style="width: 200px;">
                    <select id="roleFilterSelect" class="select-premium" style="height: 48px; border-radius: 8px;">
                        <option value="all">All Users</option>
                        <option value="admin">Admin</option>
                        <option value="coordinator">Coordinator</option>
                        <option value="researcher">Researcher</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>

            <div id="usersList" style="display: flex; flex-direction: column; gap: 12px; max-height: 500px; overflow-y: auto; padding-right: 8px;">
                <?php foreach($users as $user): ?>
                    <div class="user-item" data-name="<?php echo strtolower(htmlspecialchars($user['full_name'])); ?>" data-email="<?php echo strtolower(htmlspecialchars($user['email'])); ?>" data-role="<?php echo strtolower($user['role']); ?>" data-status="<?php echo strtolower($user['status']); ?>" style="display: flex; align-items: center; padding: 16px; border: 1px solid #E5E7EB; border-radius: 12px; background: white;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #2D5BFF; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 16px;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 15px; font-weight: 700; color: #111827;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div style="font-size: 13px; color: #6B7280;"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div style="margin: 0 24px;">
                            <?php 
                            $role_display = '';
                            if($user['role'] === 'coordinator') $role_display = 'Coordinator';
                            else if($user['role'] === 'researcher') $role_display = 'Researcher';
                            else $role_display = 'Admin';
                            ?>
                            <span class="role-badge" style="padding: 4px 16px; border: 1px solid #111827; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                <?php echo $role_display; ?>
                            </span>
                        </div>
                        <?php if($user['status'] === 'pending'): ?>
                            <div style="margin-right: 16px;">
                                <button onclick="approveUser(<?php echo $user['user_id']; ?>)" style="background: #22C55E; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: 600;">Approve</button>
                            </div>
                        <?php endif; ?>
                        <div style="cursor: pointer; font-size: 18px; color: #111827;" onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo $user['role']; ?>')">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="editRoleModal" class="modal-overlay" style="display: none; z-index: 1000;">
        <div class="modal-content-premium" style="width: 400px; padding: 24px; border-radius: 12px;">
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 20px;">Edit User Role</h2>
            <input type="hidden" id="edit-user-id">
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600;">Select Role</label>
                <select id="edit-user-role" class="select-premium" style="width: 100%;">
                    <option value="admin">Admin</option>
                    <option value="coordinator">Coordinator</option>
                    <option value="researcher">Researcher</option>
                </select>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button onclick="closeEditModal()" class="btn-modal-cancel">Cancel</button>
                <button onclick="saveUserRole()" class="btn-modal-submit" style="background: #2D5BFF; color: white;">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
        // Search and Filter Logic
        const searchInput = document.getElementById('userSearchInput');
        const roleFilter = document.getElementById('roleFilterSelect');

        function filterUsers() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filterValue = roleFilter.value.toLowerCase();
            const userItems = document.querySelectorAll('.user-item');

            userItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const email = item.getAttribute('data-email');
                const role = item.getAttribute('data-role');
                const status = item.getAttribute('data-status');

                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                let matchesFilter = true;

                if (filterValue !== 'all') {
                    if (filterValue === 'pending') {
                        matchesFilter = status === 'pending';
                    } else {
                        matchesFilter = role === filterValue;
                    }
                }

                if (matchesSearch && matchesFilter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterUsers);
        roleFilter.addEventListener('change', filterUsers);

        function approveUser(userId) {
            if(confirm('Approve this user account?')) {
                fetch('approve_user.php?id=' + userId)
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) location.reload();
                    });
            }
        }
        
        function editUser(userId, currentRole) {
            document.getElementById('edit-user-id').value = userId;
            document.getElementById('edit-user-role').value = currentRole.toLowerCase();
            document.getElementById('editRoleModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editRoleModal').style.display = 'none';
        }

        function saveUserRole() {
            const userId = document.getElementById('edit-user-id').value;
            const role = document.getElementById('edit-user-role').value;

            if (!userId || !role) {
                alert('Invalid data');
                return;
            }

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('role', role);

            fetch('update_role.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating role: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred.');
            });
        }
    </script>
</body>
</html>
