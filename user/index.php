<?php 
include '../database/dbconn.php';
session_start();

// Check if the user is logged in as a Facilitator
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Faculty', 'Personhead'])) {
    error_log("Access denied - Session usertype: " . $_SESSION['usertype']);
    header("Location: ../index.php?error=unauthorized");
    exit();
}


$user = $_SESSION['userID'];
$sql = "SELECT * FROM schedule WHERE userID = '$user'";
$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

$scheds = [];
$result = $conn->query("SELECT * FROM schedule WHERE userID = '$user'");
if ($result){
    while ($row = $result->fetch_assoc()) {
        $scheds[] = $row;
    }
} else {
    echo "Error Fetching Accounts " . $conn->error;
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
    <link rel="stylesheet" href="../css/bootstrap.css">


</head>
<body>
    <?php include './includes/navbar.php';?>
<div class="d-flex">
    <?php include './includes/sidebar.php';?>

    <div id="mainContent" class="container p-4">
        <div class="table-container">
            <h1>Schedules</h1>
                <input type="text" id="searchBar" class="form-control search-bar" placeholder="Search..." onkeyup="filterTable()">
                <div class="table-wrapper">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Schedule Code</th>
                                <th scope="col">Program Code</th>
                                <th scope="col">Program</th>
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
                                <td colspan="8" class="text-center">No schedule/s found.</td>
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
                                        <button class="btn btn-sm btn-primary me-2" >Update</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
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