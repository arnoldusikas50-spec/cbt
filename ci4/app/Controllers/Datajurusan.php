<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Datajurusan Controller - Migrated from CI3
 */
class Datajurusan extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $data = [
            'judul' => 'Data Jurusan',
            'subjudul' => 'Kelola Data Jurusan/Program',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('master/jurusan/data', $data)
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $jurusans = $this->masterModel->getAllJurusan();

        $data = [];
        foreach ($jurusans as $i => $j) {
            $data[] = [
                'no' => $i + 1,
                'id_jurusan' => $j->id_jurusan,
                'nama_jurusan' => $j->nama_jurusan ?? '-',
                'singkatan' => $j->singkatan ?? '-',
            ];
        }

        return $this->response->setJSON([
            'draw' => $this->request->getGet('draw'),
            'recordsTotal' => count($jurusans),
            'recordsFiltered' => count($jurusans),
            'data' => $data
        ]);
    }

    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        if (!$this->validate(['nama_jurusan' => 'required'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama jurusan wajib diisi']);
        }

        $db = \Config\Database::connect();

        $data = [
            'nama_jurusan' => $this->request->getPost('nama_jurusan'),
            'singkatan' => $this->request->getPost('singkatan'),
        ];

        $result = $db->table('master_jurusan')->insert($data);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data jurusan berhasil disimpan' : 'Gagal menyimpan data'
        ]);
    }

    public function update()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id_jurusan');

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        if (!$this->validate(['nama_jurusan' => 'required'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama jurusan wajib diisi']);
        }

        $db = \Config\Database::connect();

        $data = [
            'nama_jurusan' => $this->request->getPost('nama_jurusan'),
            'singkatan' => $this->request->getPost('singkatan'),
        ];

        $result = $db->table('master_jurusan')->where('id_jurusan', $id)->update($data);

        return $this->response->setJSON([
            'status' => $result !== false,
            'message' => $result !== false ? 'Data jurusan berhasil diperbarui' : 'Gagal memperbarui data'
        ]);
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $id = (int) $this->request->getPost('id_jurusan');
        
        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        $db = \Config\Database::connect();
        $result = $db->table('master_jurusan')->where('id_jurusan', $id)->delete();

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Data berhasil dihapus' : 'Gagal menghapus data'
        ]);
    }
}
