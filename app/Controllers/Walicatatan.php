<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\KelasModel;
use App\Models\MasterModel;

class Walicatatan extends BaseController
{
    protected $dashboard;
    protected $dropdown;
    protected $kelas;
    protected $master;

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
        $this->dropdown = new DropdownModel();
        $this->kelas = new KelasModel();
        $this->master = new MasterModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth');
        }

        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'judul' => 'Catatan Wali Kelas',
            'subjudul' => 'Catatan Kelas',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'guru' => $guru,
            'catatan_kelas' => $this->kelas->getCatatanKelas($guru->wali_kelas, $tp->id_tp, $smt->id_smt),
            'catatan_siswa' => $this->kelas->getCatatanSiswa($tp->id_tp, $smt->id_smt, $guru->wali_kelas),
        ];

        return view('members/guru/wali/catatan', $data);
    }

    public function siswa()
    {
        $id_siswa = $this->request->getGet('id_siswa');
        $id_kelas = $this->request->getGet('id_kelas');

        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'judul' => 'Catatan Siswa',
            'subjudul' => 'Catatan Siswa',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'guru' => $guru,
            'siswa' => $this->master->getSiswaById($id_siswa),
            'catatan_siswa' => $this->kelas->getAllCatatanSiswa($id_siswa, $tp->id_tp, $smt->id_smt),
            'id_kelas' => $id_kelas,
        ];

        return view('members/guru/wali/persiswa', $data);
    }

    public function savecatatankelas()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);

        $text = $this->request->getPost('text');
        $level = $this->request->getPost('level');

        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '1',
            'level' => $level,
            'id_kelas' => $guru->wali_kelas,
            'text' => $text,
            'reading' => json_encode([])
        ];

        $insert = $this->master->create('kelas_catatan_wali', $data);
        return $this->response->setJSON($insert);
    }

    public function savecatatansiswa()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);

        $id_siswa = $this->request->getPost('id_siswa');
        $text = $this->request->getPost('text');
        $level = $this->request->getPost('level');

        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '2',
            'level' => $level,
            'id_kelas' => $guru->wali_kelas,
            'id_siswa' => $id_siswa,
            'text' => $text,
            'reading' => json_encode([])
        ];

        $insert = $this->master->create('kelas_catatan_wali', $data);
        return $this->response->setJSON($insert);
    }

    public function hapus($id_catatan)
    {
        $delete = $this->master->delete('kelas_catatan_wali', $id_catatan, 'id_catatan');
        return $this->response->setJSON($delete);
    }
}
