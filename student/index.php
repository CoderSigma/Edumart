<?php
// Set the redirect URL
$redirect_url = "/edumart/student/dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #dc3545;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            font-size: 18px;
            color: #333;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = "<?= $redirect_url ?>";
        }, 5000); // Redirect after 5 seconds
    </script>
</head>
<body>
    <div>
        <div class="loader"></div>
        <p class="message">Redirecting you to the student dashboard in <span id="countdown">5</span> seconds...</p>
    </div>
    <script>
        let timeLeft = 5;
        const countdownElement = document.getElementById("countdown");

        setInterval(function() {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval();
            }
        }, 1000);
    </script>
</body>
</html>
