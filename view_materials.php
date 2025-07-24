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

// Get subject details
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if (!$subject_id) {
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

// Get study materials
$query = "SELECT * FROM study_materials WHERE subject_id = :subject_id ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':subject_id', $subject_id);
$stmt->execute();
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="study_materials.php">Study Materials</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($subject['subject_name']); ?></li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2><?php echo htmlspecialchars($subject['subject_name']); ?> Materials</h2>
            <p class="text-muted">Subject Code: <?php echo htmlspecialchars($subject['subject_code']); ?></p>
        </div>
    </div>

    <?php if (!empty($materials)): ?>
        <div class="accordion" id="unitsAccordion">
            <?php 
            $currentUnit = 1;
            foreach ($materials as $material): 
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo $currentUnit === 1 ? '' : 'collapsed'; ?>" 
                                type="button" data-bs-toggle="collapse" 
                                data-bs-target="#unit<?php echo $currentUnit; ?>">
                            Unit <?php echo $currentUnit; ?>: <?php echo htmlspecialchars($material['title']); ?>
                        </button>
                    </h2>
                    <div id="unit<?php echo $currentUnit; ?>" 
                         class="accordion-collapse collapse <?php echo $currentUnit === 1 ? 'show' : ''; ?>" 
                         data-bs-parent="#unitsAccordion">
                        <div class="accordion-body">
                            <?php if ($material['description']): ?>
                                <p class="mb-4"><?php echo htmlspecialchars($material['description']); ?></p>
                            <?php endif; ?>

                            <div class="row g-3">
                                <!-- PDF Section -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Study Material (PDF)</h5>
                                            <?php 
                                            $pdf_urls = json_decode($material['file_path'], true);
                                            if (!empty($pdf_urls) && is_array($pdf_urls)): 
                                            ?>
                                                <div class="list-group">
                                                    <?php foreach ($pdf_urls as $pdf_url): ?>
                                                        <?php if (!empty(trim($pdf_url))): ?>
                                                            <a href="<?php echo htmlspecialchars($pdf_url); ?>" 
                                                               class="list-group-item list-group-item-action" 
                                                               target="_blank">
                                                                <i class="fas fa-file-pdf me-2"></i>
                                                                Download PDF
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">PDF not available yet</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Video Section -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Video Lectures</h5>
                                            <?php 
                                            $video_urls = json_decode($material['video_url'], true);
                                            if (!empty($video_urls) && is_array($video_urls)): 
                                            ?>
                                                <div class="list-group">
                                                    <?php foreach ($video_urls as $index => $video_url): ?>
                                                        <?php if (!empty(trim($video_url))): ?>
                                                            <a href="view_video.php?subject_id=<?php echo $subject_id; ?>&video_url=<?php echo urlencode($video_url); ?>" 
                                                               class="list-group-item list-group-item-action">
                                                                <i class="fas fa-play-circle me-2"></i>
                                                                Video Lecture <?php echo $index + 1; ?>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">No video lectures available yet</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="card-text mt-3">
                                <small class="text-muted">
                                    Added: <?php echo date('F j, Y', strtotime($material['created_at'])); ?>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            <?php 
            $currentUnit++;
            endforeach; 
            ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No study materials have been added for this subject yet.
        </div>
    <?php endif; ?>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<?php include 'includes/footer.php'; ?> 