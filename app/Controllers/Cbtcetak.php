<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;
use App\Models\DropdownModel;

/**
 * Cbtcetak Controller - Migrated from CI3
 * Handles printing CBT cards, attendance, reports
 */
class Cbtcetak extends BaseController
{
    protected CbtModel $cbtModel;
    protected DashboardModel $dashboardModel;
    protected MasterModel $masterModel;
    protected DropdownModel $dropdownModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel();
        $this->dropdownModel = new DropdownModel();
    }

    /**
     * Main index page - Print options
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        // Only allow admin or panitia with jabatan 'panitia' (not 'pengawas')
        // Guru can also access for limited functionality
        if ($this->isPanitia() && !$this->hasPanitiaJabatan()) {
            return redirect()->to('dashboard')->with('error', 'Akses ditolak. Hanya Administrator atau Panitia yang dapat mengakses Administrasi Ujian.');
        }

        if (!$this->isAdmin() && !$this->isPanitia() && !$this->inGroup('guru')) {
            return redirect()->to('dashboard')->with('error', 'Akses Terlarang');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();

        $data = [
            'user' => $user,
            'judul' => 'Cetak Kelengkapan Ujian',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'is_admin' => $this->isAdmin() || $this->isPanitia(),
            'guru' => null,
            'ids_pengawas' => [],
            'panitia' => null,
        ];

        return view('cbt/cetak/data', $data);
    }

    /**
     * Kartu Peserta page
     */
    public function kartuPeserta()
    {
        try {
            if (!$this->isLoggedIn()) {
                return redirect()->to('auth');
            }

            $user = $this->getUser();
            $setting = $this->dashboardModel->getSetting();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return redirect()->back()->with('error', 'Tahun Pelajaran atau Semester belum aktif.');
            }

            $kelas = [];
            foreach ($this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt) as $k) {
                $kelas[$k->id_kelas] = $k->nama_kelas;
            }

            $ruang = [];
            foreach ($this->cbtModel->getAllRuang() as $r) {
                $ruang[$r->id_ruang] = $r->nama_ruang;
            }

            $data = [
                'user' => $user,
                'judul' => 'Cetak Kartu Peserta',
                'subjudul' => 'Cetak',
                'profile' => null,
                'setting' => $setting,
                'tp' => $this->dashboardModel->getTahun(),
                'tp_active' => $tp,
                'smt' => $this->dashboardModel->getSemester(),
                'smt_active' => $smt,
                'kelas' => $kelas,
                'ruang' => $ruang,
                'kartu' => $this->cbtModel->getSettingKartu(),
                'setting_rapor' => $this->dashboardModel->getSettingRapor()
            ];

            return view('cbt/cetak/kartu', $data);
        } catch (\Throwable $th) {
            return $this->response->setStatusCode(500)->setBody($th->getMessage() . '<br><pre>' . $th->getTraceAsString() . '</pre>');
        }
    }

    /**
     * Kartu Pengawas page
     */
    public function kartuPengawas()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return redirect()->back()->with('error', 'Tahun Pelajaran atau Semester belum aktif.');
        }

        $data = [
            'user' => $user,
            'judul' => 'Cetak Kartu Pengawas',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kartu' => $this->cbtModel->getSettingKartu()
        ];

        return view('cbt/cetak/kartupengawas', $data);
    }

    /**
     * Kartu Meja page
     */
    public function kartuMeja()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return redirect()->back()->with('error', 'Tahun Pelajaran atau Semester belum aktif.');
        }

        $kelas = [];
        foreach ($this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt) as $k) {
            $kelas[$k->id_kelas] = $k->nama_kelas;
        }

        $ruang = [];
        foreach ($this->cbtModel->getAllRuang() as $r) {
            $ruang[$r->id_ruang] = $r->nama_ruang;
        }
        $data['ruang'] = $ruang;

        $data = [
            'user' => $user,
            'judul' => 'Cetak Kartu Meja',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kelas' => $kelas,
            'ruang' => $ruang, // Correctly assign array
            'kartu' => $this->cbtModel->getSettingKartu()
        ];
        return view('cbt/cetak/kartumeja', $data);
    }

    /**
     * Upload file/logo
     */
    public function uploadFile($logo = 'logo')
    {
        try {
            $file = $this->request->getFile('logo');

            if (!$file || !$file->isValid()) {
                return $this->jsonResponse(false, 'File tidak valid');
            }

            $allowedTypes = ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return $this->jsonResponse(false, 'Tipe file tidak diizinkan');
            }

            $newName = $logo . '.' . $file->getExtension();
            $uploadPath = FCPATH . 'uploads/settings/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if ($file->move($uploadPath, $newName, true)) {
                return $this->jsonResponse(true, 'File berhasil diupload', [
                    'src' => base_url('uploads/settings/' . $newName),
                    'filename' => pathinfo($newName, PATHINFO_FILENAME),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize()
                ]);
            }

            return $this->jsonResponse(false, 'Gagal mengupload file');
        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    /**
     * Delete file
     */
    public function deleteFile()
    {
        try {
            $src = $this->request->getPost('src');
            $file_name = str_replace(base_url(), '', $src ?? '');
            $file_path = FCPATH . ltrim($file_name, '/');

            if (file_exists($file_path) && unlink($file_path)) {
                return $this->jsonResponse(true, 'File berhasil dihapus');
            }

            return $this->jsonResponse(false, 'File tidak ditemukan');
        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    /**
     * Save kartu
     */
    public function saveKartu()
    {
        try {
            $insert = [
                'id_set_kartu' => 123456,
                'header_1' => $this->request->getPost('header_1'),
                'header_2' => $this->request->getPost('header_2'),
                'header_3' => $this->request->getPost('header_3'),
                'header_4' => $this->request->getPost('header_4'),
                'tanggal' => $this->request->getPost('tanggal'),
            ];
            $update = $this->cbtModel->replaceKopKartu($insert);

            return $this->jsonResponse($update, $update ? 'Kartu berhasil disimpan' : 'Gagal menyimpan kartu');
        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    /**
     * Get siswa by kelas (AJAX)
     */
    public function getSiswaKelas()
    {
        try {
            $kelas = $this->request->getGet('kelas');

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return $this->jsonResponse(false, 'Tahun/Semester aktif tidak ditemukan');
            }

            if (empty($kelas)) {
                return $this->jsonResponse(false, 'Kelas tidak dipilih');
            }

            $info = [
                'kelas' => $this->masterModel->getKelasById((int) $kelas),
            ];

            // Get students by class with their ruang/sesi assignment
            $siswa = $this->cbtModel->getSiswaByKelas($tp->id_tp, $smt->id_smt, (int) $kelas);

            return $this->jsonResponse([
                'siswa' => $siswa,
                'info' => $info,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    /**
     * Get siswa by ruang (AJAX)
     */
    public function getSiswaRuang()
    {
        try {
            $sesi = $this->request->getGet('sesi');
            $jadwal = $this->request->getGet('jadwal');
            $ruang = $this->request->getGet('ruang');

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return $this->jsonResponse(false, 'Tahun/Semester aktif tidak ditemukan');
            }

            if (empty($ruang)) {
                return $this->jsonResponse(false, 'Ruang tidak dipilih');
            }

            $info = [
                'ruang' => $this->cbtModel->getRuangById($ruang),
            ];

            $siswa = $this->cbtModel->getSiswaByRuangId($tp->id_tp, $smt->id_smt, (int) $ruang);

            return $this->jsonResponse([
                'siswa' => $siswa,
                'info' => $info
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    /**
     * Get all panitia/pengawas (AJAX)
     */
    public function getPanitia()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->jsonResponse(false, 'Unauthorized');
            }

            $panitiaModel = new \App\Models\PanitiaModel();
            $panitia = $panitiaModel->get_all();

            return $this->jsonResponse([
                'panitia' => $panitia
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }


    /**
     * Save berita acara
     */
    public function saveBeritaAcara()
    {
        // Implementation
    }

    /**
     * Save pakta integritas
     */
    public function savePaktaIntegritas()
    {
        // Implementation
    }

    /**
     * Absen peserta page
     */
    public function absenPeserta()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return redirect()->back()->with('error', 'Tahun Pelajaran atau Semester belum aktif.');
        }

        $jenis_ujian = $this->cbtModel->getAllJenisUjian();
        $ujian = [];
        foreach ($jenis_ujian as $j) {
            $ujian[$j->id_jenis] = $j->nama_jenis . " (" . $j->kode_jenis . ")";
        }

        $kelas_data = $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $kelas = [];
        foreach ($kelas_data as $k) {
            $kelas[$k->id_kelas] = $k->nama_kelas;
        }

        $ruang_data = $this->cbtModel->getAllRuang();
        $ruang = [];
        foreach ($ruang_data as $r) {
            $ruang[$r->id_ruang] = $r->nama_ruang;
        }

        $sesi_data = $this->cbtModel->getAllSesi();
        $sesi = [];
        foreach ($sesi_data as $s) {
            $sesi[$s->id_sesi] = $s->nama_sesi;
        }

        $jadwal_data = $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt);
        $mapel_jadwal = [];
        foreach ($jadwal_data as $j) {
            $mapel_jadwal[$j->id_jadwal] = $j->bank_kode . " (" . $j->nama_mapel . ")";
        }

        $data = [
            'user' => $user,
            'judul' => 'Cetak Daftar Kehadiran',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kelas' => $kelas,
            'ruang' => $ruang,
            'sesi' => $sesi,
            'mapel_jadwal' => $mapel_jadwal,
            'kop' => $this->cbtModel->getSettingKopAbsensi(),
            'ujian' => $ujian
        ];

        return view('cbt/cetak/absen', $data);
    }

    /**
     * Berita Acara page
     */
    public function beritaAcara()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return redirect()->back()->with('error', 'Tahun Pelajaran atau Semester belum aktif.');
        }

        // Get all jadwal for the active TP/SMT
        $jadwal = $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt);

        // Get sesi dropdown
        $sesiData = $this->cbtModel->getAllSesi();
        $sesi = ['' => 'Pilih Sesi'];
        foreach ($sesiData as $s) {
            $sesi[$s->id_sesi] = $s->nama_sesi;
        }

        // Format Kelas dropdown
        $kelasData = $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $kelas = ['' => 'Pilih Kelas'];
        foreach ($kelasData as $k) {
            $kelas[$k->id_kelas] = $k->nama_kelas;
        }

        // Format Ruang dropdown
        $ruangData = $this->cbtModel->getAllRuang();
        $ruang = ['' => 'Pilih Ruang'];
        foreach ($ruangData as $r) {
            $ruang[$r->id_ruang] = $r->nama_ruang;
        }

        $data = [
            'user' => $user,
            'judul' => 'Cetak Berita Acara',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kelas' => $kelas,
            'ruang' => $ruang,
            'sesi' => $sesi,
            'kop' => $this->cbtModel->getSettingKopBeritaAcara(),
            'mapel_jadwal' => [],
            'jadwal' => $jadwal,
            'guru' => null,
            'panitia' => null,
        ];

        return view('cbt/cetak/beritaacara', $data);
    }

    /**
     * Pakta Integritas page
     */
    public function paktaIntegritas()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Cetak Pakta Integritas',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            // Only need minimal data or specific supervisor list?
            // CI3 uses guru list mostly
            'pengawas' => $this->masterModel->getAllGuru(),
            'nip' => '-',
            'jabatan' => 'Administrator'
        ];

        return view('cbt/cetak/paktaintegritas', $data);
    }

    /**
     * Peserta Ujian page
     */
    public function pesertaUjian($mode = null)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return redirect()->back()->with('error', 'Tahun Pelajaran atau Semester belum aktif.');
        }

        $jenis_ujian = $this->cbtModel->getAllJenisUjian();
        $ujian = [];
        foreach ($jenis_ujian as $j) {
            $ujian[$j->id_jenis] = $j->nama_jenis . " (" . $j->kode_jenis . ")";
        }

        $data = [
            'user' => $user,
            'judul' => 'Cetak Daftar Peserta',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kelass' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'ruangs' => $this->cbtModel->getAllRuang(),
            'sesis' => $this->cbtModel->getAllKodeSesi(),
            'kop' => $setting,
            'mode' => $mode,
            'ujian' => $ujian
        ];

        if ($mode == '1' || $mode == null) {
            $data['siswa'] = $this->cbtModel->getAllPesertaByRuang($tp->id_tp, $smt->id_smt);
        } else {
            $data['siswa'] = $this->cbtModel->getAllPesertaByKelas($tp->id_tp, $smt->id_smt);
        }

        return view('cbt/cetak/pesertaujian', $data);
    }

    /**
     * Pengawas schedule page
     */
    public function pengawas()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return redirect()->back()->with('error', 'Tahun Pelajaran atau Semester belum aktif.');
        }

        $jenis_ujian = $this->cbtModel->getAllJenisUjian();
        $jenis = [];
        foreach ($jenis_ujian as $j) {
            $jenis[$j->id_jenis] = $j->nama_jenis . " (" . $j->kode_jenis . ")";
        }

        $filter_selected = $this->request->getGet('filter') ?? '0';
        $jenis_selected = $this->request->getGet('jenis') ?? '';
        $dari_selected = $this->request->getGet('dari') ?? '';
        $sampai_selected = $this->request->getGet('sampai') ?? '';

        $ruang = [];
        // If filter logic is needed, add here. currently avoiding 500 error.

        $data = [
            'user' => $user,
            'judul' => 'Jadwal Pengawas',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'jenis' => $jenis,
            'filter' => ['0' => 'Semua', '1' => 'Tanggal'],
            'jenis_selected' => $jenis_selected,
            'filter_selected' => $filter_selected,
            'dari_selected' => $dari_selected,
            'sampai_selected' => $sampai_selected,
            'ruang' => $ruang,
            'jenis_ujian' => $jenis_selected != '' ? $this->cbtModel->getJenisUjianById($jenis_selected) : null
        ];

        return view('cbt/cetak/pengawas', $data);
    }

    /**
     * Save KOP for absen
     */
    public function saveKop()
    {
        // Implementation
    }

    /**
     * Save kartu meja
     */
    public function saveKartuMeja()
    {
        // Implementation
    }

    /**
     * Get jadwal by mapel (AJAX)
     */
    public function getJadwalMapel()
    {
        // Implementation
    }

    /**
     * Get all jadwal (AJAX)
     */
    public function getAllJadwal()
    {
        // Implementation
    }

    /**
     * Get data by kelas (AJAX)
     */
    public function getKelas()
    {
        try {
            $kelas_id = $this->request->getGet('kelas');
            $sesi_id = $this->request->getGet('sesi');
            $jadwal_id = $this->request->getGet('mapel');

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt)
                throw new \Exception("Tahun/Semester aktif tidak ditemukan");

            // Info
            $info = [
                'kelas' => $this->masterModel->getKelasById($kelas_id),
                'sesi' => $this->cbtModel->getSesiById($sesi_id),
                'mapel' => $jadwal_id
            ];

            // Siswa by Kelas
            // Note: getSiswaByKelas returns students. We need to filter by Schedule (Jadwal) classes?
            // Actually getSiswaByKelas fetches all students in class. 
            // We should ensure they are mapped to sessions/rooms if needed, but for "By Kelas" report, usually just class members.
            // However, JS filters using `s.id_kelas`, `s.sesi_id` etc.
            // So we need students with their session/room info attached.
            // getSiswaByKelas method in CbtModel joins cbt_sesi_siswa so it has ruang_id, sesi_id.
            $siswa = $this->cbtModel->getSiswaByKelas($tp->id_tp, $smt->id_smt, $kelas_id);

            // Jadwal & All Tgl
            $all_tgl = [];
            $kelas_ids = [];
            $jadwal = $this->cbtModel->getJadwal(intval($jadwal_id));
            if ($jadwal) {
                $jadwal->bank_kelas = $this->cbtModel->maybeUnserialize($jadwal->bank_kelas);
                if (is_array($jadwal->bank_kelas)) {
                    foreach ($jadwal->bank_kelas as $bk) {
                        $kelas_ids[] = is_array($bk) ? $bk['kelas_id'] : $bk;
                    }
                } else {
                    $kelas_ids[] = $jadwal->bank_kelas;
                }

                // Sanitize $kelas_ids - ensure no objects remain
                $kelas_ids = array_map(function ($k) {
                    if (is_object($k))
                        return $k->kelas_id ?? null;
                    if (is_array($k))
                        return $k['kelas_id'] ?? null;
                    return $k;
                }, $kelas_ids);
                $kelas_ids = array_filter($kelas_ids);

                $key = $jadwal->tgl_mulai;
                $all_tgl[$key] = [$jadwal];
            }

            // All Ruang (Sesi Definitions)
            $all_ruang = $this->cbtModel->getDistinctSesi($tp->id_tp, $smt->id_smt, $kelas_ids);

            // All Pengawas
            $all_pengawas = $this->cbtModel->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal_id);

            return $this->jsonResponse([
                'info' => $info,
                'siswa' => $siswa,
                'all_ruang' => $all_ruang,
                'all_tgl' => $all_tgl,
                'all_pengawas' => $all_pengawas,
                'kop' => ['kop_absensi' => $this->cbtModel->getSettingKopAbsensi()]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    /**
     * Get data by ruang (AJAX)
     */
    public function getRuang()
    {
        try {
            $ruang_id = $this->request->getGet('ruang');
            $sesi_id = $this->request->getGet('sesi');
            $jadwal_id = $this->request->getGet('mapel');

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt)
                throw new \Exception("Tahun/Semester aktif tidak ditemukan");

            // Info
            $info = [
                'ruang' => $this->cbtModel->getRuangById($ruang_id),
                'sesi' => $this->cbtModel->getSesiById($sesi_id),
                'mapel' => $jadwal_id
            ];

            // Siswa by Ruang
            $siswa = $this->cbtModel->getSiswaByRuangId($tp->id_tp, $smt->id_smt, (int) $ruang_id);

            // Jadwal & All Tgl
            $all_tgl = [];
            $kelas_ids = [];
            $jadwal = $this->cbtModel->getJadwal(intval($jadwal_id));
            if ($jadwal) {
                $jadwal->bank_kelas = $this->cbtModel->maybeUnserialize($jadwal->bank_kelas);
                // Normalize bank_kelas ID array
                if (is_array($jadwal->bank_kelas)) {
                    // It might be array of objects or IDs or assoc arrays
                    $tmp = [];
                    foreach ($jadwal->bank_kelas as $bk) {
                        if (is_array($bk) && isset($bk['kelas_id']))
                            $tmp[] = $bk['kelas_id'];
                        elseif (is_object($bk) && isset($bk->kelas_id))
                            $tmp[] = $bk->kelas_id;
                        else
                            $tmp[] = $bk;
                    }
                    $kelas_ids = $tmp;
                } else {
                    $kelas_ids[] = $jadwal->bank_kelas;
                }

                $key = $jadwal->tgl_mulai;
                $all_tgl[$key] = [$jadwal];
            }

            // All Ruang (Session/Room/Class constraints for validation in JS)
            $all_ruang = $this->cbtModel->getDistinctSesi($tp->id_tp, $smt->id_smt, $kelas_ids);

            // All Pengawas
            $all_pengawas = $this->cbtModel->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal_id);

            return $this->jsonResponse([
                'info' => $info,
                'siswa' => $siswa,
                'all_ruang' => $all_ruang,
                'all_tgl' => $all_tgl,
                'all_pengawas' => $all_pengawas,
                'kop' => ['kop_absensi' => $this->cbtModel->getSettingKopAbsensi()]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage());
        }
    }

    /**
     * Save KOP berita
     */
    public function saveKopBerita()
    {
        // Implementation
    }

    protected function jsonResponse($data, $msg = '', $extra = [])
    {
        $response = [
            'status' => $data ? true : false,
            'message' => $msg,
            'data' => $data
        ];
        return $this->response->setJSON(array_merge($response, $extra));
    }
}
