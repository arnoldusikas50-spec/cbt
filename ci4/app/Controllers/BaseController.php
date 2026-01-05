<?php

namespace App\Controllers;

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
     * Check if user is logged in
     * Equivalent to CI3's $this->auth->logged_in()
     */
    protected function isLoggedIn(): bool
    {
        return (bool) $this->session->get('is_login');
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
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->session->get('role') === '1';
    }

    /**
     * Respond with JSON - equivalent to CI3's respond() method
     */
    protected function respond(bool $status, string $message = '', $data = null, $errors = null, int $httpCode = 200)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
            'data' => $data
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
        $user = $db->table('users')
            ->where('id', $userId)
            ->get()
            ->getRow();

        if ($user) {
            $user->role = $this->session->get('role');
            $user->role_name = $this->session->get('role_name');
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
}