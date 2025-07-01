<?php
include '../database/dbconn.php';
session_start();

// Restrict access to Personhead only
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== "Programhead") {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_so'])) {
    $so = trim($_POST['so']);

    $check = $conn->prepare("SELECT 1 FROM so_mapping WHERE so = ?");
    $check->bind_param("s", $so);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO so_mapping (so, so_value) VALUES (?, '')");
        $stmt->bind_param("s", $so);
        $stmt->execute();

        header("Location: so_mapping.php?success=1");
        exit();
    } else {
        header("Location: so_mapping.php?error=duplicate");
        exit();
    }
}

// Handle Edit (SO column only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_so'])) {
    $sid = $_POST['sid'];
    $so = trim($_POST['so']);

    $check = $conn->prepare("SELECT 1 FROM so_mapping WHERE so = ? AND sid != ?");
    $check->bind_param("si", $so, $sid);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
        $stmt = $conn->prepare("UPDATE so_mapping SET so = ? WHERE sid = ?");
        $stmt->bind_param("si", $so, $sid);
        $stmt->execute();
        header("Location: so_mapping.php?edit=success");
        exit();
    } else {
        header("Location: so_mapping.php?error=editduplicate");
        exit();
    }
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $sid = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM so_mapping WHERE sid = ?");
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    header("Location: so_mapping.php?delete=success");
    exit();
}

// Handle AJAX update for so_value
if (isset($_POST['ajax_update']) && isset($_POST['sid']) && isset($_POST['so_value'])) {
    $stmt = $conn->prepare("UPDATE so_mapping SET so_value = ? WHERE sid = ?");
    $stmt->bind_param("si", $_POST['so_value'], $_POST['sid']);
    $stmt->execute();
    echo "success";
    exit;
}

// Fetch all SOs
$stmt = $conn->prepare("SELECT * FROM so_mapping ORDER BY so ASC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SO Mapping Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/index.css">
    <style>
        td[contenteditable="true"] {
            background-color: #fff8dc;
            min-width: 1000px;         /* Adjust width as needed */
            text-align: justify;
            padding: 8px;
            white-space: normal ;    /* Allows line breaks */
        }
    </style>
</head>
<body>
<?php include './includes/navbar.php'; ?>
<div class="d-flex">
    <?php include './includes/sidebar.php'; ?>

    <div id="mainContent" class="container mt-4">
        <div class="table-container">
            <h2>SO Mapping</h2>
            <div class="table-wrapper">
        <!-- Alerts -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                Duplicate SO.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'editduplicate'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                Cannot rename to a duplicate SO.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                SO added successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['edit']) && $_GET['edit'] === 'success'): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                SO updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                SO deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add SO Form -->
        <form method="POST" class="mb-3">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="so" class="form-control" placeholder="SO" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="add_so" class="btn btn-primary">Add SO</button>
                </div>
            </div>
        </form>

        <!-- Display SOs -->
        <table class="table table-boredered">
            <thead>
                <tr>
                    <th style="width: 20%;">SO</th>
                    <th style="width: 50%;">SO Value (Editable)</th>
                    <th style="width: 30%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="sid" value="<?= $row['sid'] ?>">
                            <td>
                                <input type="text" name="so" class="form-control" value="<?= htmlspecialchars($row['so']) ?>" required>
                            </td>
                            <td contenteditable="true" class="editable-so-value" data-sid="<?= $row['sid'] ?>">
                                <?= htmlspecialchars($row['so_value']) ?>
                            </td>
                            <td colspan="2">
                                <button type="submit" name="edit_so" class="btn btn-warning btn-sm">Edit</button>
                                <a href="?delete=<?= $row['sid'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this SO?');">Delete</a>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
            </div>
        </div>
        
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Save so_value changes on blur
    $(document).on('blur', '.editable-so-value', function () {
        const sid = $(this).data('sid');
        const newValue = $(this).text().trim();

        $.post('so_mapping.php', {
            ajax_update: true,
            sid: sid,
            so_value: newValue
        }, function (response) {
            if (response === 'success') {
                console.log('SO Value updated.');
            } else {
                alert('Error saving SO Value.');
            }
        });
    });
</script>
</body>
</html>
