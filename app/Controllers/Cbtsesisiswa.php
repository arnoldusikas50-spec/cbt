<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\CbtModel;
use App\Models\LogModel;
use App\Models\AuthModel;

class Cbtsesisiswa extends BaseController
{
    protected MasterModel $master;
    protected DashboardModel $dashboard;
    protected CbtModel $cbt;
    protected LogModel $logging;
    protected AuthModel $authModel;

    public function __construct()
    {
        $this->master = new MasterModel();
        $this->dashboard = new DashboardModel();
        $this->cbt = new CbtModel();
        $this->logging = new LogModel();
        $this->authModel = new AuthModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        if (!$tp || !$smt) {
            return "Tahun Pelajaran atau Semester belum di set.";
        }

        $user = $this->authModel->user();
        $data = [
            'user' => $user,
            'judul' => 'Atur Ruang dan Sesi Siswa',
            'subjudul' => 'Ruang dan Sesi Siswa',
            'setting' => $this->dashboard->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
        ];

        $kls = $this->request->getGet('kls');
        $kelas_selected = $kls ?? '0';

        $siswas = [];
        if ($kelas_selected != '0') {
            $siswas = $this->cbt->getRuangSesiSiswa((int) $kelas_selected, (int) $tp->id_tp, (int) $smt->id_smt);
        }

        $data['siswas'] = $siswas;
        $data['kelas_selected'] = $kelas_selected;
        $data['sesi'] = $this->cbt->getAllSesi();
        $data['ruang'] = $this->cbt->getAllRuang();

        $is_admin = $this->isAdmin();
        $role = $this->session->get('role') ?? '0';
        $is_panitia = ($role === '4' || $this->isPanitia());

        $data['is_admin'] = $is_admin || $is_panitia;

        if ($is_admin || $is_panitia) {
            $data['kelas'] = $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt);
        } else if ($this->inGroup('guru') || $role === '2') {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);
            if ($guru) {
                $jabatan = $this->master->getGuruMapelKelas((int) $guru->id_guru, (int) $tp->id_tp, (int) $smt->id_smt);
                $mapel_json = $jabatan->mapel_kelas ?? '[]';
                $mapel_arr = json_decode($mapel_json);

                $idsKelas = [];
                if (is_array($mapel_arr)) {
                    foreach ($mapel_arr as $m) {
                        if (isset($m->kelas_mapel) && is_array($m->kelas_mapel)) {
                            foreach ($m->kelas_mapel as $k) {
                                if (isset($k->kelas))
                                    $idsKelas[] = $k->kelas;
                            }
                        }
                    }
                }

                $data['kelas'] = !empty($idsKelas) ? $this->master->getAllKelasByArrayId((int) $tp->id_tp, (int) $smt->id_smt, $idsKelas) : [];
            } else {
                $data['kelas'] = [];
            }
        } else {
            return redirect()->to('dashboard');
        }

        return view('cbt/sesisiswa/data', $data);
    }

    public function editsesisiswa()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
            }

            $rs = $this->request->getPost('ruang-sesi');
            $kls = $this->request->getPost('kelas');
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            if (!$rs || !$kls || !$tp || !$smt) {
                return $this->response->setJSON(['status' => false, 'message' => 'Invalid data']);
            }

            $siswas = $this->cbt->getRuangSesiSiswa((int) $kls, (int) $tp->id_tp, (int) $smt->id_smt);
            $map_siswa = [];
            foreach ($siswas as $siswa) {
                $map_siswa[$siswa->siswa_id] = $siswa;
            }

            $update = [];
            $insert = [];

            foreach ($rs as $id_siswa => $klss) {
                foreach ($klss as $id_kelas => $val) {
                    $data = [
                        'siswa_id' => $id_siswa,
                        'kelas_id' => $id_kelas,
                        'ruang_id' => $val['ruang'],
                        'sesi_id' => $val['sesi'],
                        'tp_id' => $tp->id_tp,
                        'smt_id' => $smt->id_smt
                    ];

                    if (isset($map_siswa[$id_siswa]) && $map_siswa[$id_siswa]->id_sesi_siswa) {
                        $data['id_sesi_siswa'] = $map_siswa[$id_siswa]->id_sesi_siswa;
                        $update[] = $data;
                    } else {
                        $insert[] = $data;
                    }
                }
            }

            $db = \Config\Database::connect();
            $db->transStart();
            if (!empty($insert)) {
                $db->table('cbt_sesi_siswa')->insertBatch($insert);
            }
            if (!empty($update)) {
                $db->table('cbt_sesi_siswa')->updateBatch($update, 'id_sesi_siswa');
            }
            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['status' => false, 'message' => 'Database operation failed']);
            }

            $this->logging->saveLog(3, 'Mengatur ruang dan sesi ujian siswa');
            return $this->response->setJSON(['status' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
