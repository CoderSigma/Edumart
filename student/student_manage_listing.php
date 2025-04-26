<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("<p class='alert alert-danger'>Unauthorized access. Please log in.</p>");
}

$user_id = (int) $_SESSION['user_id'];

if (!$conn) {
    die("<p class='alert alert-danger'>Database connection failed: " . mysqli_connect_error() . "</p>");
}

// Fetch items for the current user
$query = "SELECT item_id, name, description, price, approved, status FROM items WHERE user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("<p class='alert alert-danger'>Query preparation failed: " . $conn->error . "</p>");
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("<p class='alert alert-danger'>Query execution failed: " . $stmt->error . "</p>");
}

$result = $stmt->get_result();

// Store results in an array
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

include '../components/student_navbar.php';
include '../components/student_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Listings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5"><br>
        <h2 class="mb-4">Manage Your Listings</h2>

        <?php if (!empty($items)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Approval Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['item_id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>$<?= number_format($row['price'], 2) ?></td>
                            <td>
                                <?php if ((int)$row['approved'] === 1): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <a href="edit_listing.php?id=<?= $row['item_id'] ?>" class="btn btn-warning btn-sm">Edit</a>

                                <?php if ((int)$row['approved'] === 1): ?>
                                    <a href="mark_sold.php?id=<?= $row['item_id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Mark this item as sold?')">Mark as Sold</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Mark as Sold</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="alert alert-warning">No listings found.</p>
        <?php endif; ?>
    </div>
    <?php include '../components/student_footer.php'; ?>
</body>
</html>
