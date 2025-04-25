<?php
session_start();
require '../config/db.php'; // Ensure this file connects to your database

// Handle category addition
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->close();
        $message = "<div class='alert alert-success'>Category added successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Category name cannot be empty.</div>";
    }
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);

    // Check if the category is used in the items table
    $stmt = $conn->prepare("SELECT COUNT(*) FROM items WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($itemCount);
    $stmt->fetch();
    $stmt->close();

    if ($itemCount > 0) {
        $message = "<div class='alert alert-danger'>Cannot delete this category. It is associated with $itemCount item(s).</div>";
    } else {
        // Proceed to delete
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Category deleted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error occurred while deleting the category.</div>";
        }
        $stmt->close();
    }
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
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
    <?php include '../components/admin_navbar.php'; ?>
    <?php include '../components/admin_sidebar.php'; ?>

    <div class="content">
        <h2>Manage Categories</h2>

        <?php if (isset($message)) echo $message; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="category_name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary" name="add_category">Add Category</button>
        </form>

        <table class="table mt-4 table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['category_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td>
                        <a href="manage_categories.php?delete=<?php echo $row['category_id']; ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this category?');">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
