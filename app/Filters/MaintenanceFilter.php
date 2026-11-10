<?php

namespace App\Filters;

use App\Services\MaintenanceService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * MaintenanceFilter - blocks the whole application while maintenance mode is on.
 *
 * Admin, guru and panitia keep their normal access so they can still use the
 * app (and switch maintenance off). Everyone else - including guests and
 * students - gets the maintenance page.
 *
 * Registered as a global "before" filter in Config\Filters.
 */
class MaintenanceFilter implements FilterInterface
{
    /**
     * URI prefixes (relative to the index page) that stay reachable during
     * maintenance: the login portal, the maintenance page itself, cron jobs,
     * the installer and the updater.
     *
     * @var list<string>
     */
    protected array $exceptRoutes = [
        'login',
        'auth',
        'admin/login',
        'admin/logout',
        'maintenance',
        'cron',
        'install',
        'update',
        'dbmigrate',
        'assets',
    ];

    /**
     * URI prefixes that answer with JSON instead of the HTML maintenance page.
     *
     * @var list<string>
     */
    protected array $jsonRoutes = [
        'api',
        'cbtpraktik',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $uri = trim($request->getUri()->getPath(), '/');
        $uri = preg_replace('#^index\.php/#', '', $uri) ?? $uri;

        if ($this->isExcepted($uri)) {
            return $request;
        }

        $service = new MaintenanceService();

        if (! $service->isActive()) {
            return $request;
        }

        $session = service('session');

        if ($service->roleCanBypass($session->get('role'), $session->get('role_name'))) {
            return $request;
        }

        $message = $service->getMessage();

        if ($this->wantsJson($request, $uri)) {
            return service('response')
                ->setStatusCode(503)
                ->setHeader('Retry-After', '3600')
                ->setJSON([
                    'status'  => false,
                    'message' => $message,
                    'errors'  => null,
                    'data'    => null,
                    'maintenance' => true,
                ]);
        }

        $setting = $this->getAppSetting();

        // Sesi sengaja tidak dihancurkan: begitu maintenance selesai, siswa
        // bisa langsung melanjutkan tanpa login ulang.
        return response()
            ->setStatusCode(503)
            ->setHeader('Retry-After', '3600')
            ->setBody(view('maintenance/index', [
                'judul'            => 'Maintenance',
                'message'          => $message,
                'maintenance_at'   => $service->getEndTimeDisplay(),
                'maintenance_until' => $service->getEndTime(),
                'app_name'         => $setting['nama_aplikasi'],
                'sekolah'          => $setting['sekolah'],
                'logo_kiri'        => $setting['logo_kiri'],
            ]));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    protected function isExcepted(string $uri): bool
    {
        foreach ($this->exceptRoutes as $except) {
            if ($uri === $except || str_starts_with($uri, $except . '/')) {
                return true;
            }
        }

        return false;
    }

    protected function wantsJson(RequestInterface $request, string $uri): bool
    {
        foreach ($this->jsonRoutes as $prefix) {
            if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                return true;
            }
        }

        return $request instanceof IncomingRequest && $request->isAJAX();
    }

    /**
     * Identitas aplikasi untuk ditampilkan pada halaman maintenance.
     * Gagal membaca database tidak boleh mematikan halaman, jadi ada default.
     */
    protected function getAppSetting(): array
    {
        $fallback = [
            'nama_aplikasi' => 'Aplikasi CBT',
            'sekolah'       => '',
            'logo_kiri'     => null,
        ];

        try {
            $row = \Config\Database::connect()
                ->table('setting')
                ->select('nama_aplikasi, sekolah, logo_kiri')
                ->get()
                ->getRow();
        } catch (\Throwable $e) {
            return $fallback;
        }

        if (! $row) {
            return $fallback;
        }

        return [
            'nama_aplikasi' => (string) ($row->nama_aplikasi ?: $fallback['nama_aplikasi']),
            'sekolah'       => (string) ($row->sekolah ?? ''),
            'logo_kiri'     => $row->logo_kiri ?? null,
        ];
    }
}