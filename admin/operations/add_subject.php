<?php
include '../../database/dbconn.php';
header('Content-Type: application/json');

try {
    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if data exists
    if (empty($data['subject_id']) || empty($data['subject_code']) || empty($data['description'])) {
        throw new Exception('Missing required fields.');
    }

    // Extract data from the request
    $subjectID = $data['subject_id'];
    $subjectCode = $data['subject_code'];
    $description = $data['description'];

    // Prepare the insert query
    $sql = "INSERT INTO subject (subject_id, subject_code, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $subjectID, $subjectCode, $description);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Database insert failed.');
    }

} catch (Exception $e) {
    // Return an error message in JSON format
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // Close the database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
