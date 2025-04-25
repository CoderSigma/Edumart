<?php
$conn = new mysqli('localhost', 'root', '', 'edumart');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null; 

$unreadCount = 0;
$notifications = [];
if ($user_id) {
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'unread'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($unreadCount);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT n.id, n.user_id, n.announcement_id, n.seen, a.title, a.message as announcement_message, a.created_at 
        FROM notifications n 
        JOIN announcements a ON n.announcement_id = a.announcement_id 
        WHERE n.user_id = ? 
        ORDER BY a.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../pictures/logo.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-success fixed-top">
    <a class="navbar-brand ml-3 text-white" href="../student/dashboard.php">Student Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <div class="ml-auto d-flex align-items-center">
            <span class="navbar-text text-white mr-3">
                Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>!
            </span>

            <!-- Messages Link -->
            <a href="message.php" class="text-white mr-3">
                <i class="fas fa-envelope"></i> Messages
                <?php if ($unreadCount > 0): ?>
                    <span class="badge badge-danger"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Notifications Dropdown -->
            <div class="dropdown mr-3">
                <button class="btn btn-link text-white dropdown-toggle" id="notificationDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge badge-danger"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
                    <h6 class="dropdown-header">Notifications</h6>

                    <?php if (empty($notifications)): ?>
                        <span class="dropdown-item text-muted">No new notifications</span>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <a class="dropdown-item small" href="view_announcement.php?id=<?= htmlspecialchars($notification['id']) ?>">
                                <?= nl2br(htmlspecialchars($notification['message'])) ?><br>
                                <?php if (!empty($notification['title'])): ?>
                                    <strong><?= htmlspecialchars($notification['title']) ?></strong><br>
                                <?php endif; ?>
                                <?php if (!empty($notification['announcement_message'])): ?>
                                    <?= nl2br(htmlspecialchars($notification['announcement_message'])) ?><br>
                                <?php endif; ?>
                                <small class="text-muted"><?= htmlspecialchars($notification['created_at']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center" href="all_notifications.php">View All</a>
                </div>

            </div>

            <!-- Settings Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-white dropdown-toggle" id="settingsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cog"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="settingsDropdown">
                    <a class="dropdown-item" href="profile.php">Profile</a>
                    <a class="dropdown-item" href="student_manage_listing.php">Manage Listing</a>
                    <a class="dropdown-item" href="settings.php">Settings</a>
                    <a class="dropdown-item" href="view_admin_messages.php">Message Admin</a>
                    <a class="dropdown-item" href="report.php">Report a Problem</a>
                    <div class="dropdown-divider"></div>
                    <form action="logout.php" method="POST">
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
</body>
</html>
