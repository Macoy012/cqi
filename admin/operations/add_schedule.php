<?php
include '../../database/dbconn.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is an Admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // List of required fields
    $required_fields = [
        'schedule_id', 'subject_id', 'cid', 'userID', 'academic_year', 'semester',
        'year', 'section'
    ];

    // Validate presence of required fields
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required."]);
            exit();
        }
    }

    // Retrieve and sanitize POST data
    $schedule_id = htmlspecialchars($_POST['schedule_id']);
    $subject_id = htmlspecialchars($_POST['subject_id']);
    $cid = htmlspecialchars($_POST['cid']);
    $userID = htmlspecialchars($_POST['userID']);
    $academic_year = htmlspecialchars($_POST['academic_year']);
    $semester = htmlspecialchars($_POST['semester']);
    $year = htmlspecialchars($_POST['year']);
    $section = htmlspecialchars($_POST['section']);
    
    // Additional fields (these might not be in the POST data)
    // Ensure they are passed in the form as necessary:
    $subject_code = isset($_POST['subject_code']) ? htmlspecialchars($_POST['subject_code']) : '';
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
    $course_code = isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : '';

    // SQL query to insert the data
    $sql = "INSERT INTO schedule (
                schedule_id, subject_id, cid, userID, subject_code, description,
                username, academic_year, semester, course_code, year, section
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Statement preparation failed: ' . $conn->error]);
        exit();
    }

    // Bind parameters
    $stmt->bind_param(
        'ssssssssssss',
        $schedule_id, $subject_id, $cid, $userID, $subject_code, $description,
        $username, $academic_year, $semester, $course_code, $year, $section
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Schedule added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    // Clean up
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
