<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/db.php'; // Database connection
require '../vendor/autoload.php'; // Load PHPMailer

// Redirect to a specific location
function redirectTo($location) {
    header("Location: $location");
    exit();
}

// Send approval email
function sendApprovalEmail($email, $username) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'edumart.ucv@gmail.com';
        $mail->Password = 'advi gzmd rifj nrnt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@edumart.com', 'Edumart Admin');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Account Has Been Approved';
        $mail->Body = "Hello $username,<br><br>Your account has been approved. You can now log in and use your account.<br><br><a href='http://localhost/edumart/'>Click here to access your account</a>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

// Approve user
if (isset($_GET['approve'])) {
    $user_id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE users SET approved = 1 WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // Fetch user email and username
            $user_stmt = $conn->prepare("SELECT email, username FROM users WHERE user_id = ?");
            if ($user_stmt) {
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_stmt->bind_result($email, $username);
                if ($user_stmt->fetch()) {
                    sendApprovalEmail($email, $username);
                }
                $user_stmt->close();
            }
            redirectTo("manage_users.php");
        } else {
            echo "Error approving user: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);

    // Delete related messages
    $deleteMessagesStmt = $conn->prepare("DELETE FROM messages WHERE sender_id = ?");
    if ($deleteMessagesStmt) {
        $deleteMessagesStmt->bind_param("i", $user_id);
        $deleteMessagesStmt->execute();
        $deleteMessagesStmt->close();
    }

    // Delete related reviews
    $deleteReviewsStmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ?");
    if ($deleteReviewsStmt) {
        $deleteReviewsStmt->bind_param("i", $user_id);
        $deleteReviewsStmt->execute();
        $deleteReviewsStmt->close();
    }

    // Now delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            redirectTo("manage_users.php");
        } else {
            echo "Error deleting user: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all users
$result = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #343a40;
            padding: 20px;
            color: white;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Include Navbar -->
    <?php include '../components/admin_navbar.php'; ?>

    <!-- Include Sidebar -->
    <?php include '../components/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content">
        <h2 id="tit" class="text-center mb-4">Manage Users</h2>
        <div class="table-responsive">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <button class="btn btn-primary" id="exportExcel">Export to Excel</button>
                    <button class="btn btn-danger" id="exportPDF">Export to PDF</button>
                </div>
            </div>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>User ID</th>
                        <th>Profile</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>ID Number</th>
                        <th>ID Image</th>
                        <th>Age</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Approved</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>id_67b8805f254c29.58276382
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($user['profile_picture'] ?? 'default.jpeg') ?>" 
                                     alt="Profile" class="rounded" style="width: 50px; height: 50px; object-fit: cover;" 
                                     onclick="showImage('<?= htmlspecialchars($user['profile_picture']) ?>')" 
                                     data-bs-toggle="modal" data-bs-target="#imageModal">
                            </td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['id_number']) ?></td>
                            <td>
                                <img src="../<?= htmlspecialchars($user['id_image'] ?? 'default.jpeg') ?>" 
                                     alt="ID Image" class="rounded" style="width: 50px; height: 50px; object-fit: cover;" 
                                     onclick="showImage('<?= htmlspecialchars($user['id_image']) ?>')" 
                                     data-bs-toggle="modal" data-bs-target="#imageModal">
                            </td>
                            <td><?= htmlspecialchars($user['age']) ?></td>
                            <td><?= htmlspecialchars($user['address']) ?></td>
                            <td><?= htmlspecialchars($user['phone_number']) ?></td>
                            <td><?= $user['approved'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <?php if (!$user['approved']): ?>
                                    <a href="?approve=<?= $user['user_id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                <?php endif; ?>
                                <a href="?delete=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
