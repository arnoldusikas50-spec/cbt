<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\KelasModel;
use App\Models\MasterModel;

class Walistruktur extends BaseController
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
        $struktur = $this->kelas->getStrukturKelas($guru->wali_kelas);

        if ($struktur == null) {
            $struktur = (object) $this->kelas->dummyStruktur();
        }

        $siswa = $this->master->getDataSiswaByKelas($tp->id_tp, $smt->id_smt, $guru->wali_kelas);
        $siswas = ['' => 'Pilih Siswa'];
        foreach ($siswa as $s) {
            $siswas[$s->id_siswa] = $s->nama;
        }

        $data = [
            'user' => $user,
            'judul' => 'Struktur Organisasi',
            'subjudul' => 'Struktur Organisasi Kelas',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'guru' => $guru,
            'gurus' => $this->dropdown->getAllGuru(),
            'siswas' => $siswas,
            'struktur' => $struktur,
            'id_kelas' => $guru->wali_kelas,
        ];

        return view('members/guru/wali/struktur', $data);
    }

    public function save()
    {
        $data = [
            'id_kelas' => $this->request->getPost('id_kelas'),
            'ketua' => $this->request->getPost('ketua'),
            'wakil_ketua' => $this->request->getPost('wakil_ketua'),
            'sekretaris_1' => $this->request->getPost('sekretaris_1'),
            'sekretaris_2' => $this->request->getPost('sekretaris_2'),
            'bendahara_1' => $this->request->getPost('bendahara_1'),
            'bendahara_2' => $this->request->getPost('bendahara_2'),
            'sie_ekstrakurikuler' => $this->request->getPost('sie_ekstrakurikuler'),
            'sie_upacara' => $this->request->getPost('sie_upacara'),
            'sie_olahraga' => $this->request->getPost('sie_olahraga'),
            'sie_keagamaan' => $this->request->getPost('sie_keagamaan'),
            'sie_keamanan' => $this->request->getPost('sie_keamanan'),
            'sie_ketertiban' => $this->request->getPost('sie_ketertiban'),
            'sie_kebersihan' => $this->request->getPost('sie_kebersihan'),
            'sie_keindahan' => $this->request->getPost('sie_keindahan'),
            'sie_kesehatan' => $this->request->getPost('sie_kesehatan'),
            'sie_kekeluargaan' => $this->request->getPost('sie_kekeluargaan'),
            'sie_humas' => $this->request->getPost('sie_humas')
        ];

        // Replace logic
        $db = \Config\Database::connect();
        $builder = $db->table('kelas_struktur');

        $check = $builder->where('id_kelas', $data['id_kelas'])->get()->getRow();
        if ($check) {
            $result = $builder->where('id_kelas', $data['id_kelas'])->update($data);
        } else {
            $result = $builder->insert($data);
        }

        return $this->response->setJSON($result);
    }
}
