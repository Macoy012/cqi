<?php
include '../database/dbconn.php';
session_start();

// Check if the user is logged in as a Facilitator
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Faculty', 'Programhead'])) {
    error_log("Access denied - Session usertype: " . $_SESSION['usertype']);
    header("Location: ../index.php?error=unauthorized");
    exit();
}

$user = $_SESSION['userID'];

// Get active semester and academic year
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

// Handle semester/year filtering
$selectedSemester = $_GET['semester'] ?? $activeSchedule['semester'];
$selectedYear = $_GET['academic_year'] ?? $activeSchedule['academic_year'];

$stmt = $conn->prepare("SELECT * FROM schedule WHERE userID = ? AND semester = ? AND academic_year = ?");
$stmt->bind_param('iss', $user, $selectedSemester, $selectedYear);
$stmt->execute();
$result = $stmt->get_result();

$scheds = [];
while ($row = $result->fetch_assoc()) {
    $scheds[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="cqi/css/bootstrap.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar.php'; ?>
        <div id="mainContent" class="container p-4">
            <div class="table-container">
                <h1>Schedules</h1>

                <!-- Semester and Academic Year Filter -->
                <form method="GET" class="d-flex mb-3">
                    <div class="me-2">
                        <label for="academic_year" class="form-label">Academic Year</label>
                        <select id="academic_year" name="academic_year" class="form-select">
                            <option value="2023-2024" <?= $selectedYear == '2023-2024' ? 'selected' : '' ?>>2023-2024</option>
                            <option value="2022-2023" <?= $selectedYear == '2022-2023' ? 'selected' : '' ?>>2022-2023</option>
                            <option value="<?php echo htmlspecialchars($activeSchedule['academic_year']); ?>" <?= $selectedYear == $activeSchedule['academic_year'] ? 'selected' : '' ?>>
                                <?php echo htmlspecialchars($activeSchedule['academic_year']); ?> (Active)
                            </option>
                        </select>
                    </div>
                    <div class="me-2">
                        <label for="semester" class="form-label">Semester</label>
                        <select id="semester" name="semester" class="form-select">
                            <option value="1" <?= $selectedSemester == '1' ? 'selected' : '' ?>>1st</option>
                            <option value="2" <?= $selectedSemester == '2' ? 'selected' : '' ?>>2nd</option>
                            <option value="summer" <?= $selectedSemester == 'summer' ? 'selected' : '' ?>>Summer</option>
                        </select>
                    </div>
                    <div class="align-self-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-wrapper">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Schedule ID</th>
                                <th scope="col">Course Code</th>
                                <th scope="col">Course Title</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Academic Year</th>
                                <th scope="col">Semester</th>
                                <th scope="col">Class</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTable">
                        <?php if (count($scheds) === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center">No schedules found for the selected semester and academic year.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($scheds as $row): ?>
                                <tr>
                                    <th scope="row"><a href="class.php?schedule_id=<?php echo $row['schedule_id']; ?>"><?php echo htmlspecialchars($row['schedule_id']); ?></a></th>
                                    <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['academic_year']); ?></td>
                                    <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                    <td><?php echo htmlspecialchars($row['course_code'] . " " . $row['year'] . "-" . $row['section']); ?></td>
                                    <td>
                                        <?php if ($row['academic_year'] === $selectedYear && $row['semester'] === $selectedSemester): ?>
                                            <button class="btn btn-sm btn-primary me-2">Update</button>
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        <?php else: ?>
                                            <span class="text-muted">Read-Only</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/index.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
