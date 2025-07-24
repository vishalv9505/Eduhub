<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Manage Subjects';
$success_msg = '';
$error_msg = '';
$subjects = [];
$search_performed = false;

// Handle form submission response
if (isset($_GET['success'])) {
    $success_msg = "Subject added successfully!";
} elseif (isset($_GET['error'])) {
    $error_msg = $_GET['error'];
}

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_performed = isset($_GET['search']) || isset($_GET['branch']) || isset($_GET['semester']) || isset($_GET['search_term']);
    $branch = sanitize_input($_GET['branch'] ?? '');
    $semester = sanitize_input($_GET['semester'] ?? '');
    $search_term = sanitize_input($_GET['search_term'] ?? '');

    try {
        $conn = getDBConnection();
        $query = "SELECT * FROM subjects WHERE 1=1";
        $params = [];

        if (!empty($branch)) {
            $query .= " AND branch = ?";
            $params[] = $branch;
        }
        if (!empty($semester)) {
            $query .= " AND semester = ?";
            $params[] = $semester;
        }
        if (!empty($search_term)) {
            $query .= " AND (subject_code LIKE ? OR subject_name LIKE ?)";
            $params[] = "%$search_term%";
            $params[] = "%$search_term%";
        }

        $query .= " ORDER BY branch, semester, subject_name";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $page_title; ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
            <i class="fas fa-plus me-2"></i>Add New Subject
        </button>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="branch" class="form-label">Branch</label>
                    <select class="form-control" id="branch" name="branch">
                        <option value="">All Branches</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Electronics & Communication">Electronics & Communication</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="Electrical Engineering">Electrical Engineering</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-control" id="semester" name="semester">
                        <option value="">All Semesters</option>
                        <?php for($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="search_term" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Subject code or name">
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" name="search" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subjects List -->
    <?php if ($search_performed): ?>
        <?php if (empty($subjects)): ?>
            <div class="alert alert-info">No subjects found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Semester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($subject['branch']); ?></td>
                                <td><?php echo htmlspecialchars($subject['semester']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openEditModal(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['subject_name'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSubjectForm" action="add_subject.php" method="POST" enctype="multipart/form-data">
                    <!-- Basic Subject Information -->
                    <h6 class="mb-3">Basic Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="subject_code" class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                        </div>
                        <div class="col-md-6">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="branch" class="form-label">Branch</label>
                            <select class="form-control" id="branch" name="branch" required>
                                <option value="">Select Branch</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Electronics & Communication">Electronics & Communication</option>
                                <option value="Mechanical Engineering">Mechanical Engineering</option>
                                <option value="Civil Engineering">Civil Engineering</option>
                                <option value="Electrical Engineering">Electrical Engineering</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-control" id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <?php for($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">Select Category</option>
                                <option value="Programming">Programming</option>
                                <option value="Science">Science</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Humanities">Humanities</option>
                                <option value="General">General</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <!-- Syllabus -->
                    <h6 class="mb-3">Syllabus</h6>
                    <div class="mb-3">
                        <label for="syllabus_url" class="form-label">Syllabus URL (PDF)</label>
                        <input type="url" class="form-control" id="syllabus_url" name="syllabus_url" placeholder="Enter direct PDF URL">
                        <small class="text-muted">Enter a direct link to the PDF file (e.g., Google Drive, Dropbox public link)</small>
                    </div>

                    <!-- Practicals -->
                    <h6 class="mb-3">Practicals</h6>
                    <div id="practicalsList">
                        <div class="practical-item border rounded p-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label">Practical Title</label>
                                <input type="text" class="form-control" name="practical_titles[]">
                            </div>
                            <div class="questions-list">
                                <div class="question-item border-top pt-3 mt-3">
                                    <div class="mb-3">
                                        <label class="form-label">Question Text</label>
                                        <input type="text" class="form-control" name="practical_questions[0][0][text]">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="practical_questions[0][0][description]" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Code Solution</label>
                                        <textarea class="form-control" name="practical_questions[0][0][code_solution]" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addQuestionField(this, 0)">
                                <i class="fas fa-plus me-2"></i>Add Question
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addPracticalField()">
                        <i class="fas fa-plus me-2"></i>Add Another Practical
                    </button>

                    <!-- Study Materials -->
                    <h6 class="mb-3">Study Materials</h6>
                    <div id="materialsList">
                        <div class="material-item border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Unit Number</label>
                                    <input type="number" class="form-control" name="material_units[]" min="1" max="5" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Material Title</label>
                                    <input type="text" class="form-control" name="material_titles[]" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="material_descriptions[]" rows="2"></textarea>
                                </div>
                                <!-- PDF Files Section -->
                                <div class="col-md-12 mb-3">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">PDF Files</h6>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="addPdfUrlField(this)">
                                                <i class="fas fa-plus"></i> Add PDF Link
                                            </button>
                                        </div>
                                        <div class="card-body pdf-urls-container">
                                            <div class="pdf-url-field mb-3">
                                                <div class="input-group">
                                                    <input type="url" class="form-control" name="material_urls[0][]" placeholder="Enter direct PDF URL">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeUrlField(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted">Enter a direct link to the PDF file</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Video Links Section -->
                                <div class="col-md-12 mb-3">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">Video Links</h6>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="addVideoUrlField(this)">
                                                <i class="fas fa-plus"></i> Add Video Link
                                            </button>
                                        </div>
                                        <div class="card-body video-urls-container">
                                            <div class="video-url-field mb-3">
                                                <div class="input-group">
                                                    <input type="url" class="form-control" name="material_links[0][]" placeholder="Enter YouTube URL">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeUrlField(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted">Enter a YouTube video URL</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterialField(this)">
                                <i class="fas fa-trash me-2"></i>Remove Material
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addMaterialField()">
                        <i class="fas fa-plus me-2"></i>Add Another Material
                    </button>

                    <!-- Previous Papers -->
                    <h6 class="mb-3">Previous Year Papers</h6>
                    <div id="papersList">
                        <div class="paper-item border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Year</label>
                                    <input type="number" class="form-control" name="paper_years[]" min="2000" max="2099">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Session</label>
                                    <select class="form-control" name="paper_sessions[]">
                                        <option value="Summer">Summer</option>
                                        <option value="Winter">Winter</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Paper URL (PDF)</label>
                                    <input type="url" class="form-control" name="paper_urls[]" placeholder="Enter direct PDF URL">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addPaperField()">
                        <i class="fas fa-plus me-2"></i>Add Another Paper
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addSubjectForm" class="btn btn-primary">Add Subject</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Subject: <span id="editSubjectName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editSubjectId">
                <!-- Edit Options -->
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action" onclick="editPracticals()">
                        <i class="fas fa-code me-2"></i>Edit Practicals
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="editPreviousPapers()">
                        <i class="fas fa-file-alt me-2"></i>Edit Previous Year Papers
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="editStudyMaterials()">
                        <i class="fas fa-book me-2"></i>Edit Study Materials
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="editSyllabus()">
                        <i class="fas fa-file-pdf me-2"></i>Edit Syllabus
                    </a>
                    <a href="#" class="list-group-item list-group-item-action text-danger" onclick="deleteSubject()">
                        <i class="fas fa-trash me-2"></i>Delete Subject
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Syllabus Modal -->
<div class="modal fade" id="syllabusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Syllabus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="syllabusForm">
                    <input type="hidden" id="syllabus_subject_code" name="subject_code">
                    <input type="hidden" id="syllabus_subject_name" name="subject_name">
                    <input type="hidden" id="syllabus_branch" name="branch">
                    <input type="hidden" id="syllabus_semester" name="semester">
                    <div class="mb-3">
                        <label for="academic_year" class="form-label">Academic Year</label>
                        <input type="text" class="form-control" id="academic_year" name="academic_year" required>
                        <small class="text-muted">Enter academic year (e.g., 2023-24)</small>
                    </div>
                    <div class="mb-3">
                        <label for="syllabus_url" class="form-label">Syllabus URL (PDF)</label>
                        <input type="url" class="form-control" id="syllabus_url" name="file_path" required>
                        <small class="text-muted">Enter a direct link to the PDF file</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSyllabus()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Previous Papers Modal -->
<div class="modal fade" id="papersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Previous Papers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="papersForm">
                    <input type="hidden" id="papers_subject_id" name="subject_id">
                    <div id="editPapersList">
                        <!-- Papers will be loaded dynamically -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="addEditPaperField()">
                        <i class="fas fa-plus me-2"></i>Add Paper
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="savePapers()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>What would you like to delete?</p>
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="deleteComponent('practicals')">
                        <i class="fas fa-code me-2"></i>Delete Practicals
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="deleteComponent('papers')">
                        <i class="fas fa-file-alt me-2"></i>Delete Previous Year Papers
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="deleteComponent('materials')">
                        <i class="fas fa-book me-2"></i>Delete Study Materials
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="deleteComponent('syllabus')">
                        <i class="fas fa-file-pdf me-2"></i>Delete Syllabus
                    </button>
                    <button type="button" class="list-group-item list-group-item-action text-danger" onclick="deleteComponent('subject')">
                        <i class="fas fa-trash me-2"></i>Delete Entire Subject
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        new bootstrap.Dropdown(dropdown, {
            boundary: 'window'
        });
    });

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });

    // Initialize popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });

    // Initialize modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });
});

function addMaterialField() {
    const materialsList = document.getElementById('materialsList');
    const materialCount = materialsList.children.length;
    const newItem = document.createElement('div');
    newItem.className = 'material-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label">Unit Number</label>
                <input type="number" class="form-control" name="material_units[]" min="1" max="5" required>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Material Title</label>
                <input type="text" class="form-control" name="material_titles[]" required>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="material_descriptions[]" rows="2"></textarea>
            </div>
            <!-- PDF Files Section -->
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">PDF Files</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addPdfUrlField(this)">
                            <i class="fas fa-plus"></i> Add PDF Link
                        </button>
                    </div>
                    <div class="card-body pdf-urls-container">
                        <div class="pdf-url-field mb-3">
                            <div class="input-group">
                                <input type="url" class="form-control" name="material_urls[${materialCount}][]" placeholder="Enter direct PDF URL">
                                <button type="button" class="btn btn-outline-danger" onclick="removeUrlField(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <small class="text-muted">Enter a direct link to the PDF file</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Video Links Section -->
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Video Links</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addVideoUrlField(this)">
                            <i class="fas fa-plus"></i> Add Video Link
                        </button>
                    </div>
                    <div class="card-body video-urls-container">
                        <div class="video-url-field mb-3">
                            <div class="input-group">
                                <input type="url" class="form-control" name="material_links[${materialCount}][]" placeholder="Enter YouTube URL">
                                <button type="button" class="btn btn-outline-danger" onclick="removeUrlField(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <small class="text-muted">Enter a YouTube video URL</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterialField(this)">
            <i class="fas fa-trash me-2"></i>Remove Material
        </button>
    `;
    materialsList.appendChild(newItem);
}

function addPdfUrlField(button) {
    const container = button.closest('.card').querySelector('.pdf-urls-container');
    const materialItem = button.closest('.material-item');
    const materialIndex = Array.from(document.getElementById('materialsList').children).indexOf(materialItem);
    
    const newField = document.createElement('div');
    newField.className = 'pdf-url-field mb-3';
    newField.innerHTML = `
        <div class="input-group">
            <input type="url" class="form-control" name="material_urls[${materialIndex}][]" placeholder="Enter direct PDF URL">
            <button type="button" class="btn btn-outline-danger" onclick="removeUrlField(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <small class="text-muted">Enter a direct link to the PDF file</small>
    `;
    container.appendChild(newField);
}

function addVideoUrlField(button) {
    const container = button.closest('.card').querySelector('.video-urls-container');
    const materialItem = button.closest('.material-item');
    const materialIndex = Array.from(document.getElementById('materialsList').children).indexOf(materialItem);
    
    const newField = document.createElement('div');
    newField.className = 'video-url-field mb-3';
    newField.innerHTML = `
        <div class="input-group">
            <input type="url" class="form-control" name="material_links[${materialIndex}][]" placeholder="Enter YouTube URL">
            <button type="button" class="btn btn-outline-danger" onclick="removeUrlField(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <small class="text-muted">Enter a YouTube video URL</small>
    `;
    container.appendChild(newField);
}

function removeUrlField(button) {
    const field = button.closest('.pdf-url-field, .video-url-field');
    field.remove();
}

function removeMaterialField(button) {
    button.closest('.material-item').remove();
}

function addQuestionField(button, practicalIndex) {
    const questionsList = button.closest('.practical-item').querySelector('.questions-list');
    const questionCount = questionsList.children.length;
    
    const newQuestion = document.createElement('div');
    newQuestion.className = 'question-item border-top pt-3 mt-3';
    newQuestion.innerHTML = `
        <div class="mb-3">
            <label class="form-label">Question Text</label>
            <input type="text" class="form-control" name="practical_questions[${practicalIndex}][${questionCount}][text]">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="practical_questions[${practicalIndex}][${questionCount}][description]" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Code Solution</label>
            <textarea class="form-control" name="practical_questions[${practicalIndex}][${questionCount}][code_solution]" rows="3"></textarea>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestionField(this)">
            <i class="fas fa-trash me-2"></i>Remove Question
        </button>
    `;
    questionsList.appendChild(newQuestion);
}

function removeQuestionField(button) {
    button.closest('.question-item').remove();
}

function addPracticalField() {
    const practicalsList = document.getElementById('practicalsList');
    const practicalIndex = practicalsList.children.length;
    
    const newPractical = document.createElement('div');
    newPractical.className = 'practical-item border rounded p-3 mb-3';
    newPractical.innerHTML = `
        <div class="mb-3">
            <label class="form-label">Practical Title</label>
            <input type="text" class="form-control" name="practical_titles[]">
        </div>
        <div class="questions-list">
            <div class="question-item border-top pt-3 mt-3">
                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <input type="text" class="form-control" name="practical_questions[${practicalIndex}][0][text]">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="practical_questions[${practicalIndex}][0][description]" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Code Solution</label>
                    <textarea class="form-control" name="practical_questions[${practicalIndex}][0][code_solution]" rows="3"></textarea>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addQuestionField(this, ${practicalIndex})">
            <i class="fas fa-plus me-2"></i>Add Question
        </button>
        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removePracticalField(this)">
            <i class="fas fa-trash me-2"></i>Remove Practical
        </button>
    `;
    practicalsList.appendChild(newPractical);
}

function removePracticalField(button) {
    button.closest('.practical-item').remove();
}

function addPaperField() {
    const papersList = document.getElementById('papersList');
    const newPaper = document.createElement('div');
    newPaper.className = 'paper-item border rounded p-3 mb-3';
    newPaper.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Year</label>
                <input type="number" class="form-control" name="paper_years[]" min="2000" max="2099">
            </div>
            <div class="col-md-4">
                <label class="form-label">Session</label>
                <select class="form-control" name="paper_sessions[]">
                    <option value="Summer">Summer</option>
                    <option value="Winter">Winter</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Paper URL (PDF)</label>
                <input type="url" class="form-control" name="paper_urls[]" placeholder="Enter direct PDF URL">
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removePaperField(this)">
            <i class="fas fa-trash me-2"></i>Remove Paper
        </button>
    `;
    papersList.appendChild(newPaper);
}

function removePaperField(button) {
    button.closest('.paper-item').remove();
}

function openEditModal(subjectId, subjectName) {
    document.getElementById('editSubjectId').value = subjectId;
    document.getElementById('editSubjectName').textContent = subjectName;
    new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
}

function editPracticals() {
    const subjectId = document.getElementById('editSubjectId').value;
    window.location.href = `view_practicals.php?subject_id=${subjectId}`;
}

function editPreviousPapers() {
    const subjectId = document.getElementById('editSubjectId').value;
    window.location.href = `view_papers.php?subject_id=${subjectId}`;
}

function editStudyMaterials() {
    const subjectId = document.getElementById('editSubjectId').value;
    window.location.href = `view_materials.php?subject_id=${subjectId}`;
}

function editSyllabus() {
    const subjectId = document.getElementById('editSubjectId').value;
    
    // Get subject details first
    fetch(`ajax/get_subject.php?id=${subjectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const subject = data.subject;
                
                // Set hidden fields
                document.getElementById('syllabus_subject_code').value = subject.subject_code;
                document.getElementById('syllabus_subject_name').value = subject.subject_name;
                document.getElementById('syllabus_branch').value = subject.branch;
                document.getElementById('syllabus_semester').value = subject.semester;
                
                // Get syllabus details
                fetch(`ajax/get_syllabus.php?subject_code=${subject.subject_code}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const syllabus = data.syllabus;
                            document.getElementById('academic_year').value = syllabus.academic_year;
                            document.getElementById('syllabus_url').value = syllabus.file_path;
                        } else {
                            // If no syllabus exists, clear the fields
                            document.getElementById('academic_year').value = '';
                            document.getElementById('syllabus_url').value = '';
                        }
                        
                        // Show the modal
                        new bootstrap.Modal(document.getElementById('syllabusModal')).show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load syllabus details');
                    });
            } else {
                alert('Failed to load subject details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load subject details');
        });
}

