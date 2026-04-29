<?php
$full_name = $_SESSION['full_name'] ?? 'Emily Davis';
$role = $_SESSION['role'] ?? 'admin';
$initials = strtoupper(substr($full_name, 0, 1));

// Fetch Notifications for Header
$notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notif_stmt->execute([$_SESSION['user_id']]);
$notifications = $notif_stmt->fetchAll();
$unread_count = count(array_filter($notifications, function($n) { return !$n['is_read']; }));
?>
<header class="header">
    <div class="logo-section">
        <i class="fa-solid fa-graduation-cap"></i>
        <h2>ARPMS</h2>
    </div>

    <button class="new-project-btn-header" onclick="openNewProjectModal()">
        <i class="fa-solid fa-plus"></i>
        New Project
    </button>

    <div class="filter-btn-header" id="filterBtn">
        <i class="fa-solid fa-filter" style="color: #6B7280;"></i>
    </div>

    <div class="search-container-header">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" class="search-input-header" placeholder="Search project, researchers...">
    </div>

    <div class="header-right-actions">
        <div class="notif-btn-wrapper" id="notifBtn">
            <i class="fa-regular fa-bell" style="font-size: 24px; color: #6B7280;"></i>
            <?php if ($unread_count > 0): ?>
                <span class="notif-badge-blue"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </div>

        <div class="user-profile-header" id="profileBtn">
            <div class="user-avatar-blue">
                <i class="fa-solid fa-circle-user"></i>
            </div>
            <div class="user-info-header">
                <span class="user-name-header"><?php echo htmlspecialchars($full_name); ?></span>
                <span class="user-role-header"><?php echo ucfirst($role); ?></span>
            </div>
        </div>
    </div>

    <!-- Notification Dropdown -->
    <div class="dropdown-menu-premium" id="notifDropdown" style="right: 80px; width: 320px; padding: 0;">
        <div style="padding: 16px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h4 style="font-size: 14px; font-weight: 700; margin: 0;">Notifications <span style="background: #EEF2FF; color: #2D5BFF; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;"><?php echo $unread_count; ?> New</span></h4>
            <a href="#" style="font-size: 12px; color: var(--primary-blue); text-decoration: none;">Mark all as read</a>
        </div>
        <div style="max-height: 300px; overflow-y: auto;">
            <?php if(empty($notifications)): ?>
                <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 13px;">No new notifications</div>
            <?php else: ?>
                <?php 
                $is_subdir = (strpos($_SERVER['PHP_SELF'], 'projects/') !== false || strpos($_SERVER['PHP_SELF'], 'users/') !== false || strpos($_SERVER['PHP_SELF'], 'auth/') !== false);
                $path_prefix = $is_subdir ? '../' : '';
                ?>
                <?php foreach($notifications as $n): ?>
                <?php 
                    $link = $n['link'] ?? '#';
                    if ($link !== '#' && !str_starts_with($link, 'http')) {
                        $link = $path_prefix . $link;
                    }
                ?>
                <div style="padding: 12px 16px; border-bottom: 1px solid #F3F4F6; cursor: pointer; <?php echo !$n['is_read'] ? 'background: #F9FAFB;' : ''; ?>" onclick="location.href='<?php echo $link; ?>'">
                    <p style="font-size: 13px; margin-bottom: 4px; font-weight: <?php echo !$n['is_read'] ? '600' : '400'; ?>; color: #111827;"><?php echo htmlspecialchars($n['message']); ?></p>
                    <span style="font-size: 11px; color: var(--text-muted);"><?php echo date('M j, g:i A', strtotime($n['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Dropdown -->
    <div class="dropdown-menu-premium" id="filterDropdown" style="left: 300px; top: 60px; width: 280px; padding: 16px;">
        <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 12px;">Filter Projects</h4>
        <div style="margin-bottom: 12px;">
            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; color: var(--text-muted);">Status</label>
            <select class="select-premium" style="width: 100%; padding: 8px;">
                <option value="">All Statuses</option>
                <option value="on_track">On Track</option>
                <option value="in_progress">In Progress</option>
                <option value="delayed">Delayed</option>
            </select>
        </div>
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; color: var(--text-muted);">Department</label>
            <select class="select-premium" style="width: 100%; padding: 8px;">
                <option value="">All Departments</option>
                <option value="1">College of Science</option>
                <option value="2">Engineering</option>
            </select>
        </div>
        <button class="btn-modal-submit" style="width: 100%; padding: 8px; background: #2D5BFF;">Apply Filters</button>
    </div>

    <!-- Account Dropdown (Image 1 & 2) -->
    <div class="dropdown-menu-premium" id="profileDropdown" style="right: 24px; width: 280px; padding: 0;">
        <?php
            // Fetch detailed user info for dropdown
            $user_info_stmt = $pdo->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = ?");
            $user_info_stmt->execute([$_SESSION['user_id']]);
            $u_info = $user_info_stmt->fetch();
            $dept = $u_info['department_name'] ?? 'College of Science';
        ?>
        <div style="padding: 16px; border-bottom: 1px solid var(--border-color);">
            <div style="font-size: 14px; font-weight: 700; color: #111827;"><?php echo htmlspecialchars($u_info['full_name']); ?></div>
            <div style="font-size: 13px; color: #4B5563; margin-top: 2px;"><?php echo htmlspecialchars($u_info['email']); ?></div>
            <div style="font-size: 13px; color: #4B5563; margin-top: 2px;"><?php echo htmlspecialchars($dept); ?></div>
        </div>
        <div style="padding: 8px 0;">
            <a href="users/profile.php" class="dropdown-item-premium">
                <i class="fa-regular fa-user" style="width: 20px;"></i> Profile
            </a>
            <a href="settings.php" class="dropdown-item-premium">
                <i class="fa-solid fa-gear" style="width: 20px;"></i> Settings
            </a>
            <?php if($role === 'admin'): ?>
            <a href="users/manage.php" class="dropdown-item-premium">
                <i class="fa-solid fa-users-gear" style="width: 20px;"></i> Manage Users
            </a>
            <?php endif; ?>
            <?php if($role === 'admin' || $role === 'coordinator'): ?>
            <?php $projects_path = (strpos($_SERVER['PHP_SELF'], 'projects/') !== false || strpos($_SERVER['PHP_SELF'], 'users/') !== false) ? '../projects/index.php' : 'projects/index.php'; ?>
            <a href="<?php echo $projects_path; ?>" class="dropdown-item-premium">
                <i class="fa-regular fa-folder" style="width: 20px;"></i> Projects
            </a>
            <?php endif; ?>
            <a href="teams.php" class="dropdown-item-premium">
                <i class="fa-solid fa-users" style="width: 20px;"></i> Teams
            </a>
        </div>
        <div style="border-top: 1px solid var(--border-color); padding: 8px 0;">
            <a href="auth/logout.php" class="dropdown-item-premium">
                <i class="fa-solid fa-right-from-bracket" style="width: 20px;"></i> Log Out
            </a>
        </div>
    </div>
</header>
