<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

try {
    // Get unique branches
    $stmt = $conn->prepare("SELECT DISTINCT branch FROM subjects ORDER BY branch");
    $stmt->execute();
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unique semesters
    $stmt = $conn->prepare("SELECT DISTINCT semester FROM subjects ORDER BY semester");
    $stmt->execute();
    $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If branch and semester are selected, show subjects
    if (isset($_GET['branch']) && isset($_GET['semester'])) {
        $branch = $_GET['branch'];
        $semester = (int)$_GET['semester'];
        
        // Get subjects for selected branch and semester
        $stmt = $conn->prepare("
            SELECT s.*, 
                   COUNT(p.id) as practical_count
            FROM subjects s
            LEFT JOIN practicals p ON s.id = p.subject_id
            WHERE s.branch = :branch 
            AND s.semester = :semester
            GROUP BY s.id
            ORDER BY s.subject_name
        ");
        $stmt->bindParam(':branch', $branch);
        $stmt->bindParam(':semester', $semester);
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $show_subjects = true;
    } 
    // If subject_id is selected, show practicals for that subject
    else if (isset($_GET['subject_id'])) {
        $subject_id = (int)$_GET['subject_id'];
        
        // Get subject details
        $stmt = $conn->prepare("
            SELECT s.*
            FROM subjects s
            WHERE s.id = :subject_id
        ");
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subject) {
            header('Location: practicals.php');
            exit;
        }
        
        // Get practicals for this subject
        $stmt = $conn->prepare("
            SELECT p.*, 
                   COUNT(q.id) as question_count
            FROM practicals p
            LEFT JOIN practical_questions q ON p.id = q.practical_id
            WHERE p.subject_id = :subject_id
            GROUP BY p.id
            ORDER BY p.title
        ");
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();
        $practicals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $show_practicals = true;
    }
} catch(PDOException $e) {
    $error = "Failed to load data: " . $e->getMessage();
    error_log("Database Error in practicals.php: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <?php if (!isset($show_subjects) && !isset($show_practicals)): ?>
            <!-- Show branch and semester selection -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Select Branch and Semester</h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="practicals.php" class="row g-3">
                                <div class="col-md-6">
                                    <label for="branch" class="form-label">Branch</label>
                                    <select class="form-select" id="branch" name="branch" required>
                                        <option value="">Select Branch</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo htmlspecialchars($branch['branch']); ?>">
                                                <?php echo htmlspecialchars($branch['branch']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="semester" class="form-label">Semester</label>
                                    <select class="form-select" id="semester" name="semester" required>
                                        <option value="">Select Semester</option>
                                        <?php foreach ($semesters as $sem): ?>
                                            <option value="<?php echo $sem['semester']; ?>">
                                                Semester <?php echo $sem['semester']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Show Subjects</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif (isset($show_subjects)): ?>
            <!-- Show subjects for selected branch and semester -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                Subjects for <?php echo htmlspecialchars($branch); ?> - 
                                Semester <?php echo $semester; ?>
                            </h3>
                            <div class="card-tools">
                                <a href="practicals.php" class="btn btn-default btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Selection
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($subjects)): ?>
                                <div class="row">
                                    <?php foreach ($subjects as $subject): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h5>
                                                    <p class="text-muted">Subject Code: <?php echo htmlspecialchars($subject['subject_code']); ?></p>
                                                    <p class="text-muted">Practicals: <?php echo $subject['practical_count']; ?></p>
                                                    <a href="practicals.php?subject_id=<?php echo $subject['id']; ?>" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-code me-2"></i>View Practicals
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No subjects found for this branch and semester.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif (isset($show_practicals)): ?>
            <!-- Show practicals for selected subject -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                Practicals for <?php echo htmlspecialchars($subject['subject_name']); ?>
                            </h3>
                            <p class="text-muted mb-0">
                                <?php echo htmlspecialchars($subject['branch']); ?> - 
                                Semester <?php echo $subject['semester']; ?>
                            </p>
                            <div class="card-tools mt-2">
                                <a href="practicals.php?branch=<?php echo urlencode($subject['branch']); ?>&semester=<?php echo $subject['semester']; ?>" 
                                   class="btn btn-default btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Subjects
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($practicals)): ?>
                                <div class="row">
                                    <?php foreach ($practicals as $practical): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($practical['title']); ?></h5>
                                                    <p class="text-muted">Questions: <?php echo $practical['question_count']; ?></p>
                                                    <a href="view_practical.php?subject_id=<?php echo $subject_id; ?>&practical_id=<?php echo $practical['id']; ?>" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-eye me-2"></i>View Practical
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No practicals available for this subject.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 