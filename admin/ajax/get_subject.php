<?php
require_once '../includes/functions.php';
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get and validate input
$subject_id = sanitizeInput($_GET['id'] ?? '');

if (empty($subject_id)) {
    echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get subject details
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        echo json_encode(['success' => false, 'message' => 'Subject not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'subject' => $subject
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching subject details'
    ]);
} 