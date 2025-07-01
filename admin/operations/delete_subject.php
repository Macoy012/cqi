<?php
include '../../database/dbconn.php';

// Read POST data
$data = json_decode(file_get_contents("php://input"), true);

$subject_id = $data['subject_id'];

// Prepare the DELETE SQL query
$sql = "DELETE FROM subject WHERE subject_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $subject_id);

// Execute the query and check if the subject was deleted
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete subject']);
}

$stmt->close();
$conn->close();
?>
