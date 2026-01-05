<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Admin Filter - Restrict access to admin only
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');
        $isAjax = $request instanceof IncomingRequest && $request->isAJAX();

        // First check if logged in
        if (!$session->get('is_login')) {
            if ($isAjax) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'status' => false,
                        'message' => 'Session expired. Please login again.'
                    ]);
            }
            return redirect()->to('auth');
        }

        // Check if admin
        $role = $session->get('role');
        if ($role !== '1') {
            if ($isAjax) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'status' => false,
                        'message' => 'Akses ditolak. Hanya admin yang dapat mengakses.'
                    ]);
            }
            return redirect()->to('dashboard')->with('error', 'Akses ditolak.');
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
