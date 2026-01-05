<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Datakelas Controller - Migrated from CI3
 * CRUD operations for class/rombel data
 */
class Datakelas extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Index page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Data Kelas',
            'subjudul' => 'Kelola Data Kelas/Rombel',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'gurus' => $this->masterModel->getAllGuru(),
            'jurusans' => $this->masterModel->getAllJurusan(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('master/kelas/data', $data)
            . view('_templates/dashboard/_footer');
    }

    /**
     * Get data for DataTable
     */
    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return $this->response->setJSON(['data' => []]);
        }

        try {
            $kelases = $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt);

            $data = [];
            foreach ($kelases as $i => $k) {
                $data[] = [
                    'no' => $i + 1,
                    'id_kelas' => $k->id_kelas,
                    'nama_kelas' => $k->nama_kelas,
                    'kode_kelas' => $k->kode_kelas ?? '-',
                    'level' => $k->level ?? '-',
                    'jurusan' => $k->jurusan ?? '-',
                    'id_jurusan' => $k->id_jurusan ?? '',
                    'wali_kelas' => $k->wali ?? '-',
                    'jumlah' => $this->masterModel->countSiswaInKelas($k->id_kelas, $tp->id_tp, $smt->id_smt),
                ];
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['data' => [], 'error' => $e->getMessage()]);
        }

        return $this->response->setJSON([
            'draw' => $this->request->getGet('draw'),
            'recordsTotal' => count($kelases),
            'recordsFiltered' => count($kelases),
            'data' => $data
        ]);
    }

    /**
     * Save kelas
     */
    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$this->validate(['nama_kelas' => 'required'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama kelas wajib diisi']);
        }

        $data = [
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'kode_kelas' => $this->request->getPost('kode_kelas'),
            'level' => $this->request->getPost('level'),
            'wali_kelas' => $this->request->getPost('wali_kelas'),
            'id_jurusan' => $this->request->getPost('id_jurusan'),
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
        ];

        $result = $this->masterModel->saveKelas($data);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data kelas berhasil disimpan' : 'Gagal menyimpan data'
        ]);
    }

    /**
     * Update kelas
     */
    public function update()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id_kelas');

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        if (!$this->validate(['nama_kelas' => 'required'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama kelas wajib diisi']);
        }

        $data = [
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'kode_kelas' => $this->request->getPost('kode_kelas'),
            'level' => $this->request->getPost('level'),
            'wali_kelas' => $this->request->getPost('wali_kelas'),
            'id_jurusan' => $this->request->getPost('id_jurusan'),
        ];

        $result = $this->masterModel->updateKelas($id, $data);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data kelas berhasil diperbarui' : 'Gagal memperbarui data'
        ]);
    }

    /**
     * Delete kelas
     */
    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hanya admin yang dapat menghapus']);
        }

        $id = (int) $this->request->getPost('id_kelas');

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        $result = $this->masterModel->deleteKelas($id);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data kelas berhasil dihapus' : 'Gagal menghapus data'
        ]);
    }
}
