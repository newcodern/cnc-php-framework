<?php
namespace Contingent;
class SessionManager {
    private static $instance;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function delete($key) {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }

    public function clear() {
        session_unset();
    }

    public function destroy() {
        session_destroy();
    }

    public function regenerate($deleteOldSession = true) {
        session_regenerate_id($deleteOldSession);
    }
}

?>
