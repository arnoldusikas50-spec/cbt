<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;

/**
 * Cbtsesi Controller - Migrated from CI3
 */
class Cbtsesi extends BaseController
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
            'judul' => 'Sesi Ujian',
            'subjudul' => 'Kelola Sesi Ujian CBT',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/sesi/data')
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $sesis = $this->cbtModel->getAllSesi();

            $data = [];
            foreach ($sesis as $i => $s) {
                $data[] = [
                    'no' => $i + 1,
                    'id_sesi' => $s->id_sesi,
                    'nama_sesi' => $s->nama_sesi ?? '-',
                    'waktu_mulai' => $s->waktu_mulai ?? '-',
                    'waktu_akhir' => $s->waktu_akhir ?? '-',
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
                'nama_sesi' => $this->request->getPost('nama_sesi'),
                'waktu_mulai' => $this->request->getPost('waktu_mulai'),
                'waktu_akhir' => $this->request->getPost('waktu_akhir'),
            ];

            $result = $db->table('cbt_sesi')->insert($data);

            return $result
                ? $this->respond(true, 'Sesi berhasil ditambahkan')
                : $this->respond(false, 'Gagal menambahkan sesi');
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

            $id = (int) $this->request->getPost('id_sesi');
            $db = \Config\Database::connect();

            $result = $db->table('cbt_sesi')->where('id_sesi', $id)->delete();

            return $result
                ? $this->respond(true, 'Sesi berhasil dihapus')
                : $this->respond(false, 'Gagal menghapus sesi');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }
}
