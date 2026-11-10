<?php

/**
 * Password Helper
 *
 * Util untuk hashing password yang idempoten.
 * Sumber password bisa berupa plaintext (import Excel / input manual)
 * atau sudah berupa bcrypt hash (sync Dapodik menulis hash dari username).
 * Jangan pernah meng-hash ulang hash yang sudah valid.
 */

if (!function_exists('hashPasswordSafe')) {
    /**
     * Hash password hanya jika belum merupakan hash yang valid.
     *
     * @param string|null $password
     * @return string
     */
    function hashPasswordSafe($password = null): string
    {
        $password = (string) $password;

        if ($password !== '' && password_get_info($password)['algo'] !== null) {
            return $password;
        }

        return password_hash($password, PASSWORD_BCRYPT);
    }
}
