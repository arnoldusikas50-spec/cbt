<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\NilaiModel;
use App\Models\TujuanPembelajaranModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Services\GuruNilaiAdapterService;

class Nilai extends BaseController
{
    protected $nilaiModel;
    protected $tpModel;
    protected $master;
    protected $dashboard;
    protected $adapter;

    public function __construct()
    {
        $this->nilaiModel = new NilaiModel();
        // Ensure database is up to date
        $this->nilaiModel->initialize();

        $this->tpModel = new TujuanPembelajaranModel();
        $this->master = new MasterModel();
        $this->dashboard = new DashboardModel();
        $this->adapter = new GuruNilaiAdapterService($this->nilaiModel);
    }

    private $user;
    private $guru;
    private $tp;
    private $smt;

    private function initUser()
    {
        $this->user = $this->getUser();
        if (!$this->user)
            return false;

        $tpSmt = $this->getTpSmt();
        $this->tp = $tpSmt->tp;
        $this->smt = $tpSmt->smt;

        $isAdmin = ($this->user->role == self::ROLE_ADMIN || $this->user->role == self::ROLE_PANITIA);
        $id_guru_request = $this->request->getVar('id_guru');
        if (!$id_guru_request) {
            $json = $this->safeGetJSON();
            $id_guru_request = $json ? ($json->id_guru ?? null) : null;
        }

        if ($isAdmin && $id_guru_request) {
            $this->guru = $this->dashboard->getDataGuruById($id_guru_request, $this->tp->id_tp, $this->smt->id_smt);
        } else {
            $this->guru = $this->dashboard->getDataGuruByUserId($this->user->id, $this->tp->id_tp, $this->smt->id_smt);
        }

        return true;
    }

    /**
     * Resolve guru for AJAX endpoints.
     * Admin/panitia can pass id_guru via POST to act on behalf of a teacher.
     */
    private function resolveGuru(object $user, object $thn, object $smt): ?object
    {
        $isAdmin = ($user->role == self::ROLE_ADMIN || $user->role == self::ROLE_PANITIA);
        $id_guru_request = $this->request->getPost('id_guru')
            ?: $this->request->getGet('id_guru')
            ?: null;

        // Request JSON (mis. batch save) tidak terbaca getPost(), ambil dari body payload.
        if (!$id_guru_request) {
            $payload = $this->requestPayload();
            $id_guru_request = $payload['id_guru'] ?? null;
        }

        if ($isAdmin && $id_guru_request) {
            return $this->dashboard->getDataGuruById($id_guru_request, $thn->id_tp, $smt->id_smt);
        }

        return $this->dashboard->getDataGuruByUserId($user->id, $thn->id_tp, $smt->id_smt);
    }

    /**
     * Ambil payload request: form POST (default) atau body JSON.
     */
    private function requestPayload(): array
    {
        $post = $this->request->getPost();
        if (is_array($post) && $post !== []) {
            return $post;
        }

        $contentType = (string) $this->request->getHeaderLine('Content-Type');
        if (stripos($contentType, 'application/json') === false) {
            return [];
        }

        try {
            $json = $this->request->getJSON(true);
        } catch (\Throwable $e) {
            log_message('error', '[Guru\Nilai::requestPayload] JSON tidak valid: ' . $e->getMessage());
            return [];
        }

        return is_array($json) ? $json : [];
    }

