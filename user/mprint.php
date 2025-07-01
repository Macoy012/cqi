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
$mlos_query = "SELECT CO_ID, schedule_id, CO, ILO, ATT, TAR, RM FROM mlos WHERE schedule_id = ?";
$stmt = $conn->prepare($mlos_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$mlos_result = $stmt->get_result();
$stmt->close();

// Fetch data from mcos table
$mcos_query = "SELECT MCO_ID, schedule_id, CO, ATT, TAR, RM 
               FROM mcos 
               WHERE schedule_id = ? 
               ORDER BY CAST(CO AS UNSIGNED) ASC";
$stmt = $conn->prepare($mcos_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$mcos_result = $stmt->get_result();
$stmt->close();

// Fetch data from maps table
$maps_query = "SELECT ID, schedule_id, exam_type, ILO, APS, p_timeline, comments FROM maps WHERE schedule_id = ?";
$stmt = $conn->prepare($maps_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$maps_result = $stmt->get_result();
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
            background-color: #4CAF50; /* Green */
            color: white;
            font-weight: bold;
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
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .signature-box {
            width: 48%;
            margin-bottom: 20px;
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

        .cvsu-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .cvsu-logo {
            width: 70px;
            height: auto;
        }

        .cvsu-text {
            text-align: center;
            line-height: 1.2;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .cvsu-main {
            font-weight: bold;
            font-size: 16px;
        }

        .cvsu-sub {
            font-weight: 600;
            font-size: 14px;
        }

        @media print {
            .button-container {
                display: none;
            }

            .cvsu-logo {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        <!-- CVSU Header -->
        <div class="cvsu-header">
            <img src="../cvsu-logo.png" alt="CVSU Logo" class="cvsu-logo">
            <div class="cvsu-text">
                <div>Republic of the Philippines</div>
                <div class="cvsu-main">CAVITE STATE UNIVERSITY</div>
                <div class="cvsu-sub">Don Severino de las Alas Campus</div>
                <div>Indang, Cavite</div>
            </div>
        </div>
        <h2>Midterm Learning Outcome Summary Table</h2>
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
                <?php while ($row = $mlos_result->fetch_assoc()) { ?>
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

        <h2>Midterm Course Outcome Summary Table</h2>
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
                <?php while ($row = $mcos_result->fetch_assoc()) { ?>
                    <tr>
                        <td class="hidden"><?= $row['MCO_ID']; ?></td>
                        <td class="hidden"><?= $row['schedule_id']; ?></td>
                        <td><?= $row['CO']; ?></td>
                        <td><?= number_format($row['ATT'], 2); ?></td>
                        <td><?= number_format($row['TAR'], 2); ?></td>
                        <td><?= ($row['RM'] == 'A') ? 'ATTAINED' : 'NOT ATTAINED'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2>Midterm Action Plan Summary Table</h2>
        <table>
            <thead>
                <tr>
                    <th class="hidden">ID</th>
                    <th class="hidden">Schedule ID</th>
                    <th class="hidden">Exam Type</th>
                    <th>INTENDED LEARNING OUTCOME</th>
                    <th>ACTION PLAN SUMMARY</th>
                    <th>PROPOSED TIMELINE</th>
                    <th>COMMENT</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $maps_result->fetch_assoc()) { ?>
                    <tr>
                        <td class="hidden"><?= $row['ID']; ?></td>
                        <td class="hidden"><?= $row['schedule_id']; ?></td>
                        <td class="hidden"><?= $row['exam_type']; ?></td>
                        <td><?= $row['ILO']; ?></td>
                        <td><?= $row['APS']; ?></td>
                        <td><?= $row['p_timeline']; ?></td>
                        <td><?= $row['comments']; ?></td> 
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="signature-container">
            <div class="signature-box">
                <span>PREPARED BY:</span><br>
                <strong><?= $_SESSION['username']; ?></strong>
            </div>
            <div class="signature-box">
                <span>ASSESSED BY:</span><br>
                _________________________
            </div>
            <div class="signature-box">
                <span>ENDORSED BY:</span><br>
                _________________________
            </div>
            <div class="signature-box">
                <span>APPROVED BY:</span><br>
                _________________________
            </div>
        </div>
    </div>

    <div class="date-container">
        <span><strong>Date:</strong> <?= $current_date; ?></span>
    </div>

    <div class="button-container">
        <button class="print-button" onclick="window.print()">Print</button>
        <button class="print-button" onclick="window.location.href='overall_midterm_summary.php?schedule_id=<?= $schedule_id; ?>';">
            Go Back
        </button>
    </div>

</body>
</html>

<?php $conn->close(); ?>