<?php 
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Chairperson") {
    header("Location: ../index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar with Toggle -->
<div id="sidebar" class="bg-dark text-white vh-100 p-3" style="width: 250px; position: fixed; text-align: center;">
    <img src="..\cqi.png" alt="cqi" style="max-width: 50px; margin-bottom: 20px;">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php" class="nav-link text-white <?= ($current_page == 'index.php') ? 'bg-success' : '' ?>">Dashboard</a>
        </li>
        <li class="nav-item">
            <a href="../logout.php" class="nav-link text-danger">Logout</a>
        </li>
    </ul>
</div>
