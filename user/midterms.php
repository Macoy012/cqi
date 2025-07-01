<?php 
include '../database/dbconn.php';
session_start();

// Check if the user is logged in as a Facilitator
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Faculty', 'Programhead'])) {
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

// Get the schedule_id from URL if not already set in session
if (isset($_GET['total_q'])) {
    $total_q = intval($_GET['total_q']);
    $_SESSION['total_q'] = $total_q;
} elseif (isset($_SESSION['total_q'])) {
    $total_q = $_SESSION['total_q'];
} else {
    $total_q = 10;
}

// Fetch schedule data from the database using schedule_id
$sql = "SELECT * FROM schedule WHERE schedule_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize schedule variable
$schedule = null;
if ($result->num_rows > 0) {
    $schedule = $result->fetch_assoc(); // Store the fetched schedule data
} else {
    $schedule = null;
}

// Fetch student records
$sql = "SELECT * FROM mclassrec WHERE schedule_id = ? AND exam_type = 'midterm'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize total counters for each question
$totals = array_fill(1, $total_q, 0);
$grand_total_attained = 0;

// Read and decode the JSON file for active semester/year
$active_data = file_get_contents('../data/active_semester_year.txt');
$active_data = json_decode($active_data, true); // Decode JSON into an associative array

if ($active_data && isset($active_data['academic_year'], $active_data['semester'])) {
    $active_year = $active_data['academic_year'];
    $active_semester = $active_data['semester'];
} else {
    error_log("Invalid or missing active_semester_year.txt file.");
    die("Error: Could not retrieve active semester data.");
}

// Check if the selected class is active
if ($schedule) {
    $is_active = (
        $schedule['academic_year'] === $active_year &&
        $schedule['semester'] == $active_semester
    );
} else {
    $is_active = false;
}

// Read the contents of the attainment_score.txt file
$attainment_score = file_get_contents('../data/attainment_score.txt');

// Determine the status message
$status_message = $is_active ? "Status: Active Semester" : "Status: Previous Semester";
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
    <link rel="stylesheet" href="assets/css/midterms.css">
    <link rel="stylesheet" href="cqi/css/bootstrap.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar_class.php'; ?>

        <div id="mainContent" class="container p-4">
            <?php if ($schedule): ?>
                <h2>Midterm Class Record - <?php echo htmlspecialchars($schedule['schedule_id']); ?></h2>
                <h5><?php echo htmlspecialchars($status_message); ?></h5>
                <h5>Target Attainment Level: <?php echo htmlspecialchars($attainment_score); ?>%</h5>

                <form action="" method="GET">
                    <label for="total_q">NUMBER OF ITEMS:</label>
                    <input type="number" id="total_q" name="total_q" min="1" max="300" value="<?php echo $total_q; ?>" <?php echo !$is_active ? 'disabled' : ''; ?>>
                    <input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>" />
                    <button class="btn btn-warning" type="submit" <?php echo !$is_active ? 'disabled' : ''; ?>>UPDATE</button>
                </form>

                <form action="operations/midterm_records.php" method="POST">
                    <div class="table-container">
                        <div class="table-wrapper">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col" class="sticky-left">Student Number</th>
                                        <th scope="col" class="sticky-left-2">Student Name</th>
                                        <?php for ($i = 1; $i <= $total_q; $i++) { echo "<th>Item #$i</th>"; } ?>
                                        <th scope="col" class="sticky-right">Total:</th>
                                    </tr>
                                </thead>
                                <tbody id="midtermrecords">
                                <?php while ($row = $result->fetch_assoc()) { $qsum = 0; ?>
                                    <tr>
                                        <th scope="row" class="sticky-left"><?php echo $row['studID']; ?></th>
                                        <td class="sticky-left-2"><?php echo $row['fullname']; ?></td>
                                        <input type="hidden" name="students[]" value="<?php echo $row['mid']; ?>">

                                        <?php for ($i = 1; $i <= $total_q; $i++) {
                                            $checked = ($row["Q$i"] == 1) ? "checked" : "";
                                            if ($row["Q$i"] == 1) { 
                                                $qsum++; 
                                                $totals[$i]++;
                                            }
                                            echo "<td><input type='checkbox' name='q[$row[mid]][]' value='$i' $checked ".(!$is_active ? "disabled" : "")."></td>";
                                        } ?>
                                        <td class="sticky-right"><?php echo $qsum; ?></td>
                                    </tr>
                                <?php $grand_total_attained += $qsum; } ?>
                                    <tr style="text-align: center; vertical-align: middle;">
                                        <td colspan="2" class="sticky-left">TOTAL:</td>
                                        <?php for ($i = 1; $i <= $total_q; $i++) { echo "<th>{$totals[$i]}</th>"; } ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <h2>No schedule found for the provided schedule ID.</h2>
            <?php endif; ?>
        </div>
    </div>
    <script src="./assets/js/midterms.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
