<?php
require_once 'config/database.php';

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

// Get units with their materials
$query = "SELECT u.*, 
          GROUP_CONCAT(
            JSON_OBJECT(
                'id', sm.id,
                'title', sm.title,
                'type', sm.type,
                'file_path', sm.file_path,
                'video_url', sm.video_url,
                'description', sm.description
            )
          ) as materials
          FROM units u
          LEFT JOIN study_materials sm ON u.id = sm.unit_id
          WHERE u.subject_id = :subject_id
          GROUP BY u.id
          ORDER BY u.unit_number";
$stmt = $conn->prepare($query);
$stmt->bindParam(':subject_id', $subject_id);
$stmt->execute();
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="study_materials.php">Study Materials</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($subject['subject_name']); ?></li>
        </ol>
    </nav>

    <h2 class="mb-4"><?php echo htmlspecialchars($subject['subject_name']); ?> Materials</h2>
    <p class="text-muted">Subject Code: <?php echo htmlspecialchars($subject['subject_code']); ?></p>

    <?php if (!empty($units)): ?>
        <div class="accordion" id="unitsAccordion">
            <?php foreach ($units as $unit): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#unit<?php echo $unit['id']; ?>">
                            Unit <?php echo htmlspecialchars($unit['unit_number']); ?>: 
                            <?php echo htmlspecialchars($unit['unit_title']); ?>
                        </button>
                    </h2>
                    <div id="unit<?php echo $unit['id']; ?>" class="accordion-collapse collapse" 
                         data-bs-parent="#unitsAccordion">
                        <div class="accordion-body">
                            <?php if ($unit['description']): ?>
                                <p><?php echo htmlspecialchars($unit['description']); ?></p>
                            <?php endif; ?>

                            <?php if ($unit['materials']): ?>
                                <div class="row g-4">
                                    <?php 
                                    $materials = array_filter(array_map('json_decode', explode(',', $unit['materials'])));
                                    foreach ($materials as $material): 
                                    ?>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <?php echo htmlspecialchars($material->title); ?>
                                                    </h5>
                                                    <?php if ($material->description): ?>
                                                        <p class="card-text">
                                                            <?php echo htmlspecialchars($material->description); ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if ($material->type === 'video'): ?>
                                                        <div class="ratio ratio-16x9 mb-3">
                                                            <iframe src="<?php echo htmlspecialchars($material->video_url); ?>" 
                                                                    allowfullscreen></iframe>
                                                        </div>
                                                    <?php else: ?>
                                                        <a href="<?php echo htmlspecialchars($material->file_path); ?>" 
                                                           class="btn btn-primary" target="_blank">
                                                            <i class="fas fa-download me-2"></i>
                                                            Download <?php echo strtoupper($material->type); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No materials available for this unit yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No units have been added to this subject yet.
        </div>
    <?php endif; ?>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<?php include 'includes/footer.php'; ?> 