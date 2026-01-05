<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\LogModel;

/**
 * Cbtalokasi Controller - Migrated from CI3
 * Handles CBT exam time allocation and supervisor assignment
 */
class Cbtalokasi extends BaseController
{
    protected CbtModel $cbtModel;
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
        $this->logModel = new LogModel();
    }

    /**
     * Get distinct dates from schedule
     */
    private function getDistinctTgl(int $tp, int $smt, $jenis): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('cbt_jadwal a');
        $builder->distinct();
        $builder->select('a.id_jenis, a.tgl_mulai');
        $builder->where('a.id_jenis', $jenis);
        $builder->where('a.id_tp', $tp);
        $builder->where('a.id_smt', $smt);
        $builder->orderBy('a.tgl_mulai', 'ASC');

        $result = $builder->get()->getResult();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->tgl_mulai] = date('d F Y', strtotime($row->tgl_mulai ?? ''));
            }
        }
        return $ret;
    }

    /**
     * Get distinct rooms with sessions
     */
    private function getDistinctRuang(int $tp, int $smt, array $arrKelas): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('cbt_sesi_siswa a');
        $builder->distinct();
        $builder->select('a.ruang_id, a.sesi_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi, c.waktu_mulai, c.waktu_akhir');
        $builder->where('a.tp_id', $tp);
        $builder->where('a.smt_id', $smt);
        $builder->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $builder->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        if (count($arrKelas) > 0) {
            $builder->whereIn('kelas_id', $arrKelas);
        }
        $builder->orderBy('b.nama_ruang', 'ASC');
        $builder->orderBy('c.nama_sesi', 'ASC');
        $result = $builder->get()->getResult();

        $sesis = [];
        if ($result) {
            foreach ($result as $row) {
                if (!isset($sesis[$row->sesi_id])) {
                    $sesis[$row->sesi_id] = (object) [
                        'sesi_id' => $row->sesi_id,
                        'kode_sesi' => $row->kode_sesi,
                        'nama_sesi' => $row->nama_sesi,
                        'waktu_mulai' => $row->waktu_mulai,
                        'waktu_akhir' => $row->waktu_akhir,
                        'ruangan' => []
                    ];
                }
                $sesis[$row->sesi_id]->ruangan[$row->ruang_id] = (object) [
                    'ruang_id' => $row->ruang_id,
                    'kode_ruang' => $row->kode_ruang,
                    'nama_ruang' => $row->nama_ruang
                ];
            }
        }
        return $sesis;
    }

    /**
     * Main index page - Alokasi Waktu
     */
    public function index()
    {
        // Check authentication
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->inGroup('guru')) {
            return $this->showError('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Alokasi Waktu',
            'subjudul' => 'Alokasi Waktu Ujian',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
        ];

        // Get distinct jenis from jadwal
        $id_jenis = $this->cbtModel->getDistinctJenisJadwal($tp->id_tp, $smt->id_smt);
        $ids = [];
        if ($id_jenis && count($id_jenis) > 0) {
            foreach ($id_jenis as $jenis) {
                $ids[] = $jenis->id_jenis;
            }
        }

        if ($ids && count($ids) > 0) {
            $data['jenis'] = $this->cbtModel->getAllJenisUjianByArrJenis($ids);
        } else {
            $data['jenis'] = ['' => 'belum ada jadwal ujian'];
        }

        $jenis_selected = $this->request->getGet('jenis');
        $data['jenis_selected'] = $jenis_selected;

        // Get levels based on school level
        $levels = $this->getLevelsByJenjang($setting->jenjang ?? '1');
        $data['levels'] = $levels;

        // Get jadwal data
        $jadwals = [];
        $pengawas = [];
        $arrKls = [];

        if ($jenis_selected) {
            if ($this->isAdmin()) {
                $jadwals = $this->cbtModel->getAllJadwalByTanggal($tp->id_tp, $smt->id_smt, $jenis_selected);
            } else {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $data['guru'] = $guru;
                $jadwals = $this->cbtModel->getAllJadwalByTanggal($tp->id_tp, $smt->id_smt, $jenis_selected, $guru->id_guru ?? null);
            }
            $pengawas = $this->cbtModel->getAllPengawas($tp->id_tp, $smt->id_smt);
        }

        // Extract class IDs from jadwal
        foreach ($jadwals as $jad) {
            $jad->bank_kelas = $this->maybeUnserialize($jad->bank_kelas ?? []);
            if (is_array($jad->bank_kelas)) {
                foreach ($jad->bank_kelas as $kl) {
                    if (isset($kl->kelas_id) && !in_array($kl->kelas_id, $arrKls)) {
                        $arrKls[] = $kl->kelas_id;
                    }
                }
            }
        }

        // Get sessions
        $sesis = [];
        if (count($arrKls) > 0) {
            $all_sesis = $this->cbtModel->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKls);
            foreach ($all_sesis as $row) {
                if (!isset($sesis[$row->ruang_id])) {
                    $sesis[$row->ruang_id] = (object) [
                        'ruang_id' => $row->ruang_id,
                        'kode_ruang' => $row->kode_ruang,
                        'nama_ruang' => $row->nama_ruang,
                        'sesi' => []
                    ];
                }
                $sesis[$row->ruang_id]->sesi[$row->sesi_id] = (object) [
                    'sesi_id' => $row->sesi_id,
                    'kode_sesi' => $row->kode_sesi,
                    'nama_sesi' => $row->nama_sesi,
                    'waktu_mulai' => $row->waktu_mulai,
                    'waktu_akhir' => $row->waktu_akhir,
                    'istirahat' => $row->istirahat ?? 0,
                    'kelas_id' => $row->kelas_id ?? null,
                    'nama_kelas' => $row->nama_kelas ?? null,
                    'peserta' => $row->peserta ?? 0,
                ];
            }
        }

        $data['sesis'] = array_values($sesis);
        $data['jadwals'] = $jadwals;
        $data['pengawas'] = $pengawas;
        $data['kelas'] = $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruang'] = $this->cbtModel->getAllRuang();
        $data['tanggal'] = $this->getDistinctTgl($tp->id_tp, $smt->id_smt, $jenis_selected);

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/alokasi/data')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Detail page
     */
    public function detail()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->inGroup('guru')) {
            return $this->showError('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Alokasi Waktu',
            'subjudul' => 'Detail Alokasi Waktu Ujian',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
        ];

        // Get distinct jenis from jadwal
        $id_jenis = $this->cbtModel->getDistinctJenisJadwal($tp->id_tp, $smt->id_smt);
        $ids = [];
        if ($id_jenis && count($id_jenis) > 0) {
            foreach ($id_jenis as $jenis) {
                $ids[] = $jenis->id_jenis;
            }
        }

        if ($ids && count($ids) > 0) {
            $data['jenis'] = $this->cbtModel->getAllJenisUjianByArrJenis($ids);
        } else {
            $data['jenis'] = ['' => 'belum ada jadwal ujian'];
        }

        if (!$this->isAdmin()) {
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/alokasi/data_detail')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Get dates by jenis (AJAX)
     */
    public function getTanggal()
    {
        try {
            $jenis = $this->request->getGet('jenis');
            if ($jenis) {
                $jenis = json_decode($jenis);
            }

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $jadwal = $this->getDistinctTgl($tp->id_tp, $smt->id_smt, $jenis);

            return $this->respond(true, '', $jadwal);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Get allocation data (AJAX)
     */
    public function getAlokasi()
    {
        try {
            $jenis_selected = $this->request->getPost('jenis');
            $tanggal_selected = $this->request->getPost('tanggal');

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $db = \Config\Database::connect();

            // Get jadwals
            $builder = $db->table('cbt_jadwal a');
            $builder->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.status, a.jam_ke');
            $builder->where([
                'a.id_tp' => $tp->id_tp,
                'a.id_smt' => $smt->id_smt,
                'a.tgl_mulai' => $tanggal_selected,
                'a.id_jenis' => $jenis_selected
            ]);
            $jadwals = $builder->get()->getResult();

            $arrKelas = [];
            $result = [];
            $all_siswa = [];
            $cbt_siswa = [];

            $data = [
                'today' => date('Y-m-d'),
                'pengawas' => $this->cbtModel->getAllPengawas($tp->id_tp, $smt->id_smt),
                'gurus' => $this->masterModel->getAllGuru(),
                'kelases' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            ];

            if (!$jadwals) {
                $data['sesisiswa'] = $all_siswa;
                $data['cbtsiswa'] = $cbt_siswa;
                $data['ruangs'] = [];
                $data['arr_kelas'] = $arrKelas;
                $data['jadwals_ujian'] = $result;
                return $this->respond(true, '', $data);
            }

            $ids_jadwal = [];
            foreach ($jadwals as $jadwal) {
                $ids_jadwal[] = $jadwal->id_jadwal;

                // Get bank info
                $bank = $db->table('cbt_bank b')
                    ->select('b.id_bank, b.bank_kode, b.bank_kelas, b.soal_agama, b.bank_guru_id, b.bank_mapel_id, m.kode, m.nama_mapel')
                    ->join('master_mapel m', 'm.id_mapel = b.bank_mapel_id', 'left')
                    ->where('b.id_bank', $jadwal->id_bank)
                    ->get()->getRow();

                if ($bank) {
                    $jadwal->bank = $bank;
                    $kelasIds = $this->maybeUnserialize($bank->bank_kelas);
                    if (is_array($kelasIds)) {
                        $jadwal->bank->bank_kelas = array_map('strval', array_column($kelasIds, 'kelas_id'));
                        foreach ($jadwal->bank->bank_kelas as $kls) {
                            if ($kls && !in_array($kls, $arrKelas)) {
                                $arrKelas[] = $kls;
                            }
                        }
                    }
                    $result[] = $jadwal;
                }
            }

            // Sort by mapel
            usort($result, static function ($a, $b) {
                return ($a->bank->bank_mapel_id ?? '') <=> ($b->bank->bank_mapel_id ?? '');
            });

            // Get ruangs
            $ruangs = [];
            if (count($arrKelas) > 0) {
                $ruangs = $this->cbtModel->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKelas);
            }

            // Get siswas
            if (count($arrKelas) > 0) {
                $siswaBuilder = $db->table('cbt_sesi_siswa a');
                $siswaBuilder->select('a.*, s.id_siswa, s.nama, s.nis, s.nisn, s.agama, k.kode_kelas, k.nama_kelas, r.kode_ruang, r.nama_ruang, se.kode_sesi, se.nama_sesi');
                $siswaBuilder->join('master_siswa s', 's.id_siswa = a.siswa_id', 'left');
                $siswaBuilder->join('master_kelas k', 'k.id_kelas = a.kelas_id', 'left');
                $siswaBuilder->join('cbt_ruang r', 'r.id_ruang = a.ruang_id', 'left');
                $siswaBuilder->join('cbt_sesi se', 'se.id_sesi = a.sesi_id', 'left');
                $siswaBuilder->whereIn('a.kelas_id', $arrKelas);
                $siswaBuilder->where(['a.tp_id' => $tp->id_tp, 'a.smt_id' => $smt->id_smt]);
                $siswas = $siswaBuilder->get()->getResult('array');

                if ($siswas) {
                    $ids_siswa = [];
                    foreach ($siswas as $siswa) {
                        if (!in_array($siswa['siswa_id'], $ids_siswa)) {
                            $ids_siswa[] = $siswa['siswa_id'];
                        }
                        $all_siswa[] = $siswa;
                    }

                    // Get cbt_siswa
                    if (count($ids_siswa) > 0 && count($ids_jadwal) > 0) {
                        $cbtBuilder = $db->table('cbt_siswa');
                        $cbtBuilder->select('id_bank, id_cbt_siswa, id_jadwal, id_siswa, lama_ujian, mulai, reset_izin, reset_waktu, selesai, status');
                        $cbtBuilder->whereIn('id_siswa', $ids_siswa);
                        $cbtBuilder->whereIn('id_jadwal', $ids_jadwal);
                        $cbt_siswa = $cbtBuilder->get()->getResult('array');
                    }
                }
            }

            $data['sesisiswa'] = $all_siswa;
            $data['cbtsiswa'] = $cbt_siswa ?: [];
            $data['ruangs'] = array_values($ruangs);
            $data['arr_kelas'] = $arrKelas;
            $data['jadwals_ujian'] = $result;

            return $this->respond(true, '', $data);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Save allocation (AJAX)
     */
    public function saveAlokasi()
    {
        try {
            $input = $this->request->getPost('alokasi');
            if ($input) {
                $input = json_decode($input);
            }

            if (!$input || !is_array($input)) {
                return $this->respond(false, 'Data alokasi tidak valid');
            }

            $db = \Config\Database::connect();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $for_insert = [];
            $for_update = [];
            $update_jadwal = [];

            foreach ($input as $d) {
                // Collect update for cbt_jadwal
                if (isset($d->id_jadwal) && isset($d->jam_ke)) {
                    $update_jadwal[$d->id_jadwal] = [
                        'id_jadwal' => $d->id_jadwal,
                        'jam_ke' => $d->jam_ke
                    ];
                }

                // Only process cbt_pengawas if we have valid pengawas ID
                if (isset($d->id_pengawas) && $d->id_pengawas != "0") {
                    $insert = [
                        'id_pengawas' => $d->id_pengawas,
                        'id_jadwal' => $d->id_jadwal,
                        'id_ruang' => $d->id_ruang,
                        'id_sesi' => $d->id_sesi,
                        'id_tp' => $d->id_tp,
                        'id_smt' => $d->id_smt,
                        'jam_ke' => $d->jam_ke
                    ];

                    // Check if guru
                    if ($this->inGroup('guru')) {
                        $user = $this->getUser();
                        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $d->id_tp, $d->id_smt);

                        $id_pengawas = $d->id_tp . $d->id_smt . $d->id_jadwal . $d->id_ruang . $d->id_sesi;
                        $exist = $db->table('cbt_pengawas')->where('id_pengawas', $id_pengawas)->get()->getRow();

                        if ($exist) {
                            $exist_peng = explode(",", $exist->id_guru ?? '');
                            if (!in_array($guru->id_guru, $exist_peng)) {
                                $exist_peng[] = $guru->id_guru;
                            }
                            $insert['id_guru'] = implode(",", $exist_peng);
                        } else {
                            $insert['id_guru'] = $guru->id_guru ?? '';
                        }
                    }

                    // Check if exists
                    $exists = $db->table('cbt_pengawas')->where('id_pengawas', $d->id_pengawas)->countAllResults() > 0;

                    if ($exists) {
                        $for_update[] = $insert;
                    } else {
                        $for_insert[] = $insert;
                    }
                }
            }

            $updated = 0;

            if (count($for_insert) > 0) {
                $updated1 = $db->table('cbt_pengawas')->insertBatch($for_insert);
                if ($updated1) {
                    $updated++;
                    $this->logModel->saveLog(3, 'Menambah alokasi waktu ujian');
                }
            }

            if (count($for_update) > 0) {
                $updated2 = $db->table('cbt_pengawas')->updateBatch($for_update, 'id_pengawas');
                if ($updated2) {
                    $updated++;
                    $this->logModel->saveLog(4, 'Mengupdate alokasi waktu ujian');
                }
            }

            if (count($update_jadwal) > 0) {
                $updated3 = $db->table('cbt_jadwal')->updateBatch(array_values($update_jadwal), 'id_jadwal');
                if ($updated3) {
                    $updated++;
                }
            }

            return $this->respond($updated > 0, $updated > 0 ? 'Alokasi berhasil disimpan' : 'Tidak ada perubahan', [
                'insert' => count($for_insert),
                'update' => count($for_update)
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Helper: Get levels by jenjang
     */
    private function getLevelsByJenjang(string $jenjang): array
    {
        if ($jenjang == "1") {
            return ["0" => "Pilih Level", "1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5", "6" => "6"];
        } elseif ($jenjang == "2") {
            return ["0" => "Pilih Level", "7" => "7", "8" => "8", "9" => "9"];
        } elseif ($jenjang == "3") {
            return ["0" => "Pilih Level", "10" => "10", "11" => "11", "12" => "12"];
        }
        return ["0" => "Pilih Level"];
    }

    /**
     * Helper: Maybe unserialize
     */
    private function maybeUnserialize($data)
    {
        if (is_string($data)) {
            $unserialized = @unserialize($data);
            if ($unserialized !== false || $data === 'b:0;') {
                return $unserialized;
            }
            // Try JSON decode
            $decoded = json_decode($data);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $data;
    }
}
