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
$page_title = 'Add Subject';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = sanitize_input($_POST['subject_code']);
    $subject_name = sanitize_input($_POST['subject_name']);
    $branch = sanitize_input($_POST['branch']);
    $semester = sanitize_input($_POST['semester']);

    if (empty($subject_code) || empty($subject_name) || empty($branch) || empty($semester)) {
        $error_msg = "All fields are required!";
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if subject code already exists
            $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ?");
            $stmt->execute([$subject_code]);
            if ($stmt->fetch()) {
                $error_msg = "Subject code already exists!";
            } else {
                // Insert new subject
                $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, branch, semester) VALUES (?, ?, ?, ?)");
                $stmt->execute([$subject_code, $subject_name, $branch, $semester]);
                
                // Log the action
                logAdminAction($_SESSION['admin_id'], 'add_subject', "Added subject: $subject_name ($subject_code)");
                
                $success_msg = "Subject added successfully!";
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

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="subject_code" class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="branch" class="form-label">Branch</label>
                            <select class="form-control" id="branch" name="branch" required>
                                <option value="">Select Branch</option>
                                <option value="CS">Computer Science</option>
                                <option value="IT">Information Technology</option>
                                <option value="EC">Electronics & Communication</option>
                                <option value="ME">Mechanical Engineering</option>
                                <option value="CE">Civil Engineering</option>
                                <option value="EE">Electrical Engineering</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-control" id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <?php for($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Subject</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 