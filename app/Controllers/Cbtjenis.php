<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\LogModel;
use App\Models\AuthModel;

class Cbtjenis extends BaseController
{
    protected CbtModel $cbt;
    protected DashboardModel $dashboard;
    protected LogModel $logging;
    protected AuthModel $authModel;

    public function __construct()
    {
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->logging = new LogModel();
        $this->authModel = new AuthModel();
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

        return view('cbt/jenis/data', $data);
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
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        try {
            $insert = [
                'nama_jenis' => $this->request->getPost('nama_jenis'),
                'kode_jenis' => $this->request->getPost('kode_jenis'),
                'status' => '0' // Default inactive
            ];

            $db = \Config\Database::connect();
            $db->table('cbt_jenis')->insert($insert);

            return $this->response->setJSON(['status' => true, 'message' => 'Berhasil menambah jenis ujian']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        try {
            $id = $this->request->getPost('id_jenis');
            $data = [
                'nama_jenis' => $this->request->getPost('nama_jenis'),
                'kode_jenis' => $this->request->getPost('kode_jenis')
            ];

            $this->cbt->updateJenis((int) $id, $data);
            return $this->response->setJSON(['status' => true, 'message' => 'Berhasil memperbarui jenis ujian']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function toggleStatus()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
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

            return $this->response->setJSON(['status' => $result, 'message' => $result ? $message : 'Gagal mengubah status']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        try {
            $chk = $this->request->getPost('checked');
            if (!$chk) {
                return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data yang dipilih']);
            }

            $db = \Config\Database::connect();
            $result = $db->table('cbt_jenis')->whereIn('id_jenis', $chk)->delete();

            return $this->response->setJSON(['status' => $result, 'message' => $result ? 'Berhasil menghapus jenis ujian' : 'Gagal menghapus']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
