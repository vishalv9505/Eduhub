<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
if (!isset($_POST['subject_id']) || !isset($_POST['unit_number']) || !isset($_POST['unit_title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn = get_db_connection();
    $conn->beginTransaction();

    // Check if unit number already exists for this subject
    $stmt = $conn->prepare("SELECT id FROM units WHERE subject_id = ? AND unit_number = ?");
    $stmt->execute([$_POST['subject_id'], $_POST['unit_number']]);
    if ($stmt->fetch()) {
        throw new Exception('Unit number already exists for this subject');
    }

    // Insert new unit
    $stmt = $conn->prepare("
        INSERT INTO units (subject_id, unit_number, unit_title, description) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['subject_id'],
        $_POST['unit_number'],
        $_POST['unit_title'],
        $_POST['description'] ?? null
    ]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Unit added successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 