<?php
require_once 'config/database.php';

// Get all subjects
$query = "SELECT * FROM subjects ORDER BY subject_name";
$stmt = $conn->prepare($query);
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
include 'includes/header.php';
?>

<!-- Add specific CSS for subjects page -->
<style>
body {
    padding-top: 0 !important; /* Override any default padding */
}
.navbar {
    margin-bottom: 2rem; /* Add some space between navbar and content */
}
</style>

<div class="container">
    <h2 class="mb-4">Subjects</h2>
    
    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <div class="input-group">
                <input type="text" class="form-control" id="subjectSearch" placeholder="Search subjects...">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Subjects Grid -->
    <div class="row">
        <?php foreach($subjects as $subject): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                    <p class="card-text">
                        <small class="text-muted">Code: <?php echo htmlspecialchars($subject['subject_code']); ?></small>
                    </p>
                    <?php if(isset($subject['description'])): ?>
                        <p class="card-text"><?php echo htmlspecialchars($subject['description']); ?></p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#subjectModal"
                                data-subject-name="<?php echo htmlspecialchars($subject['subject_name']); ?>"
                                data-subject-code="<?php echo htmlspecialchars($subject['subject_code']); ?>"
                                data-branch="<?php echo htmlspecialchars($subject['branch']); ?>"
                                data-semester="<?php echo htmlspecialchars($subject['semester']); ?>"
                                data-subject-id="<?php echo htmlspecialchars($subject['id']); ?>">
                            View Options
                        </button>
                        <?php if(isset($subject['category'])): ?>
                            <span class="badge bg-info">
                                <?php echo htmlspecialchars($subject['category']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Subject Options Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subject Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="subject-name mb-3"></h6>
                <div class="d-grid gap-3">
                    <a href="#" class="btn btn-primary practicals-link">
                        <i class="fas fa-laptop-code"></i> Practicals
                    </a>
                    <a href="#" class="btn btn-success syllabus-link">
                        <i class="fas fa-book"></i> Syllabus
                    </a>
                    <a href="#" class="btn btn-info papers-link">
                        <i class="fas fa-file-alt"></i> Previous Year Papers
                    </a>
                    <a href="#" class="btn btn-warning materials-link">
                        <i class="fas fa-graduation-cap"></i> Study Materials
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Subject search functionality
    const searchInput = document.getElementById('subjectSearch');
    searchInput.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const cards = document.querySelectorAll('.card');
        
        cards.forEach(card => {
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
            const code = card.querySelector('.text-muted')?.textContent.toLowerCase() || '';
            
            if (title.includes(searchText) || description.includes(searchText) || code.includes(searchText)) {
                card.closest('.col-md-4').style.display = '';
            } else {
                card.closest('.col-md-4').style.display = 'none';
            }
        });
    });

    // Modal functionality
    const subjectModal = document.getElementById('subjectModal');
    subjectModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const subjectName = button.getAttribute('data-subject-name');
        const subjectCode = button.getAttribute('data-subject-code');
        const branch = button.getAttribute('data-branch');
        const semester = button.getAttribute('data-semester');
        
        // Update modal title
        this.querySelector('.subject-name').textContent = subjectName + ' (' + subjectCode + ')';
        
        // Update links with proper parameters
        const baseParams = `?branch=${encodeURIComponent(branch)}&semester=${encodeURIComponent(semester)}&subject=${encodeURIComponent(subjectCode)}`;
        
        this.querySelector('.practicals-link').href = 'practicals.php?subject_id=' + button.getAttribute('data-subject-id');
        this.querySelector('.syllabus-link').href = 'syllabus.php' + baseParams;
        this.querySelector('.papers-link').href = 'previous_papers.php' + baseParams;
        this.querySelector('.materials-link').href = 'study_materials.php' + baseParams;
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?> 