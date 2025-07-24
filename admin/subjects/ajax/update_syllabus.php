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
$academic_year = isset($_POST['academic_year']) ? sanitize_input($_POST['academic_year']) : '';
$file_path = isset($_POST['file_path']) ? sanitize_input($_POST['file_path']) : '';

// Validate input
if (!$subject_code || !$subject_name || !$branch || !$semester || !$academic_year || !$file_path) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Check if syllabus exists
    $stmt = $conn->prepare("SELECT id FROM syllabus WHERE subject_code = ?");
    $stmt->execute([$subject_code]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing syllabus
        $stmt = $conn->prepare("UPDATE syllabus SET subject_name = ?, branch = ?, semester = ?, academic_year = ?, file_path = ? WHERE subject_code = ?");
        $stmt->execute([$subject_name, $branch, $semester, $academic_year, $file_path, $subject_code]);
    } else {
        // Insert new syllabus
        $stmt = $conn->prepare("INSERT INTO syllabus (branch, semester, subject_name, subject_code, academic_year, file_path, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$branch, $semester, $subject_name, $subject_code, $academic_year, $file_path]);
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Syllabus updated successfully']);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 