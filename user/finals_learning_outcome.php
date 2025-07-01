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


// Fetch MLO data
$sql = "SELECT id, schedule_id, ILO, QN, NOP, NOS, ATT, TAR, RM, AP FROM flo WHERE schedule_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

// Store MLO rows in an associative array by QN
$existingFLOs = [];
while ($row = $result->fetch_assoc()) {
    $existingFLOs[intval($row['QN'])] = $row;
}
$stmt->close();

// Get total number of questions from session or fallback to max QN
$total_q = $_SESSION['total_q'] ?? 0;
if (!$total_q) {
    $count_qn = "SELECT MAX(QN) FROM flo WHERE schedule_id = ?";
    $stmt = $conn->prepare($count_qn);
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $stmt->bind_result($max_qn);
    $stmt->fetch();
    $stmt->close();
    $total_q = intval($max_qn);
}

// Load attainment score
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
        <?php if ($schedule_id): ?>
            <h2>Midterm Learning Outcome - Schedule ID: <?php echo htmlspecialchars($schedule_id); ?></h2>
        <?php else: ?>
            <h2>No schedule found for the provided schedule ID.</h2>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ILO</th>
                                <th>ITEM #</th>
                                <th># OF PASSED</th>
                                <th># OF STUDENTS</th>
                                <th>ATTAINMENT</th>
                                <th>TARGET</th>
                                <th>REMARKS</th>
                                <th>ACTION PLAN</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        for ($qn = 1; $qn <= $total_q; $qn++):
                            $row = $existingFLOs[$qn] ?? [
                                'id' => '',
                                'ILO' => '',
                                'QN' => $qn,
                                'NOP' => '',
                                'NOS' => '',
                                'ATT' => '',
                                'TAR' => $attainment_score,
                                'RM' => '',
                                'AP' => ''
                            ];
                        ?>
                            <tr>
                                <td contenteditable="true" class="editable" data-id="<?php echo $row['id']; ?>" data-column="ILO">
                                    <?php echo htmlspecialchars($row['ILO']); ?>
                                </td>
                                <td><?php echo $row['QN']; ?></td>
                                <td><?php echo $row['NOP']; ?></td>
                                <td><?php echo $row['NOS']; ?></td>
                                <td><?php echo $row['ATT']; ?></td>
                                <td><?php echo $row['TAR']; ?></td>
                                <td>
                                    <?php
                                        echo ($row['RM'] == 'A') ? 'ATTAINED' :
                                            (($row['RM'] == 'NA') ? 'NOT ATTAINED' : '');
                                    ?>
                                </td>
                                <td contenteditable="true" class="editable" data-id="<?php echo $row['id']; ?>" data-column="AP">
                                    <?php echo htmlspecialchars($row['AP']); ?>
                                </td>
                            </tr>
                        <?php endfor; ?>
                        </tbody>
                    </table>

                </div>
                <button onclick="updateFLOS(<?php echo htmlspecialchars($schedule_id); ?>);" class="btn btn-warning">
                    Summarize
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFLO(id, column, value) {
    $.ajax({
        url: './operations/update_flo.php', // Path from JS file to PHP script
        type: 'POST',
        data: { id: id, column: column, value: value },
        success: function (response) {
            console.log("Server Response:", response);
            if (response.trim() !== "Success") {
                alert('Failed to save changes: ' + response);
            }
        },
        error: function (xhr, status, error) {
            alert('AJAX error: ' + error);
        }
    });
}

$(document).ready(function() {
    $(document).on("blur", ".editable", function() {
        let id = $(this).data("id");
        let column = $(this).data("column");
        let value = $(this).text().trim();
        updateFLO(id, column, value);
    });
});

function updateFLOS(schedule_id) {
    if (!confirm("Are you sure you want to update FLOS?")) return;

    $.ajax({
        url: './operations/update_flo.php',
        type: 'POST',
        data: { schedule_id: schedule_id },
        success: function(response) {
            alert(response);
            location.reload();
        },
        error: function(xhr, status, error) {
            alert('Error updating FLOS: ' + error);
        }
    });
}

$(document).ready(function () {
    // Trigger update on blur of editable cells
    $(document).on("blur", ".editable", function () {
        const id = $(this).data("id");
        const column = $(this).data("column");
        const value = $(this).text().trim();
        if (id && column) {
            updateFLO(id, column, value);
        }
    });

    // Highlight NOT ATTAINED remarks
    $("td:nth-child(7)").each(function () {
        if ($(this).text().trim() === "NOT ATTAINED") {
            $(this).css({
                "color": "red",
                "font-weight": "bold",
                "font-style": "italic"
            });
        }
    });
});
</script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
