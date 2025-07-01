<?php 
include 'database/dbconn.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT * FROM account WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if account is active
        if ($row['status'] !== 'Active') {
            // Account is inactive
            header("Location: index.php?error=account_inactive");
            exit();
        }

        // Verify the password against the stored hash
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['email'] = $row['email'];
            $_SESSION['userID'] = $row['userID'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['usertype'] = $row['usertype'];

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Redirect based on user type
            switch ($_SESSION['usertype']) {
                case "Admin":
                    header("Location: admin/dashboard.php?user=" . urlencode($_SESSION['userID']));
                    break;
                case "Faculty":
                    header("Location: user/index.php?user=" . urlencode($_SESSION['userID']));
                    break;
                case "Chairperson":
                    header("Location: chairperson/index.php?user=" . urlencode($_SESSION['userID']));
                    break;
                case "Programhead":
                    header("Location: Programhead/index.php?user=" . urlencode($_SESSION['userID']));
                    break;
                default:
                    header("Location: index.php?error=unknown_user");
                    break;
            }
            exit();
        } else {
            // Incorrect password
            header("Location: index.php?error=invalid_password");
            exit();
        }
    } else {
        // No user found with that email
        header("Location: index.php?error=user_not_found");
        exit();
    }
} else {
    // Invalid access attempt
    header("Location: index.php?error=invalid_access");
    exit();
}
?>