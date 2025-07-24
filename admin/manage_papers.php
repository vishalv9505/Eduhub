<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';

$message = '';
$error = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch = $_POST['branch'];
    $semester = $_POST['semester'];
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];
    $exam_year = $_POST['exam_year'];
    $exam_session = $_POST['exam_session'];
    
    // Handle file upload
    $target_dir = "../uploads/papers/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["paper_file"]["name"], PATHINFO_EXTENSION));
    $file_name = $subject_code . '_' . $exam_year . '_' . $exam_session . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    // Check if file already exists
    if (file_exists($target_file)) {
        $error = "Sorry, file already exists.";
    } else {
        // Check file size (max 10MB)
        if ($_FILES["paper_file"]["size"] > 10000000) {
            $error = "Sorry, your file is too large. Maximum size is 10MB.";
        } else {
            // Allow certain file formats
            if ($file_extension != "pdf" && $file_extension != "doc" && $file_extension != "docx") {
                $error = "Sorry, only PDF & DOC files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["paper_file"]["tmp_name"], $target_file)) {
                    $file_path = "uploads/papers/" . $file_name;
                    
                    $query = "INSERT INTO previous_papers (branch, semester, subject_name, subject_code, exam_year, exam_session, file_path) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
                    if ($conn instanceof mysqli) {
                        $stmt = $conn->prepare($query);
                        if ($stmt instanceof mysqli_stmt) {
                            $stmt->bind_param("sisssss", $branch, $semester, $subject_name, $subject_code, $exam_year, $exam_session, $file_path);
                            if ($stmt->execute()) {
                                $message = "Paper uploaded successfully.";
                            } else {
                                $error = "Error uploading to database.";
                            }
                            $stmt->close();
                        } else {
                            $error = "Database error: " . $conn->error;
                        }
                    } else {
                        $error = "Database connection error.";
                    }
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }
    }
}

// Fetch existing papers
$query = "SELECT * FROM previous_papers ORDER BY branch, semester, exam_year DESC";
$papers = [];
if ($conn instanceof mysqli) {
    $result = $conn->query($query);
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $papers[] = $row;
        }
        $result->free();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Previous Papers - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Manage Previous Papers</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Upload New Paper</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="branch" class="form-label">Branch</label>
                            <select class="form-select" id="branch" name="branch" required>
                                <option value="">Select Branch</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Mechanical">Mechanical</option>
                                <option value="Civil">Civil</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="subject_code" class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                        </div>
                        <div class="col-md-4">
                            <label for="exam_year" class="form-label">Exam Year</label>
                            <input type="number" class="form-control" id="exam_year" name="exam_year" required>
                        </div>
                        <div class="col-md-4">
                            <label for="exam_session" class="form-label">Exam Session</label>
                            <select class="form-select" id="exam_session" name="exam_session" required>
                                <option value="Summer">Summer</option>
                                <option value="Winter">Winter</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="paper_file" class="form-label">Paper File</label>
                            <input type="file" class="form-control" id="paper_file" name="paper_file" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Upload Paper</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Papers List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Existing Papers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Semester</th>
                                <th>Subject</th>
                                <th>Code</th>
                                <th>Year</th>
                                <th>Session</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($papers as $paper): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($paper['branch']); ?></td>
                                    <td><?php echo htmlspecialchars($paper['semester']); ?></td>
                                    <td><?php echo htmlspecialchars($paper['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($paper['subject_code']); ?></td>
                                    <td><?php echo htmlspecialchars($paper['exam_year']); ?></td>
                                    <td><?php echo htmlspecialchars($paper['exam_session']); ?></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($paper['file_path']); ?>" 
                                           class="btn btn-sm btn-primary" 
                                           target="_blank">View</a>
                                        <a href="delete_paper.php?id=<?php echo $paper['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this paper?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 