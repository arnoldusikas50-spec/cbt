<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\CbtSiswaModel;
use App\Models\DashboardModel;

/**
 * Cbtstatus Controller - Migrated from CI3 Cbtstatus
 * CBT monitoring for admin/guru/panitia
 */
class Cbtstatus extends BaseController
{
    protected CbtModel $cbtModel;
    protected CbtSiswaModel $cbtSiswaModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->cbtSiswaModel = new CbtSiswaModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Monitoring page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        // Only admin, guru, panitia can access
        if ($this->inGroup('siswa')) {
            return redirect()->to('cbtsiswa/cbt');
        }

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Monitoring CBT',
            'subjudul' => 'Status Ujian Real-time',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'ruangs' => $this->cbtModel->getAllRuang(),
            'sesis' => $this->cbtModel->getAllSesi(),
            'jadwals' => $tp && $smt ? $this->cbtModel->getActiveJadwal($tp->id_tp, $smt->id_smt) : [],
            'user' => (object) [
                'first_name' => session()->get('first_name'),
                'username' => session()->get('username'),
            ]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/status/data')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Get siswa currently taking exam
     */
    public function getSiswaUjian()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $idJadwal = $this->request->getGet('id_jadwal');

            if (!$idJadwal) {
                return $this->respond(false, 'ID Jadwal diperlukan');
            }

            // Get jadwal info
            $jadwal = $this->cbtModel->getJadwal((int) $idJadwal);

            if (!$jadwal) {
                return $this->respond(false, 'Jadwal tidak ditemukan');
            }

            // Get statistics
            $stats = $this->cbtSiswaModel->getStatistikJadwal((int) $idJadwal);

            // Get students taking exam
            $sedangUjian = $this->cbtSiswaModel->getSiswaSedangUjian((int) $idJadwal);
            $selesai = $this->cbtSiswaModel->getSiswaSelesaiUjian((int) $idJadwal);

            return $this->response->setJSON([
                'status' => true,
                'jadwal' => $jadwal,
                'stats' => $stats,
                'sedang_ujian' => $sedangUjian,
                'selesai' => $selesai
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get siswa belum ujian
     */
    public function getSiswaBelumUjian()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $idJadwal = $this->request->getGet('id_jadwal');

            if (!$idJadwal) {
                return $this->respond(false, 'ID Jadwal diperlukan');
            }

            $jadwal = $this->cbtModel->getJadwal((int) $idJadwal);

            if (!$jadwal) {
                return $this->respond(false, 'Jadwal tidak ditemukan');
            }

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            // Get all siswa in the jadwal kelas
            $bankKelas = @unserialize($jadwal->bank_kelas ?? '');
            if (!is_array($bankKelas)) {
                $bankKelas = json_decode($jadwal->bank_kelas ?? '[]', true) ?? [];
            }

            $kelasIds = array_column($bankKelas, 'kelas_id');

            if (empty($kelasIds)) {
                return $this->response->setJSON([
                    'status' => true,
                    'belum_ujian' => []
                ]);
            }

            $db = \Config\Database::connect();

            // Get siswa who haven't started exam yet
            $siswaList = $db->table('kelas_siswa a')
                ->select('a.id_siswa, b.nama, b.nis, b.nisn, c.nama_kelas')
                ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
                ->join('master_kelas c', 'a.id_kelas = c.id_kelas')
                ->whereIn('a.id_kelas', $kelasIds)
                ->where('a.id_tp', $tp->id_tp)
                ->where('a.id_smt', $smt->id_smt)
                ->get()
                ->getResult();

            // Filter out siswa who have started
            $belumUjian = [];
            foreach ($siswaList as $siswa) {
                $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, (int) $idJadwal);
                if (!$cbtSiswa || !$cbtSiswa->mulai) {
                    $belumUjian[] = $siswa;
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'belum_ujian' => $belumUjian
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Reset login izin for siswa
     */
    public function resetLogin()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            // Only admin and panitia can reset
            if (!$this->isAdmin() && !$this->inGroup('panitia')) {
                return $this->respond(false, 'Akses ditolak');
            }

            $idCbtSiswa = (int) $this->request->getPost('id_cbt_siswa');

            if (!$idCbtSiswa) {
                return $this->respond(false, 'ID tidak valid');
            }

            $result = $this->cbtSiswaModel->resetLogin($idCbtSiswa);

            if ($result) {
                return $this->respond(true, 'Reset login berhasil');
            }

            return $this->respond(false, 'Gagal reset login');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Reset waktu for siswa
     */
    public function resetWaktu()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            // Only admin can reset time
            if (!$this->isAdmin()) {
                return $this->respond(false, 'Akses ditolak');
            }

            $idCbtSiswa = (int) $this->request->getPost('id_cbt_siswa');
            $tambahMenit = (int) ($this->request->getPost('tambah_menit') ?? 15);

            if (!$idCbtSiswa) {
                return $this->respond(false, 'ID tidak valid');
            }

            $result = $this->cbtSiswaModel->resetWaktu($idCbtSiswa, $tambahMenit);

            if ($result) {
                return $this->respond(true, "Waktu ditambah {$tambahMenit} menit");
            }

            return $this->respond(false, 'Gagal reset waktu');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }
}
