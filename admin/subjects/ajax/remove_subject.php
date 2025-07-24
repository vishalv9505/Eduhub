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

if (empty($subject_id)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing subject ID']);
    exit;
}

try {
    $conn->beginTransaction();

    // Get subject name for logging
    $query = "SELECT subject_name FROM subjects WHERE id = :subject_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':subject_id' => $subject_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subject) {
        throw new Exception('Subject not found');
    }

    // Delete practical questions first
    $query = "DELETE pq FROM practical_questions pq 
              INNER JOIN practicals p ON pq.practical_id = p.id 
              WHERE p.subject_id = :subject_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':subject_id' => $subject_id]);

    // Delete practicals
    $query = "DELETE FROM practicals WHERE subject_id = :subject_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':subject_id' => $subject_id]);

    // Delete study materials
    $query = "DELETE FROM study_materials WHERE subject_id = :subject_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':subject_id' => $subject_id]);

    // Delete previous year papers
    $query = "DELETE FROM previous_year_papers WHERE subject_id = :subject_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':subject_id' => $subject_id]);

    // Finally, delete the subject
    $query = "DELETE FROM subjects WHERE id = :subject_id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':subject_id' => $subject_id]);

    // Log the action
    $query = "INSERT INTO admin_logs (admin_id, action, subject_name, details) 
              VALUES (:admin_id, 'Remove Subject', :subject_name, 'Removed subject and all related data')";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':subject_name' => $subject['subject_name']
    ]);

    $conn->commit();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?> 