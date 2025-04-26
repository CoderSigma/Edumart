<?php
session_start();
require_once '../config/db.php';

// Include navbar and sidebar
include '../components/student_navbar.php';
include '../components/student_sidebar.php';

if (!isset($_GET['item_id'])) {
    die("Invalid item.");
}

$item_id = intval($_GET['item_id']);

// Fetch item details
$itemQuery = "SELECT items.*, users.username AS seller, users.email AS seller_email, categories.category_name 
              FROM items 
              JOIN users ON items.user_id = users.user_id 
              JOIN categories ON items.category_id = categories.category_id 
              WHERE items.item_id = ?";
$stmt = $conn->prepare($itemQuery);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found.");
}

$uploadPath = "../";
$images = explode(',', $item['images']); // Assuming images are stored as comma-separated values
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['name']) ?> - Item Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .carousel-inner img {
            max-height: 500px;
            object-fit: cover;
        }
        .modal-img {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-4"><?= htmlspecialchars($item['name']) ?></h1>
    
    <div class="row">
        <div class="col-md-6">
            <!-- Image Slider -->
            <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= $uploadPath . htmlspecialchars(trim($image)) ?>" class="d-block w-100" alt="Item Image" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="openModal(this.src)">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <div class="col-md-6">
            <p><strong>Description:</strong> <?= htmlspecialchars($item['description']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($item['category_name']) ?></p>
            <p><strong>Price:</strong> P<?= htmlspecialchars($item['price']) ?></p>
            <p><strong>Listed By:</strong> <?= htmlspecialchars($item['seller']) ?> (<?= htmlspecialchars($item['seller_email']) ?>)</p>
            <p><strong>Created On:</strong> <?= isset($item['created_at']) ? htmlspecialchars($item['created_at']) : 'Not Available' ?></p>
            <a href="message.php?item_id=<?= urlencode($item['item_id']) ?>&user_id=<?= urlencode($item['user_id']) ?>" class="btn btn-primary">Message Seller</a>
        </div>
    </div>
</div>

<!-- Image Fullscreen Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" class="modal-img" src="" alt="Fullscreen Image">
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(src) {
        document.getElementById('modalImage').src = src;
    }
</script>

<?php include '../components/student_footer.php'; ?>


</body>
</html>
