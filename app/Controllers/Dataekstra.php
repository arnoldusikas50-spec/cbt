<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\KelasModel;
use App\Models\LogModel;

/**
 * Dataekstra Controller - Migrated from CI3
 * Manages extracurricular data and class assignments
 */
class Dataekstra extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;
    protected DropdownModel $dropdownModel;
    protected KelasModel $kelasModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
        $this->dropdownModel = new DropdownModel();
        $this->kelasModel = new KelasModel();
        $this->logModel = new LogModel();
    }

    /**
     * Index page
     */
    public function index()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Ekstrakurikuler',
            'subjudul' => 'Data Mata Pelajaran', // Matches CI3's subjudul
            'setting' => $this->dashboardModel->getSetting(),
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'ekskul' => $this->dropdownModel->getAllEkskul(),
            'pembimbing' => $this->dropdownModel->getAllGuru(),
        ];

        $kelas = $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $kelasEks = [];
        foreach ($kelas as $key => $kls) {
            $eskuls = $this->kelasModel->getKelasEkskul($key, $tp->id_tp, $smt->id_smt);
            foreach ($eskuls as $eskul) {
                $eskul->ekstra = $this->maybe_unserialize($eskul->ekstra ?? '[]');
            }
            $kelasEks[$key] = $eskuls;
        }

        $data['ekskul_kelas'] = $kelasEks;
        $data['kelas'] = $kelas;

        return view('master/ekstra/data', $data);
    }

    /**
     * Create Ekstra
     */
    public function create()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $insert = [
            "nama_ekstra" => $this->request->getPost('nama_ekstra'),
            "kode_ekstra" => $this->request->getPost('kode_ekstra')
        ];

        $save = $this->masterModel->create('master_ekstra', $insert);
        if ($save) {
            $this->logModel->saveLog(1, 'Menambah ekstrakurikuler: ' . $insert['nama_ekstra']);
        }

        return $this->response->setJSON(['status' => (bool) $save]);
    }

    /**
     * Read data for table (DataTables source)
     */
    public function read()
    {
        $db = \Config\Database::connect();
        $results = $db->table('master_ekstra')->get()->getResult();

        return $this->response->setJSON(['data' => $results]);
    }

    /**
     * Update Ekstra
     */
    public function update()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_ekstra');
        $data = [
            'nama_ekstra' => $this->request->getPost('nama_ekstra'),
            'kode_ekstra' => $this->request->getPost('kode_ekstra')
        ];

        $update = $this->masterModel->updateData('master_ekstra', $data, 'id_ekstra', $id);
        if ($update) {
            $this->logModel->saveLog(2, 'Mengupdate ekstrakurikuler ID: ' . $id);
        }

        return $this->response->setJSON(['status' => $update]);
    }

    /**
     * Delete Ekstra
     */
    public function delete($id)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();

        // Dynamic check for linked tables (Ported from CI3 logic)
        $tables = [];
        $tabless = $db->listTables();
        foreach ($tabless as $table) {
            $fields = $db->getFieldNames($table);
            if (in_array('id_ekstra', $fields) || in_array('ekstra_id', $fields)) {
                $tables[] = $table;
            }
        }

        $messages = [];
        foreach ($tables as $table) {
            if ($table != 'master_ekstra') {
                $count = $db->table($table)->where('id_ekstra', $id)->countAllResults();
                if ($count > 0) {
                    $messages[] = $table;
                }
            }
        }

        if (count($messages) > 0) {
            return $this->response->setJSON([
                'status' => false,
                'total' => 'Ekskul digunakan di ' . count($messages) . ' tabel:<br>' . implode('<br>', $messages)
            ]);
        }

        $delete = $this->masterModel->delete('master_ekstra', [$id], 'id_ekstra');
        if ($delete) {
            $this->logModel->saveLog(3, 'Menghapus ekstrakurikuler ID: ' . $id);
            return $this->response->setJSON(['status' => true, 'message' => 'Ekskul berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Ekskul gagal dihapus']);
    }

    /**
     * Save class assignments
     */
    public function save()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $tempKelas = $this->request->getPost('kelas');
        $check_kelas = json_decode($tempKelas ?? '[]');

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $db = \Config\Database::connect();
        $results = [];

        foreach ($check_kelas as $kls) {
            $kls_id = is_object($kls) ? $kls->kls_id : $kls['kls_id'];
            $check_ekskul = $this->request->getPost("ekskul" . $kls_id);

            if ($check_ekskul) {
                $ekstra = [];
                foreach ($check_ekskul as $val) {
                    if (!empty($val)) {
                        $ekstra[] = ['ekstra' => $val];
                    }
                }

                $data = [
                    'id_kelas_ekstra' => $kls_id . $tp->id_tp . $smt->id_smt,
                    'id_kelas' => $kls_id,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'ekstra' => json_encode($ekstra)
                ];

                $results[] = $db->table('kelas_ekstra')->replace($data);
            }
        }

        $this->logModel->saveLog(2, 'Mengupdate assignment ekstrakurikuler kelas');

        return $this->response->setJSON([
            'status' => true,
            'update' => $results
        ]);
    }
}
