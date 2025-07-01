<?php
include '../../database/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $table = isset($_POST['table']) ? $_POST['table'] : '';

    if ($id && $table) {
        if ($table == "mlos") {
            $sql = "DELETE FROM flos WHERE CO_ID = ?";
        } elseif ($table == "fcos") {
            $sql = "DELETE FROM fcos WHERE FCO_ID = ?";
        } else {
            echo "Invalid table.";
            exit;
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error deleting record.";
        }
        
        $stmt->close();
    } else {
        echo "Invalid request.";
    }
}

$conn->close();
?>
