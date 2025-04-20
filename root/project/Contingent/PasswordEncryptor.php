<?php
namespace Contingent;

class PasswordEncryptor {
    public static function hash($password) {
        $cost = 12;

        if (function_exists('getrusage')) {
            $resourceUsage = getrusage();

            if ($resourceUsage !== false && isset($resourceUsage['ru_utime.tv_usec']) && isset($resourceUsage['ru_stime.tv_usec'])) {
                $cost = max(10, round(log(($resourceUsage['ru_utime.tv_usec'] + $resourceUsage['ru_stime.tv_usec']) / 100, 2)));
            } else {
                error_log("getrusage() did not return the expected array structure.");
            }
        }

        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}

