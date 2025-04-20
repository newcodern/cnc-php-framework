<?php
namespace Contingent;

class Pusat_Komunikasi
{
    // Register error and exception handlers
    public static function register()
    {
        set_error_handler([__CLASS__, 'handleError']);
        set_exception_handler([__CLASS__, 'handleException']);
    }

    // Handle PHP errors
    public static function handleError($severity, $message, $file, $line)
    {
        // Check if output buffering is active before cleaning
        if (ob_get_level() > 0) {
            ob_clean(); // Clean the output buffer if it's active
        }

        // Pass original error data to the render function
        self::renderErrorView(compact('severity', 'message', 'file', 'line'));
    }

    // Handle exceptions
    public static function handleException($exception)
{
    // Clean any output buffer before rendering the error view
    if (ob_get_level() > 0) {
        ob_clean();
    }

    // Prepare error data specific to the exception
    $data = [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ];

    // Render the error view with the prepared data
    self::renderErrorView($data);
}


    // Render the error view with the provided data
    private static function renderErrorView($data)
    {
        // Start output buffering to capture the error page content
        ob_start();
        
        // Ensure the output buffer is clean before rendering
        if (ob_get_level() > 0) {
            ob_end_clean(); // Clear any active output buffers
        }

        // Extract variables to be used in the error view (ensure no overwriting of variables)
        extract($data, EXTR_SKIP);
        
        // Pass the actual error file and line information
        if (isset($file) && isset($line)) {
            $data['file'] = $file;
            $data['line'] = $line;
        } elseif (isset($exception)) {
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
        }

        // Render the error view (assumes `err.php` is located in the Worker folder)
        require_once __DIR__ . '/../Worker/err.php';
        
        // Capture the content of the error page
        $content = ob_get_clean();

        // Output the error page content
        echo $content;

        // Stop further script execution to avoid other content being displayed
        exit();
    }
}
