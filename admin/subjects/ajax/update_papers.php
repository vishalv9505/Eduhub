<?php
session_start();
require_once '../../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['subject_id'])) {
    echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
    exit;
}

try {
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // Get subject details
    $stmt = $conn->prepare("SELECT branch, semester, subject_name, subject_code FROM subjects WHERE id = ?");
    $stmt->execute([$_POST['subject_id']]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        throw new Exception("Subject not found");
    }
    
    // Delete existing papers for this subject
    $stmt = $conn->prepare("DELETE FROM previous_papers WHERE subject_id = ?");
    $stmt->execute([$_POST['subject_id']]);
    
    // Insert new papers
    if (isset($_POST['paper_years']) && is_array($_POST['paper_years'])) {
        $stmt = $conn->prepare("INSERT INTO previous_papers (subject_id, branch, semester, subject_name, subject_code, exam_year, exam_session, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($_POST['paper_years'] as $key => $year) {
            if (empty($year) || empty($_POST['paper_urls'][$key])) continue;
            
            $stmt->execute([
                $_POST['subject_id'],
                $subject['branch'],
                $subject['semester'],
                $subject['subject_name'],
                $subject['subject_code'],
                $year,
                $_POST['paper_sessions'][$key],
                $_POST['paper_urls'][$key]
            ]);
        }
    }

    // Log the action
    logAdminAction($_SESSION['admin_id'], 'update_papers', "Updated previous papers for subject: {$subject['subject_name']} ({$subject['subject_code']})");

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Previous papers updated successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 