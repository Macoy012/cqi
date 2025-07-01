<?php
include '../database/dbconn.php';
session_start();

// Check if the user is logged in as a Facilitator
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Faculty', 'Personhead'])) {
    error_log("Access denied - Session usertype: " . ($_SESSION['usertype'] ?? 'not set'));
    header("Location: ../index.php?error=unauthorized");
    exit();
}

// Get or retain schedule_id
if (isset($_GET['schedule_id'])) {
    $schedule_id = $_GET['schedule_id'];
    $_SESSION['schedule_id'] = $schedule_id;
} elseif (isset($_SESSION['schedule_id'])) {
    $schedule_id = $_SESSION['schedule_id'];
} else {
    $schedule_id = '';
}

// Compute CO data
include './operations/oa_sumfunc.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CQI Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/css/midterms.css" />
    <link rel="stylesheet" href="../css/bootstrap.css">
</head>

<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar_class.php'; ?>

        <div id="mainContent" class="container p-4">
            <div class="table-container">
                <?php if (!empty($co_data)): ?>
                    <h2>Overall Course Outcome Summary - Schedule ID: <?php echo htmlspecialchars($schedule_id); ?></h2>
                <?php else: ?>
                    <h2>No learning outcomes found for the provided schedule ID.</h2>
                <?php endif; ?>
                <div class="table-wrapper">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">CO NUMBER</th>
                                <th scope="col">ATTAINMENT</th>
                                <th scope="col">TARGET</th>
                                <th scope="col">REMARKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($co_data as $co => $data): ?>
                                <tr>
                                    <th scope="row"><?= htmlspecialchars($co); ?></td>
                                    <td><?= number_format($data['percent_attainment'], 0); ?>%</td>
                                    <td><?= number_format($data['target'], 0); ?>%</td>
                                    <td><?= htmlspecialchars($data['remarks']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
