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
    
    // Get all practicals for the subject
    $stmt = $conn->prepare("
        SELECT p.*, 
               GROUP_CONCAT(
                   JSON_OBJECT(
                       'question_number', pq.question_number,
                       'question_text', pq.question_text,
                       'description', pq.description,
                       'code_solution', pq.code_solution
                   )
               ) as questions
        FROM practicals p
        LEFT JOIN practical_questions pq ON p.id = pq.practical_id
        WHERE p.subject_id = ?
        GROUP BY p.id
        ORDER BY p.id
    ");
    $stmt->execute([$subject_id]);
    $practicals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process the results
    foreach ($practicals as &$practical) {
        if ($practical['questions']) {
            $practical['questions'] = json_decode('[' . $practical['questions'] . ']', true);
        } else {
            $practical['questions'] = [];
        }
    }

    echo json_encode([
        'success' => true,
        'practicals' => $practicals
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load practicals data']);
} 