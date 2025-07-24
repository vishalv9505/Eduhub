<?php
// Prevent any output before JSON
ob_start();

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
if (!isset($_POST['subject_id']) || !isset($_POST['title']) || !isset($_POST['file_type'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

$subject_id = sanitize_input($_POST['subject_id']);
$title = sanitize_input($_POST['title']);
$description = sanitize_input($_POST['description'] ?? '');
$file_type = sanitize_input($_POST['file_type']);

// Get file and video URLs
$file_paths = isset($_POST['file_paths']) ? array_filter($_POST['file_paths']) : [];
$video_urls = isset($_POST['video_urls']) ? array_filter($_POST['video_urls']) : [];

// Validate that at least one URL is provided
if (empty($file_paths) && empty($video_urls)) {
    echo json_encode(['success' => false, 'message' => 'Please provide at least one file URL or video URL']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Insert material
    $stmt = $conn->prepare("
        INSERT INTO study_materials (subject_id, title, description, file_type, file_path, video_url)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $subject_id,
        $title,
        $description,
        $file_type,
        $file_type === 'VIDEO' ? '' : implode(',', $file_paths),
        $file_type === 'VIDEO' ? implode(',', $video_urls) : ''
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Material added successfully']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 