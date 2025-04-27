<?php
session_start();
require_once '../config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check database connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch user role
$roleQuery = "SELECT role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($roleQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $user['role'] === 'admin') {
    header("Location: /edumart/admin/dashboard.php");
    exit;
}

// Filter parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Build dynamic SQL
$sql = "SELECT items.*, categories.category_name, items.user_id AS seller_id,
        CASE 
            WHEN items.status = 'Sold' THEN 'Sold'
            ELSE 'Available'
        END AS display_status
        FROM items
        JOIN categories ON items.category_id = categories.category_id
        WHERE items.approved = 1
        AND items.status != 'Sold'";

$params = [];
$types = "";

// Add search term
if (!empty($searchTerm)) {
    $sql .= " AND items.name LIKE ?";
    $params[] = "%$searchTerm%";
    $types .= "s";
}

// Add category filter
if (!empty($selectedCategory)) {
    $sql .= " AND items.category_id = ?";
    $params[] = $selectedCategory;
    $types .= "i";
}

$sql .= " ORDER BY display_status DESC, items.item_id DESC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

// Path for images
$uploadPath = "../uploads/";

include '../components/student_navbar.php';
include '../components/student_sidebar.php';
?>

<div class="mt-4 pt-5">
    <h1 class="h2 text-center mt-4 pt-5">All Items for Sale</h1>

    <form method="GET" id="filterForm" class="mb-4 d-flex justify-content-center gap-2 align-items-center">

    
    <input 
        type="text" 
        name="search" 
        placeholder="Search product" 
        class="form-control" 
        style="width: 180px; margin-right: 10px;" 
        value="<?= htmlspecialchars($searchTerm) ?>" 
        id="searchInput"
    >
    <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            Filter
        </button>
        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
            <li>
                <a class="dropdown-item" href="#" onclick="selectCategory('')">All Categories</a>
            </li>
            <?php
            $categoryResult = $conn->query("SELECT category_id, category_name FROM categories");
            while ($cat = $categoryResult->fetch_assoc()):
            ?>
                <li>
                    <a class="dropdown-item" href="#" onclick="selectCategory('<?= $cat['category_id'] ?>')">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- Hidden input for category -->
    <input type="hidden" name="category" id="categoryInput" value="<?= htmlspecialchars($selectedCategory) ?>">
    </form>


    <!-- Item Grid -->
    <div class="row">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <a href="view_item.php?item_id=<?= urlencode($item['item_id']) ?>" class="text-decoration-none text-dark">
                        <div class="card h-100 p-2 <?= ($item['display_status'] === 'Sold') ? 'border-danger' : ''; ?>">
                            <?php
                            $imagePath = htmlspecialchars($item['images'] ?? '');
                            $fullImagePath = !empty($imagePath) && file_exists($uploadPath . basename($imagePath))
                                ? $uploadPath . basename($imagePath)
                                : '../assets/no-image.png';
                            ?>
                            <img src="<?= $fullImagePath ?>" class="card-img-top img-fluid" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <p class="card-text text-muted small"><?= htmlspecialchars($item['description']) ?></p>
                                <p class="card-text text-primary fw-bold small">P<?= htmlspecialchars($item['price']) ?></p>
                                <p class="text-<?= ($item['display_status'] === 'Sold') ? 'danger' : 'success'; ?> small mb-0">
                                    <?= $item['display_status'] ?>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-danger">No items found for your filter.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../components/student_footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 600);
    });

    // Function to select category from dropdown
    function selectCategory(categoryId) {
        document.getElementById('categoryInput').value = categoryId;
        document.getElementById('filterForm').submit();
    }
</script>

<br>