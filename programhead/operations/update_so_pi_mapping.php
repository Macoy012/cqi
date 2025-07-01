<?php
include '../../database/dbconn.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// Validate allowed fields
$allowed_fields = ['pi', 'ATT'];
if (!in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'error' => 'Invalid field.']);
    exit;
}

// Sanitize and convert value if necessary
if ($field === 'pi') {
    $value = intval($value);
} elseif ($field === 'ATT') {
    $value = floatval($value);
}

// Update the specific field in so_pi_mapping table
$update_stmt = $conn->prepare("UPDATE so_pi_mapping SET $field = ? WHERE id = ?");
$update_stmt->bind_param(($field === 'pi' ? "ii" : "di"), $value, $id);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Update failed.']);
}
?>