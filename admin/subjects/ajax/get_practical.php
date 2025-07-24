<?php
session_start();
require_once '../../../config/database.php';
require_once '../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid practical ID']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Get practical details
    $stmt = $conn->prepare("
        SELECT p.*, 
               GROUP_CONCAT(
                   JSON_OBJECT(
                       'id', pq.id,
                       'question_number', pq.question_number,
                       'question_text', pq.question_text,
                       'description', pq.description,
                       'code_solution', pq.code_solution
                   )
               ) as questions
        FROM practicals p
        LEFT JOIN practical_questions pq ON p.id = pq.practical_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$_GET['id']]);
    $practical = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$practical) {
        echo json_encode(['success' => false, 'message' => 'Practical not found']);
        exit;
    }
    
    // Process questions
    if ($practical['questions']) {
        $practical['questions'] = json_decode('[' . $practical['questions'] . ']', true);
    } else {
        $practical['questions'] = [];
    }
    
    echo json_encode(['success' => true, 'practical' => $practical]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 