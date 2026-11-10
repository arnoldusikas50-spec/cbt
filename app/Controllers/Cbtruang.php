<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;

/**
 * Cbtruang Controller - Ported from CI3
 */
class Cbtruang extends BaseController
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

    public function index()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return redirect()->to('auth');
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Ruang Ujian',
            'subjudul' => 'Data Ruang Ujian',
            'setting' => $this->dashboardModel->getSetting(),
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
        ];

        return view('cbt/ruang/data', $data);
    }

    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $ruangs = $this->cbtModel->getAllRuang();
            $db = \Config\Database::connect();

            // Pre-calc distinct sesi counts per ruang to avoid per-row queries
            $sesiCounts = $db->table('cbt_sesi_siswa')
                ->select('ruang_id, COUNT(DISTINCT sesi_id) as total_sesi')
                ->where('ruang_id IS NOT NULL')
                ->groupBy('ruang_id')
                ->get()
                ->getResult();

            $countsByRuang = [];
            foreach ($sesiCounts as $row) {
                $countsByRuang[$row->ruang_id] = (int) ($row->total_sesi ?? 0);
            }

            $data = [];
            foreach ($ruangs as $i => $r) {
                $data[] = [
                    'no' => $i + 1,
                    'id_ruang' => $r->id_ruang,
                    'nama_ruang' => $r->nama_ruang ?? '-',
                    'kode_ruang' => $r->kode_ruang ?? '-',
                    'jum_sesi' => $countsByRuang[$r->id_ruang] ?? 0,
                ];
            }

            return $this->response->setJSON(['data' => $data]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function add()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        try {
            $data = [
                'nama_ruang' => $this->request->getPost('nama_ruang'),
                'kode_ruang' => $this->request->getPost('kode_ruang'),
            ];

            $db = \Config\Database::connect();
            $result = $db->table('cbt_ruang')->insert($data);

            return $this->response->setJSON([
                'status' => $result ? true : false,
                'message' => $result ? 'Ruang berhasil ditambahkan' : 'Gagal menambahkan ruang'
            ]);
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
            $id = $this->request->getPost('id_ruang');
            $data = [
                'nama_ruang' => $this->request->getPost('nama_ruang'),
                'kode_ruang' => $this->request->getPost('kode_ruang'),
            ];

            $db = \Config\Database::connect();
            $result = $db->table('cbt_ruang')->where('id_ruang', $id)->update($data);

            return $this->response->setJSON([
                'status' => $result ? true : false,
                'message' => $result ? 'Ruang berhasil diupdate' : 'Gagal mengupdate ruang'
            ]);
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
            $id = $this->request->getPost('id_ruang');

            $db = \Config\Database::connect();

            if ($chk && is_array($chk)) {
                // Bulk delete
                $result = $db->table('cbt_ruang')->whereIn('id_ruang', $chk)->delete();
                return $this->response->setJSON([
                    'status' => $result ? true : false,
                    'total' => count($chk),
                    'message' => $result ? count($chk) . ' ruang berhasil dihapus' : 'Gagal menghapus ruang'
                ]);
            } elseif ($id) {
                // Single delete
                $result = $db->table('cbt_ruang')->where('id_ruang', $id)->delete();
                return $this->response->setJSON([
                    'status' => $result ? true : false,
                    'message' => $result ? 'Ruang berhasil dihapus' : 'Gagal menghapus ruang'
                ]);
            }

            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data yang dipilih']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
