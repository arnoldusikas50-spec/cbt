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

        // Debug for cbtpraktik access
        $uri = $request->getUri()->getPath();
        if (strpos($uri, 'cbtpraktik') !== false) {
            log_message('debug', 'AuthFilter - URI: ' . $uri);
            log_message('debug', 'AuthFilter - is_login: ' . ($session->get('is_login') ? 'YES' : 'NO'));
            log_message('debug', 'AuthFilter - role: ' . $session->get('role'));
            log_message('debug', 'AuthFilter - role_name: ' . $session->get('role_name'));
            log_message('debug', 'AuthFilter - arguments: ' . json_encode($arguments));
        }

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
                        'redirect' => site_url('login')
                    ]);
            }

            // Store intended URL for redirect after login
            $session->set('redirect_url', current_url());

            // Redirect to login page
            return redirect()->to('login')->with('message', 'Silakan login terlebih dahulu.');
        }

        // Check role-based access if arguments provided
        if (!empty($arguments)) {
            $userRole = strtolower($session->get('role_name') ?? '');
            $allowedRoles = array_map('strtolower', $arguments);

            $roleMatches = in_array($userRole, $allowedRoles, true);

            if (!$roleMatches) {
                $roleId = (string) ($session->get('role') ?? '');
                $roleAliasMap = [
                    'admin' => '1',
                    'guru' => '2',
                    'siswa' => '3',
                    'panitia' => '4',
                ];

                foreach ($allowedRoles as $alias) {
                    if (($roleAliasMap[$alias] ?? null) == $roleId) { // Loose comparison
                        $roleMatches = true;
                        break;
                    }
                }
            }

            if (!$roleMatches) {
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
