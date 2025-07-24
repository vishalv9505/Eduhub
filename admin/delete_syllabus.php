<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First get the file path
    $query = "SELECT file_path FROM syllabus WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: failed to prepare statement.";
        header("Location: manage_syllabus.php");
        exit();
    }
    // Use PDO parameter binding
    $stmt->execute([$id]);
    $syllabus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($syllabus) {
        // Delete the file
        $file_path = "../" . $syllabus['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
    // Delete from database
    $query = "DELETE FROM syllabus WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "Syllabus deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting syllabus.";
    }
    }
}