<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'edumart');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if item ID is provided
if (!isset($_GET['id'])) {
    echo "Item not found.";
    exit();
}

$item_id = (int)$_GET['id'];

// Fetch item details
$sql = "SELECT items.*, users.username FROM items JOIN users ON items.user_id = users.id WHERE items.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo "Item not found.";
    exit();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['id'])) {
    $review = $conn->real_escape_string($_POST['review']);
    $rating = (int)$_POST['rating'];
    $user_id = $_SESSION['id'];

    $review_sql = "INSERT INTO reviews (item_id, user_id, review, rating) VALUES (?, ?, ?, ?)";
    $review_stmt = $conn->prepare($review_sql);
    $review_stmt->bind_param("iisi", $item_id, $user_id, $review, $rating);
    $review_stmt->execute();
    echo "Review submitted successfully!";
}

// Fetch item reviews
$reviews_sql = "SELECT reviews.*, users.username FROM reviews JOIN users ON reviews.user_id = users.id WHERE reviews.item_id = ? ORDER BY reviews.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $item_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['name']); ?> - Item Details</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0f0 0%, #000 100%);
            color: white;
            padding: 20px;
        }
        .item-details, .reviews, .add-review {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
            margin-bottom: 20px;
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        textarea, input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }
        button {
            padding: 10px 20px;
            background: #0f0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($item['name']); ?></h1>
    <div class="item-details">
        <p><strong>Seller:</strong> <?php echo htmlspecialchars($item['username']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($item['price'], 2); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
        <div class="images">
            <h3>Images:</h3>
            <?php 
            $images = explode(',', $item['images']);
            foreach ($images as $image) {
                echo "<img src='$image' alt='Item Image'>";
            }
            ?>
        </div>
    </div>

    <div class="reviews">
        <h2>Reviews</h2>
        <?php while ($review = $reviews_result->fetch_assoc()) { ?>
            <div class="review">
                <p><strong><?php echo htmlspecialchars($review['username']); ?>:</strong> <?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                <p>Rating: <?php echo $review['rating']; ?>/5</p>
                <hr>
            </div>
        <?php } ?>
    </div>

    <?php if (isset($_SESSION['id'])) { ?>
        <div class="add-review">
            <h2>Add a Review</h2>
            <form action="item.php?id=<?php echo $item_id; ?>" method="POST">
                <textarea name="review" rows="5" placeholder="Write your review here..." required></textarea>
                <label for="rating">Rating:</label>
                <select name="rating" required>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <button type="submit">Submit Review</button>
            </form>
        </div>
    <?php } else { ?>
        <p><a href="login.php">Login</a> to leave a review.</p>
    <?php } ?>
    <script>
                function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        }
    </script>
    <?php include '../components/student_footer.php'; ?>

</body>
</html>
