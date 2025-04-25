<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : null;

if ($chat_user_id > 0) {
    $stmt = $conn->prepare("SELECT sender_id, message, sent_at FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_at ASC");
    $stmt->bind_param('iiii', $user_id, $chat_user_id, $chat_user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $class = ($row['sender_id'] == $user_id) ? 'text-right' : 'text-left';
        echo "<div class='$class'><strong>" . ($row['sender_id'] == $user_id ? 'You' : 'Them') . ":</strong> " . htmlspecialchars($row['message']) . "<br><small>" . $row['sent_at'] . "</small></div>";
    }
    $stmt->close();
}
?>
