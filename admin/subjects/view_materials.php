<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

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
    
    return $videoId ? 'https://www.youtube.com/embed/' . $videoId : false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $success_msg = '';
    $error_msg = '';

    try {
        if ($action === 'add_material') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $subject_id = $_POST['subject_id'];
            
            // Debug logging
            error_log("Adding material - Title: $title, Subject ID: $subject_id");
            error_log("POST Data: " . print_r($_POST, true));
            
            // Start transaction
            $conn->beginTransaction();
            
            // Filter out empty URLs and store as JSON
            $pdf_urls = array_filter($_POST['pdf_urls'] ?? [], function($url) {
                return !empty(trim($url));
            });
            $video_urls = array_filter($_POST['video_urls'] ?? [], function($url) {
                return !empty(trim($url));
            });
            
            $pdf_json = !empty($pdf_urls) ? json_encode(array_values($pdf_urls)) : null;
            $video_json = !empty($video_urls) ? json_encode(array_values($video_urls)) : null;
            
            // Insert material with both PDF and video URLs if provided
            if (!empty($pdf_json) || !empty($video_json)) {
                error_log("Inserting Material - PDFs: $pdf_json, Videos: $video_json");
                $stmt = $conn->prepare("
                    INSERT INTO study_materials (subject_id, title, description, file_path, video_url, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute([$subject_id, $title, $description, $pdf_json, $video_json]);
                error_log("Insert Result: " . ($result ? "Success" : "Failed"));
            }
            
            // Commit transaction
            $conn->commit();
            error_log("Transaction committed successfully");
            $success_msg = "Material added successfully!";
            
        } elseif ($action === 'update_material') {
            $material_id = $_POST['material_id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            
            // Filter out empty URLs and store as JSON
            $pdf_urls = array_filter($_POST['pdf_urls'] ?? [], function($url) {
                return !empty(trim($url));
            });
            $video_urls = array_filter($_POST['video_urls'] ?? [], function($url) {
                return !empty(trim($url));
            });
            
            $pdf_json = !empty($pdf_urls) ? json_encode(array_values($pdf_urls)) : null;
            $video_json = !empty($video_urls) ? json_encode(array_values($video_urls)) : null;
            
            // Start transaction
            $conn->beginTransaction();
            
            // Update study_materials with both PDF and video URLs
            $stmt = $conn->prepare("
                UPDATE study_materials 
                SET title = ?, description = ?, file_path = ?, video_url = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $pdf_json, $video_json, $material_id]);
            
            // Commit transaction
            $conn->commit();
            $success_msg = "Material updated successfully!";
        }
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Database Error: " . $e->getMessage());
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Get subject details
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if (!$subject_id) {
    header('Location: manage.php');
    exit;
}

try {
    // Get subject details
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        header('Location: manage.php');
        exit;
    }
    
    // Get study materials
    $stmt = $conn->prepare("
        SELECT * FROM study_materials 
        WHERE subject_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$subject_id]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Database error: " . $e->getMessage();
}

// Add debug information
error_log("Materials: " . print_r($materials, true));

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Study Materials - <?php echo htmlspecialchars($subject['subject_name']); ?></h1>
                <div>
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#materialModal">
                        <i class="fas fa-plus me-2"></i>Add Material
                    </button>
                    <a href="manage.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                    </a>
                </div>
            </div>

            <?php if (isset($success_msg) && $success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if (isset($error_msg) && $error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- Units Accordion -->
            <div class="accordion" id="unitsAccordion">
                <?php if (!empty($materials)): ?>
                    <?php 
                    $currentUnit = 1;
                    foreach ($materials as $material): 
                    ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $currentUnit === 1 ? '' : 'collapsed'; ?>" 
                                        type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#unit<?php echo $currentUnit; ?>">
                                    <?php echo htmlspecialchars($material['title']); ?>
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
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0">Study Material (PDF)</h5>
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="addMaterial('PDF', <?php echo $currentUnit; ?>)">
                                                        <i class="fas fa-plus"></i> Add PDF
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <?php if (!empty($material['file_path'])): ?>
                                                        <div class="list-group">
                                                            <?php 
                                                            $pdf_urls = json_decode($material['file_path'], true);
                                                            if ($pdf_urls && is_array($pdf_urls)):
                                                                foreach ($pdf_urls as $pdf_url): 
                                                                    if (!empty(trim($pdf_url))):
                                                            ?>
                                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <a href="<?php echo htmlspecialchars($pdf_url); ?>" 
                                                                       target="_blank" class="text-decoration-none">
                                                                        <i class="fas fa-file-pdf me-2"></i>
                                                                        <?php echo htmlspecialchars($pdf_url); ?>
                                                                    </a>
                                                                    <div class="btn-group">
                                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                                onclick="editMaterial(<?php echo $material['id']; ?>)">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                                onclick="deleteMaterial(<?php echo $material['id']; ?>)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            <?php 
                                                                    endif;
                                                                endforeach;
                                                            endif;
                                                            ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-center py-3">
                                                            <p class="text-muted mb-0">No PDF materials available yet.</p>
                                                            <button type="button" class="btn btn-sm btn-primary mt-2" 
                                                                    onclick="addMaterial('PDF', <?php echo $currentUnit; ?>)">
                                                                <i class="fas fa-plus"></i> Add PDF Material
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Video Section -->
                                        <div class="col-md-6">
                                            <div class="card h-100">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0">Video Lectures</h5>
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="addMaterial('VIDEO', <?php echo $currentUnit; ?>)">
                                                        <i class="fas fa-plus"></i> Add Video
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <?php if (!empty($material['video_url'])): ?>
                                                        <div class="list-group">
                                                            <?php 
                                                            $video_urls = json_decode($material['video_url'], true);
                                                            if ($video_urls && is_array($video_urls)):
                                                                foreach ($video_urls as $video_url): 
                                                                    if (!empty(trim($video_url))):
                                                            ?>
                                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <a href="<?php echo htmlspecialchars($video_url); ?>" 
                                                                       target="_blank" class="text-decoration-none">
                                                                        <i class="fas fa-play-circle me-2"></i>
                                                                        <?php echo htmlspecialchars($video_url); ?>
                                                                    </a>
                                                                    <div class="btn-group">
                                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                                onclick="editMaterial(<?php echo $material['id']; ?>)">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                                onclick="deleteMaterial(<?php echo $material['id']; ?>)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            <?php 
                                                                    endif;
                                                                endforeach;
                                                            endif;
                                                            ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-center py-3">
                                                            <p class="text-muted mb-0">No video lectures available yet.</p>
                                                            <button type="button" class="btn btn-sm btn-primary mt-2" 
                                                                    onclick="addMaterial('VIDEO', <?php echo $currentUnit; ?>)">
                                                                <i class="fas fa-plus"></i> Add Video Material
                                                            </button>
                                                        </div>
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
                <?php else: ?>
                    <div class="alert alert-info">
                        No study materials have been added for this subject yet.
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Add Material Modal -->
<div class="modal fade" id="materialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="materialForm" method="POST">
                    <input type="hidden" name="action" value="add_material">
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                    <input type="hidden" name="material_id" id="material_id">
                    
                    <!-- Basic Information -->
                    <h6 class="mb-3">Basic Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="title" class="form-label">Unit Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <!-- PDF Section -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">PDF Materials</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addPdfField()">
                                <i class="fas fa-plus"></i> Add PDF Link
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="pdfFields">
                                <div class="pdf-field mb-3">
                                    <div class="input-group">
                                        <input type="url" class="form-control" name="pdf_urls[]" placeholder="Enter PDF URL">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Enter direct link to PDF file</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Section -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Video Materials</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addVideoField()">
                                <i class="fas fa-plus"></i> Add Video Link
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="videoFields">
                                <div class="video-field mb-3">
                                    <div class="input-group">
                                        <input type="url" class="form-control" name="video_urls[]" placeholder="Enter YouTube URL">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Enter YouTube video URL</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="materialForm" class="btn btn-primary">Save Material</button>
            </div>
        </div>
    </div>
</div>

<script>
let materialModal;

document.addEventListener('DOMContentLoaded', function() {
    materialModal = new bootstrap.Modal(document.getElementById('materialModal'));
    
    // Add click handler for the main Add Material button
    document.querySelector('[data-bs-target="#materialModal"]').addEventListener('click', function() {
        // Reset form
        document.getElementById('materialForm').reset();
        document.getElementById('material_id').value = '';
        
        materialModal.show();
    });
});

function addPdfField() {
    const container = document.getElementById('pdfFields');
    const newField = document.createElement('div');
    newField.className = 'pdf-field mb-3';
    newField.innerHTML = `
        <div class="input-group">
            <input type="url" class="form-control" name="pdf_urls[]" placeholder="Enter PDF URL">
            <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <small class="text-muted">Enter direct link to PDF file</small>
    `;
    container.appendChild(newField);
}

function addVideoField() {
    const container = document.getElementById('videoFields');
    const newField = document.createElement('div');
    newField.className = 'video-field mb-3';
    newField.innerHTML = `
        <div class="input-group">
            <input type="url" class="form-control" name="video_urls[]" placeholder="Enter YouTube URL">
            <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <small class="text-muted">Enter YouTube video URL</small>
    `;
    container.appendChild(newField);
}

function removeField(button) {
    const field = button.closest('.pdf-field, .video-field');
    field.remove();
}

function addMaterial(type, unit) {
    // Reset form
    document.getElementById('materialForm').reset();
    document.getElementById('material_id').value = '';
    
    // Reset PDF and Video fields to show only one field each
    document.getElementById('pdfFields').innerHTML = `
        <div class="pdf-field mb-3">
            <div class="input-group">
                <input type="url" class="form-control" name="pdf_urls[]" placeholder="Enter PDF URL">
                <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <small class="text-muted">Enter direct link to PDF file</small>
        </div>
    `;
    
    document.getElementById('videoFields').innerHTML = `
        <div class="video-field mb-3">
            <div class="input-group">
                <input type="url" class="form-control" name="video_urls[]" placeholder="Enter YouTube URL">
                <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <small class="text-muted">Enter YouTube video URL</small>
        </div>
    `;
    
    materialModal.show();
}

function editMaterial(id) {
    // Show loading message
    const loadingMessage = 'Loading material details...';
    console.log(loadingMessage);
    
    // Fetch material details
    fetch(`ajax/get_material.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const material = data.material;
                
                // Set form for editing
                const form = document.getElementById('materialForm');
                form.querySelector('[name="action"]').value = 'update_material';
                document.getElementById('material_id').value = material.id;
                document.getElementById('title').value = material.title;
                document.getElementById('description').value = material.description;
                
                // Reset fields
                document.getElementById('pdfFields').innerHTML = '';
                document.getElementById('videoFields').innerHTML = '';
                
                // Add PDF fields if exist
                if (material.file_path) {
                    try {
                        const pdfUrls = JSON.parse(material.file_path);
                        if (Array.isArray(pdfUrls)) {
                            pdfUrls.forEach((url, index) => {
                                const field = document.createElement('div');
                                field.className = 'pdf-field mb-3';
                                field.innerHTML = `
                                    <div class="input-group">
                                        <input type="url" class="form-control" name="pdf_urls[]" value="${url}" placeholder="Enter PDF URL">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Enter direct link to PDF file</small>
                                `;
                                document.getElementById('pdfFields').appendChild(field);
                            });
                        } else {
                            console.error('PDF URLs is not an array:', pdfUrls);
                            addPdfField(); // Add empty field as fallback
                        }
                    } catch (e) {
                        console.error('Error parsing PDF URLs:', e);
                        addPdfField(); // Add empty field as fallback
                    }
                } else {
                    addPdfField();
                }
                
                // Add video fields if exist
                if (material.video_url) {
                    try {
                        const videoUrls = JSON.parse(material.video_url);
                        if (Array.isArray(videoUrls)) {
                            videoUrls.forEach((url, index) => {
                                const field = document.createElement('div');
                                field.className = 'video-field mb-3';
                                field.innerHTML = `
                                    <div class="input-group">
                                        <input type="url" class="form-control" name="video_urls[]" value="${url}" placeholder="Enter YouTube URL">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeField(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Enter YouTube video URL</small>
                                `;
                                document.getElementById('videoFields').appendChild(field);
                            });
                        } else {
                            console.error('Video URLs is not an array:', videoUrls);
                            addVideoField(); // Add empty field as fallback
                        }
                    } catch (e) {
                        console.error('Error parsing video URLs:', e);
                        addVideoField(); // Add empty field as fallback
                    }
                } else {
                    addVideoField();
                }
                
                // Update modal title
                document.querySelector('#materialModal .modal-title').textContent = 'Edit Material';
                
                materialModal.show();
            } else {
                // Show error message with details
                let errorMessage = `Error: ${data.message}`;
                if (data.details) {
                    errorMessage += `\nDetails:\n`;
                    errorMessage += `- Error Code: ${data.details.error_code}\n`;
                    errorMessage += `- File: ${data.details.error_file}\n`;
                    errorMessage += `- Line: ${data.details.error_line}`;
                }
                console.error(errorMessage);
                alert(errorMessage);
            }
        })
        .catch(error => {
            // Show detailed error message
            const errorMessage = `Failed to load material details:\n${error.message}\n\nPlease check the console for more details and contact support if the issue persists.`;
            console.error('Error:', error);
            alert(errorMessage);
        });
}

function deleteMaterial(id) {
    if (confirm('Are you sure you want to delete this material?')) {
        fetch(`ajax/delete_material.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete material');
            });
    }
}
</script>

<?php include '../includes/footer.php'; ?> 