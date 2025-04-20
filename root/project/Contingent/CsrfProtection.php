<?php
namespace Contingent;

class CsrfProtection {
    protected $tokens = [];

    public function __construct() {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        $this->tokens = &$_SESSION['csrf_tokens'];
    }

    /**
     * Generate a CSRF token field for a specific form.
     *
     * @param string $formName The unique name of the form.
     * @return string The HTML input field with the CSRF token.
     */
    public function generateTokenField(string $formName): string {
        if (!isset($this->tokens[$formName]) || empty($this->tokens[$formName])) {
            $this->tokens[$formName] = bin2hex(random_bytes(32));
        }
        $token = $this->tokens[$formName];
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">'
             . '<input type="hidden" name="csrf_form_name" value="' . htmlspecialchars($formName) . '">';
    }

    /**
     * Validate the CSRF token for a specific form.
     *
     * @param string $formName The unique name of the form.
     * @param string $submittedToken The token submitted by the form.
     * @return bool True if the token is valid, false otherwise.
     */
    public function validateToken(string $formName, string $submittedToken): bool {
        if (!isset($this->tokens[$formName])) {
            return false;
        }
        return hash_equals($this->tokens[$formName], $submittedToken);
    }

    /**
     * Check and validate the CSRF token for the current request.
     *
     * @throws \Exception If the CSRF token validation fails.
     */
    public function checkToken(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedToken = $_POST['csrf_token'] ?? '';
            $formName = $_POST['csrf_form_name'] ?? '';

            // Handle missing or invalid session tokens
            if (!isset($this->tokens[$formName]) || empty($this->tokens[$formName])) {
                // Regenerate the token for the form
                $this->tokens[$formName] = bin2hex(random_bytes(32));

                $message = sprintf(
                    "[errorCode] => %d", 419
                );
                throw new \Exception($message);
            }

            // Validate the token
            if (!$formName || !$this->validateToken($formName, $submittedToken)) {
                $message = sprintf(
                    "[errorCode] => %d", 419
                );
                throw new \Exception($message);
            }

            // Regenerate the token after successful validation (optional for one-time use tokens)
            $this->tokens[$formName] = bin2hex(random_bytes(32));
        }
    }
}
?>