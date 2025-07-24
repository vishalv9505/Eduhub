<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['subject_id'])) {
    header('Location: manage.php');
    exit;
}

$subject_id = sanitize_input($_GET['subject_id']);
$success_msg = '';
$error_msg = '';

try {
    $conn = getDBConnection();
    
    // Get subject details
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        header('Location: manage.php');
        exit;
    }
    
    // Get all practicals with their questions in a single query
    $stmt = $conn->prepare("
        SELECT p.*, 
               pq.id as question_id,
               pq.question_number,
               pq.question_text,
               pq.description,
               pq.code_solution
        FROM practicals p
        LEFT JOIN practical_questions pq ON p.id = pq.practical_id
        WHERE p.subject_id = ?
        ORDER BY p.practical_number, pq.question_number
    ");
    $stmt->execute([$subject_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process the results to group questions by practical
    $practicals = [];
    foreach ($results as $row) {
        if (!isset($practicals[$row['id']])) {
            $practicals[$row['id']] = [
                'id' => $row['id'],
                'practical_number' => $row['practical_number'],
                'title' => $row['title'],
                'questions' => []
            ];
        }
        
        if ($row['question_id']) {
            $practicals[$row['id']]['questions'][] = [
                'id' => $row['question_id'],
                'question_number' => $row['question_number'],
                'question_text' => $row['question_text'],
                'description' => $row['description'],
                'code_solution' => $row['code_solution']
            ];
        }
    }
    
    // Convert associative array to indexed array
    $practicals = array_values($practicals);
    
} catch(PDOException $e) {
    $error_msg = "Failed to load practicals data: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title mb-0">
                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                </h3>
                <p class="text-muted mb-0">
                    <?php echo htmlspecialchars($subject['branch']); ?> - 
                    Semester <?php echo $subject['semester']; ?>
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addPracticalModal">
                    <i class="fas fa-plus me-2"></i>Add Practical
                </button>
                <a href="manage.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Subjects
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($practicals)): ?>
                <div class="alert alert-info">No practicals found for this subject.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($practicals as $practical): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <?php echo htmlspecialchars($practical['title']); ?>
                                    </h5>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="editPractical(<?php echo $practical['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deletePractical(<?php echo $practical['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($practical['questions'])): ?>
                                        <p class="text-muted">No questions added for this practical.</p>
                                    <?php else: ?>
                                        <div class="accordion" id="practical<?php echo $practical['id']; ?>">
                                            <?php foreach ($practical['questions'] as $index => $question): ?>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" 
                                                                type="button" 
                                                                data-bs-toggle="collapse" 
                                                                data-bs-target="#question<?php echo $practical['id'] . '_' . $index; ?>"
                                                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                                                onclick="handleAccordionClick(event, this)">
                                                            Question <?php echo $question['question_number']; ?>
                                                        </button>
                                                    </h2>
                                                    <div id="question<?php echo $practical['id'] . '_' . $index; ?>" 
                                                         class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                                         data-bs-parent="#practical<?php echo $practical['id']; ?>">
                                                        <div class="accordion-body">
                                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>
                                                            
                                                            <?php if (!empty($question['description'])): ?>
                                                                <div class="mb-3">
                                                                    <strong>Description:</strong>
                                                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($question['description'])); ?></p>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (!empty($question['code_solution'])): ?>
                                                                <div class="mb-0">
                                                                    <strong>Code Solution:</strong>
                                                                    <pre class="bg-light p-3 rounded mt-2"><code><?php echo htmlspecialchars($question['code_solution']); ?></code></pre>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Practical Modal -->
<div class="modal fade" id="addPracticalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Practical</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPracticalForm" action="ajax/save_practical.php" method="POST">
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                    <div class="mb-3">
                        <label for="practical_number" class="form-label">Practical Number</label>
                        <input type="number" class="form-control" id="practical_number" name="practical_number" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div id="questionsList">
                        <h6 class="mb-3">Questions</h6>
                        <div class="question-item border rounded p-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label">Question Number</label>
                                <input type="number" class="form-control" name="questions[0][number]" required min="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Question Text</label>
                                <input type="text" class="form-control" name="questions[0][text]" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="questions[0][description]" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Code Solution</label>
                                <textarea class="form-control" name="questions[0][code_solution]" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addQuestionField()">
                        <i class="fas fa-plus me-2"></i>Add Question
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addPracticalForm" class="btn btn-primary">Save Practical</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Practical Modal -->
<div class="modal fade" id="editPracticalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Practical</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPracticalForm" action="ajax/update_practical.php" method="POST">
                    <input type="hidden" name="practical_id" id="edit_practical_id">
                    <div class="mb-3">
                        <label for="edit_practical_number" class="form-label">Practical Number</label>
                        <input type="number" class="form-control" id="edit_practical_number" name="practical_number" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div id="editQuestionsList">
                        <h6 class="mb-3">Questions</h6>
                        <!-- Questions will be loaded dynamically -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addEditQuestionField()">
                        <i class="fas fa-plus me-2"></i>Add Question
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editPracticalForm" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
let questionCounter = 1;
let editQuestionCounter = 1;

function handleAccordionClick(e, button) {
    e.preventDefault();
    e.stopPropagation();
    
    const target = document.querySelector(button.getAttribute('data-bs-target'));
    if (target) {
        const isExpanded = button.getAttribute('aria-expanded') === 'true';
        console.log('Current state:', isExpanded ? 'expanded' : 'collapsed');
        
        // Toggle the button state
        button.setAttribute('aria-expanded', !isExpanded);
        button.classList.toggle('collapsed');
        
        // Toggle the content
        if (!isExpanded) {
            // Close all other accordion items in the same accordion
            const parentAccordion = button.closest('.accordion');
            parentAccordion.querySelectorAll('.accordion-collapse.show').forEach(item => {
                if (item !== target) {
                    item.classList.remove('show');
                    const otherButton = parentAccordion.querySelector(`[data-bs-target="#${item.id}"]`);
                    if (otherButton) {
                        otherButton.setAttribute('aria-expanded', 'false');
                        otherButton.classList.add('collapsed');
                    }
                }
            });
        }
        
        // Toggle the current item
        target.classList.toggle('show');
        
        console.log('New state:', !isExpanded ? 'expanded' : 'collapsed');
    }
}

function addQuestionField() {
    const questionsList = document.getElementById('questionsList');
    const newQuestion = document.createElement('div');
    newQuestion.className = 'question-item border rounded p-3 mb-3';
    newQuestion.innerHTML = `
        <div class="mb-3">
            <label class="form-label">Question Number</label>
            <input type="number" class="form-control" name="questions[${questionCounter}][number]" required min="1">
        </div>
        <div class="mb-3">
            <label class="form-label">Question Text</label>
            <input type="text" class="form-control" name="questions[${questionCounter}][text]" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="questions[${questionCounter}][description]" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Code Solution</label>
            <textarea class="form-control" name="questions[${questionCounter}][code_solution]" rows="3"></textarea>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
            <i class="fas fa-trash me-2"></i>Remove Question
        </button>
    `;
    questionsList.appendChild(newQuestion);
    questionCounter++;
}

function editPractical(practicalId) {
    // Fetch practical details
    fetch(`ajax/get_practical.php?id=${practicalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_practical_id').value = data.practical.id;
                document.getElementById('edit_practical_number').value = data.practical.practical_number;
                document.getElementById('edit_title').value = data.practical.title;
                
                // Load questions
                const questionsList = document.getElementById('editQuestionsList');
                questionsList.innerHTML = '<h6 class="mb-3">Questions</h6>';
                
                data.practical.questions.forEach((question, index) => {
                    const questionDiv = document.createElement('div');
                    questionDiv.className = 'question-item border rounded p-3 mb-3';
                    questionDiv.innerHTML = `
                        <input type="hidden" name="questions[${index}][id]" value="${question.id}">
                        <div class="mb-3">
                            <label class="form-label">Question Number</label>
                            <input type="number" class="form-control" name="questions[${index}][number]" 
                                   value="${question.question_number}" required min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Text</label>
                            <input type="text" class="form-control" name="questions[${index}][text]" 
                                   value="${question.question_text}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="questions[${index}][description]" 
                                      rows="2">${question.description || ''}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Code Solution</label>
                            <textarea class="form-control" name="questions[${index}][code_solution]" 
                                      rows="3">${question.code_solution || ''}</textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                            <i class="fas fa-trash me-2"></i>Remove Question
                        </button>
                    `;
                    questionsList.appendChild(questionDiv);
                    editQuestionCounter = index + 1;
                });
                
                new bootstrap.Modal(document.getElementById('editPracticalModal')).show();
            } else {
                alert('Failed to load practical details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load practical details');
        });
}

function addEditQuestionField() {
    const questionsList = document.getElementById('editQuestionsList');
    const newQuestion = document.createElement('div');
    newQuestion.className = 'question-item border rounded p-3 mb-3';
    newQuestion.innerHTML = `
        <div class="mb-3">
            <label class="form-label">Question Number</label>
            <input type="number" class="form-control" name="questions[${editQuestionCounter}][number]" required min="1">
        </div>
        <div class="mb-3">
            <label class="form-label">Question Text</label>
            <input type="text" class="form-control" name="questions[${editQuestionCounter}][text]" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="questions[${editQuestionCounter}][description]" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Code Solution</label>
            <textarea class="form-control" name="questions[${editQuestionCounter}][code_solution]" rows="3"></textarea>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
            <i class="fas fa-trash me-2"></i>Remove Question
        </button>
    `;
    questionsList.appendChild(newQuestion);
    editQuestionCounter++;
}

function deletePractical(practicalId) {
    if (confirm('Are you sure you want to delete this practical? This action cannot be undone.')) {
        fetch(`ajax/delete_practical.php?id=${practicalId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to delete practical: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete practical');
        });
    }
}

// Initialize forms
document.getElementById('addPracticalForm').addEventListener('submit', function(e) {
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
            alert('Failed to save practical: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save practical');
    });
});

document.getElementById('editPracticalForm').addEventListener('submit', function(e) {
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
            alert('Failed to update practical: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update practical');
    });
});
</script>

<?php include '../includes/footer.php'; ?> 