<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli('localhost', 'root', '', 'edumart');
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Fetch categories
$categories = [];
if ($categoryQuery = $conn->query("SELECT category_id, category_name FROM categories")) {
    while ($row = $categoryQuery->fetch_assoc()) {
        $categories[] = $row;
    }
    $categoryQuery->free();
}

$uploadErrors = [];
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $category_id = filter_var($_POST['category_id'] ?? '', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];
    
    if (!$name || !$description || $price === false || $price <= 0 || !$category_id) {
        $uploadErrors[] = "Please fill in all fields correctly.";
    } else {
        $uploadDir = realpath(__DIR__ . '/../uploads/') . '/';
        $dbImagePath = '../uploads/';

        if (!is_dir($uploadDir)) {
            die("Upload directory does not exist. Please check the path.");
        }


        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $fileError = $_FILES['images']['error'][$key];
            $fileSize = $_FILES['images']['size'][$key];
            $fileName = basename($_FILES['images']['name'][$key]);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

            if ($fileError === UPLOAD_ERR_OK) {
                if (!in_array($fileExt, $allowedExt)) {
                    $uploadErrors[] = "Invalid file type: $fileName";
                    continue;
                }
                if ($fileSize > 5 * 1024 * 1024) {
                    $uploadErrors[] = "File too large: $fileName";
                    continue;
                }

                $uniqueName = uniqid('img_', true) . '.' . $fileExt;
                $filePath = $uploadDir . $uniqueName;
                $dbPath = $dbImagePath . $uniqueName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $imagePaths[] = $dbPath;
                } else {
                    $uploadErrors[] = "Failed to upload: $fileName";
                }
            }
        }

        if (empty($uploadErrors) && !empty($imagePaths)) {
            $images = implode(',', $imagePaths);
            $approved = 0;
            
            $stmt = $conn->prepare("INSERT INTO items (user_id, name, description, category_id, price, images, approved) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issidsi", $user_id, $name, $description, $category_id, $price, $images, $approved);
            
            if ($stmt->execute()) {
                $successMessage = "Item listed successfully! Awaiting approval.";
            } else {
                $uploadErrors[] = "Database error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } elseif (empty($imagePaths)) {
            $uploadErrors[] = "Please upload at least one valid image.";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell an Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
    .form-control, .form-select {
        border-radius: 12px;
        padding: 12px;
        border: 1px solid #ced4da;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .image-uploader {
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #ced4da;
        padding: 30px;
        border-radius: 12px;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .image-uploader:hover {
        border-color: #198754;
    }
    #image-preview img {
        width: 100px;
        margin-right: 10px;
        margin-top: 10px;
        border-radius: 8px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
</style>
</head>
<body>
    <?php include '../components/student_navbar.php'; ?>
    <?php include '../components/student_sidebar.php'; ?>
    
    <div class="container mt-5 pt-5">
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($uploadErrors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($uploadErrors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="sell.php" method="POST" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-white" style="max-width: 600px; margin: auto;">
            <h2 class="text-center mb-4">List Your Item</h2>
            
            <div class="mb-3">
                <label for="name" class="form-label">Item Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter item name" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" id="description" rows="4" placeholder="Provide a detailed description" required></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price ($)</label>
                <input type="number" class="form-control" name="price" id="price" step="0.01" placeholder="Enter price" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" name="category_id" id="category_id" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Images (JPEG, PNG, GIF, max 2MB each)</label>
                <div class="image-uploader" id="imageUploader">
                    <span>Click or Drag & Drop Images Here</span>
                    <input type="file" class="form-control d-none" name="images[]" id="images" accept="image/*" multiple>
                </div>
                <div id="image-preview" class="mt-3"></div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary py-2">List Item</button>
            </div>
        </form>
    </div><br>
    <?php include '../components/student_footer.php'; ?>

    <script>
    const imageInput = document.getElementById('images');
    const imageUploader = document.getElementById('imageUploader');
    const preview = document.getElementById('image-preview');

    imageUploader.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', handleImageUpload);

    imageUploader.addEventListener('dragover', (e) => {
        e.preventDefault();
        imageUploader.style.borderColor = '#198754';
    });

    imageUploader.addEventListener('dragleave', () => {
        imageUploader.style.borderColor = '#ced4da';
    });

    imageUploader.addEventListener('drop', (e) => {
        e.preventDefault();
        imageInput.files = e.dataTransfer.files;
        handleImageUpload();
    });

    function handleImageUpload() {
        preview.innerHTML = '';
        const files = imageInput.files;

        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = file.name;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    }
</script>

</body>
</html>
