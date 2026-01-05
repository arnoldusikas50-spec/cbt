<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        $this->output->set_status_header('404');
        $this->load->view('errors/html/error_404', [
            'heading' => 'Page Not Found',
            'message' => 'The page you requested was not found.'
        ]);
    }

    public function forbidden()
    {
        $this->output->set_status_header('403');
        $this->load->view('errors/html/error_403');
    }

    public function access_denied()
    {
        $this->output->set_status_header('403');
        $this->load->view('errors/html/error_access');
    }

    public function internal_error()
    {
        $this->output->set_status_header('500');
        $this->load->view('errors/html/error_general', [
            'heading' => 'Internal Server Error',
            'message' => 'An unexpected error occurred.'
        ]);
    }
    
    public function unavailable()
    {
        $this->output->set_status_header('503');
        $this->load->view('errors/html/error_503');
    }
}
