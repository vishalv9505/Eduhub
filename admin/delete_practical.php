<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete practical (questions will be deleted automatically due to foreign key constraint)
    $query = "DELETE FROM practicals WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Practical and its questions deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting practical.";
    }
}

// Redirect back to manage practicals page
header("Location: manage_practicals.php");
exit(); 