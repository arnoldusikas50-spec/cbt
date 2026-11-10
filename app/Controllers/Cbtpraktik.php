<?php

namespace App\Controllers;

use App\Models\CbtPraktikModel;
use App\Models\CbtPraktikRubrikModel;
use App\Models\CbtPraktikRubrikLevelModel;
use App\Models\CbtPraktikHasilModel;
use App\Models\CbtPraktikLogModel;
use App\Models\CbtPraktikFileModel;
use App\Models\CbtPraktikTemplateRubrikModel;
use App\Models\CbtPraktikNilaiAuditModel;
use App\Models\CbtPraktikMultiAssessorModel;
use App\Models\CbtPraktikAnalisisModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\TujuanPembelajaranModel;
use App\Models\NilaiModel;
use App\Models\CbtPraktikSelfAssessmentModel;
use App\Models\CbtPraktikObservasiModel;
use App\Models\CbtPraktikRemedialModel;
use App\Models\CbtPraktikP5Model;
use App\Models\CbtPraktikP5NilaiModel;
use App\Libraries\PraktikGrading;
use App\Libraries\PraktikReportGenerator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Cbtpraktik extends BaseController
{
    protected $praktikModel;
    protected $rubrikModel;
    protected $rubrikLevelModel;
    protected $hasilModel;
    protected $logModel;
    protected $fileModel;
    protected $templateModel;
    protected $auditModel;
    protected $multiAssessorModel;
    protected $analisisModel;
    protected $tpModel;
    protected $nilaiModel;
    protected $master;
    protected $dashboard;
    protected $grading;
    protected $reportGenerator;
    protected $selfAssessmentModel;
    protected $observasiModel;
    protected $remedialModel;
    protected $p5Model;
    protected $p5NilaiModel;

    public function __construct()
    {
        $this->praktikModel = new CbtPraktikModel();
        $this->praktikModel->initialize();
        $this->rubrikModel = new CbtPraktikRubrikModel();
        $this->rubrikLevelModel = new CbtPraktikRubrikLevelModel();
        $this->hasilModel = new CbtPraktikHasilModel();
        $this->logModel = new CbtPraktikLogModel();
        $this->fileModel = new CbtPraktikFileModel();
        $this->templateModel = new CbtPraktikTemplateRubrikModel();
        $this->auditModel = new CbtPraktikNilaiAuditModel();
        $this->multiAssessorModel = new CbtPraktikMultiAssessorModel();
        $this->analisisModel = new CbtPraktikAnalisisModel();
        $this->tpModel = new TujuanPembelajaranModel();
        $this->nilaiModel = new NilaiModel();
        $this->master = new MasterModel();
        $this->dashboard = new DashboardModel();
        $this->grading = new PraktikGrading();
        $this->reportGenerator = new PraktikReportGenerator();
        $this->selfAssessmentModel = new CbtPraktikSelfAssessmentModel();
        $this->observasiModel = new CbtPraktikObservasiModel();
        $this->remedialModel = new CbtPraktikRemedialModel();
        $this->p5Model = new CbtPraktikP5Model();
        $this->p5NilaiModel = new CbtPraktikP5NilaiModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        try {
            $user = $this->authModel->user();
            $setting = $this->dashboard->getSetting();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $data = [
                'user' => $user,
                'judul' => 'Ujian Praktik',
                'subjudul' => 'Daftar Ujian Praktik',
                'setting' => $setting,
                'tp' => $this->dashboard->getTahun(),
                'tp_active' => $tp,
                'smt' => $this->dashboard->getSemester(),
                'smt_active' => $smt,
                'statistics' => $this->praktikModel->getStatistics($tp->id_tp ?? null, $smt->id_smt ?? null),
            ];

            $mapelsRaw = $this->master->getAllMapel();
            $mapels = [];
            foreach ($mapelsRaw as $m) {
                $mapels[$m->id_mapel] = $m->nama_mapel;
            }
            $data['mapels'] = $mapels;

            $kelasRaw = $this->master->getAllKelas($tp->id_tp ?? 0, $smt->id_smt ?? 0);
            $kelas = [];
            foreach ($kelasRaw as $k) {
                $kelas[$k->id_kelas] = $k;
            }
            $data['kelas'] = $kelas;

            if ($this->isAdmin() || $this->isPanitia()) {
                $data['praktik'] = $this->praktikModel->getDataPraktik();
                $idAuthor = $user->id;
            } else if ($this->inGroup('guru')) {
                $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp ?? 0, $smt->id_smt ?? 0);
                $data['praktik'] = $this->praktikModel->getDataPraktik($guru->id_guru ?? null);
                $idAuthor = $guru->id_guru ?? $user->id;
                $data['guru_id'] = $guru->id_guru ?? null;
            } else {
                return redirect()->to('dashboard');
            }

            $data['templates'] = $this->templateModel->getTemplates(
                $this->isAdmin() ? null : $idAuthor,
                null,
                true
            );

            return view('cbt/praktik/index', $data);
        } catch (\Throwable $e) {
            die("<pre>CRITICAL ERROR:\n" . $e->getMessage() . "\n" . $e->getFile() . " line " . $e->getLine() . "\n\n" . $e->getTraceAsString() . "</pre>");
        }
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Buat Ujian Praktik',
            'subjudul' => 'Formulir Pembuatan',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
        ];

        $mapelsRaw = $this->master->getAllMapel();
        $mapels = [];
        foreach ($mapelsRaw as $m) {
            $mapels[$m->id_mapel] = $m->nama_mapel;
        }
        $data['mapels'] = $mapels;

        $kelasRaw = $this->master->getAllKelas((int)$tp->id_tp, (int)$smt->id_smt);
        $data['kelas'] = $kelasRaw;

        // Get teacher data to pass to view for JS TP loading
        $idAuthor = null;
        if ($this->inGroup('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, (int)$tp->id_tp, (int)$smt->id_smt);
            $idAuthor = $guru->id_guru ?? null;
        }
        $data['idAuthor'] = $idAuthor;
        if (isset($guru)) {
            $data['guru'] = $guru;
        }

        $data['templates'] = $this->templateModel->getTemplates(
            $this->isAdmin() ? null : $user->id,
            null,
            true
        );

        $data['gurus'] = $this->master->getAllGuru();

        return view('cbt/praktik/add', $data);
    }

    public function getTPByMapel()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON([]);
        }

        $idMapel = $this->request->getGet('mapel_id');
        $idGuru = $this->request->getGet('guru_id');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        if (!$idMapel || !$tp || !$smt) {
            return $this->response->setJSON([]);
        }

        // If user is Admin or Panitia, ignore guru_id filter to show all TP for the mapel
        if ($this->isAdmin() || $this->isPanitia()) {
            $idGuru = null;
        }

        $tujuanList = [];
        if ($idGuru && is_numeric($idGuru) && $idGuru > 0) {
            $db = \Config\Database::connect();
            $guruExists = $db->table('master_guru')->where('id_guru', $idGuru)->countAllResults() > 0;
            
            if ($guruExists) {
                $tujuanList = $this->tpModel->getTujuanByGuru(
                    (int)$idGuru,
                    (int)$tp->id_tp,
                    (int)$smt->id_smt,
                    (int)$idMapel
                );
            } else {
                $tujuanList = $this->getTujuanByMapelOnly((int)$idMapel, (int)$tp->id_tp, (int)$smt->id_smt);
            }
        } else {
            $tujuanList = $this->getTujuanByMapelOnly((int)$idMapel, (int)$tp->id_tp, (int)$smt->id_smt);
        }

        return $this->response->setJSON($tujuanList);
    }

    /**
     * Debug endpoint for testing TP loading
     */
    public function testTP($mapelId = null)
    {
        $db = \Config\Database::connect();
        
        // Check table exists
        $tableExists = $db->tableExists('e_tujuan_pembelajaran');
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        // Count total TP
        $totalTP = $db->table('e_tujuan_pembelajaran')->countAllResults();
        
        // Count TP for this mapel
        $tpForMapel = 0;
        if ($mapelId) {
            $tpForMapel = $db->table('e_tujuan_pembelajaran')
                ->where('id_mapel', $mapelId)
                ->where('id_tp', $tp->id_tp)
                ->where('id_smt', $smt->id_smt)
                ->countAllResults();
        }
        
        // Sample data
        $sample = $db->table('e_tujuan_pembelajaran')
            ->limit(5)
            ->get()
            ->getResult();
        
        return $this->response->setJSON([
            'table_exists' => $tableExists,
            'table_name' => 'e_tujuan_pembelajaran',
            'total_tp' => $totalTP,
            'tp_for_mapel' => $tpForMapel,
            'mapel_id_requested' => $mapelId,
            'current_tp' => $tp->id_tp ?? null,
            'current_smt' => $smt->id_smt ?? null,
            'sample_data' => $sample
        ]);
    }

    /**
     * Get tujuan pembelajaran by mapel only (for admin use)
     */
    private function getTujuanByMapelOnly(int $idMapel, int $idTp, int $idSmt): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('e_tujuan_pembelajaran t');
        $builder->select('t.*, m.nama_mapel, m.kode as kode_mapel, g.nama_guru');
        $builder->join('master_mapel m', 'm.id_mapel = t.id_mapel');
        $builder->join('master_guru g', 'g.id_guru = t.id_guru', 'left');
        $builder->where('t.id_mapel', $idMapel);
        $builder->where('t.id_tp', $idTp);
        $builder->where('t.id_smt', $idSmt);
        
        // Only active TP
        if ($db->fieldExists('is_active', 'e_tujuan_pembelajaran')) {
            $builder->where('t.is_active', 1);
        }
        
        $builder->orderBy('t.tingkat', 'ASC');
        $builder->orderBy('t.kode', 'ASC');

        return $builder->get()->getResult();
    }

    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi telah berakhir');
        }

        $user = $this->authModel->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $kelas = $this->request->getPost('kelas');
        $kelasJson = is_array($kelas) ? json_encode($kelas) : json_encode([$kelas]);

        $idGuru = null;
        if ($this->inGroup('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $idGuru = $guru->id_guru ?? null;
        }

        $batasanFile = [
            'extensions' => $this->request->getPost('file_extensions') ?? ['pdf', 'doc', 'docx', 'zip'],
            'max_size' => $this->request->getPost('max_file_size') ?? 10485760,
        ];

        $assessors = $this->request->getPost('assessors');
        $assessorsJson = is_array($assessors) ? json_encode($assessors) : null;

        $tp_ids = $this->request->getPost('tujuan_pembelajaran');
        $tpJson = is_array($tp_ids) ? json_encode($tp_ids) : json_encode([$tp_ids]);

        $data = [
            'tp_id' => $tp->id_tp,
            'smt_id' => $smt->id_smt,
            'mapel_id' => $this->request->getPost('mapel'),
            'kelas_id' => $kelasJson,
            'judul' => $this->request->getPost('judul'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'instruksi' => $this->request->getPost('instruksi'),
            'tujuan_pembelajaran' => $tpJson,
            'jenis_praktik' => $this->request->getPost('jenis_praktik'),
            'format_pengumpulan' => $this->request->getPost('format_pengumpulan') ?? 'file',
            'batasan_file' => json_encode($batasanFile),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'durasi_menit' => $this->request->getPost('durasi_menit') ?? 0,
            'bobot' => $this->request->getPost('bobot') ?? 100,
            'kkm' => $this->request->getPost('kkm') ?? 70,
            'kktp_mode' => $this->request->getPost('kktp_mode') ?? 'kkm',
            'capaian_pembelajaran_id' => $this->request->getPost('capaian_pembelajaran_id') ?: null,
            'alur_tujuan_pembelajaran_id' => $this->request->getPost('alur_tujuan_pembelajaran_id') ?: null,
            'capaian_pembelajaran' => $this->request->getPost('capaian_pembelajaran'),
            'alur_tujuan_pembelajaran' => $this->request->getPost('alur_tujuan_pembelajaran'),
            'tipe_instruksi' => $this->request->getPost('tipe_instruksi') ?? 'tertutup',
            'panduan_kreativitas' => $this->request->getPost('panduan_kreativitas'),
            'contoh_hasil' => $this->request->getPost('contoh_hasil'),
            'sumber_belajar' => $this->request->getPost('sumber_belajar'),
            'izinkan_kolaborasi' => $this->request->getPost('izinkan_kolaborasi') ? 1 : 0,
            'aktifkan_penilaian_otomatis' => $this->request->getPost('aktifkan_otomatis') ? 1 : 0,
            'aktifkan_anti_cheat' => 0,
            'batas_tab_browser' => 0,
            'batas_copy_paste' => 0,
            'batas_screenshot' => 0,
            'wajib_webcam' => 0,
            'wajib_fullscreen' => 0,
            'created_by' => $idGuru,
            'assessors' => $assessorsJson,
            'template_id' => $this->request->getPost('template_rubrik') ?: null,
            'status' => $this->request->getPost('status') ?? 'draft',
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $praktikId = $this->praktikModel->insert($data);

        if (!$praktikId) {
            $error = $this->praktikModel->errors();
            $dbError = $this->praktikModel->db->error();
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gagal menyimpan ujian praktik: ' . json_encode($error) . ' - ' . ($dbError['message'] ?? '')
            ]);
        }

        $templateId = $this->request->getPost('template_rubrik');
        if ($templateId) {
            $this->templateModel->applyTemplate((int)$templateId, $praktikId);
        }

        $db->transComplete();

        return $this->response->setJSON([
            'status' => $db->transStatus(),
            'message' => $db->transStatus() ? 'Ujian praktik berhasil dibuat' : 'Terjadi kesalahan',
            'praktik_id' => $praktikId
        ]);
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$id);
        if (!$praktik) {
            return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses untuk mengedit ujian praktik ini');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Edit Ujian Praktik',
            'subjudul' => $praktik->judul,
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'praktik' => $praktik,
            'isEdit' => true,
        ];

        $mapelsRaw = $this->master->getAllMapel();
        $mapels = [];
        foreach ($mapelsRaw as $m) {
            $mapels[$m->id_mapel] = $m->nama_mapel;
        }
        $data['mapels'] = $mapels;

        $kelasRaw = $this->master->getAllKelas((int)$tp->id_tp, (int)$smt->id_smt);
        $data['kelas'] = $kelasRaw;

        // Get teacher data to pass to view for JS TP loading
        $idAuthor = null;
        if ($this->inGroup('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, (int)$tp->id_tp, (int)$smt->id_smt);
            $idAuthor = $guru->id_guru ?? null;
        }
        $data['idAuthor'] = $idAuthor;
        if (isset($guru)) {
            $data['guru'] = $guru;
        }

        $data['templates'] = $this->templateModel->getTemplates(
            $this->isAdmin() ? null : $user->id,
            null,
            true
        );

        $data['gurus'] = $this->master->getAllGuru();

        return view('cbt/praktik/edit', $data);
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi telah berakhir']);
        }

        $praktik = $this->praktikModel->find($id);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses untuk mengubah ujian praktik ini']);
        }

        $kelas = $this->request->getPost('kelas');
        $kelasJson = is_array($kelas) ? json_encode($kelas) : json_encode([$kelas]);

        $batasanFile = [
            'extensions' => $this->request->getPost('file_extensions') ?? ['pdf', 'doc', 'docx', 'zip'],
            'max_size' => $this->request->getPost('max_file_size') ?? 10485760,
        ];

        $assessors = $this->request->getPost('assessors');
        $assessorsJson = is_array($assessors) ? json_encode($assessors) : null;

        $tp_ids = $this->request->getPost('tujuan_pembelajaran');
        $tpJson = is_array($tp_ids) ? json_encode($tp_ids) : json_encode([$tp_ids]);

        $data = [
            'mapel_id' => $this->request->getPost('mapel'),
            'kelas_id' => $kelasJson,
            'judul' => $this->request->getPost('judul'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'instruksi' => $this->request->getPost('instruksi'),
            'tujuan_pembelajaran' => $tpJson,
            'jenis_praktik' => $this->request->getPost('jenis_praktik'),
            'format_pengumpulan' => $this->request->getPost('format_pengumpulan') ?? 'file',
            'batasan_file' => json_encode($batasanFile),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'durasi_menit' => $this->request->getPost('durasi_menit') ?? 0,
            'bobot' => $this->request->getPost('bobot') ?? 100,
            'kkm' => $this->request->getPost('kkm') ?? 70,
            'kktp_mode' => $this->request->getPost('kktp_mode') ?? 'kkm',
            'capaian_pembelajaran_id' => $this->request->getPost('capaian_pembelajaran_id') ?: null,
            'alur_tujuan_pembelajaran_id' => $this->request->getPost('alur_tujuan_pembelajaran_id') ?: null,
            'capaian_pembelajaran' => $this->request->getPost('capaian_pembelajaran'),
            'alur_tujuan_pembelajaran' => $this->request->getPost('alur_tujuan_pembelajaran'),
            'tipe_instruksi' => $this->request->getPost('tipe_instruksi') ?? 'tertutup',
            'panduan_kreativitas' => $this->request->getPost('panduan_kreativitas'),
            'contoh_hasil' => $this->request->getPost('contoh_hasil'),
            'sumber_belajar' => $this->request->getPost('sumber_belajar'),
            'izinkan_kolaborasi' => $this->request->getPost('izinkan_kolaborasi') ? 1 : 0,
            'aktifkan_penilaian_otomatis' => $this->request->getPost('aktifkan_otomatis') ? 1 : 0,
            'aktifkan_anti_cheat' => 0,
            'batas_tab_browser' => 0,
            'batas_copy_paste' => 0,
            'batas_screenshot' => 0,
            'wajib_webcam' => 0,
            'wajib_fullscreen' => 0,
            'assessors' => $assessorsJson,
            'template_id' => $this->request->getPost('template_rubrik') ?: null,
            'status' => $this->request->getPost('status') ?? 'draft',
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $this->praktikModel->update($id, $data);

        $templateId = $this->request->getPost('template_rubrik');
        if ($templateId && $this->request->getPost('apply_template')) {
            $this->rubrikModel->deleteByPraktik((int)$id);
            $this->templateModel->applyTemplate((int)$templateId, (int)$id);
        }

        $db->transComplete();

        return $this->response->setJSON([
            'status' => $db->transStatus(),
            'message' => $db->transStatus() ? 'Ujian praktik berhasil diperbarui' : 'Terjadi kesalahan',
        ]);
    }

    public function penilaian($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses untuk menilai ujian praktik ini');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();

        $hasil_raw = $this->hasilModel->getHasilByPraktik((int)$praktikId, false);

        // Normalisasi data legacy agar nilai_akhir konsisten (0-100)
        foreach ($hasil_raw as &$h) {
            $this->normalisasiNilaiAkhir($h);
        }

        $data = [
            'user' => $user,
            'judul' => 'Penilaian Praktik',
            'subjudul' => $praktik->judul,
            'setting' => $setting,
            'praktik' => $praktik,
            'hasil' => $hasil_raw,
            'rubrik' => $this->rubrikModel->getRubrikPraktik((int)$praktikId, true),
            'statistics' => $this->hasilModel->getStatistics((int)$praktikId),
        ];

        return view('cbt/praktik/penilaian', $data);
    }

    public function getHasilDetail()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false]);
        }

        $hasilId = $this->request->getGet('hasil_id');
        $hasil = $this->hasilModel->getHasilDetail((int)$hasilId);

        if (!$hasil) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        // Normalisasi data legacy agar nilai_akhir konsisten (0-100)
        $this->normalisasiNilaiAkhir($hasil);

        $rubriks = $this->rubrikModel->getRubrikPraktik($hasil->praktik_id, true);

        return $this->response->setJSON([
            'status' => true,
            'data' => $hasil,
            'rubriks' => $rubriks
        ]);
    }

    public function simpanPenilaian()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $hasilId = $this->request->getPost('hasil_id');
        $nilaiManualInput = $this->request->getPost('nilai_manual');
        $rincianNilai = $this->request->getPost('nilai_rubrik'); // Criteria values from modal
        $feedback = $this->request->getPost('feedback');

        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        $praktik = $this->praktikModel->find($hasil->praktik_id);
        if (!$praktik || !$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        $finalNilai = $nilaiManualInput;
        $rubrik = $this->rubrikModel->getRubrikPraktik((int)$hasil->praktik_id);

        // Jika ada nilai per-kriteria, hitung nilai akhir (rumus terpusat & konsisten)
        if (!empty($rincianNilai) && is_array($rincianNilai) && !empty($rubrik)) {
            $map_rubrik = [];
            foreach ($rubrik as $r) {
                $map_rubrik[$r->id] = $r;
            }

            $finalNilai = $this->hitungNilaiAkhir($rincianNilai, $map_rubrik);
        }

        $finalNilai = round((float)$finalNilai, 2);

        $data = [
            'nilai_manual' => $finalNilai,
            'nilai_akhir' => $finalNilai,
            'rincian_nilai_rubrik' => !empty($rincianNilai) ? json_encode($rincianNilai) : $hasil->rincian_nilai_rubrik,
            'status_penilaian' => 'sudah_dinilai',
            'feedback_guru' => $feedback,
        ];

        $user = $this->authModel->user();
        $guruId = $this->getCurrentGuruId($user);

        if ($this->hasilModel->update($hasilId, $data)) {
            // Audit trail: catat perubahan nilai
            $this->auditModel->logPerubahan(
                (int)$hasil->praktik_id,
                (int)$hasilId,
                (int)$hasil->siswa_id,
                $guruId,
                $hasil->nilai_akhir !== null ? CbtPraktikNilaiAuditModel::AKSI_UPDATE : CbtPraktikNilaiAuditModel::AKSI_CREATE,
                'nilai_akhir',
                $hasil->nilai_akhir !== null ? (float)$hasil->nilai_akhir : null,
                $finalNilai,
                $hasil->rincian_nilai_rubrik ? json_decode($hasil->rincian_nilai_rubrik, true) : null,
                $rincianNilai ?: null
            );

            // Sync to Nilai Proses if applicable
            $praktik = $this->praktikModel->find($hasil->praktik_id);
            if ($praktik && $praktik->tujuan_pembelajaran) {
                $this->syncToNilaiProses($hasil->siswa_id, $praktik, $finalNilai);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Penilaian berhasil disimpan'
            ]);
        }

        return $this->response->setJSON([
            'status' => false,
            'message' => 'Gagal menyimpan penilaian'
        ]);
    }

    /**
     * Manual grading page - Grid entry for all students
     */
    public function nilaiManual($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses');
        }

        $id_kelas_selected = $this->request->getGet('kelas');

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Get all students in this practical exam
        $kelasIds = $praktik->kelas_ids ?? [];
        $siswa = [];
        $listKelas = [];
        if (!empty($kelasIds)) {
            if ($id_kelas_selected && in_array($id_kelas_selected, $kelasIds)) {
                $siswa = $this->master->getSiswaByKelas($id_kelas_selected, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0), true);
            } else {
                $siswa = $this->master->getSiswaByKelasIds($kelasIds, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0));
            }
            $listKelas = $this->master->getKelasByIds($kelasIds);
        }

        // Get existing results for this practical exam
        $hasil = $this->hasilModel->getHasilByPraktik((int)$praktikId, false);
        $hasilBySiswa = [];
        foreach ($hasil as $h) {
            $hasilBySiswa[$h->siswa_id] = $h;
        }

        // Get rubrik/kriteria for this practical exam
        $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId, true);

        // If no rubrik defined, check if template_id is set
        if (empty($rubrik) && $praktik->template_id) {
            $this->templateModel->applyTemplate((int)$praktik->template_id, (int)$praktikId);
            $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId, true);
        }

        // Get available templates for this mapel
        $templates = $this->templateModel->getTemplatesByMapel((int)$praktik->mapel_id);

        $data = [
            'user' => $user,
            'judul' => 'Input Nilai Praktik Manual',
            'subjudul' => $praktik->judul,
            'setting' => $setting,
            'praktik' => $praktik,
            'siswa' => $siswa,
            'listKelas' => $listKelas,
            'id_kelas_selected' => $id_kelas_selected,
            'hasilBySiswa' => $hasilBySiswa,
            'rubrik' => $rubrik,
            'templates' => $templates,
            'statistics' => $this->hasilModel->getStatistics((int)$praktikId),
            'kkm' => $praktik->kkm ?? 75,
            'kktp_mode' => $praktik->kktp_mode ?? 'kkm',
            'capaian_pembelajaran' => $praktik->capaian_pembelajaran ?? null,
            'alur_tujuan_pembelajaran' => $praktik->alur_tujuan_pembelajaran ?? null,
        ];

        return view('cbt/praktik/nilai_manual', $data);
    }

    public function nilaiManualPdf($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses');
        }

        $id_kelas_selected = $this->request->getGet('kelas');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $kelasIds = $praktik->kelas_ids ?? [];
        $siswa = [];
        $listKelas = [];
        $nama_kelas_selected = '';

        if (!empty($kelasIds)) {
            if ($id_kelas_selected && in_array($id_kelas_selected, $kelasIds)) {
                $siswa = $this->master->getSiswaByKelas($id_kelas_selected, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0), true);
            } else {
                $siswa = $this->master->getSiswaByKelasIds($kelasIds, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0));
            }
            $listKelas = $this->master->getKelasByIds($kelasIds);
            
            if ($id_kelas_selected) {
                foreach($listKelas as $k) {
                    $curr_id = is_object($k) ? ($k->id_kelas ?? null) : ($k['id_kelas'] ?? null);
                    if($curr_id == $id_kelas_selected) {
                        $nama_kelas_selected = is_object($k) ? ($k->nama_kelas ?? '-') : ($k['nama_kelas'] ?? '-');
                        break;
                    }
                }
            }
        }

        $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId, true);

        // If no rubrik defined, check if template_id is set
        if (empty($rubrik) && $praktik->template_id) {
            $this->templateModel->applyTemplate((int)$praktik->template_id, (int)$praktikId);
            $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId, true);
        }

        // Get existing scores for pre-fill
        $hasil = $this->hasilModel->getHasilByPraktik((int)$praktikId, false);
        $hasilBySiswa = [];
        foreach ($hasil as $h) {
            $hasilBySiswa[$h->siswa_id] = $h;
        }

        $setting = $this->dashboard->getSetting();

        $data = [
            'praktik'           => $praktik,
            'siswa'             => $siswa,
            'id_kelas_selected' => $id_kelas_selected,
            'nama_kelas_selected' => $nama_kelas_selected,
            'rubrik'            => $rubrik,
            'hasilBySiswa'      => $hasilBySiswa,
            'setting'           => $setting,
            'listKelas'         => $listKelas,
        ];

        $html = view('cbt/praktik/nilai_manual_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Format_Nilai_Manual_' . preg_replace('/[^a-zA-Z0-9]/', '_', $praktik->judul) . '.pdf';
        
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Save manual grades for multiple students
     */
    public function simpanNilaiManual()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $praktikId = $this->request->getPost('praktik_id');
        $nilaiData = $this->request->getPost('nilai'); // Array of [siswa_id => [kriteria => nilai]]

        if (empty($nilaiData) || !is_array($nilaiData)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data nilai kosong']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $successCount = 0;
        $errorCount = 0;

        $user = $this->authModel->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $praktik = $this->praktikModel->find($praktikId);
        if (!$praktik || !$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }
        $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId);
        $map_rubrik = [];
        foreach ($rubrik as $r) {
            $map_rubrik[$r->id] = $r;
        }

        // Batch fetch semua hasil sekaligus (hindari N+1 query per siswa)
        $allHasil = $this->hasilModel->getHasilByPraktik((int)$praktikId, false);
        $hasilBySiswa = [];
        foreach ($allHasil as $h) {
            $hasilBySiswa[$h->siswa_id] = $h;
        }

        foreach ($nilaiData as $siswaId => $nilaiKriteria) {
            // Check if hasil record exists
            $hasil = $hasilBySiswa[$siswaId] ?? null;

            // Kumpulkan nilai terisi, lalu hitung dengan rumus terpusat (weighted average)
            $rincianNilai = [];
            foreach ($nilaiKriteria as $kriteriaId => $nilai) {
                if ($nilai !== '' && $nilai !== null) {
                    $rincianNilai[$kriteriaId] = (float)$nilai;
                }
            }

            // Lewati siswa yang tidak ada satu pun nilai diisi
            if (empty($rincianNilai)) {
                continue;
            }

            $finalNilai = $this->hitungNilaiAkhir($rincianNilai, $map_rubrik);

            $idHasil = null;
            if (!$hasil) {
                // Create new hasil record (Flexible: automatic submission on manual grading)
                $data = [
                    'praktik_id' => $praktikId,
                    'siswa_id' => $siswaId,
                    'kelas_id' => $this->request->getPost("kelas[$siswaId]") ?? null,
                    'status_pengerjaan' => 'selesai',
                    'status_penilaian' => 'sudah_dinilai',
                    'nilai_manual' => $finalNilai,
                    'nilai_akhir' => $finalNilai,
                    'rincian_nilai_rubrik' => json_encode($rincianNilai),
                    'waktu_mulai' => date('Y-m-d H:i:s'),
                    'waktu_submit' => date('Y-m-d H:i:s'), // Mark as submitted now
                ];
                
                $idHasil = $this->hasilModel->insert($data);
                if ($idHasil) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } else {
                // Update existing hasil
                $data = [
                    'nilai_manual' => $finalNilai,
                    'nilai_akhir' => $finalNilai,
                    'rincian_nilai_rubrik' => json_encode($rincianNilai),
                    'status_penilaian' => 'sudah_dinilai',
                ];

                // If they hadn't submitted but teacher is grading, mark as finished
                if ($hasil->status_pengerjaan !== 'selesai') {
                    $data['status_pengerjaan'] = 'selesai';
                    $data['waktu_submit'] = date('Y-m-d H:i:s');
                }
                
                if ($this->hasilModel->update($hasil->id, $data)) {
                    $idHasil = $hasil->id;
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            // Sync with Kurikulum Merdeka (nilai_proses)
            if ($idHasil && $praktik && $praktik->tujuan_pembelajaran) {
                $this->syncToNilaiProses($siswaId, $praktik, $finalNilai);
            }

            // Audit Trail
            if ($idHasil) {
                $this->logModel->logActivity(
                    (int)$praktikId,
                    (int)$siswaId,
                    'penilaian_manual',
                    (int)$idHasil,
                    ['nilai' => $finalNilai, 'rincian' => $rincianNilai, 'admin_id' => $user->id]
                );

                $guruId = $this->getCurrentGuruId($user);
                $this->auditModel->logPerubahan(
                    (int)$praktikId,
                    (int)$idHasil,
                    (int)$siswaId,
                    $guruId,
                    $hasil && $hasil->nilai_akhir !== null ? CbtPraktikNilaiAuditModel::AKSI_UPDATE : CbtPraktikNilaiAuditModel::AKSI_CREATE,
                    'nilai_akhir',
                    $hasil && $hasil->nilai_akhir !== null ? (float)$hasil->nilai_akhir : null,
                    $finalNilai,
                    $hasil && $hasil->rincian_nilai_rubrik ? json_decode($hasil->rincian_nilai_rubrik, true) : null,
                    $rincianNilai
                );
            }
        }

        $db->transComplete();

        return $this->response->setJSON([
            'status' => $db->transStatus(),
            'message' => "Berhasil menyimpan $successCount data nilai" . ($errorCount > 0 ? ", $errorCount gagal" : ''),
            'success_count' => $successCount,
            'error_count' => $errorCount
        ]);
    }

    /**
     * Synchronize score to Kurikulum Merdeka (nilai_proses)
     */
    protected function syncToNilaiProses($siswaId, $praktik, $nilai)
    {
        if (empty($praktik->tujuan_pembelajaran)) return;

        $tp_ids = json_decode($praktik->tujuan_pembelajaran, true);
        if (!is_array($tp_ids)) {
            $tp_ids = [$praktik->tujuan_pembelajaran];
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        foreach ($tp_ids as $tp_id) {
            if (empty($tp_id)) continue;

            $data = [
                'id_siswa' => $siswaId,
                'id_mapel' => $praktik->mapel_id,
                'id_tp' => (int)$tp_id,
                'id_guru' => $praktik->created_by,
                'id_tahun' => $tp->id_tp,
                'id_smt' => $smt->id_smt,
                'tipe' => 'praktik',
                'nilai' => $nilai,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Check if already exists
            $existing = $this->nilaiModel->db->table('nilai_proses')
                ->where([
                    'id_siswa' => $siswaId,
                    'id_mapel' => $praktik->mapel_id,
                    'id_tp' => (int)$tp_id,
                    'tipe' => 'praktik'
                ])->get()->getRow();

            if ($existing) {
                $this->nilaiModel->db->table('nilai_proses')
                    ->where('id_nilai_proses', $existing->id_nilai_proses)
                    ->update($data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->nilaiModel->db->table('nilai_proses')->insert($data);
            }
        }
    }

    public function analisisRemedial($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses');
        }

        $hasil_raw = $this->hasilModel->getHasilByPraktik((int)$praktikId, false);
        $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId, true);

        $remedialSiswa = [];
        $totalSiswa = count($hasil_raw);
        $totalLulus = 0;
        $totalRemedial = 0;

        // Kriteria analysis (which criteria are most problematic)
        $kriteriaStats = [];
        foreach ($rubrik as $r) {
            $kriteriaStats[$r->id] = [
                'nama' => $r->nama_kriteria,
                'total_nilai' => 0,
                'count' => 0,
                'remedial_count' => 0
            ];
        }

        foreach ($hasil_raw as $h) {
            // Fix: Ensure nilai_akhir is average, not sum (legacy data)
            if ($h->nilai_akhir !== null && $h->rincian_nilai_rubrik) {
                $rincian = json_decode((string)$h->rincian_nilai_rubrik, true);
                if (is_array($rincian) && count($rincian) > 0) {
                    $sum_rincian = array_sum($rincian);
                    if ($h->nilai_akhir > 100 || abs($h->nilai_akhir - $sum_rincian) < 0.1) {
                        $h->nilai_akhir = $h->nilai_akhir / count($rincian);
                    }
                }
            }

            $rincian = json_decode((string)$h->rincian_nilai_rubrik, true) ?: [];
            
            $kktpInfo = $this->getKktpForAnalysis($h->nilai_akhir, $this->getKkm($praktik));
            $tercapai = $kktpInfo['tercapai'];
            
            if (!$tercapai) {
                $totalRemedial++;
                
                // Identify weak points
                $weakPoints = [];
                foreach ($rincian as $rid => $val) {
                    $rObj = null;
                    foreach ($rubrik as $r) if ($r->id == $rid) { $rObj = $r; break; }
                    
                    if ($rObj && $val < ($rObj->bobot_persen * 0.7)) {
                        $weakPoints[] = $rObj->nama_kriteria;
                    }
                }

                $remedialSiswa[] = [
                    'id_siswa' => $h->siswa_id,
                    'nama' => $h->nama_siswa,
                    'nilai' => $h->nilai_akhir,
                    'predikat' => $kktpInfo['predikat'],
                    'singkatan' => $kktpInfo['singkatan'],
                    'weak_points' => $weakPoints
                ];
            } else {
                $totalLulus++;
            }

            // Update stats
            foreach ($rincian as $rid => $val) {
                if (isset($kriteriaStats[$rid])) {
                    $kriteriaStats[$rid]['total_nilai'] += $val;
                    $kriteriaStats[$rid]['count']++;
                }
            }
        }

        // Calculate recommendations
        $recommendations = [];
        foreach ($kriteriaStats as $id => $stat) {
            $avg = $stat['count'] > 0 ? ($stat['total_nilai'] / $stat['count']) : 0;
            // Get weight for comparison
            $weight = 0;
            foreach ($rubrik as $r) if ($r->id == $id) { $weight = $r->bobot_persen; break; }
            
            $percentage = $weight > 0 ? ($avg / $weight * 100) : 0;
            if ($percentage < 70) {
                $recommendations[] = "Peningkatan pada aspek **" . $stat['nama'] . "** diperlukan karena rata-rata pencapaian hanya " . round($percentage, 2) . "%.";
            }
        }

        if (empty($recommendations) && $totalRemedial > 0) {
            $recommendations[] = "Lakukan pengulangan materi secara umum dan sesi praktik tambahan bagi siswa remedial.";
        }

        // KKTP distribution
        $kktpDistribution = ['M' => 0, 'C' => 0, 'L' => 0, 'BB' => 0];
        foreach ($hasil_raw as $h) {
            $info = $this->getKktpForAnalysis($h->nilai_akhir, $this->getKkm($praktik));
            $kktpDistribution[$info['singkatan']] = ($kktpDistribution[$info['singkatan']] ?? 0) + 1;
        }

        $data = [
            'user' => $this->authModel->user(),
            'judul' => 'Analisis Remedial & Rekomendasi',
            'subjudul' => $praktik->judul,
            'setting' => $this->dashboard->getSetting(),
            'praktik' => $praktik,
            'remedial_siswa' => $remedialSiswa,
            'stats' => [
                'total' => $totalSiswa,
                'lulus' => $totalLulus,
                'remedial' => $totalRemedial,
                'persen_lulus' => $totalSiswa > 0 ? round($totalLulus / $totalSiswa * 100, 2) : 0
            ],
            'recommendations' => $recommendations,
            'kriteria_stats' => $kriteriaStats,
            'kktp_distribution' => $kktpDistribution
        ];

        return view('cbt/praktik/analisis', $data);
    }

    public function exportReport($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses');
        }

        $id_kelas_selected = $this->request->getGet('kelas');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Get student list (either filtered by class or all in the exam)
        $kelasIds = $praktik->kelas_ids ?? [];
        $siswaList = [];
        if (!empty($kelasIds)) {
            if ($id_kelas_selected && in_array($id_kelas_selected, $kelasIds)) {
                $siswaList = $this->master->getSiswaByKelas($id_kelas_selected, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0), true);
            } else {
                $siswaList = $this->master->getSiswaByKelasIds($kelasIds, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0));
            }
        }

        // Get existing results
        $hasil = $this->hasilModel->getHasilByPraktik((int)$praktikId, false);
        $hasilBySiswa = [];
        foreach ($hasil as $h) {
            $hasilBySiswa[$h->siswa_id] = $h;
        }

        $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId, true);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Ujian Praktik');

        // Styles
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];

        // Header Info
        $sheet->setCellValue('A1', 'LAPORAN HASIL UJIAN PRAKTIK');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        $sheet->setCellValue('A2', 'Judul Ujian: ' . $praktik->judul);
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A3', 'Mata Pelajaran: ' . ($praktik->nama_mapel ?? '-'));
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A4', 'Tanggal: ' . date('d M Y', strtotime($praktik->tanggal_mulai)));
        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A5', 'KKM: ' . $praktik->kkm . ' | Mode: ' . strtoupper($praktik->kktp_mode ?? 'KKM') . ' | KKTP: M(86-100) C(66-85) L(41-65) BB(0-40)');
        $sheet->mergeCells('A5:H5');
        
        if ($id_kelas_selected) {
            $nama_kelas = '-';
            $listKelas = $this->master->getKelasByIds($kelasIds);
            foreach($listKelas as $k) {
                if ($k->id_kelas == $id_kelas_selected) {
                    $nama_kelas = $k->nama_kelas;
                    break;
                }
            }
            $sheet->setCellValue('A6', 'Kelas: ' . $nama_kelas);
            $sheet->mergeCells('A6:H6');
        }

        // Table Headers
        $currentRow = 8;
        $sheet->setCellValue('A' . $currentRow, 'NO');
        $sheet->setCellValue('B' . $currentRow, 'NISN');
        $sheet->setCellValue('C' . $currentRow, 'NAMA SISWA');
        $sheet->setCellValue('D' . $currentRow, 'KELAS');
        
        $colIndex = 'E';
        foreach ($rubrik as $r) {
            $headerText = ($r->kode_kriteria ?? $r->id) . "\n" . $r->nama_kriteria . "\n(" . $r->bobot_persen . '%)';
            $sheet->setCellValue($colIndex . $currentRow, $headerText);
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setWrapText(true);
            $colIndex++;
        }
        
        $sheet->setCellValue($colIndex . $currentRow, 'NILAI AKHIR');
        $sheet->getStyle($colIndex . $currentRow)->getFont()->setBold(true);
        $colIndex++;
        
        $sheet->setCellValue($colIndex . $currentRow, 'PREDIKAT');
        $colIndex++;
        
        $sheet->setCellValue($colIndex . $currentRow, 'KETERANGAN');
        
        $lastCol = $colIndex;
        $sheet->getStyle('A' . $currentRow . ':' . $lastCol . $currentRow)->applyFromArray($headerStyle);

        // Data
        $no = 1;
        $currentRow++;
        foreach ($siswaList as $s) {
            $s_id = is_object($s) ? $s->id_siswa : ($s['id_siswa'] ?? null);
            if (!$s_id) continue;
            
            $row = $hasilBySiswa[$s_id] ?? null;
            
            $sheet->setCellValue('A' . $currentRow, $no++);
            
            $nisn = is_object($s) ? ($s->nisn ?? $s->nis ?? '-') : ($s['nisn'] ?? $s['nis'] ?? '-');
            $sheet->setCellValueExplicit('B' . $currentRow, $nisn, DataType::TYPE_STRING);
            
            $sheet->setCellValue('C' . $currentRow, is_object($s) ? $s->nama : ($s['nama'] ?? '-'));
            $sheet->setCellValue('D' . $currentRow, is_object($s) ? ($s->nama_kelas ?? '-') : ($s['nama_kelas'] ?? '-'));
            
            $rincian = [];
            if ($row && isset($row->rincian_nilai_rubrik)) {
                $rincian = json_decode($row->rincian_nilai_rubrik, true) ?: [];
            }
            
            $colData = 'E';
            foreach ($rubrik as $r) {
                $kId = $r->id ?? $r->kode_kriteria;
                $sheet->setCellValue($colData . $currentRow, $rincian[$kId] ?? 0);
                $colData++;
            }
            
            $nilai = $row ? ($row->nilai_akhir ?? 0) : 0;
            $sheet->setCellValue($colData . $currentRow, $nilai);
            $sheet->getStyle($colData . $currentRow)->getFont()->setBold(true);
            $colData++;
            
            // Predikat KKTP (KKM-aware)
            $kkmExport = $this->getKkm($praktik);
            $kktpInfo = $this->getKktpForAnalysis($nilai, $kkmExport);
            $predikat = $nilai > 0 ? ($kktpInfo['singkatan'] . ' (' . $kktpInfo['predikat'] . ')') : '-';
            $sheet->setCellValue($colData . $currentRow, $predikat);
            $colData++;
            
            $tercapai = $kktpInfo['tercapai'];
            $keterangan = $tercapai ? 'TUNTAS' : 'REMEDIAL';
            $sheet->setCellValue($colData . $currentRow, $keterangan);
            
            // Color for keterangan
            if ($keterangan === 'REMEDIAL') {
                $sheet->getStyle($colData . $currentRow)->getFont()->getColor()->setRGB('FF0000');
            } else {
                $sheet->getStyle($colData . $currentRow)->getFont()->getColor()->setRGB('00B050');
            }
            
            // Borders for data row
            $sheet->getStyle('A' . $currentRow . ':' . $lastCol . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $currentRow++;
        }

        // Add Signature Block
        $currentRow += 2;
        $sigCol = chr(ord($lastCol) - 2); // Position signature roughly 2 columns from the right
        if ($sigCol < 'C') $sigCol = 'C'; // Ensure it's not too far left
        
        $sheet->setCellValue($sigCol . $currentRow, 'Mengetahui,');
        $currentRow++;
        $sheet->setCellValue($sigCol . $currentRow, 'Guru Mata Pelajaran');
        $currentRow += 4; // Space for signature
        
        $teacherName = $praktik->nama_guru ?? '-';
        $sheet->setCellValue($sigCol . $currentRow, $teacherName);
        $sheet->getStyle($sigCol . $currentRow)->getFont()->setBold(true)->setUnderline(true);
        $currentRow++;
        $sheet->setCellValue($sigCol . $currentRow, 'NIP. ' . ($praktik->nip_guru ?? '-'));

        // Auto-size columns
        foreach (range('B', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth(5);

        $filename = 'Hasil_Praktik_' . preg_replace('/[^a-zA-Z0-9]/', '_', $praktik->judul) . '_' . ($id_kelas_selected ? 'Kelas_' . $id_kelas_selected . '_' : '') . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function monitor($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses');
        }

        $id_kelas_selected = $this->request->getGet('kelas');

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $kelasIds = $praktik->kelas_ids ?? [];
        $siswa = [];
        $listKelas = [];
        if (!empty($kelasIds)) {
            if ($id_kelas_selected && in_array($id_kelas_selected, $kelasIds)) {
                $siswa = $this->master->getSiswaByKelas($id_kelas_selected, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0), true);
            } else {
                $siswa = $this->master->getSiswaByKelasIds($kelasIds, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0));
            }
            $listKelas = $this->master->getKelasByIds($kelasIds);
        }

        $siswaIds = array_column($siswa, 'id_siswa');
        $progress = $this->hasilModel->getProgressSiswa((int)$praktikId, $siswaIds);

        $total_nilai = 0;
        $count_selesai = 0;
        $max_nilai = 0;
        $min_nilai = 100;

        // Fix: Ensure nilai_akhir is average, not sum (legacy data handling)
        foreach ($progress as &$p) {
            if (isset($p->nilai_akhir) && $p->nilai_akhir !== null && isset($p->rincian_nilai_rubrik) && $p->rincian_nilai_rubrik) {
                $rincian = json_decode((string)$p->rincian_nilai_rubrik, true);
                if (is_array($rincian) && count($rincian) > 0) {
                    $sum_rincian = array_sum($rincian);
                    if ($p->nilai_akhir > 100 || abs($p->nilai_akhir - $sum_rincian) < 0.1) {
                        $p->nilai_akhir = $p->nilai_akhir / count($rincian);
                    }
                }
            }
            
            if (isset($p->status_pengerjaan) && $p->status_pengerjaan === 'selesai' && $p->nilai_akhir !== null) {
                $total_nilai += $p->nilai_akhir;
                $count_selesai++;
                $max_nilai = max($max_nilai, $p->nilai_akhir);
                $min_nilai = min($min_nilai, $p->nilai_akhir);
            }
        }

        $statistics = $this->hasilModel->getStatistics((int)$praktikId);
        // Recalculate average and high/low if there were finished exams to account for legacy data
        if ($count_selesai > 0) {
            $statistics->rata_nilai = $total_nilai / $count_selesai;
            $statistics->nilai_tertinggi = $max_nilai;
            $statistics->nilai_terendah = $min_nilai;
        }

        $data = [
            'user' => $user,
            'judul' => 'Monitoring Ujian',
            'subjudul' => $praktik->judul,
            'setting' => $setting,
            'praktik' => $praktik,
            'siswa' => $siswa,
            'listKelas' => $listKelas,
            'id_kelas_selected' => $id_kelas_selected,
            'progress' => $progress,
            'statistics' => $statistics,
        ];

        return view('cbt/praktik/monitor', $data);
    }

    public function getMonitorData()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false]);
        }

        $praktikId = $this->request->getGet('praktik_id');
        $id_kelas_selected = $this->request->getGet('kelas');

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ujian praktik tidak ditemukan']);
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        $kelasIds = $praktik->kelas_id ? $this->praktikModel->decodeKelasId($praktik->kelas_id) : [];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $siswa = [];
        if (!empty($kelasIds)) {
            if ($id_kelas_selected && in_array($id_kelas_selected, $kelasIds)) {
                $siswa = $this->master->getSiswaByKelas($id_kelas_selected, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0), true);
            } else {
                $siswa = $this->master->getSiswaByKelasIds($kelasIds, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0));
            }
        }

        $siswaIds = array_column($siswa, 'id_siswa');
        $progress = $this->hasilModel->getProgressSiswa((int)$praktikId, $siswaIds);

        $total_nilai = 0;
        $count_selesai = 0;
        $max_nilai = 0;
        $min_nilai = 100;

        // Fix: Ensure nilai_akhir is average, not sum (legacy data handling)
        foreach ($progress as &$p) {
            if (isset($p->nilai_akhir) && $p->nilai_akhir !== null && isset($p->rincian_nilai_rubrik) && $p->rincian_nilai_rubrik) {
                $rincian = json_decode((string)$p->rincian_nilai_rubrik, true);
                if (is_array($rincian) && count($rincian) > 0) {
                    $sum_rincian = array_sum($rincian);
                    if ($p->nilai_akhir > 100 || abs($p->nilai_akhir - $sum_rincian) < 0.1) {
                        $p->nilai_akhir = $p->nilai_akhir / count($rincian);
                    }
                }
            }
            
            if (isset($p->status_pengerjaan) && $p->status_pengerjaan === 'selesai' && $p->nilai_akhir !== null) {
                $total_nilai += $p->nilai_akhir;
                $count_selesai++;
                $max_nilai = max($max_nilai, $p->nilai_akhir);
                $min_nilai = min($min_nilai, $p->nilai_akhir);
            }
        }

        $violations = $this->logModel->getStudentsWithViolations((int)$praktikId);
        $statistics = $this->hasilModel->getStatistics((int)$praktikId);
        
        // Recalculate average and high/low if there were finished exams to account for legacy data
        if ($count_selesai > 0) {
            $statistics->rata_nilai = $total_nilai / $count_selesai;
            $statistics->nilai_tertinggi = $max_nilai;
            $statistics->nilai_terendah = $min_nilai;
        }

        return $this->response->setJSON([
            'status' => true,
            'progress' => $progress,
            'violations' => $violations,
            'statistics' => $statistics,
            'kkm' => $this->getKkm($praktik),
        ]);
    }

    /**
     * Detail hasil per siswa
     */
    public function hasil($praktikId, $siswaId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik');
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return redirect()->to('cbtpraktik')->with('error', 'Anda tidak memiliki hak akses');
        }

        $db = \Config\Database::connect();
        $siswa = $db->table('master_siswa a')
                   ->select('a.*, b.nama_kelas')
                   ->join('kelas_siswa ks', 'a.id_siswa = ks.id_siswa', 'left')
                   ->join('master_kelas b', 'ks.id_kelas = b.id_kelas', 'left')
                   ->where('a.id_siswa', $siswaId)
                   ->orderBy('ks.id_tp', 'DESC')
                   ->orderBy('ks.id_smt', 'DESC')
                   ->limit(1)
                   ->get()->getRow();

        if (!$siswa) {
            $siswa = $db->table('master_siswa')->where('id_siswa', $siswaId)->get()->getRow();
        }

        if (!$siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan');
        }

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswaId);
        if ($hasil && $hasil->id) {
            $fileModel = new CbtPraktikFileModel();
            $hasil->files = $fileModel->getFilesByHasil($hasil->id);
        }

        if (!$hasil || $hasil->status_pengerjaan === 'belum_mulai') {
            return redirect()->back()->with('error', 'Siswa belum mulai mengerjakan');
        }

        $observasiModel = new \App\Models\CbtPraktikObservasiModel();
        $observasi = $observasiModel->where('praktik_id', (int)$praktikId)
            ->where('siswa_id', (int)$siswaId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $selfModel = new \App\Models\CbtPraktikSelfAssessmentModel();
        $selfAssessment = $selfModel->where('praktik_id', (int)$praktikId)
            ->where('siswa_id', (int)$siswaId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'user' => $this->authModel->user(),
            'judul' => 'Hasil Ujian Praktik',
            'subjudul' => $praktik->judul,
            'setting' => $this->dashboard->getSetting(),
            'praktik' => $praktik,
            'siswa' => $siswa,
            'hasil' => $hasil,
            'rubriks' => $this->rubrikModel->getRubrikPraktik((int)$praktikId, true),
            'observasi' => $observasi,
            'selfAssessment' => $selfAssessment,
        ];

        return view('cbt/praktik/hasil_siswa', $data);
    }

    public function templates()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();

        // Get filter from request if any
        $jenis = $this->request->getGet('jenis');

        // For admin (isAdmin()), show all templates
        // For non-admin, show only their templates + public
        $isAdmin = $this->isAdmin();
        $userIdFilter = $isAdmin ? null : $user->id;

        $data = [
            'user' => $user,
            'judul' => 'Manajemen Rubrik',
            'subjudul' => 'Daftar Template Rubrik',
            'setting' => $setting,
            'isAdmin' => $isAdmin,
            'templates' => $this->templateModel->getTemplates($userIdFilter, $jenis, true),
        ];

        return view('cbt/praktik/templates/index', $data);
    }

    public function templates_create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();

        $isAdmin = $this->isAdmin();
        $mapelsRaw = $this->master->getAllMapel();
        $mapels = [];
        foreach ($mapelsRaw as $m) {
            $mapels[$m->id_mapel] = $m->nama_mapel;
        }

        $gurus = [];
        if ($isAdmin) {
            $gurusRaw = $this->master->getAllGuruWithUserId();
            foreach ($gurusRaw as $g) {
                if ($g->id_user) {
                    $gurus[$g->id_user] = $g->nama_guru;
                }
            }
        }

        $data = [
            'user' => $user,
            'judul' => 'Buat Template Rubrik',
            'subjudul' => 'Form Tambah Template',
            'setting' => $setting,
            'mapels' => $mapels,
            'gurus' => $gurus,
            'isAdmin' => $isAdmin,
            'isEdit' => false,
        ];

        return view('cbt/praktik/templates/form', $data);
    }

    public function templates_save()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $user = $this->authModel->user();
        
        $namaTemplate = $this->request->getPost('nama_template');
        if (empty($namaTemplate)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama template wajib diisi']);
        }

        $data = [
            'nama_template' => $namaTemplate,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'jenis_praktik' => $this->request->getPost('jenis_praktik') ?? 'semua',
            'mapel_id' => $this->request->getPost('mapel_id') ?: null,
            'created_by' => ($this->isAdmin() && $this->request->getPost('created_by')) ? $this->request->getPost('created_by') : $user->id,
            'is_public' => $this->request->getPost('is_public') ? 1 : 0,
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $templateId = $this->templateModel->insert($data);

        if (!$templateId) {
            $db->transRollback();
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gagal menyimpan template'
            ]);
        }

        $items_raw = $this->request->getPost('items_raw');
        $items = [];
        if (!empty($items_raw)) {
            $items = json_decode($items_raw, true);
        }

        if (!empty($items)) {
            foreach ($items as $index => $item) {
                $insertData = [
                    'template_id' => $templateId,
                    'kode_kriteria' => $item['kode'],
                    'nama_kriteria' => $item['nama'],
                    'deskripsi' => $item['deskripsi'] ?? '',
                    'bobot_persen' => $item['bobot'],
                    'tipe_penilaian' => $item['tipe'] ?? 'manual',
                    'urutan' => ($index + 1),
                    'level_detail' => json_encode($item['levels'] ?? [])
                ];
                $db->table('cbt_praktik_template_rubrik_item')->insert($insertData);
            }
        }

        $db->transComplete();

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Template berhasil disimpan'
        ]);
    }

    public function templates_edit($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $template = $this->templateModel->getTemplateDetail((int)$id);
        if (!$template) {
            return redirect()->back()->with('error', 'Template tidak ditemukan');
        }

        $user = $this->authModel->user();
        $isAdmin = $this->isAdmin();

        if (!$isAdmin && $template->created_by != $user->id) {
            return redirect()->to('cbtpraktik/templates')->with('error', 'Anda tidak memiliki hak akses untuk mengedit template rubrik ini.');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();

        $isAdmin = $this->isAdmin();
        $mapelsRaw = $this->master->getAllMapel();
        $mapels = [];
        foreach ($mapelsRaw as $m) {
            $mapels[$m->id_mapel] = $m->nama_mapel;
        }

        $gurus = [];
        if ($isAdmin) {
            $gurusRaw = $this->master->getAllGuruWithUserId();
            foreach ($gurusRaw as $g) {
                if ($g->id_user) {
                    $gurus[$g->id_user] = $g->nama_guru;
                }
            }
        }

        $data = [
            'user' => $user,
            'judul' => 'Edit Template Rubrik',
            'subjudul' => 'Ubah ' . $template->nama_template,
            'setting' => $setting,
            'template' => $template,
            'mapels' => $mapels,
            'gurus' => $gurus,
            'isAdmin' => $isAdmin,
            'isEdit' => true,
        ];

        return view('cbt/praktik/templates/form', $data);
    }

    public function templates_detail($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $template = $this->templateModel->getTemplateDetail((int)$id);
        if (!$template) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        return view('cbt/praktik/templates/detail_modal', ['template' => $template]);
    }

    public function templates_update($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $template = $this->templateModel->find($id);
        if (!$template) {
            return $this->response->setJSON(['status' => false, 'message' => 'Template tidak ditemukan']);
        }

        $user = $this->authModel->user();
        if (!$this->isAdmin() && $template->created_by != $user->id) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses untuk mengubah template rubrik ini']);
        }

        $data = [
            'nama_template' => $this->request->getPost('nama_template'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'jenis_praktik' => $this->request->getPost('jenis_praktik') ?? 'semua',
            'mapel_id' => $this->request->getPost('mapel_id'),
            'is_public' => $this->request->getPost('is_public') ? 1 : 0,
        ];

        if ($this->isAdmin() && $this->request->getPost('created_by')) {
            $data['created_by'] = $this->request->getPost('created_by');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->templateModel->update($id, $data);

        // Delete old items and insert new ones
        $db->table('cbt_praktik_template_rubrik_item')->where('template_id', $id)->delete();

        // Map items from input
        $items_raw = $this->request->getPost('items_raw');
        $items = [];
        if (!empty($items_raw)) {
            $items = json_decode($items_raw, true);
        }

        if (!empty($items)) {
            foreach ($items as $index => $item) {
                $db->table('cbt_praktik_template_rubrik_item')->insert([
                    'template_id' => $id,
                    'kode_kriteria' => $item['kode'],
                    'nama_kriteria' => $item['nama'],
                    'deskripsi' => $item['deskripsi'] ?? '',
                    'bobot_persen' => $item['bobot'],
                    'tipe_penilaian' => $item['tipe'] ?? 'manual',
                    'urutan' => ($index + 1),
                    'level_detail' => json_encode($item['levels'] ?? [])
                ]);
            }
        }

        $db->transComplete();

        return $this->response->setJSON([
            'status' => $db->transStatus(),
            'message' => $db->transStatus() ? 'Template rubrik berhasil diperbarui' : 'Gagal memperbarui template'
        ]);
    }

    public function templates_delete($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $template = $this->templateModel->find($id);
        if (!$template) {
            return $this->response->setJSON(['status' => false, 'message' => 'Template tidak ditemukan']);
        }

        $user = $this->authModel->user();
        if (!$this->isAdmin() && $template->created_by != $user->id) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus template rubrik ini']);
        }

        if ($this->templateModel->delete($id)) {
            return $this->response->setJSON(['status' => true, 'message' => 'Template berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus template']);
    }

    /**
     * AI Auto Generate Rubric Template
     */
    public function templates_generate_ai()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $jenisPraktik = $this->request->getPost('jenis_praktik') ?? 'semua';
        $prompt = $this->request->getPost('prompt') ?? '';

        $aiPrompt = "Buatkan rubrik penilaian praktik untuk jenis praktik: '{$jenisPraktik}'.\n";
        $aiPrompt .= "Deskripsi / Konteks Praktik: {$prompt}\n\n";
        $aiPrompt .= "Tugas Anda adalah menghasilkan kriteria penilaian yang dinamis, kreatif, relevan dengan deskripsi yang diberikan tersebut, dan spesifik untuk mata pelajaran/kegiatannya. Jangan membuat kriteria yang monoton (seperti hanya 'Persiapan', 'Pelaksanaan', dll), tetapi berikan judul kriteria yang mencerminkan inti kegiatan tersebut.\n";
        $aiPrompt .= "Buat 4-5 kriteria dengan total bobot persis 100. Untuk setiap kriteria, buat array 'levels' yang memiliki rentang 'min' dan 'max' serta 'label'.\n";
        $aiPrompt .= "Level skor HARUS 4 tingkat berjenjang berurutan nilai (0-60, 61-75, 76-90, 91-100).\n";
        $aiPrompt .= 'Format balasan Anda HARUS HANYA JSON murni berupa array object tanpa tag markdown (jangan sertakan ```json). Contoh format:
[
  {
    "kode": "K1",
    "nama": "Nama Kriteria (Spesifik sesuai konteks, misal: Keakuratan Dimensi)",
    "bobot": 25,
    "deskripsi": "Penjelasan mendetail indikator penilaian ini.",
    "levels": [
      { "min": 0, "max": 60, "label": "Kurang - Penjelasan" },
      { "min": 61, "max": 75, "label": "Cukup - Penjelasan" },
      { "min": 76, "max": 90, "label": "Baik - Penjelasan" },
      { "min": 91, "max": 100, "label": "Sangat Baik - Penjelasan" }
    ]
  }
]';

        $aiService = new \App\Services\AIService();
        $user = $this->authModel->user();
        $apiKey = $aiService->getApiKey($user->id, $this->inGroup('guru'));

        $response = $aiService->generateContent($apiKey, $aiPrompt);

        if (!$response['success']) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal generate AI: ' . $response['message']]);
        }

        $parsed = $aiService->parseJsonResponse($response['data']);

        if (!$parsed['success']) {
            return $this->response->setJSON(['status' => false, 'message' => 'Format AI tidak sesuai: ' . $parsed['message']]);
        }

        $rubrics = $parsed['data'];

        // Validate structure and format to make sure it matches what the frontend expects
        $formattedRubrics = [];
        $kodeCounter = 1;
        foreach ($rubrics as $rubric) {
            $formattedRubrics[] = [
                'kode' => $rubric['kode'] ?? ('K' . $kodeCounter),
                'nama' => $rubric['nama'] ?? 'Kriteria ' . $kodeCounter,
                'bobot' => (int)($rubric['bobot'] ?? 20),
                'deskripsi' => $rubric['deskripsi'] ?? '',
                'levels' => is_array($rubric['levels'] ?? null) ? $rubric['levels'] : [
                    ['min' => 0, 'max' => 60, 'label' => 'Kurang'],
                    ['min' => 61, 'max' => 75, 'label' => 'Cukup'],
                    ['min' => 76, 'max' => 90, 'label' => 'Baik'],
                    ['min' => 91, 'max' => 100, 'label' => 'Sangat Baik']
                ]
            ];
            $kodeCounter++;
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Rubrik berhasil dihasilkan oleh AI',
            'data' => $formattedRubrics,
            'jenis_praktik' => $jenisPraktik,
            'prompt_preview' => substr($prompt, 0, 100) . '...'
        ]);
    }

    /**
     * ============================================
     * MULTI-ASSESSOR GRADING METHODS
     * ============================================
     */

    /**
     * Get daftar assessor untuk praktik
     */
    public function getAssessors($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        // Get assessor IDs from praktik
        $assessorIds = $praktik->assessor_ids ?? [];

        if (empty($assessorIds)) {
            // Default: hanya pembuat praktik
            $assessorIds = [$praktik->created_by];
        }

        // Get guru data
        $gurus = [];
        foreach ($assessorIds as $id) {
            $guru = $this->master->getGuruById($id);
            if ($guru) {
                $gurus[] = [
                    'id' => $id,
                    'nama' => $guru->nama_guru,
                    'nip' => $guru->nip ?? '-',
                ];
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'assessors' => $gurus
        ]);
    }

    /**
     * Simpan penilaian dari assessor
     */
    public function simpanPenilaianAssessor()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $hasilId = $this->request->getPost('hasil_id');
        $nilai = $this->request->getPost('nilai');
        $rincian = $this->request->getPost('rincian');
        $feedback = $this->request->getPost('feedback');
        $alasan = $this->request->getPost('alasan');

        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hasil tidak ditemukan']);
        }

        $praktik = $this->praktikModel->find($hasil->praktik_id);
        if (!$praktik || !$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        // Get current user as assessor
        $user = $this->authModel->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        if (!$guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        // Simpan penilaian assessor
        $success = $this->multiAssessorModel->simpanPenilaian(
            (int)$hasilId,
            $hasil->praktik_id,
            $hasil->siswa_id,
            $guru->id_guru,
            (float)$nilai,
            $rincian ?? [],
            $feedback,
            100.0
        );

        if ($success) {
            // Log audit
            $this->auditModel->logCreate(
                $hasil->praktik_id,
                $hasilId,
                $hasil->siswa_id,
                $guru->id_guru,
                (float)$nilai,
                $rincian ?? [],
                $alasan
            );

            // Update hasil dengan nilai terbaru (rata-rata dari semua assessor)
            $nilaiAkhir = $this->multiAssessorModel->hitungNilaiAkhir((int)$hasilId);
            $mergedRincian = $this->multiAssessorModel->mergeRincianNilai((int)$hasilId);

            $this->hasilModel->update($hasilId, [
                'nilai_manual' => $nilaiAkhir,
                'nilai_akhir' => $nilaiAkhir,
                'rincian_nilai_rubrik' => json_encode($mergedRincian),
                'status_penilaian' => 'sudah_dinilai',
                'feedback_guru' => $feedback,
            ]);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Penilaian berhasil disimpan',
                'nilai_akhir' => $nilaiAkhir
            ]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan penilaian']);
    }

    /**
     * Get progress penilaian multi-assessor
     */
    public function getProgressPenilaian($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $progress = $this->multiAssessorModel->getProgressPenilaian((int)$praktikId);
        $statistik = $this->multiAssessorModel->getStatistikPraktik((int)$praktikId);

        return $this->response->setJSON([
            'status' => true,
            'progress' => $progress,
            'statistik' => $statistik
        ]);
    }

    /**
     * ============================================
     * AUDIT TRAIL METHODS
     * ============================================
     */

    /**
     * Get audit trail untuk hasil
     */
    public function getAuditTrail($hasilId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $audit = $this->auditModel->getAuditTrail((int)$hasilId);
        $riwayat = $this->auditModel->getRiwayatPerSiswa((int)$hasilId);

        return $this->response->setJSON([
            'status' => true,
            'audit' => $audit,
            'riwayat' => $riwayat
        ]);
    }

    /**
     * Get audit trail untuk praktik (semua siswa)
     */
    public function getAuditTrailPraktik($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $audit = $this->auditModel->getAuditTrailPraktik((int)$praktikId);
        $statistik = $this->auditModel->getStatistikPerAssessor((int)$praktikId);

        return $this->response->setJSON([
            'status' => true,
            'audit' => $audit,
            'statistik_assessor' => $statistik
        ]);
    }

    /**
     * Get anomalous grade changes
     */
    public function getAnomalousChanges($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $anomalies = $this->auditModel->detectAnomalousChanges((int)$praktikId, 20.0);

        return $this->response->setJSON([
            'status' => true,
            'anomalies' => $anomalies,
            'count' => count($anomalies)
        ]);
    }

    /**
     * ============================================
     * REPORT GENERATION METHODS
     * ============================================
     */

    /**
     * Export laporan ke CSV
     */
    public function exportCSV($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $filepath = $this->reportGenerator->exportCSV((int)$praktikId);

        if (!file_exists($filepath)) {
            return redirect()->back()->with('error', 'Gagal generate laporan');
        }

        return $this->response->download($filepath, null)->setFileName(basename($filepath));
    }

    /**
     * Export laporan ke PDF
     */
    public function exportPDF($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $filepath = $this->reportGenerator->exportPDF((int)$praktikId);

        if (!file_exists($filepath)) {
            return redirect()->back()->with('error', 'Gagal generate laporan PDF');
        }

        return $this->response->download($filepath, null)->setFileName(basename($filepath));
    }

    /**
     * Export laporan individual ke PDF
     */
    public function exportLaporanIndividual($hasilId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $filepath = $this->reportGenerator->exportLaporanIndividualPDF((int)$hasilId);

        if (!$filepath || !file_exists($filepath)) {
            return redirect()->back()->with('error', 'Gagal generate laporan individual');
        }

        return $this->response->download($filepath, null)->setFileName(basename($filepath));
    }

    /**
     * Preview laporan (HTML)
     */
    public function previewLaporan($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $laporan = $this->reportGenerator->generateLaporanLengkap((int)$praktikId);
        $setting = $this->dashboard->getSetting();

        return view('cbt/praktik/reports/preview', [
            'laporan' => $laporan,
            'setting' => $setting,
            'judul' => 'Preview Laporan',
            'subjudul' => $laporan['info_praktik']['judul'],
            'user' => $this->authModel->user(),
        ]);
    }

    /**
     * ============================================
     * ANALYSIS & RECOMMENDATIONS METHODS
     * ============================================
     */

    /**
     * Generate analisis otomatis untuk hasil
     */
    public function generateAnalisis($hasilId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil) {
            return $this->response->setJSON(['status' => false, 'message' => 'Hasil tidak ditemukan']);
        }

        $user = $this->authModel->user();

        $success = $this->analisisModel->generateAnalisis(
            (int)$hasilId,
            $hasil->praktik_id,
            $hasil->siswa_id,
            $user->id
        );

        if ($success) {
            $analisis = $this->analisisModel->getAnalisisByHasil((int)$hasilId);
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Analisis berhasil digenerate',
                'analisis' => $analisis
            ]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal generate analisis']);
    }

    /**
     * Batch generate analisis untuk praktik
     */
    public function batchGenerateAnalisis($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $user = $this->authModel->user();
        $result = $this->analisisModel->batchGenerateAnalisis((int)$praktikId, $user->id);

        return $this->response->setJSON([
            'status' => true,
            'message' => "Analisis digenerate: {$result['success']} berhasil, {$result['failed']} gagal",
            'result' => $result
        ]);
    }

    /**
     * Get daftar siswa remedial
     */
    public function getDaftarRemedial($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $remedial = $this->analisisModel->getDaftarRemedial((int)$praktikId);
        $statistik = $this->analisisModel->getStatistikAnalisis((int)$praktikId);
        $rekomendasi = $this->analisisModel->getRekomendasiUmum((int)$praktikId);

        return $this->response->setJSON([
            'status' => true,
            'remedial' => $remedial,
            'statistik' => $statistik,
            'rekomendasi_umum' => $rekomendasi
        ]);
    }

    /**
     * Observasi: simpan hasil observasi guru
     */
    public function simpanObservasi()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $this->observasiModel->initialize();

        $praktik = $this->praktikModel->find($this->request->getPost('praktik_id'));
        if (!$praktik || !$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        $user = $this->authModel->user();

        $data = [
            'praktik_id' => $this->request->getPost('praktik_id'),
            'hasil_id' => $this->request->getPost('hasil_id'),
            'siswa_id' => $this->request->getPost('siswa_id'),
            'guru_id' => $this->getCurrentGuruId($user),
            'aspek_observasi' => $this->request->getPost('aspek_observasi'),
            'catatan_observasi' => $this->request->getPost('catatan_observasi'),
            'skor_observasi' => $this->request->getPost('skor_observasi') ?: null,
            'dimensi_ppp' => $this->request->getPost('dimensi_ppp'),
            'status' => 'aktif',
        ];

        $id = $this->observasiModel->insert($data);
        return $this->response->setJSON(['status' => true, 'id' => $id, 'message' => 'Observasi berhasil disimpan']);
    }

    /**
     * Observasi: halaman input observasi guru
     */
    public function observasiIndex($praktikId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('auth');

        $this->observasiModel->initialize();

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');

        $kelasIds = json_decode($praktik->kelas_id ?? '[]', true);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswa = [];
        if (!empty($kelasIds)) {
            $siswa = $this->master->getSiswaByKelasIds($kelasIds, $tp->id_tp, $smt->id_smt);
        }

        $observasiList = $this->observasiModel->where('praktik_id', (int)$praktikId)->findAll();
        $observasiBySiswa = [];
        foreach ($observasiList as $o) {
            $observasiBySiswa[$o->siswa_id] = $o;
        }

        $data = [
            'judul' => 'Observasi Siswa',
            'subjudul' => $praktik->judul,
            'praktik' => $praktik,
            'siswa' => $siswa,
            'observasiBySiswa' => $observasiBySiswa,
            'setting' => $this->dashboard->getSetting(),
        ];

        return view('cbt/praktik/observasi_index', $data);
    }

    /**
     * P5: daftar projek P5 untuk suatu praktik
     */
    public function p5Index($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $this->p5Model->initialize();
        $this->p5NilaiModel->initialize();

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');
        }

        $projekList = $this->p5Model->getP5ByPraktik((int)$praktikId);
        $nilaiList = $this->p5NilaiModel->getNilaiP5ByPraktik((int)$praktikId);

        // Get siswa list for this praktik
        $kelasIds = $praktik->kelas_id ? $this->praktikModel->decodeKelasId($praktik->kelas_id) : [];
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswaList = [];
        if (!empty($kelasIds)) {
            $siswaList = $this->master->getSiswaByKelasIds($kelasIds, (int)($tp->id_tp ?? 0), (int)($smt->id_smt ?? 0));
        }

        $data = [
            'judul' => 'P5 - ' . $praktik->judul,
            'subjudul' => 'Projek Penguatan Profil Pelajar Pancasila',
            'user' => $this->authModel->user(),
            'praktik' => $praktik,
            'projekList' => $projekList,
            'nilaiList' => $nilaiList,
            'siswaList' => $siswaList,
            'setting' => $this->dashboard->getSetting(),
            'dimensiPpp' => [
                'beriman' => 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia',
                'berkebinekaan' => 'Berkebinekaan Global',
                'gotong_royong' => 'Bergotong Royong',
                'mandiri' => 'Mandiri',
                'bernalar_kritis' => 'Bernalar Kritis',
                'kreatif' => 'Kreatif',
            ],
        ];

        return view('cbt/praktik/p5_index', $data);
    }

    /**
     * P5: simpan projek baru
     */
    public function p5Save()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $this->p5Model->initialize();

        $data = [
            'praktik_id' => $this->request->getPost('praktik_id'),
            'judul_projek' => $this->request->getPost('judul_projek'),
            'deskripsi_projek' => $this->request->getPost('deskripsi_projek'),
            'dimensi_ppp' => $this->request->getPost('dimensi_ppp'),
            'elemen_ppp' => $this->request->getPost('elemen_ppp'),
            'sub_elemen_ppp' => $this->request->getPost('sub_elemen_ppp'),
            'rubrik_p5' => $this->request->getPost('rubrik_p5'),
            'status' => $this->request->getPost('status') ?? 'aktif',
            'created_by' => $this->getCurrentGuruId($this->authModel->user()),
        ];

        $id = $this->request->getPost('id');
        if ($id) {
            $this->p5Model->update($id, $data);
        } else {
            $id = $this->p5Model->insert($data);
        }

        return $this->response->setJSON(['status' => true, 'id' => $id, 'message' => 'Projek P5 berhasil disimpan']);
    }

    /**
     * P5: simpan nilai P5 per siswa
     */
    public function p5SimpanNilai()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $this->p5NilaiModel->initialize();

        $nilai = (float)$this->request->getPost('nilai_akhir_p5');
        $predikat = $nilai >= 86 ? 'Mahir' : ($nilai >= 66 ? 'Cakap' : ($nilai >= 41 ? 'Layak' : 'Baru Berkembang'));

        $data = [
            'p5_id' => $this->request->getPost('p5_id'),
            'praktik_id' => $this->request->getPost('praktik_id'),
            'siswa_id' => $this->request->getPost('siswa_id'),
            'hasil_id' => $this->request->getPost('hasil_id') ?: null,
            'nilai_observasi' => $this->request->getPost('nilai_observasi') ?: null,
            'catatan_observasi' => $this->request->getPost('catatan_observasi'),
            'dimensi_ppp_tercapai' => $this->request->getPost('dimensi_ppp_tercapai'),
            'refleksi_siswa' => $this->request->getPost('refleksi_siswa'),
            'nilai_akhir_p5' => $nilai ?: null,
            'predikat_p5' => $predikat,
            'status' => 'selesai',
        ];

        $existing = $this->p5NilaiModel->where('p5_id', $data['p5_id'])
            ->where('siswa_id', $data['siswa_id'])
            ->first();

        if ($existing) {
            $this->p5NilaiModel->update($existing->id, $data);
        } else {
            $this->p5NilaiModel->insert($data);
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Nilai P5 berhasil disimpan']);
    }

    /**
     * Remedial: jadwalkan remedial untuk siswa
     */
    public function simpanRemedial()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $this->remedialModel->initialize();

        $hasilId = $this->request->getPost('hasil_id');
        $hasil = $this->hasilModel->find($hasilId);

        $data = [
            'praktik_id' => $this->request->getPost('praktik_id'),
            'hasil_id' => $hasilId,
            'siswa_id' => $this->request->getPost('siswa_id'),
            'guru_id' => $this->getCurrentGuruId($this->authModel->user()),
            'nilai_awal' => $hasil->nilai_akhir ?? 0,
            'nilai_remedial' => null,
            'status_remedial' => 'dijadwalkan',
            'jenis_remedial' => $this->request->getPost('jenis_remedial') ?? 'pengulangan',
            'catatan_remedial' => $this->request->getPost('catatan_remedial'),
            'tanggal_remedial' => $this->request->getPost('tanggal_remedial'),
            'batas_waktu' => $this->request->getPost('batas_waktu'),
            'kompetensi_diperbaiki' => $this->request->getPost('kompetensi_diperbaiki'),
        ];

        $id = $this->remedialModel->insert($data);
        return $this->response->setJSON(['status' => true, 'id' => $id, 'message' => 'Remedial berhasil dijadwalkan']);
    }

    /**
     * Remedial: halaman daftar remedial terstruktur
     */
    public function remedialIndex($praktikId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('auth');

        $this->remedialModel->initialize();

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');

        $remedialList = $this->remedialModel->getRemedialByPraktik((int)$praktikId);

        $data = [
            'judul' => 'Remedial Terstruktur',
            'subjudul' => $praktik->judul,
            'praktik' => $praktik,
            'remedialList' => $remedialList,
            'totalRemedial' => count($remedialList),
            'setting' => $this->dashboard->getSetting(),
        ];

        return view('cbt/praktik/remedial_index', $data);
    }

    /**
     * Remedial: update status / nilai remedial
     */
    public function updateRemedial()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $id = $this->request->getPost('id');
        $data = [
            'status_remedial' => $this->request->getPost('status_remedial'),
            'nilai_remedial' => $this->request->getPost('nilai_remedial') ?: null,
            'tanggal_remedial' => $this->request->getPost('tanggal_remedial'),
            'catatan_remedial' => $this->request->getPost('catatan_remedial'),
        ];

        $this->remedialModel->update($id, $data);
        return $this->response->setJSON(['status' => true, 'message' => 'Remedial berhasil diupdate']);
    }

    /**
     * Self-assessment: lihat hasil self-assessment siswa
     */
    public function lihatSelfAssessment($hasilId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $this->selfAssessmentModel->initialize();
        $assessment = $this->selfAssessmentModel->getSelfAssessment((int)$hasilId);

        return $this->response->setJSON(['status' => true, 'data' => $assessment]);
    }

    /**
     * Get rekomendasi untuk siswa
     */
    public function getRekomendasiSiswa($hasilId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $analisis = $this->analisisModel->getAnalisisByHasil((int)$hasilId);

        if (!$analisis) {
            // Generate dulu
            $hasil = $this->hasilModel->find($hasilId);
            if ($hasil) {
                $this->analisisModel->generateAnalisis((int)$hasilId, $hasil->praktik_id, $hasil->siswa_id);
                $analisis = $this->analisisModel->getAnalisisByHasil((int)$hasilId);
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'analisis' => $analisis
        ]);
    }

    /**
     * Rapor naratif Kurikulum Merdeka
     */
    public function narasiRapor($praktikId, $siswaId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('auth');

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) return redirect()->to('cbtpraktik')->with('error', 'Data tidak ditemukan');

        // Gunakan ReportGenerator untuk generate narasi
        $laporan = $this->reportGenerator->generateNarasiRapor((int)$praktikId, (int)$siswaId);
        if (empty($laporan)) return redirect()->to('cbtpraktik')->with('error', 'Data rapor tidak tersedia');

        $setting = $this->dashboard->getSetting();

        $data = [
            'judul' => 'Rapor Naratif',
            'subjudul' => $praktik->judul,
            'laporan' => $laporan,
            'setting' => $setting,
            'praktik' => $praktik,
        ];

        return view('cbt/praktik/rapor_naratif', $data);
    }

    /**
     * Helper: KKTP analysis for controller
     */
    private function getKktpForAnalysis(?float $nilai, ?float $kkm = null): array
    {
        $nilai = $nilai ?? 0;
        $kkm = $kkm ?? 66;
        $intervals = [
            ['nama' => 'Baru Berkembang', 'singkatan' => 'BB', 'min' => 0, 'max' => 40],
            ['nama' => 'Layak', 'singkatan' => 'L', 'min' => 41, 'max' => 65],
            ['nama' => 'Cakap', 'singkatan' => 'C', 'min' => 66, 'max' => 85],
            ['nama' => 'Mahir', 'singkatan' => 'M', 'min' => 86, 'max' => 100],
        ];

        foreach ($intervals as $interval) {
            if ($nilai >= $interval['min'] && $nilai <= $interval['max']) {
                return [
                    'predikat' => $interval['nama'],
                    'singkatan' => $interval['singkatan'],
                    'tercapai' => $nilai >= $kkm,
                    'nilai' => round($nilai, 2),
                ];
            }
        }
        return [
            'predikat' => 'Tidak Teridentifikasi',
            'singkatan' => '-',
            'tercapai' => false,
            'nilai' => round($nilai, 2),
        ];
    }

    /**
     * Ekstrak nilai numerik dari entri rincian (bisa skalar atau array)
     */
    private function getNilaiRincian($v): ?float
    {
        if (is_array($v)) {
            if (isset($v['nilai'])) return (float)$v['nilai'];
            if (isset($v['nilai_sementara'])) return (float)$v['nilai_sementara'];
            $nums = array_filter($v, 'is_numeric');
            return $nums ? (float)reset($nums) : null;
        }
        return is_numeric($v) ? (float)$v : null;
    }

    /**
     * Hitung nilai akhir dari rincian rubrik (weighted average atas SEMUA kriteria).
     * Kriteria yang tidak diisi dianggap 0 agar tidak menggelembungkan nilai akhir.
     * Rumus tunggal & fokus: Σ(nilai×bobot) / Σ(bobot seluruh kriteria).
     */
    private function hitungNilaiAkhir(array $rincianNilai, array $rubrikMap): float
    {
        if (empty($rubrikMap)) {
            return 0.0;
        }

        $weighted = 0.0;
        $sumBobot = 0.0;

        foreach ($rubrikMap as $kriteriaId => $item) {
            $bobot = (float)($item->bobot_persen ?? 0);
            if ($bobot <= 0) {
                continue;
            }

            $nilai = $rincianNilai[$kriteriaId] ?? $rincianNilai[(int)$kriteriaId] ?? null;
            $val = $this->getNilaiRincian($nilai);
            if ($val === null) {
                $val = 0.0; // kriteria tidak diisi = 0
            }

            $weighted += $val * $bobot;
            $sumBobot += $bobot;
        }

        return $sumBobot > 0 ? round($weighted / $sumBobot, 2) : 0.0;
    }

    /**
     * Normalisasi nilai_akhir lama (legacy: tersimpan sebagai total, bukan rata-rata).
     * Dipakai saat menampilkan data lama agar konsisten dengan format 0-100.
     */
    private function normalisasiNilaiAkhir(object $h): void
    {
        if ($h->nilai_akhir !== null && !empty($h->rincian_nilai_rubrik)) {
            $rincian = json_decode((string)$h->rincian_nilai_rubrik, true);
            if (is_array($rincian) && count($rincian) > 0) {
                $sum = array_sum(array_map([$this, 'getNilaiRincian'], $rincian));
                if ($h->nilai_akhir > 100 || abs($h->nilai_akhir - $sum) < 0.1) {
                    $h->nilai_akhir = $h->nilai_akhir / count($rincian);
                }
            }
        }
    }

    /**
     * Ambang tuntas sebuah praktik (KKM jika mode kkm, default KKTP 66).
     */
    private function getKkm(?object $praktik): float
    {
        if ($praktik && ($praktik->kktp_mode ?? null) === 'kkm' && $praktik->kkm !== null && $praktik->kkm !== '') {
            return (float)$praktik->kkm;
        }
        return 66.0;
    }

    /**
     * Resolve id_guru dari user yang sedang login (ganti session('id_guru') yang tak pernah diisi).
     */
    private function getCurrentGuruId($user): ?int
    {
        if (!$user) {
            return null;
        }

        if ($this->isAdmin() || $this->isPanitia()) {
            return null;
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp ?? 0, $smt->id_smt ?? 0);

        return $guru->id_guru ?? null;
    }

    /**
     * Cek akses guru terhadap sebuah praktik (pembuat atau assessor).
     * Admin/Panitia selalu diizinkan.
     */
    private function cekAksesPraktik(object $praktik): bool
    {
        if ($this->isAdmin() || $this->isPanitia()) {
            return true;
        }
        if (!$this->inGroup('guru')) {
            return false;
        }

        $user = $this->authModel->user();
        $guruId = $this->getCurrentGuruId($user);
        if (!$guruId) {
            return false;
        }

        if ((int)$praktik->created_by === $guruId) {
            return true;
        }

        $assessors = $praktik->assessor_ids ?? $praktik->assessors ?? [];
        if (is_string($assessors)) {
            $assessors = json_decode($assessors, true) ?: [];
        }
        if (!is_array($assessors)) {
            $assessors = [];
        }
        return in_array($guruId, array_map('intval', $assessors), true);
    }

    /**
     * Hapus (soft delete) ujian praktik beserta data hasilnya.
     */
    public function delete($id)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $praktik = $this->praktikModel->find((int)$id);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        if (!$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses untuk menghapus ujian praktik ini']);
        }

        if ($this->praktikModel->delete((int)$id)) {
            return $this->response->setJSON(['status' => true, 'message' => 'Ujian praktik berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus ujian praktik']);
    }

    /**
     * Tambah kriteria rubrik untuk suatu praktik.
     */
    public function addRubrik()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $praktikId = (int)$this->request->getPost('praktik_id');
        $praktik = $this->praktikModel->find($praktikId);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }
        if (!$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        $data = [
            'praktik_id' => $praktikId,
            'kode_kriteria' => $this->request->getPost('kode_kriteria') ?: null,
            'nama_kriteria' => $this->request->getPost('nama_kriteria'),
            'deskripsi' => $this->request->getPost('deskripsi') ?: null,
            'bobot_persen' => (float)($this->request->getPost('bobot_persen') ?? 0),
            'tipe_penilaian' => $this->request->getPost('tipe_penilaian') ?? 'manual',
            'urutan' => (int)($this->request->getPost('urutan') ?? 0),
            'dimensi_penilaian' => $this->request->getPost('dimensi_penilaian') ?: null,
            'dimensi_ppp' => $this->request->getPost('dimensi_ppp') ?: null,
        ];

        if (empty($data['nama_kriteria'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama kriteria wajib diisi']);
        }

        $id = $this->rubrikModel->insert($data);
        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan kriteria: ' . json_encode($this->rubrikModel->errors())]);
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Kriteria berhasil ditambahkan', 'rubrik_id' => $id]);
    }

    /**
     * Hapus kriteria rubrik beserta level-nya.
     */
    public function deleteRubrik()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $rubrikId = (int)$this->request->getPost('rubrik_id');
        $rubrik = $this->rubrikModel->find($rubrikId);
        if (!$rubrik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kriteria tidak ditemukan']);
        }

        $praktik = $this->praktikModel->find($rubrik->praktik_id);
        if (!$praktik || !$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        if ($this->rubrikModel->deleteRubrik($rubrikId)) {
            return $this->response->setJSON(['status' => true, 'message' => 'Kriteria berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus kriteria']);
    }

    /**
     * Jalankan penilaian otomatis untuk semua submit yang sudah selesai.
     */
    public function runAutoGrading($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $praktik = $this->praktikModel->find((int)$praktikId);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan']);
        }
        if (!$this->cekAksesPraktik($praktik)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Anda tidak memiliki hak akses']);
        }

        try {
            $result = $this->grading->batchGrade((int)$praktikId);
            return $this->response->setJSON([
                'status' => true,
                'message' => "Auto-grading selesai: {$result['graded']} dinilai, {$result['failed']} gagal",
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Cbtpraktik::runAutoGrading] praktik=' . $praktikId . ': ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Ekspor laporan Excel (alias exportReport).
     */
    public function exportExcel($praktikId)
    {
        return $this->exportReport($praktikId);
    }
}
