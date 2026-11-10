<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;

class Cbtrekap extends BaseController
{
    protected $cbt;
    protected $dashboard;
    protected $master;

    public function __construct()
    {
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->master = new MasterModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        if (!$this->isAdmin() && !$this->inGroup('guru')) {
            return $this->showError('Hanya Administrator dan Guru yang dapat mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Rekap Hasil Penilaian',
            'subjudul' => 'Penilaian',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'is_admin' => $this->isAdmin(),
            'rekaps' => $this->cekRekapSiswa(),
            'session_id' => session_id()
        ];

        if (!$this->isAdmin()) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;

            // Fetch mapel taught by guru to determine class filter
            // Logic adapted from CI3: Get classes where guru teaches mapel
            // Simplification: Views usually use this for dropdowns.
            // ... omitting complex mapel logic if view handles it via AJAX get_jadwal filters.
            // But view uses $kelas_guru
            $data['kelas_guru'] = []; // Placeholder, relying on AJAX
        }

        return $this->renderView('cbt/rekap/rekap', $data);
    }

    private function cekRekapSiswa()
    {
        // Logic to get list of jadwal IDs that have been recapped
        $db = \Config\Database::connect();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $user = $this->getUser();
        // Check cbt_rekap_jadwal table with TP/SMT filter
        $builder = $db->table('cbt_rekap_jadwal a')
            ->select('a.id_jadwal')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt);

