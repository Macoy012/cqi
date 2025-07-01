<?php
include '../database/dbconn.php';
session_start();

if ($_SESSION['usertype'] !== 'Programhead') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$schedule_id = intval($_POST['schedule_id'] ?? 0);
$so = $_POST['so'] ?? '';
$pi = intval($_POST['pi'] ?? 0);
$new_tar = floatval($_POST['tar'] ?? 0);

if (!$schedule_id || !$so || !$pi) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit();
}

// Get existing ATT
$stmt = $conn->prepare("SELECT ATT FROM so_pi_targets WHERE schedule_id = ? AND SO = ? AND PI = ?");
$stmt->bind_param("isi", $schedule_id, $so, $pi);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Target not found']);
    exit();
}

$att = floatval($result['ATT']);
$rm = $att >= $new_tar ? 'A' : 'NA';

// Update TAR and RM
$update = $conn->prepare("UPDATE so_pi_targets SET TAR = ?, RM = ? WHERE schedule_id = ? AND SO = ? AND PI = ?");
$update->bind_param("dsisi", $new_tar, $rm, $schedule_id, $so, $pi);
$update->execute();

echo json_encode(['success' => true, 'rm' => $rm]);
