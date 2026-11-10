<?php

namespace App\Controllers;

use App\Models\KurikulumMerdekaModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;

/**
 * KurikulumMerdekaAssessmentController - Controller untuk Penilaian Kurikulum Merdeka
 * Sesuai Permendikbudristek No. 53/2023 tentang Standar Penilaian Pendidikan
 */
class KurikulumMerdekaAssessment extends BaseController
{
    protected $model;
    protected $dashboard;
    protected $dropdown;
    
    public function __construct()
    {
        $this->model = new KurikulumMerdekaModel();
        $this->dashboard = new DashboardModel();
        $this->dropdown = new DropdownModel();
    }
    
    /**
     * Dashboard Penilaian Kurikulum Merdeka
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        
        $user = $this->getUser();
        
        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
            return $this->showError('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini', 403);
        }
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        $data = [
            'user' => $user,
            'judul' => 'Penilaian Kurikulum Merdeka',
            'subjudul' => 'Standar Penilaian Sesuai Permendikbudristek No. 53/2023',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
        ];
        
        // Ambil statistik penilaian
        $data['statistik'] = $this->model->getStatistikPenilaian($tp->id_tp, $smt->id_smt);
        $data['komponen_penilaian'] = $this->model->getKomponenPenilaian();
        
        return $this->renderView('kurikulum_merdeka/dashboard', $data);
    }
    
    /**
     * Input Penilaian Otentik - Sikap, Keterampilan, Pengetahuan
     */
    public function inputPenilaian()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        
        $user = $this->getUser();
        
        if (!$this->inGroup('guru')) {
            return $this->showError('Hanya Guru yang dapat melakukan input penilaian', 403);
        }
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        $idKelas = $this->request->getGet('id_kelas');
        $idMapel = $this->request->getGet('id_mapel');
        $idSiswa = $this->request->getGet('id_siswa');
        
        $data = [
            'user' => $user,
            'judul' => 'Input Penilaian Otentik',
            'subjudul' => 'Penilaian Sikap, Keterampilan, dan Pengetahuan',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'id_kelas_selected' => $idKelas,
            'id_mapel_selected' => $idMapel,
            'id_siswa_selected' => $idSiswa,
        ];
        
        // Ambil data kelas yang diajar oleh guru
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        $data['kelas'] = $this->model->getKelasByGuru($guru->id_guru, $tp->id_tp, $smt->id_smt);
        
        if ($idKelas && $idMapel) {
            // Ambil siswa dalam kelas
            $data['siswa'] = $this->model->getSiswaByKelas($idKelas, $tp->id_tp, $smt->id_smt);
            // Ambil komponen penilaian untuk mapel ini
            $data['komponen'] = $this->model->getKomponenByMapel($idMapel);
            // Ambil data penilaian yang sudah ada
            $data['nilai_existing'] = $this->model->getNilaiByKelasMapel($idKelas, $idMapel, $tp->id_tp, $smt->id_smt);
        }
        
