<?php

namespace App\Services;

/**
 * MaintenanceService - manages the application-wide maintenance mode.
 *
 * Status is stored on the `setting` table (single-row) and cached briefly so
 * every request does not hit the database. The cache is dropped whenever the
 * settings are saved.
 */
class MaintenanceService
{
    /**
     * Cache TTL in seconds. Kept short so a maintenance toggle propagates fast
     * even when the cache could not be flushed (e.g. redis down).
     */
    public const CACHE_TTL = 60;

    protected const CACHE_KEY = 'maintenance_status';

    protected const DEFAULT_MESSAGE = 'Aplikasi sedang dalam pemeliharaan (maintenance). Mohon tunggu hingga maintenance selesai.';

    /**
     * Roles that keep full access while maintenance mode is active.
     */
    protected const ALLOWED_ROLES = ['1', '2', '4']; // admin, guru, panitia

    /**
     * Read the raw maintenance settings (cached).
     */
    public function getStatus(): object
    {
        $cached = cache()->get(self::CACHE_KEY);

        if ($cached !== null) {
            return (object) $cached;
        }

        try {
            $row = \Config\Database::connect()
                ->table('setting')
                ->select('maintenance_aktif, maintenance_pesan, maintenance_sampai')
                ->get()
                ->getRow();
        } catch (\Throwable $e) {
            // Database tidak bisa dibaca: Never lock everyone out of the app.
            log_message('error', 'MaintenanceService: gagal membaca status maintenance - ' . $e->getMessage());

            return (object) ['aktif' => 0, 'pesan' => self::DEFAULT_MESSAGE, 'sampai' => null, 'expired' => true];
        }

        $status = [
            'aktif'   => (int) ($row->maintenance_aktif ?? 0),
            'pesan'   => (string) ($row->maintenance_pesan ?? ''),
            'sampai'  => $row->maintenance_sampai ?? null,
            'expired' => false,
        ];

        cache()->save(self::CACHE_KEY, $status, self::CACHE_TTL);

        return (object) $status;
    }

    /**
     * Is maintenance mode currently blocking the site?
     */
    public function isActive(): bool
    {
        $status = $this->getStatus();

        if ((int) $status->aktif !== 1) {
            return false;
        }

        return ! $this->isExpired($status);
    }

    /**
     * Maintenance message shown to blocked users.
     */
    public function getMessage(): string
    {
        $status = $this->getStatus();
        $message = trim((string) $status->pesan);

        return $message !== '' ? $message : self::DEFAULT_MESSAGE;
    }

    /**
     * Scheduled end time as stored in the database (Y-m-d H:i:s), or null when
     * maintenance has no end time.
     */
    public function getEndTime(): ?string
    {
        $status = $this->getStatus();
        $sampai = trim((string) ($status->sampai ?? ''));

        if ($sampai === '' || str_starts_with($sampai, '0000-00-00')) {
            return null;
        }

        $time = strtotime($sampai);

        return $time === false ? null : date('Y-m-d H:i:s', $time);
    }

    /**
     * Scheduled end time formatted for display. Null when there is none.
     */
    public function getEndTimeDisplay(): ?string
    {
        $sampai = $this->getEndTime();

        return $sampai === null ? null : date('d/m/Y H:i', strtotime($sampai));
    }

    /**
     * Has the scheduled maintenance window already passed?
     */
    public function isExpired(?object $status = null): bool
    {
        $status = $status ?? $this->getStatus();
        $sampai = trim((string) ($status->sampai ?? ''));

        if ($sampai === '' || str_starts_with($sampai, '0000-00-00')) {
            return false;
        }

        $time = strtotime($sampai);

        return $time !== false && $time < time();
    }

    /**
     * May the given role bypass maintenance mode?
     */
    public function roleCanBypass(?string $roleId, ?string $roleName): bool
    {
        if ($roleId === null && $roleName === null) {
            return false;
        }

        if ($roleId !== null && in_array((string) $roleId, self::ALLOWED_ROLES, true)) {
            return true;
        }

        return in_array(strtolower((string) $roleName), ['admin', 'guru', 'panitia'], true);
    }

    /**
     * Drop the cached status so the next request sees the latest value.
     */
    public function clearCache(): void
    {
        cache()->delete(self::CACHE_KEY);
    }
}