<?php
include '../../database/dbconn.php';
session_start();

$schedule_id = isset($_POST['schedule_id']) ? intval($_POST['schedule_id']) : null;

$schedule_sql = "SELECT course_code, year, section, semester, academic_year FROM schedule WHERE schedule_id = ?";
$stmt = $conn->prepare($schedule_sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

if ($schedule = $result->fetch_assoc()) {
    $course = $schedule['course_code'];
    $year = $schedule['year'];
    $section = $schedule['section'];
    $semester = $schedule['semester'];
    $academic_year = $schedule['academic_year'];

    $check_sql = "SELECT COUNT(*) FROM listofstudents WHERE studID = ? AND schedule_id = ?";
    $check_stmt = $conn->prepare($check_sql);

    $insert_sql = "INSERT INTO listofstudents (studID, fullname, course_code, year, section, semester, academic_year, schedule_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);

    $insert_mclassrec = "INSERT INTO mclassrec (schedule_id, studID, fullname, exam_type) VALUES (?, ?, ?, ?)";
    $mclassrec_stmt = $conn->prepare($insert_mclassrec);

    $insert_fclassrec = "INSERT INTO fclassrec (schedule_id, studID, fullname, exam_type) VALUES (?, ?, ?, ?)";
    $fclassrec_stmt = $conn->prepare($insert_fclassrec);

    if (isset($_POST['csv_data'])) {
        $csv = $_POST['csv_data'];
        $lines = explode("\n", $csv);
        array_shift($lines); // Skip header

        foreach ($lines as $line) {
            $columns = str_getcsv($line);
            if (count($columns) < 3) continue;

            $studID = trim($columns[2]);
            $fullname = trim($columns[1]);

            if (!empty($studID) && !empty($fullname)) {
                $check_stmt->bind_param("si", $studID, $schedule_id);
                $check_stmt->execute();
                $check_stmt->bind_result($count);
                $check_stmt->fetch();
                $check_stmt->free_result();

                if ($count == 0) {
                    $insert_stmt->bind_param("sssssssi", $studID, $fullname, $course, $year, $section, $semester, $academic_year, $schedule_id);
                    $insert_stmt->execute();

                    $exam_type_mid = "Midterm";
                    $mclassrec_stmt->bind_param("isss", $schedule_id, $studID, $fullname, $exam_type_mid);
                    $mclassrec_stmt->execute();

                    $exam_type_final = "Finals";
                    $fclassrec_stmt->bind_param("isss", $schedule_id, $studID, $fullname, $exam_type_final);
                    $fclassrec_stmt->execute();
                }
            }
        }
    }

    if (isset($_POST['studID']) && is_array($_POST['studID'])) {
        foreach ($_POST['studID'] as $key => $studID) {
            $fullname = $_POST['fullname'][$key];

            $check_stmt->bind_param("si", $studID, $schedule_id);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->free_result();

            if ($count == 0) {
                $insert_stmt->bind_param("sssssssi", $studID, $fullname, $course, $year, $section, $semester, $academic_year, $schedule_id);
                $insert_stmt->execute();

                $exam_type_mid = "Midterm";
                $mclassrec_stmt->bind_param("isss", $schedule_id, $studID, $fullname, $exam_type_mid);
                $mclassrec_stmt->execute();

                $exam_type_final = "Finals";
                $fclassrec_stmt->bind_param("isss", $schedule_id, $studID, $fullname, $exam_type_final);
                $fclassrec_stmt->execute();
            }
        }
    }

    // ✅ Update NOS
    $countQuery = "SELECT COUNT(*) AS total_students FROM listofstudents WHERE schedule_id = ?";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->bind_param("i", $schedule_id);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $rowCount = $resultCount->fetch_assoc();
    $total_students = $rowCount['total_students'];
    $stmtCount->close();

    $updateNOS = "UPDATE mlo SET NOS = ? WHERE schedule_id = ?";
    $stmtUpdateNOS = $conn->prepare($updateNOS);
    $stmtUpdateNOS->bind_param("ii", $total_students, $schedule_id);
    $stmtUpdateNOS->execute();
    $stmtUpdateNOS->close();

    $updateNOS_flo = "UPDATE flo SET NOS = ? WHERE schedule_id = ?";
    $stmtUpdateNOS_flo = $conn->prepare($updateNOS_flo);
    $stmtUpdateNOS_flo->bind_param("ii", $total_students, $schedule_id);
    $stmtUpdateNOS_flo->execute();
    $stmtUpdateNOS_flo->close();

    // ✅ Get TAR
    $TAR = intval(trim(file_get_contents('../../data/attainment_score.txt')));

    // ✅ Update NOP, ATT, RM for mlo
    $mlo_sql = "SELECT id, QN, NOP FROM mlo WHERE schedule_id = ?";
    $stmt_mlo = $conn->prepare($mlo_sql);
    $stmt_mlo->bind_param("i", $schedule_id);
    $stmt_mlo->execute();
    $result_mlo = $stmt_mlo->get_result();
    while ($row = $result_mlo->fetch_assoc()) {
        $id = $row['id'];
        $qn = $row['QN'];
        $nop = intval($row['NOP']);
        $att = ($total_students > 0) ? round(($nop / $total_students) * 100) : 0;
        $rm = ($att >= $TAR) ? 'A' : 'NA';

        $updateAtt = "UPDATE mlo SET ATT = ?, TAR = ?, RM = ? WHERE id = ?";
        $stmtUpdateAtt = $conn->prepare($updateAtt);
        $stmtUpdateAtt->bind_param("iisi", $att, $TAR, $rm, $id);
        $stmtUpdateAtt->execute();
        $stmtUpdateAtt->close();
    }
    $stmt_mlo->close();

    // ✅ Update NOP, ATT, RM for flo
    $flo_sql = "SELECT id, QN, NOP FROM flo WHERE schedule_id = ?";
    $stmt_flo = $conn->prepare($flo_sql);
    $stmt_flo->bind_param("i", $schedule_id);
    $stmt_flo->execute();
    $result_flo = $stmt_flo->get_result();
    while ($row = $result_flo->fetch_assoc()) {
        $id = $row['id'];
        $qn = $row['QN'];
        $nop = intval($row['NOP']);
        $att = ($total_students > 0) ? round(($nop / $total_students) * 100) : 0;
        $rm = ($att >= $TAR) ? 'A' : 'NA';

        $updateAtt = "UPDATE flo SET ATT = ?, TAR = ?, RM = ? WHERE id = ?";
        $stmtUpdateAtt = $conn->prepare($updateAtt);
        $stmtUpdateAtt->bind_param("iisi", $att, $TAR, $rm, $id);
        $stmtUpdateAtt->execute();
        $stmtUpdateAtt->close();
    }
    $stmt_flo->close();

    $check_stmt->close();
    $insert_stmt->close();
    $mclassrec_stmt->close();
    $fclassrec_stmt->close();
    $stmt->close();
    $conn->close();

    echo "<script>alert('Students saved successfully!'); window.location.href='/cqi/user/class.php?schedule_id=$schedule_id';</script>";
} else {
    echo "Schedule details not found.";
}
?>
