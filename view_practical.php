<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get subject_id and practical_id from URL
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$practical_id = isset($_GET['practical_id']) ? (int)$_GET['practical_id'] : 0;

if (!$subject_id || !$practical_id) {
    header('Location: practicals.php');
    exit;
}

try {
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
    
    // Get practical details with questions
    $stmt = $conn->prepare("
        SELECT p.*, 
               GROUP_CONCAT(
                   JSON_OBJECT(
                       'question_number', pq.question_number,
                       'question_text', pq.question_text,
                       'description', pq.description,
                       'code_solution', pq.code_solution
                   )
               ) as questions
        FROM practicals p
        LEFT JOIN practical_questions pq ON p.id = pq.practical_id
        WHERE p.id = :practical_id AND p.subject_id = :subject_id
        GROUP BY p.id
    ");
    $stmt->bindParam(':practical_id', $practical_id);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    $practical = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$practical) {
        header('Location: practicals.php?subject_id=' . $subject_id);
        exit;
    }
    
    // Process questions data
    if ($practical['questions']) {
        $practical['questions'] = json_decode('[' . $practical['questions'] . ']', true);
    } else {
        $practical['questions'] = [];
    }
} catch(PDOException $e) {
    $error = "Failed to load practical data: " . $e->getMessage();
    error_log("Database Error in view_practical.php: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo htmlspecialchars($practical['title']); ?>
                </h3>
                <p class="text-muted mb-0">
                    <?php echo htmlspecialchars($subject['subject_name']); ?> - 
                    <?php echo htmlspecialchars($subject['branch']); ?> - 
                    Semester <?php echo $subject['semester']; ?>
                </p>
                <div class="card-tools mt-2">
                    <a href="practicals.php?subject_id=<?php echo $subject_id; ?>" 
                       class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Practicals
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($practical['questions'])): ?>
                    <?php foreach ($practical['questions'] as $question): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Question <?php echo $question['question_number']; ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>
                                
                                <?php if (!empty($question['description'])): ?>
                                    <div class="mb-3">
                                        <h6>Description:</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($question['description'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($question['code_solution'])): ?>
                                    <div class="mb-3">
                                        <h6>Solution:</h6>
                                        <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($question['code_solution']); ?></code></pre>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No questions available for this practical.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 