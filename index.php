<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" href="http://localhost/edumart/pictures/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('pictures/IMG_5930.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Arial', sans-serif;
        }
        .logo {
            max-width: 250px;
        }
        .btn-custom {
            background-color:rgb(73, 206, 102);
            color: white;
            font-size: 16px;
            border-radius: 50px;
            padding: 15px 30px;
        }
        .btn-custom:hover {
            background-color:rgb(0, 92, 17);
            color:white;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .button-container {
            margin-top: 30px;
        }
        .heading {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        .gradient-text {
    background-image: linear-gradient(to right,rgb(178, 221, 3), #feb47b); /* Customize gradient colors */
    -webkit-background-clip: text;
    color: transparent; /* This is essential to show the gradient */
    font-weight: bold; /* Optional, for better visibility */
}

    </style>
</head>
<body>
    <div class="container">
        <div class="text-center">
            <img src="pictures/logo.png" alt="Logo" class="logo mb-4">
            <h1 class="display-4 gradient-text">EDUMART</h1>
            <div class="button-container">
                <a href="login.php" class="btn btn-custom mb-3 me-2">Login</a>
                <a href="register.php" class="btn btn-custom mb-3 ms-2">Register</a>
            </div>
            <p class="text-muted mt-4 ">Join us and start shopping today!</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
