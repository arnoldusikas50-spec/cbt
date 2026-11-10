<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;

/**
 * Cbtsesi Controller - Ported from CI3
 */
class Cbtsesi extends BaseController
{
    protected CbtModel $cbtModel;
    protected DashboardModel $dashboardModel;
    protected MasterModel $masterModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel();
    }

    /**
     * Sesi ujian boleh dikelola admin maupun panitia (route cbtsesi memang
     * didaftarkan untuk 'auth:admin,panitia'); pengawas tidak.
     */
    private function canManage(): bool
    {
        return $this->isLoggedIn() && ($this->isAdmin() || $this->isPanitia());
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->canManage()) {
            return redirect()->to('dashboard');
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Sesi Ujian',
            'subjudul' => 'Data Sesi Ujian',
            'setting' => $this->dashboardModel->getSetting(),
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
        ];

        return view('cbt/sesi/data', $data);
    }

    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $sesis = $this->cbtModel->getAllSesi();
            $data = [];

            foreach ($sesis as $i => $s) {
                $data[] = [
                    'no' => $i + 1,
                    'id_sesi' => $s->id_sesi,
                    'nama_sesi' => $s->nama_sesi ?? '-',
                    'kode_sesi' => $s->kode_sesi ?? '-',
                    'waktu_mulai' => $s->waktu_mulai ?? '-',
                    'waktu_akhir' => $s->waktu_akhir ?? '-',
                    'istirahat' => $s->istirahat ?? null,
                ];
            }

            return $this->response->setJSON(['data' => $data]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function add()
    {
        if (!$this->canManage()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        try {
            $data = [
                'nama_sesi' => $this->request->getPost('nama_sesi'),
                'kode_sesi' => $this->request->getPost('kode_sesi'),
                'waktu_mulai' => $this->request->getPost('waktu_mulai'),
                'waktu_akhir' => $this->request->getPost('waktu_akhir'),
                'istirahat' => $this->request->getPost('istirahat'),
            ];

            $db = \Config\Database::connect();
            $result = $db->table('cbt_sesi')->insert($data);

            return $this->response->setJSON([
                'status' => $result ? true : false,
                'message' => $result ? 'Sesi berhasil ditambahkan' : 'Gagal menambahkan sesi'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        if (!$this->canManage()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        try {
            $id = $this->request->getPost('id_sesi');
            $data = [
                'nama_sesi' => $this->request->getPost('nama_sesi'),
                'kode_sesi' => $this->request->getPost('kode_sesi'),
                'waktu_mulai' => $this->request->getPost('waktu_mulai'),
                'waktu_akhir' => $this->request->getPost('waktu_akhir'),
                'istirahat' => $this->request->getPost('istirahat'),
            ];

            $db = \Config\Database::connect();
            $result = $db->table('cbt_sesi')->where('id_sesi', $id)->update($data);

            return $this->response->setJSON([
                'status' => $result ? true : false,
                'message' => $result ? 'Sesi berhasil diupdate' : 'Gagal mengupdate sesi'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        if (!$this->canManage()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        try {
            $chk = $this->request->getPost('checked');
            $id = $this->request->getPost('id_sesi');

            $db = \Config\Database::connect();

            if ($chk && is_array($chk)) {
                // Bulk delete
                $result = $db->table('cbt_sesi')->whereIn('id_sesi', $chk)->delete();
                return $this->response->setJSON([
                    'status' => $result ? true : false,
                    'total' => count($chk),
                    'message' => $result ? count($chk) . ' sesi berhasil dihapus' : 'Gagal menghapus sesi'
                ]);
            } elseif ($id) {
                // Single delete
                $result = $db->table('cbt_sesi')->where('id_sesi', $id)->delete();
                return $this->response->setJSON([
                    'status' => $result ? true : false,
                    'message' => $result ? 'Sesi berhasil dihapus' : 'Gagal menghapus sesi'
                ]);
            }

            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data yang dipilih']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
