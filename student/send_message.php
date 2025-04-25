<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config.php'; // Ensure correct path

$logged_in_user_id = $_SESSION['user_id'] ?? 0;
$chat_user_id = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$item_id = isset($_POST['item_id']) && is_numeric($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$message = trim($_POST['message'] ?? '');

if ($logged_in_user_id === 0 || $chat_user_id === 0 || $item_id === 0 || empty($message)) {
    die("Invalid request.");
}

$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, item_id, timestamp) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$logged_in_user_id, $chat_user_id, $message, $item_id]);

echo "Message sent.";
?>
