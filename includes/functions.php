<?php
// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle file uploads
function upload_file($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Check if file already exists
    if (file_exists($target_file)) {
        return "Sorry, file already exists.";
    }
    
    // Check file size (limit to 10MB)
    if ($file["size"] > 10000000) {
        return "Sorry, your file is too large.";
    }
    
    // Allow certain file formats
    $allowed_types = array("pdf", "doc", "docx", "jpg", "jpeg", "png");
    if (!in_array($imageFileType, $allowed_types)) {
        return "Sorry, only PDF, DOC, DOCX, JPG, JPEG & PNG files are allowed.";
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "The file ". basename($file["name"]). " has been uploaded.";
    } else {
        return "Sorry, there was an error uploading your file.";
    }
}

// Function to get subject list
function get_subjects($conn) {
    $stmt = $conn->query("SELECT * FROM subjects ORDER BY subject_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get study materials by subject
function get_study_materials($conn, $subject_id) {
    $stmt = $conn->prepare("SELECT * FROM study_materials WHERE subject_id = ? ORDER BY created_at DESC");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get previous year papers
function get_previous_papers($conn, $subject_id) {
    $stmt = $conn->prepare("SELECT * FROM previous_papers WHERE subject_id = ? ORDER BY year DESC");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get syllabus
function get_syllabus($conn, $subject_id) {
    $stmt = $conn->prepare("SELECT * FROM syllabus WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
} 