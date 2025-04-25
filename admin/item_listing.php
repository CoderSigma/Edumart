<?php
session_start();
require '../config/db.php'; // Database connection

// Ensure user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Redirect function
function redirectTo($location) {
    header("Location: $location");
    exit();
}

// Approve item
if (isset($_GET['approve'])) {
    $item_id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE items SET approved = 1 WHERE item_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->close();
    }
    redirectTo("item_listing.php");
}

// Delete item
if (isset($_GET['delete'])) {
    $item_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->close();
    }
    redirectTo("item_listing.php");
}

// Fetch items
$query = "
    SELECT items.*, categories.category_name, users.username 
    FROM items 
    LEFT JOIN categories ON items.category_id = categories.category_id 
    LEFT JOIN users ON items.user_id = users.user_id 
    ORDER BY items.item_id DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Listing</title>
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
        .modal-body img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <?php include '../components/admin_navbar.php'; ?>

    <div class="container-fluid">
        <h2 class="text-center mb-4">Item Listing</h2>

        <div class="row">
            <div class="col-md-3">
                <?php include '../components/admin_sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between mb-3">
                    <button class="btn btn-primary" id="exportExcel">Export to Excel</button>
                    <button class="btn btn-danger" id="exportPDF">Export to PDF</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Item ID</th>
                                <th>Username</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['item_id']) ?></td>
                                    <td><?= htmlspecialchars($item['username'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($item['description']) ?></td>
                                    <td><?= htmlspecialchars($item['category_name'] ?? 'Unknown') ?></td>
                                    <td>$<?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                                    <td>
                                        <?php if (!empty($item['images']) && file_exists("../" . $item['images'])): ?>
                                            <img src="../<?= htmlspecialchars($item['images']) ?>" alt="Item Image" style="width: 50px; height: 50px; object-fit: cover;" 
                                                 onclick="showImage('../<?= htmlspecialchars($item['images']) ?>')" data-bs-toggle="modal" data-bs-target="#imageModal">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $item['approved'] ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-secondary">Pending</span>' ?>
                                    </td>
                                    <td>
                                        <?php if (!$item['approved']) : ?>
                                            <a href="?approve=<?= $item['item_id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                        <?php endif; ?>
                                        <a href="?delete=<?= $item['item_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/admin_footer.php'; ?>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Image Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Image Preview">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showImage(src) {
            document.getElementById('modalImage').src = src;
        }

        document.getElementById('exportExcel').addEventListener('click', function () {
            const table = document.querySelector('table'); 
            const exportInstance = new TableExport(table, {
                headers: true,
                footers: true,
                formats: ['xlsx'],
                filename: 'item_listing',
                bootstrap: true,
                exportButtons: false
            });
            const exportData = exportInstance.getExportData();
            const xlsxData = exportData[Object.keys(exportData)[0]].xlsx;
            exportInstance.export2file(xlsxData.data, xlsxData.mimeType, xlsxData.filename, xlsxData.fileExtension);
        });

        document.getElementById('exportPDF').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text('Item Listing', 14, 10);
            doc.autoTable({ 
                html: 'table', 
                startY: 20, 
                theme: 'grid', 
                headStyles: { fillColor: [33, 150, 243] } 
            });
            doc.save('item_listing.pdf');
        });
    </script>
</body>
</html>
