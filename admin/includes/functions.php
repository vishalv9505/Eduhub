<?php
// Include common functions
require_once __DIR__ . '/../../includes/functions.php';

// Database connection
function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        $config = require __DIR__ . '/../../config/database.php';
        if (!$config) {
            die("Failed to load database configuration");
        }
        try {
            $conn = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']}", 
                $config['username'], 
                $config['password']
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $conn;
}

// Check if user is logged in as admin
function checkAdminLogin() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Log admin action
function logAdminAction($admin_id, $action, $details = '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$admin_id, $action, $details]);
}

// Get admin details
function getAdminDetails($admin_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get subject details
function getSubjectDetails($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get practical details
function getPracticalDetails($practical_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM practicals WHERE id = ?");
    $stmt->execute([$practical_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get practical questions
function getPracticalQuestions($practical_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM practical_questions WHERE practical_id = ? ORDER BY question_number");
    $stmt->execute([$practical_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get study materials
function getStudyMaterials($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM study_materials WHERE subject_id = ? ORDER BY created_at DESC");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get previous papers
function getPreviousPapers($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM previous_papers WHERE subject_id = ? ORDER BY year DESC");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get syllabus
function getSyllabus($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM syllabus WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get video lectures
function getVideoLectures($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM video_lectures WHERE subject_id = ? ORDER BY created_at DESC");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user details
function getUserDetails($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

// Validate file upload
function validateFileUpload($file, $allowed_types = ['pdf', 'doc', 'docx'], $max_size = 5242880) {
    $errors = [];
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        $errors[] = "Invalid file type. Allowed types: " . implode(', ', $allowed_types);
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = "File size exceeds limit. Maximum size: " . formatFileSize($max_size);
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload failed with error code: " . $file['error'];
    }
    
    return $errors;
}

// Upload file
function uploadFile($file, $destination) {
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    
    $filename = generateRandomString() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filepath = $destination . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

// Delete file
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// Get branch name
function getBranchName($branch_code) {
    $branches = [
        'CS' => 'Computer Science',
        'IT' => 'Information Technology',
        'EC' => 'Electronics & Communication',
        'ME' => 'Mechanical Engineering',
        'CE' => 'Civil Engineering',
        'EE' => 'Electrical Engineering'
    ];
    return $branches[$branch_code] ?? $branch_code;
}

// Get semester name
function getSemesterName($semester) {
    return "Semester " . $semester;
}

// Get subject count by branch and semester
function getSubjectCount($branch, $semester) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM subjects WHERE branch = ? AND semester = ?");
    $stmt->execute([$branch, $semester]);
    return $stmt->fetchColumn();
}

// Get practical count by subject
function getPracticalCount($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM practicals WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    return $stmt->fetchColumn();
}

// Get study material count by subject
function getStudyMaterialCount($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM study_materials WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    return $stmt->fetchColumn();
}

// Get previous paper count by subject
function getPreviousPaperCount($subject_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM previous_papers WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    return $stmt->fetchColumn();
} 