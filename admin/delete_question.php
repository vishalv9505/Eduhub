<?php
require_once '../config/database.php';
require_once 'includes/auth_check.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get the practical_id before deleting the question
    $query = "SELECT practical_id FROM practical_questions WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $practical_id = $stmt->fetchColumn();
    
    // Delete the question
    $query = "DELETE FROM practical_questions WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Question deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting question.";
    }
    
    // Redirect back to edit practical page to show remaining questions
    header("Location: edit_practical.php?id=" . $practical_id);
} else {
    header("Location: manage_practicals.php");
}
exit(); 