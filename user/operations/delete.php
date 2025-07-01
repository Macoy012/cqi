<?php
include '../../database/dbconn.php';
session_start();

if (isset($_GET['id']) && isset($_GET['schedule_id'])) {
    $id = $_GET['id'];
    $schedule_id = $_GET['schedule_id'];

    // ✅ Get the studID before deleting
    $sql_get_studID = "SELECT studID FROM listofstudents WHERE id = ?";
    $stmt = $conn->prepare($sql_get_studID);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $studID = $row['studID'];

        // ✅ Delete from mclassrec
        $sql_delete_mclassrec = "DELETE FROM mclassrec WHERE studID = ? AND schedule_id = ?";
        $stmt_mclassrec = $conn->prepare($sql_delete_mclassrec);
        $stmt_mclassrec->bind_param("si", $studID, $schedule_id);
        $stmt_mclassrec->execute();
        $stmt_mclassrec->close();

        // ✅ Delete from fclassrec
        $sql_delete_fclassrec = "DELETE FROM fclassrec WHERE studID = ? AND schedule_id = ?";
        $stmt_fclassrec = $conn->prepare($sql_delete_fclassrec);
        $stmt_fclassrec->bind_param("si", $studID, $schedule_id);
        $stmt_fclassrec->execute();
        $stmt_fclassrec->close();

        // ✅ Delete from listofstudents
        $sql_delete_listofstudents = "DELETE FROM listofstudents WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete_listofstudents);
        $stmt_delete->bind_param("i", $id);

        if ($stmt_delete->execute()) {
            $stmt_delete->close();

            // ✅ Get new total student count
            $countQuery = "SELECT COUNT(*) AS total_students FROM listofstudents WHERE schedule_id = ?";
            $stmtCount = $conn->prepare($countQuery);
            $stmtCount->bind_param("i", $schedule_id);
            $stmtCount->execute();
            $resultCount = $stmtCount->get_result();
            $rowCount = $resultCount->fetch_assoc();
            $total_students = $rowCount['total_students'];
            $stmtCount->close();

            // ✅ Update NOS in mlo and flo
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

            // ✅ Get TAR value
            $TAR = intval(trim(file_get_contents('../../data/attainment_score.txt')));

            // ✅ Recalculate ATT, RM in mlo
            $sql_mlo = "SELECT id, NOP FROM mlo WHERE schedule_id = ?";
            $stmt_mlo = $conn->prepare($sql_mlo);
            $stmt_mlo->bind_param("i", $schedule_id);
            $stmt_mlo->execute();
            $result_mlo = $stmt_mlo->get_result();

            while ($row = $result_mlo->fetch_assoc()) {
                $id = $row['id'];
                $nop = intval($row['NOP']);
                $att = ($total_students > 0) ? round(($nop / $total_students) * 100) : 0;
                $rm = ($att >= $TAR) ? 'A' : 'NA';

                $updateAtt = "UPDATE mlo SET ATT = ?, TAR = ?, RM = ? WHERE id = ?";
                $stmtUpdate = $conn->prepare($updateAtt);
                $stmtUpdate->bind_param("iisi", $att, $TAR, $rm, $id);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            $stmt_mlo->close();

            // ✅ Recalculate ATT, RM in flo
            $sql_flo = "SELECT id, NOP FROM flo WHERE schedule_id = ?";
            $stmt_flo = $conn->prepare($sql_flo);
            $stmt_flo->bind_param("i", $schedule_id);
            $stmt_flo->execute();
            $result_flo = $stmt_flo->get_result();

            while ($row = $result_flo->fetch_assoc()) {
                $id = $row['id'];
                $nop = intval($row['NOP']);
                $att = ($total_students > 0) ? round(($nop / $total_students) * 100) : 0;
                $rm = ($att >= $TAR) ? 'A' : 'NA';

                $updateAtt = "UPDATE flo SET ATT = ?, TAR = ?, RM = ? WHERE id = ?";
                $stmtUpdate = $conn->prepare($updateAtt);
                $stmtUpdate->bind_param("iisi", $att, $TAR, $rm, $id);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            $stmt_flo->close();

            $conn->close();

            echo "<script>alert('Student deleted successfully!'); window.location.href='/cqi/user/class.php?schedule_id=$schedule_id';</script>";
        } else {
            echo "<script>alert('Error deleting student.'); window.location.href='/cqi/user/class.php?schedule_id=$schedule_id';</script>";
        }
    } else {
        echo "<script>alert('Student not found.'); window.location.href='/cqi/user/class.php?schedule_id=$schedule_id';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request.'); window.location.href='class.php';</script>";
}
?>
