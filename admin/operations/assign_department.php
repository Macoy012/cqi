<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../database/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['userID'];
    $cid = $_POST['department_name']; // Could be empty or an ID

    if (empty($userID)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required.']);
        exit();
    }

    if (empty($cid)) {
        // If no department selected, remove the department entry for this user
        $stmt = $conn->prepare("DELETE FROM department WHERE userID = ?");
        $stmt->bind_param('i', $userID);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Department removed successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        $stmt->close();
        exit();
    }

    // Retrieve the course_name based on the provided cid
    $stmtCourse = $conn->prepare("SELECT course_name FROM course WHERE cid = ?");
    $stmtCourse->bind_param('i', $cid);
    $stmtCourse->execute();
    $stmtCourse->bind_result($course_name);
    $stmtCourse->fetch();
    $stmtCourse->close();

    if (empty($course_name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Course ID.']);
        exit();
    }

    // Check if the userID already exists in the department table
    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM department WHERE userID = ?");
    $stmtCheck->bind_param('i', $userID);
    $stmtCheck->execute();
    $stmtCheck->bind_result($exists);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($exists > 0) {
        // If userID exists, update the existing entry
        $stmt = $conn->prepare("
            UPDATE department 
            SET cid = ?, department_name = ? 
            WHERE userID = ?
        ");
        $stmt->bind_param('isi', $cid, $course_name, $userID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Department updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    } else {
        // If userID does not exist, insert a new entry
        $stmt = $conn->prepare("
            INSERT INTO department (userID, cid, department_name) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iis', $userID, $cid, $course_name);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Department assigned successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>