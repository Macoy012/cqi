<?php 
$current_page = basename($_SERVER['PHP_SELF']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'N/A';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : 'N/A';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'N/A';
?>

<nav class="navbar navbar-dark bg-dark px-3" id="navbar">
    <div class="container-fluid d-flex align-items-center justify-content-end">
        <!-- Username -->
        <span class="text-white me-3"><?= htmlspecialchars($username) ?></span>

        <!-- Dropdown Menu -->
        <div class="dropdown me-3">
            <a class="btn btn-dark dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Menu
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">Profile</a></li>
                <!-- Future setting page -->
                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
            </ul>
        </div>

        <!-- Logout Button -->
        <a class="btn btn-danger" href="../logout.php" role="button" aria-label="Logout">
            <strong>X</strong>
        </a>
    </div>
</nav>

<!-- PROFILE MODAL -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">Your Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p><strong>User ID:</strong> <?= htmlspecialchars($userID) ?></p>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($fullname) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>

        <hr>

        <!-- Change Password Form -->
        <button class="btn btn-warning mb-2" type="button" onclick="togglePasswordForm()">Change Password</button>

        <form id="changePasswordForm" action="operations/change_password.php" method="POST" style="display: none;">
            <div class="mb-3">
                <label for="old_password" class="form-label">Old Password</label>
                <input type="password" class="form-control" name="old_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function togglePasswordForm() {
    const form = document.getElementById('changePasswordForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
