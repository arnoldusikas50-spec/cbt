<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\KelasModel;
use App\Models\DropdownModel;
use App\Models\LogModel;
use App\Models\MasterModel;

class Cbtjadwal extends BaseController
{
    protected $cbtModel;
    protected $dashboardModel;
    protected $kelasModel;
    protected $dropdownModel;
    protected $logModel;
    protected $masterModel;
    protected $db;
    protected $LOG_TYPE_ADD = 3;
    protected $LOG_TYPE_EDIT = 4;
    protected $LOG_TYPE_DELETE = 5;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
        $this->kelasModel = new KelasModel();
        $this->dropdownModel = new DropdownModel();
        $this->logModel = new LogModel();
        $this->masterModel = new MasterModel();
        $this->db = \Config\Database::connect();
    }

    private function getJadwalTerpakai($id_jadwal = null)
    {
        $builder = $this->db->table('cbt_siswa');
        $builder->select('id_bank,id_jadwal,id_siswa');
        if ($id_jadwal != null) {
            $builder->where('id_jadwal', $id_jadwal);
        }
        $result = $builder->get()->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_jadwal][$row->id_siswa] = $row;
        }
        return $ret;
    }

    private function getJadwalAlokasi($id_tp, $id_smt)
    {
        // 1. Check allocation in cbt_pengawas (Room & Session distribution)
        $builder = $this->db->table('cbt_pengawas');
        $builder->select('id_jadwal, COUNT(id_pengawas) as jumlah_alokasi, GROUP_CONCAT(DISTINCT jam_ke) as jam_ke');
        $builder->where('id_tp', (string)$id_tp);
        $builder->where('id_smt', (string)$id_smt);
        $builder->groupBy('id_jadwal');
        $result = $builder->get()->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[(int)$row->id_jadwal] = (object) [
                'jumlah_alokasi' => $row->jumlah_alokasi,
                'jam_ke' => $row->jam_ke
            ];
        }

        // 2. Check allocation in cbt_jadwal (Time slot only)
        $builder2 = $this->db->table('cbt_jadwal');
        $builder2->select('id_jadwal, jam_ke');
        $builder2->where('id_tp', (string)$id_tp);
        $builder2->where('id_smt', (string)$id_smt);
        $builder2->where("jam_ke IS NOT NULL");
        $builder2->where("CAST(jam_ke AS UNSIGNED) >", 0);
        $jadwals = $builder2->get()->getResult();

        foreach ($jadwals as $j) {
            if (!isset($ret[(int)$j->id_jadwal])) {
                $ret[(int)$j->id_jadwal] = (object) [
                    'jumlah_alokasi' => 0,
                    'jam_ke' => $j->jam_ke
                ];
            }
        }

        return $ret;
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        // Only allow admin, panitia with jabatan 'panitia', or guru
        // Pengawas (jabatan 'pengawas') should not access this
        if ($this->isPanitia() && !$this->hasPanitiaJabatan()) {
            return redirect()->to('dashboard')->with('error', 'Akses ditolak. Hanya Administrator atau Panitia yang dapat mengakses Jadwal.');
        }

        if (!$this->isAdmin() && !$this->isPanitia() && !$this->inGroup('guru')) {
            return redirect()->to('dashboard')->with('error', 'Akses Terlarang');
        }

        try {
            $user = $this->authModel->user($this->getUserId());
            $setting = $this->dashboardModel->getSetting();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return redirect()->to('dashboard')->with('error', 'Tahun Pelajaran atau Semester belum diatur.');
            }

            $lvl = $this->request->getGet('level');
            $level = $lvl == null ? '0' : $lvl;

            $data = [
                'user' => $user,
                'judul' => 'Jadwal Penilaian',
                'subjudul' => 'Jadwal Penilaian',
                'setting' => $setting
            ];

            $data['tp'] = $this->dashboardModel->getTahun();
            $data['tp_active'] = $tp;
            $data['smt'] = $this->dashboardModel->getSemester();
            $data['smt_active'] = $smt;
            $data['level'] = $level;

            $mode = $this->request->getGet('mode');
            $type = $this->request->getGet('type');
            $data['mode'] = $mode == null ? '1' : $mode;

            // Load extra data
            $data['ruangs'] = $this->cbtModel->getAllRuang();
            $data['sesis'] = $this->dropdownModel->getAllSesi();
            $data['jenis'] = $this->cbtModel->getAllJenisUjian();
            $data['jmlIst'] = [];
            $data['jmlMapel'] = [];

            // Selalu load data alokasi dan terpakai untuk status
            $terpakai = $this->getJadwalTerpakai();
            $jadwal_terpakai = [];
            foreach ($terpakai as $idj => $rows) {
                $jadwal_terpakai[$idj] = count($rows);
            }
            $data['total_siswa'] = $jadwal_terpakai;
            $data['alokasi_jadwal'] = $this->getJadwalAlokasi($tp->id_tp, $smt->id_smt);

            if ($mode) {
                $data['ada_ujian'] = $this->cbtModel->getDataJadwalByTgl(date('Y-m-d'));
            }

            $levels_obj = $this->masterModel->getLevelsByJenjang($setting->jenjang);
            $levels = [];
            foreach ($levels_obj as $lvlItem) {
                $levels[$lvlItem->level] = $lvlItem->level;
            }
            $data['levels'] = $levels;
            $data['kelas'] = $this->cbtModel->getKelas($tp->id_tp, $smt->id_smt);
            $data['rekaps'] = [];

            if ($this->isAdmin() || $this->isPanitia()) {
                $data['gurus'] = $this->dropdownModel->getAllGuru();
                // Safe check for mapels to prevent 500 if DB is empty
                $mapelsData = $this->dropdownModel->getAllMapel();
                $data['mapels'] = $mapelsData ?? [];

                $data['filters'] = ['' => 'Filter berdasarkan:', '0' => 'Semua', '1' => 'Guru', '2' => 'Mapel', '3' => 'Level'];
                $type = $this->request->getGet('type') ?? '0';
                $data['id_filter'] = $type;
                $jadwals = [];

                if ($type == '0') {
                    $jadwals = $this->cbtModel->getAllDataJadwal($tp->id_tp, $smt->id_smt);
                    $data['id_guru'] = null;
                    $data['id_mapel'] = null;
                    $data['id_level'] = null;
                } elseif ($type == '1') {
                    $id_guru = $this->request->getGet('id');
                    $data['id_guru'] = $id_guru;
                    $jadwals = $this->cbtModel->getAllDataJadwal($tp->id_tp, $smt->id_smt, $id_guru);
                    $data['id_mapel'] = '';
                    $data['id_level'] = '';
                } elseif ($type == '2') {
                    $id_mapel = $this->request->getGet('id');
                    $data['id_mapel'] = $id_mapel;
                    $jadwals = $this->cbtModel->getAllDataJadwal($tp->id_tp, $smt->id_smt, null, $id_mapel);
                    $data['id_guru'] = '';
                    $data['id_level'] = '';
                } elseif ($type == '3') {
                    $id_level = $this->request->getGet('id');
                    $data['id_level'] = $id_level;
                    $jadwals = $this->cbtModel->getAllDataJadwal($tp->id_tp, $smt->id_smt, null, null, $id_level);
                    $data['id_guru'] = '';
                    $data['id_mapel'] = '';
                } else {
                    $data['id_guru'] = null;
                    $data['id_mapel'] = null;
                    $data['id_level'] = null;
                }

                if ($jadwals && count($jadwals) > 0) {
                    foreach ($jadwals as $title => $arrjadwal) {
                        if ($arrjadwal) {
                            foreach ($arrjadwal as $lvl => $sjadwal) {
                                if ($sjadwal) {
                                    foreach ($sjadwal as $keys => $jadwal) {
                                        $jadwal->bank_kelas = maybe_unserialize($jadwal->bank_kelas ?? '[]');
                                    }
                                }
                            }
                        }
                    }
                }
                $data['jadwals'] = $jadwals;

                return view('cbt/jadwal/data', $data);
            } else {
                $guru = $this->dashboardModel->getDataGuruByUserId($this->getUserId(), $tp->id_tp, $smt->id_smt);
                if (!$guru) {
                    return redirect()->to('dashboard')->with('error', 'Data Guru tidak ditemukan.');
                }
                $data['guru'] = $guru;

                $mapel_guru = $this->kelasModel->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
                $mapel = ($mapel_guru && isset($mapel_guru->mapel_kelas)) ? maybe_unserialize($mapel_guru->mapel_kelas) : [];
                $arrMapel = [];
                if ($mapel) {
                    foreach ($mapel as $m) {
                        $idM = is_object($m) ? ($m->id_mapel ?? null) : (is_array($m) ? ($m['id_mapel'] ?? null) : null);
                        if (!$idM) continue;
                        $namaM = is_object($m) ? ($m->nama_mapel ?? null) : (is_array($m) ? ($m['nama_mapel'] ?? null) : null);
                        if (!$namaM) {
                            $namaM = $this->masterModel->getMapelById((int)$idM)?->nama_mapel ?? '-';
                        }
                        $arrMapel[$idM] = $namaM;
                    }
                }
                $data['mapels'] = $arrMapel;

                $data['filters'] = ['' => 'Filter berdasarkan:', '0' => 'Semua', '2' => 'Mapel', '3' => 'Level'];
                $type = $this->request->getGet('type') ?? '0';
                $data['id_filter'] = $type;
                $jadwals = [];

                if ($type == '0') {
                    $jadwals = $this->cbtModel->getAllDataJadwal($tp->id_tp, $smt->id_smt, $guru->id_guru);
                    $data['id_guru'] = null;
                    $data['id_mapel'] = null;
                    $data['id_level'] = null;
                } elseif ($type == '2') {
                    $id_mapel = $this->request->getGet('id');
                    $data['id_mapel'] = $id_mapel;
                    $jadwals = $this->cbtModel->getAllDataJadwal($tp->id_tp, $smt->id_smt, $guru->id_guru, $id_mapel);
                    $data['id_guru'] = '';
                    $data['id_level'] = '';
                } elseif ($type == '3') {
                    $id_level = $this->request->getGet('id');
                    $data['id_level'] = $id_level;
                    $jadwals = $this->cbtModel->getAllDataJadwal($tp->id_tp, $smt->id_smt, $guru->id_guru, null, $id_level);
                    $data['id_guru'] = '';
                    $data['id_mapel'] = '';
                } else {
                    $data['id_guru'] = null;
                    $data['id_mapel'] = null;
                    $data['id_level'] = null;
                }

                if ($jadwals && count($jadwals) > 0) {
                    foreach ($jadwals as $title => $arrjadwal) {
                        if ($arrjadwal) {
                            foreach ($arrjadwal as $lvl => $sjadwal) {
                                if ($sjadwal) {
                                    foreach ($sjadwal as $keys => $jadwal) {
                                        $jadwal->bank_kelas = maybe_unserialize($jadwal->bank_kelas ?? '[]');
                                    }
                                }
                            }
                        }
                    }
                }
                $data['jadwals'] = $jadwals;
                return view('cbt/jadwal/data', $data);
            }
        } catch (\Throwable $th) {
            log_message('error', '[Cbtjadwal::index] ' . $th->getMessage());
            return redirect()->to('dashboard')->with('error', 'Terjadi kesalahan saat memuat jadwal: ' . $th->getMessage());
        }
    }

    public function add($id_jadwal = 0)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $enable = $this->request->getGet('enable');
        $user = $this->authModel->user($this->getUserId());
        $data = [
            'user' => $user,
            'judul' => $id_jadwal == 0 ? 'Tambah Jadwal Ujian' : 'Edit Jadwal Ujian',
            'subjudul' => 'Jadwal Ujian',
            'setting' => $this->dashboardModel->getSetting()
        ];

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $data['tp'] = $this->dashboardModel->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboardModel->getSemester();
        $data['smt_active'] = $smt;

        if ($id_jadwal == 0) {
            // Dummy implementation of dummyJadwal
            $jadwal = (object) [
                'id_jadwal' => '0',
                'id_tp' => $tp->id_tp,
                'id_smt' => $smt->id_smt,
                'id_bank' => '',
                'id_jenis' => '',
                'tgl_mulai' => date('Y-m-d'),
                'tgl_selesai' => date('Y-m-d'),
                'durasi_ujian' => '60',
                'pengawas' => [],
                'acak_soal' => '1',
                'acak_opsi' => '1',
                'token' => '0',
                'hasil_tampil' => '0',
                'reset_login' => '0',
                'status' => '1',
                'rekap' => '0',
                'jam_ke' => '0',
                'jarak' => '0',
                'pelanggaran' => '0',
                'enable_remedial' => '1',  // Default enable remedial
                'max_remedial' => 2        // Default batas remedial 2x
            ];
            $jadwal->bank_kelas = [];
        } else {
            $jadwal = $this->cbtModel->getJadwal($id_jadwal);
            if ($jadwal) {
                $jadwal->bank_kelas = maybe_unserialize($jadwal->bank_kelas ?? '[]');
                // Set default enable_remedial if not exists
                if (!isset($jadwal->enable_remedial)) {
                    $jadwal->enable_remedial = '1';
                }
                // Set default max_remedial if not exists
                if (!isset($jadwal->max_remedial) || $jadwal->max_remedial === '') {
                    $jadwal->max_remedial = 2;
                }
            } else {
                return redirect()->to('cbtjadwal');
            }
        }

        $data['jadwal'] = $jadwal;
        $gurus = $this->dropdownModel->getAllGuru();
        $data['ruangs'] = $this->cbtModel->getAllRuang();
        $data['sesis'] = $this->cbtModel->getAllSesi();

        $jenis = $this->cbtModel->getAllJenisUjian();
        $jenisArr = [];
        foreach ($jenis as $j) {
            $jenisArr[$j->id_jenis] = $j->nama_jenis;
        }
        $data['jenis'] = $jenisArr;
        $data['active_jenis_id'] = $this->cbtModel->getActiveJenisId();
        $data['kelas'] = $this->cbtModel->getKelas($tp->id_tp, $smt->id_smt);
        $data['disable_opsi'] = $enable != null && $enable == 1;

        if ($this->isAdmin() || $this->isPanitia()) {
            $data['guru'] = $gurus;
            $data['mapel'] = $this->dropdownModel->getAllMapel();
            return view('cbt/jadwal/add', $data);
        } else {
            $guru = $this->dashboardModel->getDataGuruByUserId($this->getUserId(), $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $mapel_guru = $this->kelasModel->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
            $arrMapel = [];
            if ($mapel) {
                foreach ($mapel as $m) {
                    $idM = is_object($m) ? ($m->id_mapel ?? null) : (is_array($m) ? ($m['id_mapel'] ?? null) : null);
                    if (!$idM) continue;
                    $namaM = is_object($m) ? ($m->nama_mapel ?? null) : (is_array($m) ? ($m['nama_mapel'] ?? null) : null);
                    if (!$namaM) {
                        $namaM = $this->masterModel->getMapelById((int)$idM)?->nama_mapel ?? '-';
                    }
                    $arrMapel[$idM] = $namaM;
                }
            }
            $data['mapel'] = $arrMapel;
            return view('cbt/jadwal/add', $data);
        }
    }

    public function getBankMapel($id_mapel)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        if ($this->isAdmin() || $this->isPanitia()) {
            $banks = $this->cbtModel->getAllBankSoalByMapel($tp->id_tp, $smt->id_smt, $id_mapel);
        } else {
            $user = $this->authModel->user($this->getUserId());
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $banks = $this->cbtModel->getAllBankSoalByMapel($tp->id_tp, $smt->id_smt, $id_mapel, $guru->id_guru);
        }
        
        // Get banks yang sudah punya jadwal (kecuali jadwal yang sedang diedit)
        $id_jadwal_edit = $this->request->getGet('id_jadwal') ?? '0';
        $builder = $this->db->table('cbt_jadwal');
        $builder->select('id_bank, id_jadwal');
        $builder->where('id_tp', $tp->id_tp);
        $builder->where('id_smt', $smt->id_smt);
        $builder->where('id_bank !=', '0');
        if ($id_jadwal_edit != '0') {
            $builder->where('id_jadwal !=', $id_jadwal_edit);
        }
        $jadwals = $builder->get()->getResult();
        
        $digunakan = [];
        foreach ($jadwals as $j) {
            $digunakan[] = $j->id_bank;
        }
        
        $filtered = [];
        $kelas = [];
        $bank_status = [];
        foreach ($banks as $key => $bank) {
            $kelas[$key] = maybe_unserialize($bank->bank_kelas ?? '[]');
            $filtered[$key] = $bank->bank_kode;
            $bank_status[$key] = $bank->status_soal ?? '0';
        }
        $all_kelas = $this->cbtModel->getKelas($tp->id_tp, $smt->id_smt);
        return $this->response->setJSON(['bank' => $filtered, 'jadwal' => $digunakan, 'kelas' => $kelas, 'all_kelas' => $all_kelas, 'status' => $bank_status]);
    }

    public function saveJadwal()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $res = $this->cbtModel->saveJadwalUjian($tp->id_tp, $smt->id_smt);
        $id = $this->request->getPost('id_jadwal');

        $user = $this->authModel->user($this->getUserId());

        if ($res['success']) {
            $action = $id ? 'mengedit' : 'menambah';
            $this->logModel->saveLog($id ? $this->LOG_TYPE_EDIT : $this->LOG_TYPE_ADD, "$action jadwal pelajaran");
        }

        return $this->response->setJSON($res);
    }

    public function getToken($id_jadwal)
    {
        $jadwal = $this->db->table('cbt_jadwal')
            ->select('token_value')
            ->where('id_jadwal', $id_jadwal)
            ->get()->getRow();
        return $this->response->setJSON([
            'token' => $jadwal->token_value ?? '',
        ]);
    }

    public function aktifkan_jadwal()
    {
        $method = $this->request->getPost('method');
        $id = $this->request->getPost('id_jadwal');
        $stat = $method == '1' ? '0' : '1';

        $this->db->table('cbt_jadwal')->where('id_jadwal', $id)->update(['status' => $stat]);

        $this->logModel->saveLog(
            $stat == '1' ? $this->LOG_TYPE_ADD : $this->LOG_TYPE_EDIT,
            ($stat == '1' ? 'Mengaktifkan' : 'Menonaktifkan') . ' jadwal ujian'
        );

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Toggle remedial status for a jadwal
     */
    public function toggle_remedial()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_jadwal');
        $enable = $this->request->getPost('enable'); // '1' or '0'

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID jadwal tidak valid']);
        }

        // Ensure column exists
        if (!$this->db->fieldExists('enable_remedial', 'cbt_jadwal')) {
            // Add column if not exists
            $forge = \Config\Database::forge();
            $forge->addColumn('cbt_jadwal', [
                'enable_remedial' => [
                    'type' => 'VARCHAR',
                    'constraint' => 1,
                    'default' => '1',
                    'null' => true
                ]
            ]);
        }

        $this->db->table('cbt_jadwal')->where('id_jadwal', $id)->update(['enable_remedial' => $enable]);

        $this->logModel->saveLog(
            $this->LOG_TYPE_EDIT,
            ($enable == '1' ? 'Mengaktifkan' : 'Menonaktifkan') . ' remedial untuk jadwal ujian'
        );

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Status remedial ' . ($enable == '1' ? 'diaktifkan' : 'dinonaktifkan')
        ]);
    }

    public function deleteJadwal()
    {
        $id = $this->request->getVar('id_jadwal');
        $jadwal_dikerjakan = $this->getJadwalTerpakai($id);
        $terpakai = isset($jadwal_dikerjakan[$id]) && count($jadwal_dikerjakan[$id]) > 0;

        $rekap_jadwal = $this->db->table('cbt_rekap_jadwal')->where('id_jadwal', $id)->countAllResults();

        if ($terpakai && $rekap_jadwal == 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hasil Ujian belum direkap']);
        }

        $this->db->transStart();
        $this->db->table('cbt_jadwal')->where('id_jadwal', $id)->delete();
        $this->db->table('cbt_siswa')->where('id_jadwal', $id)->delete();
        $this->db->table('cbt_pengawas')->where('id_jadwal', $id)->delete();
        $this->db->transComplete();

        if ($this->db->transStatus()) {
            $this->logModel->saveLog($this->LOG_TYPE_DELETE, 'menghapus jadwal ujian');
            return $this->response->setJSON(['status' => true, 'message' => 'berhasil']);
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal Ujian gagal dihapus']);
        }
    }

    public function deleteAllJadwal()
    {
        $checked = $this->request->getPost('checked');
        $arrId = json_decode($checked ?? '[]');
        if (!$arrId)
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data dipilih']);

        $jadwals = $this->cbtModel->getJadwalByArrId($arrId);
        $jadwal_dikerjakan = $this->getJadwalTerpakai(); // Get all

        // Check rekap
        $backuped = $this->db->table('cbt_rekap_jadwal')->whereIn('id_jadwal', $arrId)->countAllResults();

        $digunakan = 0;
        foreach ($jadwals as $jadwal) {
            if (isset($jadwal_dikerjakan[$jadwal->id_jadwal]) && count($jadwal_dikerjakan[$jadwal->id_jadwal]) > 0) {
                $digunakan++;
            }
        }

        if ($digunakan > 0 && $backuped > 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ada hasil Ujian yang belum direkap']);
        }

        $this->db->transStart();
        $this->db->table('cbt_jadwal')->whereIn('id_jadwal', $arrId)->delete();
        $this->db->table('cbt_siswa')->whereIn('id_jadwal', $arrId)->delete();
        $this->db->table('cbt_pengawas')->whereIn('id_jadwal', $arrId)->delete();
        $this->db->transComplete();

        if ($this->db->transStatus()) {
            $this->logModel->saveLog($this->LOG_TYPE_DELETE, 'menghapus semua jadwal ujian');
            return $this->response->setJSON(['status' => true, 'message' => 'berhasil']);
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus beberapa data']);
        }
    }

    public function get_all_norekap()
    {
        // Placeholder for rekap functionality
        return $this->response->setJSON(['success' => true, 'rekap' => [], 'soal_siswa' => []]);
    }
}
