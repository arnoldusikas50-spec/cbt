<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;

/**
 * Cbtruang Controller - Migrated from CI3
 */
class Cbtruang extends BaseController
{
    protected CbtModel $cbtModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $data = [
            'judul' => 'Ruang Ujian',
            'subjudul' => 'Kelola Ruang Ujian CBT',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/ruang/data')
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $ruangs = $this->cbtModel->getAllRuang();

            $data = [];
            foreach ($ruangs as $i => $r) {
                $data[] = [
                    'no' => $i + 1,
                    'id_ruang' => $r->id_ruang,
                    'nama_ruang' => $r->nama_ruang ?? '-',
                    'kapasitas' => $r->kapasitas ?? 0,
                ];
            }

            return $this->response->setJSON(['data' => $data]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function save()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $db = \Config\Database::connect();

            $data = [
                'nama_ruang' => $this->request->getPost('nama_ruang'),
                'kapasitas' => $this->request->getPost('kapasitas') ?: 30,
            ];

            $result = $db->table('cbt_ruang')->insert($data);

            return $result
                ? $this->respond(true, 'Ruang berhasil ditambahkan')
                : $this->respond(false, 'Gagal menambahkan ruang');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            if (!$this->isAdmin()) {
                return $this->respond(false, 'Akses ditolak');
            }

            $id = (int) $this->request->getPost('id_ruang');
            $db = \Config\Database::connect();

            $result = $db->table('cbt_ruang')->where('id_ruang', $id)->delete();

            return $result
                ? $this->respond(true, 'Ruang berhasil dihapus')
                : $this->respond(false, 'Gagal menghapus ruang');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }
}
