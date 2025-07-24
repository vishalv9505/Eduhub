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

// Get paper ID from request
$paper_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$paper_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid paper ID']);
    exit;
}

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM previous_papers WHERE id = ?");
    $stmt->execute([$paper_id]);
    $paper = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paper) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Paper not found']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'paper' => $paper]);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 