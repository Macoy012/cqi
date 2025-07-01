<?php
include '../../database/dbconn.php';

if (isset($_POST['id']) && isset($_POST['column']) && isset($_POST['value'])) {
    $id = $_POST['id'];
    $column = $_POST['column'];
    $value = $_POST['value'];

    // Allow only safe columns to be updated
    if (!in_array($column, ['CO'])) {
        echo "Invalid column";
        exit;
    }

    // Update the mlos table (corrected)
    $query = "UPDATE flos SET $column = ? WHERE CO_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $value, $id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error updating: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();