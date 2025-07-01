<?php
include '../../database/dbconn.php';

$data = json_decode(file_get_contents("php://input"), true);
$schedule_id = intval($data['scheduleId']);
$ilo = $data['ilo'];
$comment = $data['comment'];

// Update comment for maps (midterms)
$table = "maps";

// Check if comment already exists
$check_sql = "SELECT ID FROM $table WHERE schedule_id = ? AND ILO = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("is", $schedule_id, $ilo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing comment
    $stmt->close();
    $update_sql = "UPDATE $table SET comments = ? WHERE schedule_id = ? AND ILO = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sis", $comment, $schedule_id, $ilo);
    $success = $update_stmt->execute();
    $update_stmt->close();
} else {
    // Insert new row with comment
    $stmt->close();
    $insert_sql = "INSERT INTO $table (schedule_id, exam_type, ILO, comments) VALUES (?, 'midterms', ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iss", $schedule_id, $ilo, $comment);
    $success = $insert_stmt->execute();
    $insert_stmt->close();
}

echo json_encode(["success" => $success]);
?>
