<?php
include '../database/dbconn.php';
session_start();

// Check if user is logged in and is Chairperson
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== "Chairperson") {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

// Get the subject_code from the URL
if (isset($_GET['subject_code'])) {
    $subject_code = $_GET['subject_code'];

    // Fetch schedule IDs for the provided subject code
    $sql = "SELECT schedule_id FROM schedule WHERE subject_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $subject_code);
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect schedule IDs
    $schedule_ids = [];
    while ($row = $result->fetch_assoc()) {
        $schedule_ids[] = $row['schedule_id'];
    }
    $stmt->close();

    if (!empty($schedule_ids)) {
        // Fetch FLOs and attainment from the flo table for the collected schedule IDs
        $placeholders = implode(',', array_fill(0, count($schedule_ids), '?'));
        $sql = "SELECT schedule_id, ilo, ATT FROM flo WHERE schedule_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        // Dynamically bind parameters
        $types = str_repeat('i', count($schedule_ids));
        $stmt->bind_param($types, ...$schedule_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        // Collect FLOs and attainment
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();

        // Group by FLO and calculate average ATT
        $flo_attainment = [];
        foreach ($data as $row) {
            $flo = $row['ilo'];
            $att = floatval($row['ATT']); // ensure numeric

            if (!isset($flo_attainment[$flo])) {
                $flo_attainment[$flo] = ['sum' => 0, 'count' => 0];
            }

            $flo_attainment[$flo]['sum'] += $att;
            $flo_attainment[$flo]['count']++;
        }

        // Calculate averages
        $averages = [];
        foreach ($flo_attainment as $flo => $values) {
            $averages[$flo] = $values['sum'] / $values['count'];
        }

        // Read target attainment from the file using relative path
        $target_file = '../data/attainment_score.txt';
        if (file_exists($target_file)) {
            $target_attainment = floatval(trim(file_get_contents($target_file)));
        } else {
            $target_attainment = null;
            $message = "Target attainment file not found.";
        }
    } else {
        $message = "No schedules found for the subject code: " . htmlspecialchars($subject_code, ENT_QUOTES, 'UTF-8');
    }
} else {
    $message = "No subject code provided in the URL.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FOIS - Subject Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <link rel="stylesheet" href="assets/css/mois.css" />
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar_overall.php'; ?>
        <div id="mainContent" class="container p-4">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>
                        Program Code: <?php echo htmlspecialchars($subject_code ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                    <?php if (isset($target_attainment)): ?>
                        <span class="text-muted">Target Attainment: <?php echo number_format($target_attainment, 2); ?></span>
                    <?php endif; ?>
                </div>
                    
                <?php if (isset($message)): ?>
                    <div class="alert alert-warning">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php else: ?>
                    <h5>Final Overall Intended Learning Outcome Summary</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ILO</th>
                                <th>Attainment</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($averages)): ?>
                                <?php foreach ($averages as $flo => $avg_att): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($flo, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo number_format($avg_att, 2); ?></td>
                                        <td>
                                            <?php
                                                if ($target_attainment !== null) {
                                                    if ($avg_att >= $target_attainment) {
                                                        echo "Attained";
                                                    } else {
                                                        echo '<span style="color: red; font-style: italic;">Not Attained</span>';
                                                    }
                                                } else {
                                                    echo "N/A";
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No records found on given schedules.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
