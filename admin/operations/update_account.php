<?php
// update_account.php
include '../../database/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['userID'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $usertype = $_POST['usertype'];
    $status = $_POST['status'];
    $password = $_POST['password']; // New password entered by the user
    $actualPassword = $_POST['actualPassword']; // Actual password stored in hidden field

    // Check if the password is changed
    if (!empty($password)) {
        // If a new password is provided, hash it before updating
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE account SET username = ?, email = ?, usertype = ?, status = ?, password = ? WHERE userID = ?");
        $stmt->bind_param('sssssi', $username, $email, $usertype, $status, $passwordHash, $userID);
    } else {
        // If the password field is empty, use the existing password (from hidden field)
        $stmt = $conn->prepare("UPDATE account SET username = ?, email = ?, usertype = ?, status = ? WHERE userID = ?");
        $stmt->bind_param('ssssi', $username, $email, $usertype, $status, $userID);
    }

    // Execute the query
    if ($stmt->execute()) {
        header("Location: ../accounts.php?success=update");
    } else {
        echo "Error updating account: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
