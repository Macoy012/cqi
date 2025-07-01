<?php 
include '../database/dbconn.php';
session_start();

// Check if the user is logged in as a Chairperson
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== "Programhead") {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

// Debug userID session
if (!isset($_SESSION['userID'])) {
    die("Error: User ID is not set in the session.");
}

$userID = $_SESSION['userID'];

// Initialize department_name
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

$activeFile = "../data/active_semester_year.txt";
function getActiveSemesterAndYearFromFile($filePath) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if ($content) {
            return json_decode($content, true); // Decode JSON string into associative array
        }
    }
    return ["semester" => "1", "academic_year" => date('Y') . "-" . (date('Y') + 1)]; // Default values
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

//Fetch schedule based on cid
$sql = "SELECT schedule.* 
        FROM schedule
        INNER JOIN department ON schedule.CID = department.CID
        WHERE department.userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID); // Bind the logged-in user's ID
$stmt->execute();
$schedules = [];
if ($result = $stmt->get_result()) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row; // Fetch all schedules matching the user's department
    }
} else {
    die("Error fetching schedules: " . $conn->error);
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" ></script>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">


</head>
<body>
    <?php include './includes/navbar.php';?>
<div class="d-flex">
    <?php include './includes/sidebar.php';?>

    <div id="mainContent" class="container p-4">
        <h5>Department: <h3><?php echo htmlspecialchars($department_name, ENT_QUOTES, 'UTF-8'); ?></h5></h3>
        <h5>Academic Year: <?php echo htmlspecialchars($activeSchedule['academic_year'], ENT_QUOTES, 'UTF-8'); ?></h5>
        <h5>Semester: <?php echo htmlspecialchars(formatSemester($activeSchedule['semester']), ENT_QUOTES, 'UTF-8'); ?></h5>
        <div class="container p-4">
            <div class="table-container">
                <h2>Class</h2>
                <input type="text" id="searchBar" class="form-control search-bar" placeholder="Search..." onkeyup="filterTable()">
                <div class="table-wrapper">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Schedule Code</th>
                                <th scope="col">Program Code</th>
                                <th scope="col">Program Title</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Year & Section</th>
                            </tr>
                        </thead>

                        <tbody>
                          <?php foreach ($schedules as $row): ?>
                            <tr>
                              <th scope="row"><a href="so_pi_mapping.php?schedule_id=<?php echo $row['schedule_id']; ?>"><?php echo htmlspecialchars($row['schedule_id']); ?></a></th>
                              <td><?php echo htmlspecialchars($row['subject_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                              <td>
                                <?php
                                  // Assuming your columns are named 'year_level' and 'section'
                                  $year  = $row['year'];
                                  $sect  = $row['section'];
                                  echo htmlspecialchars("{$year} – {$sect}", ENT_QUOTES, 'UTF-8');
                                ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
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