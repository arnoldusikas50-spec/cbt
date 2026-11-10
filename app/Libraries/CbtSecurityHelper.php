<?php

namespace App\Libraries;

/**
 * Security Helper untuk CBT System
 * Menyediakan fungsi-fungsi keamanan untuk output escaping,
 * input validation, dan audit trail.
 */
class CbtSecurityHelper
{
    /**
     * Escape output untuk mencegah XSS
     * Gunakan untuk semua output user-generated content
     * 
     * @param string|array $data Data yang akan di-escape
     * @param string $context Context escaping (html, html_attr, js, css, url)
     * @return string|array
     */
    public static function esc($data, string $context = 'html')
    {
        if (is_array($data)) {
            return array_map(function($item) use ($context) {
                return self::esc($item, $context);
            }, $data);
        }
        
        if (!is_string($data)) {
            return $data;
        }
        
        switch ($context) {
            case 'html':
                return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            case 'html_attr':
                return htmlspecialchars($data, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
            case 'js':
                return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            case 'css':
                return preg_replace('/[^a-zA-Z0-9\-\.#]/', '', $data);
            case 'url':
                return rawurlencode($data);
            default:
                return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }
    
    /**
     * Escape untuk output di JavaScript context
     * 
     * @param mixed $data
     * @return string
     */
    public static function escJs($data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Sanitize input nilai - pastikan dalam range 0-100
     * 
     * @param mixed $nilai
     * @param float $min
     * @param float $max
     * @return float|null Null jika invalid
     */
    public static function sanitizeNilai($nilai, float $min = 0, float $max = 100): ?float
    {
        if (!is_numeric($nilai)) {
            return null;
        }
        
        $nilai = floatval($nilai);
        
        if ($nilai < $min || $nilai > $max) {
            return null;
        }
        
        return round($nilai, 2);
    }
    
    /**
     * Generate CSRF token untuk AJAX requests
     * 
     * @return array
     */
    public static function getCsrfData(): array
    {
        $security = service('security');
        return [
            'name' => $security->getTokenName(),
            'value' => $security->getHash()
        ];
    }
    
    /**
     * Generate session fingerprint untuk pencegahan session hijacking
     * 
     * @return string
     */
    public static function generateFingerprint(): string
    {
        $request = service('request');
        $userAgent = $request->getUserAgent()->getAgentString();
        $ip = $request->getIPAddress();
        
        return hash('sha256', $userAgent . $ip . getenv('CI_ENCRYPTION_KEY'));
    }
    
    /**
     * Validate session fingerprint
     * 
     * @param string $storedFingerprint
     * @return bool
     */
    public static function validateFingerprint(string $storedFingerprint): bool
    {
        return hash_equals($storedFingerprint, self::generateFingerprint());
    }
    
    /**
     * Sanitize array input - rekursif
     * 
     * @param array $data
     * @return array
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $safeKey = self::esc($key, 'html');
            if (is_array($value)) {
                $sanitized[$safeKey] = self::sanitizeArray($value);
            } else {
                $sanitized[$safeKey] = self::esc($value, 'html');
            }
        }
        return $sanitized;
    }
    
    /**
     * Encrypt sensitive data untuk penyimpanan
     * 
     * @param string $data
     * @return string
     */
    public static function encrypt(string $data): string
    {
        $encrypter = service('encrypter');
        return base64_encode($encrypter->encrypt($data));
    }
    
    /**
     * Decrypt sensitive data
     * 
     * @param string $encrypted
     * @return string|false
     */
    public static function decrypt(string $encrypted)
    {
        try {
            $encrypter = service('encrypter');
            return $encrypter->decrypt(base64_decode($encrypted));
        } catch (\Exception $e) {
            log_message('error', '[CbtSecurityHelper] Decrypt failed: ' . $e->getMessage());
            return false;
        }
    }
}
