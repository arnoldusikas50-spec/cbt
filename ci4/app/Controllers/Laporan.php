<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;

/**
 * Laporan Controller - Migrated from CI3
 */
class Laporan extends BaseController
{
    protected CbtModel $cbtModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
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
            'judul' => 'Laporan',
            'subjudul' => 'Laporan dan Dokumentasi',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'jadwals' => $tp && $smt ? $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt) : [],
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('laporan/index', $data);
    }

    public function beritaAcara()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $db = \Config\Database::connect();

        $beritaAcaras = $db->table('cbt_berita_acara')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();

        $data = [
            'judul' => 'Berita Acara',
            'subjudul' => 'Daftar Berita Acara Ujian',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'berita_acaras' => $beritaAcaras,
            'jadwals' => $tp && $smt ? $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt) : [],
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('laporan/berita_acara', $data);
    }

    public function saveBeritaAcara()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $db = \Config\Database::connect();

        $data = [
            'id_jadwal' => $this->request->getPost('id_jadwal'),
            'tgl_ujian' => $this->request->getPost('tgl_ujian'),
            'waktu_mulai' => $this->request->getPost('waktu_mulai'),
            'waktu_selesai' => $this->request->getPost('waktu_selesai'),
            'catatan' => $this->request->getPost('catatan'),
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $result = $db->table('cbt_berita_acara')->insert($data);

        return $result
            ? $this->respond(true, 'Berita acara berhasil disimpan')
            : $this->respond(false, 'Gagal menyimpan berita acara');
    }

    public function paktaIntegritas()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $db = \Config\Database::connect();

        $paktas = $db->table('cbt_pakta_integritas')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();

        $data = [
            'judul' => 'Pakta Integritas',
            'subjudul' => 'Daftar Pakta Integritas',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'paktas' => $paktas,
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('laporan/pakta_integritas', $data);
    }

    public function savePaktaIntegritas()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $db = \Config\Database::connect();

        $data = [
            'nama' => $this->request->getPost('nama'),
            'jabatan' => $this->request->getPost('jabatan'),
            'instansi' => $this->request->getPost('instansi'),
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $result = $db->table('cbt_pakta_integritas')->insert($data);

        return $result
            ? $this->respond(true, 'Pakta integritas berhasil disimpan')
            : $this->respond(false, 'Gagal menyimpan pakta integritas');
    }
    public function cetakBeritaAcara($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $db = \Config\Database::connect();

        // Initial query to get id_jadwal
        $ba = $db->table('cbt_berita_acara')->where('id_berita_acara', $id)->get()->getRow();

        if (!$ba) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        // Get details with joins
        $query = $db->table('cbt_berita_acara a')
            ->select('a.*, d.nama_mapel, d.kode as kode_mapel, b.tgl_mulai')
            ->join('cbt_jadwal b', 'a.id_jadwal = b.id_jadwal', 'left')
            ->join('cbt_bank_soal c', 'b.id_bank = c.id_bank', 'left')
            ->join('master_mapel d', 'c.id_mapel = d.id_mapel', 'left')
            ->where('a.id_berita_acara', $id);

        $berita = $query->get()->getRow();

        // Helpers for day naming
        $days = ['Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'];
        $dayName = date('D', strtotime($berita->tgl_ujian));

        $data = [
            'berita' => $berita,
            'setting' => $this->dashboardModel->getSetting(),
            'hari' => $days[$dayName] ?? '-',
            'tanggal' => date('d F Y', strtotime($berita->tgl_ujian))
        ];

        return view('laporan/cetak_berita_acara', $data);
    }

    public function cetakPaktaIntegritas($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $db = \Config\Database::connect();
        $pakta = $db->table('cbt_pakta_integritas')->where('id_pakta', $id)->get()->getRow();

        if (!$pakta) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'pakta' => $pakta,
            'setting' => $this->dashboardModel->getSetting()
        ];

        return view('laporan/cetak_pakta_integritas', $data);
    }
}
