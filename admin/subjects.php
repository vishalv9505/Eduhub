<?php
require_once 'includes/functions.php';
checkAdminLogin();

$page_title = 'Manage Subjects';

// Get search parameters
$branch = $_GET['branch'] ?? '';
$semester = $_GET['semester'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT * FROM subjects WHERE 1=1";
$params = [];

if ($branch) {
    $query .= " AND branch = ?";
    $params[] = $branch;
}

if ($semester) {
    $query .= " AND semester = ?";
    $params[] = $semester;
}

if ($search) {
    $query .= " AND (subject_name LIKE ? OR subject_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY branch, semester, subject_name";

// Execute query
$conn = getDBConnection();
$stmt = $conn->prepare($query);
$stmt->execute($params);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Subjects List</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                <i class="fas fa-plus"></i> Add Subject
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="branch" class="form-label">Branch</label>
                <select class="form-select" id="branch" name="branch">
                    <option value="">All Branches</option>
                    <option value="CS" <?php echo $branch === 'CS' ? 'selected' : ''; ?>>Computer Science</option>
                    <option value="IT" <?php echo $branch === 'IT' ? 'selected' : ''; ?>>Information Technology</option>
                    <option value="EC" <?php echo $branch === 'EC' ? 'selected' : ''; ?>>Electronics & Communication</option>
                    <option value="ME" <?php echo $branch === 'ME' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                    <option value="CE" <?php echo $branch === 'CE' ? 'selected' : ''; ?>>Civil Engineering</option>
                    <option value="EE" <?php echo $branch === 'EE' ? 'selected' : ''; ?>>Electrical Engineering</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="semester" class="form-label">Semester</label>
                <select class="form-select" id="semester" name="semester">
                    <option value="">All Semesters</option>
                    <option value="1" <?php echo $semester === '1' ? 'selected' : ''; ?>>Semester 1</option>
                    <option value="2" <?php echo $semester === '2' ? 'selected' : ''; ?>>Semester 2</option>
                    <option value="3" <?php echo $semester === '3' ? 'selected' : ''; ?>>Semester 3</option>
                    <option value="4" <?php echo $semester === '4' ? 'selected' : ''; ?>>Semester 4</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or code">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>

        <!-- Subjects Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
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
                            <td><?php echo getBranchName($subject['branch']); ?></td>
                            <td><?php echo getSemesterName($subject['semester']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="editSubject(<?php echo $subject['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteSubject(<?php echo $subject['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSubjectForm" action="ajax/add_subject.php" method="POST" data-ajax="true">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subject_code" class="form-label">Subject Code</label>
                        <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject_name" class="form-label">Subject Name</label>
                        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="branch" class="form-label">Branch</label>
                        <select class="form-select" id="branch" name="branch" required>
                            <option value="">Select Branch</option>
                            <option value="CS">Computer Science</option>
                            <option value="IT">Information Technology</option>
                            <option value="EC">Electronics & Communication</option>
                            <option value="ME">Mechanical Engineering</option>
                            <option value="CE">Civil Engineering</option>
                            <option value="EE">Electrical Engineering</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester</label>
                        <select class="form-select" id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            <option value="3">Semester 3</option>
                            <option value="4">Semester 4</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSubjectForm" action="ajax/update_subject.php" method="POST" data-ajax="true">
                <input type="hidden" id="edit_subject_id" name="subject_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_subject_code" class="form-label">Subject Code</label>
                        <input type="text" class="form-control" id="edit_subject_code" name="subject_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_subject_name" class="form-label">Subject Name</label>
                        <input type="text" class="form-control" id="edit_subject_name" name="subject_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_branch" class="form-label">Branch</label>
                        <select class="form-select" id="edit_branch" name="branch" required>
                            <option value="">Select Branch</option>
                            <option value="CS">Computer Science</option>
                            <option value="IT">Information Technology</option>
                            <option value="EC">Electronics & Communication</option>
                            <option value="ME">Mechanical Engineering</option>
                            <option value="CE">Civil Engineering</option>
                            <option value="EE">Electrical Engineering</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_semester" class="form-label">Semester</label>
                        <select class="form-select" id="edit_semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            <option value="3">Semester 3</option>
                            <option value="4">Semester 4</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Subject Modal -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this subject? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteSubjectForm" action="ajax/delete_subject.php" method="POST" data-ajax="true" class="d-inline">
                    <input type="hidden" id="delete_subject_id" name="subject_id">
                    <button type="submit" class="btn btn-danger">Delete Subject</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editSubject(subjectId) {
    // Fetch subject details
    fetch(`ajax/get_subject.php?id=${subjectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const subject = data.subject;
                document.getElementById('edit_subject_id').value = subject.id;
                document.getElementById('edit_subject_code').value = subject.subject_code;
                document.getElementById('edit_subject_name').value = subject.subject_name;
                document.getElementById('edit_branch').value = subject.branch;
                document.getElementById('edit_semester').value = subject.semester;
                
                // Show modal
                new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            showAlert('An error occurred while fetching subject details.', 'danger');
        });
}

function deleteSubject(subjectId) {
    document.getElementById('delete_subject_id').value = subjectId;
    new bootstrap.Modal(document.getElementById('deleteSubjectModal')).show();
}
</script>

<?php
$page_content = ob_get_clean();

// Include header and footer
include 'includes/header.php';
include 'includes/container.php';
include 'includes/footer.php';
?> 