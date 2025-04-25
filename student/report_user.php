<?php
require '../config/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit();
}


error_log('User ID: ' . $_SESSION['user_id']);
error_log('Reported User ID: ' . $_POST['user_id']);
error_log('Reason: ' . $_POST['reason']);

// Get the user ID from session and the report reason from the POST request
$user_id = $_SESSION['user_id'];
$reported_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$reason = isset($_POST['reason']) ? $_POST['reason'] : '';

// Validate inputs
if ($reported_user_id == 0 || empty($reason)) {
    echo 'error';
    exit();
}

// Insert the report into the database
$sql = "INSERT INTO reports (user_id, problem_description) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo 'error';
    exit();
}

$stmt->bind_param("is", $reported_user_id, $reason);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}

$stmt->close();
$conn->close();
?>
