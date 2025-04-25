<?php
require '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve the user_id from session
$admin_id = 1; // Admin ID (this can be dynamically fetched from the database if needed)

// Fetch messages between this user and the admin from the admin_messages table
$sql_messages = "SELECT m.message_id, m.message, m.sent_at, m.sender_type 
                 FROM admin_messages m 
                 WHERE (m.receiver_id = ? AND m.sender_id = ?) OR (m.receiver_id = ? AND m.sender_id = ?)
                 ORDER BY m.sent_at ASC";
$stmt_messages = $conn->prepare($sql_messages);
$stmt_messages->bind_param("iiii", $user_id, $admin_id, $admin_id, $user_id);
$stmt_messages->execute();
$result_messages = $stmt_messages->get_result();

// Handle message sending by the student/user
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['admin_message'])) {
    $admin_message = trim($_POST['admin_message']);
    if (!empty($admin_message)) {
        $sender_type = 'user'; // The student is sending the message

        // Insert the message into the database, including the user_id field
        $sql_insert = "INSERT INTO admin_messages (sender_id, receiver_id, message, sender_type, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iissi", $user_id, $admin_id, $admin_message, $sender_type, $user_id);
        if ($stmt_insert->execute()) {
            $message_sent = "Message sent successfully!";
        } else {
            $message_sent = "Error sending your message.";
        }
        $stmt_insert->close();

        // Redirect to avoid re-submitting the form on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

include '../components/student_navbar.php';
include '../components/student_sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-container {
            max-width: 600px;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .message {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 10px;
            max-width: 75%;
            word-wrap: break-word;
        }

        .message-admin {
            background-color: #f1f1f1;
            text-align: left;
            margin-left: 5%;
        }

        .message-user {
            background-color: #d1e7dd;
            text-align: right;
            margin-right: 5%;
        }

        .message-time {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .message-sender {
            font-weight: bold;
        }

        .form-container {
            margin-top: 30px;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Messages with Admin</h2>

        <div class="card mb-4">
            <div class="card-body chat-container" id="messages">
                <?php while ($message = $result_messages->fetch_assoc()): ?>
                    <div class="message <?php echo $message['sender_type'] === 'admin' ? 'message-admin' : 'message-user'; ?>">
                        <span class="message-sender"><?php echo $message['sender_type'] === 'admin' ? 'Admin' : 'You'; ?>:</span>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        <div class="message-time"><?php echo htmlspecialchars($message['sent_at']); ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="card form-container">
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="admin_message" class="form-label">Send a message to Admin</label>
                        <textarea name="admin_message" id="admin_message" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                    <?php if (isset($message_sent)): ?>
                        <div class="alert alert-info mt-3"><?php echo $message_sent; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script>
        const messagesContainer = document.getElementById('messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    </script>

    <?php include '../components/student_footer.php'; ?>
</body>
</html>
