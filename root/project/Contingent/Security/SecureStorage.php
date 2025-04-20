<?php

namespace Contingent\Security;

class SecureStorage {
    private static $keys = [
        'user' => 'tohsaka_rin',
        'post' => 'rena_ryuugu',
        'general' => 'yuno_gasai',
    ];
    
    public static function encryptData(string $type, string $data, int $level): string {
        if (!isset(self::$keys[$type])) {
            throw new Exception("Key untuk tipe data '$type' tidak ditemukan!");
        }
        $key = self::$keys[$type];

        if ($level >= 3) {
            $fingerprint = hash_hmac('sha256', $data, $key);
        } else {
            $fingerprint = null;
        }

        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, substr($key, 0, 16));

        return json_encode([
            'level' => $level,
            'fingerprint' => $fingerprint,
            'encrypted' => $encrypted,
        ]);
    }

    public static function decryptData(string $type, string $json): string {
        if (!isset(self::$keys[$type])) {
            throw new Exception("Key untuk tipe data '$type' tidak ditemukan!");
        }
        $key = self::$keys[$type];
        $data = json_decode($json, true);

        return openssl_decrypt($data['encrypted'], 'aes-256-cbc', $key, 0, substr($key, 0, 16));
    }

    public static function matchEncrypted(string $type, string $value, string $json): bool {
        $data = json_decode($json, true);
        if ($data['level'] < 3) {
            return false; // Level < 3 gak pakai HMAC
        }

        if (!isset(self::$keys[$type])) {
            throw new Exception("Key untuk tipe data '$type' tidak ditemukan!");
        }
        $key = self::$keys[$type];

        $fingerprint = hash_hmac('sha256', $value, $key);
        return $fingerprint === $data['fingerprint'];
    }
}

