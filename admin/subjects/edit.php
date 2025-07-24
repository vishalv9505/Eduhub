<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$success_msg = '';
$error_msg = '';
$subjects = [];
$search_performed = false;
$page_title = 'Edit Subject';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search_performed = true;
    $branch = sanitize_input($_GET['branch'] ?? '');
    $semester = sanitize_input($_GET['semester'] ?? '');
    $search_term = sanitize_input($_GET['search_term'] ?? '');

    try {
        $conn = getDBConnection();
        $query = "SELECT * FROM subjects WHERE 1=1";
        $params = [];

        if (!empty($branch)) {
            $query .= " AND branch = ?";
            $params[] = $branch;
        }
        if (!empty($semester)) {
            $query .= " AND semester = ?";
            $params[] = $semester;
        }
        if (!empty($search_term)) {
            $query .= " AND (subject_code LIKE ? OR subject_name LIKE ?)";
            $params[] = "%$search_term%";
            $params[] = "%$search_term%";
        }

        $query .= " ORDER BY branch, semester, subject_name";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $subject_id = sanitize_input($_POST['subject_id']);
    $subject_code = sanitize_input($_POST['subject_code']);
    $subject_name = sanitize_input($_POST['subject_name']);
    $branch = sanitize_input($_POST['branch']);
    $semester = sanitize_input($_POST['semester']);

    if (empty($subject_code) || empty($subject_name) || empty($branch) || empty($semester)) {
        $error_msg = "All fields are required!";
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if subject code already exists for other subjects
            $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ? AND id != ?");
            $stmt->execute([$subject_code, $subject_id]);
            if ($stmt->fetch()) {
                $error_msg = "Subject code already exists!";
            } else {
                // Update subject
                $stmt = $conn->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, branch = ?, semester = ? WHERE id = ?");
                $stmt->execute([$subject_code, $subject_name, $branch, $semester, $subject_id]);
                
                // Log the action
                logAdminAction($_SESSION['admin_id'], 'edit_subject', "Updated subject: $subject_name ($subject_code)");
                
                $success_msg = "Subject updated successfully!";
            }
        } catch(PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Include sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- Search Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label for="branch" class="form-label">Branch</label>
                            <select class="form-control" id="branch" name="branch">
                                <option value="">All Branches</option>
                                <option value="CS">Computer Science</option>
                                <option value="IT">Information Technology</option>
                                <option value="EC">Electronics & Communication</option>
                                <option value="ME">Mechanical Engineering</option>
                                <option value="CE">Civil Engineering</option>
                                <option value="EE">Electrical Engineering</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-control" id="semester" name="semester">
                                <option value="">All Semesters</option>
                                <?php for($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="search_term" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Subject code or name">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" name="search" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Subjects List -->
            <?php if ($search_performed): ?>
                <?php if (empty($subjects)): ?>
                    <div class="alert alert-info">No subjects found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Semester</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['branch']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['semester']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $subject['id']; ?>">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $subject['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Subject</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="subject_code<?php echo $subject['id']; ?>" class="form-label">Subject Code</label>
                                                            <input type="text" class="form-control" id="subject_code<?php echo $subject['id']; ?>" name="subject_code" value="<?php echo htmlspecialchars($subject['subject_code']); ?>" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="subject_name<?php echo $subject['id']; ?>" class="form-label">Subject Name</label>
                                                            <input type="text" class="form-control" id="subject_name<?php echo $subject['id']; ?>" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="branch<?php echo $subject['id']; ?>" class="form-label">Branch</label>
                                                            <select class="form-control" id="branch<?php echo $subject['id']; ?>" name="branch" required>
                                                                <option value="CS" <?php echo $subject['branch'] == 'CS' ? 'selected' : ''; ?>>Computer Science</option>
                                                                <option value="IT" <?php echo $subject['branch'] == 'IT' ? 'selected' : ''; ?>>Information Technology</option>
                                                                <option value="EC" <?php echo $subject['branch'] == 'EC' ? 'selected' : ''; ?>>Electronics & Communication</option>
                                                                <option value="ME" <?php echo $subject['branch'] == 'ME' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                                                <option value="CE" <?php echo $subject['branch'] == 'CE' ? 'selected' : ''; ?>>Civil Engineering</option>
                                                                <option value="EE" <?php echo $subject['branch'] == 'EE' ? 'selected' : ''; ?>>Electrical Engineering</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="semester<?php echo $subject['id']; ?>" class="form-label">Semester</label>
                                                            <select class="form-control" id="semester<?php echo $subject['id']; ?>" name="semester" required>
                                                                <?php for($i = 1; $i <= 8; $i++): ?>
                                                                    <option value="<?php echo $i; ?>" <?php echo $subject['semester'] == $i ? 'selected' : ''; ?>>
                                                                        Semester <?php echo $i; ?>
                                                                    </option>
                                                                <?php endfor; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 