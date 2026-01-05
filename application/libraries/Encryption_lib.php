<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Encryption_lib {

    protected $ci;
    protected $key;

    public function __construct()
    {
        $this->ci =& get_instance();
        // Menggunakan key dari config
        $this->key = hash('sha256', $this->ci->config->item('encryption_key'), true);
    }

    public function encrypt($data)
    {
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->key, 0, $iv);
        
        // Gabungkan IV dengan data terenkripsi, lalu encode dengan base64_url_safe
        return $this->base64_url_encode($iv . $encrypted);
    }

    public function decrypt($data)
    {
        $data = $this->base64_url_decode($data);
        
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        
        if (strlen($data) < $iv_length) {
            return false;
        }
        
        // Ambil IV dari data
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->key, 0, $iv);
    }

    private function base64_url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64_url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
