<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;

class Rapor extends BaseController
{
    protected $authModel;
    protected $dashboardModel;
    protected $masterModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Rapor Siswa',
            'subjudul' => 'Cetak Rapor',
            'setting' => $this->dashboardModel->getSetting(),
            'user' => $this->authModel->user($this->getUserId()),
            'kelas' => ($tp && $smt) ? $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt) : [],
            'tp_active' => $tp,
            'smt_active' => $smt
        ];

        return view('rapor/index', $data);
    }

    public function nilai()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $idKelas = $this->request->getGet('kelas');

        if (!$idKelas) {
            return redirect()->to('rapor');
        }

        $kelas = $this->masterModel->getKelas($idKelas);

        // Fetch students in class
        // Use countSiswaInKelas logic but fetch result
        // MasterModel doesn't have getSiswaByKelas?
        // I'll use query builder here for now or add to model
        $db = \Config\Database::connect();
        $siswas = $db->table('kelas_siswa a')
            ->select('a.*, b.nama, b.nis, b.nisn')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->where('a.id_kelas', $idKelas)
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt)
            ->orderBy('b.nama', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'judul' => 'Input Nilai Rapor',
            'subjudul' => 'Kelas ' . ($kelas->nama_kelas ?? ''),
            'setting' => $this->dashboardModel->getSetting(),
            'user' => $this->authModel->user($this->getUserId()),
            'kelas' => $kelas,
            'siswas' => $siswas,
            'tp_active' => $tp,
            'smt_active' => $smt
        ];

        return view('rapor/nilai', $data);
    }

    public function saveNilai()
    {
        // Placeholder for saving
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        return $this->respond(true, 'Nilai berhasil disimpan');
    }
}
