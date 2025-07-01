<?php 
include '../database/dbconn.php';
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Admin") {
    header("Location: ../index.php");
    exit();
}

// Fetch schedules from the database
$stmt = $conn->prepare("SELECT * FROM schedule");
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error fetching schedules: " . $conn->error);
}

$sql_sub = "SELECT * FROM subject";
$result_sub = $conn->query($sql_sub);

$sql_course = "SELECT * FROM course";
$result_course = $conn->query($sql_course);

$sql_account = "SELECT * FROM account WHERE usertype = 'Faculty' || usertype = 'Personhead'";
$result_account = $conn->query($sql_account);

if ($result->num_rows > 0 ) {
    $schedules = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $schedules = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" ></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/schedules.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
<?php include './includes/navbar.php'; ?>
<div class="d-flex">
    <?php include './includes/sidebar.php'; ?>

    <div id="mainContent" class="container p-4">
        <div class="table-container">
            <h2>Schedules</h2>
            <input type="text" title="Use /sem search for schedules by semester" id="searchBar" class="form-control search-bar" placeholder="Search..." onkeyup="filterTable()">
            <div class="table-wrapper">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Schedule Code</th>
                            <th scope="col">Program Code</th>
                            <th scope="col">Program Title</th>
                            <th scope="col">Full Name</th>
                            <th scope="col">Academic Year</th>
                            <th scope="col">Semester</th>
                            <th scope="col">Course/Yr&Sect</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php foreach ($schedules as $row) { ?>
                            <tr>
                                <th scope="row"><?php echo htmlspecialchars($row['schedule_id']); ?></th>
                                <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['academic_year']); ?></td>
                                <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_code']) . ' ' . htmlspecialchars($row['year']) . '-' . htmlspecialchars($row['section']); ?></td>
                                <td>
                                    <button 
                                        class="btn btn-sm btn-success me-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateModal"
                                        data-schedule_id="<?php echo htmlspecialchars($row['schedule_id']); ?>"
                                        data-subject_id="<?php echo htmlspecialchars($row['subject_id']); ?>"
                                        data-course_code="<?php echo htmlspecialchars($row['cid']); ?>"
                                        data-subject_code="<?php echo htmlspecialchars($row['subject_code']); ?>"
                                        data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                        data-username="<?php echo htmlspecialchars($row['userID']); ?>"
                                        data-academic_year="<?php echo htmlspecialchars($row['academic_year']); ?>"
                                        data-semester="<?php echo htmlspecialchars($row['semester']); ?>"
                                        data-year_section="<?php echo htmlspecialchars($row['year']);?>"
                                        data-section="<?php echo htmlspecialchars($row['section']); ?>">
                                        Update
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-schedule_id="<?php echo htmlspecialchars($row['schedule_id']); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-sm btn-success mb-1" data-bs-toggle="modal" data-bs-target="#addScheduleModal">Add Schedule</button>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addScheduleModalLabel">Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addScheduleForm">
                    <div class="mb-3">
                        <label for="schedule_id" class="form-label">Schedule Code</label>
                        <input type="text" class="form-control" name="schedule_id" id="schedule_id" maxlength="9" pattern="\d{0,9}" required>
                        <label for="subject_id" class="form-label">Subject</label>
                        <select id="subject_id" name="subject_id" class="form-select" required>
                            <option value="" disabled selected>Select Subject</option>
                            <?php while ($row = $result_sub->fetch_assoc()) { ?>
                                <option value="<?php echo $row['subject_id']; ?>" data-subject_code="<?php echo $row['subject_code']; ?>">
                                    <?php echo $row['subject_code'] . " (" . $row['description'] . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="subject_code" id="subject_code">
                        <input type="hidden" name="description" id="description" value="">
                    </div>

                   <div class="mb-3">
                        <label for="cid" class="form-label">Program</label>
                        <select id="cid" name="cid" class="form-select" required>
                            <option value="" disabled selected>Select Program</option>
                            <?php while ($row = $result_course->fetch_assoc()) { ?>
                                <option value="<?php echo $row['cid']; ?>">
                                    <?php echo $row['course_code'] . " (" . $row['course_name'] . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="course_code" id="course_code" value="">
                    </div>

                    <div class="mb-3">
                        <label for="userID" class="form-label">Faculty</label><br>
                        <select id="userID" name="userID" class="form-select" required>
                            <option value="" disabled selected>Select Faculty</option>
                                <?php while ($row = $result_account->fetch_assoc()) { ?>
                            <option value="<?php
                                echo $row['userID']; 
                                ?>">
                                <?php echo $row['userID'] . " (" . $row['username'] . ")"; ?>
                            </option>
                        <?php } ?>
                        </select>
                        <input type="hidden" name="username" id="username" value="">
                    </div>

                    <div class="mb-3">
                        <?php
                        $currentYear = date('Y'); // Get the current year
                        $previousYear = $currentYear - 1; // Include the previous year
                        $endYear = $currentYear + 10; // Set a range for future academic years

                        echo '<label for="academic_year" class="form-label">Academic Year:</label><br>';
                        echo '<select name="academic_year" id="academic_year" class="form-control" required>';
                        echo '<option value="" disabled selected>Select Academic Year</option>';

                        // Start from the previous year
                        for ($year = $previousYear; $year < $endYear; $year++) {
                            $nextYear = $year + 1; // Calculate the next year
                            echo "<option value='$year-$nextYear'>$year - $nextYear</option>";
                        }

                        echo '</select>';
                        ?>
                    </div>
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester</label>
                        <select name="semester" id="semester" class="form-control">
                            <option value="" disabled selected>Select Semester</option>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="summer">Midyear</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="year" class="form-label">Year</label>
                        <select name="year" id="year" class="form-control">
                            <option value="" disabled selected>Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="section" class="form-label">Section</label>
                        <input type="text" class="form-control" name="section" id="section" required>
                    </div>

                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Add Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Schedule Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateScheduleForm">
                    <div class="mb-3">
                        <label for="update_schedule_id" class="form-label">Schedule Code</label>
                        <input type="text" class="form-control" name="schedule_id" id="update_schedule_id" required readonly>
                    </div>
                    <div class="mb-3">
                        <label for="update_subject_id" class="form-label">Subject</label>
                        <select id="update_subject_id" name="subject_id" class="form-select" required>
                            <?php 
                            $result_sub->data_seek(0); // Reset pointer for reuse
                            while ($row = $result_sub->fetch_assoc()) { ?>
                                <option value="<?php echo $row['subject_id']; ?>" data-subject_code="<?php echo $row['subject_code']; ?>">
                                    <?php echo $row['subject_code'] . " (" . $row['description'] . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_cid" class="form-label">Program</label>
                        <select id="update_cid" name="cid" class="form-select" required>
                            <?php 
                            $result_course->data_seek(0); // Reset pointer for reuse
                            while ($row = $result_course->fetch_assoc()) { ?>
                                <option value="<?php echo $row['cid']; ?>">
                                    <?php echo $row['course_code'] . " (" . $row['course_name'] . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_userID" class="form-label">Faculty</label>
                        <select id="update_userID" name="userID" class="form-select" required>
                            <?php 
                            $result_account->data_seek(0); // Reset pointer for reuse
                            while ($row = $result_account->fetch_assoc()) { ?>
                                <option value="<?php echo $row['userID']; ?>">
                                    <?php echo $row['userID'] . " (" . $row['username'] . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_academic_year" class="form-label">Academic Year</label>
                        <select name="academic_year" id="update_academic_year" class="form-select" required>
                            <?php 
                            $currentYear = date('Y');
                            $previousYear = $currentYear - 1;
                            $endYear = $currentYear + 10;
                            for ($year = $previousYear; $year < $endYear; $year++) {
                                $nextYear = $year + 1;
                                echo "<option value='$year-$nextYear'>$year - $nextYear</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_semester" class="form-label">Semester</label>
                        <select name="semester" id="update_semester" class="form-select">
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="summer">Midyear</option>
                        </select>
                    </div>
                        <div class="mb-3">
                            <label for="update_year" class="form-label">Year</label>
                            <select name="year" id="update_year" class="form-select" required>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                            </select>
                        </div>  

                        <div class="mb-3">
                            <label for="update_section" class="form-label">Section</label>
                            <input type="text" class="form-control" name="section" id="update_section" required>
                        </div>  

                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Update Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this schedule?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/schedules.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
