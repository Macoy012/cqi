<?php
include '../../database/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');

    if ($id && $field === 'pi_perc') {
        $stmt = $conn->prepare("UPDATE so_pi_targets SET pi_perc = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'DB error']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
    