    /**
     * Dashboard Penilaian Kurikulum Merdeka
     */
    public function index()
    {
        try {
            if (!$this->initUser())
                return redirect()->to('auth');

            if (!$this->guru) {
                if ($this->isAdmin() || $this->isPanitia()) {
                    // Admin might not have selected a teacher yet
                    $data = [
                        'judul' => 'Monitoring Penilaian',
                        'subjudul' => 'Pilih Guru untuk Memonitor',
                        'user' => $this->user,
                        'guru' => null,
                        'setting' => $this->dashboard->getSetting(),
                        'mapel_kelas' => [],
                        'tp_active' => $this->tp,
                        'smt_active' => $this->smt,
                        'total_mapel' => 0,
                        'avg_progress' => 0,
                        'avg_akhir' => 0,
                        'is_admin' => $this->isAdmin(),
                        'is_panitia' => $this->isPanitia(),
                        'gurus' => $this->master->getAllGuru()
                    ];
                    return view('guru/nilai/index', $data);
                }
                return redirect()->to('dashboard')->with('error', 'Data guru tidak ditemukan');
            }

            // Get assignments
            $mapel_guru = $this->master->getGuruMapelKelas($this->guru->id_guru, $this->tp->id_tp, $this->smt->id_smt);

            // ... (rest of the mapping logic using $this->guru, $this->tp, $this->smt)
            $mapel_kelas = [];
            if ($mapel_guru && !empty($mapel_guru->mapel_kelas)) {
                $raw_mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas);
                if (is_array($raw_mapel)) {
                    foreach ($raw_mapel as $m) {
                        if (isset($m->kelas_mapel) && is_array($m->kelas_mapel)) {
                            foreach ($m->kelas_mapel as $km) {
                                $new_m = clone $m;
                                unset($new_m->kelas_mapel);
                                $new_m->id_kelas = $km->kelas ?? ($km->id_kelas ?? null);
                                if (!$new_m->id_kelas)
                                    continue;

                                // Enrich names
                                if (!isset($new_m->nama_mapel) || !isset($new_m->kode_mapel)) {
                                    $mp = $this->master->getMapel($new_m->id_mapel);
                                    if (!isset($new_m->nama_mapel))
                                        $new_m->nama_mapel = $mp ? $mp->nama_mapel : 'Unknown';
                                    if (!isset($new_m->kode_mapel))
                                        $new_m->kode_mapel = $mp ? ($mp->kode ?? '') : '';
                                }
                                if (!isset($new_m->nama_kelas)) {
                                    $kl = $this->master->getKelasById($new_m->id_kelas);
                                    $new_m->nama_kelas = $kl ? $kl->nama_kelas : 'Unknown';
                                }

                                // Calculate Progress 
                                $progress = $this->nilaiModel->getProgressInput(
                                    $new_m->id_kelas,
                                    $new_m->id_mapel,
                                    $this->tp->id_tp,
                                    $this->smt->id_smt
                                );
                                $new_m->progress = $progress;

                                $mapel_kelas[] = $new_m;
                            }
                        } else if (isset($m->id_mapel) && isset($m->id_kelas)) {
                            if (!isset($m->nama_mapel) || !isset($m->kode_mapel)) {
                                $mp = $this->master->getMapel($m->id_mapel);
                                if (!isset($m->nama_mapel))
                                    $m->nama_mapel = $mp ? $mp->nama_mapel : 'Unknown Mapel';
                                if (!isset($m->kode_mapel))
                                    $m->kode_mapel = $mp ? ($mp->kode ?? '') : '';
                            }
                            if (!isset($m->nama_kelas)) {
                                $kl = $this->master->getKelasById($m->id_kelas);
                                $m->nama_kelas = $kl ? $kl->nama_kelas : 'Unknown Kelas';
                            }
                            // Calculate Progress
                            $progress = $this->nilaiModel->getProgressInput(
                                $m->id_kelas,
                                $m->id_mapel,
                                $this->tp->id_tp,
                                $this->smt->id_smt
                            );
                            $m->progress = $progress;

                            $mapel_kelas[] = $m;
                        }
                    }
                }
            }

            // Global statistik
            $totalMapel = count($mapel_kelas);
            $totalRataProgress = $totalMapel > 0
                ? round(array_sum(array_map(fn($m) => ($m->progress['persen'] ?? 0), $mapel_kelas)) / $totalMapel)
                : 0;
            $totalRataAkhir = $totalMapel > 0
                ? round(array_sum(array_map(fn($m) => ($m->progress['persen_akhir'] ?? 0), $mapel_kelas)) / $totalMapel)
                : 0;

            $data = [
                'judul' => 'Pengelolaan Nilai',
                'subjudul' => 'Beranda Penilaian',
                'user' => $this->user,
                'guru' => $this->guru,
                'setting' => $this->dashboard->getSetting(),
                'mapel_kelas' => $mapel_kelas,
                'tp_active' => $this->tp,
                'smt_active' => $this->smt,
                'total_mapel' => $totalMapel,
                'avg_progress' => $totalRataProgress,
                'avg_akhir' => $totalRataAkhir,
                'is_admin' => $this->isAdmin(),
                'is_panitia' => $this->isPanitia(),
                'gurus' => $this->master->getAllGuru(),
                'id_guru_selected' => $this->guru->id_guru
            ];

            return view('guru/nilai/index', $data);
        } catch (\Throwable $th) {
            log_message('error', '[Guru\Nilai::index] Error: ' . $th->getMessage() . ' Trace: ' . $th->getTraceAsString());
            return redirect()->to('dashboard')->with('error', 'Terjadi kesalahan sistem: ' . $th->getMessage());
        }
    }

    /**
     * Halaman Input Nilai Sumatif Lingkup Materi (Per TP)
     */
    public function inputProses()
    {
        if (!$this->initUser())
            return redirect()->to('auth');

        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');

        // Info mapel & kelas
        $nama_mapel = '';
        $nama_kelas = '';
        $tingkat = null;
        if ($id_mapel) {
            $mp = $this->master->getMapel($id_mapel);
            $nama_mapel = $mp ? $mp->nama_mapel : '';
        }
        if ($id_kelas) {
            $kl = $this->master->getKelasById($id_kelas);
            $nama_kelas = $kl ? $kl->nama_kelas : '';
            $tingkat = $kl ? ($kl->tingkat ?? null) : null;
            // Fallback: ekstrak tingkat dari nama kelas jika field tingkat kosong
            if (($tingkat === null || $tingkat === '' || $tingkat === '0') && $nama_kelas) {
                $tingkat = $this->extractTingkatFromNamaKelas($nama_kelas);
            }
        }

        // List TP - hanya yang aktif, sesuai semester aktif, dan sesuai tingkat kelas
        $list_tp = [];
        if ($id_mapel) {
            $list_tp = $this->tpModel->getTujuanByGuru($this->guru->id_guru, $this->tp->id_tp, $this->smt->id_smt, $id_mapel, $tingkat, null, true);
        }

        // Siswa
        $siswa = [];
        if ($id_kelas) {
            $siswa = $this->master->getSiswaByKelas($id_kelas, $this->tp->id_tp, $this->smt->id_smt, true);
        }

        // Existing Grades
        $nilai_existing = [];
        if ($id_kelas && $id_mapel) {
            $builder = \Config\Database::connect()->table('nilai_proses np');
            $builder->select('np.id_siswa, np.id_tp, np.nilai, np.id_nilai_proses'); // Prevent ks.id_tp overriding np.id_tp
            $builder->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa');
            $builder->where('ks.id_kelas', $id_kelas);
            $builder->where('np.id_mapel', $id_mapel);

            // Limit to active year/semester if possible, to prevent reading previous year data
            if (isset($this->tp) && isset($this->smt)) {
                $builder->where('np.id_tahun', $this->tp->id_tp);
                $builder->where('np.id_smt', $this->smt->id_smt);
            }

            $raw_nilai = $builder->get()->getResult();

            foreach ($raw_nilai as $n) {
                $nilai_existing[$n->id_siswa][$n->id_tp] = $n->nilai;
            }
        }

        $data = [
            'judul' => 'Nilai Sumatif Lingkup Materi',
            'subjudul' => 'Input Nilai Per Tujuan Pembelajaran',
            'user' => $this->user,
            'guru' => $this->guru,
            'setting' => $this->dashboard->getSetting(),
            'id_mapel' => $id_mapel ?: 0,
            'id_kelas' => $id_kelas ?: 0,
            'nama_mapel' => $nama_mapel,
            'nama_kelas' => $nama_kelas,
            'list_tp' => $list_tp,
            'siswa' => $siswa,
            'nilai' => $nilai_existing,
            'tp_active' => $this->tp,
            'smt_active' => $this->smt,
            'id_guru_selected' => $this->guru->id_guru,
            'is_admin' => $this->isAdmin(),
            'is_panitia' => $this->isPanitia(),
            'mapel_kelas' => $this->getMapelGuru($this->guru, $this->tp, $this->smt)
        ];

        return view('guru/nilai/input_proses', $data);
    }

    /**
     * Download Excel Nilai Harian (Proses)
     */
    public function downloadProses()
    {
        if (!$this->initUser())
            return redirect()->to('auth');

        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');

        if (!$id_mapel || !$id_kelas)
            return "Parameter tidak lengkap";

        $mp = $this->master->getMapel($id_mapel);
        $kl = $this->master->getKelasById($id_kelas);

        if (!$mp || !$kl)
            return "Data Mapel atau Kelas tidak ditemukan";

        $siswa = $this->master->getSiswaByKelas($id_kelas, $this->tp->id_tp, $this->smt->id_smt, true);
        if (empty($siswa))
            return "Tidak ada data siswa di kelas ini";

        $tingkat = $kl->tingkat ?? $this->extractTingkatFromNamaKelas($kl->nama_kelas);
        $list_tp = $this->tpModel->getTujuanByGuru($this->guru->id_guru, $this->tp->id_tp, $this->smt->id_smt, $id_mapel, $tingkat, null, true);

        // Grade Data - ambil semua nilai yang ada
        $nilai_existing = [];
        $builder = \Config\Database::connect()->table('nilai_proses np');
        $builder->select('np.id_siswa, np.id_tp, np.nilai');
        $builder->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa');
        $builder->where('ks.id_kelas', $id_kelas);
        $builder->where('np.id_mapel', $id_mapel);
        $builder->where('np.id_tahun', $this->tp->id_tp);
        $builder->where('np.id_smt', $this->smt->id_smt);
        $raw_nilai = $builder->get()->getResult();

        foreach ($raw_nilai as $n) {
            $nilai_existing[$n->id_siswa][$n->id_tp] = $n->nilai;
        }

        // Gunakan SEMUA TP (tidak difilter) agar template lengkap
        $list_tp_display = $list_tp;

        ob_clean();
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Nilai_Harian_{$mp->nama_mapel}_{$kl->nama_kelas}.xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $total_cols = 4 + count($list_tp_display);

        echo "
        <style>
            .title { font-family: 'Segoe UI', sans-serif; font-size: 16pt; font-weight: bold; color: #00695c; text-align: center; }
            .subtitle { font-family: 'Segoe UI', sans-serif; font-size: 12pt; color: #37474f; text-align: center; }
            .info-table td { font-family: 'Segoe UI', sans-serif; font-size: 10pt; padding: 3px 5px; }
            .data-table th { font-family: 'Segoe UI', sans-serif; font-size: 10pt; background-color: #00695c; color: #ffffff; border: 1px solid #004d40; vertical-align: middle; text-align: center; font-weight: bold; }
            .data-table td { font-family: 'Segoe UI', sans-serif; font-size: 10pt; border: 1px solid #b0bec5; vertical-align: middle; }
            .str { mso-number-format:\"\\@\"; }
            .num { mso-number-format:\"0\"; }
            .wrap { white-space: normal; word-wrap: break-word; }
            .text-center { text-align: center; }
            .text-bold { font-weight: bold; }
            .header-row { background-color: #e0f2f1; font-weight: bold; }
        </style>
        ";

        // Title Section
        echo "<table border='0' cellspacing='0' cellpadding='5' style='width:100%;'>
                <tr><td colspan='$total_cols' class='title'>REKAPITULASI NILAI HARIAN</td></tr>
                <tr><td colspan='$total_cols' class='subtitle'>SUMATIF LINGKUP MATERI</td></tr>
                <tr><td colspan='$total_cols'>&nbsp;</td></tr>
              </table>";

        // Info Section
        $guru_nama = is_object($this->guru) ? ($this->guru->nama_guru ?? '-') : (is_array($this->guru) ? ($this->guru['nama_guru'] ?? '-') : '-');
        echo "<table class='info-table' border='0' style='margin-bottom:15px;'>
                <tr><td style='font-weight:bold; width:120px;'>Mata Pelajaran</td><td>: " . ($mp->nama_mapel ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Kelas</td><td>: " . ($kl->nama_kelas ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Guru</td><td>: " . $guru_nama . "</td></tr>
                <tr><td style='font-weight:bold;'>Tahun Pelajaran</td><td>: " . ($this->tp->tahun ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Semester</td><td>: " . ($this->smt->nama_smt ?? '-') . "</td></tr>
              </table>";

        // Main Data Table
        echo "<table class='data-table' border='1' cellspacing='0' cellpadding='5' style='border-collapse:collapse;'>
                <thead>
                    <tr>
                        <th style='width:40px;'>No</th>
                        <th style='width:120px;'>NISN</th>
                        <th style='width:280px;'>Nama Siswa</th>";
        foreach ($list_tp_display as $tp) {
            $kode = $tp->kode ?? 'TP';
            echo "      <th style='width:60px;'>" . $kode . "</th>";
        }
        echo "          <th style='width:70px;'>Rata-rata</th>
                        <th style='width:70px;'>Predikat</th>
                    </tr>
                </thead>
                <tbody>";

        $no = 1;
        foreach ($siswa as $s) {
            $rowBg = ($no % 2 == 0) ? "bgcolor='#f5f5f5'" : "bgcolor='#ffffff'";
            $nisn_display = !empty($s->nisn) ? (string)$s->nisn : '-';
            echo "<tr $rowBg>
                    <td class='text-center'>" . $no++ . "</td>
                    <td class='str text-center' style='mso-number-format:\"\\@\";'>" . $nisn_display . "</td>
                    <td align='left' style='padding-left:8px;'>" . $s->nama . "</td>";

            $total_row = 0;
            $count_row = 0;
            foreach ($list_tp_display as $tp) {
                $raw_val = $nilai_existing[$s->id_siswa][$tp->id_tujuan] ?? '';
                $val = ($raw_val !== '' && $raw_val !== null) ? round((float) $raw_val) : '';
                $display_val = ($val !== '' && $val > 0) ? $val : '';
                echo "<td class='text-center num'>" . $display_val . "</td>";
                if ($val !== '' && $val > 0) {
                    $total_row += $val;
                    $count_row++;
                }
            }

            $avg = 0;
            if ($count_row > 0) {
                $avg_raw = $total_row / $count_row;
                $avg = floor($avg_raw) == $avg_raw ? number_format($avg_raw, 0) : number_format($avg_raw, 1);
            }
            $pred = \App\Models\NilaiModel::getPredikat((float) $avg);
            $predikat_huruf = $avg > 0 ? $pred['huruf'] : '-';

            echo "<td class='text-center text-bold' style='background-color:#e0f2f1;text-align:center;font-weight:bold;'>" . ($avg > 0 ? $avg : '') . "</td>
                  <td class='text-center' style='text-align:center;'>" . $predikat_huruf . "</td>
                  </tr>";
        }
        echo "</tbody></table><br><br>";

        // TP Legend Table
        echo "<table class='data-table' border='1' cellspacing='0' cellpadding='5' style='border-collapse:collapse;'>
                <thead>
                    <tr bgcolor='#1565c0'>
                        <th style='width:40px; color:#ffffff;'>No</th>
                        <th style='width:120px; color:#ffffff;'>Kode TP</th>
                        <th colspan='" . ($total_cols - 2) . "' style='color:#ffffff;'>Uraian Tujuan Pembelajaran</th>
                    </tr>
                </thead>
                <tbody>";
        $no_tp = 1;
        foreach ($list_tp_display as $tp) {
            $rowBg = ($no_tp % 2 == 0) ? "bgcolor='#e3f2fd'" : "bgcolor='#ffffff'";
            echo "<tr $rowBg>
                    <td class='text-center'>" . $no_tp++ . "</td>
                    <td class='text-center text-bold'>" . ($tp->kode ?? '-') . "</td>
                    <td colspan='" . ($total_cols - 2) . "' align='left' style='padding:5px 8px;'>" . ($tp->tujuan ?? '-') . "</td>
                  </tr>";
        }
        echo "</tbody></table>";
        exit;
    }

    /**
     * Halaman Input Nilai Sumatif Tengah Semester (STS) - 1 nilai per siswa
     */
    public function inputSts()
    {
        try {
            if (!$this->initUser())
                return redirect()->to('auth');

            if (!$this->guru) {
                return redirect()->to('dashboard')->with('error', 'Data guru tidak ditemukan untuk akun Anda.');
            }

            $id_mapel = $this->request->getGet('mapel');
            $id_kelas = $this->request->getGet('kelas');

            // Info mapel & kelas
            $nama_mapel = '';
            $nama_kelas = '';
            if ($id_mapel) {
                $mp = $this->master->getMapel($id_mapel);
                $nama_mapel = $mp ? $mp->nama_mapel : '';
            }
            if ($id_kelas) {
                $kl = $this->master->getKelasById($id_kelas);
                $nama_kelas = $kl ? $kl->nama_kelas : '';
            }

            // Siswa
            $siswa = [];
            if ($id_kelas) {
                $siswa = $this->master->getSiswaByKelas($id_kelas, $this->tp->id_tp, $this->smt->id_smt, true);
            }

            // Existing STS Grades (1 nilai per siswa)
            $nilai_existing = [];
            if ($id_kelas && $id_mapel) {
                try {
                    $raw_nilai = $this->nilaiModel->getAllNilaiStsKelas($id_kelas, $id_mapel, $this->tp->id_tp, $this->smt->id_smt);
                    foreach ($raw_nilai as $n) {
                        $nilai_existing[$n->id_siswa] = $n->nilai;
                    }
                } catch (\Throwable $e) {
                    log_message('error', '[inputSts] Error loading STS data: ' . $e->getMessage());
                    $nilai_existing = [];
                }
            }

            $data = [
                'judul' => 'Nilai Sumatif Tengah Semester',
                'subjudul' => 'Input Nilai STS (1 Nilai per Siswa)',
                'user' => $this->user,
                'guru' => $this->guru,
                'setting' => $this->dashboard->getSetting(),
                'id_mapel' => $id_mapel ?: 0,
                'id_kelas' => $id_kelas ?: 0,
                'nama_mapel' => $nama_mapel,
                'nama_kelas' => $nama_kelas,
                'siswa' => $siswa,
                'nilai' => $nilai_existing,
                'tp_active' => $this->tp,
                'smt_active' => $this->smt,
                'id_guru_selected' => $this->guru->id_guru,
                'is_admin' => $this->isAdmin(),
                'is_panitia' => $this->isPanitia(),
                'mapel_kelas' => $this->getMapelGuru($this->guru, $this->tp, $this->smt)
            ];

            return view('guru/nilai/input_sts', $data);
        } catch (\Throwable $e) {
            log_message('error', '[inputSts] Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->to('dashboard')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Save single nilai STS via AJAX (1 nilai per siswa)
     */
    public function saveSts()
    {
        if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
        }
        $thn = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->resolveGuru($user, $thn, $smt);
        if (!$guru) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        $data = [
            'id_siswa' => $this->request->getPost('id_siswa'),
            'id_mapel' => $this->request->getPost('id_mapel'),
            'id_guru' => $guru->id_guru,
            'id_tahun' => $thn->id_tp,
            'id_smt' => $smt->id_smt,
            'nilai' => $this->request->getPost('nilai'),
        ];

        // Validasi
        if (!$data['id_siswa'] || !$data['id_mapel'] || $data['nilai'] === '' || $data['nilai'] === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        // Validasi nilai 0-100
        $nilai = floatval($data['nilai']);
        if ($nilai < 0 || $nilai > 100) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Nilai harus antara 0-100'
            ]);
        }

        $saved = $this->nilaiModel->saveNilaiSts($data);

        return $this->response->setJSON([
            'status' => (bool) $saved,
            'message' => $saved ? 'Nilai STS tersimpan' : 'Gagal menyimpan nilai',
            'data' => ['nilai' => $nilai]
        ]);
    }

    /**
     * Delete nilai STS (when user clears the input)
     */
    public function deleteSts()
    {
        try {
            if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
                return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
            }

            $user = $this->getUser();
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
            }
            $thn = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $id_siswa = $this->request->getPost('id_siswa');
            $id_mapel = $this->request->getPost('id_mapel');

            if (!$id_siswa || !$id_mapel) {
                return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'Data tidak lengkap']);
            }

            $result = $this->adapter->deleteSts((int)$id_siswa, (int)$id_mapel, (int)$thn->id_tp, (int)$smt->id_smt);

            return $this->response->setJSON([
                'status' => $result['status'] === 'success',
                'message' => $result['message']
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[deleteSts] EXCEPTION: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download Excel Nilai STS
     */
    public function downloadSts()
    {
        if (!$this->initUser())
            return redirect()->to('auth');

        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');

        if (!$id_mapel || !$id_kelas)
            return "Parameter tidak lengkap";

        $mp = $this->master->getMapel($id_mapel);
        $kl = $this->master->getKelasById($id_kelas);

        if (!$mp || !$kl)
            return "Data Mapel atau Kelas tidak ditemukan";

        $siswa = $this->master->getSiswaByKelas($id_kelas, $this->tp->id_tp, $this->smt->id_smt, true);
        if (empty($siswa))
            return "Tidak ada data siswa di kelas ini";

        // STS Grade Data - 1 per student
        $nilai_existing = [];
        $raw_nilai = $this->nilaiModel->getAllNilaiStsKelas($id_kelas, $id_mapel, $this->tp->id_tp, $this->smt->id_smt);
        foreach ($raw_nilai as $n) {
            $nilai_existing[$n->id_siswa] = $n->nilai;
        }

        ob_clean();
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Nilai_STS_{$mp->nama_mapel}_{$kl->nama_kelas}.xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "
        <style>
            .title { font-family: 'Segoe UI', sans-serif; font-size: 16pt; font-weight: bold; color: #00695c; text-align: center; }
            .info-table td { font-family: 'Segoe UI', sans-serif; font-size: 10pt; }
            .data-table th { font-family: 'Segoe UI', sans-serif; font-size: 9pt; background-color: #00695c; color: #ffffff; border: 1px solid #004d40; vertical-align: middle; }
            .data-table td { font-family: 'Segoe UI', sans-serif; font-size: 9pt; border: 1px solid #cccccc; }
            .str { mso-number-format:\"\\@\"; }
        </style>
        ";

        echo "<div class='title'>REKAPITULASI NILAI SUMATIF TENGAH SEMESTER (STS)</div><br>";
        echo "<table class='info-table' border='0'>
                <tr><td style='font-weight:bold; width:150px;'>Mata Pelajaran</td><td>: " . ($mp->nama_mapel ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Kelas</td><td>: " . ($kl->nama_kelas ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Tahun Pelajaran</td><td>: " . ($this->tp->tahun ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Semester</td><td>: " . ($this->smt->nama_smt ?? '-') . "</td></tr>
              </table><br>";

        echo "<table class='data-table' border='1' cellspacing='0' cellpadding='5'>
                <thead>
                    <tr bgcolor='#00695c' style='color: #ffffff;'>
                        <th style='width: 40px; color: #ffffff;'>No</th>
                        <th style='width: 250px; color: #ffffff;'>Nama Siswa</th>
                        <th style='width: 100px; color: #ffffff;'>NISN</th>
                        <th style='width: 150px; color: #ffffff;'>Nilai STS</th>
                        <th style='width: 100px; color: #ffffff;'>Predikat</th>
                    </tr>
                </thead>
                <tbody>";

        $no = 1;
        foreach ($siswa as $s) {
            $rowBg = ($no % 2 == 0) ? '#f9f9f9' : '#ffffff';
            $val = $nilai_existing[$s->id_siswa] ?? 0;
            $pred = \App\Models\NilaiModel::getPredikat((float) $val);

            echo "<tr bgcolor='$rowBg'>
                    <td align='center'>" . $no++ . "</td>
                    <td align='left'>" . $s->nama . "</td>
                    <td class='str' align='center'>" . $s->nisn . "</td>
                    <td align='center' style='font-weight:bold;'>" . ($val > 0 ? $val : '-') . "</td>
                    <td align='center'>" . ($val > 0 ? $pred['huruf'] : '-') . "</td>
                  </tr>";
        }
        echo "</tbody></table>";
        exit;
    }


    /**
     * Batch save nilai STS untuk seluruh kelas (1 nilai per siswa)
     */
    public function batchSaveSts()
    {
        $nilaiData = $this->request->getPost('nilai_data');
        $mapel_id = $this->request->getPost('id_mapel');

        if (!$nilaiData || !is_array($nilaiData)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak valid']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
        }
        $thn = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->resolveGuru($user, $thn, $smt);
        if (!$guru) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        $saved = 0;
        $errors = 0;
        foreach ($nilaiData as $item) {
            if (!isset($item['id_siswa']) || !isset($item['nilai']))
                continue;
            if ($item['nilai'] === '' || $item['nilai'] === null)
                continue;

            $data = [
                'id_siswa' => $item['id_siswa'],
                'id_mapel' => $mapel_id,
                'id_guru' => $guru->id_guru,
                'id_tahun' => $thn->id_tp,
                'id_smt' => $smt->id_smt,
                'nilai' => $item['nilai']
            ];

            if ($this->nilaiModel->saveNilaiSts($data)) {
                $saved++;
            } else {
                $errors++;
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'result' => ['saved' => $saved, 'errors' => $errors],
            'message' => "$saved nilai berhasil disimpan" . ($errors > 0 ? ", $errors gagal" : "")
        ]);
    }

    /**
     * Save single nilai proses via AJAX
     */
    public function saveProses()
    {
        try {
            if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
                return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
            }

            $user = $this->getUser();
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
            }
            $thn = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $guru = $this->resolveGuru($user, $thn, $smt);
            if (!$guru) {
                return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
            }

            $raw = $this->requestPayload();

            // Pass to adapter to normalize and cross-save map
            $validated = $this->adapter->validateAndNormalizeProses($raw, (int) $guru->id_guru, (int) $thn->id_tp, (int) $smt->id_smt);
            if (!$validated['valid']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => $validated['message']
                ]);
            }

            $result = $this->adapter->saveProses($validated['payload']);

            return $this->response->setJSON([
                'status' => $result['status'] === 'success',
                'message' => $result['message'] ?? ($result['status'] === 'success' ? 'Nilai proses tersimpan' : 'Gagal menyimpan nilai'),
                'compatibility' => $validated['mapping']
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[saveProses] EXCEPTION: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Delete nilai proses (when user clears the input)
     */
    public function deleteProses()
    {
        try {
            if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
                return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
            }

            $id_siswa = $this->request->getPost('id_siswa');
            $id_mapel = $this->request->getPost('id_mapel');
            $id_tp = $this->request->getPost('id_tp');
            $tipe = $this->request->getPost('tipe') ?: 'sumatif_lm';

            if (!$id_siswa || !$id_mapel || !$id_tp) {
                return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'Data tidak lengkap']);
            }

            $result = $this->adapter->deleteProses((int)$id_siswa, (int)$id_mapel, (int)$id_tp, $tipe);

            return $this->response->setJSON([
                'status' => $result['status'] === 'success',
                'message' => $result['message']
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[deleteProses] EXCEPTION: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Batch save nilai proses untuk seluruh kelas
     */
    public function batchSaveProses()
    {
        $payload = $this->requestPayload();
        $nilaiData = $payload['nilai_data'] ?? $payload['data'] ?? null;
        $mapel_id = $payload['id_mapel'] ?? null;

        if (!$nilaiData || !is_array($nilaiData)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak valid']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
        }
        $thn = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->resolveGuru($user, $thn, $smt);
        if (!$guru) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        $batchData = [];
        foreach ($nilaiData as $item) {
            // Frontend mengirim id_tujuan; samakan dengan id_tp.
            $idTp = $item['id_tp'] ?? ($item['id_tujuan'] ?? null);
            if (!isset($item['id_siswa']) || $idTp === null || $idTp === '' || !isset($item['nilai']))
                continue;
            if ($item['nilai'] === '' || $item['nilai'] === null)
                continue;

            $batchData[] = [
                'id_siswa' => $item['id_siswa'],
                'id_mapel' => $item['id_mapel'] ?? $mapel_id,
                'id_tp' => $idTp,
                'tipe' => 'sumatif_lm',
                'komponen_penilaian' => $item['komponen_penilaian'] ?? 'pengetahuan',
                'kompetensi_dasar' => $item['kompetensi_dasar'] ?? '',
                'capaian_pembelajaran' => $item['capaian_pembelajaran'] ?? '',
                'indikator_pencapaian' => $item['indikator_pencapaian'] ?? '',
                'nilai' => $item['nilai']
            ];
        }

        if ($batchData === []) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada nilai valid untuk disimpan']);
        }

        $result = $this->adapter->saveBatchProses($batchData, (int) $guru->id_guru, (int) $thn->id_tp, (int) $smt->id_smt);

        $saved = (int) ($result['saved'] ?? 0);
        $errors = is_array($result['errors'] ?? null) ? $result['errors'] : [];
        $total = (int) ($result['total'] ?? count($batchData));
        $message = $errors === []
            ? "Berhasil menyimpan {$saved} nilai"
            : ($saved > 0
                ? "Tersimpan {$saved} dari {$total} nilai, " . count($errors) . ' gagal'
                : ($errors[0]['message'] ?? 'Gagal menyimpan nilai'));

        return $this->response->setJSON([
            'status' => $saved > 0 && $errors === [],
            'message' => $message,
            'saved' => $saved,
            'errors' => $errors,
            'result' => $result,
            'compatibility' => [
                'legacy_written' => $saved,
                'kurmer_mirrored' => $saved
            ]
        ]);
    }

    /**
     * Halaman Input Nilai Akhir Semester (SAS) & Generate Rapor
     */
    public function inputAkhir()
    {
        if (!$this->initUser())
            return redirect()->to('auth');

        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');

        // Info mapel & kelas
        $nama_mapel = '';
        $nama_kelas = '';
        if ($id_mapel) {
            $mp = $this->master->getMapel($id_mapel);
            $nama_mapel = $mp ? $mp->nama_mapel : '';
        }
        if ($id_kelas) {
            $kl = $this->master->getKelasById($id_kelas);
            $nama_kelas = $kl ? $kl->nama_kelas : '';
        }

        $siswa = [];
        if ($id_kelas) {
            $siswa = $this->master->getSiswaByKelas($id_kelas, $this->tp->id_tp, $this->smt->id_smt, true);
        }

        // Get Process Grades with TP details (Sumatif LM)
        $builder = \Config\Database::connect()->table('nilai_proses np');
        $builder->select('np.*, tp.kode, tp.tujuan');
        $builder->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa');
        $builder->join('e_tujuan_pembelajaran tp', 'tp.id_tujuan = np.id_tp');
        $builder->where('ks.id_kelas', $id_kelas);
        $builder->where('np.id_mapel', $id_mapel);
        $builder->where('np.tipe', 'sumatif_lm');
        $result = $builder->get();
        $raw_proses = $result ? $result->getResult() : [];

        // Calculate Average & Identify Best/Worst TP
        $rekap_proses = [];
        foreach ($raw_proses as $r) {
            if (!isset($rekap_proses[$r->id_siswa])) {
                $rekap_proses[$r->id_siswa] = [
                    'total' => 0,
                    'count' => 0,
                    'highest_val' => -1,
                    'highest_desc' => '',
                    'lowest_val' => 101,
                    'lowest_desc' => '',
                    'details' => []
                ];
            }
            $rekap_proses[$r->id_siswa]['total'] += $r->nilai;
            $rekap_proses[$r->id_siswa]['count']++;
            $rekap_proses[$r->id_siswa]['details'][] = [
                'kode' => $r->kode,
                'tujuan' => $r->tujuan,
                'nilai' => $r->nilai
            ];

            if ($r->nilai > $rekap_proses[$r->id_siswa]['highest_val']) {
                $rekap_proses[$r->id_siswa]['highest_val'] = $r->nilai;
                $rekap_proses[$r->id_siswa]['highest_desc'] = $r->tujuan;
            }
            if ($r->nilai < $rekap_proses[$r->id_siswa]['lowest_val']) {
                $rekap_proses[$r->id_siswa]['lowest_val'] = $r->nilai;
                $rekap_proses[$r->id_siswa]['lowest_desc'] = $r->tujuan;
            }
        }

        // Get STS (Sumatif Tengah Semester) grades - 1 nilai per siswa dari tabel nilai_sts
        $raw_sts = $this->nilaiModel->getAllNilaiStsKelas($id_kelas, $id_mapel, $this->tp->id_tp, $this->smt->id_smt);

        $rekap_sts = [];
        foreach ($raw_sts as $n) {
            $rekap_sts[$n->id_siswa] = [
                'nilai' => $n->nilai,
                'updated_at' => $n->updated_at
            ];
        }

        // Get Existing Final Grades (filtered by current class)
        $nilai_akhir_existing = [];
        $builder2 = \Config\Database::connect()->table('nilai_akhir_kurmer nak');
        $builder2->join('kelas_siswa ks', 'ks.id_siswa = nak.id_siswa');
        $builder2->where('ks.id_kelas', $id_kelas);
        $builder2->where('nak.id_mapel', $id_mapel);
        $builder2->where('nak.id_tahun', $this->tp->id_tp);
        $builder2->where('nak.id_smt', $this->smt->id_smt);
        $existing = $builder2->get()->getResult();
        foreach ($existing as $e) {
            $nilai_akhir_existing[$e->id_siswa] = $e;
        }

        // Get Practical Exam Grades (Nilai Praktik) - Rata-rata jika multiple
        $rekap_praktik = [];
        if ($id_mapel && $id_kelas) {
            // Clear cache untuk memastikan data fresh
            \Config\Database::connect()->query("SET SESSION query_cache_type = OFF");

            $builder_praktik = \Config\Database::connect()->table('cbt_praktik_hasil h');
            $builder_praktik->select('h.siswa_id, h.nilai_akhir, h.rincian_nilai_rubrik, p.judul, p.id as praktik_id, ks.id_siswa as id_siswa_kelas');
            $builder_praktik->join('cbt_praktik p', 'p.id = h.praktik_id');
            $builder_praktik->join('kelas_siswa ks', 'ks.id_siswa = h.siswa_id AND ks.id_kelas = ' . (int) $id_kelas);
            $builder_praktik->where('p.mapel_id', $id_mapel);
            $builder_praktik->where('p.tp_id', $this->tp->id_tp);
            $builder_praktik->where('p.smt_id', $this->smt->id_smt);
            $builder_praktik->where('h.status_penilaian', 'sudah_dinilai');
            $builder_praktik->orderBy('p.id', 'DESC');
            $praktik_results = $builder_praktik->get()->getResult();

            // Kumpulkan semua nilai praktik per siswa untuk dihitung rata-rata
            $temp_praktik = [];
            foreach ($praktik_results as $pr) {
                // Fix: Hanya hitung rata-rata jika nilai_akhir merupakan total (sum) dari kriteria (>100 atau sama dengan sum rincian)
                $nilai_per_ujian = (float) $pr->nilai_akhir;
                $rincian = json_decode($pr->rincian_nilai_rubrik ?? '[]', true);
                if (!empty($rincian) && is_array($rincian) && count($rincian) > 0) {
                    $sum_rincian = array_sum($rincian);
                    // Check if it's a sum (either > 100 or matches the sum of criteria)
                    if ($nilai_per_ujian > 100 || abs($nilai_per_ujian - $sum_rincian) < 0.1) {
                        $nilai_per_ujian = $nilai_per_ujian / count($rincian);
                    }
                }

                $key = $pr->id_siswa_kelas ?: $pr->siswa_id;
                if (!isset($temp_praktik[$key])) {
                    $temp_praktik[$key] = [
                        'total' => 0,
                        'count' => 0,
                        'judul' => $pr->judul
                    ];
                }
                $temp_praktik[$key]['total'] += $nilai_per_ujian;
                $temp_praktik[$key]['count']++;
            }

            // Hitung rata-rata
            foreach ($temp_praktik as $key => $data) {
                $rata_rata = $data['count'] > 0 ? round($data['total'] / $data['count'], 0) : 0;
                log_message('debug', "Praktik Siswa $key: total={$data['total']}, count={$data['count']}, rata-rata=$rata_rata");
                $rekap_praktik[$key] = [
                    'nilai' => $rata_rata,
                    'judul' => $data['judul']
                ];
            }
        }

        // Statistics
        $statistik = $this->nilaiModel->getStatistikKelas($id_kelas, $id_mapel, $this->tp->id_tp, $this->smt->id_smt);

        $data = [
            'judul' => 'Nilai Akhir Semester',
            'subjudul' => 'Rekap & Kalkulasi Rapor',
            'user' => $this->user,
            'guru' => $this->guru,
            'setting' => $this->dashboard->getSetting(),
            'id_mapel' => $id_mapel ?: 0,
            'id_kelas' => $id_kelas ?: 0,
            'nama_mapel' => $nama_mapel,
            'nama_kelas' => $nama_kelas,
            'siswa' => $siswa,
            'rekap_proses' => $rekap_proses,
            'rekap_sts' => $rekap_sts,
            'rekap_praktik' => $rekap_praktik,
            'nilai_akhir' => $nilai_akhir_existing,
            'statistik' => $statistik,
            'tp_active' => $this->tp,
            'smt_active' => $this->smt,
            'id_guru_selected' => $this->guru->id_guru,
            'is_admin' => $this->isAdmin(),
            'is_panitia' => $this->isPanitia(),
            'mapel_kelas' => $this->getMapelGuru($this->guru, $this->tp, $this->smt)
        ];

        return view('guru/nilai/input_akhir', $data);
    }

    /**
     * Save single nilai akhir
     */
    public function saveAkhir()
    {
        if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
        }
        $thn = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->resolveGuru($user, $thn, $smt);
        if (!$guru) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        $raw = [
            'id_siswa' => $this->request->getPost('id_siswa'),
            'id_mapel' => $this->request->getPost('id_mapel'),
            'nilai_sas' => $this->request->getPost('nilai_sas'),
            'bobot_proses' => $this->request->getPost('bobot_proses') ?: 50,
            'bobot_sas' => $this->request->getPost('bobot_sas') ?: 50,
            'bobot_sts' => $this->request->getPost('bobot_sts') ?: 0,
            'bobot_praktik' => $this->request->getPost('bobot_praktik') ?: 0,
            'deskripsi_tinggi' => $this->request->getPost('deskripsi_tinggi'),
            'deskripsi_rendah' => $this->request->getPost('deskripsi_rendah'),
        ];
        $validated = $this->adapter->validateAndNormalizeAkhir($raw, (int) $guru->id_guru, (int) $thn->id_tp, (int) $smt->id_smt);
        if (!$validated['valid']) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => $validated['message']
            ]);
        }
        $result = $this->adapter->saveAkhir($validated['payload']);
        return $this->response->setJSON([
            'status' => (bool) $result,
            'mapping' => $validated['mapping'] ?? null,
            'data' => [
                'legacy_nilai_rapor' => $validated['payload']['nilai_rapor'],
                'nilai_akhir_skala_4' => $validated['payload']['nilai_rapor_skala_4'],
                'predikat_skala_4' => $validated['payload']['predikat_skala_4'],
                'komponen' => [
                    'pengetahuan' => $validated['payload']['nilai_pengetahuan'],
                    'keterampilan' => $validated['payload']['nilai_keterampilan'],
                    'sikap' => $validated['payload']['nilai_sikap'],
                ]
            ]
        ]);
    }

    /**
     * Batch save all nilai akhir sekaligus
     */
    public function batchSaveAkhir()
    {
        $nilaiData = $this->request->getPost('nilai_data');
        $id_mapel = $this->request->getPost('id_mapel');
        $bobot_proses = $this->request->getPost('bobot_proses') ?: 50;
        $bobot_sas = $this->request->getPost('bobot_sas') ?: 50;
        $bobot_sts = $this->request->getPost('bobot_sts') ?: 0;
        $bobot_praktik = $this->request->getPost('bobot_praktik') ?: 0;

        if (!$nilaiData || !is_array($nilaiData)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak valid']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
        }
        $thn = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->resolveGuru($user, $thn, $smt);
        if (!$guru) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        $batchData = [];
        foreach ($nilaiData as $item) {
            if (!isset($item['id_siswa']))
                continue;

            $batchData[] = [
                'id_siswa' => $item['id_siswa'],
                'id_mapel' => $id_mapel,
                'nilai_sas' => $item['nilai_sas'] ?? 0,
                'bobot_proses' => $bobot_proses,
                'bobot_sas' => $bobot_sas,
                'bobot_sts' => $bobot_sts,
                'bobot_praktik' => $bobot_praktik,
                'deskripsi_tinggi' => $item['deskripsi_tinggi'] ?? '',
                'deskripsi_rendah' => $item['deskripsi_rendah'] ?? ''
            ];
        }

        $result = $this->adapter->saveBatchAkhir($batchData, (int) $guru->id_guru, (int) $thn->id_tp, (int) $smt->id_smt);
        return $this->response->setJSON([
            'status' => true,
            'result' => $result,
            'compatibility' => [
                'legacy_written' => $result['saved'],
                'kurmer_mirrored' => $result['saved']
            ]
        ]);
    }

    public function apiSchema()
    {
        if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        return $this->response->setJSON([
            'status' => true,
            'schema' => [
                'proses_fields' => [
                    'id_siswa',
                    'id_mapel',
                    'id_tp',
                    'nilai',
                    'komponen_penilaian',
                    'kompetensi_dasar',
                    'capaian_pembelajaran',
                    'indikator_pencapaian'
                ],
                'akhir_fields' => [
                    'id_siswa',
                    'id_mapel',
                    'nilai_sas',
                    'bobot_proses',
                    'bobot_sas',
                    'deskripsi_tinggi',
                    'deskripsi_rendah'
                ],
                'konversi' => [
                    'nilai_100_to_4' => 'nilai_skala_4 = (nilai_100/100)*4',
                    'predikat_skala_4' => 'A>=3.51,B>=2.76,C>=2.01,D<2.01'
                ]
            ]
        ]);
    }

    public function realtimeSync()
    {
        $mode = (string) $this->request->getPost('mode');
        if ($mode === 'akhir') {
            return $this->saveAkhir();
        }
        return $this->saveProses();
    }

    public function saveBobot()
    {
        if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id_mapel = $this->request->getPost('id_mapel');
        $id_kelas = $this->request->getPost('id_kelas');
        $bobot = [
            'bobot_proses' => (int) $this->request->getPost('bobot_proses'),
            'bobot_sas' => (int) $this->request->getPost('bobot_sas'),
            'bobot_sts' => (int) $this->request->getPost('bobot_sts'),
            'bobot_praktik' => (int) $this->request->getPost('bobot_praktik'),
        ];

        if (array_sum($bobot) !== 100) {
            return $this->response->setJSON(['status' => false, 'message' => 'Total bobot harus 100%']);
        }

        $thn = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $db = \Config\Database::connect();

        // Get all students in this class
        $siswa = $this->master->getSiswaByKelas($id_kelas, $thn->id_tp, $smt->id_smt, true);

        $data_to_update = [];
        foreach ($siswa as $s) {
            $data_to_update[] = array_merge([
                'id_siswa' => $s->id_siswa,
                'id_mapel' => $id_mapel,
                'id_tahun' => $thn->id_tp,
                'id_smt' => $smt->id_smt,
            ], $bobot);
        }

        if (!empty($data_to_update)) {
            // Using replace to ensure records exist with these weights
            $db->table('nilai_akhir_kurmer')->upsertBatch($data_to_update);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Bobot penilaian berhasil disimpan dan diterapkan untuk ' . count($data_to_update) . ' siswa'
        ]);
    }

    public function batchSync()
    {
        $mode = (string) $this->request->getPost('mode');
        if ($mode === 'akhir') {
            return $this->batchSaveAkhir();
        }
        return $this->batchSaveProses();
    }

    public function migrateLegacy()
    {
        if (!$this->isLoggedIn() || !($this->isGuru() || $this->isAdmin() || $this->isPanitia())) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false, 'message' => 'User session tidak valid']);
        }
        $thn = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->resolveGuru($user, $thn, $smt);
        if (!$guru) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        $mode = (string) ($this->request->getPost('mode') ?: 'all');
        if (!in_array($mode, ['all', 'proses', 'akhir'], true)) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Mode tidak valid']);
        }
        $idMapel = $this->request->getPost('id_mapel');
        $idKelas = $this->request->getPost('id_kelas');

        $result = $this->adapter->migrateLegacy(
            (int) $guru->id_guru,
            (int) $thn->id_tp,
            (int) $smt->id_smt,
            $idMapel ? (int) $idMapel : null,
            $idKelas ? (int) $idKelas : null,
            $mode
        );

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Migrasi legacy ke kurmer selesai',
            'result' => $result
        ]);
    }



    public function rekapExcel()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');

        if (!$id_mapel || !$id_kelas)
            return "Parameter tidak lengkap";

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $mp = $this->master->getMapel($id_mapel);
        $kl = $this->master->getKelasById($id_kelas);

        if (!$mp || !$kl)
            return "Data Mapel atau Kelas tidak ditemukan";

        $siswa = $this->master->getSiswaByKelas($id_kelas, $tp->id_tp, $smt->id_smt, true);
        if (empty($siswa))
            return "Tidak ada data siswa di kelas ini";

        // 1. Data Nilai Proses (Sumatif LM) - Filter by class/year/semester
        $raw_proses = \Config\Database::connect()->table('nilai_proses np')
            ->select('np.*')
            ->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa')
            ->where('ks.id_kelas', $id_kelas)
            ->where('np.id_mapel', $id_mapel)
            ->where('np.id_tahun', $tp->id_tp)
            ->where('np.id_smt', $smt->id_smt)
            ->where('np.tipe', 'sumatif_lm')
            ->get()->getResult();

        $rekap_proses = [];
        foreach ($raw_proses as $r) {
            if (!isset($rekap_proses[$r->id_siswa]))
                $rekap_proses[$r->id_siswa] = ['total' => 0, 'count' => 0];
            $rekap_proses[$r->id_siswa]['total'] += $r->nilai;
            $rekap_proses[$r->id_siswa]['count']++;
        }

        // 2. Data Nilai STS
        $raw_sts = $this->nilaiModel->getAllNilaiStsKelas($id_kelas, $id_mapel, $tp->id_tp, $smt->id_smt);
        $rekap_sts = [];
        foreach ($raw_sts as $n)
            $rekap_sts[$n->id_siswa] = $n->nilai;

        // 3. Data Nilai Praktik
        $rekap_praktik = [];
        $builder_praktik = \Config\Database::connect()->table('cbt_praktik_hasil h');
        $builder_praktik->select('h.siswa_id, h.nilai_akhir, h.rincian_nilai_rubrik');
        $builder_praktik->join('cbt_praktik p', 'p.id = h.praktik_id');
        $builder_praktik->join('kelas_siswa ks', 'ks.id_siswa = h.siswa_id AND ks.id_kelas = ' . (int) $id_kelas);
        $builder_praktik->where('p.mapel_id', $id_mapel);
        $builder_praktik->where('p.tp_id', $tp->id_tp);
        $builder_praktik->where('p.smt_id', $smt->id_smt);
        $builder_praktik->where('h.status_penilaian', 'sudah_dinilai');
        $praktik_results = $builder_praktik->get()->getResult();

        $temp_praktik = [];
        foreach ($praktik_results as $pr) {
            $nilai_per_ujian = (float) $pr->nilai_akhir;
            $rincian = json_decode($pr->rincian_nilai_rubrik ?? '[]', true);
            if (!empty($rincian) && is_array($rincian) && count($rincian) > 0) {
                $sum_rincian = array_sum($rincian);
                // Handle legacy data stored as sum
                if ($nilai_per_ujian > 100 || abs($nilai_per_ujian - $sum_rincian) < 0.1) {
                    $nilai_per_ujian = $nilai_per_ujian / count($rincian);
                }
            }

            if (!isset($temp_praktik[$pr->siswa_id])) {
                $temp_praktik[$pr->siswa_id] = ['total' => 0, 'count' => 0];
            }
            $temp_praktik[$pr->siswa_id]['total'] += $nilai_per_ujian;
            $temp_praktik[$pr->siswa_id]['count']++;
        }
        foreach ($temp_praktik as $sid => $val) {
            $rekap_praktik[$sid] = round($val['total'] / $val['count'], 2);
        }


        $existing = \Config\Database::connect()->table('nilai_akhir_kurmer nak')
            ->join('kelas_siswa ks', 'ks.id_siswa = nak.id_siswa')
            ->where('ks.id_kelas', $id_kelas)
            ->where('nak.id_mapel', $id_mapel)
            ->where('nak.id_tahun', $tp->id_tp)
            ->where('nak.id_smt', $smt->id_smt)
            ->get()->getResult();
        $nilai_akhir = [];
        foreach ($existing as $e)
            $nilai_akhir[$e->id_siswa] = $e;

        $filename = "Rekap_Nilai_" . ($kl->nama_kelas ?? 'Kelas') . "_" . ($mp->nama_mapel ?? 'Mapel') . ".xls";

        $first_na = !empty($nilai_akhir) ? reset($nilai_akhir) : null;
        $b_proc = $first_na ? $first_na->bobot_proses : 40;
        $b_sts = $first_na ? ($first_na->bobot_sts ?? 0) : 20;
        $b_sas = $first_na ? $first_na->bobot_sas : 20;
        $b_praktik = $first_na ? ($first_na->bobot_praktik ?? 0) : 20;

        ob_clean();
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Rekap_Nilai_{$mp->nama_mapel}_{$kl->nama_kelas}.xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Use inline styles and standard HTML attributes for better Excel compatibility
        echo "
        <style>
            .title { font-family: 'Segoe UI', sans-serif; font-size: 16pt; font-weight: bold; color: #00695c; text-align: center; }
            .info-table td { font-family: 'Segoe UI', sans-serif; font-size: 10pt; }
            .data-table th { font-family: 'Segoe UI', sans-serif; font-size: 9pt; background-color: #00695c; color: #ffffff; border: 1px solid #004d40; }
            .data-table td { font-family: 'Segoe UI', sans-serif; font-size: 9pt; border: 1px solid #cccccc; }
            .str { mso-number-format:\"\\@\"; }
        </style>
        ";

        echo "<div class='title'>REKAPITULASI NILAI AKHIR SEMESTER</div><br>";
        echo "<table class='info-table' border='0'>
                <tr><td style='font-weight:bold; width:150px;'>Mata Pelajaran</td><td>: " . ($mp->nama_mapel ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Kelas</td><td>: " . ($kl->nama_kelas ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Tahun Pelajaran</td><td>: " . ($tp->tahun ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Semester</td><td>: " . ($smt->nama_smt ?? '-') . "</td></tr>
                <tr><td style='font-weight:bold;'>Bobot Penilaian</td><td>: Sumatif (" . $b_proc . "%), STS (" . $b_sts . "%), SAS (" . $b_sas . "%), Praktik (" . $b_praktik . "%)</td></tr>
              </table><br>";

        echo "<table class='data-table' border='1' cellspacing='0' cellpadding='5'>
                <thead>
                    <tr bgcolor='#00695c' style='color: #ffffff;'>
                        <th style='width: 40px; color: #ffffff;'>No</th>
                        <th style='width: 250px; color: #ffffff;'>Nama Siswa</th>
                        <th style='width: 100px; color: #ffffff;'>NISN</th>
                        <th style='width: 80px; color: #ffffff;'>Rerata Sumatif (LM)</th>
                        <th style='width: 80px; color: #ffffff;'>Nilai STS</th>";
        if ($b_praktik > 0)
            echo "<th style='width: 80px; color: #ffffff;'>Nilai Praktik</th>";
        echo "          <th style='width: 80px; color: #ffffff;'>Nilai SAS</th>
                        <th style='width: 80px; color: #ffffff;'>Nilai Rapor</th>
                        <th style='width: 50px; color: #ffffff;'>Pred</th>
                        <th style='width: 300px; color: #ffffff;'>Capaian Tertinggi</th>
                        <th style='width: 300px; color: #ffffff;'>Perlu Bimbingan</th>
                    </tr>
                </thead>
                <tbody>";
        $no = 1;
        foreach ($siswa as $s) {
            $id = $s->id_siswa;
            $avg_p = isset($rekap_proses[$id]) && $rekap_proses[$id]['count'] > 0 ? round($rekap_proses[$id]['total'] / $rekap_proses[$id]['count']) : 0;
            $sts = $rekap_sts[$id] ?? 0;
            $praktik_val = $rekap_praktik[$id] ?? 0;
            $na = $nilai_akhir[$id] ?? null;
            $sas = $na ? $na->nilai_sas : 0;
            $rapor = $na ? $na->nilai_rapor : 0;
            $pred = \App\Models\NilaiModel::getPredikat($rapor ?: $avg_p);

            $rowBg = ($no % 2 == 0) ? '#f9f9f9' : '#ffffff';

            echo "<tr bgcolor='$rowBg'>
                    <td align='center'>" . $no++ . "</td>
                    <td align='left'>" . $s->nama . "</td>
                    <td class='str' align='center'>" . $s->nisn . "</td>
                    <td align='center'>" . $avg_p . "</td>
                    <td align='center'>" . $sts . "</td>";
            if ($b_praktik > 0)
                echo "<td align='center'>" . $praktik_val . "</td>";
            echo "  <td align='center'>" . $sas . "</td>
                    <td align='center' bgcolor='#e0f2f1' style='font-weight:bold; color:#00695c;'>" . $rapor . "</td>
                    <td align='center' style='font-weight:bold;'>" . $pred['huruf'] . "</td>
                    <td align='left' style='font-size:8pt;'>" . ($na ? esc($na->deskripsi_tinggi) : '-') . "</td>
                    <td align='left' style='font-size:8pt;'>" . ($na ? esc($na->deskripsi_rendah) : '-') . "</td>
                  </tr>";
        }
        echo "</tbody></table>";
        exit;
    }

    public function rekapPdf()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');

        if (!$id_mapel || !$id_kelas)
            return "Parameter tidak lengkap";

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $mp = $this->master->getMapel($id_mapel);
        $kl = $this->master->getKelasById($id_kelas);

        if (!$mp || !$kl)
            return "Data Mapel atau Kelas tidak ditemukan";

        $siswa = $this->master->getSiswaByKelas($id_kelas, $tp->id_tp, $smt->id_smt, true);
        if (empty($siswa))
            return "Tidak ada data siswa di kelas ini";

        // 1. Data Nilai Proses (Sumatif LM) - Filter by class/year/semester
        $raw_proses = \Config\Database::connect()->table('nilai_proses np')
            ->select('np.*')
            ->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa')
            ->where('ks.id_kelas', $id_kelas)
            ->where('np.id_mapel', $id_mapel)
            ->where('np.id_tahun', $tp->id_tp)
            ->where('np.id_smt', $smt->id_smt)
            ->where('np.tipe', 'sumatif_lm')
            ->get()->getResult();

        $rekap_proses = [];
        foreach ($raw_proses as $r) {
            if (!isset($rekap_proses[$r->id_siswa]))
                $rekap_proses[$r->id_siswa] = ['total' => 0, 'count' => 0];
            $rekap_proses[$r->id_siswa]['total'] += $r->nilai;
            $rekap_proses[$r->id_siswa]['count']++;
        }

        // 2. Data Nilai STS
        $raw_sts = $this->nilaiModel->getAllNilaiStsKelas($id_kelas, $id_mapel, $tp->id_tp, $smt->id_smt);
        $rekap_sts = [];
        foreach ($raw_sts as $n)
            $rekap_sts[$n->id_siswa] = $n->nilai;

        // 3. Data Nilai Praktik
        $rekap_praktik = [];
        $builder_praktik = \Config\Database::connect()->table('cbt_praktik_hasil h');
        $builder_praktik->select('h.siswa_id, h.nilai_akhir, h.rincian_nilai_rubrik');
        $builder_praktik->join('cbt_praktik p', 'p.id = h.praktik_id');
        $builder_praktik->join('kelas_siswa ks', 'ks.id_siswa = h.siswa_id AND ks.id_kelas = ' . (int) $id_kelas);
        $builder_praktik->where('p.mapel_id', $id_mapel);
        $builder_praktik->where('p.tp_id', $tp->id_tp);
        $builder_praktik->where('p.smt_id', $smt->id_smt);
        $builder_praktik->where('h.status_penilaian', 'sudah_dinilai');
        $praktik_results = $builder_praktik->get()->getResult();

        $temp_praktik = [];
        foreach ($praktik_results as $pr) {
            $nilai_per_ujian = (float) $pr->nilai_akhir;
            $rincian = json_decode($pr->rincian_nilai_rubrik ?? '[]', true);
            if (!empty($rincian) && is_array($rincian) && count($rincian) > 0) {
                $sum_rincian = array_sum($rincian);
                if ($nilai_per_ujian > 100 || abs($nilai_per_ujian - $sum_rincian) < 0.1) {
                    $nilai_per_ujian = $nilai_per_ujian / count($rincian);
                }
            }

            if (!isset($temp_praktik[$pr->siswa_id])) {
                $temp_praktik[$pr->siswa_id] = ['total' => 0, 'count' => 0];
            }
            $temp_praktik[$pr->siswa_id]['total'] += $nilai_per_ujian;
            $temp_praktik[$pr->siswa_id]['count']++;
        }
        foreach ($temp_praktik as $sid => $val) {
            $rekap_praktik[$sid] = round($val['total'] / $val['count'], 2);
        }

        // Data Praktik
        $setting = $this->dashboard->getSetting();
        $rekap_praktik = [];
        if (isset($setting->bobot_praktik) && $setting->bobot_praktik > 0) {
            $builder_praktik = \Config\Database::connect()->table('cbt_praktik_hasil h');
            $builder_praktik->select('h.siswa_id, h.nilai_akhir, h.rincian_nilai_rubrik');
            $builder_praktik->join('cbt_praktik p', 'p.id = h.praktik_id');
            $builder_praktik->where('p.mapel_id', $id_mapel);
            $builder_praktik->where('p.tp_id', $tp->id_tp);
            $builder_praktik->where('p.smt_id', $smt->id_smt);
            $builder_praktik->where('h.status_penilaian', 'sudah_dinilai');
            $praktik_results = $builder_praktik->get()->getResult();

            $temp_praktik = [];
            foreach ($praktik_results as $pr) {
                $nilai_per_ujian = (float) $pr->nilai_akhir;
                $rincian = json_decode($pr->rincian_nilai_rubrik ?? '[]', true);
                if (!empty($rincian) && is_array($rincian) && count($rincian) > 0) {
                    $sum_rincian = array_sum($rincian);
                    // Handle legacy data stored as sum
                    if ($nilai_per_ujian > 100 || abs($nilai_per_ujian - $sum_rincian) < 0.1) {
                        $nilai_per_ujian = $nilai_per_ujian / count($rincian);
                    }
                }

                if (!isset($temp_praktik[$pr->siswa_id])) {
                    $temp_praktik[$pr->siswa_id] = ['total' => 0, 'count' => 0];
                }
                $temp_praktik[$pr->siswa_id]['total'] += $nilai_per_ujian;
                $temp_praktik[$pr->siswa_id]['count']++;
            }
            foreach ($temp_praktik as $sid => $val) {
                $rekap_praktik[$sid] = round($val['total'] / $val['count'], 2);
            }
        }

        $existing = \Config\Database::connect()->table('nilai_akhir_kurmer nak')
            ->join('kelas_siswa ks', 'ks.id_siswa = nak.id_siswa')
            ->where('ks.id_kelas', $id_kelas)
            ->where('nak.id_mapel', $id_mapel)
            ->where('nak.id_tahun', $tp->id_tp)
            ->where('nak.id_smt', $smt->id_smt)
            ->get()->getResult();
        $nilai_akhir = [];
        foreach ($existing as $e)
            $nilai_akhir[$e->id_siswa] = $e;

        $user = $this->getUser();
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        $data = [
            'judul' => 'Rekap Nilai Akhir Semester',
            'mapel' => $mp,
            'kelas' => $kl,
            'tp' => $tp,
            'smt' => $smt,
            'siswa' => $siswa,
            'rekap_proses' => $rekap_proses,
            'rekap_sts' => $rekap_sts,
            'rekap_praktik' => $rekap_praktik,
            'nilai_akhir' => $nilai_akhir,
            'setting' => $setting,
            'guru' => $guru,
            'user' => $user
        ];

        $html = view('guru/nilai/rekap_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Rekap_Nilai_" . ($kl->nama_kelas ?? 'Kelas') . ".pdf", ["Attachment" => 0]);
        exit;
    }

    /**
     * Helper: Extract tingkat from nama kelas (e.g., "X IPA 1" -> 10, "XI IPS 2" -> 11, "XII IPA" -> 12)
     */
    private function extractTingkatFromNamaKelas(string $nama_kelas): ?int
    {
        $nama_upper = strtoupper(trim($nama_kelas));

        // Check for XII (12) first, then XI (11), then X (10)
        if (str_starts_with($nama_upper, 'XII') || str_contains($nama_upper, ' XII')) {
            return 12;
        }
        if (str_starts_with($nama_upper, 'XI') || str_contains($nama_upper, ' XI')) {
            return 11;
        }
        if (str_starts_with($nama_upper, 'X') || str_contains($nama_upper, ' X')) {
            return 10;
        }

        // Check for numeric prefix (e.g., "10 IPA 1", "11 IPS 2")
        if (preg_match('/^(\d{1,2})[\s\-\.]/', $nama_kelas, $matches)) {
            $tingkat = (int) $matches[1];
            if ($tingkat >= 10 && $tingkat <= 12) {
                return $tingkat;
            }
        }

        return null;
    }

    /**
     * Helper: Get mapel/kelas yang diampu guru
     */
    private function getMapelGuru($guru, $tp, $smt)
    {
        $mapel_guru = $this->master->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapel_grouped = [];
        if ($mapel_guru && !empty($mapel_guru->mapel_kelas)) {
            $raw_mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas);
            if (is_array($raw_mapel)) {
                foreach ($raw_mapel as $m) {
                    if (!isset($m->id_mapel))
                        continue;

                    $m_id = (int) $m->id_mapel;
                    if (!isset($mapel_grouped[$m_id])) {
                        $mp = $this->master->getMapel($m_id);
                        $new_m = new \stdClass();
                        $new_m->id_mapel = $m_id;
                        $new_m->nama_mapel = $mp ? $mp->nama_mapel : ($m->nama_mapel ?? 'Unknown');
                        $new_m->kode_mapel = $mp ? ($mp->kode ?? '') : ($m->kode_mapel ?? '');
                        $new_m->kelas = [];
                        $mapel_grouped[$m_id] = $new_m;
                    }

                    // Collect classes
                    $temp_classes = [];
                    if (isset($m->kelas_mapel) && is_array($m->kelas_mapel)) {
                        foreach ($m->kelas_mapel as $km) {
                            $id_k = $km->kelas ?? ($km->id_kelas ?? null);
                            if ($id_k)
                                $temp_classes[] = $id_k;
                        }
                    } else if (isset($m->id_kelas)) {
                        $temp_classes[] = $m->id_kelas;
                    }

                    foreach (array_unique($temp_classes) as $id_k) {
                        $kl = $this->master->getKelasById($id_k);
                        if ($kl) {
                            $mapel_grouped[$m_id]->kelas[] = (object) [
                                'id_kelas' => $id_k,
                                'nama_kelas' => $kl->nama_kelas,
                                'tingkat' => $kl->level_id ?? ($kl->tingkat ?? '?')
                            ];
                        }
                    }
                }
            }
        }
        return array_values($mapel_grouped);
    }

    /**
     * Ambil daftar jadwal CBT yang relevan untuk mapel dan kelas ini
     */
    public function getCbtJadwal()
    {
        if (!$this->request) {
            return \Config\Services::response()->setJSON([
                'status'  => false,
                'message' => 'Request tidak diinisialisasi. Panggil lewat routing CodeIgniter.',
            ]);
        }

        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');
        if (getenv('DBG_CBT_JS')) {
            echo 'DBG mapel=' . var_export($id_mapel, true) . ' kelas=' . var_export($id_kelas, true) . "\n";
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $cbtModel = new \App\Models\CbtModel();
        $allJadwal = $cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt);

        $filtered = [];
        foreach ($allJadwal as $j) {
            $bank_kelas = $cbtModel->maybeUnserialize($j->bank_kelas ?? '[]');
            $kelasIds = [];
            if (is_array($bank_kelas)) {
                foreach ($bank_kelas as $bk) {
                    $kelasIds[] = is_object($bk) ? ($bk->kelas_id ?? '') : (is_array($bk) ? ($bk['kelas_id'] ?? '') : $bk);
                }
            }

            // Filter berdasarkan kelas DAN mata pelajaran
            $matchMapel = true;
            if ($id_mapel) {
                $j_mapel_id = $j->bank_mapel_id ?? null;
                if ($j_mapel_id && $j_mapel_id != $id_mapel) {
                    $matchMapel = false;
                }
            }

            if (in_array($id_kelas, $kelasIds) && $matchMapel) {
                if (getenv('DBG_CBT_JS')) {
                    echo 'DBG match jadwal=' . $j->id_jadwal . ' kelasIds=' . json_encode($kelasIds) . "\n";
                }
                $filtered[] = [
                    'id_jadwal' => (int) $j->id_jadwal,
                    'bank_kode' => $j->bank_kode,
                    'nama_mapel' => $j->nama_mapel,
                    'tgl_mulai' => $j->tgl_mulai,
                    // Konfigurasi katrol dijadwal valid, tapi belum tentu ada nilai
                    // katrol yang tersimpan — dicek after sekali query (di bawah).
                    'has_config_katrol' => ($j->katrol_max > 0 && $j->katrol_min >= 0 && $j->katrol_max > $j->katrol_min),
                    'has_katrol' => false,
                ];
            }
        }

        // Satu query tambahan: jadwal manakah yang benar-benar sudah punya
        // nilai katrol tersimpan di cbt_nilai (nilai_katrol > 0)?
        $idsJadwal = array_column($filtered, 'id_jadwal');
        if ($idsJadwal) {
            $katrolAda = \Config\Database::connect()->table('cbt_nilai')
                ->select('id_jadwal, COUNT(*) AS jml')
                ->whereIn('id_jadwal', $idsJadwal)
                ->where('nilai_katrol IS NOT NULL')
                ->groupBy('id_jadwal')
                ->get()->getResult();
            $mapKatrol = [];
            foreach ($katrolAda as $k) {
                $mapKatrol[$k->id_jadwal] = (int) $k->jml;
            }
            if (getenv('DBG_CBT_JS')) {
                echo 'DBG filtered=' . count($filtered) . ' mapKatrol=' . json_encode($mapKatrol) . "\n";
            }
            foreach ($filtered as &$f) {
                $f['katrol_tersimpan'] = $mapKatrol[$f['id_jadwal']] ?? 0;
                $f['has_katrol'] = $f['has_config_katrol'] && $f['katrol_tersimpan'] > 0;
            }
            unset($f);
        }

        return $this->response->setJSON($filtered);
    }

    /**
     * Ambil nilai dari sebuah jadwal CBT untuk diimpor ke penilaian
     */
    public function getCbtNilai()
    {
        $id_jadwal = $this->request->getPost('id_jadwal');
        $id_kelas = $this->request->getPost('id_kelas');
        $import_type = $this->request->getPost('import_type') ?: 'asli';

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $cbtModel = new \App\Models\CbtModel();

        try {
            $jadwal = $cbtModel->olahNilai(1, $tp->id_tp, $smt->id_smt, $id_kelas, $id_jadwal);

            $results = [];
            $meta = [
                'dari_katrol' => [],      // siswa yang pakai nilai katrol
                'tanpa_katrol' => [],     // siswa yang jatuh ke nilai asli (katrol belum diisi)
                'skipped' => [],          // siswa di luar daftar (tidak ikut ujian sama sekali)
            ];
            if ($jadwal && isset($jadwal->sesisiswa)) {
                foreach ($jadwal->sesisiswa as $ss) {
                    // Skip siswa yang tidak pernah ikut ujian (tidak ada data jawaban/kehadiran)
                    $ikut = !empty($ss->cbtsiswa) && ((array) $ss->cbtsiswa !== []);
                    if (!$ikut) {
                        $meta['skipped'][] = (int) $ss->siswa_id;
                        continue;
                    }

                    $skorAsli = floatval($ss->nilai_akhir->skor_total);
                    $skorKatrol = isset($ss->nilai_akhir->nilai_katrol) ? floatval($ss->nilai_akhir->nilai_katrol) : 0.0;

                    if ($import_type === 'katrol' && $skorKatrol > 0) {
                        $results[$ss->siswa_id] = $skorKatrol;
                        $meta['dari_katrol'][] = (int) $ss->siswa_id;
                    } else {
                        // Mode asli, ATAU mode katrol tapi siswa belum punya nilai katrol
                        // -> jatuh ke nilai asli (ditandai di meta agar guru diinfokan).
                        $results[$ss->siswa_id] = $skorAsli;
                        if ($import_type === 'katrol') {
                            $meta['tanpa_katrol'][] = (int) $ss->siswa_id;
                        }
                    }
                }
            }

            foreach ($meta as &$list) {
                $list = array_values(array_unique($list));
            }
            unset($list);

            return $this->response->setJSON([
                'status' => true,
                'data' => $results,
                'meta' => $meta
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function cetakleger()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        $user = $this->getUser();

        // ── Filter dari request (dengan fallback ke aktif) ──────────────────
        $idTpReq  = $this->request->getGet('id_tp');
        $idSmtReq = $this->request->getGet('id_smt');
        $idKelasReq = $this->request->getGet('id_kelas');
        $idMapelReq = $this->request->getGet('id_mapel');

        $tpActive = $this->dashboard->getTahunActive();
        $smtActive = $this->dashboard->getSemesterActive();

        $idTp  = $idTpReq  ?: ($tpActive ? $tpActive->id_tp : null);
        $idSmt = $idSmtReq ?: ($smtActive ? $smtActive->id_smt : null);

        if (!$idTp || !$idSmt) {
            return redirect()->to('dashboard')->with('error', 'Tahun pelajaran atau semester belum ditetapkan.');
        }

        $tpObj  = $this->dashboard->getTahunByTahun($idTp)  ?: $tpActive;
        $smtObj = $this->dashboard->getSemesterActive(); // semarinya tetap ambil aktif untuk label
        // Cari objek smt yang sesuai dari daftar semua semester
        $allSmt = $this->dashboard->getSemester();
        foreach ($allSmt as $s) {
            if ((int)$s->id_smt === (int)$idSmt) { $smtObj = $s; break; }
        }

        $data = [
            'user'        => $user,
            'judul'       => 'Ledger Nilai Kelas',
            'subjudul'    => 'Rekapitulasi Nilai Akademik',
            'setting'     => $this->dashboard->getSetting(),
            'tp'          => $this->dashboard->getTahun(),
            'tp_active'   => $tpObj,
            'smt'         => $allSmt,
            'smt_active'  => $smtObj,
            'id_tp_sel'   => (int)$idTp,
            'id_smt_sel'  => (int)$idSmt,
            'id_kelas_sel'=> $idKelasReq ? (int)$idKelasReq : null,
            'id_mapel_sel'=> $idMapelReq ? (int)$idMapelReq : null,
        ];

        // ── Resolve kelas: paksa wali_kelas jika guru adalah wali kelas ─────
        $guru = $this->dashboard->getDataGuruByUsername($user->username, $idTp, $idSmt);
        $data['guru'] = $guru;

        $isWaliKelas = $guru && !empty($guru->wali_kelas);
        if ($idKelasReq) {
            $idKelas = (int)$idKelasReq;
        } elseif ($isWaliKelas) {
            $idKelas = (int)$guru->wali_kelas;
        } else {
            return redirect()->to('dashboard')->with('error', 'Anda belum ditetapkan sebagai wali kelas. Pilih kelas secara manual.');
        }

        $data['kelas'] = $this->master->getKelasById($idKelas);
        if (!$data['kelas']) {
            return redirect()->to('dashboard')->with('error', 'Kelas yang dipilih tidak ditemukan.');
        }

        // ── Data siswa & mapel ─────────────────────────────────────────────
        $kelompoks = array_values($this->master->getDataKelompokMapel());
        $siswas = $this->master->getSiswaByKelas($idKelas, $idTp, $idSmt, true);

        $allMapels = $this->master->getAllMapel();
        // Filter mapel jika ada id_mapel yang dipilih
        if ($idMapelReq) {
            $mapels = array_filter($allMapels, fn($m) => (int)$m->id_mapel === (int)$idMapelReq);
            $mapels = array_values($mapels);
        } else {
            $mapels = $allMapels;
        }

        $arrSiswaIds = empty($siswas) ? [0] : array_column($siswas, 'id_siswa');

        // ── Ambil nilai dari tabel nilai_akhir_kurmer ──────────────────────
        $db = \Config\Database::connect();
        $nilai_rapor = [];
        if ($db->tableExists('nilai_akhir_kurmer')) {
            $builder = $db->table('nilai_akhir_kurmer')
                ->select('id_siswa, id_mapel, nilai_rapor as nilai')
                ->where('id_tahun', $idTp)
                ->where('id_smt', $idSmt)
                ->whereIn('id_siswa', $arrSiswaIds);

            if ($idMapelReq) {
                $builder->where('id_mapel', (int)$idMapelReq);
            }

            $nilai_rapor = $builder->get()->getResult();
        }

        $nilai = [];
        foreach ($nilai_rapor as $n) {
            $nilai[$n->id_siswa][$n->id_mapel] = $n;
        }

        // ── Hitung total, rata-rata, dan peringkat (handle tie) ────────────
        $rankData = [];
        $siswaStats = [];
        foreach ($siswas as $siswa) {
            $jml = 0;
            $cnt = 0;
            $mapelNilai = [];

            foreach ($mapels as $m) {
                $n = isset($nilai[$siswa->id_siswa][$m->id_mapel]) ? $nilai[$siswa->id_siswa][$m->id_mapel]->nilai : null;
                if ($n !== null && $n !== '') {
                    $n = (float)$n;
                    $mapelNilai[$m->id_mapel] = $n;
                    if ($n >= 0) { // nilai 0 tetap dihitung
                        $jml += $n;
                        $cnt++;
                    }
                } else {
                    $mapelNilai[$m->id_mapel] = null;
                }
            }

            $avg = $cnt > 0 ? round($jml / $cnt, 2) : 0;
            $rankData[$siswa->id_siswa] = $jml;
            $siswaStats[$siswa->id_siswa] = [
                'jml'  => $jml,
                'cnt'  => $cnt,
                'avg'  => $avg,
                'nilai' => $mapelNilai,
            ];
        }

        // Peringkat dengan penanganan tie (nilai sama = ranking sama)
        arsort($rankData);
        $ranks = [];
        $prevScore = null;
        $prevRank = 0;
        $countSame = 0;
        foreach ($rankData as $sid => $score) {
            if ($prevScore !== null && $score == $prevScore) {
                $ranks[$sid] = $prevRank;
                $countSame++;
            } else {
                $ranks[$sid] = $prevRank + $countSame + 1;
                $prevRank = $ranks[$sid];
                $countSame = 0;
            }
            $prevScore = $score;
        }

        $data['siswas']      = $siswas;
        $data['mapels']      = $mapels;
        $data['kelompoks']   = $kelompoks;
        $data['nilai']       = $nilai;
        $data['siswaStats']  = $siswaStats;
        $data['ranks']       = $ranks;
        $data['allKelas']    = $this->master->getAllKelas($idTp, $idSmt);

        return $this->renderView('members/guru/rapor/cetak/leger', $data);
    }

    public function dkn()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        $user = $this->getUser();

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'DKN Rapor Akademik',
            'subjudul' => 'Daftar Kumpulan Nilai Kurikulum Merdeka',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
        ];

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru || empty($guru->wali_kelas)) {
            return redirect()->to('dashboard')->with('error', 'Anda belum ditetapkan sebagai wali kelas.');
        }

        $id_kelas = $guru->wali_kelas;
        $data['guru'] = $guru;
        $data['kelas'] = $this->master->getKelasById($id_kelas);

        $kelompoks = array_values($this->master->getDataKelompokMapel());
        $siswas = $this->master->getSiswaByKelas($id_kelas, $tp->id_tp, $smt->id_smt, true);

        $mapels = $this->master->getAllMapel();

        $arrSiswaIds = empty($siswas) ? [0] : array_column($siswas, 'id_siswa');

        $db = \Config\Database::connect();
        $nilai_rapor = [];
        if ($db->tableExists('nilai_akhir_kurmer')) {
            $nilai_rapor = $db->table('nilai_akhir_kurmer')
                ->select('id_siswa, id_mapel, nilai_rapor as nilai')
                ->where('id_tahun', $tp->id_tp)
                ->where('id_smt', $smt->id_smt)
                ->whereIn('id_siswa', $arrSiswaIds)
                ->get()->getResult();
        }

        $nilai = [];
        foreach ($nilai_rapor as $n) {
            $nilai[$n->id_siswa][$n->id_mapel] = $n;
        }

        $data['siswas'] = $siswas;
        $data['mapels'] = $mapels;
        $data['kelompoks'] = $kelompoks;
        $data['nilai'] = $nilai;

        return $this->renderView('members/guru/rapor/cetak/dkn', $data);
    }
    protected function getTpSmt()
    {
        return (object) [
            'tp' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemesterActive()
        ];
    }
}
