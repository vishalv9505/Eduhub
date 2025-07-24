<?php
session_start();
require_once '../../../config/database.php';
require_once '../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$subject_code = isset($_POST['subject_code']) ? sanitize_input($_POST['subject_code']) : '';
$subject_name = isset($_POST['subject_name']) ? sanitize_input($_POST['subject_name']) : '';
$branch = isset($_POST['branch']) ? sanitize_input($_POST['branch']) : '';
$semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 0;
$exam_year = isset($_POST['exam_year']) ? (int)$_POST['exam_year'] : 0;
$exam_session = isset($_POST['exam_session']) ? sanitize_input($_POST['exam_session']) : '';
$file_path = isset($_POST['file_path']) ? sanitize_input($_POST['file_path']) : '';

// Validate input
if (!$subject_code || !$subject_name || !$branch || !$semester || !$exam_year || !$exam_session || !$file_path) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if ($exam_year < 2000 || $exam_year > 2099) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid year']);
    exit;
}

if (!in_array($exam_session, ['Summer', 'Winter'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Insert new paper
    $stmt = $conn->prepare("INSERT INTO previous_papers (branch, semester, subject_name, subject_code, exam_year, exam_session, file_path, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$branch, $semester, $subject_name, $subject_code, $exam_year, $exam_session, $file_path]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Paper added successfully']);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 