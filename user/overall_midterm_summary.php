<?php
include '../database/dbconn.php';
session_start();

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

//Code for fetching data//
$sql = "SELECT id, schedule_id, ILO, QN, NOP, NOS, ATT, TAR, RM, AP FROM mlo WHERE schedule_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

//Fetch data from mlos table//
$mlos_query = "SELECT CO_ID, schedule_id, CO, ILO, ATT, TAR, RM FROM mlos WHERE schedule_id = ?";
$stmt = $conn->prepare($mlos_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$mlos_result = $stmt->get_result();
$stmt->close();

// Fetch data from mcos table//
$mcos_query = "SELECT MCO_ID, schedule_id, CO, ATT, TAR, RM 
               FROM mcos 
               WHERE schedule_id = ? 
               ORDER BY CAST(CO AS UNSIGNED) ASC";
$stmt = $conn->prepare($mcos_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$mcos_result = $stmt->get_result();
$stmt->close();

// Fetch and correct APS summary in maps table//
$maps_query = "SELECT ID, schedule_id, exam_type, ILO, APS, p_timeline, comments FROM maps WHERE schedule_id = ?";
$stmt = $conn->prepare($maps_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$maps_result = $stmt->get_result();
$stmt->close();


// Initialize schedule variable

$attainment_score = file_get_contents('../data/attainment_score.txt');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CQI Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/css/midterms.css">
    <link rel="stylesheet" href="assets/css/oms.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar_class.php'; ?>

        <div id="mainContent" class="container p-4">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="mlos-tab" data-bs-toggle="tab" data-bs-target="#mlos" type="button" role="tab" aria-controls="mlos" aria-selected="true">Midterm Learning Outcome Summary</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="mcos-tab" data-bs-toggle="tab" data-bs-target="#mcos" type="button" role="tab" aria-controls="mcos" aria-selected="false">Midterm Course Outcome Summary</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="maps-tab" data-bs-toggle="tab" data-bs-target="#maps" type="button" role="tab" aria-controls="maps" aria-selected="false">Midterm Action Plan Summary</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-4" id="tabContent">
                <!-- MLOS Tab -->
                <div class="tab-pane fade show active" id="mlos" role="tabpanel" aria-labelledby="mlos-tab">
                    <div class="table-container">
                        <?php if ($result->num_rows > 0): ?>
                        <h3>Midterm Learning Outcome Summary - Schedule ID: <?php echo htmlspecialchars($schedule_id); ?></h3>
                        <?php else: ?>
                            <h1>No Schedule found for the provided Schedule ID.</h1>
                        <?php endif; ?>
                        <div class="table-wrapper">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col" class="hidden">CO_ID</th>
                                        <th scope="col" class="hidden">Schedule ID</th>
                                        <th scope="col">COURSE OUTCOME</th>
                                        <th scope="col">INTENDED LEARNING OUTCOME</th>
                                        <th scope="col">ATTAINMENT</th>
                                        <th scope="col">TARGET</th>
                                        <th scope="col">REMARKS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $mlos_result->fetch_assoc()){ ?>
                                        <tr id="mlos_<?= $row['CO_ID']; ?>">
                                            <td class="hidden"><?= $row['CO_ID']; ?></td>
                                            <td class="hidden"><?= $row['schedule_id']; ?></td>
                                            <th class="highlight editable" contenteditable="true" data-id="<?= $row['CO_ID']; ?>" data-column="CO"><?= $row['CO']; ?></th>
                                            <td><?= $row['ILO']; ?></td>
                                            <td><?= number_format($row['ATT'], 2); ?></td>
                                            <td><?= number_format($row['TAR'], 2); ?></td>
                                            <td class="<?= $row['RM'] == 'A' ? '' : 'not-attained'; ?>">
                                                <?= $row['RM'] == 'A' ? 'ATTAINED' : 'NOT ATTAINED'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button id="sumCOATT" class="btn btn-sm btn-success mb-1">Save</button>
                        </div>
                    </div>
                </div>

                <!-- MCOS Tab -->
                <div class="tab-pane fade" id="mcos" role="tabpanel" aria-labelledby="mcos-tab">
                    <div class="table-container">
                        <h3>Midterm Course Outcome Summary</h3>
                        <div class="table-wrapper">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col" class="hidden">ID</th>
                                        <th scope="col" class="hidden">Schedule ID</th>
                                        <th scope="col">COURSE OUTCOME</th>
                                        <th scope="col">ATTAINMENT</th>
                                        <th scope="col">TARGET</th>
                                        <th scope="col">REMARKS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $mcos_result->fetch_assoc()) { ?>
                                        <tr id="mcos_<?= $row['MCO_ID']; ?>">
                                            <td class="hidden"><?= $row['MCO_ID']; ?></td>
                                            <td class="hidden"><?= $row['schedule_id']; ?></td>
                                            <td><?= $row['CO']; ?></td>
                                            <td><?= number_format($row['ATT'], 2); ?></td>
                                            <td><?= number_format($row['TAR'], 2); ?></td>
                                            <td class="<?= $row['RM'] == 'A' ? '' : 'not-attained'; ?>">
                                                <?= $row['RM'] == 'A' ? 'ATTAINED' : 'NOT ATTAINED'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- MAPS Tab -->
                <div class="tab-pane fade" id="maps" role="tabpanel" aria-labelledby="maps-tab">
                    <div class="table-container">
                        <h3>Midterm Action Plan Summary</h3>
                        <div class="table-wrapper">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col" class="hidden">ID</th>
                                        <th scope="col" class="hidden">Schedule ID</th>
                                        <th scope="col" class="hidden">EXAM TYPES</th>
                                        <th scope="col">INTENDED LEARNING OUTCOME</th>
                                        <th scope="col">ACTION PLAN SUMMARY</th>
                                        <th scope="col">PROPOSED TIMELINE</th>
                                        <th scope="col">COMMENT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $maps_result->fetch_assoc()) { ?>
                                        <tr id="maps_<?= $row['ID']; ?>">
                                            <td class="hidden"><?= $row['ID']; ?></td>
                                            <td class="hidden"><?= $row['schedule_id']; ?></td>
                                            <td class="hidden"><?= $row['exam_type']; ?></td>
                                            <td><?= $row['ILO']; ?></td>
                                            <td><?= $row['APS']; ?></td>
                                            <td class="highlight editable" contenteditable="true" data-id="<?= $row['ID']; ?>" data-column="p_timeline"><?= $row['p_timeline']; ?></td>
                                            <td><?=$row['comments']?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                        <a class="btn btn-sm btn-success mb-1" href="mprint.php?schedule_id=<?php echo $schedule_id; ?>&exam_type=midterm">Print</a>              
                        </div>                
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $("#sumCOATT").on("click", function () {
            $.post("./operations/mcos_att.php", { schedule_id: "<?= $schedule_id; ?>" }, function (response) {
                alert(response);
                location.reload();
            });
        });
    </script>
    <script src="./assets/js/mlo_functions.js"></script>
    <script src="/cqi/js/bootstrap.bundle.min.js"></script>
</body>
</html>