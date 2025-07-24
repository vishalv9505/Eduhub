<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First get the file path
    $query = "SELECT file_path FROM previous_papers WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->execute([$id]);
        $paper = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Database error: failed to prepare statement.";
        header("Location: manage_papers.php");
        exit();
    }
    
    if ($paper) {
        // Delete the file
        $file_path = "../" . $paper['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete from database
        $query = "DELETE FROM previous_papers WHERE id = ?";
        $stmt = $conn->prepare($query);
        $query = "DELETE FROM previous_papers WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$id])) {
            $_SESSION['message'] = "Paper deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting paper.";
        }
    }
}
// Redirect back to manage papers page
header("Location: manage_papers.php");
exit(); 