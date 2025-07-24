<?php
require_once '../config/database.php';
include 'includes/header.php';

// Get all branches and semesters for filters
$branches = ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical'];
$semesters = range(1, 8);

// Get subjects if branch and semester are selected
$selected_branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$selected_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$selected_subject = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

$subjects = [];
if ($selected_branch && $selected_semester) {
    $query = "SELECT id, subject_name, subject_code FROM subjects 
              WHERE branch = :branch AND semester = :semester 
              ORDER BY subject_name ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':branch', $selected_branch);
    $stmt->bindParam(':semester', $selected_semester);
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get practicals if subject is selected
$practicals = [];
if ($selected_subject) {
    $query = "SELECT p.*, s.subject_name, s.subject_code 
              FROM practicals p
              JOIN subjects s ON p.subject_id = s.id
              WHERE p.subject_id = :subject_id 
              ORDER BY p.practical_number ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':subject_id', $selected_subject);
    $stmt->execute();
    $practicals = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Practicals</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Manage Practicals</li>
    </ol>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="branch" class="form-label">Branch</label>
                    <select class="form-select" id="branch" name="branch" required>
                        <option value="">Select Branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch; ?>" <?php echo $selected_branch === $branch ? 'selected' : ''; ?>>
                                <?php echo $branch; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-select" id="semester" name="semester" required>
                        <option value="">Select Semester</option>
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?php echo $sem; ?>" <?php echo $selected_semester == $sem ? 'selected' : ''; ?>>
                                Semester <?php echo $sem; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="subject" class="form-label">Subject</label>
                    <select class="form-select" id="subject" name="subject_id" <?php echo empty($subjects) ? 'disabled' : ''; ?>>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo $selected_subject == $subject['id'] ? 'selected' : ''; ?>>
                                <?php echo $subject['subject_name'] . ' (' . $subject['subject_code'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected_subject): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Practicals List
                </div>
                <a href="add_practical.php?subject_id=<?php echo $selected_subject; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Add New Practical
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($practicals)): ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Practical No.</th>
                                <th>Title</th>
                                <th>File</th>
                                <th>Added Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($practicals as $practical): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($practical['practical_number']); ?></td>
                                    <td><?php echo htmlspecialchars($practical['title']); ?></td>
                                    <td>
                                        <?php if (!empty($practical['file_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($practical['file_path']); ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($practical['created_at'])); ?></td>
                                    <td>
                                        <a href="edit_practical.php?id=<?php echo $practical['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deletePractical(<?php echo $practical['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        No practicals found for this subject. <a href="add_practical.php?subject_id=<?php echo $selected_subject; ?>">Add one now</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function deletePractical(id) {
    if (confirm('Are you sure you want to delete this practical?')) {
        window.location.href = `delete_practical.php?id=${id}&subject_id=<?php echo $selected_subject; ?>`;
    }
}

// Dynamic subject loading
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branch');
    const semesterSelect = document.getElementById('semester');
    const subjectSelect = document.getElementById('subject');

    function updateSubjects() {
        const branch = branchSelect.value;
        const semester = semesterSelect.value;

        if (branch && semester) {
            fetch(`../ajax/get_subjects.php?branch=${encodeURIComponent(branch)}&semester=${encodeURIComponent(semester)}`)
                .then(response => response.json())
                .then(data => {
                    subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                    data.forEach(subject => {
                        subjectSelect.innerHTML += `
                            <option value="${subject.id}">
                                ${subject.subject_name} (${subject.subject_code})
                            </option>
                        `;
                    });
                    subjectSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    subjectSelect.disabled = true;
                });
        } else {
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            subjectSelect.disabled = true;
        }
    }

    branchSelect.addEventListener('change', updateSubjects);
    semesterSelect.addEventListener('change', updateSubjects);
});
</script>

<?php include 'includes/footer.php'; ?> 