<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Auth Filter - Check if user is logged in
 * Migrated from CI3 authentication checks
 */
class AuthFilter implements FilterInterface
{
    /**
     * Check authentication before controller method
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        // Check if user is logged in
        if (!$session->get('is_login')) {
            // Check if it's an AJAX request
            $isAjax = $request instanceof IncomingRequest && $request->isAJAX();

            if ($isAjax) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'status' => false,
                        'message' => 'Session expired. Please login again.',
                        'redirect' => site_url('auth')
                    ]);
            }

            // Store intended URL for redirect after login
            $session->set('redirect_url', current_url());

            // Redirect to login page
            return redirect()->to('auth')->with('message', 'Silakan login terlebih dahulu.');
        }

        // Check role-based access if arguments provided
        if (!empty($arguments)) {
            $userRole = strtolower($session->get('role_name') ?? '');
            $allowedRoles = array_map('strtolower', $arguments);

            if (!in_array($userRole, $allowedRoles)) {
                $isAjax = $request instanceof IncomingRequest && $request->isAJAX();
                if ($isAjax) {
                    return service('response')
                        ->setStatusCode(403)
                        ->setJSON([
                            'status' => false,
                            'message' => 'Anda tidak memiliki akses ke halaman ini.'
                        ]);
                }

                return redirect()->to('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
            }
        }

        return $request;
    }

    /**
     * After controller method (not used)
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
        return $response;
    }
}
