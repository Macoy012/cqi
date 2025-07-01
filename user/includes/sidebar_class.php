<?php 

$current_page = basename($_SERVER['PHP_SELF']);
$schedule_id = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : (isset($_SESSION['schedule_id']) ? $_SESSION['schedule_id'] : '');
?>

<!-- Sidebar with Toggle -->
<div id="sidebar" class="bg-dark text-white vh-100 p-3" style="width: 250px; position: fixed; text-align: center;">
    <img src="../cqi.png" alt="cqi" style="max-width: 50px; margin-bottom: 20px;">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php" class="nav-link text-white <?= ($current_page == 'index.php') ? 'bg-success' : '' ?>">Dashboard</a>
        </li>
        <li class="nav-item">
            <a href="class.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'class.php') ? 'bg-success' : '' ?>">Class</a>
        </li>
        <li class="nav-item">
            <!-- Midterm Navigation with sub-tabs -->
            <a class="nav-link text-white <?= ($current_page == 'midterms.php') ? 'bg-success' : '' ?>" href="#midtermNav" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="midtermNav">
                Midterms
            </a>
            <div class="collapse" id="midtermNav">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="midterms.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'midterms.php') ? 'bg-success' : '' ?>">View Midterms</a>
                    </li>
                    <li class="nav-item">
                        <a href="midterm_learning_outcome.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'midterm_learning_outcome.php') ? 'bg-success' : '' ?>">Midterm Learning Outcome</a>
                    </li>
                    <li class="nav-item">
                        <a href="overall_midterm_summary.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'midterm_summary.php') ? 'bg-success' : '' ?>">Overall Midterm Summary</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <!-- Finals Navigation with sub-tabs -->
            <a class="nav-link text-white <?= ($current_page == 'finals.php') ? 'bg-success' : '' ?>" href="#finalsNav" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="finalsNav">
                Finals
            </a>
            <div class="collapse" id="finalsNav">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="finals.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'finals.php') ? 'bg-success' : '' ?>">View Finals</a>
                    </li>
                    <li class="nav-item">
                        <a href="finals_learning_outcome.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'finals_learning_outcome.php') ? 'bg-success' : '' ?>">Finals Learning Outcome</a>
                    </li>
                    <li class="nav-item">
                        <a href="overall_finals_summary.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'finals_summary.php') ? 'bg-success' : '' ?>">Overall Finals Summary</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <!-- Overall Navigation with sub-tabs -->
            <a class="nav-link text-white <?= ($current_page == 'overall.php') ? 'bg-success' : '' ?>" href="#OverallNav" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="finalsNav">
                Overall Summary
            </a>
            <div class="collapse" id="OverallNav">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="overall.php?schedule_id=<?php echo htmlspecialchars($schedule_id); ?>" class="nav-link text-white <?= ($current_page == 'overall.php') ? 'bg-success' : '' ?>">Overall Summary</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a href="../logout.php" class="nav-link text-danger">Logout</a>
        </li>
    </ul>
</div>
