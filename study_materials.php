<?php
require_once 'config/database.php';

$branches = ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical', 'Civil'];
$semesters = range(1, 8);

$selected_branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

$subjects = [];
if ($selected_branch && $selected_semester) {
    $query = "SELECT * FROM subjects WHERE branch = :branch AND semester = :semester ORDER BY subject_name";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':branch', $selected_branch);
    $stmt->bindParam(':semester', $selected_semester);
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4">Study Materials</h2>
            
            <!-- Branch and Semester Selection -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label for="branch" class="form-label">Select Branch</label>
                            <select class="form-select" id="branch" name="branch" required>
                                <option value="">Choose Branch</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo htmlspecialchars($branch); ?>" 
                                            <?php echo $selected_branch === $branch ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($branch); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="semester" class="form-label">Select Semester</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">Choose Semester</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?php echo $sem; ?>" 
                                            <?php echo $selected_semester == $sem ? 'selected' : ''; ?>>
                                        Semester <?php echo $sem; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects Grid -->
    <?php if ($selected_branch && $selected_semester): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($subjects as $subject): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted">Code: <?php echo htmlspecialchars($subject['subject_code']); ?></small>
                            </p>
                            <?php if(isset($subject['description'])): ?>
                                <p class="card-text"><?php echo htmlspecialchars($subject['description']); ?></p>
                            <?php endif; ?>
                            <a href="view_materials.php?subject_id=<?php echo $subject['id']; ?>" 
                               class="btn btn-primary">
                                View Materials
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($selected_branch || $selected_semester): ?>
        <div class="alert alert-info">
            Please select both branch and semester to view subjects.
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 