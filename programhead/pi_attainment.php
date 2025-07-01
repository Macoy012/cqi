<?php
include '../database/dbconn.php';
session_start();

if ($_SESSION['usertype'] !== 'Programhead') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : ($_SESSION['schedule_id'] ?? null);
if (!$schedule_id) {
    die("Schedule ID is required.");
}
$_SESSION['schedule_id'] = $schedule_id;

$attainment_score_path = '../data/attainment_score.txt';
if (!file_exists($attainment_score_path)) {
    die("TAR file not found.");
}
$TAR = intval(trim(file_get_contents($attainment_score_path)));

$check_stmt = $conn->prepare("SELECT COUNT(*) AS count FROM so_pi_mapping WHERE schedule_id = ?");
$check_stmt->bind_param("i", $schedule_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result()->fetch_assoc();

if ((int)$check_result['count'] === 0) {
    $del_stmt = $conn->prepare("DELETE FROM so_pi_targets WHERE schedule_id = ?");
    $del_stmt->bind_param("i", $schedule_id);
    $del_stmt->execute();
} else {
    $cleanup_stmt = $conn->prepare("
        DELETE FROM so_pi_targets 
        WHERE (SO, PI) IN (
            SELECT t.SO, t.PI
            FROM so_pi_targets t
            LEFT JOIN so_pi_mapping m
            ON t.schedule_id = m.schedule_id AND t.SO = m.so AND t.PI = m.pi
            WHERE m.pi IS NULL OR m.pi = ''
        )
        AND schedule_id = ?
    ");
    $cleanup_stmt->bind_param("i", $schedule_id);
    $cleanup_stmt->execute();

    $avg_stmt = $conn->prepare("
        SELECT so, pi, AVG(ATT) AS avg_att
        FROM so_pi_mapping
        WHERE schedule_id = ?
        GROUP BY so, pi
    ");
    $avg_stmt->bind_param("i", $schedule_id);
    $avg_stmt->execute();
    $result = $avg_stmt->get_result();

    $insert_stmt = $conn->prepare("
        INSERT INTO so_pi_targets (schedule_id, SO, PI, ATT, TAR, RM)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            ATT = VALUES(ATT),
            TAR = VALUES(TAR),
            RM = VALUES(RM),
            pi_perc = IFNULL(so_pi_targets.pi_perc, '')
    ");

    while ($row = $result->fetch_assoc()) {
        $so = $row['so'];
        $pi = intval($row['pi']);
        $avg_att = intval(round($row['avg_att']));
        $rm = $avg_att >= $TAR ? 'A' : 'NA';
        $insert_stmt->bind_param("isiiis", $schedule_id, $so, $pi, $avg_att, $TAR, $rm);
        $insert_stmt->execute();
    }

    // SO-level Direct Attainment
    $so_data = $conn->prepare("
        SELECT SO, ATT, pi_perc 
        FROM so_pi_targets 
        WHERE schedule_id = ?
    ");
    $so_data->bind_param("i", $schedule_id);
    $so_data->execute();
    $result = $so_data->get_result();

    $so_attainment = [];

    while ($row = $result->fetch_assoc()) {
        $so = $row['SO'];
        $att = intval($row['ATT']);
        $pi_perc = intval($row['pi_perc']);
        $contribution = intval(($pi_perc * $att) / 100);

        if (!isset($so_attainment[$so])) {
            $so_attainment[$so] = 0;
        }
        $so_attainment[$so] += $contribution;
    }

    $insert_so_stmt = $conn->prepare("
        INSERT INTO so_targets (schedule_id, so, ATT, TAR, RM)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE ATT = VALUES(ATT), TAR = VALUES(TAR), RM = VALUES(RM)
    ");

    foreach ($so_attainment as $so => $att_sum) {
        $rm = $att_sum >= $TAR ? 'A' : 'NA';
        $insert_so_stmt->bind_param("isiss", $schedule_id, $so, $att_sum, $TAR, $rm);
        $insert_so_stmt->execute();
    }
}

// Fetch PI-level data
$data_query = $conn->prepare("
    SELECT id, SO, PI, ATT, TAR, RM, pi_perc
    FROM so_pi_targets
    WHERE schedule_id = ?
    ORDER BY SO, PI
");
$data_query->bind_param("i", $schedule_id);
$data_query->execute();
$data_result = $data_query->get_result();

// Fetch SO-level data
$so_targets_query = $conn->prepare("
    SELECT so, ATT, TAR, RM
    FROM so_targets
    WHERE schedule_id = ?
    ORDER BY so
");
$so_targets_query->bind_param("i", $schedule_id);
$so_targets_query->execute();
$so_targets_result = $so_targets_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SO-PI Target Attainment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/index.css">
    <style>
        td[contenteditable="true"] {
            background-color: #fff6e5;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include './includes/navbar.php'; ?>
<div class="d-flex">
    <?php include './includes/sidebar_class.php'; ?>
    <div class="container p-4" id="mainContent">
            <h2>SO-PI Target Attainment Table for Schedule ID: <?= htmlspecialchars($schedule_id) ?></h2>
    
            <div class="alert alert-success mt-3">
                Table updated using average ATT values from mappings, editable PI %, and SO-level direct attainment is stored and displayed below.
            </div>
    
            <!-- PI Table -->
            <table class="table table-bordered table-striped mt-4">
                <thead>
                    <tr>
                        <th>SO</th>
                        <th>PI</th>
                        <th>PI %</th>
                        <th>Average ATT</th>
                        <th>TAR</th>
                        <th>RM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $data_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['SO']) ?></td>
                            <td><?= htmlspecialchars($row['PI']) ?></td>
                            <td contenteditable="true" data-id="<?= $row['id'] ?>" data-field="pi_perc"><?= htmlspecialchars($row['pi_perc']) ?></td>
                            <td><?= $row['ATT'] ?></td>
                            <td><?= $row['TAR'] ?></td>
                            <td class="<?= $row['RM'] === 'A' ? 'table-success' : 'table-danger' ?>">
                                <?= $row['RM'] ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
    
            <!-- SO Direct Attainment Table -->
            <h4 class="mt-5">SO-Level Direct Attainment</h4>
            <table class="table table-bordered table-hover mt-3">
                <thead>
                    <tr>
                        <th>SO</th>
                        <th>Direct Attainment</th>
                        <th>TAR</th>
                        <th>RM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $so_targets_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['so']) ?></td>
                            <td><?= $row['ATT'] ?></td>
                            <td><?= $row['TAR'] ?></td>
                            <td class="<?= $row['RM'] === 'A' ? 'table-success' : 'table-danger' ?>">
                                <?= $row['RM'] ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
</div>

<!-- JavaScript for inline PI % updates -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("table").addEventListener("blur", function (e) {
        if (e.target.matches("td[contenteditable='true']")) {
            const td = e.target;
            const id = td.dataset.id;
            const field = td.dataset.field;
            const value = td.innerText.trim();

            fetch('./operations/update_pi_perc.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id, field, value })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert("Update failed: " + (data.error || "Unknown error"));
                }
            })
            .catch(err => {
                alert("Error: " + err.message);
            });
        }
    }, true);
});
</script>
</body>
</html>
