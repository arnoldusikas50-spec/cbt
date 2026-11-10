<?php

namespace App\Controllers;

use App\Traits\SiswaAktifTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides common functionality for all controllers.
 * Migrated from CI3 CBT AKM project.
 */
abstract class BaseController extends Controller
{
    use SiswaAktifTrait;

    /**
     * Role constants
     */
    protected const ROLE_ADMIN = '1';
    protected const ROLE_GURU = '2';
    protected const ROLE_SISWA = '3';
    protected const ROLE_PANITIA = '4';

    /**
     * Jabatan constants for panitia
     */
    protected const JABATAN_PANITIA = 'panitia';
    protected const JABATAN_PENGAWAS = 'pengawas';

    /**
     * Instance of the main Request object.
     *
     * @var \CodeIgniter\HTTP\IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form', 'url', 'text', 'init'];

    /**
     * Session service
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * @var \App\Models\AuthModel
     */
    protected \App\Models\AuthModel $authModel;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload session service
        $this->session = service('session');
        try {
            $this->authModel = new \App\Models\AuthModel();
        } catch (\Throwable $e) {
            // Database connection failed - will be handled by controllers
        }
    }


    /**
     * Get current user ID
     */
    protected function getUserId(): ?int
    {
        return $this->session->get('user_id');
    }

    /**
     * Get current user role
     */
    protected function getUserRole(): ?string
    {
        return $this->session->get('role_name');
    }

    /**
     * Check if current user is in specified group/role
     */
    protected function inGroup(string $group): bool
    {
        $role = $this->session->get('role_name');
        return strtolower($role ?? '') === strtolower($group);
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        return $this->session->has('is_login') && $this->session->get('is_login') === true;
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        $roleId = (string) $this->session->get('role');
        $roleName = strtolower((string) $this->session->get('role_name'));
        return $roleId == self::ROLE_ADMIN || $roleName === 'admin';
    }

    /**
     * Check if current user is guru (teacher)
     */
    protected function isGuru(): bool
    {
        $roleId = (string) $this->session->get('role');
        $roleName = strtolower((string) $this->session->get('role_name'));
        return $roleId == self::ROLE_GURU || $roleName === 'guru';
    }

    /**
     * Check if current user is panitia
     */
    protected function isPanitia(): bool
    {
        $roleId = (string) $this->session->get('role');
        $roleName = strtolower((string) $this->session->get('role_name'));
        return $roleId == self::ROLE_PANITIA || $roleName === 'panitia';
    }

    /**
     * Check if panitia has jabatan pengawas
     * Requires panitia data object from getDataPanitiaByUserId
     */
    protected function isPengawas(?object $panitia): bool
    {
        if (!$panitia || empty($panitia->jabatan)) {
            return false;
        }
        return strtolower($panitia->jabatan) === self::JABATAN_PENGAWAS;
    }

    /**
     * Check if panitia has jabatan panitia (koordinator)
     * Requires panitia data object from getDataPanitiaByUserId
     */
    protected function isPanitiaJabatan(?object $panitia): bool
    {
        if (!$panitia || empty($panitia->jabatan)) {
            return false;
        }
        $jabatan = strtolower($panitia->jabatan);
        return $jabatan === self::JABATAN_PANITIA || $jabatan === '';
    }

    /**
     * Check if current user has panitia jabatan (not pengawas)
     * Helper method to restrict access to specific controllers
     */
    protected function hasPanitiaJabatan(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        if (!$this->isPanitia()) {
            return false;
        }
        
        // Get panitia data
        $dashboard = new \App\Models\DashboardModel();
        $panitia = $dashboard->getDataPanitiaByUserId(
            (int) $this->session->get('user_id'),
            (int) $dashboard->getTahunActive()->id_tp,
            (int) $dashboard->getSemesterActive()->id_smt
        );
        
        return $this->isPanitiaJabatan($panitia);
    }

    /**
     * Check if current user has pengawas jabatan
     */
    protected function hasPengawasJabatan(): bool
    {
        if ($this->isAdmin()) {
            return false; // Admin is not pengawas
        }
        
        if (!$this->isPanitia()) {
            return false;
        }
        
        // Get panitia data
        $dashboard = new \App\Models\DashboardModel();
        $panitia = $dashboard->getDataPanitiaByUserId(
            (int) $this->session->get('user_id'),
            (int) $dashboard->getTahunActive()->id_tp,
            (int) $dashboard->getSemesterActive()->id_smt
        );
        
        return $this->isPengawas($panitia);
    }

    /**
     * Respond with JSON - equivalent to CI3's respond() method
     * Includes CSRF tokens for AJAX response consistency
     */
    protected function respond(bool $status, string $message = '', $data = null, $errors = null, int $httpCode = 200)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ];

        return $this->response
            ->setStatusCode($httpCode)
            ->setContentType('application/json')
            ->setJSON($response);
    }

    /**
     * Get shared assets path (for accessing CI3 assets folder)
     */
    protected function getAssetsPath(): string
    {
        return base_url('../assets/');
    }

    /**
     * Get current logged in user object
     * Equivalent to CI3's $this->auth->user()
     */
    protected function getUser(): ?object
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return null;
        }

        $db = \Config\Database::connect();
        $query = $db->table('users')
            ->where('id', $userId)
            ->get();
        $user = $query ? $query->getRow() : null;

        if ($user) {
            $user->role = $this->session->get('role');
            $user->role_name = $this->session->get('role_name');

            // Combine first and last name if nama_lengkap doesn't exist
            if (!isset($user->nama_lengkap)) {
                if (!empty($user->first_name)) {
                    $user->nama_lengkap = $user->first_name . (!empty($user->last_name) ? ' ' . $user->last_name : '');
                } else {
                    $userSessName = session()->get('nama');
                    $user->nama_lengkap = !empty($userSessName) ? $userSessName : 'IKASMEDIA';
                }
            }
        }

        return $user;
    }

    /**
     * Show error page
     * Equivalent to CI3's show_error()
     */
    protected function showError(string $message, int $statusCode = 500)
    {
        return view('errors/html/error_' . $statusCode, [
            'message' => $message
        ]);
    }

    /**
     * Render view with standard layout
     * Equivalent to CI3's render_view()
     */
    protected function renderView(string $view, array $data = [])
    {
        // Add common data
        $data['base_url'] = base_url();
        $data['user'] = $data['user'] ?? $this->getUser();

        return view($view, $data);
    }

    /**
     * Decode data that might be serialized or JSON
     */
    protected function maybe_unserialize($data)
    {
        if (empty($data)) {
            return [];
        }

        if (is_array($data) || is_object($data)) {
            return $data;
        }

        // Try JSON first
        $decoded = json_decode($data);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Try Serialized
        $unserialized = @unserialize($data);
        if ($unserialized !== false || $data === 'b:0;') {
            return $unserialized;
        }

        return $data;
    }
    /**
     * Cache helper - Get data from cache or execute callback
     * Wrapper for CodeIgniter's cache() helper with Redis
     */
    protected function cacheRemember(string $key, int $ttl, callable $callback)
    {
        $cache = cache();
        $data = $cache->get($key);

        if ($data === null) {
            $data = $callback();
            $cache->save($key, $data, $ttl);
        }

        return $data;
    }

    /**
     * Cache helper - Clear specific cache key
     */
    protected function cacheForget(string $key): bool
    {
        return cache()->delete($key);
    }

    /**
     * Cache helper - Clear all cache
     */
    protected function cacheFlush(): bool
    {
        return cache()->clean();
    }

    /**
     * Read value from object/array with a list of candidate keys.
     *
     * @param mixed $source
     * @param array<int, string> $keys
     * @param mixed $default
     * @return mixed
     */
    protected function pickMixed($source, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return $source[$key];
            }
            if (is_object($source) && property_exists($source, $key)) {
                return $source->$key;
            }
        }
        return $default;
    }

    /**
     * Safely read JSON body without throwing on invalid/empty JSON.
     * Returns decoded data, or null when body is empty or not valid JSON.
     *
     * @param bool $assoc Return result as associative array
     * @return mixed|null
     */
    protected function safeGetJSON(bool $assoc = false)
    {
        try {
            return $this->request->getJSON($assoc);
        } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
            return null;
        }
    }
}