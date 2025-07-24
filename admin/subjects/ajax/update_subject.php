<?php
session_start();
require_once '../../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$subject_id = $_POST['subject_id'] ?? 0;
$subject_name = $_POST['subject_name'] ?? '';
$subject_code = $_POST['subject_code'] ?? '';
$branch = $_POST['branch'] ?? '';
$semester = $_POST['semester'] ?? '';

if (empty($subject_id) || empty($subject_name) || empty($subject_code) || empty($branch) || empty($semester)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $conn->beginTransaction();

    // Update subject
    $query = "UPDATE subjects SET 
              subject_name = :subject_name,
              subject_code = :subject_code,
              branch = :branch,
              semester = :semester
              WHERE id = :subject_id";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':subject_name' => $subject_name,
        ':subject_code' => $subject_code,
        ':branch' => $branch,
        ':semester' => $semester,
        ':subject_id' => $subject_id
    ]);

    // Log the action
    $query = "INSERT INTO admin_logs (admin_id, action, subject_name, details) 
              VALUES (:admin_id, 'Update Subject', :subject_name, 'Updated subject details')";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':subject_name' => $subject_name
    ]);

    $conn->commit();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 