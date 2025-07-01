<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../database/dbconn.php';
session_start();


if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Admin") {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION["userID"];
$username = "";

$query = $conn->prepare("SELECT username FROM account WHERE userID = ?");
$query->bind_param('i', $userID);
$query->execute();
$query->bind_result($username);
$query->fetch();
$query->close();

$accounts = [];
$result = $conn->query('SELECT * FROM account');
if ($result){
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
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
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/accounts.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">


</head>
<body>
    <?php include './includes/navbar.php';?>
<div class="d-flex">
    <?php include './includes/sidebar.php';?>
    
    <div id="mainContent" class="container p-4">
    <div class="container p-4">
    <div class="table-container">
        <h1>Accounts</h1>
        <input type="text" id="searchBar" class="form-control search-bar" placeholder="Search..." onkeyup="filterTable()">
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Employee Number</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">User  Type</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTable">
                    <?php foreach ($accounts as $row) { ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($row['userID']); ?></th>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['usertype']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-success me-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#updateModal"
                                    data-id="<?php echo htmlspecialchars($row['userID']); ?>"
                                    data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                    data-usertype="<?php echo htmlspecialchars($row['usertype']); ?>"
                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                    data-password="<?php echo htmlspecialchars($row['password']); ?>">Update</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-sm btn-success mb-1" data-bs-toggle="modal" data-bs-target="#addAccountModal">Add Account</button>
    </div>

    <div class="table-container mt-3">
        <h1>Department Chair</h1>
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Employee Number</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Department</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="chairPersonTable">
                    <?php 
                    $sql = "SELECT 
                                a.userID, 
                                a.username, 
                                IFNULL(d.department_name, 'N/A') AS department 
                                FROM account a 
                                LEFT JOIN department d ON a.userID = d.userID 
                                WHERE a.usertype = 'Chairperson'";
                    $rslt = $conn->query($sql);

                    while ($row = $rslt->fetch_assoc()) { ?>
                    <tr>
                        <th scope="row"><?php echo htmlspecialchars($row['userID']); ?></th>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-success me-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#assignDepartmentModal"
                                data-id="<?php echo htmlspecialchars($row['userID']); ?>"
                                data-username="<?php echo htmlspecialchars($row['username']); ?>">Assign Department</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-container mt-3"> <!-- Added margin-top for spacing -->
        <h1>Program Heads</h1>
        <div class="table-wrapper">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Employee Number</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Department</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="programHeadTable">
                    <?php
                    $query = "SELECT 
                                a.userID, 
                                a.username, 
                                IFNULL(d.department_name, 'N/A') AS department 
                              FROM account a 
                              LEFT JOIN department d ON a.userID = d.userID 
                              WHERE a.usertype = 'Programhead'";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($row['userID']); ?></th>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-success me-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#assignDepartmentModal"
                                    data-id="<?php echo htmlspecialchars($row['userID']); ?>"
                                    data-username="<?php echo htmlspecialchars($row['username']); ?>">Assign Department</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

</div>

<!-- Assign Department Modal -->
<div class="modal fade" id="assignDepartmentModal" tabindex="-1" aria-labelledby="assignDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignDepartmentModalLabel">Assign Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignDepartmentForm" method="POST" action="operations/assign_department.php">
                    <input type="hidden" name="userID" id="assign_userID">
                    <div class="mb-3">
                        <label for="assign_username" class="form-label">Employee Name</label>
                        <input type="text" class="form-control" id="assign_username" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="assign_department" class="form-label">Department</label>
                        <select name="department_name" id="assign_department" class="form-select">
                            <option value="" selected>Select Department/Unset</option>
                            <?php
                            // Fetch courses for the dropdown from the 'courses' table
                            $courseQuery = "SELECT cid, course_name FROM course";
                            $courseResult = $conn->query($courseQuery);
                            while ($courseRow = $courseResult->fetch_assoc()) { ?>
                                <option value="<?php echo htmlspecialchars($courseRow['cid']); ?>">
                                    <?php echo htmlspecialchars($courseRow['course_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAccountModalLabel">Add Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAccountForm">
                    <div class="mb-3">
                        <label for="userID" class="form-label">Employee Number</label>
                        <input type="text" class="form-control" id="userID" name="userID" maxlength="9" pattern="\d{0,9}" required>
                        <small class="form-text text-muted">Only numbers with up to 9 digits are allowed.</small>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                        <small class="form-text text-muted">Use <i>@cvsu.edu.ph</i> domain only</small>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="userType" class="form-label">User Type</label>
                        <select class="form-select" id="userType" required>
                            <option value="Admin">Admin</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Programhead">Programhead</option>
                            <option value="Chairperson">Chairperson</option>
                        </select>
                    </div>
                    <input type="hidden" id="Status" name="status" value="Active">
                    <div class="mb-3 text-end">
                        <button type="submit" class="btn btn-success">Add Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- UPDATE MODAL -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" method="POST" action="operations/update_account.php">
                        <div class="mb-3">
                            <label for="modalUserID" class="form-label">User ID</label>
                            <input type="text" class="form-control" id="modalUserID" name="userID" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="modalUsername" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="modalUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="modalEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="modalPassword" name="password" placeholder="Enter new password(leave empty to use current password)">
                            <input type="hidden" id="actualPassword" name="actualPassword">
                            <small id="passwordMessage" class="form-text text-muted"></small> <!-- Message about password -->
                        </div>
                        <div class="mb-3">
                            <label for="modalUserType" class="form-label">User Type</label>
                            <select class="form-select" id="modalUserType" name="usertype">
                                <option value="Admin">Admin</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Programhead">Programhead</option>
                                <option value="Chairperson">Chairperson</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modalStatus" class="form-label">Status</label>
                            <select class="form-select" id="modalStatus" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="assets/js/accounts.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>