<?php
include '../../database/dbconn.php';
session_start();

$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $schedule_id = isset($input['schedule_id']) ? intval($input['schedule_id']) : null;
    $studID = isset($input['studID']) ? trim($input['studID']) : null;
    $fullname = isset($input['fullname']) ? trim($input['fullname']) : null;

    if (!$studID || !$fullname || !$schedule_id) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit();
    }

    try {
        $schedule_sql = "SELECT * FROM schedule WHERE schedule_id = ?";
        $schedule_stmt = $conn->prepare($schedule_sql);
        $schedule_stmt->bind_param('i', $schedule_id);
        $schedule_stmt->execute();
        $schedule_result = $schedule_stmt->get_result();

        if ($schedule_result->num_rows > 0) {
            $schedule = $schedule_result->fetch_assoc();
            $course_code = $schedule['course_code'];
            $year = $schedule['year'];
            $section = $schedule['section'];
            $semester = $schedule['semester'];
            $academic_year = $schedule['academic_year'];

            $sql = "INSERT INTO listofstudents (studID, fullname, schedule_id, course_code, year, section, semester, academic_year) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssisssss', $studID, $fullname, $schedule_id, $course_code, $year, $section, $semester, $academic_year);

            if ($stmt->execute()) {
                // ✅ Insert into mclassrec (Midterm)
                $insert_mclassrec = "INSERT INTO mclassrec (schedule_id, studID, fullname, exam_type) VALUES (?, ?, ?, 'Midterm')";
                $mclassrec_stmt = $conn->prepare($insert_mclassrec);
                $mclassrec_stmt->bind_param('iss', $schedule_id, $studID, $fullname);
                $mclassrec_stmt->execute();
                $mclassrec_stmt->close();

                // ✅ Insert into fclassrec (Finals)
                $insert_fclassrec = "INSERT INTO fclassrec (schedule_id, studID, fullname, exam_type) VALUES (?, ?, ?, 'Finals')";
                $fclassrec_stmt = $conn->prepare($insert_fclassrec);
                $fclassrec_stmt->bind_param('iss', $schedule_id, $studID, $fullname);
                $fclassrec_stmt->execute();
                $fclassrec_stmt->close();

                // ✅ Read TAR
                $TAR = intval(trim(file_get_contents('../../data/attainment_score.txt')));

                // ✅ Count NOS
                $countQuery = "SELECT COUNT(*) AS total_students FROM listofstudents WHERE schedule_id = ?";
                $stmtCount = $conn->prepare($countQuery);
                $stmtCount->bind_param("i", $schedule_id);
                $stmtCount->execute();
                $resultCount = $stmtCount->get_result();
                $rowCount = $resultCount->fetch_assoc();
                $total_students = $rowCount['total_students'];
                $stmtCount->close();

                // ✅ Loop through all QN rows in mlo
                $query_mlo = "SELECT QN FROM mlo WHERE schedule_id = ?";
                $stmt_qn_mlo = $conn->prepare($query_mlo);
                $stmt_qn_mlo->bind_param("i", $schedule_id);
                $stmt_qn_mlo->execute();
                $result_qn_mlo = $stmt_qn_mlo->get_result();

                while ($row = $result_qn_mlo->fetch_assoc()) {
                    $qn = $row['QN'];

                    $stmt_nop = $conn->prepare("SELECT NOP FROM mlo WHERE schedule_id = ? AND QN = ?");
                    $stmt_nop->bind_param("ii", $schedule_id, $qn);
                    $stmt_nop->execute();
                    $res_nop = $stmt_nop->get_result();
                    $nop = ($res_nop->num_rows > 0) ? intval($res_nop->fetch_assoc()['NOP']) : 0;
                    $stmt_nop->close();

                    $att = ($total_students > 0) ? round(($nop / $total_students) * 100) : 0;
                    $rm = ($att >= $TAR) ? 'A' : 'NA';

                    $stmt_up = $conn->prepare("UPDATE mlo SET NOS = ?, ATT = ?, TAR = ?, RM = ? WHERE schedule_id = ? AND QN = ?");
                    $stmt_up->bind_param("iiisii", $total_students, $att, $TAR, $rm, $schedule_id, $qn);
                    $stmt_up->execute();
                    $stmt_up->close();
                }
                $stmt_qn_mlo->close();

                // ✅ Loop through all QN rows in flo
                $query_flo = "SELECT QN FROM flo WHERE schedule_id = ?";
                $stmt_qn_flo = $conn->prepare($query_flo);
                $stmt_qn_flo->bind_param("i", $schedule_id);
                $stmt_qn_flo->execute();
                $result_qn_flo = $stmt_qn_flo->get_result();

                while ($row = $result_qn_flo->fetch_assoc()) {
                    $qn = $row['QN'];

                    $stmt_nop = $conn->prepare("SELECT NOP FROM flo WHERE schedule_id = ? AND QN = ?");
                    $stmt_nop->bind_param("ii", $schedule_id, $qn);
                    $stmt_nop->execute();
                    $res_nop = $stmt_nop->get_result();
                    $nop = ($res_nop->num_rows > 0) ? intval($res_nop->fetch_assoc()['NOP']) : 0;
                    $stmt_nop->close();

                    $att = ($total_students > 0) ? round(($nop / $total_students) * 100) : 0;
                    $rm = ($att >= $TAR) ? 'A' : 'NA';

                    $stmt_up = $conn->prepare("UPDATE flo SET NOS = ?, ATT = ?, TAR = ?, RM = ? WHERE schedule_id = ? AND QN = ?");
                    $stmt_up->bind_param("iiisii", $total_students, $att, $TAR, $rm, $schedule_id, $qn);
                    $stmt_up->execute();
                    $stmt_up->close();
                }
                $stmt_qn_flo->close();

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid schedule ID.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
