<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'researcher';
?>
<div class="sidebar">
    <div class="logo-section" style="padding: 24px; color: white;">
        <i class="fa-solid fa-graduation-cap"></i>
        <h2 style="color: white;">ARPMS</h2>
    </div>
    
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="teams.php" class="menu-item <?php echo $current_page == 'teams.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-users"></i>
            <span>Teams</span>
        </a>

        <a href="projects/list.php" class="menu-item <?php echo $current_page == 'list.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-lines"></i>
            <span>Projects</span>
        </a>
        
        <a href="users/profile.php" class="menu-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-user"></i>
            <span>Profile</span>
        </a>
        
        <a href="settings.php" class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
        </a>
        
        <?php if ($role === 'admin'): ?>
        <a href="users/manage.php" class="menu-item">
            <i class="fa-solid fa-user-shield"></i>
            <span>Admin Panel</span>
        </a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-bottom: 24px;">
            <a href="auth/logout.php" class="menu-item">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Log Out</span>
            </a>
        </div>
    </div>
</div>
