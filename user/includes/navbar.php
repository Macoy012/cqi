<?php 

$current_page = basename($_SERVER['PHP_SELF']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<nav class="navbar navbar-dark bg-dark px-3" id ="navbar" >
    <div class="container-fluid d-flex align-items-center justify-content-end">
        
        <!-- Username -->
        <span class="text-white me-3"><?= htmlspecialchars($username) ?></span>

        <!-- Dropdown Menu -->
        <div class="dropdown me-3">
            <a class="btn btn-dark dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Menu
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
            </ul>
        </div>

        <!-- Logout Button -->
        <a class="btn btn-danger" href="../logout.php" role="button" aria-label="Logout">
            <strong>X</strong>
        </a>

    </div>
</nav>

<!-- Bootstrap JS (Ensures dropdown works) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
