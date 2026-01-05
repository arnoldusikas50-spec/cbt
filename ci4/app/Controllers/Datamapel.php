<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Datamapel Controller - Migrated from CI3
 * CRUD operations for subject/mapel data
 */
class Datamapel extends BaseController
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

        $data = [
            'judul' => 'Data Mapel',
            'subjudul' => 'Kelola Data Mata Pelajaran',
            'setting' => $setting,
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('master/mapel/data', $data)
            . view('_templates/dashboard/_footer');
    }

    /**
     * Get data for DataTable
     */
    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $setting = $this->dashboardModel->getSetting();
        $jenjang = $setting->jenjang ?? null;

        try {
            $mapels = $this->masterModel->getAllMapel($jenjang ? (int) $jenjang : null);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'draw' => $this->request->getGet('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }

        $data = [];
        foreach ($mapels as $i => $m) {
            $data[] = [
                'no' => $i + 1,
                'id_mapel' => $m->id_mapel,
                'nama_mapel' => $m->nama_mapel,
                'kode' => $m->kode ?? '-',
                'kelompok' => $m->kelompok ?? '-',
                'urut' => $m->urut ?? 0,
                'status' => $m->status ?? '1',
            ];
        }

        return $this->response->setJSON([
            'draw' => $this->request->getGet('draw'),
            'recordsTotal' => count($mapels),
            'recordsFiltered' => count($mapels),
            'data' => $data
        ]);
    }

    /**
     * Save mapel
     */
    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        if (!$this->validate(['nama_mapel' => 'required'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama mapel wajib diisi']);
        }

        $data = [
            'nama_mapel' => $this->request->getPost('nama_mapel'),
            'kode' => $this->request->getPost('kode'),
            'kelompok' => $this->request->getPost('kelompok'),
            'urut' => $this->request->getPost('urut') ?: 0,
            'jenjang' => $this->request->getPost('jenjang') ?: 1,
            'status' => '1'
        ];

        $result = $this->masterModel->saveMapel($data);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data mapel berhasil disimpan' : 'Gagal menyimpan data'
        ]);
    }

    /**
     * Update mapel
     */
    public function update()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id_mapel');

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        $data = [
            'nama_mapel' => $this->request->getPost('nama_mapel'),
            'kode' => $this->request->getPost('kode'),
            'kelompok' => $this->request->getPost('kelompok'),
            'urut' => $this->request->getPost('urut') ?: 0,
        ];

        $result = $this->masterModel->updateMapel($id, $data);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data mapel berhasil diperbarui' : 'Gagal memperbarui data'
        ]);
    }

    /**
     * Delete mapel
     */
    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hanya admin yang dapat menghapus']);
        }

        $id = (int) $this->request->getPost('id_mapel');

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        $result = $this->masterModel->deleteMapel($id);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data mapel berhasil dihapus' : 'Gagal menghapus data'
        ]);
    }
}
