<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Legacy installer controller has been retired.
 * Any request to /install now returns 404 to avoid exposing the old wizard.
 */
class Install extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->denyAccess();
    }

    /**
     * Always show 404 for legacy installer endpoints.
     */
    private function denyAccess(): void {
        show_404();
    }

    public function index() {
        $this->denyAccess();
    }

    public function steps() {
        $this->denyAccess();
    }

    public function checkDatabase() {
        $this->denyAccess();
    }

    public function createDb() {
        $this->denyAccess();
    }

    public function createSetting() {
        $this->denyAccess();
    }

    public function createAdmin() {
        $this->denyAccess();
    }

    public function createApp() {
        $this->denyAccess();
    }
}
