<?php
session_start();
require_once '../../../config/database.php';
require_once '../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['subject_id'])) {
    echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
    exit;
}

$subject_id = sanitize_input($_GET['subject_id']);

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT id, title, description, type, content_path, 
               CASE WHEN type = 'VIDEO' THEN content_path ELSE NULL END as video_url
        FROM study_materials 
        WHERE subject_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$subject_id]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'materials' => $materials
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load materials data']);
} 