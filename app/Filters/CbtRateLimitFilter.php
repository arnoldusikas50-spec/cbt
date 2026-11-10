<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * CbtRateLimitFilter
 *
 * Rate limiting khusus endpoint CBT siswa yang hit sering:
 *   - saveJawaban   : maks 60 req/mnt per siswa  (1 per detik cukup untuk autosave)
 *   - toggleRagu    : maks 30 req/mnt per siswa
 *   - getSoal       : maks 120 req/mnt per siswa (navigasi cepat tetap OK)
 *
 * Menggunakan file cache (bisa diganti Redis dengan mengubah $cache driver).
 * Key: cbt_rl_{user_id}_{endpoint_slug}_{window_minute}
 *
 * Cara daftar di Routes.php:
 *   $routes->post('cbt/save_jawaban', 'Frontend\Cbt::saveJawaban', ['filter' => 'cbt_ratelimit:save_jawaban,60']);
 *   $routes->post('cbt/toggle_ragu',  'Frontend\Cbt::toggleRagu',  ['filter' => 'cbt_ratelimit:toggle_ragu,30']);
 *   $routes->post('cbt/get_soal',     'Frontend\Cbt::getSoal',     ['filter' => 'cbt_ratelimit:get_soal,120']);
 */
class CbtRateLimitFilter implements FilterInterface
{
    /**
     * Default limits per endpoint slug (req/mnt)
     */
    private const LIMITS = [
        'save_jawaban' => 60,
        'toggle_ragu'  => 30,
        'get_soal'     => 120,
        'default'      => 120,
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        // Hanya berlaku untuk IncomingRequest (HTTP)
        if (!$request instanceof IncomingRequest) {
            return;
        }

        $session = service('session');
        $userId  = $session->get('user_id');

        // Jika tidak login, biarkan AuthFilter yang menangani
        if (!$userId) {
            return;
        }

        // Parse argumen: ['save_jawaban', '60'] atau ['get_soal']
        $slug  = $arguments[0] ?? 'default';
        $limit = isset($arguments[1]) ? (int) $arguments[1] : (self::LIMITS[$slug] ?? self::LIMITS['default']);

        $cache      = service('cache');
        $windowMin  = (int) floor(time() / 60); // window 1 menit
        $cacheKey   = "cbt_rl_{$userId}_{$slug}_{$windowMin}";

        $hits = (int) ($cache->get($cacheKey) ?? 0);

        if ($hits >= $limit) {
            // Rate limit terlampaui — kembalikan JSON 429
            $retryAfter = 60 - (time() % 60);
            return service('response')
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string) $retryAfter)
                ->setHeader('X-RateLimit-Limit', (string) $limit)
                ->setHeader('X-RateLimit-Remaining', '0')
                ->setJSON([
                    'status'      => false,
                    'error'       => 'rate_limited',
                    'message'     => "Terlalu banyak permintaan. Tunggu {$retryAfter} detik.",
                    'retry_after' => $retryAfter,
                ]);
        }

        // Increment counter, TTL sedikit lebih dari 1 menit agar tidak cut off di batas window
        $cache->save($cacheKey, $hits + 1, 65);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
