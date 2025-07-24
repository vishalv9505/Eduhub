<?php
session_start();
require_once '../../../config/database.php';
require_once '../../includes/functions.php';

// Set header to JSON
header('Content-Type: application/json');

// Debug logging
error_log("GET_MATERIAL.PHP - Request received: " . print_r($_GET, true));

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in again.']);
    exit;
}

// Get material ID from request
$material_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Debug logging
error_log("GET_MATERIAL.PHP - Material ID: " . $material_id);

if (!$material_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid material ID: ' . $_GET['id']]);
    exit;
}

try {
    // Debug logging
    error_log("GET_MATERIAL.PHP - Attempting database connection");
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get material details
    $stmt = $conn->prepare("SELECT * FROM study_materials WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt->execute([$material_id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug logging
    error_log("GET_MATERIAL.PHP - Query result: " . print_r($material, true));
    
    if (!$material) {
        echo json_encode([
            'success' => false, 
            'message' => 'Material not found with ID: ' . $material_id
        ]);
        exit;
    }
    
    // Return success response with material data
    echo json_encode([
        'success' => true,
        'material' => [
            'id' => $material['id'],
            'title' => $material['title'],
            'description' => $material['description'],
            'file_path' => $material['file_path'],
            'video_url' => $material['video_url']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database Error in get_material.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'details' => [
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ]
    ]);
    exit;
} catch (Exception $e) {
    error_log("General Error in get_material.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
} 