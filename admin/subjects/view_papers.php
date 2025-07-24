<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get subject ID from URL
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if (!$subject_id) {
    header('Location: manage.php');
    exit;
}

// Get subject details
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subject) {
        header('Location: manage.php');
        exit;
    }

    // Get previous papers for this subject
    $stmt = $conn->prepare("SELECT * FROM previous_papers WHERE subject_code = ? ORDER BY exam_year DESC, exam_session");
    $stmt->execute([$subject['subject_code']]);
    $papers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error_msg = "Error: " . $e->getMessage();
}

$page_title = "Previous Year Papers - " . $subject['subject_name'];
include '../includes/header.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $page_title; ?></h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaperModal">
            <i class="fas fa-plus me-2"></i>Add New Paper
        </button>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
    <?php endif; ?>

    <!-- Papers List -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Session</th>
                    <th>Paper URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($papers as $paper): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($paper['exam_year']); ?></td>
                        <td><?php echo htmlspecialchars($paper['exam_session']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($paper['file_path']); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i>View Paper
                            </a>
                        </td>
                        <td>
                            <button type="button" 
                                    class="btn btn-sm btn-primary me-2" 
                                    onclick="editPaper(<?php echo $paper['id']; ?>)">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-danger" 
                                    onclick="deletePaper(<?php echo $paper['id']; ?>)">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add Paper Modal -->
<div class="modal fade" id="addPaperModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Paper</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPaperForm" action="ajax/add_paper.php" method="POST">
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                    <input type="hidden" name="subject_code" value="<?php echo $subject['subject_code']; ?>">
                    <input type="hidden" name="subject_name" value="<?php echo $subject['subject_name']; ?>">
                    <input type="hidden" name="branch" value="<?php echo $subject['branch']; ?>">
                    <input type="hidden" name="semester" value="<?php echo $subject['semester']; ?>">
                    <div class="mb-3">
                        <label for="exam_year" class="form-label">Year</label>
                        <input type="number" class="form-control" id="exam_year" name="exam_year" min="2000" max="2099" required>
                    </div>
                    <div class="mb-3">
                        <label for="exam_session" class="form-label">Session</label>
                        <select class="form-control" id="exam_session" name="exam_session" required>
                            <option value="Summer">Summer</option>
                            <option value="Winter">Winter</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file_path" class="form-label">Paper URL (PDF)</label>
                        <input type="url" class="form-control" id="file_path" name="file_path" required>
                        <small class="text-muted">Enter a direct link to the PDF file</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addPaperForm" class="btn btn-primary">Add Paper</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Paper Modal -->
<div class="modal fade" id="editPaperModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Paper</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPaperForm" action="ajax/update_paper.php" method="POST">
                    <input type="hidden" id="edit_paper_id" name="paper_id">
                    <div class="mb-3">
                        <label for="edit_exam_year" class="form-label">Year</label>
                        <input type="number" class="form-control" id="edit_exam_year" name="exam_year" min="2000" max="2099" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_exam_session" class="form-label">Session</label>
                        <select class="form-control" id="edit_exam_session" name="exam_session" required>
                            <option value="Summer">Summer</option>
                            <option value="Winter">Winter</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_file_path" class="form-label">Paper URL (PDF)</label>
                        <input type="url" class="form-control" id="edit_file_path" name="file_path" required>
                        <small class="text-muted">Enter a direct link to the PDF file</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editPaperForm" class="btn btn-primary">Update Paper</button>
            </div>
        </div>
    </div>
</div>

<script>
function editPaper(paperId) {
    // Fetch paper details
    fetch(`ajax/get_paper.php?id=${paperId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const paper = data.paper;
                document.getElementById('edit_paper_id').value = paper.id;
                document.getElementById('edit_exam_year').value = paper.exam_year;
                document.getElementById('edit_exam_session').value = paper.exam_session;
                document.getElementById('edit_file_path').value = paper.file_path;
                
                // Show the modal
                new bootstrap.Modal(document.getElementById('editPaperModal')).show();
            } else {
                alert('Failed to load paper details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load paper details');
        });
}

function deletePaper(paperId) {
    if (confirm('Are you sure you want to delete this paper?')) {
        fetch(`ajax/delete_paper.php?id=${paperId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Failed to delete paper: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete paper');
            });
    }
}

// Handle form submissions
document.getElementById('addPaperForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to add paper: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add paper');
    });
});

document.getElementById('editPaperForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to update paper: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update paper');
    });
});
</script>

<?php include '../includes/footer.php'; ?> 