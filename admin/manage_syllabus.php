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
    $academic_year = $_POST['academic_year'];
    
    // Handle file upload
    $target_dir = "../uploads/syllabus/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["syllabus_file"]["name"], PATHINFO_EXTENSION));
    $file_name = $subject_code . '_' . $academic_year . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    // Check if file already exists
    if (file_exists($target_file)) {
        $error = "Sorry, file already exists.";
    } else {
        // Check file size (max 10MB)
        if ($_FILES["syllabus_file"]["size"] > 10000000) {
            $error = "Sorry, your file is too large. Maximum size is 10MB.";
        } else {
            // Allow certain file formats
            if ($file_extension != "pdf" && $file_extension != "doc" && $file_extension != "docx") {
                $error = "Sorry, only PDF & DOC files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["syllabus_file"]["tmp_name"], $target_file)) {
                    $file_path = "uploads/syllabus/" . $file_name;
                    
                    $query = "INSERT INTO syllabus (branch, semester, subject_name, subject_code, academic_year, file_path) 
                             VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    if ($stmt instanceof mysqli_stmt) {
                        $stmt->bind_param("sissss", $branch, $semester, $subject_name, $subject_code, $academic_year, $file_path);
                        if ($stmt->execute()) {
                            $message = "Syllabus uploaded successfully.";
                        } else {
                            $error = "Error uploading to database.";
                        }
                        $stmt->close();
                    } else {
                        $error = "Database error: Failed to prepare statement.";
                    }

                } else {
                    $error = "Sorry, there was an error uploading your file.";
                }
            }
        }
    }
}

// Fetch existing syllabi
$query = "SELECT * FROM syllabus ORDER BY branch, semester, subject_code";
$result = $conn->query($query);
$syllabi = [];
if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
        $syllabi[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Syllabus - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Manage Syllabus</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Upload New Syllabus</h5>
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
                        <div class="col-md-6">
                            <label for="academic_year" class="form-label">Academic Year</label>
                            <input type="text" class="form-control" id="academic_year" name="academic_year" placeholder="e.g., 2023-24" required>
                        </div>
                        <div class="col-md-6">
                            <label for="syllabus_file" class="form-label">Syllabus File</label>
                            <input type="file" class="form-control" id="syllabus_file" name="syllabus_file" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Upload Syllabus</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Syllabus List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Existing Syllabi</h5>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($syllabi as $syllabus): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($syllabus['branch']); ?></td>
                                    <td><?php echo htmlspecialchars($syllabus['semester']); ?></td>
                                    <td><?php echo htmlspecialchars($syllabus['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($syllabus['subject_code']); ?></td>
                                    <td><?php echo htmlspecialchars($syllabus['academic_year']); ?></td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($syllabus['file_path']); ?>" 
                                           class="btn btn-sm btn-primary" 
                                           target="_blank">View</a>
                                        <a href="delete_syllabus.php?id=<?php echo $syllabus['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this syllabus?')">Delete</a>
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