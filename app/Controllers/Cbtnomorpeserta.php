<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\CbtModel;
use App\Models\DropdownModel;
use App\Models\KelasModel;
use App\Models\LogModel;

/**
 * Cbtnomorpeserta Controller - Migrated from CI3
 * Generate nomor peserta ujian
 */
class Cbtnomorpeserta extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;
    protected CbtModel $cbtModel;
    protected DropdownModel $dropdownModel;
    protected KelasModel $kelasModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
        $this->cbtModel = new CbtModel();
        $this->dropdownModel = new DropdownModel();
        $this->kelasModel = new KelasModel();
        $this->logModel = new LogModel();
    }

    /**
     * Index page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->isGuru()) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak');
        }

        $user = $this->getUser();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Nomor Peserta',
            'subjudul' => 'Generate Nomor Peserta Ujian',
            'setting' => $this->dashboardModel->getSetting(),
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'ruang' => $this->dropdownModel->getAllRuang(),
            'sesi' => $this->dropdownModel->getAllSesi(),
        ];

        if ($this->isAdmin()) {
            $data['kelas'] = $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt);
        } else {
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;

            $arrId = [];

            if ($guru) {
                $mapel_guru = $this->kelasModel->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);

                if ($mapel_guru && isset($mapel_guru->mapel_kelas)) {
                    $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas);

                    if ($mapel && is_array($mapel) && count($mapel) > 0) {
                        if (isset($mapel[0]->kelas_mapel) && is_array($mapel[0]->kelas_mapel)) {
                            foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                                if (isset($id_mapel->kelas)) {
                                    $arrId[] = $id_mapel->kelas;
                                }
                            }
                        }
                    }
                }
            }

            $data['kelas'] = count($arrId) > 0
                ? $this->dropdownModel->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId)
                : [];
        }

        return view('cbt/nomorpeserta/data', $data);
    }

    /**
     * Save nomor peserta
     */
    public function saveNomor()
    {
        $input = json_decode($this->request->getPost('siswa'));

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $ids_nomor = [];
        foreach ($input as $in) {
            $ids_nomor[] = $in->id . $tp->id_tp . $smt->id_smt;
        }

        $update = [];
        $insert = [];

        if (count($ids_nomor) > 0) {
            $arrNomor = $this->cbtModel->cekNomorPeserta($ids_nomor);

            foreach ($input as $in) {
                $nomorAda = $arrNomor[$in->id . $tp->id_tp . $smt->id_smt] ?? null;

                if ($nomorAda) {
                    $update[] = [
                        'id_nomor' => $nomorAda->id_nomor,
                        'id_siswa' => $in->id,
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'nomor_peserta' => $in->nomor,
                    ];
                } else {
                    $insert[] = [
                        'id_nomor' => $in->id . $tp->id_tp . $smt->id_smt,
                        'id_siswa' => $in->id,
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'nomor_peserta' => $in->nomor,
                    ];
                }
            }
        }

        $db = \Config\Database::connect();

        if (count($insert) > 0) {
            $db->table('cbt_nomor_peserta')->insertBatch($insert);
        }

        if (count($update) > 0) {
            $db->table('cbt_nomor_peserta')->updateBatch($update, 'id_nomor');
        }

        $duplikat = $this->cbtModel->hapus_duplikat_nomor_peserta();

        $this->logModel->saveLog(1, 'Membuat nomor peserta ujian');

        return $this->response->setJSON([
            'status' => true,
            'insert' => $insert,
            'update' => $update,
            'duplikat' => $duplikat
        ]);
    }

    /**
     * Reset nomor peserta
     */
    public function resetNomor()
    {
        $input = json_decode($this->request->getGet('kelas'));
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $siswas = $this->cbtModel->getSiswaByKelasArray($tp->id_tp, $smt->id_smt, $input);
        $id_siswa_array = array_column($siswas, 'id_siswa');
        $arrNomor = $this->cbtModel->getAllNomorPeserta($tp->id_tp, $smt->id_smt, $id_siswa_array);

        $update = [];
        foreach ($siswas as $siswa) {
            if (isset($arrNomor[$siswa->id_siswa])) {
                $update[] = [
                    'id_nomor' => $arrNomor[$siswa->id_siswa]->id_nomor,
                    'nomor_peserta' => '',
                ];
            }
        }

        if (count($update) > 0) {
            $db = \Config\Database::connect();
            $db->table('cbt_nomor_peserta')->updateBatch($update, 'id_nomor');
        }

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Get siswa by kelas
     */
    public function getSiswaKelas()
    {
        $kelas = json_decode($this->request->getPost('kelas'));
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if ($this->isAdmin()) {
            $siswas = $this->cbtModel->getSiswaByKelasArray($tp->id_tp, $smt->id_smt, $kelas);
        } else {
            $user = $this->getUser();
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

            $arrId = [];

            if ($guru) {
                $mapel_guru = $this->kelasModel->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);

                if ($mapel_guru && isset($mapel_guru->mapel_kelas)) {
                    $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas);

                    if ($mapel && is_array($mapel) && count($mapel) > 0) {
                        if (isset($mapel[0]->kelas_mapel) && is_array($mapel[0]->kelas_mapel)) {
                            foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                                if (isset($id_mapel->kelas)) {
                                    $arrId[] = $id_mapel->kelas;
                                }
                            }
                        }
                    }
                }
            }

            $siswas = $this->cbtModel->getSiswaByKelasArray($tp->id_tp, $smt->id_smt, $arrId);
        }

        $id_siswa_array = array_column($siswas, 'id_siswa');
        $arrNomor = [];

        if (count($id_siswa_array) > 0) {
            $arrNomor = $this->cbtModel->getAllNomorPeserta($tp->id_tp, $smt->id_smt, $id_siswa_array);
        }

        return $this->response->setJSON([
            'kelas' => $kelas,
            'siswa' => $siswas,
            'nomor' => $arrNomor
        ]);
    }
}
