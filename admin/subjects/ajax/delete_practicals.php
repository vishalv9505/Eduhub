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
    
    // Delete practicals directly using subject_id
    $stmt = $conn->prepare("DELETE FROM practicals WHERE subject_id = ?");
    $stmt->execute([$subject_id]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Practicals deleted successfully']);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 