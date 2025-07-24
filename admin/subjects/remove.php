<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid subject ID']);
    exit;
}

$subject_id = (int)$_GET['id'];

try {
    $conn = getDBConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Get subject details for reference
    $stmt = $conn->prepare("SELECT subject_name, subject_code, branch, semester FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        throw new Exception('Subject not found');
    }
    
    // Delete practical questions first (they reference practicals)
    $stmt = $conn->prepare("DELETE pq FROM practical_questions pq 
                           INNER JOIN practicals p ON pq.practical_id = p.id 
                           WHERE p.subject_id = ?");
    $stmt->execute([$subject_id]);
    
    // Delete from practicals
    $stmt = $conn->prepare("DELETE FROM practicals WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    
    // Delete from study_materials
    $stmt = $conn->prepare("DELETE FROM study_materials WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    
    // Delete from previous_papers using subject_code, branch, and semester
    $stmt = $conn->prepare("DELETE FROM previous_papers WHERE branch = ? AND semester = ? AND subject_code = ?");
    $stmt->execute([$subject['branch'], $subject['semester'], $subject['subject_code']]);
    
    // Delete from syllabus using subject_code, branch, and semester
    $stmt = $conn->prepare("DELETE FROM syllabus WHERE branch = ? AND semester = ? AND subject_code = ?");
    $stmt->execute([$subject['branch'], $subject['semester'], $subject['subject_code']]);
    
    // Finally delete the subject
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    
    // Check if subject was actually deleted
    if ($stmt->rowCount() === 0) {
        throw new Exception('Subject not found or already deleted');
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Subject deleted successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 