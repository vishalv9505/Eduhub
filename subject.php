<?php
require_once 'config/database.php';

// Get subject ID from URL
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if (!$subject_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get subject details
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = :subject_id");
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subject) {
        header('Location: index.php');
        exit;
    }

    // Get video lectures
    $stmt = $conn->prepare("
        SELECT DISTINCT vl.*, sm.title as material_title 
        FROM video_lectures vl
        JOIN study_materials sm ON vl.study_material_id = sm.id
        WHERE sm.subject_id = :subject_id
        ORDER BY vl.id DESC
    ");
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get practicals
    $stmt = $conn->prepare("
        SELECT p.*, 
               GROUP_CONCAT(q.question SEPARATOR '|||') as questions,
               GROUP_CONCAT(q.id SEPARATOR '|||') as question_ids
        FROM practicals p
        LEFT JOIN practical_questions q ON p.id = q.practical_id
        WHERE p.subject_id = :subject_id
        GROUP BY p.id
        ORDER BY p.id DESC
    ");
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    $practicals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process practicals data
    foreach ($practicals as &$practical) {
        if (!empty($practical['questions'])) {
            $practical['question_list'] = explode('|||', $practical['questions']);
            $practical['question_ids'] = explode('|||', $practical['question_ids']);
        } else {
            $practical['question_list'] = [];
            $practical['question_ids'] = [];
        }
    }
    unset($practical);

} catch(PDOException $e) {
    $error = "Failed to load subject details";
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                    <p class="text-muted mb-0">Subject Code: <?php echo htmlspecialchars($subject['subject_code']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Lectures Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Video Lectures</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($videos)): ?>
                <div class="row">
                    <?php foreach ($videos as $video): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h5>
                                    <?php if (!empty($video['description'])): ?>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
                                    <?php endif; ?>
                                    <a href="videos.php?subject_id=<?php echo $subject_id; ?>&video_id=<?php echo $video['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-play me-2"></i>Watch Video
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No video lectures available yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Practicals Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Practicals</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($practicals)): ?>
                <div class="row">
                    <?php foreach ($practicals as $practical): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($practical['title']); ?></h5>
                                    <?php if (!empty($practical['description'])): ?>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($practical['description'])); ?></p>
                                    <?php endif; ?>
                                    <a href="practicals.php?subject_id=<?php echo $subject_id; ?>&practical_id=<?php echo $practical['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-code me-2"></i>View Practical
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No practicals available yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 