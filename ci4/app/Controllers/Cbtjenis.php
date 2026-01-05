<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\LogModel;

class Cbtjenis extends BaseController
{
    protected CbtModel $cbt;
    protected DashboardModel $dashboard;
    protected LogModel $logging;

    public function __construct()
    {
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->logging = new LogModel();
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('auth');
        }

        $data = [
            'user' => $this->authModel->user(),
            'judul' => 'Jenis Ujian',
            'subjudul' => 'Data Jenis Ujian',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
            'jenis_ujian' => $this->cbt->getAllJenisUjian()
        ];

        $db = \Config\Database::connect();
        $data['active_jenis'] = $db->table('cbt_jenis')->where('status', '1')->get()->getRow();

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/jenis/data')
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }
        return $this->response->setJSON($this->cbt->getJenis());
    }

    public function add()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $insert = [
                'nama_jenis' => $this->request->getPost('nama_jenis'),
                'kode_jenis' => $this->request->getPost('kode_jenis'),
                'status' => '0' // Default inactive
            ];

            $db = \Config\Database::connect();
            $db->table('cbt_jenis')->insert($insert);

            return $this->respond(true, 'Berhasil menambah jenis ujian');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function update()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $id = $this->request->getPost('id_jenis');
            $data = [
                'nama_jenis' => $this->request->getPost('nama_jenis'),
                'kode_jenis' => $this->request->getPost('kode_jenis')
            ];

            $this->cbt->updateJenis((int) $id, $data);
            return $this->respond(true, 'Berhasil memperbarui jenis ujian');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function toggleStatus()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $id = $this->request->getPost('id_jenis');
            $status = $this->request->getPost('status');
            $new_status = $status == '1' ? '0' : '1';

            $db = \Config\Database::connect();
            // Jika mengaktifkan, nonaktifkan semua jenis lain terlebih dahulu
            if ($new_status == '1') {
                $db->table('cbt_jenis')->update(['status' => '0']);
            }

            // Update status jenis yang dipilih
            $result = $db->table('cbt_jenis')->where('id_jenis', $id)->update(['status' => $new_status]);

            // Get nama jenis untuk pesan
            $jenis = $db->table('cbt_jenis')->where('id_jenis', $id)->get()->getRow();
            $nama = $jenis ? $jenis->nama_jenis : '';

            $message = $new_status == '1'
                ? "Jenis \"$nama\" diaktifkan, yang lain dinonaktifkan"
                : "Jenis \"$nama\" dinonaktifkan";

            return $this->respond($result, $result ? $message : 'Gagal mengubah status', [
                'new_status' => $new_status
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $chk = $this->request->getPost('checked');
            if (!$chk) {
                return $this->respond(false, 'Tidak ada data yang dipilih');
            }

            $db = \Config\Database::connect();
            $result = $db->table('cbt_jenis')->whereIn('id_jenis', $chk)->delete();

            return $this->respond($result, $result ? 'Berhasil menghapus jenis ujian' : 'Gagal menghapus', ['total' => count($chk)]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }
}
