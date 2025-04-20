<?php
namespace Contingent;
class PasswordGenerator {
    private $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_';

    public function generatePassword($length = 12) {
        // Shuffle the characters
        $shuffledChars = str_shuffle($this->chars);

        // Get the length of the character set
        $charLength = strlen($shuffledChars);

        // Initialize the password variable
        $password = '';

        // Generate random password
        for ($i = 0; $i < $length; $i++) {
            $password .= $shuffledChars[mt_rand(0, $charLength - 1)];
        }

        return $password;
    }
}
