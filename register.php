<?php
include 'database/dbconn.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_POST['userID'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $usertype = $_POST['usertype'];

    // Check if passwords match
    if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM account WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already in use.";
        } else {
            // Hash the password before saving to the database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO account (userID, email, username, password, usertype) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $userID, $email, $username, $hashed_password, $usertype);

            if ($stmt->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "There was an error during registration. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T1wpuKPn6M5iIToTRN8mHlwfj5dGLz6thk6frzi8bYjz4+AuQe87bfuF7XJw7mZ0R" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #37474f;
            overflow: hidden;
        }

        .navbar {
            transition: top 0.4s ease-in-out;
        }

        .container {
            position: relative;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-success fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand mx-auto h1">Continuous Quality Improvement</span>
        </div>
    </nav>

    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="p-5 bg-light border rounded">
            <h1 class="text-center">Sign Up</h1>
            
            <!-- Display errors or success messages -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>
            
            <form action="#" method="post">
                <div class="mb-3">
                    <label for="userID" class="form-label">User ID:</label>
                    <input type="text" id="userID" name="userID" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="usertype" class="form-label">User Type:</label>
                    <select class="form-select" id="usertype" name="usertype" required>
                        <option value="Admin">Admin</option>
                        <option value="Facilitator">Facilitator</option>
                        <option value="Chairperson">Chairperson</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-lg rounded-pill w-100 shadow-sm"><strong>Sign Up</strong></button>
            </form>
        </div>
    </div>

</body>
</html>
