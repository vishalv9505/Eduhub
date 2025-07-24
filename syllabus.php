<?php
require_once 'config/database.php';

$branches = ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical', 'Civil'];
$semesters = range(1, 8);

$selected_branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

$syllabi = [];
if ($selected_branch && $selected_semester) {
    $query = "SELECT * FROM syllabus WHERE branch = :branch AND semester = :semester ORDER BY subject_code";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':branch', $selected_branch);
    $stmt->bindParam(':semester', $selected_semester);
    $stmt->execute();
    $syllabi = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include header
include 'includes/header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Subject Syllabus</h2>
    
    <!-- Selection Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="branch" class="form-label">Select Branch</label>
                    <select class="form-select" id="branch" name="branch" required>
                        <option value="">Choose Branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch; ?>" <?php echo $selected_branch === $branch ? 'selected' : ''; ?>>
                                <?php echo $branch; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="semester" class="form-label">Select Semester</label>
                    <select class="form-select" id="semester" name="semester" required>
                        <option value="">Choose Semester</option>
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?php echo $sem; ?>" <?php echo $selected_semester == $sem ? 'selected' : ''; ?>>
                                Semester <?php echo $sem; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Search Syllabus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Syllabus Table -->
    <?php if (!empty($syllabi)): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Academic Year</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($syllabi as $syllabus): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($syllabus['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($syllabus['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($syllabus['academic_year']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($syllabus['file_path']); ?>" 
                                   class="btn btn-sm btn-primary" 
                                   target="_blank">
                                    Download
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($selected_branch && $selected_semester): ?>
        <div class="alert alert-info">
            No syllabus found for the selected branch and semester.
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?> 