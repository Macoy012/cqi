<?php
include '../../database/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $question = $_POST['question'] ?? '';
    $checked = $_POST['checked'] ?? '0';
    $max_question = isset($_POST['max_question']) ? intval($_POST['max_question']) : 0;

    if ($student_id && $question) {
        $column = "Q" . intval($question);
        $value = intval($checked); // 1 if checked, 0 if unchecked

        // Update mclassrec
        $sql = "UPDATE fclassrec SET $column = ? WHERE fid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $value, $student_id);

        if ($stmt->execute()) {
            // ✅ Nullify all Qx > max_question
            if ($max_question > 0) {
                $columnsResult = $conn->query("SHOW COLUMNS FROM mclassrec");
                $nullifyColumns = [];

                while ($col = $columnsResult->fetch_assoc()) {
                    if (preg_match('/^Q(\d+)$/', $col['Field'], $matches)) {
                        $qNum = intval($matches[1]);
                        if ($qNum > $max_question) {
                            $nullifyColumns[] = "Q$qNum = NULL";
                        }
                    }
                }

                if (!empty($nullifyColumns)) {
                    $nullifySQL = "UPDATE fclassrec SET " . implode(", ", $nullifyColumns) . " WHERE fid = ?";
                    $stmtNullify = $conn->prepare($nullifySQL);
                    $stmtNullify->bind_param("i", $student_id);
                    $stmtNullify->execute();
                    $stmtNullify->close();
                }
            }

            // ✅ After successful update, run MLO logic

            // First, get the schedule_id of this student
            $scheduleQuery = "SELECT schedule_id FROM fclassrec WHERE fid = ?";
            $stmtSch = $conn->prepare($scheduleQuery);
            $stmtSch->bind_param("i", $student_id);
            $stmtSch->execute();
            $stmtSch->bind_result($schedule_id);
            $stmtSch->fetch();
            $stmtSch->close();

            if ($schedule_id) {
                $qn = intval($question);

                // Count total students for this schedule
                $countStudents = "SELECT COUNT(*) FROM fclassrec WHERE schedule_id = ?";
                $stmt = $conn->prepare($countStudents);
                $stmt->bind_param("i", $schedule_id);
                $stmt->execute();
                $stmt->bind_result($nos);
                $stmt->fetch();
                $stmt->close();

                // Count how many passed for this QN (NOP)
                $sqlNOP = "SELECT COUNT(*) FROM fclassrec WHERE schedule_id = ? AND Q$qn = 1";
                $stmt = $conn->prepare($sqlNOP);
                $stmt->bind_param("i", $schedule_id);
                $stmt->execute();
                $stmt->bind_result($nop);
                $stmt->fetch();
                $stmt->close();

                // Get target attainment
                $TAR = intval(trim(file_get_contents('../../data/attainment_score.txt')));

                // Update TAR for all QNs in this schedule
                $updateTARAll = "UPDATE flo SET TAR = ? WHERE schedule_id = ?";
                $stmtTAR = $conn->prepare($updateTARAll);
                $stmtTAR->bind_param("ii", $TAR, $schedule_id);
                $stmtTAR->execute();
                $stmtTAR->close();

                $att = ($nos > 0) ? round(($nop / $nos) * 100) : 0;
                $rm = ($att >= $TAR) ? 'A' : 'NA';

                // Check if MLO exists
                $check = "SELECT COUNT(*) FROM flo WHERE schedule_id = ? AND QN = ?";
                $stmt = $conn->prepare($check);
                $stmt->bind_param("ii", $schedule_id, $qn);
                $stmt->execute();
                $stmt->bind_result($exists);
                $stmt->fetch();
                $stmt->close();

                if ($exists > 0) {
                    // Update
                    $update = "UPDATE flo SET NOP = ?, NOS = ?, ATT = ?, TAR = ?, RM = ? WHERE schedule_id = ? AND QN = ?";
                    $stmt = $conn->prepare($update);
                    $stmt->bind_param("iiiisii", $nop, $nos, $att, $TAR, $rm, $schedule_id, $qn);
                } else {
                    // Insert
                    $insert = "INSERT INTO flo (schedule_id, QN, NOP, NOS, ATT, TAR, RM) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert);
                    $stmt->bind_param("iiiiisi", $schedule_id, $qn, $nop, $nos, $att, $TAR, $rm);
                }

                $stmt->execute();
                $stmt->close();
            }

            echo "Success";
        } else {
            echo "Failed to update";
        }
    } else {
        echo "Invalid data";
    }
} else {
    echo "Invalid request method";
}
?>
