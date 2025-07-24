<?php
session_start();
require_once '../../../config/database.php';
require_once '../../includes/functions.php';

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
if (!isset($_POST['subject_id']) || !isset($_POST['practical_number']) || !isset($_POST['title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // Insert practical
    $stmt = $conn->prepare("INSERT INTO practicals (subject_id, practical_number, title) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['subject_id'],
        $_POST['practical_number'],
        $_POST['title']
    ]);
    
    $practical_id = $conn->lastInsertId();
    
    // Insert questions if any
    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        $stmt = $conn->prepare("
            INSERT INTO practical_questions 
            (practical_id, question_number, question_text, description, code_solution) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['questions'] as $question) {
            if (empty($question['text'])) continue;
            
            $stmt->execute([
                $practical_id,
                $question['number'],
                $question['text'],
                $question['description'] ?? null,
                $question['code_solution'] ?? null
            ]);
        }
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Practical saved successfully']);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 