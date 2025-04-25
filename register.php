<?php
require 'config/db.php'; // Database connection

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        // Collect and sanitize inputs
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
        $address = trim($_POST['address']);
        $phone_number = trim($_POST['phone_number']);
        $id_number = trim($_POST['id_number']);
        $role = 'student';
        $approved = 0; // Default to not approved

        // Validate required fields
        if (!$age || empty($username) || empty($email) || empty($password) || empty($id_number)) {
            $error = "All fields are required.";
        } else {
            // Handle ID image upload
            $target_dir = "uploads/";
            $upload_ok = true;
            $file = $_FILES["id_image"];
            $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            // Validate file type and size (max 2MB)
            if (!in_array($file_ext, $allowed_types)) {
                $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
                $upload_ok = false;
            } elseif ($file["size"] > 2 * 1024 * 1024) {
                $error = "File size must be less than 2MB.";
                $upload_ok = false;
            }

            // Generate unique file name
            if ($upload_ok) {
                $id_image = $target_dir . uniqid("id_", true) . "." . $file_ext;

                if (!move_uploaded_file($file["tmp_name"], $id_image)) {
                    $error = "Failed to upload ID image. Please try again.";
                    $upload_ok = false;
                }
            }

            if ($upload_ok) {
                // Check if username, email, or ID number exists
                $check_sql = "SELECT user_id FROM users WHERE username = ? OR email = ? OR id_number = ?";
                $stmt = $conn->prepare($check_sql);
                $stmt->bind_param("sss", $username, $email, $id_number);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "Username, email, or ID number is already registered.";
                } else {
                    // Insert new user
                    $insert_sql = "INSERT INTO users (username, email, password, age, address, phone_number, id_number, id_image, role, approved) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("sssisssssi", $username, $email, $password, $age, $address, $phone_number, $id_number, $id_image, $role, $approved);

                    if ($stmt->execute()) {
                        $success = "Registration successful. Redirecting...";
                        echo "<script>
                                setTimeout(function() {
                                    window.location.href = 'login.php';
                                }, 3000);
                              </script>";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
                $stmt->close();
            }
        }
    }
}

// Generate a CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Background image and gradient overlay */
        body {
            background: url('pictures/edu.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin: 0;
        }

        /* Center the login card and add padding */
        .card {
            width: 350px;
            margin: 80px 150px;  /* Right and top margin */
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.8); /* Light background for the card */
        }

        .card p {
            color: black;
        }

        .card .btn {
            background-color: YellowGreen;
            color: black;
        }

        /* Optional: Adjust the height of the card */
        .card-body {
            padding: 20px;
        }
    </style>
</head>
<body class="d-flex align-items-center vh-100 bg-light">
    <div class="card p-4 shadow">
        <h2 class="text-center mb-3">Register</h2>

        <?php if ($error): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?= htmlspecialchars($error) ?>'
                });
            </script>
        <?php elseif ($success): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Registration Success!',
                    text: 'Please wait for admin approval.',
                    showConfirmButton: false,
                    timer: 3000
                }).then(() => {
                    window.location.href = 'login.php';
                });
            </script>
        <?php endif; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" name="age" placeholder="Age" required>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" name="address" placeholder="Address" required>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" name="phone_number" placeholder="Phone Number" required>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" name="id_number" placeholder="ID Number" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Upload ID Image (Max: 2MB)</label>
                <input type="file" class="form-control" name="id_image" accept="image/*" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn text-white" style="background-color: yellowgreen;">Register</button>
            </div>
        </form>
        <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
