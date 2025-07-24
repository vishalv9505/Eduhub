<?php
require_once '../includes/functions.php';
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get and validate input
$subject_code = sanitizeInput($_POST['subject_code'] ?? '');
$subject_name = sanitizeInput($_POST['subject_name'] ?? '');
$branch = sanitizeInput($_POST['branch'] ?? '');
$semester = sanitizeInput($_POST['semester'] ?? '');

if (empty($subject_code) || empty($subject_name) || empty($branch) || empty($semester)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate branch
$valid_branches = ['CS', 'IT', 'EC', 'ME', 'CE', 'EE'];
if (!in_array($branch, $valid_branches)) {
    echo json_encode(['success' => false, 'message' => 'Invalid branch']);
    exit();
}

// Validate semester
$valid_semesters = ['1', '2', '3', '4'];
if (!in_array($semester, $valid_semesters)) {
    echo json_encode(['success' => false, 'message' => 'Invalid semester']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check if subject code already exists
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ?");
    $stmt->execute([$subject_code]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Subject code already exists']);
        exit();
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Insert subject
    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, branch, semester) VALUES (?, ?, ?, ?)");
    $stmt->execute([$subject_code, $subject_name, $branch, $semester]);
    
    // Log action
    logAdminAction($_SESSION['admin_id'], 'add_subject', "Added subject: $subject_code - $subject_name");
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Subject added successfully',
        'redirect' => 'subjects.php'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while adding the subject'
    ]);
} 