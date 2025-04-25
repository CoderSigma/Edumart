<?php
require '../config/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$session_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $session_user_id;

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $session_user_id; // Profile being viewed

$message = '';

// Fetch user information securely
$sql = "SELECT user_id, username, email, age, address, phone_number, id_number, profile_picture FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    die("User not found.");
}

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_review'])) {
    $review_text = trim($_POST['review_text']);
    $rating = intval($_POST['rating']);
    $reviewer_id = $_SESSION['user_id']; // The logged-in user

    // Validate review input
    if (empty($review_text)) {
        $message = "<div class='alert alert-danger'>Review text cannot be empty.</div>";
    } elseif ($rating < 1 || $rating > 5) {
        $message = "<div class='alert alert-danger'>Rating must be between 1 and 5.</div>";
    } else {
        // Insert review into database
        $sql_insert = "INSERT INTO reviews (user_id, reviewer_id, review_text, rating) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        if (!$stmt_insert) {
            die("Database error: " . $conn->error);
        }
        $stmt_insert->bind_param("iisi", $user_id, $reviewer_id, $review_text, $rating);

        if ($stmt_insert->execute()) {
            $message = "<div class='alert alert-success'>Review submitted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error submitting review.</div>";
        }
        $stmt_insert->close();
    }
}

// Fetch user reviews
$sql_reviews = "SELECT r.review_text, r.rating, r.created_at, u.username AS reviewer_name
                FROM reviews r 
                JOIN users u ON r.reviewer_id = u.user_id 
                WHERE r.user_id = ?";
$reviews_stmt = $conn->prepare($sql_reviews);
if (!$reviews_stmt) {
    die("Database error: " . $conn->error);
}
$reviews_stmt->bind_param("i", $user_id);
$reviews_stmt->execute();
$result_reviews = $reviews_stmt->get_result();

// Handle Profile Picture Upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['upload_profile_pic'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = basename($_FILES['profile_pic']['name']);
        $uploadDir = '../uploads/';
        $uploadPath = $uploadDir . $fileName;

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // Update database with new profile picture path
                $sql_update = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param("si", $uploadPath, $user_id);
                    if ($stmt_update->execute()) {
                        $message = "<div class='alert alert-success'>Profile picture updated successfully.</div>";
                        $user['profile_picture'] = $uploadPath; // Update the user array with the new profile picture
                    } else {
                        $message = "<div class='alert alert-danger'>Failed to update profile picture.</div>";
                    }
                    $stmt_update->close();
                }
            } else {
                $message = "<div class='alert alert-danger'>Error uploading file. " . $_FILES['profile_pic']['error'] . "</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.</div>";
        }
    } else {
        // Display the specific error code if the upload failed
        $message = "<div class='alert alert-danger'>File upload error. Error code: " . $_FILES['profile_pic']['error'] . "</div>";
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
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            color: #333;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
        }
        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .btn-custom {
            background-color: #4CAF50;
            color: white;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom text-center border-0">
                    <div class="card-body">
                        <h2 class="mb-4"><?php echo htmlspecialchars($user['username']); ?></h2>
                        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: '../uploads/default.jpeg'); ?>" 
                             alt="Profile Picture" class="profile-img mb-4">
                           <?php if (!isset($_GET['user_id'])) : ?>
                                <!-- Upload Form (Visible only if 'user_id' is not set in the URL) -->
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="profile_picture" class="form-label">Upload Profile Picture</label>
                                        <input type="file" name="profile_pic" id="profile_picture" class="form-control" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="upload_profile_pic" class="btn btn-custom shadow-sm fw-bold">Upload</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>

                        <div class="row text-start mt-4">
                            <div class="col-6">
                                <p><strong>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                            </div>
                            <div class="col-6">
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                                <p><strong>ID Number:</strong> <?php echo htmlspecialchars($user['id_number']); ?></p>
                            </div>
                        </div>

                        <?php if (!empty($message)): ?>
                            <?php echo $message; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php
                $session_user_id = intval($_SESSION['user_id']);
                $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $session_user_id;
                ?>
                
                <!-- Review Submission Form -->
                <?php if ($session_user_id !== $user_id): ?>
                <div class="card card-custom mt-4">
                    <div class="card-body">
                        <h2 class="text-center mb-3">Write a Review</h2>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="review_text" class="form-label">Your Review</label>
                                <textarea name="review_text" id="review_text" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating (1-5)</label>
                                <select name="rating" id="rating" class="form-select" required>
                                    <option value="">Select Rating</option>
                                    <option value="1">⭐ 1</option>
                                    <option value="2">⭐⭐ 2</option>
                                    <option value="3">⭐⭐⭐ 3</option>
                                    <option value="4">⭐⭐⭐⭐ 4</option>
                                    <option value="5">⭐⭐⭐⭐⭐ 5</option>
                                </select>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-custom w-100">Submit Review</button>
                        </form>
                        <button class="btn btn-danger mt-4" onclick="reportUser(<?php echo $user_id; ?>)">Report User</button>

                    </div>
                </div>
                <?php endif; ?>

                <!-- Reviews Section -->
                <div class="card card-custom mt-4">
                    <div class="card-body">
                        <h2 class="text-center mb-3">User Reviews</h2>
                        <?php if ($result_reviews->num_rows > 0): ?>
                            <?php while ($review = $result_reviews->fetch_assoc()): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <p><strong><?php echo htmlspecialchars($review['reviewer_name']); ?>:</strong> 
                                       <?php echo htmlspecialchars($review['review_text']); ?></p>
                                    <p><strong>Rating:</strong> ⭐ <?php echo htmlspecialchars($review['rating']); ?>/5</p>
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($review['created_at']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No reviews yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.0/dist/sweetalert2.all.min.js"></script>

    <script>
        function reportUser(user_id) {
            Swal.fire({
                title: 'Why are you reporting this user?',
                input: 'select',
                inputOptions: {
                    'spam': 'Spam',
                    'abusive': 'Abusive Behavior',
                    'inappropriate': 'Inappropriate Content',
                    'other': 'Other'
                },
                inputPlaceholder: 'Choose a reason...',
                showCancelButton: true,
                confirmButtonText: 'Report',
                cancelButtonText: 'Cancel',
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('Please select a reason');
                        return false;
                    }

                    // Send the report via AJAX
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'report_user.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            if (xhr.responseText === 'success') {
                                Swal.fire('Thank you for reporting!', 'Your report has been submitted.', 'success');
                            } else {
                                Swal.fire('Error', 'There was an issue submitting the report.', 'error');
                            }
                        }
                    };
                    xhr.send('user_id=' + user_id + '&reason=' + reason);
                }
            });
        }
    </script>
</body>
</html>
