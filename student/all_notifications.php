<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'edumart');

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch announcements
$announcements = [];

$stmt = $conn->prepare("SELECT announcement_id, title, message, created_at FROM announcements ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Announcements</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>All Announcements</h2>
    <ul class="list-group">
        <?php foreach ($announcements as $announcement): ?>
            <li class="list-group-item">
                <strong><?= htmlspecialchars($announcement['title']) ?></strong>
                <p><?= nl2br(htmlspecialchars($announcement['message'])) ?></p>
                <br><small class="text-muted"><?= $announcement['created_at'] ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
