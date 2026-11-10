<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\CbtPraktikModel;
use App\Models\CbtPraktikHasilModel;
use App\Models\CbtPraktikLogModel;
use App\Models\CbtPraktikFileModel;
use App\Models\CbtPraktikRubrikModel;
use App\Models\CbtPraktikSelfAssessmentModel;
use App\Models\CbtPraktikObservasiModel;
use App\Models\CbtPraktikRemedialModel;
use App\Models\CbtPraktikUmpanBalikModel;
use App\Models\CbtPraktikPortofolioModel;
use App\Libraries\PraktikGrading;

class CbtPraktikSiswa extends BaseController
{
    protected $dashboardModel;
    protected $praktikModel;
    protected $hasilModel;
    protected $logModel;
    protected $fileModel;
    protected $rubrikModel;
    protected $selfAssessmentModel;
    protected $observasiModel;
    protected $remedialModel;
    protected $umpanBalikModel;
    protected $portofolioModel;
    protected $grading;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->praktikModel = new CbtPraktikModel();
        $this->hasilModel = new CbtPraktikHasilModel();
        $this->logModel = new CbtPraktikLogModel();
        $this->fileModel = new CbtPraktikFileModel();
        $this->rubrikModel = new CbtPraktikRubrikModel();
        $this->selfAssessmentModel = new CbtPraktikSelfAssessmentModel();
        $this->observasiModel = new CbtPraktikObservasiModel();
        $this->remedialModel = new CbtPraktikRemedialModel();
        $this->umpanBalikModel = new CbtPraktikUmpanBalikModel();
        $this->portofolioModel = new CbtPraktikPortofolioModel();
        $this->grading = new PraktikGrading();
    }

    /**
     * Get siswa data from DashboardModel (same as Dashboard controller)
     */
    protected function getSiswaData()
    {
        $username = session()->get('username');
        if (!$username) {
            return null;
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        return $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);
    }

    public function index()
    {
        if (!$this->isLoggedIn()) return redirect()->to('login');
        
        try {
            $username = session()->get('username');
            if (!$username) {
                die('<pre>ERROR: No username in session</pre>');
            }

            // Get active academic year and semester
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();
            
            if (!$tp || !$smt) {
                die('<pre>ERROR: tp or smt is null<br>tp: ' . print_r($tp, true) . '<br>smt: ' . print_r($smt, true) . '</pre>');
            }

            // Get siswa data like Dashboard controller does
            $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);
            
            if (!$siswa) {
                return redirect()->to('dashboard')->with('error', 'Data siswa tidak ditemukan');
            }
            
            $praktikList = $this->praktikModel->getActivePraktikForSiswa(
                (int)$siswa->id_siswa,
                (int)$siswa->id_kelas
            );

            return view('members/siswa/cbt/praktik', [
                'judul' => 'Ujian Praktik',
                'subjudul' => 'Daftar Ujian',
                'siswa' => $siswa,
                'praktik' => $praktikList,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[CbtPraktikSiswa] Error: ' . $e->getMessage());
            return redirect()->to('dashboard')->with('error', 'Terjadi kesalahan sistem');
        }
    }

    public function konfirmasi($praktikId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('login');

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return redirect()->to('dashboard')->with('error', 'Data siswa tidak ditemukan');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);

        if (!$praktik) {
            return redirect()->to('cbtsiswa/praktik')->with('error', 'Ujian tidak ditemukan');
        }

        $availability = $this->praktikModel->isAvailableForSiswa(
            (int)$praktikId,
            (int)$siswa->id_kelas,
            (int)$siswa->id_siswa
        );

        if (!$availability['available']) {
            return redirect()->to('cbtsiswa/praktik')->with('error', $availability['message']);
        }

        return view('members/siswa/cbt/praktik_konfirmasi', [
            'judul' => 'Konfirmasi Ujian',
            'subjudul' => $praktik->judul,
            'siswa' => $siswa,
            'praktik' => $praktik,
            'can_start' => $availability['can_start'] ?? false,
            'can_continue' => $availability['can_continue'] ?? false,
            'hasil_id' => $availability['hasil_id'] ?? null,
        ]);
    }

    public function mulai()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $praktikId = $this->request->getPost('praktik_id');

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ujian tidak ditemukan']);
        }

        $availability = $this->praktikModel->isAvailableForSiswa(
            (int)$praktikId,
            (int)$siswa->id_kelas,
            (int)$siswa->id_siswa
        );

        if (!$availability['available']) {
            return $this->response->setJSON(['status' => false, 'message' => $availability['message']]);
        }

        $hasil = $this->hasilModel->mulaiUjian(
            (int)$praktikId,
            (int)$siswa->id_siswa,
            (int)$siswa->id_kelas
        );

        if (!$hasil) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memulai ujian']);
        }

        $this->logModel->logActivity(
            (int)$praktikId,
            (int)$siswa->id_siswa,
            CbtPraktikLogModel::AKTIVITAS_MULAI,
            $hasil->id
        );

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Ujian dimulai',
            'redirect' => base_url('cbtsiswa/praktik/ujian/' . $praktikId)
        ]);
    }

    public function ujian($praktikId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('login');

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return redirect()->to('dashboard')->with('error', 'Data siswa tidak ditemukan');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);

        if (!$praktik) {
            return redirect()->to('cbtsiswa/praktik')->with('error', 'Ujian tidak ditemukan');
        }

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);

        if (!$hasil || $hasil->status_pengerjaan === CbtPraktikHasilModel::STATUS_SELESAI) {
            return redirect()->to('cbtsiswa/praktik')->with('error', 'Ujian tidak tersedia atau sudah selesai');
        }

        $sisaWaktu = 0;
        if ($praktik->durasi_menit > 0) {
            $elapsed = time() - strtotime($hasil->waktu_mulai);
            $sisaWaktu = ($praktik->durasi_menit * 60) - $elapsed;
            if ($sisaWaktu <= 0) {
                return redirect()->to('cbtsiswa/praktik/auto_submit/' . $praktikId);
            }
        }

        return view('members/siswa/cbt/praktik_ujian', [
            'judul' => $praktik->judul,
            'subjudul' => 'Sedang Mengerjakan',
            'siswa' => $siswa,
            'praktik' => $praktik,
            'hasil' => $hasil,
            'rubriks' => $this->rubrikModel->getRubrikPraktik((int)$praktikId, true),
            'sisa_waktu' => $sisaWaktu,
        ]);
    }

    public function saveDraft()
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false]);

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $praktikId = $this->request->getPost('praktik_id');
        $hasilId = $this->request->getPost('hasil_id');

        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil || $hasil->siswa_id != $siswa->id_siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $data = [];
        $fields = ['jawaban_teks', 'kode_jawaban', 'url_simulasi', 'catatan_siswa', 'bahasa_pemrograman'];
        foreach ($fields as $field) {
            if ($this->request->getPost($field) !== null) {
                $data[$field] = $this->request->getPost($field);
            }
        }

        if (!empty($data)) {
            $this->hasilModel->update($hasilId, $data);
            $this->logModel->logActivity(
                (int)$praktikId,
                (int)$siswa->id_siswa,
                CbtPraktikLogModel::AKTIVITAS_SIMPAN_DRAFT,
                (int)$hasilId,
                ['fields' => array_keys($data)]
            );
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Draft disimpan']);
    }

    public function uploadFile()
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false]);

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $praktikId = $this->request->getPost('praktik_id');
        $hasilId = $this->request->getPost('hasil_id');

        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil || $hasil->siswa_id != $siswa->id_siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $file = $this->request->getFile('file_praktik');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => false, 'message' => 'File tidak valid']);
        }

        $praktik = $this->praktikModel->find($praktikId);
        $batasan = json_decode($praktik->batasan_file ?? '{}', true);
        $maxSize = $batasan['max_size'] ?? 10485760;
        $allowedExt = $batasan['extensions'] ?? ['pdf', 'doc', 'docx', 'zip', 'jpg', 'png'];

        if ($file->getSize() > $maxSize) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ukuran file terlalu besar']);
        }

        if (!in_array(strtolower($file->getExtension()), $allowedExt)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tipe file tidak diizinkan']);
        }

        $uploadPath = WRITEPATH . 'uploads/praktik/' . $praktikId . '/';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

        $newName = $siswa->id_siswa . '_' . time() . '_' . $file->getRandomName();
        $file->move($uploadPath, $newName);

        $this->fileModel->insert([
            'hasil_id' => $hasilId,
            'praktik_id' => $praktikId,
            'siswa_id' => $siswa->id_siswa,
            'nama_file' => $newName,
            'nama_file_asli' => $file->getClientName(),
            'tipe_file' => $file->getMimeType(),
            'ukuran_file' => $file->getSize(),
            'path_file' => 'uploads/praktik/' . $praktikId . '/' . $newName,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'File berhasil diupload',
            'file_name' => $newName
        ]);
    }

    public function deleteFile()
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false]);

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $fileId = (int)$this->request->getPost('file_id');
        $file = $this->fileModel->find($fileId);
        if (!$file || $file->siswa_id != $siswa->id_siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $hasil = $this->hasilModel->find($file->hasil_id);
        if (!$hasil || $hasil->status_pengerjaan === CbtPraktikHasilModel::STATUS_SELESAI) {
            return $this->response->setJSON(['status' => false, 'message' => 'File tidak dapat dihapus setelah ujian disubmit']);
        }

        $path = $this->fileModel->deleteFile($fileId);

        if ($path) {
            $fullPath = WRITEPATH . $path;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        return $this->response->setJSON(['status' => true, 'message' => 'File berhasil dihapus']);
    }

    public function submit()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $praktikId = $this->request->getPost('praktik_id');
        $hasilId = $this->request->getPost('hasil_id');

        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil || $hasil->siswa_id != $siswa->id_siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        if ($hasil->status_pengerjaan === CbtPraktikHasilModel::STATUS_SELESAI) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ujian sudah selesai']);
        }

        // Validasi server-side: praktik aktif, rentang tanggal, dan waktu masih tersisa
        $praktik = $this->praktikModel->find($praktikId);
        if (!$praktik) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ujian tidak ditemukan']);
        }

        $now = date('Y-m-d H:i:s');
        if (!in_array($praktik->status, ['aktif', 'sedang_berlangsung'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ujian praktik belum aktif atau sudah selesai']);
        }
        if ($now < $praktik->tanggal_mulai) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ujian praktik belum dimulai']);
        }
        if ($now > $praktik->tanggal_selesai) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ujian praktik sudah berakhir']);
        }
        if ($praktik->durasi_menit > 0) {
            $elapsed = time() - strtotime($hasil->waktu_mulai);
            if ($elapsed > $praktik->durasi_menit * 60) {
                return $this->response->setJSON(['status' => false, 'message' => 'Waktu pengerjaan telah habis, ujian disubmit otomatis']);
            }
        }

        $data = [];
        $fields = ['jawaban_teks', 'kode_jawaban', 'url_simulasi', 'catatan_siswa', 'bahasa_pemrograman'];
        foreach ($fields as $field) {
            if ($this->request->getPost($field) !== null) {
                $data[$field] = $this->request->getPost($field);
            }
        }

        // Plagiarism check
        if (!empty($data['kode_jawaban']) || !empty($data['jawaban_teks'])) {
            $text = $data['kode_jawaban'] ?? $data['jawaban_teks'];
            $similar = $this->hasilModel->checkPlagiarism(
                (int)$praktikId,
                (int)$siswa->id_siswa,
                $text
            );

            if (!empty($similar)) {
                $maxSim = max(array_column($similar, 'similarity'));
                $data['status_plagiasi'] = $maxSim > 80 ? 'terindikasi' : ($maxSim > 50 ? 'dicurigai' : 'aman');
                $data['persentase_plagiasi'] = $maxSim;
            }
        }

        if ($this->hasilModel->submitUjian((int)$hasilId, $data)) {
            $this->logModel->logActivity(
                (int)$praktikId,
                (int)$siswa->id_siswa,
                CbtPraktikLogModel::AKTIVITAS_SUBMIT,
                (int)$hasilId
            );

            // Trigger auto-grading bila diaktifkan guru
            if (!empty($praktik->aktifkan_penilaian_otomatis)) {
                try {
                    $this->grading->gradeSubmission((int)$praktikId, (int)$hasilId);
                } catch (\Throwable $e) {
                    log_message('error', '[CbtPraktikSiswa::submit] auto-grading gagal: ' . $e->getMessage());
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Ujian berhasil disubmit',
                'redirect' => base_url('cbtsiswa/praktik')
            ]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal submit']);
    }

    /**
     * Submit otomatis saat waktu habis / meninggalkan ujian
     */
    public function autoSubmit($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('login');
        }

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return redirect()->to('dashboard')->with('error', 'Data siswa tidak ditemukan');
        }

        $praktik = $this->praktikModel->find((int)$praktikId);
        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);

        if (!$praktik || !$hasil) {
            return redirect()->to('cbtsiswa/praktik')->with('error', 'Ujian tidak ditemukan');
        }

        if ($hasil->status_pengerjaan !== CbtPraktikHasilModel::STATUS_SELESAI) {
            $this->hasilModel->submitUjian((int)$hasil->id, []);

            $this->logModel->logActivity(
                (int)$praktikId,
                (int)$siswa->id_siswa,
                CbtPraktikLogModel::AKTIVITAS_SUBMIT,
                (int)$hasil->id,
                ['otomatis' => true, 'alasan' => 'waktu_habis']
            );

            if (!empty($praktik->aktifkan_penilaian_otomatis)) {
                try {
                    $this->grading->gradeSubmission((int)$praktikId, (int)$hasil->id);
                } catch (\Throwable $e) {
                    log_message('error', '[CbtPraktikSiswa::autoSubmit] auto-grading gagal: ' . $e->getMessage());
                }
            }
        }

        return redirect()->to('cbtsiswa/praktik')->with('success', 'Waktu habis, jawaban disubmit otomatis');
    }

    /**
     * Feedback: ambil daftar umpan balik guru untuk hasil siswa
     */
    public function getFeedback($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);
        if (!$hasil) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data ujian tidak ditemukan']);
        }

        $list = $this->umpanBalikModel->getUmpanBalikByHasil($hasil->id);

        // Tandai belum dibaca menjadi sudah dibaca
        foreach ($list as $fb) {
            if (!$fb->telah_dibaca) {
                $this->umpanBalikModel->tandaiDibaca($fb->id);
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => array_map(function ($fb) {
                $fb->telah_dibaca = 1;
                return $fb;
            }, $list),
        ]);
    }

    /**
     * Feedback: siswa membalas umpan balik guru
     */
    public function replyFeedback()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $umpanBalikId = $this->request->getPost('umpan_balik_id');
        $balasan = trim((string)$this->request->getPost('balasan'));

        if (empty($balasan)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Balasan tidak boleh kosong']);
        }

        $fb = $this->umpanBalikModel->find($umpanBalikId);
        if (!$fb || $fb->siswa_id != $siswa->id_siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        if ($this->umpanBalikModel->balasUmpanBalik((int)$umpanBalikId, $balasan)) {
            return $this->response->setJSON(['status' => true, 'message' => 'Balasan terkirim']);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal mengirim balasan']);
    }

    /**
     * Portofolio: tambah item portofolio siswa
     */
    public function addPortfolioItem()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $praktikId = $this->request->getPost('praktik_id');
        $hasilId = $this->request->getPost('hasil_id');

        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil || $hasil->siswa_id != $siswa->id_siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $data = [
            'hasil_id' => $hasilId,
            'praktik_id' => $praktikId,
            'siswa_id' => $siswa->id_siswa,
            'judul_item' => $this->request->getPost('judul_item'),
            'deskripsi_item' => $this->request->getPost('deskripsi_item'),
            'tipe_item' => $this->request->getPost('tipe_item') ?? 'dokumen',
            'path_item' => $this->request->getPost('path_item'),
            'urutan' => (int)($this->request->getPost('urutan') ?? 0),
            'is_highlight' => $this->request->getPost('is_highlight') ? 1 : 0,
            'catatan_siswa' => $this->request->getPost('catatan_siswa'),
        ];

        if (empty($data['judul_item'])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Judul item wajib diisi']);
        }

        $item = $this->portofolioModel->tambahItem($data);
        if ($item) {
            return $this->response->setJSON(['status' => true, 'message' => 'Item portofolio ditambahkan', 'item' => $item]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menambah item portofolio']);
    }

    /**
     * Portofolio: ambil daftar item portofolio siswa
     */
    public function getPortfolioItems($praktikId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);
        }

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);
        if (!$hasil) {
            return $this->response->setJSON(['status' => false, 'data' => []]);
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => $this->portofolioModel->getPortofolioByHasil($hasil->id),
        ]);
    }

    public function logActivity()
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false]);

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $praktikId = $this->request->getPost('praktik_id');
        $tipe = $this->request->getPost('tipe_aktivitas');
        $detail = $this->request->getPost('detail');

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);

        $this->logModel->logActivity(
            (int)$praktikId,
            (int)$siswa->id_siswa,
            $tipe,
            $hasil ? $hasil->id : null,
            $detail ? json_decode($detail, true) : null
        );

        return $this->response->setJSON(['status' => true]);
    }

    public function checkTime()
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false]);

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);
        }

        $praktikId = $this->request->getGet('praktik_id');

        $praktik = $this->praktikModel->find($praktikId);
        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);

        if (!$hasil || $hasil->status_pengerjaan === CbtPraktikHasilModel::STATUS_SELESAI) {
            return $this->response->setJSON(['status' => false, 'ended' => true, 'message' => 'Ujian telah berakhir']);
        }

        $sisaWaktu = 0;
        $warning = false;

        if ($praktik->durasi_menit > 0) {
            $elapsed = time() - strtotime($hasil->waktu_mulai);
            $sisaWaktu = ($praktik->durasi_menit * 60) - $elapsed;

            if ($sisaWaktu <= 0) {
                return $this->response->setJSON(['status' => false, 'ended' => true, 'message' => 'Waktu habis']);
            }

            if ($sisaWaktu <= 300) $warning = true;
        }

        return $this->response->setJSON([
            'status' => true,
            'sisa_waktu' => $sisaWaktu,
            'warning' => $warning,
            'ended' => false
        ]);
    }

    /**
     * Self-assessment: siswa menilai dirinya sendiri
     */
    public function selfAssessment($praktikId)
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);

        $siswa = $this->getSiswaData();
        if (!$siswa) return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);

        $this->selfAssessmentModel->initialize();

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);
        if (!$hasil) return $this->response->setJSON(['status' => false, 'message' => 'Data ujian tidak ditemukan']);

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'praktik_id' => (int)$praktikId,
                'hasil_id' => $hasil->id,
                'siswa_id' => (int)$siswa->id_siswa,
                'rubrik_id' => $this->request->getPost('rubrik_id') ?: null,
                'nilai_diri' => $this->request->getPost('nilai_diri') ?: null,
                'refleksi' => $this->request->getPost('refleksi'),
                'kesulitan' => $this->request->getPost('kesulitan'),
                'strategi' => $this->request->getPost('strategi'),
                'dimensi_ppp_teridentifikasi' => $this->request->getPost('dimensi_ppp'),
                'status' => 'submitted',
            ];

            $existing = $this->selfAssessmentModel->getSelfAssessment($hasil->id);
            if ($existing) {
                $this->selfAssessmentModel->update($existing->id, $data);
            } else {
                $this->selfAssessmentModel->insert($data);
            }

            return $this->response->setJSON(['status' => true, 'message' => 'Self-assessment berhasil disimpan']);
        }

        $assessment = $this->selfAssessmentModel->getSelfAssessment($hasil->id);
        return $this->response->setJSON(['status' => true, 'data' => $assessment]);
    }

    /**
     * Self-assessment view (HTML page)
     */
    public function selfAssessmentView($praktikId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('login');

        $siswa = $this->getSiswaData();
        if (!$siswa) return redirect()->to('dashboard')->with('error', 'Data siswa tidak ditemukan');

        $this->selfAssessmentModel->initialize();

        $praktik = $this->praktikModel->find((int)$praktikId);
        if (!$praktik) return redirect()->to('cbtsiswa/praktik')->with('error', 'Ujian tidak ditemukan');

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);
        $assessment = $hasil ? $this->selfAssessmentModel->getSelfAssessment($hasil->id) : null;
        $setting = $this->dashboardModel->getSetting();

        return view('members/siswa/cbt/self_assessment', [
            'judul' => 'Self-Assessment',
            'subjudul' => $praktik->judul,
            'praktik' => $praktik,
            'hasil' => $hasil,
            'assessment' => $assessment,
            'setting' => $setting,
        ]);
    }

    /**
     * Observasi: menampilkan hasil observasi guru
     */
    public function observasi($praktikId)
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);

        $siswa = $this->getSiswaData();
        if (!$siswa) return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);

        $this->observasiModel->initialize();

        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);
        if (!$hasil) return $this->response->setJSON(['status' => false, 'message' => 'Data ujian tidak ditemukan']);

        $observasiList = $this->observasiModel->getObservasiByHasil($hasil->id);
        return $this->response->setJSON(['status' => true, 'data' => $observasiList]);
    }

    /**
     * Remedial: menampilkan status remedial siswa
     */
    public function remedial($praktikId)
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['status' => false, 'message' => 'Sesi berakhir']);

        $siswa = $this->getSiswaData();
        if (!$siswa) return $this->response->setJSON(['status' => false, 'message' => 'Data siswa tidak ditemukan']);

        $this->remedialModel->initialize();

        $remedial = $this->remedialModel->getRemedialBySiswa((int)$praktikId, (int)$siswa->id_siswa);
        return $this->response->setJSON(['status' => true, 'data' => $remedial]);
    }

    /**
     * View exam result for student
     */
    public function hasil($praktikId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('login');

        $siswa = $this->getSiswaData();
        if (!$siswa) {
            return redirect()->to('dashboard')->with('error', 'Data siswa tidak ditemukan');
        }

        $praktik = $this->praktikModel->getDetailPraktik((int)$praktikId);
        if (!$praktik) {
            return redirect()->to('cbtsiswa/praktik')->with('error', 'Ujian tidak ditemukan');
        }

        // Get student's result
        $hasil = $this->hasilModel->getHasilSiswa((int)$praktikId, (int)$siswa->id_siswa);
        
        if (!$hasil || $hasil->status_pengerjaan !== CbtPraktikHasilModel::STATUS_SELESAI) {
            return redirect()->to('cbtsiswa/praktik')->with('error', 'Hasil ujian belum tersedia');
        }

        // Get rubrik details
        $rubrik = $this->rubrikModel->getRubrikPraktik((int)$praktikId, true);
        
        // Parse rincian nilai
        $rincianNilai = json_decode($hasil->rincian_nilai_rubrik ?? '[]', true) ?: [];

        // Get setting for layout
        $setting = $this->dashboardModel->getSetting();

        return view('members/siswa/cbt/praktik_hasil', [
            'judul' => 'Hasil Ujian Praktik',
            'subjudul' => $praktik->judul,
            'siswa' => $siswa,
            'praktik' => $praktik,
            'hasil' => $hasil,
            'rubrik' => $rubrik,
            'rincian_nilai' => $rincianNilai,
            'setting' => $setting,
        ]);
    }
}
