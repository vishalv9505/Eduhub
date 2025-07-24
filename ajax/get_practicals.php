<?php
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get subject ID
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

// Validate input
if (empty($subject_id)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid subject ID']);
    exit;
}

try {
    // First verify the subject exists
    $check_subject = "SELECT id FROM subjects WHERE id = :subject_id";
    $stmt = $conn->prepare($check_subject);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Subject not found']);
        exit;
    }

    // First get all practicals
    $query = "SELECT p.* FROM practicals p WHERE p.subject_id = :subject_id ORDER BY p.practical_number ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    $practicals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Then get questions for each practical
    foreach ($practicals as &$practical) {
        $query = "SELECT * FROM practical_questions WHERE practical_id = :practical_id ORDER BY question_number ASC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':practical_id', $practical['id']);
        $stmt->execute();
        $practical['questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($practicals);
} catch (PDOException $e) {
    // Log the error
    error_log("Database error in get_practicals.php: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error occurred']);
}
?> 