<?php

namespace App\Controllers;

use App\Models\DashboardModel;

use App\Models\CbtModel;
use App\Services\CbtService;
use App\Traits\CbtAuthTrait;

/**
 * Kbm Controller - Kegiatan Belajar Mengajar
 * E-Learning & Hasil Nilai for students
 * 
 * Optimized with CbtService and CbtAuthTrait
 */
class Kbm extends BaseController
{
    use CbtAuthTrait;

    protected DashboardModel $dashboardModel;

    protected CbtModel $cbtModel;
    protected CbtService $cbtService;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();

        $this->cbtModel = new CbtModel();
        $this->cbtService = new CbtService();
    }

    public function index()
    {
        return redirect()->to('kbm/hasil');
    }

    /**
     * Hasil - Student grades/nilai page - Optimized
     */
    public function hasil()
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return redirect()->to('auth/siswa');
        }

        // Use service for cached data
        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return redirect()->to('auth/siswa');
        }

        $period = $this->cbtService->getActivePeriod();
        $logs = []; // Removed ElearningModel usage

        $data = [
            'user' => (object) ['first_name' => session()->get('first_name')],
            'siswa' => $siswa,
            'judul' => 'Nilai',
            'subjudul' => 'Nilai Hasil Belajar',
            'setting' => $this->dashboardModel->getSetting(),
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'tp_active' => $period['tp'],
            'smt_active' => $period['smt'],
            'id_siswa' => $siswa->id_siswa,
            'log' => $logs,
            'nilai_materi' => $logs[1] ?? [],
            'nilai_tugas' => $logs[2] ?? [],
            'running_text' => $this->dashboardModel->getRunningText()
        ];

        return view('members/siswa/elearning/nilai', $data);
    }

    /**
     * Get all nilai via AJAX - Optimized with service
     */
    public function get_all_nilai()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        // Use service for cached student data
        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->respond(false, 'Data siswa tidak ditemukan');
        }

        $period = $this->cbtService->getActivePeriod();
        $tp = $period['tp'];
        $smt = $period['smt'];

        $db = \Config\Database::connect();

        // 0. SELF-HEALING PRE-CHECK (Correct Placement)
        // Find finished exams without scores using verified SESSION ID
        $orphanExams = $db->table('cbt_siswa s')
            ->select('s.id_jadwal')
            ->join('cbt_nilai n', 's.id_siswa = n.id_siswa AND s.id_jadwal = n.id_jadwal', 'left')
            ->where('s.id_siswa', $siswa->id_siswa)
            ->where('s.status', '2') // Finished
            ->where('n.id_nilai IS NULL')
            ->get()->getResult();

        if (!empty($orphanExams)) {
            foreach ($orphanExams as $orphan) {
                log_message('debug', 'Self-Healing: Calculating score for jadwal=' . $orphan->id_jadwal);
                $this->cbtModel->hitungNilai($siswa->id_siswa, $orphan->id_jadwal);
            }
        }

        // Get Schedules by Level OR by Existing Nilai
        // 1. Get IDs from Gradebook
        $nBuilder = $db->table('cbt_nilai')->select('id_jadwal')->where('id_siswa', $siswa->id_siswa);
        $nIds = array_column($nBuilder->get()->getResultArray(), 'id_jadwal');

        // 2. Query Jadwal (Level Match OR All Levels OR Has Score)
        $jBuilder = $db->table('cbt_jadwal a');
        $jBuilder->select('a.*, b.bank_kode, b.bank_kelas, b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, c.nama_jenis, d.kode as kode_mapel, d.nama_mapel');
        $jBuilder->join('cbt_bank_soal b', 'a.id_bank=b.id_bank', 'left');
        $jBuilder->join('cbt_jenis c', 'a.id_jenis=c.id_jenis', 'left');
        $jBuilder->join('master_mapel d', 'b.bank_mapel_id=d.id_mapel', 'left');
        $jBuilder->where('a.id_tp', $tp->id_tp);
        $jBuilder->where('a.id_smt', $smt->id_smt);

        $jBuilder->groupStart();
        $jBuilder->where('b.bank_level', $siswa->level_id ?? 0);
        $jBuilder->orWhere('b.bank_level', 0);
        if (!empty($nIds)) {
            $jBuilder->orWhereIn('a.id_jadwal', $nIds);
        }
        $jBuilder->groupEnd();

        $jBuilder->orderBy('a.tgl_mulai', 'DESC');
        $jadwal = $jBuilder->get()->getResult();

        // Debug log removed for production

        // Get skor per jadwal
        $skor = [];
        $durasi = [];

        foreach ($jadwal as $j) {
            // 1. Attach cbt_siswa log (Required by JS)
            $j->cbtsiswa = $db->table('cbt_siswa')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('id_jadwal', $j->id_jadwal)
                ->get()->getResult();

            // 2. Format Structure (Nested Objects for JS compatibility)
            // JS expects: j.jenis.nama_jenis, j.bank.mapel.kode
            $j->jenis = (object) ['nama_jenis' => $j->nama_jenis];
            $j->bank = (object) [
                'bank_kode' => $j->bank_kode,
                'soal_agama' => '', // Default
                'tampil_pg' => $j->tampil_pg,
                'tampil_kompleks' => $j->tampil_kompleks,
                'tampil_jodohkan' => $j->tampil_jodohkan,
                'tampil_isian' => $j->tampil_isian,
                'tampil_esai' => $j->tampil_esai,
                'mapel' => (object) ['kode' => $j->kode_mapel, 'nama_mapel' => $j->nama_mapel]
            ];

            // ... continue with skor fetch ...
            $s = $db->table('cbt_nilai')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('id_jadwal', $j->id_jadwal)
                ->get()->getRow();

            if (!$s) {
                // Self-Healing: Check if exam is actually finished (status 2) but score missing
                $statusUjian = $db->table('cbt_siswa')
                    ->select('status')
                    ->where('id_siswa', $siswa->id_siswa)
                    ->where('id_jadwal', $j->id_jadwal)
                    ->get()->getRow();

                if ($statusUjian && $statusUjian->status == '2') {
                    // Calculate now!
                    $this->cbtModel->hitungNilai($siswa->id_siswa, $j->id_jadwal);
                    // Fetch again
                    $s = $db->table('cbt_nilai')
                        ->where('id_siswa', $siswa->id_siswa)
                        ->where('id_jadwal', $j->id_jadwal)
                        ->get()->getRow();
                }
            }

            if ($s) {
                $skor[$j->id_jadwal] = $s;
            }

            $d = $db->table('cbt_siswa')
                ->select('mulai, selesai, lama_ujian')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('id_jadwal', $j->id_jadwal)
                ->get()->getRow();
            if ($d) {
                $durasi[$j->id_jadwal] = $d;
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'all_jadwal' => $jadwal,
            'jadwal' => $jadwal,
            'skor' => $skor,
            'durasi' => $durasi,
            'siswa' => $siswa // Added for JS
        ]);
    }

    /**
     * Get nilai per jadwal via AJAX - Optimized
     */
    public function get_nilai_jadwal()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $id_jadwal = (int) $this->request->getGet('jadwal');
        $id_siswa = (int) $this->request->getGet('siswa');

        $db = \Config\Database::connect();

        // Use service for cached jadwal
        $jadwal = $this->cbtService->getJadwalComplete($id_jadwal);
        if (!$jadwal) {
            return $this->jsonError('Jadwal tidak ditemukan');
        }

        // 2. Format Jadwal Object (Bank, etc)
        // Similar to get_all_nilai but single
        $jadwal->bank = $db->table('cbt_bank_soal')->where('id_bank', $jadwal->id_bank)->get()->getRow();
        if ($jadwal->bank) {
            // Fetch mapel info if needed by JS (j.bank.mapel.kode is unused in dialog, but safe to have)
        }

        // 3. Get Nilai & Durasi
        $skorData = $db->table('cbt_nilai')
            ->where('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()->getRowArray(); // Array for easier manipulation

        $durasiData = $db->table('cbt_siswa')
            ->select('mulai, selesai, lama_ujian')
            ->where('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()->getRow();

        // 4. Construct 'sesisiswa' structure expected by JS showNilaiDialog
        $nilaiAkhir = [];
        if ($skorData) {
            // Map flat cbt_nilai columns to nested structure expected by View
            $nilaiAkhir = [
                'dikoreksi' => '0', // Assume auto
                'pg_benar' => $skorData['pg_benar'],
                'pg_nilai' => $skorData['pg_nilai'],
                'skor_asli' => $skorData['pg_nilai'], // Total Score
                // Mock structures for types to prevent JS errors
                'skor_pg' => ['jml_benar' => $skorData['pg_benar'], 'skor_total' => $skorData['pg_nilai']],
                'skor_kompleks' => ['jml_benar' => 0, 'skor_asli' => $skorData['kompleks_nilai'] ?? 0],
                'skor_jodohkan' => ['jml_benar' => 0, 'skor_asli' => 0],
                'skor_isian' => ['jml_benar' => 0, 'skor_asli' => 0],
                'skor_essai' => ['skor_asli' => 0],
                'skor_urutan' => ['jml_benar' => 0, 'skor_asli' => 0],
                'skor_cloze' => ['jml_benar' => 0, 'skor_asli' => 0],
            ];
        }

        $durasiObj = [
            'mulai' => $durasiData->mulai ?? '-',
            'selesai' => $durasiData->selesai ?? '-',
            'durasi' => $durasiData->lama_ujian ?? '-'
        ];

        // Attach to Jadwal
        $jadwal->sesisiswa = (object) [
            'durasi' => (object) $durasiObj,
            'nilai_akhir' => (object) $nilaiAkhir
        ];

        return $this->response->setJSON([
            'success' => true, // Fix key from status to success
            'jadwal' => $jadwal
        ]);
    }


}
