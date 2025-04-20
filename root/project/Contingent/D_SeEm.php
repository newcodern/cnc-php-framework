<?php
namespace Contingent;

class D_SeEm
{
    public static function set($key, $message, $type = 'info', $redirectUrl = null, $expireInSeconds = 0.5)
    {
        // Store the flash message and type in session
        $_SESSION['flash_messages'][$key] = [
            'message' => $message,
            'type' => $type,
            'expire_at' => microtime(true) + $expireInSeconds
        ];

        // Redirect logic as before
        if ($redirectUrl) {
            header("Location: $redirectUrl");
            exit;
        } else {
            $redirectUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            if ($redirectUrl) {
                header("Location: $redirectUrl");
            }
            exit;
        }
    }

    public static function get($key)
    {
        if (
            isset($_SESSION['flash_messages'][$key]) &&
            is_array($_SESSION['flash_messages'][$key]) &&
            microtime(true) < $_SESSION['flash_messages'][$key]['expire_at']
        ) {
            $messageData = $_SESSION['flash_messages'][$key];
            unset($_SESSION['flash_messages'][$key]);
            return $messageData;
        }

        return null;
    }

    public static function has($key)
    {
        return (
            isset($_SESSION['flash_messages'][$key]) &&
            is_array($_SESSION['flash_messages'][$key]) &&
            microtime(true) < $_SESSION['flash_messages'][$key]['expire_at']
        );
    }
}
