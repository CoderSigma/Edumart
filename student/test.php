
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeepSeek App</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 1.5rem;
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 1rem;
            resize: vertical;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .response-container {
            margin-top: 2rem;
            text-align: left;
        }

        #response-output {
            background: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>DeepSeek API Integration</h1>
        <form id="deepseek-form" method="POST">
            <textarea name="input_text" placeholder="Enter your text here..." required><?php
                // Preserve input text after form submission
                echo isset($_POST['input_text']) ? htmlspecialchars($_POST['input_text']) : '';
            ?></textarea>
            <button type="submit">Submit</button>
        </form>

        <div class="response-container">
            <h2>API Response:</h2>
            <div id="response-output">
                <?php
                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Get input text from the form
                    $inputText = $_POST['input_text'];

                    // Replace with your DeepSeek API endpoint and key
                    $apiUrl = "https://api.deepseek.com/v1/endpoint"; // Example URL
                    $apiKey = "sk-15534fcdbdb64e968e6ce2ae4289c93e"; // Replace with your API key

                    // Prepare the API request
                    $data = [
                        'text' => $inputText,
                        // Add other parameters as required by the API
                    ];

                    // Initialize cURL
                    $ch = curl_init($apiUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $apiKey,
                    ]);

                    // Execute the request
                    $response = curl_exec($ch);
                    curl_close($ch);

                    // Display the API response
                    if ($response) {
                        echo htmlspecialchars($response);
                    } else {
                        echo "Error: No response from the API.";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>