<?php
include '../../database/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id']) && isset($_POST['column']) && isset($_POST['value'])) {
        // ✅ Updating individual columns in mlo
        $id = $_POST['id'];
        $column = $_POST['column'];
        $value = trim($_POST['value']);

        // ✅ Allowed columns
        $allowed_columns = ['ILO', 'AP', 'p_timeline'];

        if (!in_array($column, $allowed_columns)) {
            echo "Invalid column.";
            exit();
        }

        // ✅ Prepare query to update any allowed field
        $sql = "UPDATE mlo SET $column = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $id);

        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['schedule_id'])) {
        // ✅ Summing attainment and updating mlos
        $schedule_id = $_POST['schedule_id'];

        // Step 1: Get unique ILO numbers from mlo
        $queryILO = "SELECT DISTINCT ILO FROM mlo WHERE schedule_id = ?";
        $stmtILO = $conn->prepare($queryILO);
        $stmtILO->bind_param("i", $schedule_id);
        $stmtILO->execute();
        $resultILO = $stmtILO->get_result();

        $ilo_numbers = [];
        while ($row = $resultILO->fetch_assoc()) {
            $ilo_numbers[] = $row['ILO'];
        }
        $stmtILO->close();

        // Step 2: DELETE ILO entries from mlos & maps that no longer exist in mlo
        if (!empty($ilo_numbers)) {
            $placeholders = implode(",", array_fill(0, count($ilo_numbers), "?"));
            $types = str_repeat("i", count($ilo_numbers));

            $deleteMissingILO = "DELETE FROM mlos WHERE schedule_id = ? AND ILO NOT IN ($placeholders)";
            $stmtDelete = $conn->prepare($deleteMissingILO);
            $stmtDelete->bind_param("i" . $types, $schedule_id, ...$ilo_numbers);
            $stmtDelete->execute();
            $stmtDelete->close();

            $deleteMissingMaps = "DELETE FROM maps WHERE schedule_id = ? AND ILO NOT IN ($placeholders)";
            $stmtDeleteMaps = $conn->prepare($deleteMissingMaps);
            $stmtDeleteMaps->bind_param("i" . $types, $schedule_id, ...$ilo_numbers);
            $stmtDeleteMaps->execute();
            $stmtDeleteMaps->close();
        } else {
            // If no ILOs exist in mlo, delete all mlos & maps entries for this schedule_id
            $deleteAllMLOS = "DELETE FROM mlos WHERE schedule_id = ?";
            $stmtDeleteAll = $conn->prepare($deleteAllMLOS);
            $stmtDeleteAll->bind_param("i", $schedule_id);
            $stmtDeleteAll->execute();
            $stmtDeleteAll->close();

            $deleteAllMAPS = "DELETE FROM maps WHERE schedule_id = ?";
            $stmtDeleteMapsAll = $conn->prepare($deleteAllMAPS);
            $stmtDeleteMapsAll->bind_param("i", $schedule_id);
            $stmtDeleteMapsAll->execute();
            $stmtDeleteMapsAll->close();

            echo "All MLOS and MAPS records deleted.";
            exit();
        }

        // Step 3: Process each ILO and update mlos
        foreach ($ilo_numbers as $ilo) {
            // ✅ Skip ILOs that are 0, NULL, or empty
            if (empty($ilo) || intval($ilo) === 0) {
                continue;
            }

            // ✅ Get ATT, count, and TAR values
            $querySumATT = "SELECT COALESCE(SUM(ATT), 0), COALESCE(COUNT(*), 1), COALESCE(MAX(TAR), 0) FROM mlo WHERE schedule_id = ? AND ILO = ?";
            $stmtSumATT = $conn->prepare($querySumATT);
            $stmtSumATT->bind_param("ii", $schedule_id, $ilo);
            $stmtSumATT->execute();
            $stmtSumATT->bind_result($sum_att, $count_ilo, $target);
            $stmtSumATT->fetch();
            $stmtSumATT->close();

            // ✅ Compute ATT Correctly
            $ilo_att = ($count_ilo > 0) ? ($sum_att / $count_ilo) : 0;

            // Compare ATT with TARGET to determine Remarks (RM)
            $remarks = ($ilo_att >= $target) ? "A" : "NA";

            // Check if ILO exists in mlos
            $checkILO = "SELECT COUNT(*) FROM mlos WHERE schedule_id = ? AND ILO = ?";
            $stmtCheck = $conn->prepare($checkILO);
            $stmtCheck->bind_param("ii", $schedule_id, $ilo);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                $updateILO = "UPDATE mlos SET ATT = ?, TAR = ?, RM = ? WHERE schedule_id = ? AND ILO = ?";
                $stmtUpdate = $conn->prepare($updateILO);
                $stmtUpdate->bind_param("ddsii", $ilo_att, $target, $remarks, $schedule_id, $ilo);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            } else {
                $insertILO = "INSERT INTO mlos (schedule_id, ILO, ATT, TAR, RM) VALUES (?, ?, ?, ?, ?)";
                $stmtInsert = $conn->prepare($insertILO);
                $stmtInsert->bind_param("iidds", $schedule_id, $ilo, $ilo_att, $target, $remarks);
                $stmtInsert->execute();
                $stmtInsert->close();
            }

            // Step 4: Summarize APS based on ILO and insert into maps table
            $apsQuery = "SELECT GROUP_CONCAT(DISTINCT AP ORDER BY AP ASC SEPARATOR ', ') AS summarized_APS FROM mlo WHERE schedule_id = ? AND ILO = ?";
            $stmtAPS = $conn->prepare($apsQuery);
            $stmtAPS->bind_param("ii", $schedule_id, $ilo);
            $stmtAPS->execute();
            $apsResult = $stmtAPS->get_result()->fetch_assoc();
            $summarized_APS = $apsResult['summarized_APS'] ?? '';
            $stmtAPS->close();

            // Get the exam type dynamically from mlo
            $examTypeQuery = "SELECT DISTINCT exam_type FROM mlo WHERE schedule_id = ?";
            $stmtExamType = $conn->prepare($examTypeQuery);
            $stmtExamType->bind_param("i", $schedule_id);
            $stmtExamType->execute();
            $examTypeResult = $stmtExamType->get_result()->fetch_assoc();
            $exam_type = $examTypeResult['exam_type'] ?? 'Midterm';
            $stmtExamType->close();

            $checkQuery = "SELECT ID FROM maps WHERE schedule_id = ? AND ILO = ?";
            $stmtCheck = $conn->prepare($checkQuery);
            $stmtCheck->bind_param("ii", $schedule_id, $ilo);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $exists = $result->num_rows > 0;
            $stmtCheck->close();

            if ($exists) {
                $updateMapsQuery = "UPDATE maps SET APS = ?, exam_type = ? WHERE schedule_id = ? AND ILO = ?";
                $stmt = $conn->prepare($updateMapsQuery);
                $stmt->bind_param("ssii", $summarized_APS, $exam_type, $schedule_id, $ilo);
            } else {
                $insertMapsQuery = "INSERT INTO maps (schedule_id, exam_type, ILO, APS) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insertMapsQuery);
                $stmt->bind_param("isis", $schedule_id, $exam_type, $ilo, $summarized_APS);
            }

            $stmt->execute();
            $stmt->close();
        }

        echo "MLOS and MAPS successfully updated.";
    } else {
        echo "Invalid request.";
    }
}

$conn->close();
?>
