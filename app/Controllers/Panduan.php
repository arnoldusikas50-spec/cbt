<?php

namespace App\Controllers;

use App\Models\DashboardModel;

class Panduan extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function pengawas()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        
        $data = [
            'judul' => 'Panduan Pengawas',
            'subjudul' => 'Petunjuk Teknis & Operasional',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'user' => $this->getUser()
        ];

        return view('panduan/pengawas', $data);
    }
}
