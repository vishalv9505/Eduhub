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
        SELECT id, year, session, file_path as url
        FROM previous_papers 
        WHERE subject_id = ?
        ORDER BY year DESC, session
    ");
    $stmt->execute([$subject_id]);
    $papers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'papers' => $papers
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load papers data']);
} 