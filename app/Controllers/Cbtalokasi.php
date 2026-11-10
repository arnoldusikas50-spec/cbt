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

        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
            return $this->showError('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini', 403);
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
                // Skip sesi with missing critical data
                if (empty($row->kode_sesi) || empty($row->waktu_mulai) || empty($row->waktu_akhir)) {
                    continue;
                }

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

        return view('cbt/alokasi/data', $data);
    }

    /**
     * Detail page
     */
    public function detail()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
            return $this->showError('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini', 403);
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

        return view('cbt/alokasi/data_detail', $data);
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
                $bank = $db->table('cbt_bank_soal b')
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
                // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
                $this->onlySiswaAktif($siswaBuilder, 'a', 'bi', 'siswa_id');
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
                    $row = [
                        'id_jadwal' => $d->id_jadwal,
                        'id_ruang'  => $d->id_ruang,
                        'id_sesi'   => $d->id_sesi,
                        'id_tp'     => $d->id_tp,
                        'id_smt'    => $d->id_smt,
                        'jam_ke'    => $d->jam_ke,
                    ];

                    // Cari assignment yang sudah ada pada kombinasi jadwal + ruang + sesi
                    $exist = $db->table('cbt_pengawas')
                        ->where('id_tp', $d->id_tp)
                        ->where('id_smt', $d->id_smt)
                        ->where('id_jadwal', $d->id_jadwal)
                        ->where('id_ruang', $d->id_ruang)
                        ->where('id_sesi', $d->id_sesi)
                        ->orderBy('id_pengawas', 'ASC')
                        ->get()->getRow();

                    $idGuru = '';
                    if ($this->inGroup('guru')) {
                        // Guru menambah dirinya ke daftar pengawas alokasi
                        $user = $this->getUser();
                        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $d->id_tp, $d->id_smt);
                        $idGuru = $guru->id_guru ?? '';

                        if ($exist) {
                            $exist_peng = array_values(array_filter(explode(",", (string) ($exist->id_guru ?? ''))));
                            if ($idGuru !== '' && !in_array($idGuru, $exist_peng, true)) {
                                $exist_peng[] = $idGuru;
                            }
                            $idGuru = implode(",", $exist_peng);
                        }
                    } elseif ($exist) {
                        // Admin/Panitia hanya mengatur waktu, penugasan pengawas tetap dipertahankan
                        $idGuru = (string) ($exist->id_guru ?? '');
                    }
                    $row['id_guru'] = $idGuru;

                    if ($exist) {
                        $row['id_pengawas'] = $exist->id_pengawas;
                        $for_update[] = $row;
                    } else {
                        $for_insert[] = $row;
                    }
                }
            }

            $db->transStart();

            if (count($for_insert) > 0) {
                $db->table('cbt_pengawas')->insertBatch($for_insert);
            }

            if (count($for_update) > 0) {
                $db->table('cbt_pengawas')->updateBatch($for_update, 'id_pengawas');
            }

            if (count($update_jadwal) > 0) {
                $db->table('cbt_jadwal')->updateBatch(array_values($update_jadwal), 'id_jadwal');

                // Invalidate jadwal cache untuk setiap jadwal yang jam_ke-nya berubah
                $cache = \Config\Services::cache();
                foreach (array_keys($update_jadwal) as $idJadwal) {
                    $cache->delete("cbt_jadwal_complete_{$idJadwal}");
                }
            }

            $db->transComplete();

            if ($db->transStatus()) {
                $logType = count($for_insert) > 0 ? 3 : (count($for_update) > 0 ? 4 : 0);
                if ($logType > 0) {
                    $this->logModel->saveLog($logType, count($for_insert) > 0 ? 'Menambah alokasi waktu ujian' : 'Mengupdate alokasi waktu ujian');
                }
            }

            return $this->respond($db->transStatus(), $db->transStatus() ? 'Alokasi berhasil disimpan' : 'Gagal menyimpan alokasi', [
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
        $listLevel = [
            "1" => ["1", "2", "3", "4", "5", "6"],       // SD/MI
            "2" => ["7", "8", "9"],                        // SMP/MTS
            "3" => ["10", "11", "12"],                     // SMA/MA/SMK
            "4" => ["10", "11", "12"],                     // SMK
        ];

        // Jenjang kosong/tidak dikenal -> tampilkan semua level supaya tetap bisa dipilih
        $levels = $listLevel[$jenjang] ?? array_map('strval', range(1, 12));

        // Jangan pakai array_merge di sini: key integer akan dinomori ulang,
        // akibatnya level 7/8/9 terkirim sebagai 1/2/3.
        $options = ["0" => "Pilih Level"];
        foreach ($levels as $level) {
            $options[$level] = $level;
        }

        return $options;
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
