<?php
session_start();
require_once '../config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Validate item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$item_id = intval($_GET['id']);

// Update item status to 'sold'
$query = "UPDATE items SET status = 'sold' WHERE item_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("ii", $item_id, $user_id);
if ($stmt->execute()) {
    header("Location: student_manage_listing.php?success=Item marked as sold");
} else {
    die("Failed to mark item as sold.");
}
?>
