<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\CbtModel;
use App\Models\LogModel;

class Cbtnomorpeserta extends BaseController
{
    protected MasterModel $master;
    protected DashboardModel $dashboard;
    protected CbtModel $cbt;
    protected LogModel $logging;

    public function __construct()
    {
        $this->master = new MasterModel();
        $this->dashboard = new DashboardModel();
        $this->cbt = new CbtModel();
        $this->logging = new LogModel();
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
            'judul' => 'Nomor Peserta',
            'subjudul' => 'Generate Nomor Peserta Ujian',
            'setting' => $this->dashboard->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboard->getTahun(),
            'smt' => $this->dashboard->getSemester(),
            'ruang' => $this->cbt->getAllRuang(),
            'sesi' => $this->cbt->getAllSesi(),
        ];

        if ($this->isAdmin()) {
            $data['kelas'] = $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt);
        } else if ($this->inGroup('guru')) {
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
                                if (isset($k->id_kelas))
                                    $idsKelas[] = $k->id_kelas;
                                elseif (isset($k->kelas))
                                    $idsKelas[] = $k->kelas;
                            }
                        }
                    }
                }
                $data['kelas'] = !empty($idsKelas) ? $this->master->getAllKelasByArrayId((int) $tp->id_tp, (int) $smt->id_smt, array_unique($idsKelas)) : [];
            } else {
                $data['kelas'] = [];
            }
        } else {
            return redirect()->to('dashboard');
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/nomorpeserta/data')
            . view('_templates/dashboard/_footer');
    }

    public function getSiswaKelas()
    {
        try {
            $kelas = json_decode($this->request->getPost('kelas'), true);
            if (empty($kelas)) {
                return $this->response->setJSON(['siswa' => [], 'nomor' => []]);
            }

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $siswas = $this->cbt->getSiswaByKelasArray((int) $tp->id_tp, (int) $smt->id_smt, $kelas);

            $id_siswa_array = array_column($siswas, 'id_siswa');
            $arrNomor = [];
            if (!empty($id_siswa_array)) {
                $arrNomor = $this->cbt->getAllNomorPeserta((int) $tp->id_tp, (int) $smt->id_smt, $id_siswa_array);
            }

            return $this->response->setJSON([
                'kelas' => $kelas,
                'siswa' => $siswas,
                'nomor' => $arrNomor
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveNomor()
    {
        try {
            $input = json_decode($this->request->getPost('siswa'), true);
            if (empty($input)) {
                return $this->response->setJSON(['status' => false, 'message' => 'No data received']);
            }

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $ids_nomor = [];
            foreach ($input as $in) {
                $ids_nomor[] = $in['id'] . $tp->id_tp . $smt->id_smt;
            }

            $update = [];
            $insert = [];

            if (!empty($ids_nomor)) {
                $arrNomor = $this->cbt->cekNomorPeserta($ids_nomor);
                foreach ($input as $in) {
                    $key = $in['id'] . $tp->id_tp . $smt->id_smt;
                    $nomorAda = $arrNomor[$key] ?? null;

                    $data = [
                        'id_nomor' => $key,
                        'id_siswa' => $in['id'],
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'nomor_peserta' => $in['nomor'],
                    ];

                    if ($nomorAda) {
                        $update[] = $data;
                    } else {
                        $insert[] = $data;
                    }
                }
            }

            $db = \Config\Database::connect();
            if (!empty($insert)) {
                $db->table('cbt_nomor_peserta')->insertBatch($insert);
            }
            if (!empty($update)) {
                $db->table('cbt_nomor_peserta')->updateBatch($update, 'id_nomor');
            }

            $this->cbt->hapus_duplikat_nomor_peserta();
            $this->logging->saveLog(3, 'Membuat nomor peserta ujian');

            return $this->response->setJSON(['status' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function resetNomor()
    {
        try {
            $input = json_decode($this->request->getGet('kelas'), true);
            if (empty($input)) {
                return $this->response->setJSON(['status' => false, 'message' => 'No class selected']);
            }

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $siswas = $this->cbt->getSiswaByKelasArray((int) $tp->id_tp, (int) $smt->id_smt, $input);
            $id_siswa_array = array_column($siswas, 'id_siswa');

            if (!empty($id_siswa_array)) {
                $arrNomor = $this->cbt->getAllNomorPeserta((int) $tp->id_tp, (int) $smt->id_smt, $id_siswa_array);
                $update = [];
                foreach ($id_siswa_array as $id_siswa) {
                    if (isset($arrNomor[$id_siswa])) {
                        $update[] = [
                            'id_nomor' => $arrNomor[$id_siswa]->id_nomor,
                            'nomor_peserta' => '',
                        ];
                    }
                }

                if (!empty($update)) {
                    $db = \Config\Database::connect();
                    $db->table('cbt_nomor_peserta')->updateBatch($update, 'id_nomor');
                }
            }

            return $this->response->setJSON(['status' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
