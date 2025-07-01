<?php
include '../database/dbconn.php';
session_start();

// Restrict access to Personhead only
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== "Programhead") {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

if (!isset($_SESSION['schedule_id']) && !isset($_GET['schedule_id'])) {
    die("Schedule ID is missing.");
}

$schedule_id = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : $_SESSION['schedule_id'];
$_SESSION['schedule_id'] = $schedule_id;

// Handle deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $del = $conn->prepare("DELETE FROM so_pi_mapping WHERE id = ? AND schedule_id = ?");
    $del->bind_param("ii", $id, $schedule_id);
    $del->execute();
    header("Location: so_pi_mapping.php?schedule_id=$schedule_id&deleted=1");
    exit();
}

// Handle new insertions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_mapping'])) {
    foreach ($_POST['pi'] as $co_id => $pi_value) {
        $so_value = $_POST['so'][$co_id];

        // Validate SO (globally, no schedule_id)
        $check_so = $conn->prepare("SELECT 1 FROM so_mapping WHERE so = ?");
        $check_so->bind_param("s", $so_value);
        $check_so->execute();
        $so_valid = $check_so->get_result();

        // Prevent duplicate co_id + so for this schedule
        $check_dup = $conn->prepare("SELECT 1 FROM so_pi_mapping WHERE schedule_id = ? AND co_id = ? AND so = ?");
        $check_dup->bind_param("iss", $schedule_id, $co_id, $so_value);
        $check_dup->execute();
        $is_duplicate = $check_dup->get_result();

        if ($so_valid->num_rows > 0 && $is_duplicate->num_rows === 0) {
            $att_stmt = $conn->prepare("SELECT ATT FROM overallco WHERE schedule_id = ? AND co_id = ?");
            $att_stmt->bind_param("is", $schedule_id, $co_id);
            $att_stmt->execute();
            $att_result = $att_stmt->get_result();
            $att_row = $att_result->fetch_assoc();
            $att = $att_row['ATT'];

            $insert = $conn->prepare("
                        INSERT INTO so_pi_mapping (schedule_id, so, pi, co_id, ATT)
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE pi = VALUES(pi), ATT = VALUES(ATT)
                    ");
                    $insert->bind_param("isiss", $schedule_id, $so_value, $pi_value, $co_id, $att);
                    $insert->execute();
                    }
    }
    header("Location: so_pi_mapping.php?schedule_id=$schedule_id&success=1");
    exit();
}

// Fetch COs from overallco
$co_stmt = $conn->prepare("SELECT co_id, ATT FROM overallco WHERE schedule_id = ?");
$co_stmt->bind_param("i", $schedule_id);
$co_stmt->execute();
$co_result = $co_stmt->get_result();

// Fetch global SOs
$so_stmt = $conn->prepare("SELECT so FROM so_mapping ORDER BY so ASC");
$so_stmt->execute();
$so_result = $so_stmt->get_result();

$sos = [];
while ($row = $so_result->fetch_assoc()) {
    $sos[] = $row['so'];
}

// Fetch existing mappings
$stmt = $conn->prepare("SELECT * FROM so_pi_mapping WHERE schedule_id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$existing = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SO-PI Mapping</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/index.css">
    <style>
        td[contenteditable="true"] {
            background-color: #fff8dc;
        }
    </style>
</head>
<body>
<?php include './includes/navbar.php'; ?>
<div class="d-flex">
    <?php include './includes/sidebar_class.php'; ?>

    <div id="mainContent" class="container mt-4">
        <h3>SO-PI Mapping for Schedule ID: <?= htmlspecialchars($schedule_id) ?></h3>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Mappings saved successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-danger">Mapping deleted successfully.</div>
        <?php endif; ?>

        <h5 class="mt-4">Existing Mappings</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>CO ID</th>
                    <th>PI</th>
                    <th>SO</th>
                    <th>ATT</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $existing->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['co_id']) ?></td> <!-- NOT editable -->
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-field="pi"><?= htmlspecialchars($row['pi']) ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-field="so"><?= htmlspecialchars($row['so']) ?></td>
                        <td contenteditable="true" data-id="<?= $row['id'] ?>" data-field="ATT"><?= htmlspecialchars($row['ATT']) ?></td>
                        <td>
                            <a href="?schedule_id=<?= $schedule_id ?>&delete=<?= $row['id'] ?>"
                            onclick="return confirm('Are you sure you want to delete this mapping?');"
                            class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h5 class="mt-4">Add New Mapping</h5>
        <form method="POST">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>CO ID</th>
                        <th>ATT</th>
                        <th>PI (Integer)</th>
                        <th>SO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($co = $co_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($co['co_id']) ?></td>
                            <td><?= htmlspecialchars($co['ATT']) ?></td>
                            <td>
                                <input type="number" name="pi[<?= $co['co_id'] ?>]" class="form-control" required>
                            </td>
                            <td>
                                <select name="so[<?= $co['co_id'] ?>]" class="form-select" required>
                                    <option value="" disabled selected>Choose SO</option>
                                    <?php foreach ($sos as $so): ?>
                                        <option value="<?= htmlspecialchars($so) ?>"><?= htmlspecialchars($so) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" name="submit_mapping" class="btn btn-primary">Save Mappings</button>
        </form>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.querySelector("table");

    table.addEventListener("blur", function (e) {
        if (e.target.matches("td[contenteditable='true']")) {
            const td = e.target;
            const id = td.dataset.id;
            const field = td.dataset.field;
            const value = td.innerText.trim();

            // Send AJAX request
            fetch('./operations/update_so_pi_mapping.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    id: id,
                    field: field,
                    value: value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert("Update failed: " + (data.error || "Unknown error"));
                }
            })
            .catch(err => {
                alert("Error updating field: " + err.message);
            });
        }
    }, true); // useCapture to catch blur before element loses focus
});
</script>

</body>
</html>
