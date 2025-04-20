<?php
namespace Contingent;

class D_TeHa
{
    public static function input($key, $default = null)
    {
        $data = [];

        // Retrieve data based on the request method
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $data = $_GET;
                break;
            case 'POST':
                $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

                // Check if the content type is JSON
                if ($contentType === 'application/json') {
                    // Decode JSON data for POST requests with JSON content
                    $jsonInput = file_get_contents("php://input");
                    $data = json_decode($jsonInput, true);
                } else {
                    // Otherwise, use regular POST data
                    $data = $_POST;
                }
                break;
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                parse_str(file_get_contents('php://input'), $data);
                break;
            case 'FILES':
                $data = $_FILES;
                break;
            // Add more cases for other request methods as needed
        }

        // Check if the input is a string (e.g., JSON data)
        if (is_string($data)) {
            // Decode JSON data
            $decoded_data = json_decode($data, true);

            // Check if decoding was successful and the result is an array
            if (is_array($decoded_data)) {
                $data = $decoded_data;
            } else {
                // If decoding failed or the result is not an array, use an empty array
                $data = [];
            }
        }

        // Sanitize HTML input if it exists
        if (isset($data[$key])) {
            $data[$key] = self::sanitize_html($data[$key]);
        }

        // Retrieve data from URL parameters if the key is not found in the current data
        if (!isset($data[$key]) && isset($_GET[$key])) {
            $data[$key] = $_GET[$key];
        }

        return $data[$key] ?? $default;
    }

    private static function sanitize_html($input) {
    if (is_array($input)) {
        // If input is an array, recursively sanitize each element
        foreach ($input as $key => $value) {
            $input[$key] = self::sanitize_html($value);
        }
    } else {
        // If input is a string, sanitize HTML tags
        // Define the allowed HTML tags and attributes
        $allowed_tags = '<mark><p><br><a><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><img><blockquote>';

        // Remove potentially harmful HTML tags
        $clean_html = strip_tags($input, $allowed_tags);

        $input = $clean_html;
    }

    return $input;
}


}
