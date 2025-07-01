<?php
include '../../database/dbconn.php';

if (isset($_POST['id']) && isset($_POST['column']) && isset($_POST['value'])) {
    $id = $_POST['id'];
    $column = $_POST['column'];
    $value = $_POST['value'];

    // Ensure column is safe
    if (!in_array($column, ['p_timeline'])) {
        echo "Invalid column";
        exit;
    }

    // Update the maps table
    $query = "UPDATE maps SET $column = ? WHERE ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $value, $id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error updating";
    }

    $stmt->close();
}

$conn->close();
?>
