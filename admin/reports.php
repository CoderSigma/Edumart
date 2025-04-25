<?php
require '../config/db.php'; // Database connection
include '../components/admin_navbar.php';
include '../components/admin_sidebar.php';

// Fetch user-reported problems, including the attachment column for images
$problems = $conn->query("SELECT reports.report_id, users.username, reports.problem_description, reports.created_at, reports.attachment
                         FROM reports 
                         JOIN users ON reports.user_id = users.user_id 
                         ORDER BY reports.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #343a40;
            padding: 20px;
            color: white;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <div class="content">
        <h2 id="tit" class="text-center mb-4">User-Reported Problems</h2>
        <div class="table-container">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <button class="btn btn-primary" id="exportExcel">Export to Excel</button>
                    <button class="btn btn-danger" id="exportPDF">Export to PDF</button>
                </div>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Problem Description</th>
                        <th>Date Reported</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($problems->num_rows > 0): ?>
                        <?php while ($row = $problems->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['report_id'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['problem_description']) ?></td>
                                <td><?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?></td>
                                <td>
                                    <?php if ($row['attachment']): ?>
                                        <a href="<?= $row['attachment'] ?>" target="_blank">
                                            <img src="<?= $row['attachment'] ?>" alt="Image Preview">
                                        </a>
                                    <?php else: ?>
                                        No attachment
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No problems reported yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../components/admin_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
