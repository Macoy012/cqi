<?php 
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != "Chairperson") {
    header("Location: ../index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Check if a subject code is provided (for pages like mois.php)
$subject_code = isset($_GET['subject_code']) ? $_GET['subject_code'] : null;
?>

<!-- Sidebar with Toggle -->
<div id="sidebar" class="bg-dark text-white vh-100 p-3" style="width: 250px; position: fixed; text-align: center;">
    <img src="..\cqi.png" alt="cqi" style="max-width: 50px; margin-bottom: 20px;">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php" class="nav-link text-white <?= ($current_page == 'index.php') ? 'bg-success' : '' ?>">Dashboard</a>
        </li>
        <li class="nav-item">
            <a href="mois.php<?= $subject_code ? '?subject_code=' . urlencode($subject_code) : '' ?>" 
               class="nav-link text-white <?= ($current_page == 'mois.php') ? 'bg-success' : '' ?>">Midterms</a>
        </li>
        <li class="nav-item">
            <a href="fois.php<?= $subject_code ? '?subject_code=' . urlencode($subject_code) : '' ?>" 
               class="nav-link text-white <?= ($current_page == 'fois.php') ? 'bg-success' : '' ?>">Finals</a>
        </li>
        <li class="nav-item">
            <a href="../logout.php" class="nav-link text-danger">Logout</a>
        </li>
    </ul>
</div>
