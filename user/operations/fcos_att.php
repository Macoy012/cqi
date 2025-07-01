<?php
include '../../database/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['schedule_id']) && !empty(trim($_POST['schedule_id']))) {
        $schedule_id = htmlspecialchars(trim($_POST['schedule_id']));
        // Proceed with your logic here, e.g., database operations
    } else {
        echo "Schedule ID is missing.";
        
    }


    // ✅ Sum ATT and group by CO #
    $sumQuery = "SELECT CO, 
                        SUM(CAST(ATT AS SIGNED)) AS total_ATT, 
                        MAX(TAR) AS tar_value  
                 FROM flos 
                 WHERE schedule_id = ?
                 GROUP BY CO";

    $stmtSum = $conn->prepare($sumQuery);
    $stmtSum->bind_param("i", $schedule_id);
    $stmtSum->execute();
    $sumResult = $stmtSum->get_result();

    $existingCOs = []; // Track existing CO values

    if ($sumResult->num_rows > 0) {
        while ($row = $sumResult->fetch_assoc()) {
            $co = $row['CO'];
            $total_ATT = (int)$row['total_ATT'];
            $tar = (int)$row['tar_value'];

            // ✅ Get the count of ILOs under each CO
            $countCOQuery = "SELECT COUNT(*) as countCO FROM flos WHERE schedule_id = ? AND CO = ?";
            $stmtCountCO = $conn->prepare($countCOQuery);
            $stmtCountCO->bind_param("is", $schedule_id, $co);
            $stmtCountCO->execute();
            $resultCountCO = $stmtCountCO->get_result()->fetch_assoc();
            $countCO = (int)$resultCountCO['countCO']; // Count of ILOs under CO
            $stmtCountCO->close();

            // ✅ Compute ATT correctly
            $computed_ATT = ($countCO > 0) ? ceil($total_ATT / $countCO) : 0;

            // ✅ Compute RM (A if ATT >= TAR, otherwise NA)
            $rm = ($computed_ATT >= $tar) ? 'A' : 'NA';

            // ✅ Check if record exists in `mcos`
            $checkQuery = "SELECT COUNT(*) FROM fcos WHERE schedule_id = ? AND CO = ?";
            $stmtCheck = $conn->prepare($checkQuery);
            $stmtCheck->bind_param("is", $schedule_id, $co);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                // ✅ Update existing record
                $updateQuery = "UPDATE fcos 
                                SET ATT = ?, TAR = ?, RM = ? 
                                WHERE schedule_id = ? AND CO = ?";
                $stmtUpdate = $conn->prepare($updateQuery);
                $stmtUpdate->bind_param("iisis", $computed_ATT, $tar, $rm, $schedule_id, $co);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            } else {
                // ✅ Insert new record if not found
                $insertQuery = "INSERT INTO fcos (schedule_id, CO, ATT, TAR, RM) 
                                VALUES (?, ?, ?, ?, ?)";
                $stmtInsert = $conn->prepare($insertQuery);
                $stmtInsert->bind_param("isiss", $schedule_id, $co, $computed_ATT, $tar, $rm);
                $stmtInsert->execute();
                $stmtInsert->close();
            }

            // ✅ Track CO values that exist in `mlos`
            $existingCOs[] = $co;
        }
    } else {
        echo "No records found in `mlos` for the given Schedule ID.";
    }

    $stmtSum->close();

    // ✅ Delete entries in `mcos` if CO is **not found** in `mlos`
    if (!empty($existingCOs)) {
        $placeholders = implode(",", array_fill(0, count($existingCOs), "?"));
        $deleteQuery = "DELETE FROM fcos WHERE schedule_id = ? AND CO NOT IN ($placeholders)";
        $stmtDelete = $conn->prepare($deleteQuery);

        // Bind parameters dynamically
        $types = str_repeat("s", count($existingCOs)); // 's' for string CO values
        $stmtDelete->bind_param("i" . $types, $schedule_id, ...$existingCOs);
        $stmtDelete->execute();
        $stmtDelete->close();
    } else {
        // If no `CO` exists in `mlos`, delete all records for this `schedule_id` in `mcos`
        $deleteAllQuery = "DELETE FROM fcos WHERE schedule_id = ?";
        $stmtDeleteAll = $conn->prepare($deleteAllQuery);
        $stmtDeleteAll->bind_param("i", $schedule_id);
        $stmtDeleteAll->execute();
        $stmtDeleteAll->close();
    }

    echo "Summation, update, and cleanup completed successfully.";
}

$conn->close();
?>