        return $this->renderView('kurikulum_merdeka/input_penilaian', $data);
    }
    
    /**
     * Simpan Penilaian Otentik
     */
    public function simpanPenilaian()
    {
        if (!$this->isLoggedIn() || !$this->inGroup('guru')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        $data = $this->request->getPost();
        
        // Validasi data
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_siswa' => 'required|numeric',
            'id_mapel' => 'required|numeric',
            'id_komponen' => 'required|numeric',
            'nilai' => 'required|decimal',
            'jenis_penilaian' => 'required|in_list[Harian,Tengah_Semester,Akhir_Semester,Ujian_Sekolah]',
        ]);
        
        if (!$validation->run($data)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ]);
        }
        
        // Tentukan predikat berdasarkan nilai dan jenis komponen
        $komponen = $this->model->getKomponenById($data['id_komponen']);
        $predikat = $this->tentukanPredikat($data['nilai'], $komponen->jenis_penilaian);
        
        // Siapkan data untuk disimpan
        $nilaiData = [
            'siswa_id' => $data['id_siswa'],
            'mapel_id' => $data['id_mapel'],
            'komponen_id' => $data['id_komponen'],
            'nilai' => $data['nilai'],
            'predikat' => $predikat,
            'deskripsi' => $data['deskripsi'] ?? null,
            'jenis_penilaian' => $data['jenis_penilaian'],
            'tanggal_penilaian' => date('Y-m-d'),
            'guru_id' => $this->dashboard->getDataGuruByUserId($this->getUser()->id, $tp->id_tp, $smt->id_smt)->id_guru,
            'semester' => $smt->id_smt,
            'tahun_pelajaran' => $tp->tahun,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        // Simpan atau update nilai
        $result = $this->model->simpanNilaiKomponen($nilaiData);
        
        if ($result) {
            // Hitung ulang nilai akhir
            $this->hitungNilaiAkhir($data['id_siswa'], $data['id_mapel'], $tp->id_tp, $smt->id_smt);
            
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Nilai berhasil disimpan',
                'predikat' => $predikat
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gagal menyimpan nilai'
            ]);
        }
    }
    
    /**
     * Portofolio Digital Siswa dengan P5
     */
    public function portofolioDigital()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        
        $user = $this->getUser();
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        $idSiswa = $this->request->getGet('id_siswa');
        
        $data = [
            'user' => $user,
            'judul' => 'Portofolio Digital',
            'subjudul' => 'P5 - Proyek Penguatan Profil Pelajar Pancasila',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'id_siswa_selected' => $idSiswa,
        ];
        
        // Ambil data siswa jika dipilih
        if ($idSiswa) {
            $data['siswa'] = $this->model->getSiswaById($idSiswa);
            $data['portofolio'] = $this->model->getPortofolioBySiswa($idSiswa, $tp->id_tp, $smt->id_smt);
            $data['p5_report'] = $this->model->getP5ReportBySiswa($idSiswa, $tp->id_tp, $smt->id_smt);
        }
        
        // Ambil daftar siswa untuk guru
        if ($this->inGroup('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['siswa_list'] = $this->model->getSiswaByGuru($guru->id_guru, $tp->id_tp, $smt->id_smt);
        } elseif ($this->inGroup('siswa')) {
            // Siswa lihat portofolio sendiri
            $siswa = $this->model->getSiswaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($siswa) {
                return redirect()->to('kurikulum_merdeka/portofolio_digital?id_siswa=' . $siswa->id_siswa);
            }
        }
        
        return $this->renderView('kurikulum_merdeka/portofolio_digital', $data);
    }
    
    /**
     * Simpan Portofolio Digital
     */
    public function simpanPortofolio()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        $data = $this->request->getPost();
        
        // Handle upload file
        $buktiPembelajaran = [];
        $files = $this->request->getFiles();
        
        if (isset($files['bukti_pembelajaran'])) {
            foreach ($files['bukti_pembelajaran'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->randomName();
                    $file->move(ROOTPATH . 'public/uploads/portofolio/', $newName);
                    $buktiPembelajaran[] = [
                        'type' => $file->getClientMimeType(),
                        'filename' => $newName,
                        'original_name' => $file->getClientName(),
                        'size' => $file->getSize(),
                    ];
                }
            }
        }
        
        $portofolioData = [
            'siswa_id' => $data['id_siswa'],
            'elemen_p5' => $data['elemen_p5'],
            'sub_elemen' => $data['sub_elemen'] ?? null,
            'capaian_pembelajaran' => $data['capaian_pembelajaran'],
            'bukti_pembelajaran' => json_encode($buktiPembelajaran),
            'jenis_bukti' => $data['jenis_bukti'],
            'tanggal_kegiatan' => $data['tanggal_kegiatan'],
            'refleksi_siswa' => $data['refleksi_siswa'] ?? null,
            'feedback_guru' => $data['feedback_guru'] ?? null,
            'nilai_otentik' => $data['nilai_otentik'] ?? null,
            'rubrik_penilaian' => json_encode($this->buatRubrikPenilaian($data['elemen_p5'])),
            'status_validasi' => 'Draft',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $result = $this->model->simpanPortofolio($portofolioData);
        
        if ($result) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Portofolio berhasil disimpan'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gagal menyimpan portofolio'
            ]);
        }
    }
    
    /**
     * Pelaporan P5 ke Dapodik dan EMIS
     */
    public function laporanP5()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        
        $user = $this->getUser();
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        $data = [
            'user' => $user,
            'judul' => 'Laporan P5',
            'subjudul' => 'Proyek Penguatan Profil Pelajar Pancasila',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
        ];
        
        // Ambil data P5 yang belum disync
        $data['p5_belum_sync'] = $this->model->getP5BelumSync($tp->id_tp, $smt->id_smt);
        $data['p5_sudah_sync'] = $this->model->getP5SudahSync($tp->id_tp, $smt->id_smt);
        
        return $this->renderView('kurikulum_merdeka/laporan_p5', $data);
    }
    
    /**
     * Sync P5 ke Dapodik dan EMIS
     */
    public function syncP5()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        
        $idReport = $this->request->getPost('id_report');
        
        try {
            // Ambil data P5
            $p5Data = $this->model->getP5ReportById($idReport);
            
            // Simulasi sync ke Dapodik (API endpoint)
            $dapodikResponse = $this->syncToDapodik($p5Data);
            
            // Simulasi sync ke EMIS
            $emisResponse = $this->syncToEMIS($p5Data);
            
            if ($dapodikResponse['status'] && $emisResponse['status']) {
                // Update status sync
                $this->model->updateSyncStatus($idReport, 'Sudah_Sync', [
                    'link_dapodik' => $dapodikResponse['link'],
                    'link_emis' => $emisResponse['link'],
                    'last_sync' => date('Y-m-d H:i:s')
                ]);
                
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Sync berhasil ke Dapodik dan EMIS'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal sync: ' . ($dapodikResponse['message'] ?? $emisResponse['message'])
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Audit Keamanan Data
     */
    public function auditKeamanan()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return redirect()->to('auth');
        }
        
        $user = $this->getUser();
        
        $data = [
            'user' => $user,
            'judul' => 'Audit Keamanan Data',
            'subjudul' => 'SNIST & GDPR-like Compliance',
            'setting' => $this->dashboard->getSetting(),
        ];
        
        // Ambil log audit
        $data['audit_log'] = $this->model->getAuditLog(30); // 30 hari terakhir
        $data['kepatuhan_stats'] = $this->model->getKepatuhanStats();
        $data['potensi_pelanggaran'] = $this->model->getPotensiPelanggaran();
        
        return $this->renderView('kurikulum_merdeka/audit_keamanan', $data);
    }
    
    /**
     * Helper: Tentukan Predikat berdasarkan nilai dan jenis penilaian
     */
    private function tentukanPredikat($nilai, $jenisPenilaian)
    {
        if ($jenisPenilaian == 'Pengetahuan' || $jenisPenilaian == 'Sikap') {
            if ($nilai >= 90) return 'A';
            if ($nilai >= 80) return 'B';
            if ($nilai >= 70) return 'C';
            return 'D';
        } elseif ($jenisPenilaian == 'Keterampilan') {
            if ($nilai >= 90) return 'SB'; // Sangat Baik
            if ($nilai >= 80) return 'B';  // Baik
            if ($nilai >= 70) return 'C';  // Cukup
            return 'K'; // Kurang
        }
        return null;
    }
    
    /**
     * Helper: Hitung Nilai Akhir berdasarkan bobot komponen
     */
    private function hitungNilaiAkhir($idSiswa, $idMapel, $idTp, $idSmt)
    {
        $this->model->hitungDanSimpanNilaiAkhir($idSiswa, $idMapel, $idTp, $idSmt);
    }
    
    /**
     * Helper: Buat Rubrik Penilaian untuk P5
     */
    private function buatRubrikPenilaian($elemenP5)
    {
        // Rubrik standar untuk setiap elemen P5
        $rubrik = [
            'Beriman dan Bertaqwa kepada Tuhan YME' => [
                'indikator' => ['Menjalankan ibadah', 'Menghormati perbedaan', 'Bersyukur'],
                'skor' => [4, 3, 2, 1]
            ],
            'Berkebinekaan Global' => [
                'indikator' => ['Menghargai budaya', 'Berkomunikasi efektif', 'Bekerja sama'],
                'skor' => [4, 3, 2, 1]
            ],
            'Bergotong Royong' => [
                'indikator' => ['Partisipasi aktif', 'Menolong sesama', 'Tanggung jawab'],
                'skor' => [4, 3, 2, 1]
            ],
            'Kreatif' => [
                'indikator' => ['Ide orisinal', 'Solusi inovatif', 'Eksplorasi'],
                'skor' => [4, 3, 2, 1]
            ],
            'Mandiri' => [
                'indikator' => ['Inisiatif', 'Manajemen waktu', 'Refleksi diri'],
                'skor' => [4, 3, 2, 1]
            ],
            'Bernalar Kritis' => [
                'indikator' => ['Analisis logis', 'Pertanyaan kritis', 'Evaluasi'],
                'skor' => [4, 3, 2, 1]
            ]
        ];
        
        return $rubrik[$elemenP5] ?? null;
    }
    
    /**
     * Simulasi Sync ke Dapodik
     */
    private function syncToDapodik($p5Data)
    {
        // Simulasi API call ke Dapodik
        // Dalam implementasi nyata, ini akan mengirim data ke API Dapodik
        return [
            'status' => true,
            'link' => 'https://dapodik.kemdikbud.go.id/p5/' . uniqid(),
            'message' => 'Berhasil sync ke Dapodik'
        ];
    }
    
    /**
     * Simulasi Sync ke EMIS
     */
    private function syncToEMIS($p5Data)
    {
        // Simulasi API call ke EMIS
        // Dalam implementasi nyata, ini akan mengirim data ke API EMIS
        return [
            'status' => true,
            'link' => 'https://emis.kemdikbud.go.id/p5/' . uniqid(),
            'message' => 'Berhasil sync ke EMIS'
        ];
    }
}
