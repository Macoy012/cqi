<?php 
include '../database/dbconn.php';
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Admin") {
    header("Location: ../index.php");
    exit();
}

$sql = "SELECT * FROM subject";
$result = $conn->query($sql);

if ($result->num_rows > 0 ) {
    $subjects = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $subjects = [];
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
    <link rel="stylesheet" href="assets/css/subjects.css">
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
                            <th scope="col" class="w-10">No.</th>
                            <th scope="col" class="w-20">Program Code</th>
                            <th scope="col" class="w-50">Program</th>
                            <th scope="col" class="w-15">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="userTable">
                        <?php foreach ($subjects as $row) { ?>
                            <tr>
                                <th scope="row"><?php echo htmlspecialchars($row['subject_id']); ?></th>
                                <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-success me-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#updateModal"
                                        data-subject_id="<?php echo htmlspecialchars($row['subject_id']); ?>"
                                        data-subject_code="<?php echo htmlspecialchars($row['subject_code']); ?>"
                                        data-description="<?php echo htmlspecialchars($row['description']); ?>">
                                        Update
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-subject_id="<?php echo htmlspecialchars($row['subject_id']); ?>">Delete</button>
                                </td>
                            </tr>
                            <?php } ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-sm btn-success mb-1" data-bs-toggle="modal" data-bs-target="#addSubjectModal">Add Course</button>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSubjectForm">
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">No.</label>
                        <input type="text" class="form-control" id="subject_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="subject_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="description" required>
                    </div>
                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Subject Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateSubjectForm">
                    <div class="mb-3">
                        <label for="update_subject_id" class="form-label">Subject ID</label>
                        <input type="text" class="form-control" id="update_subject_id" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="update_subject_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="update_subject_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="update_description" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="update_description" required>
                    </div>
                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Alert Success Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="alertMessage">
                Operation was successful! The subject has been added to the list.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
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
                Are you sure you want to delete this subject?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>


<script src="assets/js/subjects.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>