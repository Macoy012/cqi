<?php

if (empty($schedule_id)) {
    $co_data = [];
    return;
}

$target_from_file = file_exists('../data/attainment_score.txt') ? (int)file_get_contents('../data/attainment_score.txt') : 75;

// Step 1: Fetch all unique CO values from both midterm and final
$co_list = [];
$co_query = "SELECT DISTINCT CO FROM (
                SELECT CO FROM mlos WHERE schedule_id = ? 
                UNION 
                SELECT CO FROM flos WHERE schedule_id = ?
            ) AS combined";
$stmt = $conn->prepare($co_query);
$stmt->bind_param("ii", $schedule_id, $schedule_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $co_list[] = trim($row['CO']);
}
$stmt->close();

// Step 2: Skip DB target; use file value

// Step 3: Compute data for each CO
$co_data = [];
foreach ($co_list as $co) {
    $midterm_att = 0;
    $stmt = $conn->prepare("SELECT ATT FROM mcos WHERE schedule_id = ? AND CO = ?");
    $stmt->bind_param("is", $schedule_id, $co);
    $stmt->execute();
    $stmt->bind_result($midterm_att);
    $stmt->fetch();
    $stmt->close();
    $midterm_att = $midterm_att ?? 0;

    $final_att = 0;
    $stmt = $conn->prepare("SELECT ATT FROM fcos WHERE schedule_id = ? AND CO = ?");
    $stmt->bind_param("is", $schedule_id, $co);
    $stmt->execute();
    $stmt->bind_result($final_att);
    $stmt->fetch();
    $stmt->close();
    $final_att = $final_att ?? 0;

    $co_data[$co]['midterm'] = (int)$midterm_att;
    $co_data[$co]['final'] = (int)$final_att;

    if ($midterm_att && $final_att) {
        $co_data[$co]['percent_attainment'] = round(($midterm_att + $final_att) / 2);
    } else {
        $co_data[$co]['percent_attainment'] = round(max($midterm_att, $final_att));
    }

    $co_data[$co]['target'] = $target_from_file;
    $co_data[$co]['remarks'] = ($co_data[$co]['percent_attainment'] >= $co_data[$co]['target']) ? "A" : "NA";
}

// Step 4: Insert into overall_co table
$insert_stmt = $conn->prepare("INSERT INTO overallco (co_id, schedule_id, ATT, TAR, RM) VALUES (?, ?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE ATT = VALUES(ATT), TAR = VALUES(TAR), RM = VALUES(RM)");

if (!$insert_stmt) {
    die("Insert prepare failed: " . $conn->error);
}

foreach ($co_data as $co => $data) {
    $co_id = (int)$co;
    $att = (int)$data['percent_attainment'];
    $tar = (int)$data['target'];
    $rm = $data['remarks']; // Should be 'A' or 'NA'

    $insert_stmt->bind_param("iiiss", $co_id, $schedule_id, $att, $tar, $rm);
    if (!$insert_stmt->execute()) {
        error_log("Insert failed for CO $co_id: " . $insert_stmt->error);
    }
}

$insert_stmt->close();
