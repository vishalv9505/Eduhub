<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
    <div class="position-sticky">
        <div class="d-flex justify-content-between align-items-center p-3 text-white">
            <h5 class="sidebar-heading mb-0">Eduhub Admin</h5>
            <button id="sidebarToggle" class="btn btn-link text-white p-0 d-none d-md-block">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo $current_page === 'manage.php' ? 'active' : ''; ?>" href="<?php echo $current_page === 'manage.php' ? '#' : '/Eduhub/admin/subjects/manage.php'; ?>">
                    <i class="fas fa-book me-2"></i>
                    <span class="nav-text">Manage Subjects</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/admin/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav> 