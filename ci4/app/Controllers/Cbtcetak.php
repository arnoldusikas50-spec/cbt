<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;

/**
 * Cbtcetak Controller - Migrated from CI3
 * Handles printing CBT cards, attendance, reports
 */
class Cbtcetak extends BaseController
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

    /**
     * Main index page - Print options
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->inGroup('panitia')) {
            return $this->showError('Akses tidak diizinkan', 403);
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Cetak Data Penilaian',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kop' => $this->cbtModel->getSettingKopAbsensi(),
            'is_admin' => $this->isAdmin() || $this->inGroup('panitia'),
        ];

        if (!$this->isAdmin() && $this->inGroup('guru')) {
            $data['guru'] = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/data')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Kartu Peserta page
     */
    public function kartuPeserta()
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
            'judul' => 'Cetak Kartu Peserta',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kartu' => $this->cbtModel->getSettingKartu(),
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'ruang' => $this->cbtModel->getAllRuang(),
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/kartu')
            . view('_templates/dashboard/_footer');
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
            'kartu_meja' => $this->cbtModel->getSettingKartu(),
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'ruang' => $this->cbtModel->getAllRuang(),
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/kartumeja')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Upload file/logo
     */
    public function uploadFile($logo = 'logo')
    {
        try {
            $file = $this->request->getFile('logo');

            if (!$file || !$file->isValid()) {
                return $this->respond(false, 'File tidak valid');
            }

            $allowedTypes = ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return $this->respond(false, 'Tipe file tidak diizinkan');
            }

            $newName = $logo . '.' . $file->getExtension();
            $uploadPath = FCPATH . 'uploads/settings/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if ($file->move($uploadPath, $newName, true)) {
                return $this->respond(true, 'File berhasil diupload', [
                    'src' => base_url('uploads/settings/' . $newName),
                    'filename' => pathinfo($newName, PATHINFO_FILENAME),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize()
                ]);
            }

            return $this->respond(false, 'Gagal mengupload file');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
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
                return $this->respond(true, 'File berhasil dihapus');
            }

            return $this->respond(false, 'File tidak ditemukan');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
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

            $db = \Config\Database::connect();
            $update = $db->table('cbt_kop_kartu')->replace($insert);

            return $this->respond($update, $update ? 'Kartu berhasil disimpan' : 'Gagal menyimpan kartu');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Get siswa by kelas (AJAX)
     */
    public function getSiswaKelas()
    {
        try {
            $sesi = $this->request->getGet('sesi');
            $jadwal = $this->request->getGet('jadwal');
            $kelas = $this->request->getGet('kelas');

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $db = \Config\Database::connect();

            // Get siswa data
            $siswas = $this->cbtModel->getRuangSiswaByKelas($tp->id_tp, $smt->id_smt, $kelas, $sesi);

            foreach ($siswas as $siswa) {
                $siswa->foto = file_exists(FCPATH . ($siswa->foto ?? '')) ? $siswa->foto : 'assets/img/siswa.png';
            }

            $data = [
                'siswa' => $siswas,
                'info' => [
                    'kelas' => $kelas != 'all' ? $this->masterModel->getKelasById($kelas) : null,
                    'sesi' => $sesi ? $this->cbtModel->getSesiById($sesi) : null,
                    'jadwal' => $jadwal ? $this->cbtModel->getJadwalById($jadwal) : null,
                ]
            ];

            return $this->respond(true, '', $data);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Get siswa by ruang (AJAX)
     */
    public function getSiswaRuang()
    {
        try {
            $ruang = $this->request->getGet('ruang');
            $sesi = $this->request->getGet('sesi');
            $jadwal = $this->request->getGet('jadwal');

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $s = ($sesi == "null" || empty($sesi)) ? null : $sesi;

            $siswas = $this->cbtModel->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $s);

            foreach ($siswas as $siswa) {
                $siswa->foto = file_exists(FCPATH . ($siswa->foto ?? '')) ? $siswa->foto : 'assets/img/siswa.png';
            }

            $data = [
                'siswa' => $siswas,
                'info' => [
                    'ruang' => $this->cbtModel->getRuangById($ruang),
                    'sesi' => $s ? $this->cbtModel->getSesiById($s) : null,
                    'jadwal' => $jadwal ? $this->cbtModel->getJadwalById($jadwal) : null,
                ]
            ];

            return $this->respond(true, '', $data);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Save berita acara
     */
    public function saveBeritaAcara()
    {
        try {
            $input = $this->request->getPost();
            $update = $this->cbtModel->saveBeritaAcara($input);
            return $this->respond($update, $update ? 'Berita acara berhasil disimpan' : 'Gagal menyimpan');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Save pakta integritas
     */
    public function savePaktaIntegritas()
    {
        try {
            $input = $this->request->getPost();
            $update = $this->cbtModel->savePaktaIntegritas($input);
            return $this->respond($update, $update ? 'Pakta integritas berhasil disimpan' : 'Gagal menyimpan');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
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
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'ruang' => $this->cbtModel->getAllRuang(),
            'sesi' => $this->cbtModel->getAllSesi(),
            'kop' => $this->cbtModel->getSettingKopAbsensi(),
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/absen')
            . view('_templates/dashboard/_footer');
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
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'ruang' => $this->cbtModel->getAllRuang(),
            'sesi' => $this->cbtModel->getAllSesi(),
            'kop' => $this->cbtModel->getSettingKopBeritaAcara(),
            'saved_ba' => $this->cbtModel->getAllBeritaAcara($tp->id_tp, $smt->id_smt),
        ];

        if ($this->isAdmin()) {
            $data['profile'] = (object) [
                'nama_lengkap' => ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''),
                'nip' => '-',
                'jabatan' => 'Administrator'
            ];
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/beritaacara')
            . view('_templates/dashboard/_footer');
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
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'ruang' => $this->cbtModel->getAllRuang(),
            'sesi' => $this->cbtModel->getAllSesi(),
            'kop' => $this->cbtModel->getSettingKopBeritaAcara(),
            'saved_pi' => $this->cbtModel->getAllPaktaIntegritas($tp->id_tp, $smt->id_smt),
        ];

        if ($this->isAdmin()) {
            $data['profile'] = (object) [
                'nama_lengkap' => ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''),
                'nip' => '-',
                'jabatan' => 'Administrator'
            ];
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/paktaintegritas')
            . view('_templates/dashboard/_footer');
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
        ];

        if ($mode == '1' || $mode == null) {
            $data['siswa'] = $this->cbtModel->getAllPesertaByRuang($tp->id_tp, $smt->id_smt);
        } else {
            $data['siswa'] = $this->cbtModel->getAllPesertaByKelas($tp->id_tp, $smt->id_smt);
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/pesertaujian')
            . view('_templates/dashboard/_footer');
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

        $jenis_selected = $this->request->getGet('jenis');

        $data = [
            'user' => $user,
            'judul' => 'Jadwal Pengawas',
            'subjudul' => 'Cetak Jadwal Pengawas',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'jenis_selected' => $jenis_selected,
            'filter' => ['0' => 'Semua', '1' => 'Tanggal'],
            'gurus' => $this->masterModel->getAllGuru(),
        ];

        // Get distinct jenis from jadwal
        $id_jenis = $this->cbtModel->getDistinctJenisJadwal($tp->id_tp, $smt->id_smt);
        $ids = [];
        if ($id_jenis && count($id_jenis) > 0) {
            foreach ($id_jenis as $jenis) {
                $ids[] = $jenis->id_jenis;
            }
        }

        if (count($ids) > 0) {
            $data['jenis'] = $this->cbtModel->getAllJenisUjianByArrJenis($ids);
        } else {
            $data['jenis'] = ['' => 'belum ada jadwal ujian'];
        }

        // Get jadwals if jenis selected
        $jadwals = [];
        if ($jenis_selected) {
            $jadwals = $this->cbtModel->getJadwalByJenis($tp->id_tp, $smt->id_smt, $jenis_selected);
        }
        $data['jadwals'] = $jadwals;
        $data['pengawas'] = $jenis_selected ? $this->cbtModel->getAllPengawas($tp->id_tp, $smt->id_smt) : [];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/cetak/pengawas')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Save KOP for absen
     */
    public function saveKop()
    {
        try {
            $insert = [
                'id_kop' => 123456,
                'header_1' => $this->request->getPost('header_1'),
                'header_2' => $this->request->getPost('header_2'),
                'header_3' => $this->request->getPost('header_3'),
                'header_4' => $this->request->getPost('header_4'),
                'proktor' => $this->request->getPost('proktor'),
                'pengawas_1' => $this->request->getPost('pengawas_1'),
                'pengawas_2' => $this->request->getPost('pengawas_2'),
            ];

            $db = \Config\Database::connect();
            $update = $db->table('cbt_kop_absensi')->replace($insert);

            return $this->respond($update, $update ? 'KOP berhasil disimpan' : 'Gagal menyimpan');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Save KOP berita
     */
    public function saveKopBerita()
    {
        try {
            $insert = [
                'id_kop' => 123456,
                'header_1' => $this->request->getPost('header_1'),
                'header_2' => $this->request->getPost('header_2'),
                'header_3' => $this->request->getPost('header_3'),
                'header_4' => $this->request->getPost('header_4'),
            ];

            $db = \Config\Database::connect();
            $update = $db->table('cbt_kop_berita')->replace($insert);

            return $this->respond($update, $update ? 'KOP berhasil disimpan' : 'Gagal menyimpan');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }
}
