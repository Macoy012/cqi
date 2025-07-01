<?php
include '../../database/dbconn.php';
header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Extract the course ID
$cid = $data['cid'];

// Prepare the delete query
$sql = "DELETE FROM course WHERE cid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cid);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete course.']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
