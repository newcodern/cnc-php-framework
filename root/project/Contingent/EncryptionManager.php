<?php
namespace Contingent;
class EncryptionManager {
    private $key;

    public function __construct() {
        $this->key = random_bytes(32); // Generate a 256-bit encryption key
    }

    public function encrypt($data) {
        $iv = random_bytes(16); // Initialization Vector
        $cipherText = openssl_encrypt($data, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $cipherText . $tag);
    }

    public function decrypt($encryptedData) {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $tag = substr($data, -16);
        $cipherText = substr($data, 16, -16);
        return openssl_decrypt($cipherText, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
    }

    public function getKey() {
        return base64_encode($this->key);
    }

    public function setKey($key) {
        $this->key = $key;
    }

}

?>
