<?php
include '../../database/dbconn.php';
header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Extract data from the request
$subjectID = $data['subjectID'];
$subjectCode = $data['subjectCode'];
$description = $data['description'];

// Prepare the update query
$sql = "UPDATE subject SET subject_code = ?, description = ? WHERE subject_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $subjectCode, $description, $subjectID);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update subject.']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
