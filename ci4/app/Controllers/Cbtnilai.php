<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\CbtSiswaModel;
use App\Models\DashboardModel;

/**
 * Cbtnilai Controller - Migrated from CI3
 */
class Cbtnilai extends BaseController
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

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Nilai Ujian',
            'subjudul' => 'Lihat Nilai CBT',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'jadwals' => $tp && $smt ? $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt) : [],
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('cbt/nilai/data', $data)
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $idJadwal = $this->request->getGet('id_jadwal');

            if (!$idJadwal) {
                return $this->response->setJSON(['data' => []]);
            }

            $db = \Config\Database::connect();
            $nilais = $db->table('cbt_siswa a')
                ->select('a.*, b.nama, b.nis, c.nama_kelas')
                ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
                ->join('kelas_siswa d', 'a.id_siswa = d.id_siswa')
                ->join('master_kelas c', 'd.id_kelas = c.id_kelas')
                ->where('a.id_jadwal', $idJadwal)
                ->where('a.selesai IS NOT NULL')
                ->orderBy('b.nama', 'ASC')
                ->get()
                ->getResult();

            $data = [];
            foreach ($nilais as $i => $n) {
                $data[] = [
                    'no' => $i + 1,
                    'id_cbt_siswa' => $n->id_cbt_siswa,
                    'nama' => $n->nama ?? '-',
                    'nis' => $n->nis ?? '-',
                    'kelas' => $n->nama_kelas ?? '-',
                    'jml_benar' => $n->jml_benar ?? 0,
                    'jml_salah' => $n->jml_salah ?? 0,
                    'nilai' => $n->nilai ?? 0,
                ];
            }

            return $this->response->setJSON(['data' => $data]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    public function detail(int $id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $db = \Config\Database::connect();

        $cbtSiswa = $db->table('cbt_siswa a')
            ->select('a.*, b.nama, b.nis, c.nama_kelas, d.bank_nama, e.nama_mapel')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('kelas_siswa f', 'a.id_siswa = f.id_siswa')
            ->join('master_kelas c', 'f.id_kelas = c.id_kelas')
            ->join('cbt_jadwal g', 'a.id_jadwal = g.id_jadwal')
            ->join('cbt_bank_soal d', 'g.id_bank = d.id_bank')
            ->join('master_mapel e', 'g.id_mapel = e.id_mapel', 'left')
            ->where('a.id_cbt_siswa', $id)
            ->get()
            ->getRow();

        if (!$cbtSiswa) {
            return redirect()->to('cbtnilai')->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'judul' => 'Detail Nilai',
            'subjudul' => $cbtSiswa->nama,
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'cbt_siswa' => $cbtSiswa,
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('cbt/nilai/detail', $data)
            . view('_templates/dashboard/_footer');
    }
    public function export()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $idJadwal = $this->request->getGet('id_jadwal');

        if (!$idJadwal) {
            return redirect()->back()->with('error', 'Pilih jadwal terlebih dahulu');
        }

        $db = \Config\Database::connect();

        $jadwal = $db->table('cbt_jadwal a')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank')
            ->select('b.bank_nama')
            ->where('a.id_jadwal', $idJadwal)
            ->get()->getRow();

        $nilais = $db->table('cbt_siswa a')
            ->select('a.*, b.nama, b.nis, c.nama_kelas')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('kelas_siswa d', 'a.id_siswa = d.id_siswa')
            ->join('master_kelas c', 'd.id_kelas = c.id_kelas')
            ->where('a.id_jadwal', $idJadwal)
            ->where('a.selesai IS NOT NULL')
            ->orderBy('c.nama_kelas', 'ASC')
            ->orderBy('b.nama', 'ASC')
            ->get()
            ->getResult();

        $filename = 'Nilai_Ujian_' . str_replace(' ', '_', $jadwal->bank_nama ?? 'CBT') . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // Header
        fputcsv($out, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Benar', 'Salah', 'Nilai']);

        // Data
        foreach ($nilais as $i => $n) {
            fputcsv($out, [
                $i + 1,
                $n->nis,
                $n->nama,
                $n->nama_kelas,
                $n->jml_benar,
                $n->jml_salah,
                $n->nilai
            ]);
        }

        fclose($out);
        exit;
    }
}
