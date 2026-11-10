<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;

/**
 * Datamapel Controller - Ported from CI3
 */
class Datamapel extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;
    protected DropdownModel $dropdownModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
        $this->dropdownModel = new DropdownModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $db = \Config\Database::connect();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $kelompok_utama = $this->masterModel->getDataKelompokMapel();
        $sub_kelompok = $this->masterModel->getDataSubKelompokMapel();

        $kelases = $db->table('master_kelas')
            ->where('id_tp', $tp->id_tp)
            ->where('id_smt', $smt->id_smt)
            ->orderBy('level_id', 'ASC')
            ->orderBy('nama_kelas', 'ASC')
            ->get()->getResult();

        $gurus = $db->table('master_guru')
            ->select('id_guru, nama_guru, kode_guru')
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('nama_guru', 'ASC')
            ->get()->getResult();

        $totalMapel = $db->table('master_mapel')->countAllResults();
        $syncedMapel = $db->table('master_mapel')
            ->where('dapo_id IS NOT NULL', null, false)
            ->where('dapo_id !=', '')
            ->countAllResults();

        $data = [
            'user'           => $this->getUser(),
            'judul'          => 'Mata Pelajaran',
            'subjudul'       => 'Daftar Mata Pelajaran & Pembagian Kelas',
            'setting'        => $setting,
            'tp'             => $this->dashboardModel->getTahun(),
            'tp_active'      => $tp,
            'smt'            => $this->dashboardModel->getSemester(),
            'smt_active'     => $smt,
            'kategori'       => ['WAJIB', 'AGAMA (Kemenag)', 'PEMINATAN AKADEMIK', 'AKADEMIK KEJURUAN', 'LINTAS MINAT', 'MULOK'],
            'kelompok_mapel' => $kelompok_utama + $sub_kelompok,
            'kelompok'       => $this->dropdownModel->getDataKelompokMapel(),
            'status'         => ['0' => 'Nonaktif', '1' => 'Aktif'],
            'kelases'        => $kelases,
            'gurus'          => $gurus,
            'stats'          => [
                'total'  => $totalMapel,
                'synced' => $syncedMapel,
                'local'  => $totalMapel - $syncedMapel,
            ]
        ];

        return view('master/mapel/data', $data);
    }

    public function getdatakelompok()
    {
        $id_parent = 0;
        $db = \Config\Database::connect();
        $builder = $db->table('master_kelompok_mapel')
            ->where('id_parent', $id_parent)
            ->orderBy('kode_kel_mapel', 'ASC');

        $data = $builder->get()->getResult();
        return $this->response->setJSON([
            'draw' => $this->request->getPost('draw'),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }

    public function getdatasubkelompok()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('master_kelompok_mapel')
            ->where('id_parent <>', 0)
            ->orderBy('kode_kel_mapel', 'ASC');

        $data = $builder->get()->getResult();
        return $this->response->setJSON([
            'draw' => $this->request->getPost('draw'),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }

    public function read()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('master_mapel');

        $dapoFilter = $this->request->getPost('dapodik_filter');
        if ($dapoFilter === 'synced') {
            $builder->where('dapo_id IS NOT NULL', null, false)->where('dapo_id !=', '');
        } else if ($dapoFilter === 'local') {
            $builder->groupStart()
                ->where('dapo_id IS NULL', null, false)
                ->orWhere('dapo_id', '')
                ->groupEnd();
        }

        $kumerFilter = $this->request->getPost('kumer_filter');
        if (!empty($kumerFilter) && $kumerFilter !== 'all') {
            if ($kumerFilter === 'pilihan') {
                $builder->groupStart()
                    ->whereIn('kelompok', ['C', 'LM', 'PILIHAN'])
                    ->orLike('nama_mapel', 'Pilihan')
                    ->orLike('nama_mapel', 'Lintas Minat')
                    ->groupEnd();
            } elseif ($kumerFilter === 'mulok') {
                $builder->groupStart()
                    ->where('kelompok', 'MULOK')
                    ->orLike('nama_mapel', 'Muatan Lokal')
                    ->groupEnd();
            } elseif ($kumerFilter === 'wajib') {
                $builder->groupStart()
                    ->whereNotIn('kelompok', ['C', 'LM', 'PILIHAN', 'MULOK'])
                    ->notLike('nama_mapel', 'Pilihan')
                    ->notLike('nama_mapel', 'Lintas Minat')
                    ->notLike('nama_mapel', 'Muatan Lokal')
                    ->groupEnd();
            }
        }

        $builder->orderBy('urutan_tampil', 'ASC')
            ->orderBy('nama_mapel', 'ASC');

        $data = $builder->get()->getResult();

        foreach ($data as &$row) {
            $row->deletable = '1';
            $row->is_dapo = (!empty($row->dapo_id));
            $kel = strtoupper(trim($row->kelompok ?? ''));
            // Pilihan: Kelompok C (Peminatan/Pilihan), LM (Lintas Minat), atau nama mapel
            if (in_array($kel, ['C', 'LM', 'PILIHAN'])
                || mb_stripos($row->nama_mapel, 'Pilihan') !== false
                || mb_stripos($row->nama_mapel, 'Lintas Minat') !== false) {
                $row->kumer_type = 'PILIHAN';
            // Mulok: Kelompok MULOK atau nama mapel
            } elseif ($kel === 'MULOK'
                || mb_stripos($row->nama_mapel, 'Muatan Lokal') !== false) {
                $row->kumer_type = 'MULOK';
            } else {
                // Wajib: Kelompok A (umum), B (peminatan umum), UMUM, PAI, Kelom, dll
                $row->kumer_type = 'WAJIB';
            }
        }

        return $this->response->setJSON([
            'draw'            => $this->request->getPost('draw'),
            'recordsTotal'    => count($data),
            'recordsFiltered' => count($data),
            'data'            => $data
        ]);
    }

    public function addkelompokmapel()
    {
        $id = $this->request->getPost('id_kel_mapel');
        $nama = trim((string)$this->request->getPost('nama_kel_mapel'));
        $kode = trim((string)$this->request->getPost('kode_kel_mapel'));
        $kategori = trim((string)$this->request->getPost('kategori'));
        $id_parent = (int)$this->request->getPost('id_parent');

        if (empty($nama) || empty($kode)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Nama dan Kode kelompok mapel harus diisi'
            ]);
        }

        // Fallback category from parent if creating sub-kelompok and category is empty
        if ($id_parent > 0 && empty($kategori)) {
            $db = \Config\Database::connect();
            $parent = $db->table('master_kelompok_mapel')->where('id_kel_mapel', $id_parent)->get()->getRow();
            if ($parent && !empty($parent->kategori)) {
                $kategori = $parent->kategori;
            }
        }

        $insert = [
            'nama_kel_mapel' => $nama,
            'kode_kel_mapel' => $kode,
            'kategori'       => $kategori,
            'id_parent'      => $id_parent,
        ];

        if (!empty($id)) {
            $res = $this->masterModel->updateData('master_kelompok_mapel', $insert, 'id_kel_mapel', $id);
        } else {
            $res = $this->masterModel->create('master_kelompok_mapel', $insert);
        }

        return $this->response->setJSON([
            'status'  => (bool)$res,
            'message' => $res ? 'Data kelompok mapel berhasil disimpan' : 'Gagal menyimpan data kelompok mapel'
        ]);
    }

    public function create()
    {
        $setting = $this->dashboardModel->getSetting();
        $namMapel = trim((string) $this->request->getPost('nama_mapel'));
        $kodeMapel = trim((string) $this->request->getPost('kode_mapel'));

        if (empty($namMapel) || empty($kodeMapel)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama dan Kode Mapel harus diisi']);
        }

        $insert = [
            'nama_mapel'    => $namMapel,
            'kode'          => $kodeMapel,
            'kelompok'      => $this->request->getPost('kelompok') ?? '-',
            'urutan_tampil' => (int) ($this->request->getPost('urutan_tampil') ?? 1),
            'jenjang'       => $setting ? (int) $setting->jenjang : 0,
            'status'        => $this->request->getPost('status') ?? '1',
        ];

        $res = $this->masterModel->create('master_mapel', $insert);
        $newId = $res ? \Config\Database::connect()->insertID() : 0;
        return $this->response->setJSON([
            'status'  => (bool) $res,
            'message' => $res ? 'Mata pelajaran berhasil ditambahkan' : 'Gagal menyimpan mata pelajaran',
            'id'      => $newId,
        ]);
    }

    public function save()
    {
        return $this->create();
    }

    public function update()
    {
        $id = $this->request->getPost('id_mapel');
        $data = [
            "nama_mapel"    => $this->request->getPost('nama_mapel'),
            "kode"          => $this->request->getPost('kode_mapel'),
            "kelompok"      => $this->request->getPost('kelompok'),
            "urutan_tampil" => $this->request->getPost('urutan_tampil'),
            "status"        => $this->request->getPost('status')
        ];

        $res = $this->masterModel->updateData('master_mapel', $data, 'id_mapel', $id);
        return $this->response->setJSON(['status' => $res, 'message' => $res ? 'Berhasil' : 'Gagal']);
    }

    public function hapuskelompok()
    {
        $id = $this->request->getPost('id_kel');
        $res = $this->masterModel->hapus('master_kelompok_mapel', $id, 'id_kel_mapel');
        return $this->response->setJSON(['status' => $res, 'message' => $res ? 'Berhasil' : 'Gagal']);
    }

    public function delete()
    {
        $ids = $this->request->getPost('checked');
        if (!$ids) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data yang dipilih']);
        }

        $res = $this->masterModel->hapus('master_mapel', $ids, 'id_mapel');
        return $this->response->setJSON(['status' => $res, 'total' => count($ids)]);
    }

    public function aktifkan($id)
    {
        $res = $this->masterModel->updateMapel($id, ['status' => '1']);
        return $this->response->setJSON($res);
    }

    public function toggle_status()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id_mapel');
        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID Mapel tidak valid']);
        }

        // Read current status from DB to avoid stale UI state
        $db   = \Config\Database::connect();
        $row  = $db->table('master_mapel')->select('status')->where('id_mapel', $id)->get()->getRow();
        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mapel tidak ditemukan']);
        }
        $newStatus = ($row->status == 1) ? '0' : '1';

        $res = $this->masterModel->updateData('master_mapel', ['status' => $newStatus], 'id_mapel', $id);
        return $this->response->setJSON([
            'status'     => (bool) $res,
            'new_status' => $newStatus,
            'message'    => $res ? 'Status mapel berhasil diperbarui' : 'Gagal memperbarui status',
        ]);
    }

    public function get_mapel_kelas()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $idKelas = (int)$this->request->getPost('id_kelas');
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$idKelas) {
            return $this->response->setJSON(['status' => false, 'message' => 'Pilih kelas terlebih dahulu']);
        }

        $detail = $this->masterModel->getMapelPerKelasDetail((int)$tp->id_tp, (int)$smt->id_smt, $idKelas);
        return $this->response->setJSON([
            'status' => true,
            'id_kelas' => $idKelas,
            'detail' => $detail
        ]);
    }

    public function save_mapel_kelas()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $idKelas = (int)$this->request->getPost('id_kelas');
        $idMapel = (int)$this->request->getPost('id_mapel');
        $idGuru = (int)$this->request->getPost('id_guru');
        $isAssign = (bool)$this->request->getPost('is_assign');

        if (!$idKelas || !$idMapel || !$idGuru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data kelas, mapel, atau guru tidak valid']);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $res = $this->masterModel->toggleTeacherMapelKelas((int)$tp->id_tp, (int)$smt->id_smt, $idKelas, $idMapel, $idGuru, $isAssign);

        return $this->response->setJSON([
            'status' => $res,
            'message' => $res ? ($isAssign ? 'Mapel & Guru Pengampu berhasil dialokasikan ke kelas' : 'Pengalokasian mapel berhasil dihapus dari kelas') : 'Gagal memperbarui alokasi mapel kelas'
        ]);
    }
}
