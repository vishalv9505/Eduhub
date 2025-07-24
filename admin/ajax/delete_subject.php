<?php
require_once '../includes/functions.php';
checkAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get and validate input
$subject_id = sanitizeInput($_POST['subject_id'] ?? '');

if (empty($subject_id)) {
    echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check if subject exists
    $stmt = $conn->prepare("SELECT subject_code, subject_name FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        echo json_encode(['success' => false, 'message' => 'Subject not found']);
        exit();
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete related records
    $stmt = $conn->prepare("DELETE FROM practical_questions WHERE practical_id IN (SELECT id FROM practicals WHERE subject_id = ?)");
    $stmt->execute([$subject_id]);
    
    $stmt = $conn->prepare("DELETE FROM practicals WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    
    $stmt = $conn->prepare("DELETE FROM study_materials WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    
    $stmt = $conn->prepare("DELETE FROM previous_papers WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    
    // Delete subject
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    
    // Log action
    logAdminAction($_SESSION['admin_id'], 'delete_subject', "Deleted subject: {$subject['subject_code']} - {$subject['subject_name']}");
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Subject deleted successfully',
        'redirect' => 'subjects.php'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the subject'
    ]);
} 