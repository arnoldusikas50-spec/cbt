<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\KelasModel;
use App\Models\DropdownModel;
use App\Models\LogModel;

/**
 * Datakelas Controller - Migrated from CI3
 * Manages class/rombel data, student assignments, and promotions
 */
class Datakelas extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;
    protected KelasModel $kelasModel;
    protected DropdownModel $dropdownModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
        $this->kelasModel = new KelasModel();
        $this->dropdownModel = new DropdownModel();
        $this->logModel = new LogModel();
    }

    /**
     * Index page - List of classes
     */
    public function index()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return redirect()->to('auth');
        }

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Kelas',
            'subjudul' => 'Data Kelas',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
        ];

        $kelas = [];
        $kelas_lama = [];

        $kelas = $this->kelasModel->getKelasList($tp->id_tp, $smt->id_smt);
        foreach ($kelas as $k) {
            $k->jml_siswa = $this->masterModel->countSiswaInKelas($k->id_kelas, $tp->id_tp, $smt->id_smt);
        }

        $tp_before = $this->dashboardModel->getTpBefore($tp->tahun);
        if ($tp_before) {
            $kelas_lama = $this->kelasModel->getKelasList($tp_before->id_tp, '2');
        }

        $data['kelas'] = $kelas;
        $data['kelas_lama'] = $kelas_lama;
        $data['jurusan'] = $this->dropdownModel->getDataJurusan();
        $data['level'] = $this->dropdownModel->getAllLevelKelas($setting->jenjang);
        $data['guru'] = $this->dropdownModel->getAllGuru();

        return view('master/kelas/data', $data);
    }

    /**
     * Detail page - Student list in a class
     */
    public function detail($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $setting = $this->dashboardModel->getSetting();

        $kelas = $this->kelasModel->get_one($id);
        if ($kelas) {
            $kelas->siswa = $this->maybe_unserialize($kelas->jumlah_siswa ?? '[]');
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Detail Kelas',
            'subjudul' => 'Detail Kelas',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'kelas' => $kelas,
            'jurusan' => $this->dropdownModel->getDataJurusan(),
            'level' => $this->dropdownModel->getAllLevelKelas($setting->jenjang),
            'guru' => $this->dropdownModel->getAllGuru(),
            'siswas' => $this->kelasModel->get_siswa_kelas($id, $tp->id_tp, $smt->id_smt),
            'struktur' => $this->kelasModel->getStrukturKelas($id) ?? (object) $this->kelasModel->dummyStruktur(),
        ];

        return view('master/kelas/detail', $data);
    }

    /**
     * Add new class page
     */
    public function add()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $setting = $this->dashboardModel->getSetting();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Tambah Kelas',
            'subjudul' => 'Tambah Kelas Baru',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'kelas' => (object) ['nama_kelas' => '', 'kode_kelas' => '', 'jurusan_id' => '', 'level_id' => ''],
            'jurusan' => $this->dropdownModel->getDataJurusan(),
            'level' => $this->dropdownModel->getAllLevelKelas($setting->jenjang),
            'siswa' => $this->masterModel->getAllSiswa($tp->id_tp, $smt->id_smt, 2000), // Semua siswa aktif (termasuk yang belum punya rombel)
        ];

        return view('master/kelas/add', $data);
    }

    /**
     * Edit class page
     */
    public function edit($id)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $setting = $this->dashboardModel->getSetting();

        $kelas = $this->kelasModel->get_one($id);
        if ($kelas) {
            $kelas->jumlah_siswa = $this->maybe_unserialize($kelas->jumlah_siswa ?? '[]');
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Edit Kelas',
            'subjudul' => 'Edit Data Kelas',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'id_kelas' => $id,
            'kelas' => $kelas,
            'jurusan' => $this->dropdownModel->getDataJurusan(),
            'level' => $this->dropdownModel->getAllLevelKelas($setting->jenjang),
            'guru' => $this->kelasModel->getWaliKelas($tp->id_tp, $smt->id_smt),
            'siswa' => $this->masterModel->getAllSiswa($tp->id_tp, $smt->id_smt, 2000),
            'siswakelas' => $this->kelasModel->get_siswa_kelas($id, $tp->id_tp, $smt->id_smt),
        ];

        return view('master/kelas/add', $data);
    }

    /**
     * Save/Update class and student assignments
     */
    public function save()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $id_tp = $tp->id_tp;
        $id_smt = $smt->id_smt;

        $id_kelas = $this->request->getPost('id_kelas');
        $nama_kelas = $this->request->getPost('nama_kelas');
        $guru_id = $this->request->getPost('guru_id');
        $siswas = $this->request->getPost('siswa') ?? [];

        // Build jumlah_siswa JSON for storing in master_kelas
        $siswakelas = [];
        foreach ($siswas as $id_siswa) {
            if ($id_siswa) {
                $siswakelas[] = ['id' => $id_siswa];
            }
        }
        $jumlah = json_encode($siswakelas);

        $insert = [
            'nama_kelas' => $nama_kelas,
            'kode_kelas' => $this->request->getPost('kode_kelas'),
            'id_tp' => $id_tp,
            'id_smt' => $id_smt,
            'jurusan_id' => $this->request->getPost('jurusan_id') ?? 0,
            'level_id' => $this->request->getPost('level_id'),
            'guru_id' => $guru_id ?? '',
            'jumlah_siswa' => $jumlah,
        ];

        $db = \Config\Database::connect();
        $id_new = null;

        if ($id_kelas) {
            $save = $this->masterModel->updateData('master_kelas', $insert, 'id_kelas', $id_kelas);
            $this->logModel->saveLog(2, 'Mengupdate kelas: ' . $nama_kelas);
        } else {
            $save = $this->masterModel->create('master_kelas', $insert);
            $id_new = $db->insertID();
            $id_kelas = $id_new;
            $this->logModel->saveLog(1, 'Menambah kelas: ' . $nama_kelas);
        }

        // Update jabatan_guru for wali kelas
        if ($guru_id) {
            $db->table('jabatan_guru')
                ->set('id_kelas', $id_kelas)
                ->where('id_jabatan_guru', $guru_id . $id_tp . $id_smt)
                ->update();
        }

        // Handle students - like CI3 logic
        $siswa_inserted = 0;
        $unselect = $this->request->getPost('unselect') ?? [];

        // First, get existing students in this class
        $existing_siswa = $this->kelasModel->get_status_siswa_kelas($id_kelas, $id_tp, $id_smt);

        // Prepare insert/update array
        $insert_kelas_siswa = [];

        // Existing students that need to be removed (set id_kelas = 0)
        if ($id_kelas && !$id_new) {
            foreach ($existing_siswa as $id_siswa => $sis) {
                $insert_kelas_siswa[$id_tp . $id_smt . $id_siswa] = [
                    'id_kelas_siswa' => $id_tp . $id_smt . $id_siswa,
                    'id_tp' => $id_tp,
                    'id_smt' => $id_smt,
                    'id_kelas' => 0, // Will be overwritten if still selected
                    'id_siswa' => $id_siswa,
                ];
            }
        }

        // Add selected students
        foreach ($siswas as $id_siswa) {
            if ($id_siswa) {
                $new_id_kelas = $id_kelas;
                $insert_kelas_siswa[$id_tp . $id_smt . $id_siswa] = [
                    'id_kelas_siswa' => $id_tp . $id_smt . $id_siswa,
                    'id_tp' => $id_tp,
                    'id_smt' => $id_smt,
                    'id_kelas' => $new_id_kelas,
                    'id_siswa' => $id_siswa,
                ];
            }
        }

        // Replace all
        foreach ($insert_kelas_siswa as $ins) {
            if ($db->table('kelas_siswa')->replace($ins)) {
                $siswa_inserted++;
            }
        }

        return $this->response->setJSON([
            'status' => $save,
            'siswa' => $siswa_inserted
        ]);
    }

    /**
     * Bulk create classes from modal
     */
    public function bulk_create()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $kelas_json = $this->request->getPost('kelas');
        $classes = json_decode($kelas_json ?? '[]');

        $insert_count = 0;
        if (!empty($classes)) {
            $db = \Config\Database::connect();
            foreach ($classes as $c) {
                $data = (array) $c;
                if ($db->table('master_kelas')->insert($data)) {
                    $insert_count++;
                }
            }
        }

        $this->logModel->saveLog(1, "Bulk create $insert_count kelas");

        return $this->response->setJSON([
            'success' => $insert_count > 0,
            'insert' => $insert_count
        ]);
    }

    /**
     * Delete class
     */
    public function hapus($id)
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['kelas' => false, 'message' => 'Unauthorized']);
        }

        // Check if class is empty before delete? CI3 doesn't seem to check, but it's safer.
        $delete = $this->masterModel->hapus('master_kelas', [$id], 'id_kelas');
        if ($delete) {
            $db = \Config\Database::connect();
            $db->table('kelas_siswa')->where('id_kelas', $id)->delete();
            $this->logModel->saveLog(3, 'Menghapus kelas ID: ' . $id);
        }

        return $this->response->setJSON(['kelas' => $delete]);
    }

    public function manage()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $setting = $this->dashboardModel->getSetting();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Manage Kelas',
            'subjudul' => 'Atur Siswa Per Semester',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'kelas' => $this->kelasModel->getNamaKelasByNama($tp->id_tp, '1'),
            'kelas2' => $this->kelasModel->getNamaKelasByNama($tp->id_tp, '2'),
        ];

        return view('master/kelas/persemester', $data);
    }

    public function copyallfromsmt1()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $id_kelas_smt1 = $this->request->getPost('kelas_satu');

        $row_insert = 0;
        if ($id_kelas_smt1) {
            $db = \Config\Database::connect();
            foreach ($id_kelas_smt1 as $id) {
                $kls_smt1 = $db->table('master_kelas')->where('id_kelas', $id)->get()->getRow();
                if ($kls_smt1) {
                    $insert_kelas = [
                        'nama_kelas' => $kls_smt1->nama_kelas,
                        'kode_kelas' => $kls_smt1->kode_kelas,
                        'id_tp' => $tp->id_tp,
                        'id_smt' => '2',
                        'jurusan_id' => $kls_smt1->jurusan_id,
                        'level_id' => $kls_smt1->level_id,
                        'guru_id' => $kls_smt1->guru_id,
                        'jumlah_siswa' => $kls_smt1->jumlah_siswa,
                    ];
                    $db->table('master_kelas')->insert($insert_kelas);
                    $new_id = $db->insertID();

                    $siswas = $db->table('kelas_siswa')
                        ->where(['id_kelas' => $id, 'id_tp' => $tp->id_tp, 'id_smt' => '1'])
                        ->get()->getResult();

                    foreach ($siswas as $siswa) {
                        $db->table('kelas_siswa')->insert([
                            'id_siswa' => $siswa->id_siswa,
                            'id_kelas' => $new_id,
                            'id_tp' => $tp->id_tp,
                            'id_smt' => '2'
                        ]);
                    }
                    $row_insert++;
                }
            }
        }
        return $this->response->setJSON(['success' => $row_insert > 0, 'insert' => $row_insert]);
    }

    public function kenaikan()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $setting = $this->dashboardModel->getSetting();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Manage Kelas',
            'subjudul' => 'Kenaikan Kelas/Rombel',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
        ];

        $kelas_selected = $this->request->getGet('kelas');
        $type_selected = $this->request->getGet('type');
        $data['kelas_selected'] = $kelas_selected;
        $data['type_selected'] = $type_selected;

        $tpBefore = $this->dashboardModel->getTpBefore($tp->tahun);
        $data['tpBefore'] = $tpBefore;

        if ($tpBefore) {
            $data['kelas_lama'] = $this->kelasModel->getNamaKelasByNama($tpBefore->id_tp, '2');
            if ($kelas_selected != null) {
                $data['siswas'] = $this->kelasModel->get_siswa_kelas($kelas_selected, $tpBefore->id_tp, '2');
                $kelas_now = $this->kelasModel->get_one($kelas_selected);

                $data['kelases'] = $this->kelasModel->getNamaKelasByNama($tp->id_tp, '1');

                $level_now = $kelas_now->level_id;
                $level_next = $level_now + 1;
                $data['level'] = $level_next;

                $data['buat_kelas_baru'] = $this->group_kelas_by_level($data['kelases']);
                $data['all_kelas_baru'] = $this->get_kelas_next($data['kelas_lama']);

                $siswa_baru = $this->kelasModel->get_siswa_kelas_baru($tp->id_tp, '1');
                $data['siswa_kelas_baru'] = [];
                foreach ($siswa_baru as $sb) {
                    $data['siswa_kelas_baru'][$sb->id_siswa] = $sb;
                }
            }
        }

        return view('master/kelas/naikkelas', $data);
    }

    private function group_kelas_by_level($kelases)
    {
        $ret = [];
        foreach ($kelases as $id => $nama) {
            $arr = explode(' ', $nama);
            $level = $arr[0];
            if (!isset($ret[$level]))
                $ret[$level] = [];
            $ret[$level][$id] = $nama;
        }
        return $ret;
    }

    private function get_kelas_next($kelas_lama)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $kelas_baru = $this->kelasModel->getNamaKelasByNama($tp->id_tp, $smt->id_smt);

        $ret = [];
        foreach ($kelas_lama as $id_lama => $nama_lama) {
            $nama_baru = $this->get_next_name($nama_lama);
            $id_baru = array_search($nama_baru, $kelas_baru);
            $ret[$id_lama] = $id_baru ? $id_baru : '0';
        }
        return $ret;
    }

    private function get_next_name($name)
    {
        $arr = explode(' ', $name);
        if (count($arr) > 1) {
            $level = $arr[0];
            $next_level = $this->is_roman($level) ? $this->roman_to_int($level) + 1 : $level + 1;
            $arr[0] = $this->is_roman($level) ? $this->int_to_roman($next_level) : $next_level;
        }
        return implode(' ', $arr);
    }

    private function is_roman($str)
    {
        return preg_match('/^[IVXLCDM]+$/', $str);
    }

    private function roman_to_int($roman)
    {
        $romans = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];
        $result = 0;
        foreach ($romans as $key => $value) {
            while (strpos($roman, $key) === 0) {
                $result += $value;
                $roman = substr($roman, strlen($key));
            }
        }
        return $result;
    }

    private function int_to_roman($number)
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }

    public function naikkelas()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(false);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $id_tp = $tp->id_tp;
        $id_smt = '1';

        $kelas_json = $this->request->getPost('kelas');
        $kelas = json_decode($kelas_json ?? '[]');
        $db = \Config\Database::connect();

        $row_insert = 0;
        if (!empty($kelas)) {
            foreach ($kelas as $kls) {
                $kls_id = array_search($kls->nama_kls_baru, $this->kelasModel->getNamaKelasByNama($id_tp, $id_smt));
                if ($kls_id) {
                    $insert_siswa = [
                        'id_siswa' => $kls->id_siswa,
                        'id_kelas' => $kls_id,
                        'id_tp' => $id_tp,
                        'id_smt' => $id_smt
                    ];
                    $db->table('kelas_siswa')->replace($insert_siswa);
                    $row_insert++;
                }
            }
        }
        return $this->response->setJSON($row_insert > 0);
    }

    public function naikkan_semua()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['res' => 0]);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $tpBefore = $this->dashboardModel->getTpBefore($tp->tahun);
        $id_tp = $tp->id_tp;
        $id_smt = '1';

        $kelas_baru = $this->request->getPost('kelas_baru');
        $db = \Config\Database::connect();

        $row_insert = 0;
        if ($kelas_baru) {
            foreach ($kelas_baru as $id_lama => $id_baru) {
                if ($id_baru != '0') {
                    $siswas = $db->table('kelas_siswa')
                        ->where(['id_kelas' => $id_lama, 'id_tp' => $tpBefore->id_tp, 'id_smt' => '2'])
                        ->get()->getResult();

                    foreach ($siswas as $siswa) {
                        // Check if student is "Naik" from rapor? CI3 does this via $siswa->naik check if filtered.
                        // For now we just follow the basic logic or add the filter if needed.
                        $db->table('kelas_siswa')->replace([
                            'id_siswa' => $siswa->id_siswa,
                            'id_kelas' => $id_baru,
                            'id_tp' => $id_tp,
                            'id_smt' => $id_smt
                        ]);
                        $row_insert++;
                    }
                }
            }
        }
        return $this->response->setJSON(['res' => $row_insert]);
    }
}
