<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * File Integrity Helper
 * 
 * Helper functions for checking file integrity using checksums.
 */

if (!function_exists('get_file_md5')) {
    /**
     * Get MD5 hash of a file
     * 
     * @param string $file_path Path to file
     * @return string|false MD5 hash or false if file doesn't exist
     */
    function get_file_md5($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }
        return md5_file($file_path);
    }
}

if (!function_exists('get_file_sha256')) {
    /**
     * Get SHA256 hash of a file (more secure than MD5)
     * 
     * @param string $file_path Path to file
     * @return string|false SHA256 hash or false if file doesn't exist
     */
    function get_file_sha256($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }
        return hash_file('sha256', $file_path);
    }
}

if (!function_exists('verify_file_checksum')) {
    /**
     * Verify a file's checksum
     * 
     * @param string $file_path Path to file
     * @param string $expected_hash Expected hash value
     * @param string $algorithm Hash algorithm: 'md5' or 'sha256'
     * @return array ['valid' => bool, 'actual_hash' => string, 'expected_hash' => string]
     */
    function verify_file_checksum($file_path, $expected_hash, $algorithm = 'md5')
    {
        if (!file_exists($file_path)) {
            return [
                'valid' => false,
                'error' => 'File tidak ditemukan',
                'file_path' => $file_path
            ];
        }

        $actual_hash = ($algorithm === 'sha256')
            ? hash_file('sha256', $file_path)
            : md5_file($file_path);

        return [
            'valid' => ($actual_hash === $expected_hash),
            'actual_hash' => $actual_hash,
            'expected_hash' => $expected_hash,
            'algorithm' => $algorithm,
            'file_size' => filesize($file_path)
        ];
    }
}

if (!function_exists('get_media_files_with_checksums')) {
    /**
     * Get list of media files in a directory with their checksums
     * 
     * @param string $directory Directory path
     * @param array $extensions Allowed extensions (empty = all files)
     * @return array Array of file info with checksums
     */
    function get_media_files_with_checksums($directory, $extensions = [])
    {
        $files = [];
        $directory = rtrim($directory, '/\\') . '/';

        if (!is_dir($directory)) {
            return $files;
        }

        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full_path = $directory . $item;
            if (!is_file($full_path)) {
                continue;
            }

            // Check extension filter
            if (!empty($extensions)) {
                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                if (!in_array($ext, $extensions)) {
                    continue;
                }
            }

            $files[] = [
                'name' => $item,
                'path' => $full_path,
                'size' => filesize($full_path),
                'md5' => md5_file($full_path),
                'modified' => filemtime($full_path)
            ];
        }

        return $files;
    }
}

if (!function_exists('compare_file_lists')) {
    /**
     * Compare two file lists and find differences
     * 
     * @param array $local_files Local file list with checksums
     * @param array $remote_files Remote file list with checksums
     * @return array ['missing' => [], 'outdated' => [], 'extra' => [], 'synced' => []]
     */
    function compare_file_lists($local_files, $remote_files)
    {
        $result = [
            'missing' => [],    // Files on remote but not on local
            'outdated' => [],   // Files that exist on both but have different checksums
            'extra' => [],      // Files on local but not on remote
            'synced' => []      // Files that are identical
        ];

        // Index local files by name
        $local_index = [];
        foreach ($local_files as $file) {
            $local_index[$file['name']] = $file;
        }

        // Index remote files by name
        $remote_index = [];
        foreach ($remote_files as $file) {
            $remote_index[$file['name']] = $file;
        }

        // Check remote files against local
        foreach ($remote_index as $name => $remote_file) {
            if (!isset($local_index[$name])) {
                // File missing locally
                $result['missing'][] = $remote_file;
            } else {
                // File exists - compare checksums
                $local_file = $local_index[$name];
                if ($local_file['md5'] !== $remote_file['md5']) {
                    $result['outdated'][] = [
                        'name' => $name,
                        'local_md5' => $local_file['md5'],
                        'remote_md5' => $remote_file['md5'],
                        'local_size' => $local_file['size'],
                        'remote_size' => $remote_file['size']
                    ];
                } else {
                    $result['synced'][] = $name;
                }
            }
        }

        // Check for extra local files
        foreach ($local_index as $name => $local_file) {
            if (!isset($remote_index[$name])) {
                $result['extra'][] = $local_file;
            }
        }

        return $result;
    }
}

if (!function_exists('calculate_directory_checksum')) {
    /**
     * Calculate a combined checksum for an entire directory
     * Useful for quick comparison of large media folders
     * 
     * @param string $directory Directory path
     * @return string Combined MD5 hash of all files
     */
    function calculate_directory_checksum($directory)
    {
        $files = get_media_files_with_checksums($directory);

        // Sort by filename for consistent ordering
        usort($files, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        // Combine all checksums
        $combined = '';
        foreach ($files as $file) {
            $combined .= $file['name'] . ':' . $file['md5'] . ';';
        }

        return md5($combined);
    }
}
