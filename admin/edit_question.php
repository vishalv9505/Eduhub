<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';

$message = '';
$error = '';

if (!isset($_GET['id'])) {
    header("Location: manage_practicals.php");
    exit();
}

$question_id = $_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_number = $_POST['question_number'];
    $question_text = $_POST['question_text'];
    $description = $_POST['description'];
    $code_solution = $_POST['code_solution'];
    
    $query = "UPDATE practical_questions SET 
              question_number = :question_number,
              question_text = :question_text,
              description = :description,
              code_solution = :code_solution
              WHERE id = :id";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':question_number', $question_number);
    $stmt->bindParam(':question_text', $question_text);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':code_solution', $code_solution);
    $stmt->bindParam(':id', $question_id);
    
    if ($stmt->execute()) {
        $message = "Question updated successfully.";
    } else {
        $error = "Error updating question.";
    }
}

// Fetch question details
$query = "SELECT q.*, p.branch, p.semester, p.subject_name, p.practical_number 
          FROM practical_questions q 
          JOIN practicals p ON q.practical_id = p.id 
          WHERE q.id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $question_id);
$stmt->execute();
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    header("Location: manage_practicals.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Question</h2>
            <a href="manage_practicals.php" class="btn btn-secondary">Back to Practicals</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <?php echo htmlspecialchars($question['branch'] . ' - Semester ' . $question['semester']); ?>
                    <br>
                    <?php echo htmlspecialchars($question['subject_name'] . ' - Practical ' . $question['practical_number']); ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Question Number</label>
                            <input type="number" class="form-control" name="question_number" 
                                   value="<?php echo htmlspecialchars($question['question_number']); ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Question Text</label>
                            <textarea class="form-control" name="question_text" rows="3" required><?php 
                                echo htmlspecialchars($question['question_text']); 
                            ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"><?php 
                                echo htmlspecialchars($question['description']); 
                            ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Code Solution</label>
                            <textarea class="form-control font-monospace" name="code_solution" rows="8"><?php 
                                echo htmlspecialchars($question['code_solution']); 
                            ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Update Question</button>
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