function saveSyllabus() {
    const form = document.getElementById('syllabusForm');
    const formData = new FormData(form);
    
    fetch('ajax/update_syllabus.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Syllabus updated successfully');
            bootstrap.Modal.getInstance(document.getElementById('syllabusModal')).hide();
        } else {
            alert('Failed to update syllabus: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update syllabus');
    });
}

function deleteSubject() {
    const subjectId = document.getElementById('editSubjectId').value;
    const subjectName = document.getElementById('editSubjectName').textContent;
    
    // Close the edit modal first
    bootstrap.Modal.getInstance(document.getElementById('editSubjectModal')).hide();
    
    // Show the delete options modal
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function deleteComponent(component) {
    const subjectId = document.getElementById('editSubjectId').value;
    const subjectName = document.getElementById('editSubjectName').textContent;
    
    let confirmMessage = '';
    let endpoint = '';
    
    switch(component) {
        case 'practicals':
            confirmMessage = `Are you sure you want to delete all practicals for "${subjectName}"?`;
            endpoint = 'ajax/delete_practicals.php';
            break;
        case 'papers':
            confirmMessage = `Are you sure you want to delete all previous year papers for "${subjectName}"?`;
            endpoint = 'ajax/delete_papers.php';
            break;
        case 'materials':
            confirmMessage = `Are you sure you want to delete all study materials for "${subjectName}"?`;
            endpoint = 'ajax/delete_materials.php';
            break;
        case 'syllabus':
            confirmMessage = `Are you sure you want to delete the syllabus for "${subjectName}"?`;
            endpoint = 'ajax/delete_syllabus.php';
            break;
        case 'subject':
            confirmMessage = `Are you sure you want to delete the entire subject "${subjectName}"? This will delete all associated data including practicals, papers, materials, and syllabus.`;
            endpoint = 'remove.php';
            break;
    }

    if (confirm(confirmMessage)) {
        // Close the delete options modal
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        
        // Send AJAX request to delete the component
        fetch(`${endpoint}?id=${subjectId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Deleted successfully');
                if (component === 'subject') {
                    window.location.reload();
                }
            } else {
                alert('Error deleting: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting. Please try again.');
        });
    }
}

// Add form submission handler
document.getElementById('addSubjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('add_subject.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('addSubjectModal')).hide();
            
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show';
            successAlert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert alert before the search form
            const searchForm = document.querySelector('.card.mb-4');
            searchForm.parentNode.insertBefore(successAlert, searchForm);
            
            // Reload the page after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the subject.');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html> 