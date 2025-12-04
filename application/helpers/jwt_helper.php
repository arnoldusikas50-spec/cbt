<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('jwt_encode_param')) {
    /**
     * Encode sensitive parameter to JWT token
     * @param string $param_name
     * @param mixed $param_value
     * @return string
     */
    function jwt_encode_param($param_name, $param_value) {
        $CI =& get_instance();
        $CI->load->library('jwt_lib');
        return $CI->jwt_lib->create_param_token($param_name, $param_value);
    }
}

if (!function_exists('jwt_decode_param')) {
    /**
     * Decode JWT token to get parameter value
     * @param string $token
     * @param string $param_name
     * @return mixed|null
     */
    function jwt_decode_param($token, $param_name) {
        $CI =& get_instance();
        $CI->load->library('jwt_lib');
        return $CI->jwt_lib->extract_param($token, $param_name);
    }
}

if (!function_exists('jwt_encode_data')) {
    /**
     * Encode array data to JWT token
     * @param array $data
     * @return string
     */
    function jwt_encode_data($data) {
        $CI =& get_instance();
        $CI->load->library('jwt_lib');
        return $CI->jwt_lib->encode($data);
    }
}

if (!function_exists('jwt_decode_data')) {
    /**
     * Decode JWT token to get data
     * @param string $token
     * @return array|null
     */
    function jwt_decode_data($token) {
        $CI =& get_instance();
        $CI->load->library('jwt_lib');
        return $CI->jwt_lib->get_data($token);
    }
}

if (!function_exists('jwt_validate_token')) {
    /**
     * Validate JWT token
     * @param string $token
     * @return bool
     */
    function jwt_validate_token($token) {
        $CI =& get_instance();
        $CI->load->library('jwt_lib');
        return $CI->jwt_lib->validate($token);
    }
}
