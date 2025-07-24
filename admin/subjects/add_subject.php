<?php
session_start();
require_once '../../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage.php');
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    $conn = getDBConnection();
    $conn->beginTransaction();

    // Basic subject information
    $subject_code = sanitize_input($_POST['subject_code']);
    $subject_name = sanitize_input($_POST['subject_name']);
    $branch = sanitize_input($_POST['branch']);
    $semester = sanitize_input($_POST['semester']);
    $category = sanitize_input($_POST['category'] ?? null);

    // Check if subject code already exists
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ? AND branch = ? AND semester = ?");
    $stmt->execute([$subject_code, $branch, $semester]);
    if ($stmt->fetch()) {
        throw new Exception("Subject already exists for this branch and semester!");
    }

    // Insert subject
    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, branch, semester) VALUES (?, ?, ?, ?)");
    $stmt->execute([$subject_code, $subject_name, $branch, $semester]);
    $subject_id = $conn->lastInsertId();

    // Handle study materials
    if (isset($_POST['material_titles'])) {
        foreach ($_POST['material_titles'] as $key => $title) {
            if (empty($title)) continue;

            // Get arrays of URLs for this material
            $pdf_urls = array_filter($_POST['material_urls'][$key] ?? [], function($url) {
                return !empty(trim($url));
            });
            $video_urls = array_filter($_POST['material_links'][$key] ?? [], function($url) {
                return !empty(trim($url));
            });

            // Convert arrays to JSON
            $pdf_json = !empty($pdf_urls) ? json_encode(array_values($pdf_urls)) : null;
            $video_json = !empty($video_urls) ? json_encode(array_values($video_urls)) : null;

            if ($pdf_json || $video_json) {
                // Insert study material
                $stmt = $conn->prepare("
                    INSERT INTO study_materials (
                        subject_id, 
                        title, 
                        description, 
                        file_path, 
                        video_url, 
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $subject_id,
                    $title,
                    $_POST['material_descriptions'][$key] ?? null,
                    $pdf_json,
                    $video_json
                ]);
            }
        }
    }

    // Handle practicals
    if (isset($_POST['practical_titles'])) {
        foreach ($_POST['practical_titles'] as $key => $title) {
            if (empty($title)) continue;

            // Insert practical
            $stmt = $conn->prepare("INSERT INTO practicals (subject_id, title) VALUES (?, ?)");
            $stmt->execute([$subject_id, $title]);
            $practical_id = $conn->lastInsertId();

            // Insert practical questions if they exist
            if (isset($_POST['practical_questions'][$key]) && is_array($_POST['practical_questions'][$key])) {
                foreach ($_POST['practical_questions'][$key] as $q_key => $question) {
                    if (empty($question['text'])) continue;

                    $stmt = $conn->prepare("INSERT INTO practical_questions (practical_id, question_text, code_solution) VALUES (?, ?, ?)");
                    $stmt->execute([
                        $practical_id,
                        $question['text'],
                        $question['code_solution'] ?? null
                    ]);
                }
            }
        }
    }

    // Handle previous papers
    if (isset($_POST['paper_years'])) {
        foreach ($_POST['paper_years'] as $key => $year) {
            if (empty($year) || empty($_POST['paper_urls'][$key])) continue;

            $stmt = $conn->prepare("INSERT INTO previous_papers (branch, semester, subject_name, subject_code, exam_year, exam_session, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $branch,
                $semester,
                $subject_name,
                $subject_code,
                $year,
                $_POST['paper_sessions'][$key] ?? 'Summer',
                $_POST['paper_urls'][$key]
            ]);
        }
    }

    // Handle syllabus
    if (!empty($_POST['syllabus_url'])) {
        $stmt = $conn->prepare("INSERT INTO syllabus (branch, semester, subject_name, subject_code, academic_year, file_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $branch,
            $semester,
            $subject_name,
            $subject_code,
            date('Y'),
            $_POST['syllabus_url']
        ]);
    }

    // Log the action
    logAdminAction($_SESSION['admin_id'], 'add_subject', "Added subject: $subject_name ($subject_code)");

    $conn->commit();
    $response['success'] = true;
    $response['message'] = "Subject added successfully!";
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = "Error: " . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 