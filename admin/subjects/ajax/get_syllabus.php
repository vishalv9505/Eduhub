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

// Get subject code from request
$subject_code = isset($_GET['subject_code']) ? sanitize_input($_GET['subject_code']) : '';

if (!$subject_code) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid subject code']);
    exit;
}

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM syllabus WHERE subject_code = ?");
    $stmt->execute([$subject_code]);
    $syllabus = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$syllabus) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Syllabus not found']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'syllabus' => $syllabus]);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 