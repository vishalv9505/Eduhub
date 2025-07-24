<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_GET['subject_id']) || !isset($_GET['video_id'])) {
    header('Location: index.php');
    exit;
}

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$video_id = isset($_GET['video_id']) ? (int)$_GET['video_id'] : 0;

// Function to convert YouTube URL to embed URL
function getYoutubeEmbedUrl($url) {
    $videoId = '';
    
    // Check for various YouTube URL formats
    if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $matches)) {
        $videoId = $matches[1];
    } else if (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $matches)) {
        $videoId = $matches[1];
    } else if (preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $url, $matches)) {
        $videoId = $matches[1];
    } else if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    
    if ($videoId) {
        return 'https://www.youtube.com/embed/' . $videoId;
    }
    
    return false;
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
    
    // Get specific video lecture
    $stmt = $conn->prepare("
        SELECT vl.*, sm.title as material_title
        FROM video_lectures vl
        JOIN study_materials sm ON vl.study_material_id = sm.id
        WHERE vl.id = :video_id AND sm.subject_id = :subject_id
    ");
    $stmt->bindParam(':video_id', $video_id);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$video) {
        header('Location: subject.php?id=' . $subject_id);
        exit;
    }
    
    // Convert YouTube URL to embed URL
    $embedUrl = getYoutubeEmbedUrl($video['video_url']);
    if (!$embedUrl) {
        $error = "Invalid YouTube URL format";
    }
} catch(PDOException $e) {
    $error = "Failed to load video lecture";
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                    <div class="card-tools">
                        <a href="view_materials.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Subject
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php else: ?>
                        <div class="video-item border rounded p-3 mb-4">
                            <h4 class="mb-3"><?php echo htmlspecialchars($video['material_title']); ?></h4>
                            
                            <?php if (!empty($video['description'])): ?>
                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="video-container mb-3">
                                <iframe 
                                    width="100%" 
                                    height="400" 
                                    src="<?php echo htmlspecialchars($embedUrl); ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 