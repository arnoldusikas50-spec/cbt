<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\CbtModel;
use App\Models\CbtSiswaModel;
use App\Models\DashboardModel;

/**
 * Cbtsiswa Controller - Migrated from CI3 Cbtsiswa
 * Handles student CBT exam interface
 */
class Cbtsiswa extends BaseController
{
    protected AuthModel $authModel;
    protected CbtModel $cbtModel;
    protected CbtSiswaModel $cbtSiswaModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->cbtModel = new CbtModel();
        $this->cbtSiswaModel = new CbtSiswaModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Index - redirect to cbt
     */
    public function index()
    {
        return redirect()->to('cbtsiswa/cbt');
    }

    /**
     * Main CBT exam page
     */
    public function cbt()
    {
        // Must be logged in as siswa
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth/siswa');
        }

        if (!$this->inGroup('siswa')) {
            return redirect()->to('dashboard');
        }

        $user = $this->authModel->user($this->getUserId());
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt || !$user) {
            return view('errors/disable_login');
        }

        // Get siswa data
        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return view('errors/disable_login', [
                'setting' => $setting,
                'message' => 'Data siswa tidak ditemukan'
            ]);
        }

        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'judul' => 'CBT Ujian',
            'subjudul' => 'Daftar Ujian'
        ];

        return view('cbt/index', $data);
    }

    /**
     * Get available exams for student via AJAX
     */
    public function getCbtSiswa()
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $user = $this->authModel->user($this->getUserId());
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt || !$user) {
                return $this->respond(false, 'Session expired');
            }

            $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

            if (!$siswa) {
                return $this->respond(false, 'Data siswa tidak ditemukan');
            }

            // Get available jadwal for today
            $jadwals = $this->cbtModel->getDataJadwalByTgl(date('Y-m-d'));

            $availableExams = [];

            foreach ($jadwals as $jadwal) {
                // Check if siswa's kelas is included
                $bankKelas = @unserialize($jadwal->bank_kelas);
                if (!is_array($bankKelas)) {
                    $bankKelas = json_decode($jadwal->bank_kelas, true) ?? [];
                }

                $kelasIds = array_column($bankKelas, 'kelas_id');

                if (in_array($siswa->id_kelas, $kelasIds)) {
                    // Check exam status for this student
                    $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $jadwal->id_jadwal);

                    $jadwal->status_siswa = 'belum';
                    $jadwal->mulai = null;
                    $jadwal->selesai = null;

                    if ($cbtSiswa) {
                        if ($cbtSiswa->selesai) {
                            $jadwal->status_siswa = 'selesai';
                        } elseif ($cbtSiswa->mulai) {
                            $jadwal->status_siswa = 'sedang';
                        }
                        $jadwal->mulai = $cbtSiswa->mulai;
                        $jadwal->selesai = $cbtSiswa->selesai;
                        $jadwal->lama_ujian = $cbtSiswa->lama_ujian ?? null;
                    }

                    $availableExams[] = $jadwal;
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'siswa' => $siswa,
                'jadwals' => $availableExams
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Confirm and enter exam
     */
    public function konfirmasi(int $idJadwal)
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->respond(false, 'Unauthorized');
        }

        $user = $this->authModel->user($this->getUserId());
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return $this->respond(false, 'Data siswa tidak ditemukan');
        }

        $jadwal = $this->cbtModel->getJadwal($idJadwal);

        if (!$jadwal) {
            return $this->respond(false, 'Jadwal tidak ditemukan');
        }

        // Check if token is required and valid
        $token = $this->cbtModel->getToken();
        $inputToken = $this->request->getPost('token');

        if ($token && $token->token && $token->token !== $inputToken) {
            return $this->respond(false, 'Token tidak valid');
        }

        // Check time window
        $now = date('Y-m-d H:i:s');
        if ($now < $jadwal->tgl_mulai || $now > $jadwal->tgl_selesai) {
            return $this->respond(false, 'Waktu ujian belum dimulai atau sudah berakhir');
        }

        return $this->respond(true, 'Konfirmasi berhasil', [
            'jadwal' => $jadwal,
            'siswa' => $siswa
        ]);
    }

    /**
     * Validate and start exam
     */
    public function validasiJadwal()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $idJadwal = (int) $this->request->getPost('id_jadwal');

            $user = $this->authModel->user($this->getUserId());
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();
            $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

            if (!$siswa) {
                return $this->respond(false, 'Data siswa tidak ditemukan');
            }

            $jadwal = $this->cbtModel->getJadwal($idJadwal);

            if (!$jadwal) {
                return $this->respond(false, 'Jadwal tidak ditemukan');
            }

            // Start or continue exam
            $idCbtSiswa = $this->cbtSiswaModel->startExam(
                $siswa->id_siswa,
                $idJadwal,
                $jadwal->id_bank
            );

            // Log activity
            $this->cbtModel->saveLog($siswa->id_siswa, $idJadwal, 1, 'Memulai ujian');

            return $this->respond(true, 'Ujian dimulai', [
                'id_cbt_siswa' => $idCbtSiswa,
                'jadwal' => $jadwal,
                'durasi' => $jadwal->durasi ?? 60
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, 'Gagal memulai ujian: ' . $e->getMessage());
        }
    }

    /**
     * Save answer
     */
    public function saveJawaban()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $idJadwal = (int) $this->request->getPost('id_jadwal');
            $idSoal = (int) $this->request->getPost('id_soal');
            $jawaban = $this->request->getPost('jawaban');
            $ragu = $this->request->getPost('ragu') === '1';

            $user = $this->authModel->user($this->getUserId());
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();
            $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

            if (!$siswa) {
                return $this->respond(false, 'Session expired');
            }

            // Validate exam session
            $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
            if (!$cbtSiswa || $cbtSiswa->selesai) {
                return $this->respond(false, 'Ujian sudah selesai');
            }

            // Save or update jawaban
            $db = \Config\Database::connect();

            $existing = $db->table('cbt_jawaban')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('id_jadwal', $idJadwal)
                ->where('id_soal', $idSoal)
                ->get()
                ->getRow();

            if ($existing) {
                $db->table('cbt_jawaban')
                    ->where('id_jawaban', $existing->id_jawaban)
                    ->update([
                        'jawaban' => $jawaban,
                        'ragu' => $ragu ? '1' : '0',
                        'waktu' => date('Y-m-d H:i:s')
                    ]);
            } else {
                $db->table('cbt_jawaban')->insert([
                    'id_siswa' => $siswa->id_siswa,
                    'id_jadwal' => $idJadwal,
                    'id_soal' => $idSoal,
                    'jawaban' => $jawaban,
                    'ragu' => $ragu ? '1' : '0',
                    'waktu' => date('Y-m-d H:i:s')
                ]);
            }

            return $this->respond(true, 'Jawaban tersimpan');
        } catch (\Exception $e) {
            return $this->respond(false, 'Gagal menyimpan jawaban: ' . $e->getMessage());
        }
    }

    /**
     * Toggle ragu-ragu status
     */
    public function toggleRagu()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Invalid request');
        }

        $idJadwal = (int) $this->request->getPost('id_jadwal');
        $idSoal = (int) $this->request->getPost('id_soal');
        $ragu = $this->request->getPost('ragu') === '1';

        $user = $this->authModel->user($this->getUserId());
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return $this->respond(false, 'Session expired');
        }

        // Validate exam session
        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if (!$cbtSiswa || $cbtSiswa->selesai) {
            return $this->respond(false, 'Ujian sudah selesai');
        }

        $db = \Config\Database::connect();

        $db->table('cbt_jawaban')
            ->where('id_siswa', $siswa->id_siswa)
            ->where('id_jadwal', $idJadwal)
            ->where('id_soal', $idSoal)
            ->update(['ragu' => $ragu ? '1' : '0']);

        return $this->respond(true, 'Status ragu diubah');
    }

    /**
     * End exam
     */
    public function selesaiUjian()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $idJadwal = (int) $this->request->getPost('id_jadwal');

            $user = $this->authModel->user($this->getUserId());
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();
            $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

            if (!$siswa) {
                return $this->respond(false, 'Session expired');
            }

            $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);

            if (!$cbtSiswa) {
                return $this->respond(false, 'Data ujian tidak ditemukan');
            }

            // End exam
            $this->cbtSiswaModel->endExam($cbtSiswa->id_cbt_siswa);

            // Log activity
            $this->cbtModel->saveLog($siswa->id_siswa, $idJadwal, 2, 'Menyelesaikan ujian');

            return $this->respond(true, 'Ujian selesai', [
                'redirect' => site_url('cbtsiswa/cbt')
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, 'Gagal menyelesaikan ujian: ' . $e->getMessage());
        }
    }

    /**
     * Leave CBT (force logout from exam)
     */
    public function leavecbt()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth/siswa');
        }

        // Log the leave action if in exam
        $idJadwal = $this->request->getPost('id_jadwal');

        if ($idJadwal) {
            $user = $this->authModel->user($this->getUserId());
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();
            $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

            if ($siswa) {
                $this->cbtModel->saveLog($siswa->id_siswa, (int) $idJadwal, 3, 'Keluar dari ujian');
            }
        }

        return $this->respond(true, 'OK', [
            'redirect' => site_url('cbtsiswa/cbt')
        ]);
    }
    public function ujian($idJadwal)
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return redirect()->to('auth/siswa');
        }

        $user = $this->authModel->user($this->getUserId());
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return redirect()->to('cbtsiswa/cbt')->with('error', 'Sesi siswa tidak valid');
        }

        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if (!$cbtSiswa || $cbtSiswa->selesai) {
            return redirect()->to('cbtsiswa/cbt');
        }

        $jadwal = $this->cbtModel->getJadwal($idJadwal);
        if (!$jadwal) {
            return redirect()->to('cbtsiswa/cbt');
        }

        // Questions
        $questions = $this->cbtModel->getSoalByBank($jadwal->id_bank, $jadwal->acak_soal ?? '0');

        // Answers
        $answers = $this->cbtModel->getJawabanSiswa($siswa->id_siswa, $idJadwal);

        // Calculate timer
        $durasiDetik = ($jadwal->durasi ?? 60) * 60;
        $mulai = strtotime($cbtSiswa->mulai ?? date('Y-m-d H:i:s'));
        $selesaiJadwal = strtotime($jadwal->tgl_selesai);
        $elapsed = time() - $mulai;
        $sisaWaktu = $durasiDetik - $elapsed;

        // Cap by jadwal end time
        $sisaJadwal = $selesaiJadwal - time();
        if ($sisaWaktu > $sisaJadwal) {
            $sisaWaktu = $sisaJadwal;
        }

        if ($sisaWaktu <= 0) {
            // Should auto finish
            // For now let JS handle it or redirect?
            // Usually we show page and JS submits finish
        }

        $data = [
            'judul' => 'Ujian Berlangsung',
            'subjudul' => $jadwal->nama_mapel ?? $jadwal->bank_nama,
            'user' => $user,
            'siswa' => $siswa,
            'setting' => $this->dashboardModel->getSetting(),
            'jadwal' => $jadwal,
            'questions' => $questions,
            'answers' => $answers,
            'sisa_waktu' => $sisaWaktu,
            'tp_active' => $tp,
            'smt_active' => $smt
        ];

        return view('cbt/ujian', $data);
    }
}
