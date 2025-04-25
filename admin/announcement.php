<?php
require '../config/db.php';
session_start();

// Admin check (user_id = 1 assumed for admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Handle announcement creation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['title']) && isset($_POST['message'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    
    if (!empty($title) && !empty($message)) {
        // Sanitize title and message input
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        // Insert the announcement into the database
        $sql = "INSERT INTO announcements (title, message) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $title, $message);
            if ($stmt->execute()) {
                $announcement_sent = "✅ Announcement posted successfully!";
            } else {
                $announcement_sent = "❌ Failed to post announcement.";
            }
            $stmt->close();
        } else {
            $announcement_sent = "⚠️ Database error: " . $conn->error;
        }
    }
}

// Fetch all announcements
$sql_announcements = "SELECT announcement_id, title, message, created_at FROM announcements ORDER BY created_at DESC";
$result_announcements = $conn->query($sql_announcements);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #343a40;
            color: white;
            transition: width 0.3s ease;
            overflow: hidden;
            z-index: 1000;
        }

        .sidebar.closed {
            width: 60px;
        }

        .sidebar h3 {
            padding: 15px;
            font-size: 20px;
            text-align: center;
        }

        .sidebar a {
            display: block;
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .sidebar.closed a {
            text-align: center;
            font-size: 14px;
            padding: 10px;
        }

        .sidebar.closed a span {
            display: none;
        }

        .toggle-btn {
            position: absolute;
            top: 15px;
            right: -25px;
            width: 25px;
            height: 25px;
            background-color: #343a40;
            color: white;
            font-size: 16px;
            text-align: center;
            line-height: 25px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
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
        <h1>Post an Announcement for Students</h1>
        
        <!-- Announcement Form -->
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post Announcement</button>
        </form>

        <?php if (!empty($announcement_sent)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($announcement_sent) ?></div>
        <?php endif; ?>

        <!-- Display Announcements -->
        <h2>Recent Announcements</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Posted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_announcements->num_rows > 0): ?>
                        <?php while ($announcement = $result_announcements->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($announcement['title']) ?></td>
                                <td><?= nl2br(htmlspecialchars($announcement['message'])) ?></td>
                                <td><?= htmlspecialchars($announcement['created_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No announcements yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../components/admin_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
