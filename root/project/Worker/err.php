<?php
//902: Directive Errors

preg_match_all("/\[(.*?)\] => (.*?)(?= ,|\z)/", $message, $matches);
$extractedData = array_combine($matches[1], $matches[2]);
if(isset($extractedData['errorCode']) == '902'){
    $errorCode = isset($statusCode) ? $statusCode : $extractedData['errorCode'];
    if($extractedData['errorCode'] == '902'){
        $file = $extractedData['View_File'];
        $line = $extractedData['line'];
        $exceptionMessage = $extractedData['error'];
    }
}else{
    $file = isset($file) ? $file : 'Unknown file';
    $line = isset($line) ? $line : 'Unknown line';
    $trace = isset($trace) ? $trace : 'No trace available';

    // Define a simple error code or custom message if needed
    $errorCode = isset($statusCode) ? $statusCode : 500;
    $exceptionMessage = isset($message) ? $message : 'An unexpected error occurred'; // Use $formattedMessage here
}

$trace = isset($trace) ? $trace : 'No trace available'; // Ensure $trace is defined, e.g. $e->getTraceAsString()

if (isset($extractedData['errorCode']) == 419) {
    // Custom error page for 419 error
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="/hanako/project/public/md/css/mdb.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome for icons -->
    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js"></script> <!-- Font Awesome JavaScript -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <title>Error 419</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            text-align: center;
        }
        h1 {
            color: #e74c3c;
            font-size: 3em;
        }
        p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <span style="font-size: 3em;color: #e74c3c;">Page Expired  </span><span  class="btn btn-lg btn-link" style="font-size:25px;" data-bs-toggle="popover" title="For Developer" data-bs-content="nigga, you forgot to add @csrf to the form, if you already did, then its just error 419."><i class="fas fa-info-circle"></i></span>
        <p><strong>Page Expired. Please Go back to previous page or navigate or previous pages before submission</strong></p>
        <a href="/" class="btn btn-lg btn-link">Go Back to Home</a>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });
</script>

</body>
</html>
HTML;
    exit;
} else {
    // Default error page
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error $errorCode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            text-align: center;
        }
        h1 {
            color: #e74c3c;
            font-size: 3em;
        }
        p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .details {
            background-color: #f0f0f0;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .details strong {
            font-weight: bold;
        }
        pre {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            font-size: 1em;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .button {
            display: inline-block;
            padding: 12px 20px;
            background-color: #3498db;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Error $errorCode</h1>
        
        <p><strong>Sorry, something went wrong. Please try again later or contact support.</strong></p>
        
        <!-- Display error details -->
        <div class="details">
            <p><strong>Message:</strong> $exceptionMessage</p>
            <p style="word-break:break-all;"><strong>File:</strong> $file</p>
            <p><strong>Line:</strong> $line</p>
        </div>

        <!-- Stack trace or additional info -->
        <h2>Stack Trace:</h2>
        <pre>$trace</pre>

        <!-- Return to homepage -->
        <a href="/" class="button">Go Back to Home</a>
    </div>

</body>
</html>
HTML;

    exit;
}
?>