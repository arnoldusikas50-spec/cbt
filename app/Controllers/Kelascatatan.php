<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\KelasModel;
use App\Models\MasterModel;
use App\Models\LogModel;

class Kelascatatan extends BaseController
{
    protected $dashboard;
    protected $dropdown;
    protected $kelas;
    protected $master;
    protected $logging;

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
        $this->dropdown = new DropdownModel();
        $this->kelas = new KelasModel();
        $this->master = new MasterModel();
        $this->logging = new LogModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth');
        }

        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $id_kelas = $this->request->getGet('kelas');
        $id_mapel = $this->request->getGet('mapel');

        $data = [
            'user' => $user,
            'judul' => 'Catatan Guru',
            'subjudul' => 'Catatan Selama Pembelajaran',
            'setting' => $setting,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'kelas_selected' => $id_kelas,
            'mapel_selected' => $id_mapel,
        ];

        if ($id_kelas != null) {
            $cat_kelas = $this->kelas->getCatatanMapelKelas($id_kelas, $id_mapel, $tp->id_tp, $smt->id_smt);
            foreach ($cat_kelas as $ck) {
                $ck->reading = json_decode($ck->reading ?? '[]');
            }
            $data['cat_kelas'] = $cat_kelas;
            $data['cat_siswa'] = $this->kelas->getCatatanMapelSiswa($tp->id_tp, $smt->id_smt, $id_kelas, $id_mapel);
        }

        if (session()->get('level') == 'admin') {
            $data['mapel'] = $this->dropdown->getAllMapel();
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            return view('members/guru/kelas/catatan/data', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = json_decode($mapel_guru->mapel_kelas ?? '[]');

            $arrId = [];
            if ($mapel != null) {
                foreach ($mapel as $mpl) {
                    foreach ($mpl->kelas_mapel as $kls_mpl) {
                        array_push($arrId, $kls_mpl->kelas);
                    }
                }
            }
            $kelasses = [];
            if (count($arrId) > 0)
                $kelasses = $this->dropdown->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId);

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls_mapel) {
                        foreach ($kelasses as $key => $kelass) {
                            if ($kls_mapel->kelas == $key) {
                                $arrKelas[$m->id_mapel][$key] = $kelass;
                            }
                        }
                    }
                }
            }

            $data['mapel'] = $arrMapel;
            $data['kelas'] = $arrKelas;

            return view('members/guru/kelas/catatan/data', $data);
        }
    }

    public function siswa()
    {
        $id_siswa = $this->request->getGet('id');
        $id_mapel = $this->request->getGet('mapel');
        $id_kelas = $this->request->getGet('kelas');

        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Catatan Siswa',
            'subjudul' => 'Catatan Siswa',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'siswa' => $this->master->getSiswaById($id_siswa),
            'catatan_siswa' => $this->kelas->getAllCatatanMapelSiswa($id_siswa, $id_mapel, $tp->id_tp, $smt->id_smt),
            'mapel' => $id_mapel,
            'kelas' => $id_kelas,
        ];

        if (session()->get('level') == 'admin') {
            return view('members/guru/kelas/catatan/persiswa', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            return view('members/guru/kelas/catatan/persiswa', $data);
        }
    }

    public function savecatatankelas()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        $id_kelas = $this->request->getPost('id_kelas');
        $id_mapel = $this->request->getPost('id_mapel');
        $text = $this->request->getPost('text');
        $level = $this->request->getPost('level');

        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '1',
            'id_mapel' => $id_mapel,
            'id_kelas' => $id_kelas,
            'id_guru' => $guru->id_guru,
            'level' => $level,
            'text' => $text,
            'reading' => json_encode([])
        ];

        $insert = $this->master->create('kelas_catatan_mapel', $data);
        if ($insert) {
            $this->logging->saveLog(1, 'Membuat catatan mapel');
        }

        return $this->response->setJSON($insert);
    }

    public function savecatatansiswa()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        $id_siswa = $this->request->getPost('id_siswa');
        $id_mapel = $this->request->getPost('id_mapel');
        $text = $this->request->getPost('text');
        $level = $this->request->getPost('level');

        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '2',
            'id_mapel' => $id_mapel,
            'id_siswa' => $id_siswa,
            'id_guru' => $guru->id_guru,
            'level' => $level,
            'text' => $text,
            'reading' => json_encode([])
        ];

        $insert = $this->master->create('kelas_catatan_mapel', $data);
        if ($insert) {
            $this->logging->saveLog(1, 'Membuat catatan siswa');
        }

        return $this->response->setJSON($insert);
    }

    public function hapus($id_catatan)
    {
        $delete = $this->master->delete('kelas_catatan_mapel', $id_catatan, 'id_catatan');
        $this->logging->saveLog(3, 'Menghapus catatan mapel/siswa');

        return $this->response->setJSON($delete);
    }
}
