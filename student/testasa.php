<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'config/database.php';
require 'classes/User.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for PHPMailer

session_start();

$db = new Database();
$connection = $db->getConnection();
$user = new User($connection);

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $loginUser = $user->login($email, $password);

    if ($loginUser) {
        $_SESSION['user'] = [
            'user_id' => $loginUser['user_id'],
            'role' => $loginUser['role'],
            'email' => $loginUser['email']
        ];

        $stmt = $connection->prepare("INSERT INTO logs (user_id, action, timestamp) VALUES (?, 'Login Successful', NOW())");
        $stmt->execute([$loginUser['user_id']]);

        $redirect_url = '';
        switch ($loginUser['role']) {
            case 'admin':
                $redirect_url = 'admin/dashboard.php';
                break;
            case 'student':
                // 2FA setup
                $code = rand(100000, 999999);
                $_SESSION['2fa_code'] = $code;
                $_SESSION['2fa_email'] = $loginUser['email'];

                // Send email via PHPMailer
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'khenzakie7@gmail.com'; // Your Gmail
                    $mail->Password = 'fnwk emhi khpr unye';  // App password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Email content
                    $mail->setFrom('your-email@gmail.com', 'Best Link College');
                    $mail->addAddress($loginUser['email']); // Send to user's email
                    $mail->isHTML(true);
                    $mail->Subject = 'Your 2FA Code';
                    $mail->Body    = "<h3>Hello!</h3><p>Your Best Link College 2FA code is: <strong>$code</strong></p>";

                    $mail->send();
                } catch (Exception $e) {
                    $error_message = "❌ Email not sent. Error: " . $mail->ErrorInfo;
                }

                $redirect_url = 'verify_2fa.php';
                break;
            case 'super_admin':
                $redirect_url = 'super_admin/dashboard.php';
                break;
            case 'staff':
                $redirect_url = 'staff/dashboard.php';
                break;
            default:
                $error_message = "❌ Unknown role. Please contact support.";
                session_destroy();
                break;
        }

        if (!empty($redirect_url)) {
            header("Location: $redirect_url");
            exit;
        }
    } else {
        $error_message = "❌ Invalid login credentials";

        $stmt = $connection->prepare("INSERT INTO logs (user_id, action, timestamp) VALUES (NULL, ?, NOW())");
        $stmt->execute(["Login Failed - Email: $email"]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Best Link College - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('uploads/BCPbg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: rgb(211, 60, 40);
        }
        .card { background-color: rgb(36, 123, 138); color: #fff; border-radius: 10px; }
        .form-control { background-color: #fff; color: #000; border: 1px solid #444; }
        .btn-primary { background-color: #444; border: none; }
        .btn-primary:hover { background-color: #666; }
        a { color: #bbb; } a:hover { color: #fff; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4" style="width: 350px;">
        <h3 class="text-center mb-3">BEST LINK COLLEGE</h3>
        <?php if (!empty($error_message)): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>
        <?php if (!empty($success_message)): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center mt-3"><a href="forget_password.php">Forgot password?</a></p>
    </div>
</body>
</html>
