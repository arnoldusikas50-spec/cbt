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

        $mapels = $this->masterModel->getAllMapel();
        $mapel_peminatan = [];
        foreach ($mapels as $m) {
            $mapel_peminatan[] = [
                'id_mapel' => $m->id_mapel,
                'nama_mapel' => $m->nama_mapel
            ];
        }

        $data = [
            'judul' => 'Data Jurusan',
            'subjudul' => 'Kelola Data Jurusan / Program Keahlian',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'user' => (object) ['first_name' => session()->get('first_name')],
            'mapel_peminatan' => $mapel_peminatan
        ];

        return view('master/jurusan/data', $data);
    }

    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $jurusans = $this->masterModel->getAllJurusan();
        $allMapel = $this->masterModel->getAllMapel();
        
        $mapelMap = [];
        foreach ($allMapel as $m) {
            $mapelMap[$m->id_mapel] = $m->nama_mapel;
        }

        $data = [];
        foreach ($jurusans as $i => $j) {
            // Parse mapel_peminatan (comma separated string)
            $mapelBadges = [];
            if (!empty($j->mapel_peminatan)) {
                $ids = array_filter(explode(',', $j->mapel_peminatan));
                foreach ($ids as $mid) {
                    $mid = trim($mid);
                    if (isset($mapelMap[$mid])) {
                        $mapelBadges[] = '<span class="badge bg-secondary bg-opacity-10 text-secondary border me-1 mb-1">' . esc($mapelMap[$mid]) . '</span>';
                    }
                }
            }

            $mapelText = !empty($mapelBadges) 
                ? implode(' ', $mapelBadges) 
                : '<span class="text-muted small">Semua / Tidak Ada</span>';

            $statusVal = (int) ($j->status ?? 1);
            $statusBadge = '<span class="badge-action-status ' . ($statusVal === 1 ? '' : 'inactive') . '">' . ($statusVal === 1 ? 'Aktif' : 'Nonaktif') . '</span>';
            $toggleTitle = $statusVal === 1 ? 'Nonaktifkan Jurusan' : 'Aktifkan Jurusan';

            $data[] = [
                'no' => $i + 1,
                'id_jurusan' => $j->id_jurusan,
                'kode_jurusan' => '<span class="fw-bold text-dark">' . esc($j->kode_jurusan ?? $j->singkatan ?? '-') . '</span>',
                'nama_jurusan' => esc($j->nama_jurusan ?? '-'),
                'mapel_peminatan' => $mapelText,
                'status' => $statusBadge,
                'aksi' => '<div class="btn-action-group">' .
                          '<button class="btn btn-action btn-action-warning btn-toggle-status" data-id="' . $j->id_jurusan . '" data-status="' . $statusVal . '" title="' . $toggleTitle . '"><i class="bi bi-slash-circle"></i></button>' .
                          '<button class="btn btn-action btn-action-edit btn-edit" data-id="' . $j->id_jurusan . '" title="Edit"><i class="bi bi-pencil-fill"></i></button>' .
                          '<button class="btn btn-action btn-action-delete btn-hapus" data-id="' . $j->id_jurusan . '" title="Hapus"><i class="bi bi-trash-fill"></i></button>' .
                          '</div>',
            ];
        }

        $draw = $this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 1;

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => count($jurusans),
            'recordsFiltered' => count($jurusans),
            'data' => $data
        ]);
    }

    public function toggle_status()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id_jurusan');
        $currentStatus = (int) $this->request->getPost('status');
        $newStatus = $currentStatus === 1 ? 0 : 1;

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        $db = \Config\Database::connect();
        $result = $db->table('master_jurusan')->where('id_jurusan', $id)->update(['status' => $newStatus]);

        return $this->response->setJSON([
            'status' => $result !== false,
            'message' => $result !== false ? 'Status jurusan berhasil diperbarui' : 'Gagal memperbarui status'
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

        $mapelPeminatan = $this->request->getPost('mapel_peminatan');
        $mapelStr = is_array($mapelPeminatan) ? implode(',', array_filter($mapelPeminatan)) . ',' : '';

        $data = [
            'nama_jurusan' => $this->request->getPost('nama_jurusan'),
            'kode_jurusan' => $this->request->getPost('kode_jurusan'),
            'mapel_peminatan' => $mapelStr,
            'deletable' => 1
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

        $mapelPeminatan = $this->request->getPost('mapel_peminatan');
        $mapelStr = is_array($mapelPeminatan) ? implode(',', array_filter($mapelPeminatan)) . ',' : '';

        $data = [
            'nama_jurusan' => $this->request->getPost('nama_jurusan'),
            'kode_jurusan' => $this->request->getPost('kode_jurusan'),
            'mapel_peminatan' => $mapelStr,
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
        
        // Prevent deleting non-deletable jurusan if flagged
        $jurusan = $db->table('master_jurusan')->where('id_jurusan', $id)->get()->getRow();
        if ($jurusan && isset($jurusan->deletable) && $jurusan->deletable == 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jurusan ini dikunci oleh sistem dan tidak dapat dihapus']);
        }

        $result = $db->table('master_jurusan')->where('id_jurusan', $id)->delete();

        return $this->response->setJSON([
            'status' => (bool) $result,
            'message' => $result ? 'Data berhasil dihapus' : 'Gagal menghapus data'
        ]);
    }

    public function get_data($id = null)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        $db = \Config\Database::connect();
        $jurusan = $db->table('master_jurusan')->where('id_jurusan', $id)->get()->getRow();

        if (!$jurusan) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        $mapelList = [];
        if (!empty($jurusan->mapel_peminatan)) {
            $ids = array_filter(explode(',', $jurusan->mapel_peminatan));
            foreach ($ids as $mid) {
                $mapelList[] = ['id_mapel' => trim($mid)];
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'id_jurusan' => $jurusan->id_jurusan,
                'kode_jurusan' => $jurusan->kode_jurusan ?? $jurusan->singkatan ?? '',
                'nama_jurusan' => $jurusan->nama_jurusan ?? '',
                'mapel_peminatan' => $mapelList
            ]
        ]);
    }
}
