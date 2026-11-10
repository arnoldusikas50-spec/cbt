<?php

namespace App\Controllers;

use App\Libraries\CbtSecurityHelper;
use App\Models\CbtAuditModel;
use App\Models\CbtModel;
use App\Services\CbtService;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\KelasModel;
use App\Models\MasterModel;

class Cbtnilai extends BaseController
{
    protected $cbt;
    protected $dashboard;
    protected $dropdown;
    protected $kelas;
    protected $master;
    protected $auditModel;

    public function __construct()
    {
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->dropdown = new DropdownModel();
        $this->kelas = new KelasModel();
        $this->master = new MasterModel();
        $this->auditModel = new CbtAuditModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();

        // Only allow admin, guru, or panitia with jabatan 'panitia' (not 'pengawas')
        if ($this->isPanitia() && !$this->hasPanitiaJabatan()) {
            return $this->showError('Akses ditolak. Hanya Administrator atau Panitia yang dapat mengakses Nilai Ujian.', 403);
        }

        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
            return $this->showError('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini', 403);
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Hasil Ujian Siswa',
            'subjudul' => 'Nilai Siswa',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'tp_jadwal' => $tp->tahun
        ];

        $kelas_selected = $this->request->getGet('id_kelas');
        $jadwal_selected = $this->request->getGet('id_jadwal');
        $data['kelas_selected'] = $kelas_selected;
        $data['jadwal_selected'] = $jadwal_selected;

        $id_guru = null;
        if ($this->inGroup('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $id_guru = $guru->id_guru;
        }

        if ($jadwal_selected !== null) {
            // Fetch jadwals using CbtModel instead of CI3 ORM
            // We need jadwals for dropdown filtering if needed, but logic below iterates.
            // Simplified: Fetch all jadwals then filter based on class selection
            $jadwals = $this->cbt->getAllJadwal($tp->id_tp, $smt->id_smt);
            if ($this->inGroup('guru')) {
                $jadwals = array_filter($jadwals, function ($j) use ($id_guru) {
                    return $j->bank_guru_id == $id_guru;
                });
            }

            $jdwl = [];
            foreach ($jadwals as $jadwal) {
                // bank_kelas in CI4 getAllJadwal is likely string or array
                $bank_kelas = $this->cbt->maybeUnserialize($jadwal->bank_kelas ?? '[]');
                $arrKls = [];
                if (is_array($bank_kelas)) {
                    foreach ($bank_kelas as $bk)
                        $arrKls[] = is_array($bk) ? ($bk['kelas_id'] ?? '') : (is_object($bk) ? ($bk->kelas_id ?? '') : $bk);
                }

                if (in_array($kelas_selected, $arrKls)) {
                    $jdwl[$jadwal->id_jadwal] = ($jadwal->kode_mapel ?? '') . ' (' . ($jadwal->bank_kode ?? '') . ')';
                }
            }
            $data['jadwal'] = $jdwl;
        } else {
            $data['jadwal'] = [];
            $data['nilai'] = [];
        }

        // Force no-cache
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        if ($this->isAdmin()) {
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            return $this->renderView('cbt/nilai/data_nilai', $data);
        } elseif ($this->isPanitia()) {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            return $this->renderView('cbt/nilai/data_nilai', $data);
        } else {
            // For Guru: Get classes taught or all classes?
            // CI3: $this->cbt->getJadwalGuru(..., $id_guru)
            // We can implement getJadwalGuru or fetch manually.
            // Simplest: Get all classes where this guru has schedules?
            // Or just getAllKelas for simplicity if guru teaches many.
            // CI3 logic: Extracts classes from schedules.
            $allJ = $this->cbt->getAllJadwal($tp->id_tp, $smt->id_smt); 
            $kelasIds = [];
            
            // Filter jadwals to only this guru
            foreach ($allJ as $j) {
                if ($j->bank_guru_id == $id_guru) {
                    $bankKelas = $this->cbt->maybeUnserialize($j->bank_kelas ?? '[]');
                    if (is_array($bankKelas)) {
                        foreach ($bankKelas as $bk) {
                            $kid = is_array($bk) ? ($bk['kelas_id'] ?? 0) : (is_object($bk) ? ($bk->kelas_id ?? 0) : $bk);
                            if ($kid && !in_array($kid, $kelasIds)) {
                                $kelasIds[] = $kid;
                            }
                        }
                    }
                }
            }

            // Build dropdown only for classes this guru has schedules for
            $dropdown_kelas = [];
            if (!empty($kelasIds)) {
                $db = \Config\Database::connect();
                $kelasObj = $db->table('master_kelas')
                    ->whereIn('id_kelas', $kelasIds)
                    ->get()->getResult();
                    
                foreach ($kelasObj as $k) {
                    $dropdown_kelas[$k->id_kelas] = $k->nama_kelas;
                }
            }
            $data['kelas'] = $dropdown_kelas;
            return $this->renderView('cbt/nilai/data_nilai', $data);
        }
    }

