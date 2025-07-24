<?php
require_once 'config/database.php';

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

// Get parameters
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$video_url = isset($_GET['video_url']) ? urldecode($_GET['video_url']) : '';

if (!$subject_id || !$video_url) {
    header('Location: study_materials.php');
    exit;
}

// Get subject details
$query = "SELECT * FROM subjects WHERE id = :subject_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':subject_id', $subject_id);
$stmt->execute();
$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    header('Location: study_materials.php');
    exit;
}

// Get the embed URL
$embed_url = getYoutubeEmbedUrl($video_url);

if (!$embed_url) {
    header('Location: view_materials.php?subject_id=' . $subject_id);
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="study_materials.php">Study Materials</a></li>
            <li class="breadcrumb-item"><a href="view_materials.php?subject_id=<?php echo $subject_id; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></a></li>
            <li class="breadcrumb-item active">Video Lecture</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2><?php echo htmlspecialchars($subject['subject_name']); ?> - Video Lecture</h2>
            <p class="text-muted">Subject Code: <?php echo htmlspecialchars($subject['subject_code']); ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="ratio ratio-16x9">
                        <iframe src="<?php echo htmlspecialchars($embed_url); ?>" 
                                allowfullscreen 
                                class="rounded"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <a href="view_materials.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Materials
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 