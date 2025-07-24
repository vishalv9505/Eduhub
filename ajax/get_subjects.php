<?php
require_once '../config/database.php';

// Get parameters
$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;

// Validate input
if (empty($branch) || empty($semester)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

try {
    // Prepare and execute query
    $query = "SELECT id, subject_name, subject_code FROM subjects 
              WHERE branch = :branch AND semester = :semester 
              ORDER BY subject_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':branch', $branch);
    $stmt->bindParam(':semester', $semester);
    $stmt->execute();
    
    // Fetch all subjects
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($subjects);
} catch (PDOException $e) {
    // Return empty array on error
    header('Content-Type: application/json');
    echo json_encode([]);
}
?> 