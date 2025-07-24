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

// Get material ID from request
$material_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$material_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid material ID']);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Delete the material
    $stmt = $conn->prepare("DELETE FROM study_materials WHERE id = ?");
    $result = $stmt->execute([$material_id]);
    
    if ($result) {
        // Commit transaction
        $conn->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Material deleted successfully']);
    } else {
        // Rollback on failure
        $conn->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to delete material']);
    }
    
} catch (PDOException $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Database Error in delete_material.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
} 