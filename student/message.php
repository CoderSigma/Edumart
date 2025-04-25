<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : null;
$chat_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $user_id;
$messages = [];

// Fetch user details
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: [];
}

$chat_user = getUserDetails($conn, $chat_user_id);

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && $item_id) {
    $message = trim($_POST['message']);

    if ($message) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at, item_id) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param('iisi', $user_id, $chat_user_id, $message, $item_id);
        $stmt->execute();
        header("Location: message.php?user_id=$chat_user_id&item_id=$item_id");
        exit();
    }
}

// Fetch chat messages
$stmt = $conn->prepare("SELECT sender_id, message, sent_at FROM messages 
                        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
                        ORDER BY sent_at ASC");
$stmt->bind_param('iiii', $user_id, $chat_user_id, $chat_user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();

// Fetch conversations
$stmt = $conn->prepare("SELECT DISTINCT u.user_id, u.username, u.profile_picture 
                        FROM users u 
                        JOIN messages m ON (m.sender_id = u.user_id OR m.receiver_id = u.user_id) 
                        WHERE (m.receiver_id = ? OR m.sender_id = ?) 
                        ORDER BY (SELECT MAX(sent_at) FROM messages WHERE sender_id = u.user_id OR receiver_id = u.user_id) DESC");
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .chat-container { border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); background: #fff; padding: 20px; }
        .message-box { height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; border-radius: 5px; }
        .message-item { padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .sent { background: #007bff; color: white; text-align: right; }
        .received { background: #e9ecef; }
        .message-form { display: flex; align-items: center; }
        .message-input { flex-grow: 1; margin-right: 10px; }
    </style>
</head>
<body class="bg-light">
    <?php include '../components/student_navbar.php'; ?>
    <?php include '../components/student_sidebar.php'; ?>
    
    <div class="container mt-4 pt-5">
        <div class="row">
            <div class="col-md-4" id="conversation-list">
                <h4>Your Conversations</h4>
                <ul class="list-group">
                    <?php while ($conversation = $conversations->fetch_assoc()): ?>
                        <li class="list-group-item d-flex align-items-center conversation-item">
                            <img src="../uploads/<?= htmlspecialchars($conversation['profile_picture'] ?: 'default.png'); ?>" class="rounded-circle mr-2" width="40" height="40">
                            <a href="message.php?user_id=<?= $conversation['user_id']; ?>&item_id=<?= $item_id ?? ''; ?>" class="conversation-link">
                                <?= htmlspecialchars($conversation['username']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="col-md-8">
                <?php if (!empty($chat_user)): ?>
                <div class="chat-container">
                    <div class="d-flex align-items-center mb-3">
                        <a href="dashboard.php" class="btn btn-secondary" id="back-button">&#8592; Back</a>
                        <a href="profile.php?user_id=<?= $chat_user_id; ?>" class="d-flex align-items-center ml-3">
                            <img src="../uploads/<?= htmlspecialchars($chat_user['profile_picture'] ?: 'default.png'); ?>" class="rounded-circle mx-2" width="40" height="40">
                            <strong><?= htmlspecialchars($chat_user['username']); ?></strong>
                        </a>
                    </div>
                    <div id="message-box" class="message-box">
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message-item <?= $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <?= htmlspecialchars($msg['message']); ?>
                                <small class="d-block text-muted">(<?= $msg['sent_at']; ?>)</small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <form method="POST" class="message-form">
                        <textarea class="form-control message-input" name="message" rows="1" placeholder="Type your message..." required></textarea>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let conversationList = document.getElementById("conversation-list");
            let backButton = document.getElementById("back-button");
            
            let conversationLinks = document.querySelectorAll(".conversation-link");
            conversationLinks.forEach(link => {
                link.addEventListener("click", function() {
                    conversationList.style.display = "none";
                });
            });

            if (backButton) {
                backButton.addEventListener("click", function() {
                    conversationList.style.display = "block";
                });
            }
        });
    </script>
    <?php include '../components/student_footer.php'; ?>

</body>
</html>
