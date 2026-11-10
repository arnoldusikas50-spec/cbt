<?php

namespace App\Services;

use App\Models\DashboardModel;

/**
 * TelegramService — pengirim notifikasi ke satu grup/channel Telegram.
 *
 * Konfigurasi diambil dari tabel `setting`:
 *   telegram_bot_token : token bot dari @BotFather
 *   telegram_chat_id   : id grup/channel tujuan
 *
 * Prinsip:
 *  - Notifikasi TIDAK boleh menggagalkan alur bisnis. Semua error ditangkap,
 *    hanya dicatat ke log, dan pemanggil tetap Jalan.
 *  - Format HTML dipakai agar bisa link & tebal; bila Telegram menolak
 *    entitas HTML, pesan dikirim ulang sebagai teks biasa.
 */
class TelegramService
{
    /** @var string */
    protected $token = '';

    /** @var string */
    protected $chatId = '';

    /** Timeout (detik) — sengaja pendek supaya halaman tidak ikut menggantung. */
    protected int $connectTimeout = 5;

    protected int $timeout = 8;

    public function __construct(?object $setting = null)
    {
        if ($setting === null) {
            $setting = (new DashboardModel())->getSetting();
        }

        $this->token  = trim((string) ($setting->telegram_bot_token ?? ''));
        $this->chatId = trim((string) ($setting->telegram_chat_id ?? ''));
    }

    /**
     * True bila token + chat id sudah diisi.
     */
    public function isEnabled(): bool
    {
        return $this->token !== '' && $this->chatId !== '';
    }

    /**
     * Informasi bot (dipakai untuk tombol "Kirim Pesan Tes").
     *
     * @return array{ok: bool, message: string, data?: array}
     */
    public function getMe(): array
    {
        return $this->request('getMe');
    }

    /**
     * Kirim pesan ke grup tujuan.
     *
     * @param string $text Isi pesan (mendukung HTML dasar Telegram).
     * @param array  $opts Opsi tambahan, mis. ['disable_notification' => true].
     *
     * @return array{ok: bool, message: string}
     */
    public function send(string $text, array $opts = []): array
    {
        if (! $this->isEnabled()) {
            return ['ok' => false, 'message' => 'Integrasi Telegram belum dikonfigurasi (token/chat id kosong).'];
        }

        $payload = array_merge([
            'chat_id'    => $this->chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ], $opts);

        $res = $this->request('sendMessage', $payload);

        if (! $res['ok'] && str_contains(strtolower($res['message']), 'parse entities')) {
            // Format HTML tidak valid → kirim ulang sebagai teks biasa.
            $res = $this->request('sendMessage', array_merge($payload, [
                'text'       => html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5),
                'parse_mode' => '',
            ]));
        }

        return $res;
    }

    /**
     * Panggilan API Telegram. Selalu mengembalikan array, tidak pernah melempar error.
     */
    protected function request(string $method, array $payload = []): array
    {
        if ($this->token === '') {
            return ['ok' => false, 'message' => 'Bot token belum diisi.'];
        }

        $url = 'https://api.telegram.org/bot' . $this->token . '/' . $method;

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($payload),
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $body = curl_exec($ch);
            $err  = curl_error($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body === false) {
                $message = 'Gagal menghubungi Telegram: ' . ($err !== '' ? $err : 'timeout/error tidak diketahui');

                log_message('warning', '[Telegram] ' . $method . ': ' . $message);

                return ['ok' => false, 'message' => $message];
            }

            $json = json_decode((string) $body, true);
            $ok   = (bool) ($json['ok'] ?? false);
            $desc = (string) ($json['description'] ?? '');

            if ($ok) {
                $botName = (string) ($json['result']['username'] ?? '');

                return [
                    'ok'      => true,
                    'message' => 'Berhasil dikirim',
                    'data'    => ['bot' => $botName, 'chat_id' => $this->chatId],
                ];
            }

            $message = $desc !== '' ? $desc : 'Telegram menolak permintaan (HTTP ' . $code . ')';

            // Catatan: logger.threshold = 4 (production) hanya menyimpan level
            // ERROR ke file, jadi error Telegram tetap dicatat di level ERROR —
            // token salah memang hal yang harus ditindaklanjuti admin. Bedanya,
            // pesannya langsung memberi petunjuk ke halaman Pengaturan.
            log_message('error', '[Telegram] ' . $method . ': ' . $message . ' — periksa Bot Token & Chat ID di Pengaturan → Notifikasi.');

            return ['ok' => false, 'message' => $message];
        } catch (\Throwable $e) {
            log_message('error', '[Telegram] ' . $method . ' exception: ' . $e->getMessage());

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}