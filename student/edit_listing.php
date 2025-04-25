<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p class='alert alert-danger'>Unauthorized access. Please log in.</p>");
}

$user_id = $_SESSION['user_id'];

// Check if an item ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<p class='alert alert-danger'>Invalid request. No item selected.</p>");
}

$item_id = $_GET['id'];

// Fetch the item details
$query = "SELECT name, description, price, status FROM items WHERE item_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<p class='alert alert-warning'>No such item found or unauthorized access.</p>");
}

$item = $result->fetch_assoc();

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Update query
    $updateQuery = "UPDATE items SET name = ?, description = ?, price = ?, status = ? WHERE item_id = ? AND user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssdssi", $name, $description, $price, $status, $item_id, $user_id);

    if ($updateStmt->execute()) {
        echo "<p class='alert alert-success'>Item updated successfully.</p>";
    } else {
        echo "<p class='alert alert-danger'>Update failed: " . $conn->error . "</p>";
    }
}

// Debugging output
echo "<p class='alert alert-info'>Debug: Editing Item ID = $item_id for User ID = $user_id</p>";

include '../components/student_navbar.php';
include '../components/student_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Listing</h2>
        <form method="post">
            <div class="form-group">
                <label for="name">Item Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($item['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($item['price']) ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Available" <?= $item['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="Sold" <?= $item['status'] === 'Sold' ? 'selected' : '' ?>>Sold</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Listing</button>
            <a href="student_manage_listing.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php include '../components/student_footer.php'; ?>

</body>
</html>
