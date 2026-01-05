<?php

namespace App\Controllers;

/**
 * Home Controller - Default landing page
 */
class Home extends BaseController
{
    public function index()
    {
        // Check if logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            return redirect()->to('dashboard');
        }

        // Otherwise redirect to login
        return redirect()->to('auth');
    }
}