        if (!$this->isAdmin()) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($guru) {
                $builder->join('cbt_bank_soal b', 'a.id_bank = b.id_bank');
                $builder->where('b.bank_guru_id', $guru->id_guru);
            }
        }

        $res = $builder->get()->getResult();

        return ['ids_jadwal' => array_column($res, 'id_jadwal')];
    }

    public function get_jadwal()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->getUser();

        $db = \Config\Database::connect();

        // Fetch Jadwals manually with relations to match CI3 response structure
        $builder = $db->table('cbt_jadwal a')
            ->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.rekap, a.pelanggaran, a.jam_ke, a.durasi_ujian, a.acak_soal, a.acak_opsi,
                b.bank_kode, b.bank_level, b.bank_kelas, b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, b.bank_guru_id, b.soal_agama,
                m.nama_mapel, j.kode_jenis, j.nama_jenis, tp.tahun as tp_tahun, smt.nama_smt as smt_nama')
            ->join('cbt_bank_soal b', 'a.id_bank=b.id_bank')
            ->join('master_mapel m', 'b.bank_mapel_id=m.id_mapel', 'left')
            ->join('cbt_jenis j', 'a.id_jenis=j.id_jenis', 'left')
            ->join('master_tp tp', 'a.id_tp=tp.id_tp', 'left')
            ->join('master_smt smt', 'a.id_smt=smt.id_smt', 'left')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt);

        if (!$this->isAdmin()) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($guru) {
                $builder->where('b.bank_guru_id', $guru->id_guru);
            } else {
                // If guru data not found, restrict to nothing for safety
                $builder->where('a.id_jadwal', 0);
            }
        }

        $jadwals = $builder->get()->getResult();

        // Populate nested data (sesisiswa, pengawas) manually
        $idsJadwal = [];
        foreach ($jadwals as $jadwal) {
            $idsJadwal[] = $jadwal->id_jadwal;
            $jadwal->bank_kelas = $this->cbt->maybeUnserialize($jadwal->bank_kelas);

            // Format Bank Data
            $jadwal->bank = (object) [
                'bank_kelas' => $jadwal->bank_kelas,
                'bank_kode' => $jadwal->bank_kode,
                'tampil_pg' => $jadwal->tampil_pg,
                'tampil_kompleks' => $jadwal->tampil_kompleks,
                'tampil_jodohkan' => $jadwal->tampil_jodohkan,
                'tampil_isian' => $jadwal->tampil_isian,
                'tampil_esai' => $jadwal->tampil_esai,
                'soal_agama' => $jadwal->soal_agama ?? '-',
                'mapel' => (object) ['nama_mapel' => $jadwal->nama_mapel]
            ];

            // Fix TP/SMT structure for JS
            $jadwal->tp = (object) ['tahun' => $jadwal->tp_tahun];
            $jadwal->smt = (object) ['nama_smt' => $jadwal->smt_nama];

            // Sesi Siswa logic (Simplified: Count participants or list them?)
            // CI3 returned list of students. Heavy!
            // Let's try to optimize or fetch simple list.
            // CI3 `with_sesisiswa` fetched students.
            // We can fetch cbt_sesi_siswa for these schedules.
            $jadwal->sesisiswa = []; // Populate below
        }

        // Fetch Cbt Siswa (Status)
        $cbtSiswaData = [];
        if (!empty($idsJadwal)) {
            $cbtSiswaData = $db->table('cbt_siswa')
                ->whereIn('id_jadwal', $idsJadwal)
                ->get()->getResult();
        }

        // Get all kelas IDs from bank_kelas for all jadwals
        $allKelasIds = [];
        foreach ($jadwals as $jadwal) {
            if (is_array($jadwal->bank_kelas)) {
                foreach ($jadwal->bank_kelas as $bk) {
                    if (isset($bk->kelas_id) && $bk->kelas_id) {
                        $allKelasIds[] = $bk->kelas_id;
                    } elseif (isset($bk['kelas_id']) && $bk['kelas_id']) {
                        $allKelasIds[] = $bk['kelas_id'];
                    }
                }
            }
        }
        $allKelasIds = array_unique(array_filter($allKelasIds));

        // Fetch all students from cbt_sesi_siswa for these classes
        $sesiSiswaData = [];
        if (!empty($allKelasIds)) {
            $sesiSiswaBuilder = $db->table('cbt_sesi_siswa a')
                ->select('a.siswa_id, a.kelas_id, a.ruang_id, a.sesi_id, b.nama, b.nis, b.nisn, b.agama')
                ->join('master_siswa b', 'a.siswa_id = b.id_siswa', 'left')
                ->where('a.tp_id', $tp->id_tp)
                ->where('a.smt_id', $smt->id_smt)
                ->whereIn('a.kelas_id', $allKelasIds);
            // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
            $this->onlySiswaAktif($sesiSiswaBuilder, 'a', 'bi', 'siswa_id');
            $sesiSiswaData = $sesiSiswaBuilder->get()->getResult();
        }

        // Group sesi siswa by kelas_id for quick lookup
        $sesiSiswaByKelas = [];
        foreach ($sesiSiswaData as $ss) {
            $sesiSiswaByKelas[$ss->kelas_id][] = $ss;
        }

        // Populate sesisiswa for each jadwal
        foreach ($jadwals as $jadwal) {
            $jadwal->sesisiswa = [];
            if (is_array($jadwal->bank_kelas)) {
                foreach ($jadwal->bank_kelas as $bk) {
                    $kelasId = $bk->kelas_id ?? ($bk['kelas_id'] ?? null);
                    if ($kelasId && isset($sesiSiswaByKelas[$kelasId])) {
                        foreach ($sesiSiswaByKelas[$kelasId] as $siswa) {
                            $jadwal->sesisiswa[] = (object) [
                                'siswa_id' => $siswa->siswa_id,
                                'kelas_id' => $siswa->kelas_id,
                                'ruang_id' => $siswa->ruang_id,
                                'sesi_id' => $siswa->sesi_id,
                                'siswa' => (object) [
                                    'nama' => $siswa->nama,
                                    'nis' => $siswa->nis,
                                    'nisn' => $siswa->nisn,
                                    'agama' => $siswa->agama
                                ]
                            ];
                        }
                    }
                }
            }
        }

        $data = [
            'success' => true,
            'jadwal' => $jadwals, // View handles details
            'total' => count($jadwals),
            'pengawas' => [], // Populate if critical
            'kelases' => $this->master->getAllKelas($tp->id_tp, $smt->id_smt),
            'cbtsiswa' => $cbtSiswaData
        ];

        return $this->response->setJSON($data);
    }

    public function get_rekap($page = null)
    {
        log_message('info', 'Cbtrekap::get_rekap called with page: ' . $page);
        try {
            $db = \Config\Database::connect();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            if (!$tp || !$smt) {
                return $this->response->setJSON([
                    'success' => true,
                    'rekap' => [],
                    'kelases' => [],
                    'total' => 0
                ]);
            }

            $user = $this->getUser();
            $builder = $db->table('cbt_rekap_jadwal a')
                ->where('a.id_tp', $tp->id_tp)
                ->where('a.id_smt', $smt->id_smt);

            if (!$this->isAdmin()) {
                $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                if ($guru) {
                    $builder->join('cbt_bank_soal b', 'a.id_bank = b.id_bank');
                    $builder->where('b.bank_guru_id', $guru->id_guru);
                } else {
                    $builder->where('a.id_rekap', 0);
                }
            }

            $req = $builder->get()->getResult();

            $jadwalIds = array_column($req, 'id_jadwal');
            $siswas = [];
            // Initialize $siswas for all jadwalIds to avoid undefined index errors!
            foreach ($jadwalIds as $idJadwal) {
                $siswas[$idJadwal] = [];
            }

            if (!empty($jadwalIds)) {
                // Get rekap siswa data
                $rekapBuilder = $db->table('cbt_rekap_siswa a')
                    ->select('a.*, ms.agama, ms.nama as nama_siswa')
                    ->join('master_siswa ms', 'ms.id_siswa = a.id_siswa', 'left')
                    ->whereIn('a.id_jadwal', $jadwalIds);
                // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
                $this->onlySiswaAktif($rekapBuilder, 'a');
                $rawSiswa = $rekapBuilder->get()->getResult();

                // ALWAYS get kelas from kelas_siswa or cbt_sesi_siswa as the source of truth
                $siswaKelasMap = [];
                if (!empty($rawSiswa)) {
                    $siswaIds = array_unique(array_column($rawSiswa, 'id_siswa'));

                    // Try kelas_siswa first (uses id_siswa, id_kelas, id_tp, id_smt column names)
                    try {
                        $kelasData = $db->table('kelas_siswa')
                            ->select('id_siswa, id_kelas')
                            ->where('id_tp', $tp->id_tp)
                            ->where('id_smt', $smt->id_smt)
                            ->whereIn('id_siswa', $siswaIds)
                            ->get()->getResult();

                        foreach ($kelasData as $kd) {
                            $siswaKelasMap[(int) $kd->id_siswa] = (int) $kd->id_kelas;
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'kelas_siswa lookup error: ' . $e->getMessage());
                    }

                    // Fallback: cbt_sesi_siswa
                    if (empty($siswaKelasMap)) {
                        try {
                            $sesiData = $db->table('cbt_sesi_siswa')
                                ->select('siswa_id, kelas_id')
                                ->where('tp_id', $tp->id_tp)
                                ->where('smt_id', $smt->id_smt)
                                ->whereIn('siswa_id', $siswaIds)
                                ->get()->getResult();

                            foreach ($sesiData as $sd) {
                                $siswaKelasMap[$sd->siswa_id] = $sd->kelas_id;
                            }
                        } catch (\Exception $e) {
                            log_message('error', 'cbt_sesi_siswa lookup error: ' . $e->getMessage());
                        }
                    }
                }

                foreach ($rawSiswa as $s) {
                    $s->nilai_input = $this->cbt->maybeUnserialize($s->nilai_input ?? null);
                    $s->siswa       = (object)['agama' => $s->agama ?? ''];
                    $siswaId        = (int)($s->id_siswa ?? 0);
                    $s->kelas_id    = $siswaKelasMap[$siswaId] ?? ($s->id_kelas ?? 0);

                    $siswas[$s->id_jadwal][] = $s;
                }

            }

            foreach ($req as $r) {
                $jadwalObj = $this->cbt->maybeUnserialize($r->jadwal ?? null);
                $r->jadwal = is_object($jadwalObj) ? $jadwalObj : (object)[];
                $bankObj = $this->cbt->maybeUnserialize($r->bank_soal ?? null);
                $r->bank = is_object($bankObj) ? $bankObj : (object)[];

                // Ensure soal_agama is available for filtering
                if ($bankObj && !isset($bankObj->soal_agama)) {
                    $bankObj->soal_agama = '-';
                }

                // Promote properties to root for JS View compatibility
                if ($jadwalObj) {
                    // Ensure object access
                    $jadwalObj = (object) $jadwalObj;

                    $r->tgl_mulai = $jadwalObj->tgl_mulai ?? $jadwalObj->mulai ?? null;
                    $r->tgl_selesai = $jadwalObj->tgl_selesai ?? $jadwalObj->selesai ?? null;
                    $r->kode_jenis = $jadwalObj->kode_jenis ?? '';
                    $r->bank_kelas = $jadwalObj->bank_kelas ?? [];

                    // Unserialize bank_kelas if needed
                    if (is_string($r->bank_kelas)) {
                        $r->bank_kelas = $this->cbt->maybeUnserialize($r->bank_kelas);
                    }
                } else {
                    $r->bank_kelas = [];
                }

                $r->rekapsiswa = $siswas[$r->id_jadwal] ?? [];

                // ALWAYS generate bank_kelas from actual student data for accurate display
                // This ensures we show the kelas that students are actually in
                if (!empty($r->rekapsiswa)) {
                    $uniqueKelas = array_unique(array_filter(array_column($r->rekapsiswa, 'kelas_id')));
                    $genKelas = [];
                    foreach ($uniqueKelas as $kid) {
                        if ($kid) {
                            $genKelas[] = (object) ['kelas_id' => $kid];
                        }
                    }
                    // Only override if we found actual kelas from students
                    if (!empty($genKelas)) {
                        $r->bank_kelas = $genKelas;
                    }
                }
            }

            // Get Involved Classes data for JS Map to prevent "Kelas tidak ditemukan"
            $allKelasIds = [];
            foreach ($siswas as $jId => $sList) {
                foreach ($sList as $s) {
                    if (!empty($s->kelas_id)) {
                        $allKelasIds[] = $s->kelas_id;
                    }
                }
            }
            $allKelasIds = array_unique(array_filter($allKelasIds));

            $kelases = [];
            if (!empty($allKelasIds)) {
                $kelases = $db->table('master_kelas')->whereIn('id_kelas', $allKelasIds)->get()->getResult();
            }

            return $this->response->setJSON([
                'success' => true,
                'rekap' => $req,
                'kelases' => $kelases,
                'total' => count($req)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Cbtrekap::get_rekap error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function rekap()
    {
        $idJadwal = json_decode($this->request->getPost('ids_jadwal'));
        if (empty($idJadwal)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada jadwal dipilih']);
        }

        $db = \Config\Database::connect();
        $db->transBegin(); // Start transaction for data integrity

        try {
            // 1. Fetch Source Data (Jadwal + Bank + Mapel + Jenis)
            $builder = $db->table('cbt_jadwal a')
                ->select('a.*, b.bank_kode, b.bank_level, b.bank_kelas, 
                          b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, b.tampil_urutan, b.tampil_cloze, 
                          b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai, b.bobot_urutan, b.bobot_cloze,
                          b.bank_mapel_id, m.nama_mapel, j.kode_jenis')
                ->join('cbt_bank_soal b', 'a.id_bank=b.id_bank', 'left')
                ->join('master_mapel m', 'b.bank_mapel_id=m.id_mapel', 'left')
                ->join('cbt_jenis j', 'a.id_jenis=j.id_jenis', 'left')
                ->whereIn('a.id_jadwal', $idJadwal);

            $sources = $builder->get()->getResult();

            $toInsert = [];
            $toUpdate = [];

            // Check Existing to decide Insert vs Update
            $existing = $db->table('cbt_rekap_jadwal')->whereIn('id_jadwal', $idJadwal)->get()->getResult();
            $mapExisting = [];
            foreach ($existing as $e) {
                $mapExisting[$e->id_jadwal] = $e->id_rekap;
            }

            // ── OPTIMASI: pre-load semua soal per bank dalam 1 query ─────────
            // Sebelum: 1 query soal PER jadwal di dalam loop = N query
            // Sesudah: 1 query yang fetch semua bank sekaligus
            $allBankIds = array_unique(array_column($sources, 'id_bank'));
            $allSoalsRaw = [];
            if (!empty($allBankIds)) {
                $allSoalsRaw = $db->table('cbt_soal')
                    ->whereIn('bank_id', $allBankIds)
                    ->get()->getResult();
            }
            // Index by bank_id
            $soalsByBank = [];
            foreach ($allSoalsRaw as $s) {
                $soalsByBank[$s->bank_id][] = $s;
            }

            foreach ($sources as $src) {
                // Gunakan soal yang sudah di-preload — tidak ada query DB di sini
                $soals = $soalsByBank[$src->id_bank] ?? [];

                // Create Bank Snapshot Object - Ensure ALL question types are captured
                $bankObj = (object) [
                    'id_bank' => $src->id_bank,
                    'bank_kode' => $src->bank_kode,
                    'bank_level' => $src->bank_level,
                    'bank_kelas' => $src->bank_kelas,
                    'tampil_pg' => $src->tampil_pg,
                    'tampil_kompleks' => $src->tampil_kompleks ?? 0,
                    'tampil_jodohkan' => $src->tampil_jodohkan ?? 0,
                    'tampil_isian' => $src->tampil_isian ?? 0,
                    'tampil_esai' => $src->tampil_esai ?? 0,
                    'tampil_urutan' => $src->tampil_urutan ?? 0,
                    'tampil_cloze' => $src->tampil_cloze ?? 0,
                    'bobot_pg' => $src->bobot_pg ?? 0,
                    'bobot_kompleks' => $src->bobot_kompleks ?? 0,
                    'bobot_jodohkan' => $src->bobot_jodohkan ?? 0,
                    'bobot_isian' => $src->bobot_isian ?? 0,
                    'bobot_esai' => $src->bobot_esai ?? 0,
                    'bobot_urutan' => $src->bobot_urutan ?? 0,
                    'bobot_cloze' => $src->bobot_cloze ?? 0,
                    'opsi' => $src->opsi ?? 5,
                    'soal' => $soals
                ];

                // Categorize soal
            $soal_pg = []; $soal_kompleks = []; $soal_jodohkan = []; $soal_isian = []; $soal_esai = []; $soal_urutan = []; $soal_cloze = [];
            foreach ($soals as $s) {
                if ($s->tampilkan == '1') {
                    if ($s->jenis == '1') $soal_pg[] = $s;
                    elseif ($s->jenis == '2') $soal_kompleks[] = $s;
                    elseif ($s->jenis == '3') $soal_jodohkan[] = $s;
                    elseif ($s->jenis == '4') $soal_isian[] = $s;
                    elseif ($s->jenis == '5') $soal_esai[] = $s;
                    elseif ($s->jenis == '6') $soal_urutan[] = $s;
                    elseif ($s->jenis == '7') $soal_cloze[] = $s;
                }
            }

                // Clean data for Jadwal Snapshot
                $jadwalObj = clone $src;

                $now = date('Y-m-d H:i:s');
                $data = [
                    'id_jadwal' => $src->id_jadwal,
                    'id_tp' => $src->id_tp,
                    'id_smt' => $src->id_smt,
                    'id_jenis' => $src->id_jenis,
                    'kode_jenis' => $src->kode_jenis ?? 'Ujian',
                    'id_bank' => $src->id_bank,
                    'id_mapel' => $src->bank_mapel_id ?? 0,
                    'nama_mapel' => $src->nama_mapel ?? 'Mapel Terhapus',
                    'jadwal' => serialize($jadwalObj),
                    'bank_soal' => serialize($bankObj),
                    'soal_pg' => serialize($soal_pg),
                    'soal_kompleks' => serialize($soal_kompleks),
                    'soal_jodohkan' => serialize($soal_jodohkan),
                    'soal_isian' => serialize($soal_isian),
                    'soal_esai' => serialize($soal_esai),
                    'updated_at' => $now,
                ];

                if (isset($mapExisting[$src->id_jadwal])) {
                    // Prepare Update
                    $data['id_rekap'] = $mapExisting[$src->id_jadwal];
                    $toUpdate[] = $data;
                } else {
                    $data['created_at'] = $now;
                    $toInsert[] = $data;
                }
            }

            if (!empty($toInsert))
                $db->table('cbt_rekap_jadwal')->insertBatch($toInsert);
            if (!empty($toUpdate))
                $db->table('cbt_rekap_jadwal')->updateBatch($toUpdate, 'id_rekap');

            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Rekap Jadwal Berhasil Disimpan']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Rekap Error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal simpan rekap: ' . $e->getMessage()]);
        }
    }

    public function rekap_nilai_siswa()
    {
        try {
            $idJadwal = json_decode($this->request->getPost('idjadwal'));
            if (empty($idJadwal)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada jadwal dipilih']);
            }

            $db = \Config\Database::connect();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $totalRekap = 0;

            foreach ($idJadwal as $idj) {
                // Use olahNilai to get FULLY processed results for ALL students in this schedule
                $res = $this->cbt->olahNilai(0, $tp->id_tp, $smt->id_smt, null, $idj);
                if (!$res || empty($res->sesisiswa)) {
                    continue;
                }

                $toInsert = [];
                $toUpdate = [];

                // Check existing rekap for this specific schedule
                $existing = $db->table('cbt_rekap_siswa')->where('id_jadwal', $idj)->get()->getResult();
                $mapExisting = [];
                foreach ($existing as $e) {
                    $mapExisting[$e->id_siswa] = $e->id_rekap_siswa;
                }

                foreach ($res->sesisiswa as $ss) {
                    // Skip students who haven't started (no cbt_siswa or no start time)
                    if (!$ss->cbtsiswa || empty($ss->cbtsiswa->mulai)) {
                        continue;
                    }

                    // Format data for cbt_rekap_siswa table
                    $data = [
                        'id_siswa' => $ss->siswa_id,
                        'id_jadwal' => $idj,
                        'id_bank' => $res->id_bank,
                        'id_kelas' => $ss->kelas_id,
                        'nama_siswa' => $ss->siswa->nama ?? 'Siswa Terhapus',
                        'no_peserta' => $ss->peserta->nomor_peserta ?? $ss->siswa->nis ?? '-',
                        // Snapshot answer details from PG detail if available
                        'jawaban' => json_encode($ss->nilai_akhir->skor_pg->detail ?? []),
                        // Final compiled score object
                        'nilai' => json_encode($ss->nilai_akhir),
                        // Correction history
                        'nilai_input' => json_encode($ss->cbtsiswa->nilai_input ?? new \stdClass()),
                        // Catatan: nilai_katrol tidak lagi ditulis sebagai kolom
                        // terpisah karena kolom tsb tidak ada di cbt_rekap_siswa
                        // (menyebabkan "Unknown column" & rollback). Nilainya
                        // tetap tersimpan di dalam JSON kolom `nilai` di atas.
                        'mulai' => $ss->cbtsiswa->mulai,
                        'selesai' => $ss->cbtsiswa->selesai,
                        'pelanggaran' => json_encode($ss->cbtsiswa->pelanggaran ?? new \stdClass())
                    ];

                    if (isset($mapExisting[$ss->siswa_id])) {
                        $data['id_rekap_siswa'] = $mapExisting[$ss->siswa_id];
                        $toUpdate[] = $data;
                    } else {
                        $toInsert[] = $data;
                    }
                }

                if (!empty($toInsert) || !empty($toUpdate)) {
                    $db->transBegin();
                    try {
                        if (!empty($toInsert)) {
                            $db->table('cbt_rekap_siswa')->insertBatch($toInsert);
                        }
                        if (!empty($toUpdate)) {
                            $db->table('cbt_rekap_siswa')->updateBatch($toUpdate, 'id_rekap_siswa');
                        }
                        $db->transCommit();
                    } catch (\Exception $e) {
                        $db->transRollback();
                        log_message('error', 'Rekap batch error for jadwal ' . $idj . ': ' . $e->getMessage());
                        continue;
                    }
                }

                // Update rekap status in cbt_jadwal (outside transaction - less critical)
                $db->table('cbt_jadwal')->where('id_jadwal', $idj)->update(['rekap' => '1']);

                $totalRekap += count($toInsert) + count($toUpdate);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Rekap Nilai Siswa Berhasil',
                'rekap_siswa' => $totalRekap
            ]);
        } catch (\Exception $e) {
            if (isset($db) && $db->transStatus() === false) {
                $db->transRollback();
            }
            log_message('error', 'Rekap Nilai Siswa Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat rekap: ' . $e->getMessage()
            ]);
        }
    }

    public function get_nilai_rekap()
    {
        $idKelas = $this->request->getPost('id_kelas');
        $idJadwal = $this->request->getPost('id_jadwal');

        $db = \Config\Database::connect();

        // Get Rekap Jadwal
        // Note: id_jadwal param here might be the REAL jadwal ID, but we need ID REKAP if filtering by rekap.
        // Actually the Dropdown in View passes `id_jadwal`.
        // We find the rekap record for this jadwal.
        $rekapJadwal = $db->table('cbt_rekap_jadwal')->where('id_jadwal', $idJadwal)->get()->getRow();

        if (!$rekapJadwal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Rekap tidak ditemukan']);
        }

        // Unserialize Snapshot Data
        $jadwal = $this->cbt->maybeUnserialize($rekapJadwal->jadwal);
        $bank = $this->cbt->maybeUnserialize($rekapJadwal->bank_soal);

        // Always get current katrol configuration from main jadwal table so it's not stale in rekap
        $currJadwal = $db->table('cbt_jadwal')->select('katrol_min, katrol_max')->where('id_jadwal', $idJadwal)->get()->getRow();
        if ($currJadwal) {
            if (!is_object($jadwal)) $jadwal = (object) [];
            $jadwal->katrol_min = $currJadwal->katrol_min;
            $jadwal->katrol_max = $currJadwal->katrol_max;
        }

        // Get TP/SMT first
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Get ALL rekap for this jadwal (do NOT filter by id_kelas in query as column might be missing)
        $rekaps = $db->table('cbt_rekap_siswa')->where('id_jadwal', $idJadwal)->get()->getResult();

        // ── Bangun map siswa → kelas ─────────────────────────────────────────
        // Priority: 1) id_kelas dari snapshot rekap, 2) kelas_siswa, 3) cbt_sesi_siswa
        $siswaKelasMap = [];
        if (!empty($rekaps)) {
            $needLookup = [];
            foreach ($rekaps as $r) {
                $sid = (string) $r->id_siswa;
                if (!empty($r->id_kelas) && $r->id_kelas != '0') {
                    $siswaKelasMap[$sid] = (string) $r->id_kelas;
                } else {
                    $needLookup[] = $r->id_siswa;
                }
            }

            if (!empty($needLookup)) {
                // Fallback ke kelas_siswa
                $kelasData = $db->table('kelas_siswa')
                    ->select('id_siswa, id_kelas')
                    ->where('id_tp', $tp->id_tp)
                    ->where('id_smt', $smt->id_smt)
                    ->whereIn('id_siswa', $needLookup)
                    ->get()->getResult();
                foreach ($kelasData as $kd) {
                    $siswaKelasMap[(string) $kd->id_siswa] = (string) $kd->id_kelas;
                }

                // Fallback ke cbt_sesi_siswa untuk yang masih belum ditemukan
                $stillMissing = array_values(array_filter($needLookup, fn($sid) => !isset($siswaKelasMap[(string) $sid])));
                if (!empty($stillMissing)) {
                    $sesiData = $db->table('cbt_sesi_siswa')
                        ->select('siswa_id, kelas_id')
                        ->where('tp_id', $tp->id_tp)
                        ->where('smt_id', $smt->id_smt)
                        ->whereIn('siswa_id', $stillMissing)
                        ->get()->getResult();
                    foreach ($sesiData as $sd) {
                        $siswaKelasMap[(string) $sd->siswa_id] = (string) $sd->kelas_id;
                    }
                }
            }
        }

        // Filter by kelas jika diperlukan
        if ($idKelas) {
            $idKelasStr = (string) $idKelas;
            $rekaps = array_values(array_filter($rekaps, function ($r) use ($idKelasStr, $siswaKelasMap) {
                return ($siswaKelasMap[(string) $r->id_siswa] ?? '0') === $idKelasStr;
            }));
        }

        // Map to View Structure
        $sesisiswa = [];
        foreach ($rekaps as $r) {
            $item = new \stdClass();
            $item->siswa_id = $r->id_siswa;
            $item->siswa = (object) [
                'id_siswa' => $r->id_siswa,
                'nama' => $r->nama_siswa ?? '',
                'nis' => $r->no_peserta ?? '',
            ];
            // Set no_peserta and nama_siswa from snapshot
            $item->no_peserta = $r->no_peserta ?? '';
            $item->nama_siswa = $r->nama_siswa ?? '';

            // Unserialize answers
            $item->cbtsiswa = (object) [
                'jawaban' => $this->cbt->maybeUnserialize($r->jawaban),
                'nilai' => $this->cbt->maybeUnserialize($r->nilai),
                'nilai_input' => $this->cbt->maybeUnserialize($r->nilai_input),
                'mulai' => $r->mulai,
                'selesai' => $r->selesai,
                'pelanggaran' => $r->pelanggaran
            ];

            // Also set nilai_akhir from nilai data for ekspor_rekap view
            $nilaiData = $this->cbt->maybeUnserialize($r->nilai);
            if (!is_object($nilaiData)) {
                if (is_array($nilaiData)) $nilaiData = (object) $nilaiData;
                else $nilaiData = (object) ['skor_total' => 0];
            }
            $nilaiData->nilai_katrol = $r->nilai_katrol ?? 0;
            $item->nilai_akhir = $this->cbt->sanitizeScoreObject($nilaiData);

            // Build skor_pg.detail from jawaban data for view compatibility
            $jawabanData = $item->cbtsiswa->jawaban ?? [];
            if (!empty($jawabanData) && is_array($jawabanData)) {
                $detailPg = [];
                foreach ($jawabanData as $idSoal => $jawab) {
                    if (is_array($jawab) && isset($jawab['jawab'])) {
                        $detailPg[] = [
                            'id_soal' => $idSoal,
                            'jawaban_siswa' => $jawab['jawab'] ?? '-',
                            'benar' => $jawab['benar'] ?? false
                        ];
                    } elseif (is_object($jawab)) {
                        $detailPg[] = [
                            'id_soal' => $idSoal,
                            'jawaban_siswa' => $jawab->jawab ?? $jawab->jawaban ?? '-',
                            'benar' => $jawab->benar ?? false
                        ];
                    }
                }
                if (!isset($item->nilai_akhir->skor_pg)) {
                    $item->nilai_akhir->skor_pg = (object) [];
                }
                $item->nilai_akhir->skor_pg->detail = $detailPg;
            }

            // Set pelanggaran for view
            $item->pelanggaran = $this->cbt->maybeUnserialize($r->pelanggaran);

            // Compute elapsed duration for rekap
            $mulaiR = $r->mulai;
            $selesaiR = $r->selesai;
            $lamaR = null;
            if ($mulaiR && $selesaiR) {
                $diff = max(0, strtotime($selesaiR) - strtotime($mulaiR));
                $lamaR = sprintf('%d:%02d', floor($diff / 60), $diff % 60);
            }

            // Set durasi for view (required by data_nilai.php)
            $item->durasi = (object) [
                'mulai'        => $mulaiR,
                'selesai'      => $selesaiR,
                'mulai_ujian'  => $mulaiR ? date('H:i', strtotime($mulaiR)) : null,
                'lama_ujian'   => $lamaR,
                'status_ujian' => $selesaiR ? 'Selesai' : ($mulaiR ? 'Mengerjakan' : '-')
            ];

            $sesisiswa[] = $item;
        }

        // Enhance with real name/nis if possible
        if (!empty($sesisiswa)) {
            $ids = array_column($rekaps, 'id_siswa');
            $students = $db->table('master_siswa')->whereIn('id_siswa', $ids)->get()->getResult();
            $mapStu = [];
            foreach ($students as $s)
                $mapStu[$s->id_siswa] = $s;

            foreach ($sesisiswa as $s) {
                if (isset($mapStu[$s->siswa_id])) {
                    $s->siswa->nama = $mapStu[$s->siswa_id]->nama;
                    $s->siswa->nis = $mapStu[$s->siswa_id]->nis;
                    // Use snapshot no_peserta first, fallback to master_siswa.nisn
                    if (empty($s->no_peserta) || $s->no_peserta === '-') {
                        $s->no_peserta = $mapStu[$s->siswa_id]->nisn ?? $mapStu[$s->siswa_id]->nis ?? '';
                    }
                    // Also set nama_siswa directly for view compatibility
                    $s->nama_siswa = $mapStu[$s->siswa_id]->nama;
                }
            }
        }

        // Construct Result Object
        // Matching structure used in data_nilai.php JS: result.jadwal.bank, result.jadwal.sesisiswa...

        $jadwalObj = $jadwal;
        // Ensure properties exist
        if (!is_object($jadwalObj))
            $jadwalObj = (object) [];
        $jadwalObj->sesisiswa = $sesisiswa;
        $jadwalObj->bank = $bank;

        // Soal logic for columns
        $soals = $bank->soal ?? [];
        if (!is_array($soals))
            $soals = $this->cbt->maybeUnserialize($soals);

        // Filter soals (koreksi style)
        $jadwalObj->soal_pg = array_values(array_filter($soals, function ($s) {
            return $s->jenis == '1' && $s->tampilkan == '1';
        }));
        $jadwalObj->soal_kompleks = array_values(array_filter($soals, function ($s) {
            return $s->jenis == '2' && $s->tampilkan == '1';
        }));
        $jadwalObj->soal_jodohkan = array_values(array_filter($soals, function ($s) {
            return $s->jenis == '3' && $s->tampilkan == '1';
        }));
        $jadwalObj->soal_isian = array_values(array_filter($soals, function ($s) {
            return $s->jenis == '4' && $s->tampilkan == '1';
        }));
        $jadwalObj->soal_esai = array_values(array_filter($soals, function ($s) {
            return $s->jenis == '5' && $s->tampilkan == '1';
        }));
        $jadwalObj->soal_urutan = array_values(array_filter($soals, function ($s) {
            return $s->jenis == '6' && $s->tampilkan == '1';
        }));
        $jadwalObj->soal_cloze = array_values(array_filter($soals, function ($s) {
            return $s->jenis == '7' && $s->tampilkan == '1';
        }));

        // Get bank_kelas for the view
        $bankKelas = $bank->bank_kelas ?? [];
        if (is_string($bankKelas)) {
            $bankKelas = $this->cbt->maybeUnserialize($bankKelas);
        }

        // Get actual kelas data for bankKelas
        $kelasBank = [];
        $allKelasIds = [];
        if (is_array($bankKelas)) {
            foreach ($bankKelas as $bk) {
                $kid = $bk->kelas_id ?? ($bk['kelas_id'] ?? null);
                if ($kid) {
                    $allKelasIds[] = $kid;
                }
            }
        }
        // Also add the selected kelas
        if ($idKelas) {
            $allKelasIds[] = $idKelas;
        }
        $allKelasIds = array_unique(array_filter($allKelasIds));

        $kelasBankMap = [];
        $kelasBankArray = [];
        if (!empty($allKelasIds)) {
            $kelasData = $db->table('master_kelas')->whereIn('id_kelas', $allKelasIds)->get()->getResult();
            foreach ($kelasData as $k) {
                // Map id => nama for data_nilai.php compatibility
                $kelasBankMap[$k->id_kelas] = $k->nama_kelas ?? $k->kode_kelas ?? ('Kelas ' . $k->id_kelas);

                // Array of objects for ekspor_rekap.php compatibility
                $kelasBankArray[] = (object) [
                    'id_kelas' => $k->id_kelas,
                    'nama_kelas' => $k->nama_kelas ?? $k->kode_kelas ?? ('Kelas ' . $k->id_kelas),
                    'kode_kelas' => $k->kode_kelas ?? '',
                    'level_id' => $k->level_id ?? 0
                ];
            }
        }

        // Get TP/SMT for display
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'success' => true,
            'rekap' => [
                'jadwal' => $jadwalObj,
                'bank_soal' => $bank,
                'rekapsiswa' => $sesisiswa,
                'soal_pg' => $jadwalObj->soal_pg,
                'soal_kompleks' => $jadwalObj->soal_kompleks,
                'soal_jodohkan' => $jadwalObj->soal_jodohkan,
                'soal_isian' => $jadwalObj->soal_isian,
                'soal_esai' => $jadwalObj->soal_esai,
                'soal_urutan' => $jadwalObj->soal_urutan,
                'soal_cloze' => $jadwalObj->soal_cloze,
                // Pass other meta
                'nama_mapel' => $rekapJadwal->nama_mapel,
                'nama_guru' => '', // Fetch if needed
                'kode_jenis' => $rekapJadwal->kode_jenis,
                'tgl_mulai' => $jadwal->tgl_mulai ?? null,
                'tgl_selesai' => $jadwal->tgl_selesai ?? null,
                'tp' => $tp->tahun ?? '',
                'smt' => $smt->smt ?? '',
                // Add kelas_bank for view: use Array of Objects
                'kelas_bank' => $kelasBankArray
            ],
            'kelas_selected' => $idKelas,
            'jadwal_selected' => $idJadwal,
            // Use Map for data_nilai.php
            'kelas' => $kelasBankMap,
            'durasi_siswa' => []
        ];

        return $this->response->setJSON($data);
    }

    public function view_koreksi()
    {
        $user = $this->getUser();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Rekap Nilai Siswa',
            'subjudul' => 'Koreksi Rekap',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'is_rekap' => true,
        ];

        $data['kelas'] = $this->master->getDropdownKelas($tp->id_tp, $smt->id_smt);
        $data['kelas_selected'] = $this->request->getGet('id_kelas');
        $data['jadwal_selected'] = $this->request->getGet('id_jadwal');

        if (!$this->isAdmin()) {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        }

        return $this->renderView('cbt/nilai/data_nilai', $data);
    }

    /**
     * Ekspor Rekap Nilai Siswa ke Excel
     */
    public function ekspor_rekap()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        if (!$this->isAdmin() && !$this->inGroup('guru')) {
            return $this->showError('Hanya Administrator dan Guru yang dapat mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $jadwal_selected = $this->request->getGet('id_jadwal');
        $kelas_selected = $this->request->getGet('id_kelas');

        $db = \Config\Database::connect();
        
        // Fetch recapped schedules for filter dropdown
        $builder = $db->table('cbt_rekap_jadwal r')
            ->select('r.id_jadwal, r.kode_mapel, r.nama_mapel, r.kode_jenis')
            ->join('cbt_jadwal j', 'j.id_jadwal = r.id_jadwal', 'left')
            ->join('cbt_bank_soal b', 'b.id_bank = r.id_bank', 'left')
            ->where('r.id_tp', $tp->id_tp)
            ->where('r.id_smt', $smt->id_smt);

        if (!$this->isAdmin()) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($guru) {
                $builder->where('b.bank_guru_id', $guru->id_guru);
            }
        }
        $jadwals = $builder->get()->getResult();

        // Fetch classes for filter dropdown
        $kelas = $this->master->getDropdownKelas($tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'judul' => 'Ekspor Rekap Nilai',
            'subjudul' => 'Hasil CBT',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'jadwals' => $jadwals,
            'kelas' => $kelas,
            'jadwal_selected' => $jadwal_selected,
            'kelas_selected' => $kelas_selected,
            'is_rekap' => true
        ];

        if (!$this->isAdmin()) {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        }

        return $this->renderView('cbt/rekap/ekspor_rekap', $data);
    }

    // Additional methods like koreksi, inputEssai can reuse Cbtnilai logic or forward calls.

    public function koreksi()
    {
        $user = $this->getUser();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $id_siswa = $this->request->getGet('id_siswa');
        $id_jadwal = $this->request->getGet('id_jadwal');

        $db = \Config\Database::connect();

        // Use the TP/SMT from the schedule record instead of active session
        // This ensures old rekaps can be viewed regardless of the active TP/SMT
        $jadwal_rec = $db->table('cbt_jadwal')->where('id_jadwal', $id_jadwal)->get()->getRow();
        if (!$jadwal_rec) {
            $jadwal_rec = $db->table('cbt_rekap_jadwal')->where('id_jadwal', $id_jadwal)->get()->getRow();
        }

        $id_tp = $jadwal_rec->id_tp ?? $tp->id_tp;
        $id_smt = $jadwal_rec->id_smt ?? $smt->id_smt;

        $data = [
            'user' => $user,
            'judul' => 'Koreksi Hasil Siswa',
            'subjudul' => 'Hasil Siswa',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'is_rekap' => 1,
            'id_jadwal' => $id_jadwal,
            'id_bank' => 0,
            'siswa' => null,
            'info' => null,
            'rekap_mapel' => '',
            'rekap_guru' => '',
            'soal_pg' => [],
            'soal_kompleks' => [],
            'soal_jodohkan' => [],
            'soal_isian' => [],
            'soal_esai' => [],
            'soal_urutan' => [],
            'soal_cloze' => []
        ];

        // Assuming source data correction updates rekap too if rekap exists
        // olahNilai here fetches from cbt_siswa
        $jadwal = $this->cbt->olahNilai(2, $id_tp, $id_smt, $id_siswa, $id_jadwal, true);
        if ($jadwal && isset($jadwal->sesisiswa[0])) {
            $jadwal->sesisiswa = $jadwal->sesisiswa[0];

            $data['siswa'] = $jadwal->sesisiswa;
            $data['info'] = $jadwal->bank;
            $data['id_bank'] = $jadwal->id_bank;
            $data['rekap_mapel'] = $jadwal->nama_mapel ?? '';
            $data['rekap_guru'] = $jadwal->nama_guru ?? '';
            $data['jenis'] = $jadwal->nama_jenis;
            $data['tp_jadwal'] = $jadwal->tp_tahun ?? $tp->tahun;
            $data['jadwal'] = $jadwal;

            $data['soal_pg'] = $jadwal->soal_pg;
            $data['soal_kompleks'] = $jadwal->soal_kompleks;
            $data['soal_jodohkan'] = $jadwal->soal_jodohkan;
            $data['soal_isian'] = $jadwal->soal_isian;
            $data['soal_esai'] = $jadwal->soal_esai;
            $data['soal_urutan'] = $jadwal->soal_urutan;
            $data['soal_cloze'] = $jadwal->soal_cloze;
        } else {
            // Fallback for archived data if not found in current session
            $rekapSiswa = $db->table('cbt_rekap_siswa a')
                ->select('a.*, b.nama as nama_siswa, b.nis, b.nisn')
                ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
                ->where('a.id_siswa', $id_siswa)
                ->where('a.id_jadwal', $id_jadwal)
                ->get()->getRow();

            if ($rekapSiswa) {
                 // Build a basic student object that the view expects
                 $rekapJadwal = $db->table('cbt_rekap_jadwal')->where('id_jadwal', $id_jadwal)->get()->getRow();
                 if ($rekapJadwal) {
                     $bank = $this->cbt->maybeUnserialize($rekapJadwal->bank_soal);
                     $data['info'] = $bank;
                     $data['rekap_mapel'] = $rekapJadwal->nama_mapel;
                     $data['id_bank'] = $rekapJadwal->id_bank;
                     
                     $nilai_input = $this->cbt->maybeUnserialize($rekapSiswa->nilai_input);
                     $nilai_data = $this->cbt->maybeUnserialize($rekapSiswa->nilai);
                     $data['siswa'] = (object) [
                         'siswa_id' => $id_siswa,
                         'siswa' => (object) ['nama' => $rekapSiswa->nama_siswa, 'nis' => $rekapSiswa->nis],
                         'peserta' => (object) ['nomor_peserta' => $rekapSiswa->nis], // Fallback to NIS
                         'kelas' => $rekapSiswa->id_kelas,
                         'no_peserta' => $rekapSiswa->nis,
                         'nilai_akhir' => (object) [
                             'skor_total' => $nilai_data->skor_total ?? 0,
                             'dikoreksi' => $nilai_input->dikoreksi ?? 0,
                             'skor_pg' => (object) ['skor_total' => $nilai_input->pg_nilai ?? 0],
                             'skor_kompleks' => (object) ['skor_total' => $nilai_input->kompleks_nilai ?? 0],
                             'skor_jodohkan' => (object) ['skor_total' => $nilai_input->jodohkan_nilai ?? 0],
                             'skor_isian' => (object) ['skor_total' => $nilai_input->isian_nilai ?? 0],
                             'skor_essai' => (object) ['skor_total' => $nilai_input->essai_nilai ?? 0],
                             'skor_urutan' => (object) ['skor_total' => $nilai_input->urutan_nilai ?? 0],
                             'skor_cloze' => (object) ['skor_total' => $nilai_input->cloze_nilai ?? 0],
                         ]
                     ];
                     
                     $soalPg = $this->cbt->maybeUnserialize($rekapJadwal->soal_pg ?? '');
                     $data['soal_pg'] = is_array($soalPg) ? $soalPg : [];
                     
                     $soalKompleks = $this->cbt->maybeUnserialize($rekapJadwal->soal_kompleks ?? '');
                     $data['soal_kompleks'] = is_array($soalKompleks) ? $soalKompleks : [];
                     
                     $soalJodohkan = $this->cbt->maybeUnserialize($rekapJadwal->soal_jodohkan ?? '');
                     $data['soal_jodohkan'] = is_array($soalJodohkan) ? $soalJodohkan : [];
                     
                     $soalIsian = $this->cbt->maybeUnserialize($rekapJadwal->soal_isian ?? '');
                     $data['soal_isian'] = is_array($soalIsian) ? $soalIsian : [];
                     
                     $soalEsai = $this->cbt->maybeUnserialize($rekapJadwal->soal_esai ?? '');
                     $data['soal_esai'] = is_array($soalEsai) ? $soalEsai : [];
                 }
            }
        }

        if ($this->isAdmin()) {
            return $this->renderView('cbt/nilai/koreksi', $data);
        } elseif ($this->isPanitia()) {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            return $this->renderView('cbt/nilai/koreksi', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            return $this->renderView('cbt/nilai/koreksi', $data);
        }
    }

    public function inputEssai()
    {
        $user = $this->getUser();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $kelas_selected = $this->request->getGet('id_kelas');
        $jadwal_selected = $this->request->getGet('id_jadwal');


        $data = [
            'user' => $user,
            'judul' => 'Input Nilai Manual',
            'subjudul' => '',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'kelas_selected' => $kelas_selected,
            'jadwal_selected' => $jadwal_selected,
            'is_rekap' => true
        ];

        $data['kelas'] = $this->master->getKelasById($kelas_selected);

        $db = \Config\Database::connect();
        $rekapJadwal = $db->table('cbt_rekap_jadwal')->where('id_jadwal', $jadwal_selected)->get()->getRow();
        if (!$rekapJadwal) {
            return $this->showError('Data Rekap tidak ditemukan');
        }

        $jadwal = $this->cbt->maybeUnserialize($rekapJadwal->jadwal);
        $bank = $this->cbt->maybeUnserialize($rekapJadwal->bank_soal);

        $jadwal->bank = $bank;

        // Fetch All Rekap Siswa for this jadwal
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
        $rekapBuilder = $db->table('cbt_rekap_siswa rs')
            ->select('rs.*')
            ->where('rs.id_jadwal', $jadwal_selected);
        $this->onlySiswaAktif($rekapBuilder, 'rs');
        $rekaps = $rekapBuilder->get()->getResult();

        // Filter Logic (id_siswa -> id_kelas mapping)
        // Note: Duplicate logic from get_nilai_rekap, but necessary here.
        if ($kelas_selected) {
            $siswaIds = array_unique(array_column($rekaps, 'id_siswa'));
            $siswaKelasMap = [];
            // Try kelas_siswa
            $kelasData = $db->table('kelas_siswa')
                ->select('id_siswa, id_kelas')
                ->where('id_tp', $tp->id_tp)
                ->where('id_smt', $smt->id_smt)
                ->whereIn('id_siswa', $siswaIds)
                ->get()->getResult();
            foreach ($kelasData as $kd) {
                $siswaKelasMap[(int) $kd->id_siswa] = (int) $kd->id_kelas;
            }
            // Try cbt_sesi_siswa if incomplete
            if (count($siswaKelasMap) < count($siswaIds)) {
                $sesiData = $db->table('cbt_sesi_siswa')
                    ->select('siswa_id, kelas_id')
                    ->where('tp_id', $tp->id_tp)
                    ->where('smt_id', $smt->id_smt)
                    ->whereIn('siswa_id', $siswaIds)
                    ->get()->getResult();
                foreach ($sesiData as $sd) {
                    $siswaKelasMap[$sd->siswa_id] = $sd->kelas_id;
                }
            }

            $rekaps = array_filter($rekaps, function ($r) use ($kelas_selected, $siswaKelasMap) {
                return ($siswaKelasMap[$r->id_siswa] ?? 0) == $kelas_selected;
            });
        }

        // Enhance with Name/NIS
        $ids = array_column($rekaps, 'id_siswa');
        $mapStu = [];
        if (!empty($ids)) {
            $students = $db->table('master_siswa')->whereIn('id_siswa', $ids)->get()->getResult();
            foreach ($students as $s)
                $mapStu[$s->id_siswa] = $s;
        }

        $rekapsiswa = [];
        foreach ($rekaps as $r) {
            $stu = $mapStu[$r->id_siswa] ?? null;
            
            // Resolve Name: Priority (Record Snapshot -> Master Data -> Fallback)
            $nama = !empty($r->nama_siswa) ? $r->nama_siswa : (!empty($stu->nama) ? $stu->nama : 'Siswa Terhapus');
            
            // Resolve No Peserta: Priority (Record Snapshot -> Master Data NIS -> Fallback)
            $no_peserta = !empty($r->no_peserta) ? $r->no_peserta : (!empty($stu->nis) ? $stu->nis : '-');

            $item = new \stdClass();
            $item->id_siswa = $r->id_siswa;
            $item->siswa = (object) [
                'id_siswa' => $r->id_siswa,
                'nama' => $nama,
            ];
            $item->no_peserta = $no_peserta;
            // Add peserta object for nested view compatibility
            $item->peserta = (object) ['nomor_peserta' => $no_peserta];

            $nilai = $this->cbt->maybeUnserialize($r->nilai) ?? (object) [];
            $nilai_input = $this->cbt->maybeUnserialize($r->nilai_input) ?? (object) [];

            // Build nilai_akhir structure for View compatibility
            $item->nilai_akhir = (object) [
                'skor_pg' => (object) [
                    'skor_total' => $nilai_input->pg_nilai ?? $nilai->skor_pg->skor_total ?? 0,
                    'skor_asli' => $nilai->skor_pg->skor_total ?? 0,
                ],
                'skor_kompleks' => (object) [
                    'skor_total' => $nilai_input->kompleks_nilai ?? $nilai->skor_kompleks->skor_total ?? 0,
                    'skor_asli' => $nilai->skor_kompleks->skor_total ?? 0
                ],
                'skor_jodohkan' => (object) [
                    'skor_total' => $nilai_input->jodohkan_nilai ?? $nilai->skor_jodohkan->skor_total ?? 0,
                    'skor_asli' => $nilai->skor_jodohkan->skor_total ?? 0
                ],
                'skor_isian' => (object) [
                    'skor_total' => $nilai_input->isian_nilai ?? $nilai->skor_isian->skor_total ?? 0,
                    'skor_asli' => $nilai->skor_isian->skor_total ?? 0
                ],
                'skor_essai' => (object) [
                    'skor_total' => $nilai_input->essai_nilai ?? $nilai->skor_essai->skor_total ?? 0,
                    'skor_asli' => $nilai->skor_essai->skor_total ?? 0
                ],
                'skor_urutan' => (object) [
                    'skor_total' => $nilai_input->urutan_nilai ?? $nilai->skor_urutan->skor_total ?? 0,
                    'skor_asli' => $nilai->skor_urutan->skor_total ?? 0
                ],
                'skor_cloze' => (object) [
                    'skor_total' => $nilai_input->cloze_nilai ?? $nilai->skor_cloze->skor_total ?? 0,
                    'skor_asli' => $nilai->skor_cloze->skor_total ?? 0
                ],
                'dikoreksi' => $nilai_input->dikoreksi ?? 0
            ];

            // Calculate total score for consistency with live view
            $item->nilai_akhir->skor_total = floatval($item->nilai_akhir->skor_pg->skor_total) +
                                           floatval($item->nilai_akhir->skor_kompleks->skor_total) +
                                           floatval($item->nilai_akhir->skor_jodohkan->skor_total) +
                                           floatval($item->nilai_akhir->skor_isian->skor_total) +
                                           floatval($item->nilai_akhir->skor_essai->skor_total) +
                                           floatval($item->nilai_akhir->skor_urutan->skor_total) +
                                           floatval($item->nilai_akhir->skor_cloze->skor_total);

            $rekapsiswa[] = $item;
        }

        $jadwal->rekapsiswa = $rekapsiswa;
        $data['jadwal'] = $jadwal;

        if ($this->isAdmin()) {
            return $this->renderView('cbt/nilai/input_nilai', $data);
        } elseif ($this->isPanitia()) {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            return $this->renderView('cbt/nilai/input_nilai', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            return $this->renderView('cbt/nilai/input_nilai', $data);
        }
    }

    public function hapusrekap()
    {
        $ids_json = $this->request->getPost('ids');
        $ids = json_decode($ids_json, true);

        if (empty($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
        }

        $db = \Config\Database::connect();

        $trans = $db->transBegin();

        try {
            // Hapus Rekap Jadwal
            $db->table('cbt_rekap_jadwal')->whereIn('id_jadwal', $ids)->delete();

            // Hapus Rekap Siswa
            $db->table('cbt_rekap_siswa')->whereIn('id_jadwal', $ids)->delete();

            // Update Jadwal status rekap
            $db->table('cbt_jadwal')->whereIn('id_jadwal', $ids)->update(['rekap' => 0]);

            $db->transCommit();
            return $this->response->setJSON(['success' => true, 'message' => 'Rekap berhasil dihapus']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus rekap: ' . $e->getMessage()]);
        }
    }

    /**
     * Save manual score corrections for rekap (similar to Cbtnilai::simpanKoreksi but for rekap)
     */
    public function simpankoreksi()
    {
        $siswa_id = $this->request->getPost('siswa');
        $jadwal_id = $this->request->getPost('jadwal');
        $bank_id = $this->request->getPost('bank');
        $field = $this->request->getPost('field');
        $nilai = $this->request->getPost('nilai');

        if (!$siswa_id || !$jadwal_id || !$field || !$nilai) {
            return $this->response->setJSON([
                'success' => 0,
                'message' => 'Parameter tidak lengkap'
            ]);
        }

        $nilaiData = json_decode($nilai, true);
        if (!is_array($nilaiData) || count($nilaiData) === 0) {
            return $this->response->setJSON([
                'success' => 0,
                'message' => 'Data nilai tidak valid'
            ]);
        }

        $db = \Config\Database::connect();

        // Get Both Records to maintain synchronization
        $cbtSiswa = $db->table('cbt_siswa')
            ->where('id_siswa', $siswa_id)
            ->where('id_jadwal', $jadwal_id)
            ->get()->getRow();

        $rekapSiswa = $db->table('cbt_rekap_siswa')
            ->where('id_siswa', $siswa_id)
            ->where('id_jadwal', $jadwal_id)
            ->get()->getRow();

        if (!$cbtSiswa && !$rekapSiswa) {
            return $this->response->setJSON([
                'success' => 0,
                'message' => 'Data siswa tidak ditemukan'
            ]);
        }

        $success = false;
        $totalManual = 0;
        foreach ($nilaiData as $item) {
            $totalManual += floatval($item['koreksi'] ?? 0);
        }

        try {
            $db->transStart();

            // Process cbt_siswa (Live Data)
            if ($cbtSiswa) {
                $nilaiInput = !empty($cbtSiswa->nilai_input) ? $this->cbt->maybeUnserialize($cbtSiswa->nilai_input) : new \stdClass();
                if (is_array($nilaiInput)) $nilaiInput = (object) $nilaiInput;
                if (!is_object($nilaiInput)) $nilaiInput = new \stdClass();
                
                // Sync typo migration if any
                if ($field === 'esai_nilai' && isset($nilaiInput->essai_nilai)) unset($nilaiInput->essai_nilai);
                
                $nilaiInput->{$field} = $nilaiData;
                $nilaiInput->dikoreksi = '1';
                
                $res = $db->table('cbt_siswa')
                    ->where('id_cbt_siswa', $cbtSiswa->id_cbt_siswa)
                    ->update(['nilai_input' => json_encode($nilaiInput)]);
                if ($res) $success = true;
            }

            // Process cbt_rekap_siswa (Snapshot)
            if ($rekapSiswa) {
                $nilaiInputRekap = !empty($rekapSiswa->nilai_input) ? $this->cbt->maybeUnserialize($rekapSiswa->nilai_input) : new \stdClass();
                if (is_array($nilaiInputRekap)) $nilaiInputRekap = (object) $nilaiInputRekap;
                if (!is_object($nilaiInputRekap)) $nilaiInputRekap = new \stdClass();
                
                if ($field === 'esai_nilai' && isset($nilaiInputRekap->essai_nilai)) unset($nilaiInputRekap->essai_nilai);
                
                $nilaiInputRekap->{$field} = $nilaiData;
                $nilaiInputRekap->dikoreksi = '1';
                
                $res = $db->table('cbt_rekap_siswa')
                    ->where('id_rekap_siswa', $rekapSiswa->id_rekap_siswa)
                    ->update(['nilai_input' => json_encode($nilaiInputRekap)]);
                if ($res) $success = true;
            }

            $db->transComplete();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Cbtrekap::simpankoreksi transaction failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => 0,
                'message' => 'Terjadi kesalahan saat menyimpan koreksi'
            ]);
        }

        return $this->response->setJSON([
            'success' => $success ? count($nilaiData) : 0,
            'message' => $success ? 'Berhasil menyimpan koreksi' : 'Gagal menyimpan',
            'total' => $totalManual
        ]);
    }

    /**
     * Mark a student's rekap as corrected
     */
    public function tandaikoreksi()
    {
        $siswa_id = $this->request->getPost('siswa');
        $jadwal_id = $this->request->getPost('jadwal');

        if (!$siswa_id || !$jadwal_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter tidak lengkap'
            ]);
        }

        $db = \Config\Database::connect();

        // Process cbt_siswa
        $cbtSiswa = $db->table('cbt_siswa')
            ->where('id_siswa', $siswa_id)
            ->where('id_jadwal', $jadwal_id)
            ->get()
            ->getRow();

        // Process cbt_rekap_siswa
        $rekapSiswa = $db->table('cbt_rekap_siswa')
            ->where('id_siswa', $siswa_id)
            ->where('id_jadwal', $jadwal_id)
            ->get()
            ->getRow();

        try {
            $db->transStart();

            if ($cbtSiswa) {
                $nilaiInput = !empty($cbtSiswa->nilai_input) ? $this->cbt->maybeUnserialize($cbtSiswa->nilai_input) : new \stdClass();
                if (is_array($nilaiInput)) $nilaiInput = (object) $nilaiInput;
                if (!is_object($nilaiInput)) $nilaiInput = new \stdClass();
                $nilaiInput->dikoreksi = '1';
                $db->table('cbt_siswa')->where('id_cbt_siswa', $cbtSiswa->id_cbt_siswa)->update(['nilai_input' => json_encode($nilaiInput)]);
            }

            if ($rekapSiswa) {
                $nilaiInputRekap = !empty($rekapSiswa->nilai_input) ? $this->cbt->maybeUnserialize($rekapSiswa->nilai_input) : new \stdClass();
                if (is_array($nilaiInputRekap)) $nilaiInputRekap = (object) $nilaiInputRekap;
                if (!is_object($nilaiInputRekap)) $nilaiInputRekap = new \stdClass();
                $nilaiInputRekap->dikoreksi = '1';
                $db->table('cbt_rekap_siswa')->where('id_rekap_siswa', $rekapSiswa->id_rekap_siswa)->update(['nilai_input' => json_encode($nilaiInputRekap)]);
            }

            $db->transComplete();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Cbtrekap::tandaikoreksi transaction failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menandai koreksi'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Berhasil ditandai'
        ]);
    }

    /**
     * Save manual input values for rekap siswa (batch)
     */
    public function simpannilaiinput()
    {
        $db = \Config\Database::connect();

        $jadwal = $this->request->getPost('jadwal');
        $banksoal = $this->request->getPost('bank_soal');
        $nilais = json_decode($this->request->getPost('nilai'));
        $idSiswa = json_decode($this->request->getPost('id_siswa'), true);

        if (!$nilais || !is_array($nilais)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid nilai data']);
        }

        // Get CBT Siswa
        $cbtSiswa = $db->table('cbt_siswa')
            ->select('id_cbt_siswa, id_siswa, nilai_input')
            ->where('id_jadwal', $jadwal)
            ->whereIn('id_siswa', $idSiswa)
            ->get()
            ->getResult();
        $map_cbt = [];
        foreach ($cbtSiswa ?? [] as $cs) $map_cbt[$cs->id_siswa] = $cs;

        // Get Rekap Siswa
        $rekapSiswa = $db->table('cbt_rekap_siswa')
            ->select('id_rekap_siswa, id_siswa, nilai_input')
            ->where('id_jadwal', $jadwal)
            ->whereIn('id_siswa', $idSiswa)
            ->get()
            ->getResult();

        $map_rekap = [];
        foreach ($rekapSiswa ?? [] as $rekap) {
            $map_rekap[$rekap->id_siswa] = $rekap;
        }

        $updated = [];
        $rekaped = [];

        foreach ($nilais as $nilai) {
            $new_vals = [
                'kompleks_nilai' => $nilai->kompleks_nilai ?? '0',
                'jodohkan_nilai' => $nilai->jodohkan_nilai ?? '0',
                'isian_nilai' => $nilai->isian_nilai ?? '0',
                'essai_nilai' => $nilai->essai_nilai ?? '0',
                'urutan_nilai' => $nilai->urutan_nilai ?? '0',
                'cloze_nilai' => $nilai->cloze_nilai ?? '0'
            ];

            // Skip if untouched
            $has_value = false;
            foreach ($new_vals as $v) { if ($v != 0) { $has_value = true; break; } }
            if (!$has_value) continue;

            // Process CBT update
            if (isset($map_cbt[$nilai->id_siswa])) {
                $siswa = $map_cbt[$nilai->id_siswa];
                $nilai_siswa = $this->cbt->maybeUnserialize($siswa->nilai_input);
                if (!is_object($nilai_siswa)) $nilai_siswa = (object) [];
                $changed = false;
                foreach ($new_vals as $k => $v) {
                    if (!isset($nilai_siswa->$k) || $nilai_siswa->$k != $v) { $nilai_siswa->$k = $v; $changed = true; }
                }
                if ($changed || ($nilai_siswa->dikoreksi ?? '0') == '0') {
                    $nilai_siswa->dikoreksi = '1';
                    $updated[] = ['id_cbt_siswa' => $siswa->id_cbt_siswa, 'nilai_input' => json_encode($nilai_siswa)];
                }
            }

            // Process Rekap update
            if (isset($map_rekap[$nilai->id_siswa])) {
                $siswa = $map_rekap[$nilai->id_siswa];
                $nilai_rekap = $this->cbt->maybeUnserialize($siswa->nilai_input);
                if (!is_object($nilai_rekap)) $nilai_rekap = (object) [];
                $changed = false;
                foreach ($new_vals as $k => $v) {
                    if (!isset($nilai_rekap->$k) || $nilai_rekap->$k != $v) { $nilai_rekap->$k = $v; $changed = true; }
                }
                if ($changed || ($nilai_rekap->dikoreksi ?? '0') == '0') {
                    $nilai_rekap->dikoreksi = '1';
                    $rekaped[] = ['id_rekap_siswa' => $siswa->id_rekap_siswa, 'nilai_input' => json_encode($nilai_rekap)];
                }
            }
        }

        try {
            $db->transStart();

            if (count($updated) > 0) $db->table('cbt_siswa')->updateBatch($updated, 'id_cbt_siswa');
            if (count($rekaped) > 0) $db->table('cbt_rekap_siswa')->updateBatch($rekaped, 'id_rekap_siswa');

            $db->transComplete();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Cbtrekap::simpannilaiinput transaction failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => 0,
                'message' => 'Terjadi kesalahan saat menyimpan nilai input'
            ]);
        }

        return $this->response->setJSON([
            'success' => count($updated) + count($rekaped),
            'updated_cbt' => count($updated),
            'updated_rekap' => count($rekaped)
        ]);
    }
}
