<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Jwt_lib {
    
    private $secret_key;
    private $algorithm = 'HS256';
    private $expire_time = 3600; // 1 hour
    
    public function __construct() {
        $ci =& get_instance();
        $this->secret_key = $ci->config->item('jwt_secret_key') ?: 'IkasmediaCBTdisdikNTT';
        $this->expire_time = $ci->config->item('jwt_expire_time') ?: 3600;
    }
    
    /**
     * Simple JWT-like encoding for parameter hiding
     * @param array $data
     * @return string
     */
    public function encode($data) {
        $payload = [
            'iat' => time(),
            'exp' => time() + $this->expire_time,
            'data' => $data
        ];
        
        $json_payload = json_encode($payload);
        $signature = hash_hmac('sha256', $json_payload, $this->secret_key);
        
        return base64_encode($json_payload . '.' . $signature);
    }
    
    /**
     * Decode JWT-like token
     * @param string $token
     * @return object|null
     */
    public function decode($token) {
        try {
            if (empty($token)) {
                log_message('error', 'JWT Decode Error: Empty token provided');
                return null;
            }
            
            $decoded = base64_decode($token);
            if ($decoded === false) {
                log_message('error', 'JWT Decode Error: Base64 decode failed');
                return null;
            }
            
            if (strpos($decoded, '.') === false) {
                log_message('error', 'JWT Decode Error: Invalid token format, missing separator');
                return null;
            }
            
            list($json_payload, $signature) = explode('.', $decoded, 2);
            
            if (empty($json_payload) || empty($signature)) {
                log_message('error', 'JWT Decode Error: Invalid token format, missing payload or signature');
                return null;
            }
            
            // Verify signature
            $expected_signature = hash_hmac('sha256', $json_payload, $this->secret_key);
            if (!hash_equals($signature, $expected_signature)) {
                log_message('error', 'JWT Decode Error: Signature verification failed');
                return null;
            }
            
            $payload = json_decode($json_payload);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JWT Decode Error: JSON decode failed: ' . json_last_error_msg());
                return null;
            }
            
            if (!isset($payload->exp) || !isset($payload->data)) {
                log_message('error', 'JWT Decode Error: Missing required payload fields');
                return null;
            }
            
            // Check expiration
            if (time() > $payload->exp) {
                log_message('error', 'JWT Decode Error: Token expired');
                return null;
            }
            
            return $payload;
        } catch (Exception $e) {
            log_message('error', 'JWT Decode Error: ' . $e->getMessage());
            return null;
        } catch (Error $e) {
            log_message('error', 'JWT Decode System Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate JWT token
     * @param string $token
     * @return bool
     */
    public function validate($token) {
        $decoded = $this->decode($token);
        return $decoded !== null;
    }
    
    /**
     * Get data from JWT token
     * @param string $token
     * @return array|null
     */
    public function get_data($token) {
        $decoded = $this->decode($token);
        return $decoded ? (array) $decoded->data : null;
    }
    
    /**
     * Create token for sensitive parameters
     * @param string $param_name
     * @param mixed $param_value
     * @return string
     */
    public function create_param_token($param_name, $param_value) {
        $data = [
            $param_name => $param_value,
            'timestamp' => time()
        ];
        return $this->encode($data);
    }
    
    /**
     * Extract parameter from token
     * @param string $token
     * @param string $param_name
     * @return mixed|null
     */
    public function extract_param($token, $param_name) {
        try {
            if (empty($param_name)) {
                log_message('error', 'JWT Extract Param Error: Empty parameter name');
                return null;
            }
            
            $data = $this->get_data($token);
            if ($data === null) {
                log_message('error', 'JWT Extract Param Error: Failed to get data from token');
                return null;
            }
            
            if (!is_array($data)) {
                log_message('error', 'JWT Extract Param Error: Data is not an array');
                return null;
            }
            
            if (!isset($data[$param_name])) {
                log_message('error', "JWT Extract Param Error: Parameter '{$param_name}' not found in token data");
                return null;
            }
            
            return $data[$param_name];
        } catch (Exception $e) {
            log_message('error', 'JWT Extract Param Error: ' . $e->getMessage());
            return null;
        } catch (Error $e) {
            log_message('error', 'JWT Extract Param System Error: ' . $e->getMessage());
            return null;
        }
    }
}
