<?php
include '../database/dbconn.php';
session_start();

// Check if user is logged in and is Chairperson
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== "Chairperson") {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

if (!isset($_SESSION['userID'])) {
    die("Error: User ID is not set in the session.");
}

$userID = $_SESSION['userID'];

// Get the CID and department name for the logged-in user's department
$sql = "SELECT CID, department_name FROM department WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->bind_result($cid, $department_name);
if (!$stmt->fetch()) {
    die("No department found for this user.");
}
$stmt->close();

// Get active semester and year from a file
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
        case "1":
            return "1st";
        case "2":
            return "2nd";
        case "summer":
            return "Summer";
        default:
            return "Unknown Semester";
    }
}

// Fetch subject codes, descriptions, and consolidate schedule IDs where CID matches
$sql = "
    SELECT 
        subject_code, 
        description, 
        GROUP_CONCAT(schedule_id SEPARATOR ', ') AS schedule_ids
    FROM 
        schedule 
    WHERE 
        CID = ? 
    GROUP BY 
        subject_code, 
        description
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $cid);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}
$stmt->close();
$conn->close();
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
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar.php'; ?>

        <div id="mainContent" class="container p-4">
            <h5>Department: <h3><?php echo htmlspecialchars($department_name, ENT_QUOTES, 'UTF-8'); ?></h3></h5>
            <h5>Academic Year: <?php echo htmlspecialchars($activeSchedule['academic_year'], ENT_QUOTES, 'UTF-8'); ?></h5>
            <h5>Semester: <?php echo htmlspecialchars(formatSemester($activeSchedule['semester']), ENT_QUOTES, 'UTF-8'); ?></h5>
            <div class="container p-4">
                <div class="table-container">
                    <h2>Programs</h2>
                    <input type="text" id="searchBar" class="form-control search-bar" placeholder="Search..." onkeyup="filterTable()">
                    <div class="table-wrapper">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Subject Code</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Schedule IDs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td>
                                            <a href="mois.php?subject_code=<?php echo urlencode($schedule['subject_code']); ?>">
                                                <?php echo htmlspecialchars($schedule['subject_code'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($schedule['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($schedule['schedule_ids'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function filterTable() {
            const search = document.getElementById('searchBar').value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(search));
                row.style.display = match ? '' : 'none';
            });
        }
    </script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
