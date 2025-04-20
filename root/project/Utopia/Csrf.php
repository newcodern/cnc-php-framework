<?php

class Csrf
{
    public static function generate()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validate($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function field()
    {
        return '<input type="hidden" name="csrf_token" value="' . self::generate() . '">';
    }

    public static function validateRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userToken = $_POST['csrf_token'];

            if (!self::validate($userToken)) {
                // CSRF token is not valid, handle accordingly (e.g., log, deny access)
                die("CSRF token validation failed!");
            }
        }
    }
}


