<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("<p class='alert alert-danger'>Unauthorized access. Please log in.</p>");
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<p class='alert alert-danger'>Invalid request. No item selected.</p>");
}

$item_id = $_GET['id'];

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch current item
$query = "SELECT name, description, price, images FROM items WHERE item_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<p class='alert alert-warning'>No such item found or unauthorized access.</p>");
}

$item = $result->fetch_assoc();

// Handle delete image action
if (isset($_GET['delete_image'])) {
    $deleteImage = $_GET['delete_image'];

    $imagesArray = array_filter(explode(',', $item['images']));
    $key = array_search($deleteImage, $imagesArray);

    if ($key !== false) {
        $filePath = '../' . $deleteImage;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        unset($imagesArray[$key]);
        $newImages = implode(',', $imagesArray);

        $updateQuery = "UPDATE items SET images = ? WHERE item_id = ? AND user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sii", $newImages, $item_id, $user_id);
        $updateStmt->execute();

        header("Location: student_manage_listing.php?id=$item_id");
        exit();
    }
}

// Handle upload new image action
if (isset($_POST['update_image'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("<p class='alert alert-danger'>Invalid CSRF token.</p>");
    }

    $oldImage = $_POST['old_image'];

    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['new_image']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            echo "<p class='alert alert-danger'>Only JPEG, PNG, and GIF image files are allowed.</p>";
            exit();
        }

        $uploadDir = '../uploads/';
        $newFilename = 'img_' . uniqid() . '.' . pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
        $uploadPath = $uploadDir . $newFilename;

        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $uploadPath)) {
            $imagesArray = array_filter(explode(',', $item['images']));
            $key = array_search($oldImage, $imagesArray);

            if ($key !== false) {
                $oldFilePath = '../' . $oldImage;
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                $imagesArray[$key] = 'uploads/' . $newFilename;
                $newImages = implode(',', $imagesArray);

                $updateQuery = "UPDATE items SET images = ? WHERE item_id = ? AND user_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("sii", $newImages, $item_id, $user_id);
                $updateStmt->execute();

                header("Location: student_manage_listing.php?id=$item_id");
                exit();
            }
        } else {
            echo "<p class='alert alert-danger'>Failed to upload new image.</p>";
        }
    } else {
        echo "<p class='alert alert-danger'>No file selected or upload error.</p>";
    }
}

// Handle update listing details
if (isset($_POST['name'], $_POST['description'], $_POST['price']) && !isset($_POST['update_image'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("<p class='alert alert-danger'>Invalid CSRF token.</p>");
    }

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    if (empty($name) || empty($description) || $price < 0) {
        echo "<p class='alert alert-danger'>Please fill out all fields correctly.</p>";
    } else {
        $updateQuery = "UPDATE items SET name = ?, description = ?, price = ? WHERE item_id = ? AND user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssdii", $name, $description, $price, $item_id, $user_id);

        if ($updateStmt->execute()) {
            echo "<p class='alert alert-success'>Listing updated successfully!</p>";
            // Reload the item to reflect the updated data
            header("Location: student_manage_listing.php?id=$item_id&success=1");
            exit();
        } else {
            echo "<p class='alert alert-danger'>Failed to update listing. Please try again.</p>";
        }
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
    <title>Edit Listing</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .vertical-images img {
            max-width: 300px;
            height: auto;
            margin-bottom: 15px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Edit Listing</h2>

    <?php if (!empty($item['images'])): ?>
        <div class="form-group vertical-images">
            <label>Current Images:</label><br>
            <?php 
                $imagesArray = explode(',', $item['images']); 
                foreach ($imagesArray as $index => $imagePath): 
                    $imagePath = trim($imagePath);
                    if (!empty($imagePath)):
            ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" 
                     alt="Item Image" 
                     class="img-thumbnail"
                     data-toggle="modal" 
                     data-target="#imageModal<?= $index ?>">
                
                <!-- Modal -->
                <div class="modal fade" id="imageModal<?= $index ?>" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel<?= $index ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="imageModalLabel<?= $index ?>">Image Options</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body text-center">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="Modal Image" class="img-fluid mb-3">

                        <!-- Update Form -->
                        <form method="post" enctype="multipart/form-data" class="mb-2">
                            <input type="hidden" name="old_image" value="<?= htmlspecialchars($imagePath) ?>">
                            <div class="form-group">
                                <input type="file" name="new_image" class="form-control-file" required>
                            </div>
                            <button type="submit" name="update_image" class="btn btn-primary mb-2">Update Image</button>
                        </form>

                        <!-- Delete Button -->
                        <a href="student_manage_listing.php?id=<?= $item_id ?>&delete_image=<?= urlencode($imagePath) ?>" class="btn btn-danger mb-2" onclick="return confirm('Are you sure you want to delete this image?');">Delete Image</a><br>

                        <!-- Cancel Button -->
                        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                      </div>
                    </div>
                  </div>
                </div>
            <?php 
                    endif;
                endforeach; 
            ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="form-group">
            <label for="name">Item Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($item['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price (₱)</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($item['price']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Listing</button>
        <a href="student_manage_listing.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include '../components/student_footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