    public function get_nilai_siswa()
    {
        $kelas_selected = $this->request->getPost('id_kelas');
        $jadwal_selected = $this->request->getPost('id_jadwal');

        // Pagination parameters
        $page = max(1, (int) ($this->request->getPost('page') ?? 1));
        $perPage = max(1, (int) ($this->request->getPost('per_page') ?? 50));

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        try {
            // Call olahNilai from CbtModel
            $jadwal = $this->cbt->olahNilai(1, $tp->id_tp, $smt->id_smt, $kelas_selected, $jadwal_selected, true);

            if ($jadwal && is_object($jadwal)) {
                // Remove heavy data not needed for summary list
                unset($jadwal->soal_kompleks, $jadwal->soal_jodohkan, $jadwal->soal_isian, $jadwal->soal_esai);

                // Pagination slice untuk sesisiswa
                $sesisiswaAll = $jadwal->sesisiswa ?? [];
                $totalRows = is_array($sesisiswaAll) ? count($sesisiswaAll) : 0;
                $offset = ($page - 1) * $perPage;
                $jadwal->sesisiswa = array_slice($sesisiswaAll, $offset, $perPage);
                $jadwal->pagination = (object) [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalRows,
                    'total_pages' => $perPage > 0 ? ceil($totalRows / $perPage) : 1,
                ];
            }

            // Re-fetch classes for dropdown update if needed
            $kelas = $this->master->getAllKelas($tp->id_tp, $smt->id_smt);
            $kelasDropdown = [];
            foreach ($kelas as $k)
                $kelasDropdown[$k->id_kelas] = $k->nama_kelas;

            // Get nama_guru from jadwal->bank or direct property
            $nama_guru = $jadwal->bank->nama_guru ?? $jadwal->nama_guru ?? '-';

            $data = [
                'kelas' => CbtSecurityHelper::esc($kelasDropdown),
                'jadwal' => $jadwal,
                'kelas_selected' => CbtSecurityHelper::esc($kelas_selected),
                'jadwal_selected' => CbtSecurityHelper::esc($jadwal_selected),
                'tp_jadwal' => CbtSecurityHelper::esc($tp->tahun),
                'nama_guru' => CbtSecurityHelper::esc($nama_guru),
                'durasi_siswa' => [],
                'success' => true
            ];
            return $this->response->setJSON($data);

        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage() . ' at line ' . $e->getLine()]);
        }
    }

    public function save_pengurangan()
    {
        $id_jadwal = $this->request->getPost('id_jadwal');
        $siswas = json_decode($this->request->getPost('id_siswa'), true);
        $nilai = json_decode($this->request->getPost('arr_point'));

        $db = \Config\Database::connect();

        $id_siswa = array_column($siswas, 'id_siswa');
        if (empty($id_siswa))
            return $this->response->setJSON(['success' => false]);

        // Fetch existing CBT Siswa
        $allCbtSiswa = $db->table('cbt_siswa')
            ->select('id_cbt_siswa,id_siswa,id_jadwal,pelanggaran')
            ->whereIn('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()->getResult();

        $mapCbt = [];
        foreach ($allCbtSiswa as $s)
            $mapCbt[$s->id_siswa] = $s;

        // Fetch existing Rekap Siswa
        $allRekapSiswa = $db->table('cbt_rekap_siswa')
            ->select('id_rekap_siswa,id_siswa,id_jadwal,pelanggaran')
            ->whereIn('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()->getResult();

        $mapRekap = [];
        foreach ($allRekapSiswa as $s)
            $mapRekap[$s->id_siswa] = $s;

        $updateCbt = [];
        $updateRekap = [];

        foreach ($nilai as $nil) {
            $id = $nil->id_siswa;
            if (isset($mapCbt[$id])) {
                $cbt = $mapCbt[$id];
                $cbt->pelanggaran = json_encode($nil->pelanggaran);
                $updateCbt[] = (array) $cbt;
            }
            if (isset($mapRekap[$id])) {
                $rekap = $mapRekap[$id];
                $rekap->pelanggaran = json_encode($nil->pelanggaran);
                $updateRekap[] = (array) $rekap;
            }
        }

        $db->transStart();
        if (!empty($updateCbt)) {
            $db->table('cbt_siswa')->updateBatch($updateCbt, 'id_cbt_siswa');
        }
        if (!empty($updateRekap)) {
            $db->table('cbt_rekap_siswa')->updateBatch($updateRekap, 'id_rekap_siswa');
        }
        $db->transComplete();

        return $this->response->setJSON(['success' => $db->transStatus()]);
    }

    public function koreksi()
    {
        try {
            $user = $this->getUser();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $id_siswa = $this->request->getGet('id_siswa');
            $id_jadwal = $this->request->getGet('id_jadwal');

            $data = [
                'user' => $user,
                'judul' => 'Koreksi Hasil Siswa',
                'subjudul' => 'Hasil Siswa',
                'setting' => $this->dashboard->getSetting(),
                'tp' => $this->dashboard->getTahun(),
                'tp_active' => $tp,
                'smt' => $this->dashboard->getSemester(),
                'smt_active' => $smt,
                'is_rekap' => 0,
                // Default values
                'siswa' => null,
                'info' => null,
                'id_jadwal' => $id_jadwal,
                'jadwal_selected' => $id_jadwal,
                'kelas_selected' => '',
                'id_bank' => null,
                'jenis' => null,
                'tp_jadwal' => $tp->tahun ?? '',
                'jadwal' => null,
                'soal_pg' => [],
                'soal_kompleks' => [],
                'soal_jodohkan' => [],
                'soal_isian' => [],
                'soal_esai' => [],
                'soal_urutan' => [],
                'soal_cloze' => [],
                // Fallback for rekap view compatibility
                'rekap_mapel' => '-',
                'rekap_guru' => '-'
            ];

            if ($id_siswa && $id_jadwal) {
                $jadwal = $this->cbt->olahNilai(2, $tp->id_tp, $smt->id_smt, $id_siswa, $id_jadwal, true);
                if ($jadwal && isset($jadwal->sesisiswa[0])) {
                    $jadwal->sesisiswa = $jadwal->sesisiswa[0];

                    $data['siswa'] = $jadwal->sesisiswa;
                    $data['info'] = $jadwal->bank;
                    $data['id_jadwal'] = $jadwal->id_jadwal;
                    $data['id_bank'] = $jadwal->id_bank;
                    $data['jenis'] = $jadwal->nama_jenis;
                    $data['tp_jadwal'] = $tp->tahun;
                    $data['jadwal'] = $jadwal;

                    $data['soal_pg'] = $jadwal->soal_pg ?? [];
                    $data['soal_kompleks'] = $jadwal->soal_kompleks ?? [];
                    $data['soal_jodohkan'] = $jadwal->soal_jodohkan ?? [];
                    $data['soal_isian'] = $jadwal->soal_isian ?? [];
                    $data['soal_esai'] = $jadwal->soal_esai ?? [];
                    $data['soal_urutan'] = $jadwal->soal_urutan ?? [];
                    $data['soal_cloze'] = $jadwal->soal_cloze ?? [];

                    // Set actual mapel and guru names for View
                    $data['rekap_mapel'] = $jadwal->bank->nama_mapel ?? $jadwal->nama_mapel ?? '-';
                    $data['rekap_guru'] = $jadwal->bank->nama_guru ?? $jadwal->nama_guru ?? '-';
                }
            }

            // Force no-cache to solve JS update issues
            $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $this->response->setHeader('Pragma', 'no-cache');

            if ($this->isAdmin()) {
                return $this->renderView('cbt/nilai/koreksi', $data);
            } elseif ($this->isPanitia()) {
                $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                return $this->renderView('cbt/nilai/koreksi', $data);
            } else {
                $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                return $this->renderView('cbt/nilai/koreksi', $data);
            }
        } catch (\Throwable $e) {
            die('Error in koreksi: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . $e->getFile());
        }
    }

    /**
     * AJAX endpoint to get koreksi data for a single student
     */
    public function get_koreksi()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $id_siswa = $this->request->getPost('siswa');
        $id_jadwal = $this->request->getPost('jadwal');
        $is_rekap = $this->request->getPost('is_rekap') ?? '0';

        if ($is_rekap == '1') {
            try {
                $db = \Config\Database::connect();
                $rekapSiswa = $db->table('cbt_rekap_siswa a')
                    ->select('a.*, b.nama as nama_siswa, b.nis')
                    ->join('master_siswa b', 'a.id_siswa = b.id_siswa', 'left')
                    ->where('a.id_siswa', $id_siswa)
                    ->where('a.id_jadwal', $id_jadwal)
                    ->get()->getRow();

                if (!$rekapSiswa) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Data siswa tidak ditemukan di rekap']);
                }

                $rekapJadwal = $db->table('cbt_rekap_jadwal')->where('id_jadwal', $id_jadwal)->get()->getRow();
                $bank = $this->cbt->maybeUnserialize($rekapJadwal->bank_soal ?? '');
                
                $nilai_data = $this->cbt->maybeUnserialize($rekapSiswa->nilai) ?: new \stdClass();
                $nilai_input = $this->cbt->maybeUnserialize($rekapSiswa->nilai_input);
                
                // Provide defaults if missing
                if (!isset($nilai_data->skor_pg)) $nilai_data->skor_pg = (object)['detail' => []];
                if (!isset($nilai_data->skor_kompleks)) $nilai_data->skor_kompleks = (object)['detail' => []];
                if (!isset($nilai_data->skor_jodohkan)) $nilai_data->skor_jodohkan = (object)['detail' => []];
                if (!isset($nilai_data->skor_isian)) $nilai_data->skor_isian = (object)['detail' => []];
                if (!isset($nilai_data->skor_essai)) $nilai_data->skor_essai = (object)['detail' => []];
                if (!isset($nilai_data->skor_urutan)) $nilai_data->skor_urutan = (object)['detail' => []];
                if (!isset($nilai_data->skor_cloze)) $nilai_data->skor_cloze = (object)['detail' => []];

                $nilai_data->dikoreksi = $nilai_input->dikoreksi ?? 0;
                
                $siswa = (object) [
                    'id_siswa' => $id_siswa,
                    'siswa_id' => $id_siswa,
                    'nama' => $rekapSiswa->nama_siswa ?? '',
                    'nis' => $rekapSiswa->nis ?? '',
                    'nilai_akhir' => $nilai_data,
                ];

                $soalPg = $this->cbt->maybeUnserialize($rekapJadwal->soal_pg ?? '');
                $soalKompleks = $this->cbt->maybeUnserialize($rekapJadwal->soal_kompleks ?? '');
                $soalJodohkan = $this->cbt->maybeUnserialize($rekapJadwal->soal_jodohkan ?? '');
                $soalIsian = $this->cbt->maybeUnserialize($rekapJadwal->soal_isian ?? '');
                $soalEsai = $this->cbt->maybeUnserialize($rekapJadwal->soal_esai ?? '');
                $soalUrutan = $this->cbt->maybeUnserialize($rekapJadwal->soal_urutan ?? '');
                $soalCloze = $this->cbt->maybeUnserialize($rekapJadwal->soal_cloze ?? '');

                $data = [
                    'success' => true,
                    'siswa' => $siswa,
                    'info' => $bank,
                    'soal_pg' => is_array($soalPg) ? $soalPg : [],
                    'soal_kompleks' => is_array($soalKompleks) ? $soalKompleks : [],
                    'soal_jodohkan' => is_array($soalJodohkan) ? $soalJodohkan : [],
                    'soal_isian' => is_array($soalIsian) ? $soalIsian : [],
                    'soal_esai' => is_array($soalEsai) ? $soalEsai : [],
                    'soal_urutan' => is_array($soalUrutan) ? $soalUrutan : [],
                    'soal_cloze' => is_array($soalCloze) ? $soalCloze : []
                ];
                return $this->response->setJSON($data);
            } catch (\Throwable $e) {
                return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        try {
            $jadwal = $this->cbt->olahNilai(2, $tp->id_tp, $smt->id_smt, $id_siswa, $id_jadwal, true);

            if (!$jadwal || !isset($jadwal->sesisiswa[0])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Data siswa tidak ditemukan']);
            }

            $siswa = $jadwal->sesisiswa[0];

            // Get jawaban details from cbt_jawaban joined with cbt_soal
            $db = \Config\Database::connect();
            
            try {
                // KEAMANAN: gunakan parameterized query, bukan string interpolasi
                $jawabanRows = $db->query(
                    "SELECT s.id_soal, j.jawaban AS jawaban_siswa,
                            s.jawaban AS kunci, s.nomor_soal, s.jenis, s.opsi_a
                     FROM cbt_soal s
                     LEFT JOIN cbt_jawaban j
                            ON j.id_soal   = s.id_soal
                           AND j.id_siswa  = ?
                           AND j.id_jadwal = ?
                     WHERE s.bank_id = ?
                     ORDER BY s.jenis ASC, s.nomor_soal ASC",
                    [(int) $id_siswa, (int) $id_jadwal, (int) $jadwal->id_bank]
                )->getResult();
            } catch (\Exception $e) {
                return $this->response->setJSON(['success' => false, 'message' => 'Query error: ' . $e->getMessage()]);
            }

            // Build detail arrays per type
            $detailPg = [];
            $detailKompleks = [];
            $detailJodohkan = [];
            $detailIsian = [];
            $detailEsai = [];
            $detailUrutan = [];
            $detailCloze = [];

            // Weights from bank soal
            $bank = $jadwal->bank;
            $weights = [
                '1' => ['bobot' => $bank->bobot_pg ?? 0, 'tampil' => $bank->tampil_pg ?? 1],
                '2' => ['bobot' => $bank->bobot_kompleks ?? 0, 'tampil' => $bank->tampil_kompleks ?? 1],
                '3' => ['bobot' => $bank->bobot_jodohkan ?? 0, 'tampil' => $bank->tampil_jodohkan ?? 1],
                '4' => ['bobot' => $bank->bobot_isian ?? 0, 'tampil' => $bank->tampil_isian ?? 1],
                '5' => ['bobot' => $bank->bobot_esai ?? 0, 'tampil' => $bank->tampil_esai ?? 1],
                '6' => ['bobot' => $bank->bobot_urutan ?? 0, 'tampil' => $bank->tampil_urutan ?? 1],
                '7' => ['bobot' => $bank->bobot_cloze ?? 0, 'tampil' => $bank->tampil_cloze ?? 1],
            ];

            // Parse nilai_input strictly as associative array
            $nilaiInputRaw = $siswa->cbtsiswa->nilai_input ?? '{}';
            $nilaiInputArr = is_string($nilaiInputRaw) ? json_decode($nilaiInputRaw, true) : json_decode(json_encode($nilaiInputRaw), true);
            if (!is_array($nilaiInputArr)) $nilaiInputArr = [];

            $manualKoreksi = [
                '1' => (isset($nilaiInputArr['pg_nilai']) && is_array($nilaiInputArr['pg_nilai'])) ? array_column($nilaiInputArr['pg_nilai'], 'koreksi', 'id_soal') : [],
                '2' => (isset($nilaiInputArr['kompleks_nilai']) && is_array($nilaiInputArr['kompleks_nilai'])) ? array_column($nilaiInputArr['kompleks_nilai'], 'koreksi', 'id_soal') : [],
                '3' => (isset($nilaiInputArr['jodohkan_nilai']) && is_array($nilaiInputArr['jodohkan_nilai'])) ? array_column($nilaiInputArr['jodohkan_nilai'], 'koreksi', 'id_soal') : [],
                '4' => (isset($nilaiInputArr['isian_nilai']) && is_array($nilaiInputArr['isian_nilai'])) ? array_column($nilaiInputArr['isian_nilai'], 'koreksi', 'id_soal') : [],
                '5' => (isset($nilaiInputArr['essai_nilai']) && is_array($nilaiInputArr['essai_nilai'])) ? array_column($nilaiInputArr['essai_nilai'], 'koreksi', 'id_soal') : [],
                '6' => (isset($nilaiInputArr['urutan_nilai']) && is_array($nilaiInputArr['urutan_nilai'])) ? array_column($nilaiInputArr['urutan_nilai'], 'koreksi', 'id_soal') : [],
                '7' => (isset($nilaiInputArr['cloze_nilai']) && is_array($nilaiInputArr['cloze_nilai'])) ? array_column($nilaiInputArr['cloze_nilai'], 'koreksi', 'id_soal') : [],
            ];

            $totalSkor = 0;

            foreach ($jawabanRows as $jr) {
                $evaluation = $this->cbt->evaluateAnswer($jr->jenis, $jr->jawaban_siswa, $jr->kunci);
                $skorMentah = $evaluation['skor'];
                $isCorrect = $evaluation['is_correct'];

                $typeWeight = $weights[$jr->jenis] ?? ['bobot' => 0, 'tampil' => 1];
                $maxPoint = $typeWeight['tampil'] > 0 ? ($typeWeight['bobot'] / $typeWeight['tampil']) : 0;
                $skorJawaban = round($skorMentah * $maxPoint, 2);
                $nilaiAsli = $skorJawaban; // Simpan nilai asli sebelum ditimpa

                // Terapkan koreksi manual jika ada
                if (isset($manualKoreksi[$jr->jenis][$jr->id_soal])) {
                    $skorJawaban = floatval($manualKoreksi[$jr->jenis][$jr->id_soal]);
                }

                $totalSkor += $skorJawaban;

                $detail = (object) [
                    'id_soal' => $jr->id_soal,
                    'nomor' => $jr->nomor_soal,
                    'jawaban_siswa' => $this->cbt->maybeUnserialize($jr->jawaban_siswa),
                    'kunci' => $this->cbt->maybeUnserialize($jr->kunci),
                    'benar' => $isCorrect,
                    'skor_jawaban' => $skorJawaban,
                    'nilai_asli' => $nilaiAsli,
                    'analisa' => $isCorrect ? '<span class="badge bg-success">Benar</span>' : ($skorMentah > 0 ? '<span class="badge bg-warning">Sebagian</span>' : '<span class="badge bg-danger">Salah</span>')
                ];

                // Special handling for Menjodohkan (type 3) to support view requirements
                if ($jr->jenis == '3') {
                    $kunci = $detail->kunci;
                    $jawab = $detail->jawaban_siswa;
                    
                    $tabel_soal = [];
                    $tabel_jawab = [];
                    
                    if (isset($kunci->jawaban) && is_array($kunci->jawaban)) {
                        $matrix = $kunci->jawaban;
                        $rows = count($matrix);
                        $cols = count($matrix[0]);
                        
                        // Header (Right side labels)
                        $headers = [];
                        for ($c = 1; $c < $cols; $c++) $headers[] = $matrix[0][$c];
                        
                        // Rows (Left side labels and student answers)
                        for ($r = 1; $r < $rows; $r++) {
                            $leftLabel = $matrix[$r][0];
                            $tabel_soal[] = $leftLabel;
                            
                            // Find student match for this left label (L{r-1})
                            $matchKey = 'L' . ($r - 1);
                            $studentMatchIdx = isset($jawab->jawaban->{$matchKey}) ? intval(substr($jawab->jawaban->{$matchKey}, 1)) : -1;
                            $tabel_jawab[] = ($studentMatchIdx >= 0 && isset($headers[$studentMatchIdx])) ? $headers[$studentMatchIdx] : '-';
                        }
                    }
                    
                    $detail->tabel_soal = $tabel_soal;
                    $detail->tabel_jawab = $tabel_jawab;
                }

                switch ($jr->jenis) {
                    case '1': $detailPg[] = $detail; break;
                    case '2': $detailKompleks[] = $detail; break;
                    case '3': $detailJodohkan[] = $detail; break;
                    case '4': $detailIsian[] = $detail; break;
                    case '5': $detailEsai[] = $detail; break;
                    case '6': $detailUrutan[] = $detail; break;
                    case '7': $detailCloze[] = $detail; break;
                }
            }

            // Build nilai_akhir structure with details
            $siswa->nilai_akhir = (object) [
                'skor_pg' => (object) [
                    'skor_total' => array_sum(array_column($detailPg, 'skor_jawaban')),
                    'skor_asli' => array_sum(array_column($detailPg, 'nilai_asli')),
                    'detail' => $detailPg
                ],
                'skor_kompleks' => (object) [
                    'skor_total' => array_sum(array_column($detailKompleks, 'skor_jawaban')),
                    'skor_asli' => array_sum(array_column($detailKompleks, 'nilai_asli')),
                    'detail' => $detailKompleks
                ],
                'skor_jodohkan' => (object) [
                    'skor_total' => array_sum(array_column($detailJodohkan, 'skor_jawaban')),
                    'skor_asli' => array_sum(array_column($detailJodohkan, 'nilai_asli')),
                    'detail' => $detailJodohkan
                ],
                'skor_isian' => (object) [
                    'skor_total' => array_sum(array_column($detailIsian, 'skor_jawaban')),
                    'skor_asli' => array_sum(array_column($detailIsian, 'nilai_asli')),
                    'detail' => $detailIsian
                ],
                'skor_essai' => (object) [
                    'skor_total' => array_sum(array_column($detailEsai, 'skor_jawaban')),
                    'skor_asli' => array_sum(array_column($detailEsai, 'nilai_asli')),
                    'detail' => $detailEsai
                ],
                'skor_urutan' => (object) [
                    'skor_total' => array_sum(array_column($detailUrutan, 'skor_jawaban')),
                    'skor_asli' => array_sum(array_column($detailUrutan, 'nilai_asli')),
                    'detail' => $detailUrutan
                ],
                'skor_cloze' => (object) [
                    'skor_total' => array_sum(array_column($detailCloze, 'skor_jawaban')),
                    'skor_asli' => array_sum(array_column($detailCloze, 'nilai_asli')),
                    'detail' => $detailCloze
                ],
                'skor_total' => round($totalSkor, 2),
                'dikoreksi' => $nilaiInputArr['dikoreksi'] ?? 0
            ];

            $data = [
                'success' => true,
                'siswa' => $siswa,
                'info' => $jadwal->bank,
                'soal_pg' => $jadwal->soal_pg ?? [],
                'soal_kompleks' => $jadwal->soal_kompleks ?? [],
                'soal_jodohkan' => $jadwal->soal_jodohkan ?? [],
                'soal_isian' => $jadwal->soal_isian ?? [],
                'soal_esai' => $jadwal->soal_esai ?? [],
                'soal_urutan' => $jadwal->soal_urutan ?? [],
                'soal_cloze' => $jadwal->soal_cloze ?? []
            ];

            return $this->response->setJSON($data);

        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function inputEssai()
    {
        try {
            $user = $this->getUser();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            if (!$tp || !$smt) {
                return $this->showError('Tahun Pelajaran atau Semester aktif belum diset.', 500);
            }

            $kelas_selected = $this->request->getGet('id_kelas');
            $jadwal_selected = $this->request->getGet('id_jadwal');

            if (!$kelas_selected || !$jadwal_selected) {
                return $this->showError('Parameter ID Kelas atau ID Jadwal tidak lengkap.', 400);
            }

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
                'jadwal_selected' => $jadwal_selected
            ];

            // Fetch single class info for display
            $db = \Config\Database::connect();
            $kelasObj = $db->table('master_kelas')
                ->where('id_kelas', $kelas_selected)
                ->get()
                ->getRow();
            $data['kelas'] = $kelasObj;

            $jadwal = $this->cbt->olahNilai(1, $tp->id_tp, $smt->id_smt, $kelas_selected, $jadwal_selected);

            if (!$jadwal) {
                return $this->showError('Data jadwal tidak ditemukan atau terjadi kesalahan dalam pengambilan data (olahNilai returned null).', 404);
            }

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
        } catch (\Exception $e) {
            return $this->showError($e->getMessage(), 500);
        }
    }

    /**
     * Mark all / unmark all correction status.
     * OPTIMASI: SELECT + loop JSON decode → JSON_SET langsung di SQL (2 query).
     */
    public function mark_all()
    {
        $id_jadwal = $this->request->getPost('id_jadwal');
        $siswas    = json_decode($this->request->getPost('id_siswa'), true);

        if (!$siswas || !is_array($siswas)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid student data']);
        }

$db = \Config\Database::connect();
        $id_siswa   = array_column($siswas, 'id_siswa');

        $unmark = [];
        $mark   = [];
        foreach ($siswas as $s) {
            if (($s['memulai'] ?? '0') == '2') {
                $unmark[] = $s['id_siswa'];
            } else {
                $mark[] = $s['id_siswa'];
            }
        }

        $db->transStart();
        $updated = 0;
        foreach ([
            ['ids' => $mark,   'val' => '1'],
            ['ids' => $unmark, 'val' => '0'],
        ] as $grp) {
            if (empty($grp['ids'])) continue;
            $val = $grp['val'];
            $ids = implode(',', array_map('intval', $grp['ids']));

            $db->query(
                "UPDATE cbt_siswa
                 SET nilai_input = JSON_SET(COALESCE(NULLIF(nilai_input,''), '{}'), '$.dikoreksi', ?)
                 WHERE id_jadwal = ? AND id_siswa IN ($ids)",
                [$val, $id_jadwal]
            );
            $updated += $db->affectedRows();

            $db->query(
                "UPDATE cbt_rekap_siswa
                 SET nilai_input = JSON_SET(COALESCE(NULLIF(nilai_input,''), '{}'), '$.dikoreksi', ?)
                 WHERE id_jadwal = ? AND id_siswa IN ($ids)",
                [$val, $id_jadwal]
            );
        }
        $db->transComplete();

        return $this->response->setJSON([
            'success'  => $db->transStatus(),
            'siswa'    => $siswas,
            'updated'  => $updated,
        ]);
    }

    public function simpanNilaiInput()
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
            ->select('id_cbt_siswa, id_siswa, nilai_input, status')
            ->where('id_jadwal', $jadwal)
            ->whereIn('id_siswa', $idSiswa)
            ->get()
            ->getResult();

        $map_cbt = [];
        foreach ($cbtSiswa as $cbt) {
            $map_cbt[$cbt->id_siswa] = $cbt;
        }

        // Get Rekap Siswa
        $rekapSiswa = $db->table('cbt_rekap_siswa')
            ->select('id_rekap_siswa, id_siswa, nilai_input, status')
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
        $blm_selesai = [];

        foreach ($nilais as $nilai) {
            $new_vals = [
                'kompleks_nilai' => $nilai->kompleks_nilai ?? '0',
                'jodohkan_nilai' => $nilai->jodohkan_nilai ?? '0',
                'isian_nilai' => $nilai->isian_nilai ?? '0',
                'essai_nilai' => $nilai->essai_nilai ?? '0',
                'urutan_nilai' => $nilai->urutan_nilai ?? '0',
                'cloze_nilai' => $nilai->cloze_nilai ?? '0'
            ];

            // 1. Skip if all inputs are zero (untouched/un-graded students)
            $has_value = false;
            foreach ($new_vals as $v) {
                if ($v != 0) {
                    $has_value = true;
                    break;
                }
            }
            if (!$has_value) continue;

            // 2. Process CBT Update
            if (isset($map_cbt[$nilai->id_siswa])) {
                $siswa = $map_cbt[$nilai->id_siswa];
                $nilai_siswa = json_decode($siswa->nilai_input ?? '{}');
                if (!is_object($nilai_siswa)) $nilai_siswa = (object) [];

                $changed = false;
                foreach ($new_vals as $k => $v) {
                    if (!isset($nilai_siswa->$k) || $nilai_siswa->$k != $v) {
                        $nilai_siswa->$k = $v;
                        $changed = true;
                    }
                }

                if ($changed || ($nilai_siswa->dikoreksi ?? '0') == '0') {
                    $nilai_siswa->dikoreksi = '1';
                    $updated[] = [
                        'id_cbt_siswa' => $siswa->id_cbt_siswa,
                        'nilai_input' => json_encode($nilai_siswa)
                    ];
                }
            }

            // 3. Process Rekap Update
            if (isset($map_rekap[$nilai->id_siswa])) {
                $siswa = $map_rekap[$nilai->id_siswa];
                $nilai_rekap = json_decode($siswa->nilai_input ?? '{}');
                if (!is_object($nilai_rekap)) $nilai_rekap = (object) [];

                $changed = false;
                foreach ($new_vals as $k => $v) {
                    if (!isset($nilai_rekap->$k) || $nilai_rekap->$k != $v) {
                        $nilai_rekap->$k = $v;
                        $changed = true;
                    }
                }

                if ($changed || ($nilai_rekap->dikoreksi ?? '0') == '0') {
                    $nilai_rekap->dikoreksi = '1';
                    $rekaped[] = [
                        'id_rekap_siswa' => $siswa->id_rekap_siswa,
                        'nilai_input' => json_encode($nilai_rekap)
                    ];
                }
            }
        }

        $db->transStart();
        if (count($updated) > 0) {
            $db->table('cbt_siswa')->updateBatch($updated, 'id_cbt_siswa');
        }
        if (count($rekaped) > 0) {
            $db->table('cbt_rekap_siswa')->updateBatch($rekaped, 'id_rekap_siswa');
        }
        $db->transComplete();

        return $this->response->setJSON([
            'success' => $db->transStatus() && (count($updated) + count($rekaped) > 0),
            'updated_cbt' => count($updated),
            'updated_rekap' => count($rekaped),
            'blm_selesai' => count($blm_selesai)
        ]);
    }

    /**
     * Mark a student's answers as corrected.
     * OPTIMASI: SELECT + UPDATE → 1 UPDATE langsung via JSON_SET (atomik).
     */
    public function tandaiKoreksi()
    {
        $siswa_id  = $this->request->getPost('siswa');
        $jadwal_id = $this->request->getPost('jadwal');

        if (!$siswa_id || !$jadwal_id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parameter tidak lengkap']);
        }

        $db = \Config\Database::connect();

        // ── OPTIMASI: gunakan JSON_SET agar tidak perlu SELECT dulu ──────────
        // JSON_SET membuat objek baru jika nilai_input NULL/kosong lewat COALESCE
        $ok = $db->query(
            "UPDATE cbt_siswa
             SET nilai_input = JSON_SET(
                 COALESCE(NULLIF(nilai_input, ''), '{}'),
                 '$.dikoreksi', '1'
             )
             WHERE id_siswa  = ?
               AND id_jadwal = ?",
            [(int) $siswa_id, (int) $jadwal_id]
        );

        $affected = $db->affectedRows();

        if ($ok && $affected > 0) {
            // Ambil nilai baru untuk audit trail (opsional, tidak blokir response)
            $this->auditModel->logNilaiChange(
                $siswa_id, $jadwal_id,
                'MARK_CORRECTED',
                null,
                ['dikoreksi' => '1'],
                'Tandai sudah dikoreksi'
            );
        }

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Berhasil ditandai' : 'Gagal menandai',
        ]);
    }


    /**
     * Save manual score corrections.
     * OPTIMASI: hapus semua debug log + paralel fetch cbt_siswa & cbt_rekap_siswa
     * dalam 1 multi-row update.
     */
    public function simpanKoreksi()
    {
        $siswa_id  = $this->request->getPost('siswa');
        $jadwal_id = $this->request->getPost('jadwal');
        $bank_id   = $this->request->getPost('bank');
        $field     = $this->request->getPost('field');
        $nilai     = $this->request->getPost('nilai');

        if (!$siswa_id || !$jadwal_id || !$field || !$nilai) {
            return $this->response->setJSON(['success' => 0, 'message' => 'Parameter tidak lengkap']);
        }

        // Whitelist field untuk cegah arbitrary key injection
        $allowedFields = ['pg_nilai', 'kompleks_nilai', 'jodohkan_nilai', 'isian_nilai',
                          'essai_nilai', 'urutan_nilai', 'cloze_nilai'];
        if (!in_array($field, $allowedFields, true)) {
            return $this->response->setJSON(['success' => 0, 'message' => 'Field tidak valid']);
        }

        $nilaiData = json_decode($nilai, true);
        if (!is_array($nilaiData) || count($nilaiData) === 0) {
            return $this->response->setJSON(['success' => 0, 'message' => 'Data nilai tidak valid']);
        }

        // Validasi range nilai
        $totalManual = 0;
        foreach ($nilaiData as $item) {
            $v = floatval($item['koreksi'] ?? 0);
            if ($v < 0 || $v > 100) {
                return $this->response->setJSON(['success' => 0, 'message' => 'Nilai harus dalam range 0-100']);
            }
            $totalManual += $v;
        }

        $db = \Config\Database::connect();

        // ── OPTIMASI: 2 SELECT + kondisional → 1 query dengan UNION ──────────
        // Ambil cbt_siswa dan cbt_rekap_siswa sekaligus
        $cbtSiswa = $db->table('cbt_siswa')
            ->where('id_siswa', $siswa_id)
            ->where('id_jadwal', $jadwal_id)
            ->get()->getRow();

        $rekapSiswa = $db->table('cbt_rekap_siswa')
            ->where('id_siswa', $siswa_id)
            ->where('id_jadwal', $jadwal_id)
            ->get()->getRow();

        if (!$cbtSiswa && !$rekapSiswa) {
            return $this->response->setJSON(['success' => 0, 'message' => 'Data siswa tidak ditemukan']);
        }

        $updated   = false;
        $oldStr    = null;
        $newStr    = null;
        $newRekapStr = null;

        try {
            $db->transStart();

            // Update cbt_siswa
            if ($cbtSiswa) {
                $nilaiInput = !empty($cbtSiswa->nilai_input)
                    ? $this->cbt->maybeUnserialize($cbtSiswa->nilai_input)
                    : new \stdClass();
                if (is_array($nilaiInput))  $nilaiInput = (object) $nilaiInput;
                if (!is_object($nilaiInput)) $nilaiInput = new \stdClass();

                $oldStr = $cbtSiswa->nilai_input;
                $nilaiInput->{$field} = $nilaiData;
                $newStr = json_encode($nilaiInput);

                if ($oldStr !== $newStr) {
                    $res = $db->table('cbt_siswa')
                        ->where('id_cbt_siswa', $cbtSiswa->id_cbt_siswa)
                        ->update(['nilai_input' => $newStr]);
                    if ($res && $db->affectedRows() > 0) $updated = true;
                } else {
                    $updated = true; // tidak ada perubahan = sukses
                }
            }

            // Update cbt_rekap_siswa
            if ($rekapSiswa) {
                $nilaiRekap = !empty($rekapSiswa->nilai_input)
                    ? $this->cbt->maybeUnserialize($rekapSiswa->nilai_input)
                    : new \stdClass();
                if (is_array($nilaiRekap))  $nilaiRekap = (object) $nilaiRekap;
                if (!is_object($nilaiRekap)) $nilaiRekap = new \stdClass();

                $nilaiRekap->{$field} = $nilaiData;
                $newRekapStr = json_encode($nilaiRekap);

                if ($rekapSiswa->nilai_input !== $newRekapStr) {
                    $res = $db->table('cbt_rekap_siswa')
                        ->where('id_rekap_siswa', $rekapSiswa->id_rekap_siswa)
                        ->update(['nilai_input' => $newRekapStr]);
                    if ($res && $db->affectedRows() > 0) $updated = true;
                } else {
                    $updated = true;
                }
            }

            $db->transComplete();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Cbtnilai::simpanKoreksi transaction failed: ' . $e->getMessage());
            return $this->response->setJSON(['success' => 0, 'message' => 'Terjadi kesalahan saat menyimpan koreksi']);
        }

        // Audit trail (hanya jika ada perubahan)
        if ($updated && $oldStr !== $newStr) {
            $this->auditModel->logNilaiChange(
                $siswa_id, $jadwal_id,
                'CORRECT',
                ['nilai_input' => $oldStr],
                ['nilai_input' => $newStr, 'field' => $field, 'total' => $totalManual],
                'Koreksi manual untuk field: ' . $field
            );
        }

        return $this->response->setJSON([
            'success' => $updated ? count($nilaiData) : 0,
            'message' => $updated ? 'Berhasil menyimpan koreksi' : 'Gagal menyimpan',
            'total'   => $totalManual,
        ]);
    }

    /**
     * Batch recalculate scores for all students in a schedule.
     * OPTIMASI: antrikan semua siswa ke cbt_scoring_queue (tanpa exec CLI),
     * sehingga response langsung kembali ke guru tanpa menunggu kalkulasi.
     * Konsumen web-cron /cron/scoring_queue atau self-driver memprosesnya.
     * Fallback ke sinkronus (batchHitungNilai dengan chunking) bila enqueue gagal.
     */
    public function recalculateAll()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $idJadwal = (int) $this->request->getPost('id_jadwal');
        if (!$idJadwal) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID Jadwal diperlukan']);
        }

        $db = \Config\Database::connect();

        $students = $db->table('cbt_siswa')
            ->select('id_siswa')
            ->where('id_jadwal', $idJadwal)
            ->where('status', '2')
            ->get()->getResultArray();

        if (empty($students)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada siswa yang sudah selesai ujian']);
        }

        $totalSiswa = count($students);

        // ── Enqueue semua siswa ke scoring queue (tanpa exec CLI) ───────────
        $cbtService = new CbtService();
        $enqueued   = 0;
        foreach ($students as $s) {
            if ($cbtService->enqueueScoring((int) $s['id_siswa'], $idJadwal, true)) {
                $enqueued++;
            }
        }

        if ($enqueued > 0) {
            return $this->response->setJSON([
                'success'       => true,
                'async'         => true,
                'message'       => "Kalkulasi nilai untuk {$enqueued} siswa sedang diproses di background.",
                'success_count' => $enqueued,
                'fail_count'    => $totalSiswa - $enqueued,
            ]);
        }

        // ── Fallback: sinkronus dengan chunking ──────────────────────────────
        $cbtModel     = new CbtModel();
        $studentIds   = array_column($students, 'id_siswa');
        $successCount = 0;
        $failCount    = 0;
        $errors       = [];
        $chunkSize    = 200;

        foreach (array_chunk($studentIds, $chunkSize) as $chunk) {
            $res           = $cbtModel->batchHitungNilai($chunk, $idJadwal);
            $successCount += $res['success'];
            $failCount    += $res['failed'];
            if (!empty($res['errors'])) {
                $errors = array_merge($errors, array_slice($res['errors'], 0, 20));
            }
        }

        return $this->response->setJSON([
            'success'       => $successCount > 0,
            'async'         => false,
            'message'       => "Berhasil: {$successCount} siswa, Gagal: {$failCount} siswa",
            'success_count' => $successCount,
            'fail_count'    => $failCount,
            'errors'        => $errors,
        ]);
    }

    /**
     * Get detailed score breakdown for a student
     * Returns breakdown per question type with weighted scores
     */
    public function getDetailedScore()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $idSiswa = $this->request->getPost('id_siswa');
        $idJadwal = $this->request->getPost('id_jadwal');

        if (!$idSiswa || !$idJadwal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parameter tidak lengkap']);
        }

        $db = \Config\Database::connect();

        // Get nilai record
        $nilai = $db->table('cbt_nilai')
            ->where('id_siswa', $idSiswa)
            ->where('id_jadwal', $idJadwal)
            ->get()->getRow();

        if (!$nilai) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data nilai tidak ditemukan'
            ]);
        }

        // Parse detail JSON if available
        $detailScores = [];
        if (!empty($nilai->detail_json)) {
            $detailScores = json_decode($nilai->detail_json, true) ?? [];
        }

        // Get jadwal and bobot info
        $jadwal = $db->table('cbt_jadwal j')
            ->select('j.*, b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, 
                b.bobot_isian, b.bobot_esai, b.bobot_urutan, b.bobot_cloze')
            ->join('cbt_bank_soal b', 'j.id_bank = b.id_bank', 'left')
            ->where('j.id_jadwal', $idJadwal)
            ->get()->getRow();

        // Build response with type labels
        $typeLabels = [
            '1' => 'Pilihan Ganda',
            '2' => 'PG Kompleks',
            '3' => 'Menjodohkan',
            '4' => 'Isian Singkat',
            '5' => 'Esai',
            '6' => 'Mengurutkan',
            '7' => 'Isian Rumpang'
        ];

        $breakdown = [];
        foreach ($detailScores as $type => $data) {
            $breakdown[] = [
                'type' => $type,
                'label' => $typeLabels[$type] ?? 'Tipe ' . $type,
                'skor_mentah' => $data['skor_mentah'] ?? 0,
                'total_soal' => $data['total_soal'] ?? 0,
                'persentase' => $data['persentase'] ?? 0,
                'bobot' => $data['bobot'] ?? 0,
                'nilai_berbobot' => $data['nilai_berbobot'] ?? 0
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'id_siswa' => $idSiswa,
                'id_jadwal' => $idJadwal,
                'pg_nilai' => $nilai->pg_nilai ?? 0,
                'nilai_asli' => $nilai->nilai_asli ?? $nilai->pg_nilai ?? 0,
                'pg_benar' => $nilai->pg_benar ?? 0,
                'breakdown' => $breakdown,
                'bobot' => [
                    'pg' => $jadwal->bobot_pg ?? 0,
                    'kompleks' => $jadwal->bobot_kompleks ?? 0,
                    'jodohkan' => $jadwal->bobot_jodohkan ?? 0,
                    'isian' => $jadwal->bobot_isian ?? 0,
                    'esai' => $jadwal->bobot_esai ?? 0,
                    'urutan' => $jadwal->bobot_urutan ?? 0,
                    'cloze' => $jadwal->bobot_cloze ?? 0
                ]
            ]
        ]);
    }

    /**
     * Export detailed scores to Excel format
     */
    public function exportDetailedScores()
    {
        $idJadwal = $this->request->getGet('jadwal');
        $idKelas = $this->request->getGet('kelas');

        if (!$idJadwal) {
            return redirect()->back()->with('error', 'Jadwal diperlukan');
        }

        $cbtModel = new CbtModel();
        $db = \Config\Database::connect();

        // Get jadwal info
        $jadwal = $db->table('cbt_jadwal j')
            ->select('j.*, b.bank_nama, b.bobot_pg, b.bobot_kompleks, 
                b.bobot_jodohkan, b.bobot_isian, b.bobot_esai, b.bobot_urutan, b.bobot_cloze')
            ->join('cbt_bank_soal b', 'j.id_bank = b.id_bank', 'left')
            ->where('j.id_jadwal', $idJadwal)
            ->get()->getRow();

        if (!$jadwal) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan');
        }

        // Get all students with scores
        $query = $db->table('cbt_nilai cn')
            ->select('cn.*, s.nama as nama_siswa, s.nis, k.nama_kelas')
            ->join('master_siswa s', 'cn.id_siswa = s.id_siswa', 'left')
            ->join('kelas_siswa ks', 's.id_siswa = ks.id_siswa', 'left')
            ->join('master_kelas k', 'ks.kelas_id = k.id_kelas', 'left')
            ->where('cn.id_jadwal', $idJadwal);
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
        $this->onlySiswaAktif($query, 'cn');

        if ($idKelas) {
            $query->where('ks.kelas_id', $idKelas);
        }

        $scores = $query->orderBy('k.nama_kelas', 'ASC')
            ->orderBy('s.nama', 'ASC')
            ->get()->getResult();

        // Prepare data for view/export
        $data = [
            'jadwal' => $jadwal,
            'scores' => $scores,
            'title' => 'Detail Nilai - ' . ($jadwal->bank_nama ?? 'Ujian')
        ];

        // Return as JSON for now (can be converted to Excel export)
        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Export scores to xAPI-like JSON format for interoperability
     */
    public function exportXAPI()
    {
        $idJadwal = $this->request->getGet('jadwal');
        if (!$idJadwal) {
            return $this->response->setJSON(['success' => false, 'error' => 'Jadwal ID required']);
        }

        $db = \Config\Database::connect();
        
        // Get Jadwal and Bank info
        $jadwal = $db->table('cbt_jadwal j')
            ->select('j.*, b.bank_nama, b.id_bank')
            ->join('cbt_bank_soal b', 'j.id_bank = b.id_bank')
            ->where('j.id_jadwal', $idJadwal)
            ->get()->getRow();

        if (!$jadwal) {
            return $this->response->setJSON(['success' => false, 'error' => 'Jadwal not found']);
        }

        // Get all scores for this jadwal
        $xapiBuilder = $db->table('cbt_nilai cn')
            ->select('cn.*, s.nama as nama_siswa, s.nis, cs.mulai, cs.selesai, cs.lama_ujian')
            ->join('master_siswa s', 'cn.id_siswa = s.id_siswa')
            ->join('cbt_siswa cs', 'cn.id_siswa = cs.id_siswa AND cn.id_jadwal = cs.id_jadwal')
            ->where('cn.id_jadwal', $idJadwal);
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
        $this->onlySiswaAktif($xapiBuilder, 'cn');
        $scores = $xapiBuilder->get()->getResult();

        $statements = [];
        $baseUrl = base_url();
        
        foreach ($scores as $s) {
            $statements[] = [
                'actor' => [
                    'name' => $s->nama_siswa,
                    'account' => [
                        'homePage' => $baseUrl,
                        'name' => $s->nis
                    ],
                    'objectType' => 'Agent'
                ],
                'verb' => [
                    'id' => 'http://adlnet.gov/expapi/verbs/completed',
                    'display' => ['en-US' => 'completed']
                ],
                'object' => [
                    'id' => $baseUrl . '/cbt/test/' . $idJadwal,
                    'definition' => [
                        'name' => ['en-US' => $jadwal->bank_nama],
                        'type' => 'http://adlnet.gov/expapi/activities/assessment'
                    ],
                    'objectType' => 'Activity'
                ],
                'result' => [
                    'score' => [
                        'scaled' => floatval($s->pg_nilai) / 100,
                        'raw' => floatval($s->pg_nilai),
                        'min' => 0,
                        'max' => 100
                    ],
                    'duration' => $s->lama_ujian, // In PT standard ideally, but stored as H:i:s
                    'completion' => true,
                    'success' => floatval($s->pg_nilai) >= 75
                ],
                'timestamp' => $s->selesai ? date('c', strtotime($s->selesai)) : date('c')
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'metadata' => [
                'version' => '1.0.0',
                'standard' => 'xAPI-Lite',
                'generated_at' => date('c'),
                'assessment_name' => $jadwal->bank_nama,
                'total_statements' => count($statements)
            ],
            'statements' => $statements
        ]);
    }

    /**
     * Get scoring statistics for a jadwal
     * Shows average, highest, lowest scores and grade distribution
     */
    public function getScoringStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $idJadwal = $this->request->getPost('id_jadwal');
        if (!$idJadwal) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID Jadwal diperlukan']);
        }

        $cbtModel = new CbtModel();

        try {
            $stats = $cbtModel->getScoringStats($idJadwal);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getScoringStats error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate scoring formula
     * Checks if weights are properly configured for a bank soal
     */
    public function validateScoringConfig()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $idJadwal = $this->request->getPost('id_jadwal');
        if (!$idJadwal) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID Jadwal diperlukan']);
        }

        $db = \Config\Database::connect();

        // Get jadwal with bank info
        $jadwal = $db->table('cbt_jadwal j')
            ->select('j.*, b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, 
                b.bobot_isian, b.bobot_esai, b.bobot_urutan, b.bobot_cloze,
                b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, 
                b.tampil_isian, b.tampil_esai, b.tampil_urutan, b.tampil_cloze')
            ->join('cbt_bank_soal b', 'j.id_bank = b.id_bank', 'left')
            ->where('j.id_jadwal', $idJadwal)
            ->get()->getRow();

        if (!$jadwal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan'
            ]);
        }

        $issues = [];
        $totalWeight = 0;

        // Check each question type
        $types = [
            'pg' => ['tampil' => $jadwal->tampil_pg, 'bobot' => $jadwal->bobot_pg, 'label' => 'Pilihan Ganda'],
            'kompleks' => ['tampil' => $jadwal->tampil_kompleks, 'bobot' => $jadwal->bobot_kompleks, 'label' => 'PG Kompleks'],
            'jodohkan' => ['tampil' => $jadwal->tampil_jodohkan, 'bobot' => $jadwal->bobot_jodohkan, 'label' => 'Menjodohkan'],
            'isian' => ['tampil' => $jadwal->tampil_isian, 'bobot' => $jadwal->bobot_isian, 'label' => 'Isian Singkat'],
            'esai' => ['tampil' => $jadwal->tampil_esai, 'bobot' => $jadwal->bobot_esai, 'label' => 'Esai'],
            'urutan' => ['tampil' => $jadwal->tampil_urutan, 'bobot' => $jadwal->bobot_urutan, 'label' => 'Mengurutkan'],
            'cloze' => ['tampil' => $jadwal->tampil_cloze ?? 0, 'bobot' => $jadwal->bobot_cloze ?? 0, 'label' => 'Isian Rumpang']
        ];

        foreach ($types as $key => $type) {
            if ($type['tampil'] > 0) {
                if ($type['bobot'] == 0) {
                    $issues[] = "{$type['label']}: Bobot belum diatur (default: persentase sederhana)";
                }
                $totalWeight += floatval($type['bobot']);
            }
        }

        // Check if total weight makes sense
        if ($totalWeight > 0 && $totalWeight != 100) {
            $issues[] = "Total bobot = {$totalWeight}% (biasanya 100%)";
        }

        // Check katrol settings
        if ($jadwal->katrol_max > 0 && $jadwal->katrol_min > 0) {
            if ($jadwal->katrol_max <= $jadwal->katrol_min) {
                $issues[] = "Katrol: nilai max ({$jadwal->katrol_max}) harus lebih besar dari min ({$jadwal->katrol_min})";
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'jadwal_id' => $idJadwal,
                'total_weight' => $totalWeight,
                'issues' => $issues,
                'is_valid' => empty($issues),
                'types' => $types
            ]
        ]);
    }

    /**
     * save_katrol — Hitung & simpan nilai katrol (normalisasi linear).
     * OPTIMASI: loop UPDATE per-baris → updateBatch sekali jalan untuk
     * kedua tabel (cbt_nilai dan cbt_rekap_siswa).
     */
    public function save_katrol()
    {
        $id_jadwal    = $this->request->getPost('id_jadwal');
        $id_kelas     = $this->request->getPost('id_kelas');
        $min_konversi = (float) $this->request->getPost('min');
        $max_konversi = (float) $this->request->getPost('max');

        if (!$id_jadwal) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID Jadwal tidak boleh kosong']);
        }

        $db  = \Config\Database::connect();
        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // 1. Simpan konfigurasi katrol ke jadwal
        $db->table('cbt_jadwal')->where('id_jadwal', $id_jadwal)
            ->update(['katrol_min' => $min_konversi, 'katrol_max' => $max_konversi]);

        // ── Helper: hitung nilai katrol (normalisasi linear min-max) ─────────
        $calcKatrol = function (float $skorAsli, float $minAsli, float $rangeAsli,
                                float $minKonversi, float $rangeTarget): float {
            if ($rangeAsli <= 0) return $minKonversi + $rangeTarget; // semua sama → max
            $katrol = $minKonversi + (($skorAsli - $minAsli) / $rangeAsli) * $rangeTarget;
            return round(max($skorAsli, $katrol));
        };

        // 2. Batch update cbt_nilai ─────────────────────────────────────────
        $builder = $db->table('cbt_nilai a')
            ->select('a.id_nilai, a.nilai_asli')
            ->join('kelas_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->where('a.id_jadwal', $id_jadwal);

        if ($id_kelas) {
            $builder->where('b.id_tp', $tp->id_tp)
                    ->where('b.id_smt', $smt->id_smt)
                    ->where('b.id_kelas', $id_kelas);
        }

        $nilaiSiswa = $builder->get()->getResult();

        if (!empty($nilaiSiswa)) {
            $asliArr   = array_column($nilaiSiswa, 'nilai_asli');
            $minAsli   = (float) min($asliArr);
            $maxAsli   = (float) max($asliArr);
            $rangeAsli = $maxAsli - $minAsli;
            $rangeTgt  = $max_konversi - $min_konversi;

            // OPTIMASI: kumpulkan semua update lalu kirim dalam 1 updateBatch
            $batchNilai = [];
            foreach ($nilaiSiswa as $s) {
                $batchNilai[] = [
                    'id_nilai'     => $s->id_nilai,
                    'nilai_katrol' => $calcKatrol((float) $s->nilai_asli, $minAsli, $rangeAsli, $min_konversi, $rangeTgt),
                ];
            }
            $db->table('cbt_nilai')->updateBatch($batchNilai, 'id_nilai');
        } else {
            // Tidak ada nilai → set default agar $rangeAsli tersedia untuk rekap
            $minAsli = 0; $rangeAsli = 0; $rangeTgt = $max_konversi - $min_konversi;
        }

        // 3. Batch update cbt_rekap_siswa ─────────────────────────────────
        $builderRekap = $db->table('cbt_rekap_siswa')
            ->where('id_jadwal', $id_jadwal);
        if ($id_kelas) $builderRekap->where('id_kelas', $id_kelas);

        $rekapSiswa = $builderRekap->get()->getResult();

        if (!empty($rekapSiswa)) {
            $batchRekap = [];
            foreach ($rekapSiswa as $rs) {
                $nilaiData = $this->cbt->maybeUnserialize($rs->nilai ?? '');
                $skorAsli  = 0.0;
                if (is_array($nilaiData) && isset($nilaiData['skor_total'])) {
                    $skorAsli = (float) $nilaiData['skor_total'];
                } elseif (is_object($nilaiData) && isset($nilaiData->skor_total)) {
                    $skorAsli = (float) $nilaiData->skor_total;
                }
                $batchRekap[] = [
                    'id_rekap_siswa' => $rs->id_rekap_siswa,
                    'nilai_katrol'   => $calcKatrol($skorAsli, $minAsli, $rangeAsli, $min_konversi, $rangeTgt),
                ];
            }
            $db->table('cbt_rekap_siswa')->updateBatch($batchRekap, 'id_rekap_siswa');
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Nilai hasil katrol berhasil disimpan ke database']);
    }
}

