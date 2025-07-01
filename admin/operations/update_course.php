<?php
include '../../database/dbconn.php';
header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Extract data from the request
$cid = $data['cid'];
$courseCode = $data['course_code'];
$courseName = $data['course_name'];

// Prepare the update query
$sql = "UPDATE courses SET course_code = ?, course_name = ? WHERE cid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $courseCode, $courseName, $cid);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update course.']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
