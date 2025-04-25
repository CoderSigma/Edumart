<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'edumart');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Retrieve user's notification settings
$query = "SELECT email_notifications, site_notifications FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($emailNotifications, $siteNotifications);
$stmt->fetch();
$stmt->close();

// Handle notification settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notifications'])) {
    $emailNotifications = isset($_POST['emailNotifications']) ? 1 : 0;
    $siteNotifications = isset($_POST['siteNotifications']) ? 1 : 0;

    $updateQuery = "UPDATE users SET email_notifications = ?, site_notifications = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("iii", $emailNotifications, $siteNotifications, $user_id);

    if ($updateStmt->execute()) {
        $notificationSuccess = "Notification settings updated successfully.";
    } else {
        $notificationError = "Failed to update notification settings.";
    }

    $updateStmt->close();
}

// Handle account deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_account'])) {
    $deactivateQuery = "UPDATE users SET approved = 0 WHERE user_id = ?";
    $deactivateStmt = $conn->prepare($deactivateQuery);
    $deactivateStmt->bind_param("i", $user_id);

    if ($deactivateStmt->execute()) {
        // Destroy session and redirect to login or homepage
        session_destroy();
        header("Location: index.php?deactivated=true");
        exit();
    } else {
        $deactivationError = "Failed to deactivate account. Please try again.";
    }

    $deactivateStmt->close();
}

include '../components/student_navbar.php';
include '../components/student_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Settings</h2>
    <hr>

    <!-- Notification Settings -->
    <h4>Notification Settings</h4>
    <?php if (isset($notificationSuccess)) : ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($notificationSuccess); ?></div>
    <?php elseif (isset($notificationError)) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($notificationError); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="emailNotifications" name="emailNotifications" <?php echo $emailNotifications ? 'checked' : ''; ?>>
            <label class="form-check-label" for="emailNotifications">
                Email Notifications
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="siteNotifications" name="siteNotifications" <?php echo $siteNotifications ? 'checked' : ''; ?>>
            <label class="form-check-label" for="siteNotifications">
                Website Notifications
            </label>
        </div>
        <button type="submit" name="save_notifications" class="btn btn-primary mt-3">Save Changes</button>
    </form>

    <hr>

    <!-- Change Password -->
    <h4>Change Password</h4>
    <form method="POST">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
    </form>
    <hr>

    <!-- Deactivate Account -->
    <h4>Deactivate account.</h4>
    <form method="POST" onsubmit="return confirm('Are you sure you want to deactivate your account?');">
        <button type="submit" name="deactivate_account" class="btn btn-danger">Deactivate Account</button>
    </form>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<?php include '../components/student_footer.php'; ?>
</body>
</html>
