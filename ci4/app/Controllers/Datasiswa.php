<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Datasiswa Controller - Migrated from CI3
 * CRUD operations for student data
 */
class Datasiswa extends BaseController
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
            'judul' => 'Data Siswa',
            'subjudul' => 'Kelola Data Siswa',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'kelases' => $tp && $smt ? $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt) : [],
            'user' => (object) [
                'first_name' => session()->get('first_name'),
            ]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('master/siswa/data', $data)
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

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return $this->response->setJSON(['data' => []]);
        }

        $draw = $this->request->getGet('draw');
        $start = (int) ($this->request->getGet('start') ?? 0);
        $length = (int) ($this->request->getGet('length') ?? 10);
        $search = $this->request->getGet('search')['value'] ?? null;

        $siswa = $this->masterModel->getAllSiswa($tp->id_tp, $smt->id_smt, $length, $start, $search);
        $total = $this->masterModel->getTotalSiswa($tp->id_tp, $smt->id_smt, $search);

        $data = [];
        foreach ($siswa as $i => $s) {
            $data[] = [
                'no' => $start + $i + 1,
                'id_siswa' => $s->id_siswa,
                'nama' => $s->nama,
                'nis' => $s->nis ?? '-',
                'nisn' => $s->nisn ?? '-',
                'kelas' => $s->nama_kelas ?? '-',
                'jk' => $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                'status' => $s->status ?? '1',
                'username' => $s->username ?? '-',
            ];
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    /**
     * Save siswa
     */
    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'nama' => 'required|min_length[3]',
            'nis' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->respond(false, implode('<br>', $validation->getErrors()));
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'nis' => $this->request->getPost('nis'),
            'nisn' => $this->request->getPost('nisn'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'agama' => $this->request->getPost('agama'),
            'alamat' => $this->request->getPost('alamat'),
            'username' => $this->request->getPost('username') ?: $this->request->getPost('nis'),
            'status' => '1'
        ];

        $result = $this->masterModel->saveSiswa($data);

        if ($result) {
            return $this->respond(true, 'Data siswa berhasil disimpan');
        }

        return $this->respond(false, 'Gagal menyimpan data');
    }

    /**
     * Update siswa
     */
    public function update()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $id = (int) $this->request->getPost('id_siswa');

        if (!$id) {
            return $this->respond(false, 'ID tidak valid');
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'nis' => $this->request->getPost('nis'),
            'nisn' => $this->request->getPost('nisn'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'agama' => $this->request->getPost('agama'),
            'alamat' => $this->request->getPost('alamat'),
        ];

        $result = $this->masterModel->updateSiswa($id, $data);

        if ($result) {
            return $this->respond(true, 'Data siswa berhasil diperbarui');
        }

        return $this->respond(false, 'Gagal memperbarui data');
    }

    /**
     * Delete siswa
     */
    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Hanya admin yang dapat menghapus');
        }

        $id = (int) $this->request->getPost('id_siswa');

        if (!$id) {
            return $this->respond(false, 'ID tidak valid');
        }

        $result = $this->masterModel->deleteSiswa($id);

        if ($result) {
            return $this->respond(true, 'Data siswa berhasil dihapus');
        }

        return $this->respond(false, 'Gagal menghapus data');
    }
}
