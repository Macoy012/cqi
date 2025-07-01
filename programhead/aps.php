<?php 
include '../database/dbconn.php';
session_start();

// Check if the user is logged in as a Chairperson
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== "Programhead") {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

// Check if userID is set
if (!isset($_SESSION['userID'])) {
    die("Error: User ID is not set in the session.");
}

$userID = $_SESSION['userID'];

// ✅ Get schedule_id from URL
if (!isset($_GET['schedule_id'])) {
    die("Error: schedule_id not provided.");
}
$schedule_id = intval($_GET['schedule_id']);

// Get department name
$department_name = "";
$sql = "SELECT department_name FROM department WHERE userID = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->bind_result($department_name);
    if (!$stmt->fetch()) {
        $department_name = "No department assigned.";
    }
    $stmt->close();
} else {
    die("SQL Error: " . $conn->error);
}

// Load active semester and year
$activeFile = "../data/active_semester_year.txt";
function getActiveSemesterAndYearFromFile($filePath) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if ($content) {
            return json_decode($content, true);
        }
    }
    return ["semester" => "1", "academic_year" => date('Y') . "-" . (date('Y') + 1)];
}
$activeSchedule = getActiveSemesterAndYearFromFile($activeFile);

function formatSemester($semester) {
    switch ($semester) {
        case "1": return "1st";
        case "2": return "2nd";
        case "summer": return "Summer";
        default: return "Unknown Semester";
    }
}

// FETCH DATA FROM MAPS TABLE (Midterms)
$maps_query = "SELECT ID, schedule_id, exam_type, ILO, APS, p_timeline, comments FROM maps WHERE schedule_id = ?";
$stmt = $conn->prepare($maps_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$maps_result = $stmt->get_result();
$stmt->close();

// FETCH DATA FROM FAPS TABLE (Finals)
$faps_query = "SELECT ID, schedule_id, exam_type, ILO, APS, p_timeline, comments FROM faps WHERE schedule_id = ?";
$stmt = $conn->prepare($faps_query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$faps_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Plan Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/aps.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
<div class="d-flex">
    <?php include './includes/sidebar.php'; ?>

    <div id="mainContent" class="container p-4">
        <h4>Department: <?= htmlspecialchars($department_name) ?></h4>
        <h5>Academic Year: <?= htmlspecialchars($activeSchedule['academic_year']) ?></h5>
        <h5>Semester: <?= htmlspecialchars(formatSemester($activeSchedule['semester'])) ?></h5>

        <!-- MIDTERM -->
        <div class="container p-4">
            <div class="table-container">
                <h3>Midterms Action Plan Summary</h3>
                <div class="table-wrapper">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ILO</th>
                                <th>ACTION PLAN SUMMARY</th>
                                <th>PROPOSED TIMELINE</th>
                                <th>COMMENT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $maps_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['ILO']) ?></td>
                                <td><?= htmlspecialchars($row['APS']) ?></td>
                                <td><?= htmlspecialchars($row['p_timeline']) ?></td>
                                <td contenteditable="true"><?= htmlspecialchars($row['comments']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FINALS -->
        <div class="container p-4">
            <div class="table-container">
                <h3>Finals Action Plan Summary</h3>
                <div class="table-wrapper">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ILO</th>
                                <th>ACTION PLAN SUMMARY</th>
                                <th>PROPOSED TIMELINE</th>
                                <th>COMMENT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $faps_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['ILO']) ?></td>
                                <td><?= htmlspecialchars($row['APS']) ?></td>
                                <td><?= htmlspecialchars($row['p_timeline']) ?></td>
                                <td contenteditable="true"><?= htmlspecialchars($row['comments']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
<script>
document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
    cell.addEventListener('blur', function () {
        const row = this.parentElement;
        const isFinals = row.closest('.container').querySelector('h3').innerText.includes("Finals");
        const ilo = row.cells[0].innerText.trim();
        const comment = this.innerText.trim();
        const scheduleId = <?= json_encode($schedule_id) ?>;

        const url = isFinals ? 'operations/update_faps_comment.php' : 'operations/update_maps_comment.php';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ scheduleId, ilo, comment })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('❌ Failed to save comment.');
            }
        })
        .catch(() => alert('❌ AJAX error.'));
    });
});

</script>
