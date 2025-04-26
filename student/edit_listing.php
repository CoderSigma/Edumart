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

// Fetch the item details (including images)
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
        // Remove file from server
        $filePath = '../' . $deleteImage;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove image path from database
        unset($imagesArray[$key]);
        $newImages = implode(',', $imagesArray);

        $updateQuery = "UPDATE items SET images = ? WHERE item_id = ? AND user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sii", $newImages, $item_id, $user_id);
        $updateStmt->execute();

        // Redirect to prevent resubmission
        header("Location: edit_item.php?id=$item_id");
        exit();
    }
}

// Handle upload new image action
if (isset($_POST['update_image'])) {
    $oldImage = $_POST['old_image'];

    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === 0) {
        $uploadDir = '../uploads/';
        $newFilename = 'img_' . uniqid() . '.' . pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
        $uploadPath = $uploadDir . $newFilename;

        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $uploadPath)) {
            // Update image path in database
            $imagesArray = array_filter(explode(',', $item['images']));
            $key = array_search($oldImage, $imagesArray);

            if ($key !== false) {
                // Delete old image file
                $oldFilePath = '../' . $oldImage;
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                // Replace old image with new one
                $imagesArray[$key] = 'uploads/' . $newFilename;
                $newImages = implode(',', $imagesArray);

                $updateQuery = "UPDATE items SET images = ? WHERE item_id = ? AND user_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("sii", $newImages, $item_id, $user_id);
                $updateStmt->execute();

                // Redirect to avoid resubmission
                header("Location: edit_item.php?id=$item_id");
                exit();
            }
        } else {
            echo "<p class='alert alert-danger'>Failed to upload new image.</p>";
        }
    } else {
        echo "<p class='alert alert-danger'>No file selected or upload error.</p>";
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
                        <a href="?id=<?= $item_id ?>&delete_image=<?= urlencode($imagePath) ?>" class="btn btn-danger mb-2" onclick="return confirm('Are you sure you want to delete this image?');">Delete Image</a><br>

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
