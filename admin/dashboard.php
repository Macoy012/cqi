<?php 
include '../database/dbconn.php';
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Admin") {
    header("Location: ../index.php");
    exit();
}

// File path for attainment score storage
$score_file = "../data/attainment_score.txt";

// Handle form submission (if admin updates the score)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['attainment_score'])) {
    $new_score = $_POST['attainment_score'];

    // Validate input (must be numeric)
    if (!is_numeric($new_score)) {
        echo "<script>alert('Invalid score! Please enter a number.');</script>";
    } else {
        file_put_contents($score_file, $new_score);
        echo "<script>alert('Attainment score updated successfully!');</script>";
    }
}

// Retrieve the current attainment score
$current_score = file_exists($score_file) ? file_get_contents($score_file) : "Not Set";

// File path for storing active semester and year
$activeFile = "../data/active_semester_year.txt";

// Function to read the active semester and year from the file
function getActiveSemesterAndYearFromFile($filePath) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if ($content) {
            return json_decode($content, true); // Decode JSON string into associative array
        }
    }
    return ["semester" => "1", "academic_year" => date('Y') . "-" . (date('Y') + 1)]; // Default values
}

// Function to write the active semester and year to the file
function setActiveSemesterAndYearToFile($filePath, $semester, $academicYear) {
    $data = json_encode(["semester" => $semester, "academic_year" => $academicYear]);
    file_put_contents($filePath, $data);
}

// Retrieve current active semester and year
$activeSchedule = getActiveSemesterAndYearFromFile($activeFile);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['semester'], $_POST['academic_year'])) {
    $activeSemester = $_POST['semester'];
    $activeYear = $_POST['academic_year'];

    // Set new active semester and year in the file
    setActiveSemesterAndYearToFile($activeFile, $activeSemester, $activeYear);

    echo "<script>alert('Active semester and school year updated successfully!');</script>";
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
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar.php'; ?>

        <div id="mainContent" class="container p-4">
            <h2>Admin Dashboard</h2>
            <!-- Dashboard Statistics Section -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Accounts</h5>
                            <p class="card-text">
                                <?php 
                                    $totalAccountsQuery = "SELECT COUNT(*) as total FROM account";
                                    $result = mysqli_query($conn, $totalAccountsQuery);
                                    $totalAccounts = mysqli_fetch_assoc($result)['total'];
                                    echo $totalAccounts;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Number of Programs</h5>
                            <p class="card-text">
                                <?php 
                                    $totalFacultyQuery = "SELECT COUNT(*) as total FROM course";
                                    $result = mysqli_query($conn, $totalFacultyQuery);
                                    $totalFaculty = mysqli_fetch_assoc($result)['total'];
                                    echo $totalFaculty;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Program Heads</h5>
                            <p class="card-text">
                                <?php 
                                    $totalPersonHeadQuery = "SELECT COUNT(*) as total FROM account WHERE usertype = 'Programhead'";
                                    $result = mysqli_query($conn, $totalPersonHeadQuery);
                                    $totalPersonHeads = mysqli_fetch_assoc($result)['total'];
                                    echo $totalPersonHeads;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Department Chair</h5>
                            <p class="card-text">
                                <?php 
                                    $totalChairpersonQuery = "SELECT COUNT(*) as total FROM account WHERE usertype = 'Chairperson'";
                                    $resultChair = mysqli_query($conn, $totalChairpersonQuery);
                                    $totalChairperson = mysqli_fetch_assoc($resultChair)['total'];
                                    echo $totalChairperson;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Active Year and Semester</h5>
                            <p class="card-text">
                                <?php 
                                    $academicYear = $activeSchedule['academic_year'];
                                    $semester = $activeSchedule['semester'];

                                    // Display the active semester in the appropriate format
                                    $semesterDisplay = $semester == "1" ? "1st Sem" : ($semester == "2" ? "2nd Sem" : "Summer");
                                    echo $academicYear . " - " . $semesterDisplay;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

            </div>
            <!-- End of Dashboard Statistics Section -->

            <!-- Active Semester and Year Section -->
            <div class="col-md-3">
                <div class="card mb-3" style="width: 300px;
                    border: 1px solid rgb(49, 167, 69);
                    border-radius: 10px;">
                    <div class="card-body">
                        <h5 class="card-title">Set Active Semester and School Year</h5>
                        <form id="setActiveForm" method="POST">
                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester:</label>
                                <select name="semester" id="semester" class="form-select" required>
                                    <option value="1" <?php if ($activeSchedule['semester'] == '1') echo 'selected'; ?>>1st</option>
                                    <option value="2" <?php if ($activeSchedule['semester'] == '2') echo 'selected'; ?>>2nd</option>
                                    <option value="Summer" <?php if ($activeSchedule['semester'] == 'Summer') echo 'selected'; ?>>Summer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="academic_year" class="form-label">School Year:</label>
                                <select name="academic_year" id="academic_year" class="form-select" required>
                                    <?php
                                        $currentYear = date('Y');
                                        $previousYear = $currentYear - 1;
                                        $endYear = $currentYear + 10;
                                        for ($year = $previousYear; $year < $endYear; $year++) {
                                            $nextYear = $year + 1;
                                            echo "<option value='$year-$nextYear' " . ($activeSchedule['academic_year'] == "$year-$nextYear" ? 'selected' : '') . ">$year - $nextYear</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Set Active</button>
                        </form>
                    </div>
                </div>
                <!-- End of Active Semester and Year Section -->
                
                <!-- Attainment Score Section -->
                <div class="card mt-4" style="width: 200px;
                    border: 1px solid rgb(49, 167, 69);
                    border-radius: 10px;">
                    <div class="card-body">
                        <h5 class="card-title">Target Attainment Level</h5>
                        <form method="POST">
                            <label for="attainment_score">Set Attainment Level:</label>
                            <input type="text" name="attainment_score" class="form-control mb-2" 
                                   value="<?php echo $current_score; ?>" required>
                            <button type="submit" class="btn btn-success">Save</button>
                        </form>
                    </div>
                </div>
            <!-- End of Attainment Score Section -->
            </div>            
        </div>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>