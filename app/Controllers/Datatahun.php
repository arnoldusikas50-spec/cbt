<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\LogModel;
use App\Models\AuthModel;

class Datatahun extends BaseController
{
    protected MasterModel $master;
    protected DashboardModel $dashboard;
    protected LogModel $logging;
    protected AuthModel $authModel;

    public function __construct()
    {
        $this->master = new MasterModel();
        $this->dashboard = new DashboardModel();
        $this->logging = new LogModel();
        $this->authModel = new AuthModel();
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $this->authModel->user(),
            'judul' => 'Tahun Pelajaran dan Semester',
            'subjudul' => 'Atur Tahun Pelajaran dan Semester',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'tp_active_obj' => $tp,
            'smt_active_obj' => $smt
        ];

        $jml = $this->master->getJmlHariEfektif(($tp->id_tp ?? '') . ($smt->id_smt ?? ''));
        $data['jml_hari'] = $jml == null ? '0' : $jml->jml_hari_efektif;

        return view('master/tahun/data', $data);
    }

    public function data()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }
        return $this->response->setJSON($this->dashboard->getDataTahun());
    }

    public function gantiTahun()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        $aktif = $this->request->getPost('active');
        $inputTp = json_decode($this->request->getPost('tahun') ?? '[]');

        $update = [];
        foreach ($inputTp as $tps) {
            $id_tp = $tps->id;
            $tahun = $tps->tp;

            $update[] = [
                'id_tp' => $id_tp,
                'tahun' => $tahun,
                'active' => ($id_tp == $aktif) ? 1 : 0
            ];
        }

        if (!empty($update)) {
            $this->dashboard->updateData('master_tp', $update, 'id_tp', null, true);
        }

        // Clear cache after changing tahun
        $this->dashboard->clearCache();

        $this->logging->saveLog(4, 'mengganti tahun ajaran aktif');
        return $this->response->setJSON(['status' => true, 'msg' => 'Tahun Pelajaran berhasil diganti']);
    }

    public function gantiSemester()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        $aktif = $this->request->getPost('active');
        $inputSmt = json_decode($this->request->getPost('semester') ?? '[]');

        $update = [];
        foreach ($inputSmt as $tps) {
            $id_smt = $tps->id;
            $smt = $tps->Semester;

            $update[] = [
                'id_smt' => $id_smt,
                'smt' => $smt,
                'active' => ($id_smt == $aktif) ? 1 : 0
            ];
        }

        if (!empty($update)) {
            $this->dashboard->updateData('master_smt', $update, 'id_smt', null, true);
        }

        // Clear cache after changing semester
        $this->dashboard->clearCache();

        $this->logging->saveLog(4, 'mengganti semester aktif');
        return $this->response->setJSON(['status' => true, 'msg' => 'Semester berhasil diganti']);
    }

    public function add()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        $method = $this->request->getPost('method');
        $tahun = $this->request->getPost('tahun');

        if ($method == 'add') {
            $insert = ["tahun" => $tahun, "active" => 0];
            $this->master->create('master_tp', $insert);
            $this->logging->saveLog(3, 'menambah tahun pelajaran');
        } else {
            $id = $this->request->getPost('id_tahun');
            $update = ['tahun' => $tahun];
            $this->master->updateData('master_tp', $update, 'id_tp', $id);
            $this->logging->saveLog(4, 'mengedit tahun pelajaran');
        }

        return $this->response->setJSON(['status' => true]);
    }

    public function saveHariEfektif()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $db = \Config\Database::connect();
        $input = [
            'id_hari_efektif' => ($tp->id_tp ?? '') . ($smt->id_smt ?? ''),
            'jml_hari_efektif' => $this->request->getPost('jml_hari'),
        ];

        $db->table('master_hari_efektif')->replace($input);

        return $this->response->setJSON(['status' => true]);
    }

    public function hapusTahun()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Unauthorized');
        }

        $id = $this->request->getPost('hapus');
        if ($this->dashboard->hapus('master_tp', $id, 'id_tp')) {
            $this->logging->saveLog(5, 'menghapus tahun pelajaran');
            return $this->response->setJSON(['status' => true, 'msg' => 'Tahun Pelajaran berhasil dihapus']);
        }
        return $this->response->setJSON(['status' => false, 'msg' => 'Gagal menghapus tahun pelajaran']);
    }
}
