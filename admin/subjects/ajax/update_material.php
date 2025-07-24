<?php
session_start();
require_once '../../../config/database.php';
require_once '../../includes/functions.php';

// Set header to JSON
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required fields are provided
if (!isset($_POST['material_id']) || !isset($_POST['title']) || !isset($_POST['file_type'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

$material_id = sanitize_input($_POST['material_id']);
$title = sanitize_input($_POST['title']);
$description = sanitize_input($_POST['description'] ?? '');
$file_type = sanitize_input($_POST['file_type']);

// Get file and video URLs
$file_paths = isset($_POST['edit_file_paths']) ? array_filter($_POST['edit_file_paths']) : [];
$video_urls = isset($_POST['edit_video_urls']) ? array_filter($_POST['edit_video_urls']) : [];

// Validate that at least one URL is provided
if (empty($file_paths) && empty($video_urls)) {
    echo json_encode(['success' => false, 'message' => 'Please provide at least one file URL or video URL']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Update material
    $stmt = $conn->prepare("
        UPDATE study_materials 
        SET title = ?, description = ?, file_type = ?, file_path = ?, video_url = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $title,
        $description,
        $file_type,
        $file_type === 'VIDEO' ? '' : implode(',', $file_paths),
        $file_type === 'VIDEO' ? implode(',', $video_urls) : '',
        $material_id
    ]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Material not found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'Material updated successfully']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 