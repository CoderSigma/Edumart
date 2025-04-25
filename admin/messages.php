<?php
require '../config/db.php';
session_start();

// Admin check (user_id = 1 assumed for admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$message_sent = "";

// Fetch the logged-in admin's username
$admin_username = "";
if ($_SESSION['user_id'] == 1) {
    $sql_admin = "SELECT username FROM users WHERE user_id = 1";
    $result_admin = $conn->query($sql_admin);
    if ($result_admin->num_rows > 0) {
        $admin_data = $result_admin->fetch_assoc();
        $admin_username = $admin_data['username'];
    }
}

// Handle message sending
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message']) && $user_id > 0) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Sanitize message input to prevent XSS attacks
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        // Prepare and execute the query to insert the message
        $sql = "INSERT INTO admin_messages (sender_id, user_id, receiver_id, message) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iiis", $_SESSION['user_id'], $user_id, $user_id, $message);  // Passing $user_id as receiver_id
            if ($stmt->execute()) {
                $message_sent = "✅ Message sent successfully!";
            } else {
                $message_sent = "❌ Failed to send message.";
            }
            $stmt->close();
        } else {
            $message_sent = "⚠️ Database error: " . $conn->error;
        }
    }
}

// Fetch users who have messages
$sql_users = "SELECT DISTINCT m.user_id, u.username
              FROM admin_messages m
              JOIN users u ON m.user_id = u.user_id
              ORDER BY u.username";

$result_users = $conn->query($sql_users);

// Fetch messages for selected user
$messages = [];
if ($user_id > 0) {
    $sql_messages = "SELECT m.message_id, m.message, m.sent_at, u.username
                     FROM admin_messages m
                     JOIN users u ON m.sender_id = u.user_id
                     WHERE m.user_id = ? 
                     ORDER BY m.sent_at ASC";

    if ($stmt_messages = $conn->prepare($sql_messages)) {
        $stmt_messages->bind_param("i", $user_id);
        $stmt_messages->execute();
        $result = $stmt_messages->get_result();
        if ($result->num_rows > 0) {
            $messages = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            echo "No messages found for this user.";
        }
        $stmt_messages->close();
    } else {
        echo "Query failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Messages</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
        }
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
        }
        .message-bubble {
            background-color: #e9f5ff;
            padding: 10px;
            border-radius: 10px;
        }
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
<body>
<!-- Navbar -->
<?php include '../components/admin_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <?php include '../components/admin_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 mt-4">
            <!-- Users List -->
            <div class="card mb-3">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">Users Who Have Sent Messages</h4>
                </div>
                <div class="card-body">
                    <?php if ($result_users->num_rows > 0): ?>
                        <ul class="list-group">
                            <?php while ($user = $result_users->fetch_assoc()): ?>
                                <li class="list-group-item">
                                    <a href="?user_id=<?= htmlspecialchars($user['user_id']) ?>">
                                        <?= htmlspecialchars($user['username']) ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No users have sent messages yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages View -->
            <?php if ($user_id > 0): ?>
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Conversation with User ID: <?= $user_id ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- Messages List -->
                        <div class="chat-box border rounded p-3 bg-light mb-4">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="mb-3">
                                    <div class="message-bubble">
                                        <strong><?= htmlspecialchars($msg['username']) ?>:</strong>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                        <small class="text-muted"><?= htmlspecialchars($msg['sent_at']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No messages from this user.</p>
                        <?php endif; ?>
                        </div>

                        <!-- Send Form (moved below conversation) -->
                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message:</label>
                                <textarea name="message" id="message" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Send</button>
                        </form>

                        <?php if (!empty($message_sent)): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($message_sent) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include '../components/admin_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
