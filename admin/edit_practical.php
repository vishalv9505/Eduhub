<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';

$message = '';
$error = '';

if (!isset($_GET['id'])) {
    header("Location: manage_practicals.php");
    exit();
}

$practical_id = $_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch = $_POST['branch'];
    $semester = $_POST['semester'];
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];
    $practical_number = $_POST['practical_number'];
    $title = $_POST['title'];
    
    $query = "UPDATE practicals SET 
              branch = :branch,
              semester = :semester,
              subject_name = :subject_name,
              subject_code = :subject_code,
              practical_number = :practical_number,
              title = :title
              WHERE id = :id";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':branch', $branch);
    $stmt->bindParam(':semester', $semester);
    $stmt->bindParam(':subject_name', $subject_name);
    $stmt->bindParam(':subject_code', $subject_code);
    $stmt->bindParam(':practical_number', $practical_number);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':id', $practical_id);
    
    if ($stmt->execute()) {
        $message = "Practical updated successfully.";
    } else {
        $error = "Error updating practical.";
    }
}

// Fetch practical details
$query = "SELECT * FROM practicals WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $practical_id);
$stmt->execute();
$practical = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$practical) {
    header("Location: manage_practicals.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Practical - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Practical</h2>
            <a href="manage_practicals.php" class="btn btn-secondary">Back to Practicals</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <select class="form-select" name="branch" required>
                                <option value="">Select Branch</option>
                                <?php
                                $branches = ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical', 'Civil'];
                                foreach ($branches as $branch):
                                ?>
                                    <option value="<?php echo $branch; ?>" <?php echo $practical['branch'] === $branch ? 'selected' : ''; ?>>
                                        <?php echo $branch; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" required>
                                <option value="">Select Semester</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $practical['semester'] == $i ? 'selected' : ''; ?>>
                                        Semester <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" name="subject_name" 
                                   value="<?php echo htmlspecialchars($practical['subject_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" name="subject_code" 
                                   value="<?php echo htmlspecialchars($practical['subject_code']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Practical Number</label>
                            <input type="number" class="form-control" name="practical_number" 
                                   value="<?php echo htmlspecialchars($practical['practical_number']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" 
                                   value="<?php echo htmlspecialchars($practical['title']); ?>" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Update Practical</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 