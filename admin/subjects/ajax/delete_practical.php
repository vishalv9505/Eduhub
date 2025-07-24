<?php
session_start();
require_once '../../../config/database.php';
require_once '../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid practical ID']);
    exit;
}

try {
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // Delete practical questions first (they reference practicals)
    $stmt = $conn->prepare("DELETE FROM practical_questions WHERE practical_id = ?");
    $stmt->execute([$_GET['id']]);
    
    // Delete practical
    $stmt = $conn->prepare("DELETE FROM practicals WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    // Check if practical was actually deleted
    if ($stmt->rowCount() === 0) {
        throw new Exception('Practical not found or already deleted');
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Practical deleted successfully']);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 