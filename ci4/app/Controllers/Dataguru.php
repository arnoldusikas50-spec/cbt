<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Dataguru Controller - Migrated from CI3
 * CRUD operations for teacher data
 */
class Dataguru extends BaseController
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
            'judul' => 'Data Guru',
            'subjudul' => 'Kelola Data Guru',
            'setting' => $setting,
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'mapels' => $this->masterModel->getAllMapel(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('master/guru/data', $data)
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

        $search = $this->request->getGet('search')['value'] ?? null;
        $gurus = $this->masterModel->getAllGuru($search);

        $data = [];
        foreach ($gurus as $i => $g) {
            $data[] = [
                'no' => $i + 1,
                'id_guru' => $g->id_guru,
                'nama_guru' => $g->nama_guru,
                'nip' => $g->nip ?? '-',
                'nuptk' => $g->nuptk ?? '-',
                'email' => $g->email ?? '-',
                'no_hp' => $g->no_hp ?? '-',
                'status' => $g->status ?? '1',
            ];
        }

        return $this->response->setJSON([
            'draw' => $this->request->getGet('draw'),
            'recordsTotal' => count($gurus),
            'recordsFiltered' => count($gurus),
            'data' => $data
        ]);
    }

    /**
     * Save guru
     */
    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $validation = \Config\Services::validation();

        if (!$this->validate(['nama_guru' => 'required|min_length[3]'])) {
            return $this->respond(false, implode('<br>', $validation->getErrors()));
        }

        $data = [
            'nama_guru' => $this->request->getPost('nama_guru'),
            'nip' => $this->request->getPost('nip'),
            'nuptk' => $this->request->getPost('nuptk'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'email' => $this->request->getPost('email'),
            'no_hp' => $this->request->getPost('no_hp'),
            'alamat' => $this->request->getPost('alamat'),
            'status' => '1'
        ];

        $result = $this->masterModel->saveGuru($data);

        return $result
            ? $this->respond(true, 'Data guru berhasil disimpan')
            : $this->respond(false, 'Gagal menyimpan data');
    }

    /**
     * Update guru
     */
    public function update()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $id = (int) $this->request->getPost('id_guru');

        if (!$id) {
            return $this->respond(false, 'ID tidak valid');
        }

        $data = [
            'nama_guru' => $this->request->getPost('nama_guru'),
            'nip' => $this->request->getPost('nip'),
            'nuptk' => $this->request->getPost('nuptk'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'email' => $this->request->getPost('email'),
            'no_hp' => $this->request->getPost('no_hp'),
            'alamat' => $this->request->getPost('alamat'),
        ];

        $result = $this->masterModel->updateGuru($id, $data);

        return $result
            ? $this->respond(true, 'Data guru berhasil diperbarui')
            : $this->respond(false, 'Gagal memperbarui data');
    }

    /**
     * Delete guru
     */
    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Hanya admin yang dapat menghapus');
        }

        $id = (int) $this->request->getPost('id_guru');

        if (!$id) {
            return $this->respond(false, 'ID tidak valid');
        }

        $result = $this->masterModel->deleteGuru($id);

        return $result
            ? $this->respond(true, 'Data guru berhasil dihapus')
            : $this->respond(false, 'Gagal menghapus data');
    }
}
