<?php
session_start();
require 'config/db.php';
require 'config/smtp.php'; // Ensure PHPMailer is configured

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

ob_start(); // Start output buffering

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address.',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        </script>";
        echo $message;
        exit;
    }

    // Prepare and execute the query securely
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $new_password = bin2hex(random_bytes(4)); // Generate a secure random password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password in database securely
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $hashed_password, $email);
        if ($update_stmt->execute()) {
            // Send Email with PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Change if using another SMTP provider
                $mail->SMTPAuth   = true;
                $mail->Username   = 'edumart.ucv@gmail.com'; // Your SMTP email
                $mail->Password   = 'advi gzmd rifj nrnt'; // Use an App Password or real password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('edumart.ucv@gmail.com', 'EduMart Support');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Password Reset - EduMart";
                $mail->Body    = "<p>Hello <b>{$user['username']}</b>,</p>
                                  <p>Your new password is: <b>$new_password</b></p>
                                  <p>Please change your password after logging in.</p>
                                  <p>Best Regards,<br>EduMart Team</p>";

                $mail->send();
                $message = "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Reset',
                        text: 'A new password has been sent to your email.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then(() => { window.location.href = 'index.php'; });
                </script>";
            } catch (Exception $e) {
                $message = "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Email Error',
                        text: 'Failed to send email. Try again later.',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                </script>";
            }
        }
    } else {
        $message = "<script>
            Swal.fire({
                icon: 'error',
                title: 'User Not Found',
                text: 'This email is not registered in our system.',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4 shadow-sm" style="width: 350px;">
        <h2 class="text-center">Forgot Password</h2>
        <form action="" method="POST">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
        <p class="text-center mt-3"><a href="index.php">Back to Login</a></p>
    </div>
    <?php if (isset($message)) echo $message; ?> <!-- This ensures that Swal executes after the script loads -->
</body>
</html>
