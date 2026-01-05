<?php
/**

 * Date: 12/06/2020
 * Time: 10.27
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Datalogs extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
    }

    public function index()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Log Aktivitas',
            'subjudul' => 'Log Aktivitas',
            'setting' => $this->dashboard->getSetting()
        ];
        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['is_admin'] = $this->auth->is_admin();
        if($this->auth->is_admin()) {
            $this->render_view(1, 'master/logs/data', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->render_view(2, 'master/logs/data', $data);
        }
    }
}
