<?php 
include 'database/dbconn.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title> 
    <link rel="stylesheet" href="./css/bootstrap.css">
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
            background-image: url("cvsubg.jpg");
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(1px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 0;
        }

        .navbar {
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .logo {
            max-width: 120px;
        }

        .container-fluid {
            display: flex;
            justify-content: center;
            align-items: center;
            transform: translateX(-60px);
        }

        .text-center {
            text-align: center;
        }

        h5, h3 {
            color: white;
            margin: 0;
        }

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(2.5px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border-radius: 15px;
        }

    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-success fixed-top">
        <div class="container-fluid d-flex justify-content-center align-items-center">
            <img src="cvsu-logo.png" alt="cvsu" class="logo me-3">
            <div class="text-center">
                <span class="navbar-brand h1 d-block">CONTINUOUS QUALITY IMPROVEMENT</span>
                <h3 class="mb-0">Cavite State University - Indang, Cavite</h3>
                <h5 class="mt-0">College of Engineering and Information Technology</h5>
            </div>
        </div>
    </nav>

<!-- Login Form -->
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="p-5 login-card">
            <div class="text-center mb-3">
                <img src="cqi.png" alt="Login Image" style="max-width: 90px;">
            </div>
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label"><strong>Email:</strong></label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><strong>Password:</strong></label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember password?</label>
                </div>
                <br>
                <button type="submit" class="btn btn-success btn-lg rounded-pill w-100 shadow-sm" style="box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);"><strong>Login</strong></button>
            </form>
        </div>
    </div>

<!-- Error Modal -->
<div class="modal" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Login Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?php
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'invalid_password') {
                            echo "Incorrect password. Please try again.";
                        } elseif ($_GET['error'] == 'user_not_found') {
                            echo "No account found with that email. Please check your email.";
                        } elseif ($_GET['error'] == 'unknown_user') {
                            echo "An unknown user type was encountered.";
                        } else {
                            echo "An unknown error occurred. Please try again.";
                        }
                    }
                    ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    }
    </script>
</body>
</html>
