<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
            .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 250px;
        background-color: #343a40;
        color: white;
        transition: width 0.3s ease;
        overflow: hidden;
        z-index: 1000;
    }

    .sidebar.closed {
        width: 60px;
    }

    .sidebar h3 {
        padding: 15px;
        font-size: 20px;
        text-align: center;
    }

    .sidebar a {
        display: block;
        padding: 10px 15px;
        color: white;
        text-decoration: none;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #495057;
    }

    .sidebar.closed a {
        text-align: center;
        font-size: 14px;
        padding: 10px;
    }

    .sidebar.closed a span {
        display: none;
    }

    .toggle-btn {
        position: absolute;
        top: 15px;
        right: -25px;
        width: 25px;
        height: 25px;
        background-color: #343a40;
        color: white;
        font-size: 16px;
        text-align: center;
        line-height: 25px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 1001;
    }
        .content {
            margin-left: 270px;
            padding: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Include Navbar -->
    <?php include '../components/admin_navbar.php'; ?>

    <!-- Include Sidebar -->
    <?php include '../components/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content">
        <h2>Welcome, (Admin)</h2>
        <p>This is your admin dashboard where you can manage users, items, reports, and messages.</p>

        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text">Approve, delete, or modify user accounts.</p>
                        <a href="manage_users.php" class="btn btn-light">Go</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Item Listings</h5>
                        <p class="card-text">Approve or delete items posted by students.</p>
                        <a href="item_listing.php" class="btn btn-light">Go</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">View system logs and activity reports.</p>
                        <a href="reports.php" class="btn btn-light">Go</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include '../components/admin_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
