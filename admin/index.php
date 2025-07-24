<?php
session_start();
require_once '../config/database.php';
require_once 'includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Include header
include 'includes/header.php';

$page_title = 'Dashboard';

// Get statistics
$conn = getDBConnection();

// Get total subjects count
$stmt = $conn->query("SELECT COUNT(*) FROM subjects");
$total_subjects = $stmt->fetchColumn();

// Get total practicals count
$stmt = $conn->query("SELECT COUNT(*) FROM practicals");
$total_practicals = $stmt->fetchColumn();

// Get total study materials count
$stmt = $conn->query("SELECT COUNT(*) FROM study_materials");
$total_materials = $stmt->fetchColumn();

// Get total previous papers count
$stmt = $conn->query("SELECT COUNT(*) FROM previous_papers");
$total_papers = $stmt->fetchColumn();

// Get total users count
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

// Get recent admin actions
$stmt = $conn->query("
    SELECT al.*, au.username 
    FROM admin_logs al 
    JOIN admin_users au ON al.admin_id = au.id 
    ORDER BY al.created_at DESC 
    LIMIT 5
");
$recent_actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get subjects by branch
$stmt = $conn->query("
    SELECT branch, COUNT(*) as count 
    FROM subjects 
    GROUP BY branch
");
$subjects_by_branch = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get admin details
$admin = getAdminDetails($_SESSION['admin_id']);

// Get statistics
$stats = [
    'subjects' => $conn->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
    'practicals' => $conn->query("SELECT COUNT(*) FROM practicals")->fetchColumn(),
    'study_materials' => $conn->query("SELECT COUNT(*) FROM study_materials")->fetchColumn(),
    'previous_papers' => $conn->query("SELECT COUNT(*) FROM previous_papers")->fetchColumn(),
    'video_lectures' => $conn->query("SELECT COUNT(*) FROM video_lectures")->fetchColumn()
];

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="index.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="subjects/add.php">
                            <i class="fas fa-plus me-2"></i>Add Subject
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="subjects/edit.php">
                            <i class="fas fa-edit me-2"></i>Edit Subject
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="subjects/remove.php">
                            <i class="fas fa-trash me-2"></i>Remove Subject
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Subjects</h5>
                            <p class="card-text display-4"><?php echo $stats['subjects']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Practicals</h5>
                            <p class="card-text display-4"><?php echo $stats['practicals']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Study Materials</h5>
                            <p class="card-text display-4"><?php echo $stats['study_materials']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Video Lectures</h5>
                            <p class="card-text display-4"><?php echo $stats['video_lectures']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_actions as $action): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($action['username']); ?></td>
                                        <td><?php echo htmlspecialchars($action['action']); ?></td>
                                        <td><?php echo htmlspecialchars($action['details']); ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($action['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$page_content = ob_get_clean();

// Include header and footer
include 'includes/footer.php';
?> 