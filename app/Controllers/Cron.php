<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class Cron extends BaseController
{
    protected $dashboardModel;
    protected $setting;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->setting = $this->dashboardModel->getSetting();
    }

    /**
     * Auto-close sesi ujian yang expired via HTTP (untuk shared hosting).
     *
     * Panggil dari crontab server/cpanel:
     *   * /5 * * * * curl -s "https://domain.com/cron/auto_close_expired?key=SECRET_KEY" > /dev/null
     *
     * Kunci keamanan diambil dari .env: CRON_SECRET_KEY
     */
    public function auto_close_expired()
    {
        // Validasi secret key — mencegah akses publik sembarangan
        $key       = $this->request->getGet('key') ?? '';
        $secretKey = env('CRON_SECRET_KEY', '');

        if (empty($secretKey) || $key !== $secretKey) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $toleransi = (int) ($this->request->getGet('toleransi') ?? 120);
        $db        = \Config\Database::connect();
        $now       = date('Y-m-d H:i:s');
        $nowTs     = time();

        $sesiAktif = $db->query(
            "SELECT cs.id_cbt_siswa, cs.id_siswa, cs.id_jadwal, cs.mulai, cs.reset_waktu, j.durasi_ujian
             FROM cbt_siswa cs
             JOIN cbt_jadwal j ON j.id_jadwal = cs.id_jadwal
             WHERE cs.mulai IS NOT NULL AND cs.selesai IS NULL AND cs.status != '9'
             ORDER BY cs.mulai ASC"
        )->getResult();

        $ditutup = 0;
        $service = new \App\Services\CbtService();

        foreach ($sesiAktif as $sesi) {
            $batasDetik   = ((int) $sesi->durasi_ujian * 60) + ((int) ($sesi->reset_waktu ?? 0) * 60) + $toleransi;
            $elapsedDetik = $nowTs - strtotime($sesi->mulai);

            if ($elapsedDetik <= $batasDetik) {
                continue;
            }

            $lamaUjian = gmdate('H:i:s', min($elapsedDetik, (int) $sesi->durasi_ujian * 60));

            $db->table('cbt_siswa')
                ->where('id_cbt_siswa', $sesi->id_cbt_siswa)
                ->update(['selesai' => $now, 'lama_ujian' => $lamaUjian, 'status' => '2']);

            // Hitung nilai (sync — HTTP request tidak bisa spawn child process)
            try {
                $service->calculateScore((int) $sesi->id_siswa, (int) $sesi->id_jadwal);
            } catch (\Throwable $e) {
                log_message('error', "[Cron::auto_close] hitung nilai gagal siswa={$sesi->id_siswa}: " . $e->getMessage());
            }

            $ditutup++;
        }

        log_message('info', "[Cron::auto_close_expired] ditutup={$ditutup}, total_aktif=" . count($sesiAktif));

        return $this->response->setJSON([
            'status'        => true,
            'ditutup'       => $ditutup,
            'total_diperiksa' => count($sesiAktif),
            'waktu'         => $now,
        ]);
    }

    /**
     * Sinkronisasi Dapodik otomatis via HTTP (untuk shared hosting tanpa crontab).
     *
     * Panggil dari cron server/cpanel:
     *   curl -s "https://domain.com/cron/dapodik_sync?key=SECRET_KEY" > /dev/null
     *
     * Hanya berjalan jika 'Auto Sync' aktif di pengaturan Integrasi Dapodik.
     */
    public function dapodik_sync()
    {
        // Validasi secret key — mencegah akses publik sembarangan
        $key       = $this->request->getGet('key') ?? '';
        $secretKey = env('CRON_SECRET_KEY', '');

        if (empty($secretKey) || $key !== $secretKey) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $db = \Config\Database::connect();
        $autoSync = 0;
        if ($db->tableExists('dapodik_settings')) {
            $autoSync = (int) ($db->table('dapodik_settings')->get()->getRowArray()['auto_sync'] ?? 0);
        }

        if (!$autoSync) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Auto-sync Dapodik nonaktif.',
            ]);
        }

        try {
            $client  = new \App\Libraries\DapodikClient();
            $service = new \App\Services\DapodikSyncService();
            $result  = $service->syncAllFromApi($client, null);

            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', '[Cron::dapodik_sync] Gagal: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Proses antrian scoring CBT via HTTP (untuk shared hosting tanpa crontab CLI).
     *
     * Panggil dari cron server/cpanel/panel:
     *   * * * * * curl -s "https://domain.com/cron/scoring_queue?key=SECRET_KEY" > /dev/null
     *
     * Memproses batch kecil (default 50) per pemanggilan. Bisa menambah
     * parameter &limit=N untuk lonjakan.
     *
     * Kunci keamanan diambil dari .env: CRON_SECRET_KEY
     */
    public function scoring_queue()
    {
        // Validasi secret key — mencegah akses publik sembarangan
        $key       = $this->request->getGet('key') ?? '';
        $secretKey = env('CRON_SECRET_KEY', '');

        if (empty($secretKey) || $key !== $secretKey) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $limit       = (int) ($this->request->getGet('limit') ?? 50);
        $maxAttempts = (int) ($this->request->getGet('max_attempts') ?? 3);

        try {
            $service = new \App\Services\CbtService();
            $result  = $service->processScoringQueue($limit, $maxAttempts);

            return $this->response->setJSON([
                'status'  => true,
                'ok'      => $result['ok'],
                'failed'  => $result['failed'],
                'skipped' => $result['skipped'],
                'claimed' => $result['claimed'],
                'waktu'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Cron::scoring_queue] Gagal: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Daily Telegram/WA Report
     *
     * Mengirim ringkasan Laporan Harian ke grup Telegram.
     *
     * curl -s "https://domain.com/cron/daily_report?key=SECRET_KEY" > /dev/null
     * curl -s "https://domain.com/cron/daily_report?key=SECRET_KEY&tanggal=2026-10-20" > /dev/null
     */
    public function daily_report()
    {
        $key       = $this->request->getGet('key') ?? '';
        $secretKey = env('CRON_SECRET_KEY', '');

        if (empty($secretKey) || $key !== $secretKey) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        $tanggal = trim((string) $this->request->getGet('tanggal'));
        if ($tanggal === '') {
            $tanggal = date('Y-m-d');
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => false,
                'message' => 'Format tanggal harus YYYY-MM-DD',
            ]);
        }

        try {
            $laporan = new \App\Controllers\Laporan();
            $data = $laporan->buildLaporanHarian($tanggal);

            $telegram = new \App\Services\TelegramService();

            if (! $telegram->isEnabled()) {
                return $this->response->setJSON([
                    'status'    => false,
                    'message'   => 'Integrasi Telegram belum dikonfigurasi (token/chat id kosong), laporan tidak dikirim.',
                    'tanggal'   => $tanggal,
                    'ringkasan' => $data['ringkasan'] ?? [],
                ]);
            }

            $res = $telegram->send($this->formatLaporanHarian($data));

            return $this->response->setJSON([
                'status'    => (bool) ($res['ok'] ?? false),
                'message'   => $res['message'] ?? 'Gagal mengirim laporan',
                'tanggal'   => $tanggal,
                'ringkasan' => $data['ringkasan'] ?? [],
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Cron::daily_report] Gagal: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Format ringkasan Laporan Harian menjadi pesan Telegram (HTML).
     */
    protected function formatLaporanHarian(array $data): string
    {
        $r = $data['ringkasan'] ?? [];
        $lines = [];

        $lines[] = '<b>Laporan Harian Ujian</b>';
        $lines[] = 'Tanggal: ' . ($data['tanggal'] ?? date('Y-m-d'));
        if (! empty($data['jenis_ujian'])) {
            $lines[] = 'Jenis Ujian: ' . $data['jenis_ujian'];
        }
        $lines[] = '';
        $lines[] = 'Total sesi: ' . (int) ($r['jml_sesi'] ?? 0)
            . ' (terisi: ' . (int) ($r['sesi_terisi'] ?? 0)
            . ', kosong: ' . (int) ($r['sesi_kosong'] ?? 0) . ')';
        $lines[] = 'Peserta hadir: ' . (int) ($r['total_hadir'] ?? 0)
            . ' / ' . (int) ($r['total_peserta'] ?? 0)
            . ' (' . ($r['persentase_hadir'] ?? 0) . '%)';
        $lines[] = 'Tidak hadir: ' . (int) ($r['total_tidak_hadir'] ?? 0);
        $lines[] = 'Jam ujian: ' . ($r['jam_mulai'] ?? '-') . ' - ' . ($r['jam_selesai'] ?? '-');
        $lines[] = 'Kondisi: ' . ($r['kondisi'] ?? '-');
        $lines[] = 'Catatan: ' . ($r['catatan'] ?? '-');

        if (! empty($r['ruang'])) {
            $lines[] = '';
            $lines[] = 'Ruang: ' . (is_array($r['ruang']) ? implode(', ', $r['ruang']) : (string) $r['ruang']);
        }

        return implode("\n", $lines);
    }

    /**
     * Auto-close ujian praktik yang melewati tanggal_selesai via HTTP.
     *
     * curl -s "https://domain.com/cron/praktik_close_expired?key=SECRET_KEY" > /dev/null
     */
    public function praktik_close_expired()
    {
        $key       = $this->request->getGet('key') ?? '';
        $secretKey = env('CRON_SECRET_KEY', '');

        if (empty($secretKey) || $key !== $secretKey) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Forbidden']);
        }

        try {
            $model = new \App\Models\CbtPraktikModel();
            $changed = $model->updateStatusByTime();

            return $this->response->setJSON([
                'status'  => true,
                'changed' => $changed,
                'waktu'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Cron::praktik_close_expired] Gagal: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }
}

