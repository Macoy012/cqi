<?php
include '../../database/dbconn.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseCode = trim($_POST['course_code']);
    $courseName = trim($_POST['course_name']);

    // Validate inputs
    if (empty($courseCode) || empty($courseName)) {
        $message = 'All fields are required!';
    } else {
        // Insert into the database
        $sql = "INSERT INTO course (course_code, course_name) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $courseCode, $courseName);

            if ($stmt->execute()) {
                $message = 'Program added successfully!';
            } else {
                $message = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = 'Failed to prepare the statement.';
        }
    }
} else {
    $message = 'Invalid request method.';
}

// Encode the message to make it URL-safe
$message = urlencode($message);

// Redirect back to courses.php with the encoded message
header("Location: ../courses.php?message={$message}");
exit();