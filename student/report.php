<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $problem_description = $conn->real_escape_string($_POST['problem_description']);
    $file_uploaded = false;
    $file_path = '';

    if (!empty($problem_description)) {
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            // Handle file upload
            $target_dir = "../uploads/";  // Directory to save the file
            $target_file = $target_dir . basename($_FILES["attachment"]["name"]);
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $file_size = $_FILES['attachment']['size'];

            // Check file size (max 5MB for example)
            if ($file_size > 5000000) {
                $error_message = "❌ File is too large. Maximum size is 5MB.";
            } else {
                // Check file type (Allow only image files or pdf for this example)
                if ($file_type == "jpg" || $file_type == "jpeg" || $file_type == "png" || $file_type == "pdf") {
                    if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
                        $file_uploaded = true;
                        $file_path = $target_file;  // Save the file path for later use in DB
                    } else {
                        $error_message = "❌ Error uploading the file. Please try again.";
                    }
                } else {
                    $error_message = "❌ Only JPG, JPEG, PNG, and PDF files are allowed.";
                }
            }
        }

        // If file upload is successful, insert the report into DB
        if ($file_uploaded) {
            $stmt = $conn->prepare("INSERT INTO reports (user_id, problem_description, attachment) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $user_id, $problem_description, $file_path);
            if ($stmt->execute()) {
                $success_message = "✅ Your problem has been reported successfully.";
            } else {
                $error_message = "❌ Failed to report the problem. Please try again.";
            }
            $stmt->close();
        } elseif (!$file_uploaded) {
            $stmt = $conn->prepare("INSERT INTO reports (user_id, problem_description) VALUES (?, ?)");
            $stmt->bind_param('is', $user_id, $problem_description);
            if ($stmt->execute()) {
                $success_message = "✅ Your problem has been reported successfully.";
            } else {
                $error_message = "❌ Failed to report the problem. Please try again.";
            }
            $stmt->close();
        }
    } else {
        $error_message = "⚠️ Please describe the problem.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report a Problem</title>
    <link rel="icon" type="image/x-icon" href="../pictures/logo.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .content-area {
            width: 100%;
            max-width: 700px;
            padding: 40px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        textarea {
            resize: vertical;
        }
        .main-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #filePreview {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../components/student_navbar.php'; ?>

    <div class="main-container">
        <?php include '../components/student_sidebar.php'; ?>
        <div class="content-area">
            <h3 class="text-center text-primary mb-4">🛠 Report a Problem</h3>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <form action="report.php" method="POST" enctype="multipart/form-data" novalidate>
                <div class="form-group">
                    <label for="problem_description" class="font-weight-bold">Problem Description</label>
                    <textarea class="form-control shadow-sm" id="problem_description" name="problem_description" rows="6" placeholder="Clearly explain the issue you're facing..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="attachment" class="font-weight-bold">Attach a File</label>
                    <input type="file" class="form-control" id="attachment" name="attachment">
                </div>
                <!-- Preview section -->
                <div id="filePreview"></div>

                <button type="submit" class="btn btn-success btn-block mt-3">📨 Submit Report</button>
            </form>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    // File input preview
    document.getElementById('attachment').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('filePreview');

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                let filePreviewContent = '';

                // Check file type and display preview accordingly
                const fileType = file.type.split('/')[0];
                if (fileType === 'image') {
                    filePreviewContent = `<img src="${e.target.result}" class="img-fluid" alt="File preview" style="max-width: 100%; height: auto;">`;
                } else if (file.type === 'application/pdf') {
                    filePreviewContent = `<embed src="${e.target.result}" type="application/pdf" width="100%" height="400px">`;
                } else {
                    filePreviewContent = `<p>File preview is not available for this file type.</p>`;
                }

                preview.innerHTML = filePreviewContent;
            }

            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
