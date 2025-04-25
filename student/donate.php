<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'edumart');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($conn->real_escape_string($_POST['name']));
    $description = trim($conn->real_escape_string($_POST['description']));
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $user_id = $_SESSION['user_id']; // Ensure this session variable exists

    if (empty($name) || empty($description) || $category_id === 0) {
        $message = "Please fill out all required fields.";
    } else {
        // Image upload handling
        $imagePath = ""; // Initialize image path
        $allowedTypes = ['jpeg', 'jpg', 'png', 'gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        $uploadDir = "../uploads/"; // Use correct relative path for the upload directory

        foreach ($_FILES['images']['name'] as $key => $imageName) {
            if ($_FILES['images']['error'][$key] === 0) {
                $fileInfo = pathinfo($imageName);
                $fileExt = strtolower($fileInfo['extension']);
                $fileSize = $_FILES['images']['size'][$key];

                if (in_array($fileExt, $allowedTypes) && $fileSize <= $maxFileSize) {
                    $uniqueName = uniqid() . "_" . $fileInfo['basename'];
                    $imagePath = $uploadDir . $uniqueName;

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $imagePath)) {
                        break; // Store only the first valid image and exit loop
                    }
                }
            }
        }

        if (empty($message)) {
            // Insert item into the database with status='pending'
            $stmt = $conn->prepare("INSERT INTO items (user_id, name, description, category_id, price, images, approved) VALUES (?, ?, ?, ?, 0, ?, 0)");
            $stmt->bind_param("issis", $user_id, $name, $description, $category_id, $imagePath);

            if ($stmt->execute()) {
                $message = "Item listed successfully! It is currently pending approval.";
            } else {
                $message = "Failed to list the item. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate an Item - EduMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

<style>
    .form-control, .form-select {
        border-radius: 12px;
        padding: 12px;
        border: 1px solid #ced4da;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
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

<div class=" container" style="padding-top: 70px;">

    <!-- Message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Donation Item Submission Form -->
    <form action="donate.php" method="POST" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-white" style="max-width: 600px; margin: auto;">
        <h2 class="text-center mb-4">List Item for Donation</h2>
        <div class="mb-3">
            <label for="name" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter item name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Item Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Provide a brief description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="" disabled selected>Select a category</option>
                <?php
                $result = $conn->query("SELECT category_id, category_name FROM categories");
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['category_id'] . '">' . htmlspecialchars($row['category_name']) . '</option>';
                }
                ?>
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
            <button type="submit" class="btn btn-success">List Item for Donation</button>
        </div>
    </form>
</div>
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
