<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #495057;
        }
        .error-message {
            font-size: 1.5rem;
            color: #6c757d;
        }
        .home-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <div class="error-message">Internal Router Error</div>
    </div>
</body>
</html>
