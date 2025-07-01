<?php
include '../database/dbconn.php';
session_start(); // Ensure session is started

// Check if the user is logged in as a Facilitator
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Faculty', 'Personhead'])) {
    error_log("Access denied - Session usertype: " . $_SESSION['usertype']);
    header("Location: ../index.php?error=unauthorized");
    exit();
}

if (isset($_GET['schedule_id'])) {
    $schedule_id = $_GET['schedule_id'];
    $_SESSION['schedule_id'] = $schedule_id;
} elseif (isset($_SESSION['schedule_id'])) {
    $schedule_id = $_SESSION['schedule_id'];
} else {
    $schedule_id = '';
}

// Fetch data from mlos table
$flos_query = "SELECT CO_ID, schedule_id, CO, ILO, ATT, TAR, RM FROM flos WHERE schedule_id = ?";
$stmt = $conn->prepare($flos_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$flos_result = $stmt->get_result();
$stmt->close();

// Fetch data from fcos table
$fcos_query = "SELECT FCO_ID, schedule_id, CO, ATT, TAR, RM 
               FROM fcos 
               WHERE schedule_id = ? 
               ORDER BY CAST(CO AS UNSIGNED) ASC";
$stmt = $conn->prepare($fcos_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$fcos_result = $stmt->get_result();
$stmt->close();

// Fetch data from maps table
$faps_query = "SELECT ID, schedule_id, exam_type, ILO, APS, p_timeline FROM faps WHERE schedule_id = ?";
$stmt = $conn->prepare($faps_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$faps_result = $stmt->get_result();
$stmt->close();

// Get current date
$current_date = date("F j, Y"); // Format: March 10, 2025
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Tables</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        .print-container {
            width: 210mm;
            margin: auto;
            padding: 20px;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .hidden {
            display: none;
        }
        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin: 20px;
        }
        .print-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: blue;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .signature-container {
            margin-top: 20px;
        }
        .signature {
            margin-top: 10px;
        }
        .signature span {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .date-container {
            position: absolute;
            right: 20px;
            top: 20px;
            text-align: right;
        }
        @media print {
            .button-container {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        <h2>Finals Learning Outcome Summary Table</h2>
        <table>
            <thead>
                <tr>
                    <th class="hidden">CO_ID</th>
                    <th class="hidden">Schedule ID</th>
                    <th>COURSE OUTCOME</th>
                    <th>INTENDED LEARNING OUTCOME</th>
                    <th>ATTAINMENT</th>
                    <th>TARGET</th>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $flos_result->fetch_assoc()) { ?>
                    <tr>
                        <td class="hidden"><?= $row['CO_ID']; ?></td>
                        <td class="hidden"><?= $row['schedule_id']; ?></td>
                        <td><?= $row['CO']; ?></td>
                        <td><?= $row['ILO']; ?></td>
                        <td><?= number_format($row['ATT'], 2); ?></td>
                        <td><?= number_format($row['TAR'], 2); ?></td>
                        <td><?= ($row['RM'] == 'A') ? 'ATTAINED' : 'NOT ATTAINED'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2>Finals Course Outcome Summary Table</h2>
        <table>
            <thead>
                <tr>
                    <th class="hidden">ID</th>
                    <th class="hidden">Schedule ID</th>
                    <th>COURSE OUTCOME</th>
                    <th>ATTAINMENT</th>
                    <th>TARGET</th>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $fcos_result->fetch_assoc()) { ?>
                    <tr>
                        <td class="hidden"><?= $row['FCO_ID']; ?></td>
                        <td class="hidden"><?= $row['schedule_id']; ?></td>
                        <td><?= $row['CO']; ?></td>
                        <td><?= number_format($row['ATT'], 2); ?></td>
                        <td><?= number_format($row['TAR'], 2); ?></td>
                        <td><?= ($row['RM'] == 'A') ? 'ATTAINED' : 'NOT ATTAINED'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2>Finals Action Plan Summary Table</h2>
        <table>
            <thead>
                <tr>
                    <th class="hidden">ID</th>
                    <th class="hidden">Schedule ID</th>
                    <th class="hidden">Exam Type</th>
                    <th>INTENDED LEARNING OUTCOME</th>
                    <th>ACTION PLAN SUMMARY</th>
                    <th>PROPOSED TIMELINE</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $faps_result->fetch_assoc()) { ?>
                    <tr>
                        <td class="hidden"><?= $row['ID']; ?></td>
                        <td class="hidden"><?= $row['schedule_id']; ?></td>
                        <td class="hidden"><?= $row['exam_type']; ?></td>
                        <td><?= $row['ILO']; ?></td>
                        <td><?= $row['APS']; ?></td>
                        <td><?= $row['p_timeline']; ?></td> 
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="signature-container">
            <div class="signature">
                <br><span>PREPARED BY:</span><br
                <strong><?= $_SESSION['username']; ?></strong>
            </div>
            <div class="signature">
                <span>ASSESSED BY:</span>
                _________________________
            </div>
            <div class="signature">
                <span>ENDORSED BY:</span>
                _________________________
            </div>
            <div class="signature">
                <span>APPROVED BY:</span>
                _________________________
            </div>
        </div>
    </div>

    <div class="date-container">
        <span><strong>Date:</strong> <?= $current_date; ?></span>
    </div>

    <div class="button-container">
        <button class="print-button" onclick="window.print()">Print</button>
        <button class="print-button" onclick="window.location.href='overall_finals_summary.php?schedule_id=<?= $schedule_id; ?>';">
            Go Back
        </button>
    </div>

</body>
</html>

<?php $conn->close(); ?>