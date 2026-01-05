<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Init extends CI_Controller {

	public function index()
    {
        $this->load->helper('init');

        if (!database_connected() || is_database_empty() || is_setting_empty()) {
            redirect('install');
        }

        $current_version = get_current_migration_version();
        $latest_version = get_latest_migration_version();

        if ($current_version < $latest_version) {
            $data = [
                'judul'     => 'Menu Migrasi',
                'subjudul'  => 'System Information CBT by Ikasmedia',
            ];
            $this->load->view('install/header', $data);
            $this->load->view('init');
            $this->load->view('install/footer');
        } else {
            redirect('auth');
        }
    }
}
