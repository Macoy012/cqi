<?php 
include '../database/dbconn.php';
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Admin") {
    header("Location: ../index.php");
    exit();
}

$sql = "SELECT * FROM course";
$result = $conn->query($sql);

if ($result->num_rows > 0 ) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $courses = [];
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
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/courses.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">


</head>
<body>
<?php include './includes/navbar.php';?>
<div class="d-flex">
    <?php include './includes/sidebar.php';?>

    <div id="mainContent" class="container p-4">
        <div class="table-container">
            <h1>Courses</h1>
            <input type="text" id="searchBar" class="form-control search-bar" placeholder="Search..." onkeyup="filterTable()">
            <div class="table-wrapper">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Course ID</th>
                            <th scope="col">Program Code</th>
                            <th scope="col">Program</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php foreach ($courses as $row) { ?>
                            <tr>
                                <th scope="row"><?php echo htmlspecialchars($row['cid']); ?></th>
                                <td><?php echo htmlspecialchars($row['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#updateModal"
                                        data-cid="<?php echo htmlspecialchars($row['cid']); ?>"
                                        data-course_code="<?php echo htmlspecialchars($row['course_code']); ?>"
                                        data-course_name="<?php echo htmlspecialchars($row['course_name']); ?>">Update</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-cid="<?php echo htmlspecialchars($row['cid']); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-sm btn-success mb-1" data-bs-toggle="modal" data-bs-target="#addCourseModal">Add Course</button>
        </div>
    </div>
</div>


<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCourseModalLabel">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm" method="POST" action="operations/add_course.php">
                    <div class="mb-3">
                        <label for="course_code" class="form-label">Course Code</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="course_code" 
                            name="course_code" 
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="course_name" 
                            name="course_name" 
                            required>
                    </div>
                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Update Course Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateCourseForm">
                    <div class="mb-3">
                        <label for="update_cid" class="form-label">Course ID</label>
                        <input type="text" class="form-control" id="update_cid" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="update_course_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="update_course_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="update_course_name" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="update_course_name" required>
                    </div>
                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/courses.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>