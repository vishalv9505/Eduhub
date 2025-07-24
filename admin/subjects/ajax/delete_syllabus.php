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

// Get subject ID from request
$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$subject_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid subject ID']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Get subject code first
    $stmt = $conn->prepare("SELECT subject_code FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Subject not found']);
        exit;
    }
    
    // Delete syllabus
    $stmt = $conn->prepare("DELETE FROM syllabus WHERE subject_code = ?");
    $stmt->execute([$subject['subject_code']]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Syllabus deleted successfully']);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 