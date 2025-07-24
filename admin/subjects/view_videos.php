<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['subject_id'])) {
    header('Location: manage.php');
    exit;
}

$subject_id = sanitize_input($_GET['subject_id']);

try {
    $conn = getDBConnection();
    
    // Get subject details
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        header('Location: manage.php');
        exit;
    }
    
    // Get video lectures
    $stmt = $conn->prepare("
        SELECT id, title, description, content_path as video_url, created_at
        FROM study_materials 
        WHERE subject_id = ? AND type = 'VIDEO'
        ORDER BY created_at DESC
    ");
    $stmt->execute([$subject_id]);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Failed to load video lectures";
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Video Lectures for <?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                    <div class="card-tools">
                        <a href="manage.php" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Subjects
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php else: ?>
                        <?php foreach ($videos as $video): ?>
                            <div class="video-item border rounded p-3 mb-4">
                                <h4 class="mb-3"><?php echo htmlspecialchars($video['title']); ?></h4>
                                
                                <?php if (!empty($video['description'])): ?>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
                                <?php endif; ?>
                                
                                <div class="video-container mb-3">
                                    <iframe 
                                        width="100%" 
                                        height="400" 
                                        src="<?php echo htmlspecialchars($video['video_url']); ?>" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 