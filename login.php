<?php
session_start();
require 'config/db.php'; // Database connection

$error = '';

// Check if user is already logged in
if (isset($_SESSION['user_id']) || (isset($_COOKIE['remember_me']) && $_COOKIE['remember_me'] == true)) {
    header("Location: /edumart/student/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = $conn->real_escape_string($_POST['username_or_email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$username_or_email' OR username='$username_or_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['approved'] == 0) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Account Pending Approval',
                        text: 'Your account is not yet approved. Please contact administration or check your email for approval.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Handle "Remember Me"
            if (isset($_POST['remember_me'])) {
                setcookie('remember_me', true, time() + (86400 * 30), "/"); // 30 days
            }

            if ($user['role'] == 'admin') {
                header("Location: /edumart/admin/dashboard.php");
            } else {
                header("Location: /edumart/student/dashboard.php");
            }
            exit();
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: 'Incorrect password. Please try again.',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'User Not Found',
                    text: 'Please check your username or email.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="http://localhost/edumart/pictures/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Background image and gradient overlay */
        body {
            background: url('pictures/edu.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: justify;
            justify-content: flex-end;
            align-items: center;
            margin: 0;
        }

        /* Center the login card and add padding */
        .card {
            width: 350px;
            float: right;
            margin: 80px;
            margin-top: 142px;
            margin-right: 150px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.8); /* Light background for the card */
        }

        .card p {
            color: black;
        }

        .card .btn {
            background-color: YellowGreen;
            color: black;
        }
    </style>
</head>
<body>
    <div class="card p-4 shadow-sm">
        <h2 class="text-center">Login</h2>
        <form action="" method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" name="username_or_email" placeholder="Username or Email" required>
            </div>
            <div class="mb-3 position-relative">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                <span class="position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePassword()" style="cursor: pointer;">
                    <i id="password-icon" class="bi bi-eye-slash"></i>
                </span>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="remember_me" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>
            <button type="submit" class="btn w-100">Login</button>
        </form>
        <p class="text-center mt-3"><a href="forgot_password.php">Forgot Password?</a></p>
        <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</body>
</html>
