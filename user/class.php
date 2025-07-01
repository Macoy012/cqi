<?php 
include '../database/dbconn.php';
session_start();

// Check if the user is logged in as a Facilitator
if (!isset($_SESSION['usertype']) || !in_array($_SESSION['usertype'], ['Faculty', 'Programhead'])) {
    error_log("Access denied - Session usertype: " . $_SESSION['usertype']);
    header("Location: ../index.php?error=unauthorized");
    exit();
}

// Get schedule_id from URL
$schedule_id = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : null;

// Fetch active semester and academic year
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

// Initialize schedule data
$schedule = null;

if ($schedule_id) {
    // Prepare the query to fetch the schedule with the given schedule_id
    $sql = "SELECT * FROM schedule WHERE schedule_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any schedule is found
    if ($result->num_rows > 0) {
        $schedule = $result->fetch_assoc(); // Store fetched schedule data
    } else {
        $schedule = null; // No matching schedule found
    }
}

// Fetch students for the schedule
$sql = "SELECT * FROM listofstudents WHERE schedule_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the selected schedule is active
$isActiveSchedule = $schedule && $schedule['semester'] == $activeSchedule['semester'] && $schedule['academic_year'] == $activeSchedule['academic_year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/class.css">
    <link rel="stylesheet" href="cqi/css/bootstrap.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <div class="d-flex">
        <?php include './includes/sidebar_class.php'; ?>

        <div id="mainContent" class="container p-4">
            <h1>Class</h1>
            
            <?php if ($schedule): ?>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Schedule ID: <?php echo htmlspecialchars($schedule['schedule_id']); ?></h5>
                        <h5>Subject Code: <?php echo htmlspecialchars($schedule['subject_code']); ?></h5>
                        <h5>Description: <?php echo htmlspecialchars($schedule['description']); ?></h5>
                        <h5>Teacher Assigned: <?php echo htmlspecialchars($schedule['username']); ?></h5>
                        <h5>Academic Year: <?php echo htmlspecialchars($schedule['academic_year']); ?></h5>
                    </div>
                    <div class="col-md-6">
                        <h5>Semester: <?php echo htmlspecialchars($schedule['semester']); ?></h5>
                        <h5>Course: <?php echo htmlspecialchars($schedule['course_code']); ?></h5>
                        <h5>Year: <?php echo htmlspecialchars($schedule['year']); ?></h5>
                        <h5>Section: <?php echo htmlspecialchars($schedule['section']); ?></h5>
                    </div>
                </div>
            <?php else: ?>
                <h5>No schedule found for the selected ID.</h5>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Student Number</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Course</th>
                                <th scope="col">Year</th>
                                <th scope="col">Section</th>
                                <th scope="col">Semester</th>
                                <th scope="col">Academic Year</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        <tbody id="listofstudents">
                        <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <th scope="row"><?php echo $row['studID']; ?></th>
                                    <td><?php echo $row['fullname']; ?></td>
                                    <td><?php echo $row['course_code']; ?></td>
                                    <td><?php echo $row['year']; ?></td>
                                    <td><?php echo $row['section']; ?></td>
                                    <td><?php echo $row['semester']; ?></td>
                                    <td><?php echo $row['academic_year']; ?></td>
                                    <td>
                                        <?php if ($isActiveSchedule): ?>
                                            <a class="btn btn-sm btn-danger" href="operations/delete.php?id=<?php echo $row['id']; ?>&schedule_id=<?php echo $schedule_id; ?>"
                                                onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                                        <?php else: ?>
                                            <span class="text-muted">View Only</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($isActiveSchedule): ?>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add Student</button>
                    <h2>Upload Excel File (.xlsx)</h2>
                    <p><strong>Note:</strong> Excel file will be converted to CSV automatically in-browser.</p>
                    <input type="file" id="excelFile" accept=".xlsx">
                    <button class="btn btn-success" onclick="convertExcelToCSV()">Upload</button>
                <?php else: ?>
                    <p class="text-muted">Actions are disabled for previous semesters/years.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal for Adding Student -->
    <?php if ($isActiveSchedule): ?>
        <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStudentModalLabel">Add Student: Class - <?php echo htmlspecialchars($schedule['schedule_id']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addStudentForm">
                            <div class="mb-3">
                                <label for="studentIdInput" class="form-label">Student Number</label>
                                <input type="text" class="form-control" id="studentIdInput" required>
                            </div>
                            <div class="mb-3">
                                <label for="fullNameInput" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullNameInput" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const scheduleId = "<?php echo htmlspecialchars($schedule_id); ?>";

        function convertExcelToCSV() {
            const fileInput = document.getElementById('excelFile');
            const file = fileInput.files[0];
            if (!file) return alert("Please select an Excel file.");

            const reader = new FileReader();
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });

                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const csv = XLSX.utils.sheet_to_csv(firstSheet);

                const form = document.createElement('form');
                form.method = 'POST';
                form.enctype = 'multipart/form-data';
                form.action = 'operations/addstudentexcel.php';

                const scheduleInput = document.createElement('input');
                scheduleInput.type = 'hidden';
                scheduleInput.name = 'schedule_id';
                scheduleInput.value = "<?php echo $schedule_id; ?>";

                const csvInput = document.createElement('input');
                csvInput.type = 'hidden';
                csvInput.name = 'csv_data';
                csvInput.value = csv;

                form.appendChild(scheduleInput);
                form.appendChild(csvInput);
                document.body.appendChild(form);
                form.submit();
            };
            reader.readAsArrayBuffer(file);
        }
    </script>
</body>
</html>
