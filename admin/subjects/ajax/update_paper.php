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
$paper_id = isset($_POST['paper_id']) ? (int)$_POST['paper_id'] : 0;
$exam_year = isset($_POST['exam_year']) ? (int)$_POST['exam_year'] : 0;
$exam_session = isset($_POST['exam_session']) ? sanitize_input($_POST['exam_session']) : '';
$file_path = isset($_POST['file_path']) ? sanitize_input($_POST['file_path']) : '';

// Validate input
if (!$paper_id || !$exam_year || !$exam_session || !$file_path) {
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
    
    // Check if paper exists
    $stmt = $conn->prepare("SELECT id FROM previous_papers WHERE id = ?");
    $stmt->execute([$paper_id]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Paper not found']);
        exit;
    }

    // Update paper
    $stmt = $conn->prepare("UPDATE previous_papers SET exam_year = ?, exam_session = ?, file_path = ? WHERE id = ?");
    $stmt->execute([$exam_year, $exam_session, $file_path, $paper_id]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Paper updated successfully']);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 