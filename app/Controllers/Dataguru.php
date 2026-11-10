<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\LogModel;

/**
 * Dataguru Controller - Migrated from CI3
 * Comprehensive teacher management
 */
class Dataguru extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;
    protected DropdownModel $dropdownModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
        $this->dropdownModel = new DropdownModel();
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

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $mode = $this->request->getGet('mode') ?? '1';

        // Prepare data kelas [id => nama]
        $rawKelass = $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $kelass = [];
        foreach ($rawKelass as $k) {
            $kelass[$k->id_kelas] = $k->nama_kelas;
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Guru',
            'subjudul' => 'Data Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'mode' => $mode == '1' ? '1' : '2',
            'extras' => $this->dropdownModel->getAllKodeEkskul(),
            'kelass' => $kelass,
        ];

        $mapels = $this->masterModel->getAllMapel();
        $retMapel = [];
        foreach ($mapels as $row) {
            $retMapel[$row->id_mapel] = $row;
        }
        $data['mapels'] = $retMapel;

        return view('master/guru/data', $data);
    }

    /**
     * Custom pagination list (Ported from CI3/Similar to Datasiswa)
     */
    public function list()
    {
        $page = (int) ($this->request->getVar('page') ?? 1);
        $limit = (int) ($this->request->getVar('limit') ?? 10);
        $search = $this->request->getVar('search');
        $dapodik = $this->request->getVar('dapodik');

        $offset = max(0, ($page - 1) * $limit);
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $count_guru = $this->masterModel->getGuruTotalPage($tp->id_tp, $smt->id_smt, $search, $dapodik);
        $lists = $this->masterModel->getGuruPage($tp->id_tp, $smt->id_smt, $offset, $limit, $search, $dapodik);

        $rawKelass = $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $kelasMap = [];
        foreach ($rawKelass as $k) {
            $kelasMap[$k->id_kelas] = $k->nama_kelas;
        }

        $allMapels = $this->masterModel->getAllMapel();
        $mapelMap = [];
        foreach ($allMapels as $m) {
            $mapelMap[$m->id_mapel] = $m->nama_mapel;
        }

        foreach ($lists as $list) {
            $list->foto = !empty($list->foto) && file_exists(FCPATH . $list->foto)
                ? base_url($list->foto)
                : base_url('assets/img/guru.png');
            
            $rawMapelKelas = $this->maybe_unserialize($list->mapel_kelas ?? '[]');
            $list->mapel_kelas = is_array($rawMapelKelas) ? $rawMapelKelas : [];
            
            $mapels = [];
            if (is_array($list->mapel_kelas)) {
                foreach ($list->mapel_kelas as $mk) {
                    $idMapel = is_object($mk) ? ($mk->id_mapel ?? null) : (is_array($mk) ? ($mk['id_mapel'] ?? null) : null);
                    if (!$idMapel) continue;

                    $namaMapel = is_object($mk) ? ($mk->nama_mapel ?? null) : (is_array($mk) ? ($mk['nama_mapel'] ?? null) : null);
                    if (!$namaMapel && isset($mapelMap[$idMapel])) {
                        $namaMapel = $mapelMap[$idMapel];
                    }

                    $rawKelasList = is_object($mk) ? ($mk->kelas_mapel ?? []) : (is_array($mk) ? ($mk['kelas_mapel'] ?? []) : []);
                    $namasKelas = [];
                    if (is_array($rawKelasList)) {
                        foreach ($rawKelasList as $km) {
                            $kId = is_object($km) ? ($km->kelas ?? null) : (is_array($km) ? ($km['kelas'] ?? null) : null);
                            if ($kId && isset($kelasMap[$kId])) {
                                $namasKelas[] = $kelasMap[$kId];
                            }
                        }
                    }

                    if ($namaMapel) {
                        $mapels[] = [
                            'id_mapel'   => $idMapel,
                            'nama_mapel' => $namaMapel,
                            'kelas'      => $namasKelas
                        ];
                    }
                }
            }
            $list->mapels = $mapels;
        }

        $from = $count_guru > 0 ? $offset + 1 : 0;
        $to = min($offset + $limit, $count_guru);
        $totalPages = (int) ceil($count_guru / $limit);

        return $this->response->setJSON([
            'status' => true,
            'data'   => [
                'gurus'      => $lists,
                'pagination' => [
                    'total'       => $count_guru,
                    'total_pages' => $totalPages,
                    'offset'      => $offset,
                    'from'        => $from,
                    'to'          => $to,
                    'page'        => $page,
                    'limit'       => $limit
                ]
            ]
        ]);
    }

    /**
     * Data for DataTable
     */
    public function data()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $search = $this->request->getGet('search')['value'] ?? null;

        $gurus = $this->masterModel->getDataGuru($tp->id_tp, $smt->id_smt, $search);

        return $this->response->setJSON([
            'draw' => $this->request->getGet('draw'),
            'recordsTotal' => count($gurus),
            'recordsFiltered' => count($gurus),
            'data' => $gurus
        ]);
    }

    /**
     * Edit Guru Profile
     */
    public function edit($id)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $guru = $this->masterModel->getGuruById($id, $tp->id_tp, $smt->id_smt);

        if (!$guru) {
            return redirect()->to('dataguru')->with('error', 'Guru tidak ditemukan');
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Edit Guru',
            'subjudul' => 'Edit Data Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'id_active' => $id,
            'guru' => $guru,
            'mapel' => $this->masterModel->getAllMapel(),
        ];

        // Prepare Inputs like CI3
        $data['input_profile'] = [
            (object) ['label' => 'Nama Lengkap', 'name' => 'nama_guru', 'value' => $guru->nama_guru, 'icon' => 'bi bi-person', 'type' => 'text'],
            (object) ['label' => 'Email', 'name' => 'email', 'value' => $guru->email ?? '', 'icon' => 'bi bi-envelope', 'type' => 'text'],
            (object) ['label' => 'NIP / NUPTK', 'name' => 'nip', 'value' => $guru->nip, 'icon' => 'bi bi-person-badge', 'type' => 'text'],
            (object) ['label' => 'Jenis Kelamin', 'name' => 'jenis_kelamin', 'value' => $guru->jenis_kelamin, 'icon' => 'bi bi-gender-ambiguous', 'type' => 'text'],
            (object) ['label' => 'No. Handphone', 'name' => 'no_hp', 'value' => $guru->no_hp, 'icon' => 'bi bi-telephone', 'type' => 'number'],
            (object) ['label' => 'Agama', 'name' => 'agama', 'value' => $guru->agama, 'icon' => 'bi bi-person', 'type' => 'text'],
            (object) ['label' => 'Google API Key', 'name' => 'api_key', 'value' => $guru->api_key ?? '', 'icon' => 'bi bi-key', 'type' => 'text'],
        ];

        $data['input_alamat'] = [
            (object) ['label' => 'NIK', 'name' => 'no_ktp', 'value' => $guru->no_ktp, 'icon' => 'bi bi-person-badge', 'type' => 'number'],
            (object) ['label' => 'Tempat Lahir', 'name' => 'tempat_lahir', 'value' => $guru->tempat_lahir, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Tgl. Lahir', 'name' => 'tgl_lahir', 'value' => $guru->tgl_lahir, 'icon' => 'bi bi-calendar-date', 'type' => 'text'],
            (object) ['label' => 'Alamat', 'name' => 'alamat_jalan', 'value' => $guru->alamat_jalan, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Kecamatan', 'name' => 'kecamatan', 'value' => $guru->kecamatan, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Kota/Kab.', 'name' => 'kabupaten', 'value' => $guru->kabupaten, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Provinsi', 'name' => 'provinsi', 'value' => $guru->provinsi, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Kode Pos', 'name' => 'kode_pos', 'value' => $guru->kode_pos, 'icon' => 'bi bi-envelope', 'type' => 'number'],
        ];

        $data['input_dokumen'] = [
            (object) ['label' => 'KTP', 'id' => 'ktp_id', 'name' => 'ktp_id', 'type' => 'file'],
            (object) ['label' => 'KK', 'id' => 'kk_id', 'name' => 'kk_id', 'type' => 'file'],
            (object) ['label' => 'Sertifikat', 'id' => 'sertifikat', 'name' => 'serti[]', 'type' => 'file'],
        ];

        return view('master/guru/edit', $data);
    }

    /**
     * Create Guru
     */
    public function create()
    {
        $nip = $this->request->getPost('nip');
        $nama_guru = $this->request->getPost('nama_guru');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $rules = [
            'nama_guru' => 'required',
            'username' => 'required|is_unique[master_guru.username]',
            'password' => 'required'
        ];

        if ($nip) {
            $rules['nip'] = 'numeric|is_unique[master_guru.nip]';
        }

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $nama = explode(' ', $nama_guru ?? '');
            $user = [
                'username' => trim($username),
                'password' => password_hash(trim($password), PASSWORD_BCRYPT),
                'first_name' => $nama[0],
                'last_name' => count($nama) > 1 ? end($nama) : '',
                'active' => 1
            ];

            $db = \Config\Database::connect();
            $db->transException(true)->transStart();

            $db->table('users')->insert($user);
            $id_user = $db->insertID();

            // FIX: Add user to 'guru' group (id=2)
            $db->table('users_groups')->insert([
                'user_id' => $id_user,
                'group_id' => 2 // 2 is typically for Guru
            ]);

            $input = [
                'id_user' => $id_user,
                'nip' => trim($nip ?? ''),
                'nama_guru' => trim($nama_guru),
                'username' => trim($username),
                'password' => trim($password),
                'foto' => null,
                // 'status' => 1 // Removed: column does not exist
            ];

            $this->masterModel->create('master_guru', $input);
            $this->logModel->saveLog(1, 'Menambah guru: ' . $nama_guru); // 1 = ADD

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan ke database']);
            }

            return $this->response->setJSON(['status' => true]);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }

        return $this->response->setJSON(['status' => false]);
    }

    /**
     * Save Guru Profile Update
     */
    public function save()
    {
        $id_guru = $this->request->getPost('id_guru');
        $data = $this->request->getPost();
        unset($data['id_guru'], $data['method'], $data[csrf_token()]);

        // Get existing guru data to retrieve id_user and existing documents
        $existingGuru = $this->masterModel->getGuruById($id_guru);
        
        if ($existingGuru && $existingGuru->id_user) {
            $id_user = $existingGuru->id_user;
            $dokumen = json_decode($existingGuru->dokumen ?? '{}', true);
            $docPathRel = 'uploads/users/' . $id_user . '/documents/';
            $docPathAbs = FCPATH . $docPathRel;

            if (!is_dir($docPathAbs)) {
                mkdir($docPathAbs, 0755, true);
            }

            // Upload KTP
            $fileKtp = $this->request->getFile('ktp_id');
            if ($fileKtp && $fileKtp->isValid() && !$fileKtp->hasMoved()) {
                $newName = 'ktp_' . $fileKtp->getRandomName();
                $fileKtp->move($docPathAbs, $newName);
                $dokumen['ktp'] = $docPathRel . $newName;
            }

            // Upload KK
            $fileKk = $this->request->getFile('kk_id');
            if ($fileKk && $fileKk->isValid() && !$fileKk->hasMoved()) {
                $newName = 'kk_' . $fileKk->getRandomName();
                $fileKk->move($docPathAbs, $newName);
                $dokumen['kk'] = $docPathRel . $newName;
            }

            // Upload Sertifikat (Multiple)
            if ($filesSerti = $this->request->getFiles()) {
                if (isset($filesSerti['serti'])) {
                    $sertiFiles = $filesSerti['serti'];
                    $sertiPaths = $dokumen['sertifikat'] ?? [];
                    if (!is_array($sertiPaths)) $sertiPaths = []; // Safety check

                    foreach ($sertiFiles as $file) {
                        if ($file->isValid() && !$file->hasMoved()) {
                            $newName = 'sert_' . $file->getRandomName();
                            $file->move($docPathAbs, $newName);
                            $sertiPaths[] = $docPathRel . $newName;
                        }
                    }
                    $dokumen['sertifikat'] = $sertiPaths;
                }
            }
            
            $data['dokumen'] = json_encode($dokumen);
        }

        $success = $this->masterModel->updateData('master_guru', $data, 'id_guru', $id_guru);
        if ($success) {
            // SYNC TO USERS TABLE
            if ($existingGuru && $existingGuru->id_user) {
                $userUpdate = [];
                // Map fields from guru form to users table
                if (isset($data['email']))
                    $userUpdate['email'] = $data['email'];
                if (isset($data['nama_guru'])) {
                    $nama = explode(' ', $data['nama_guru']);
                    $userUpdate['first_name'] = $nama[0];
                    $userUpdate['last_name'] = count($nama) > 1 ? end($nama) : '';
                }
                
                // Sinkronisasi phone (no_hp)
                if (isset($data['no_hp']))
                    $userUpdate['phone'] = $data['no_hp'];
                    
                // Sinkronisasi company (NIP/NUPTK)
                if (isset($data['nip']))
                    $userUpdate['company'] = $data['nip'];

                if (!empty($userUpdate)) {
                    $db = \Config\Database::connect();
                    $db->table('users')->where('id', $existingGuru->id_user)->update($userUpdate);
                }
            }

            $this->logModel->saveLog(2, 'Mengedit profile guru ID: ' . $id_guru); // 2 = EDIT
        }

        return $this->response->setJSON(['status' => $success]);
    }

    /**
     * Delete Guru - Checks for linked data
     */
    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_guru');
        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'No guru selected']);
        }

        // Check for linked data (simplified check)
        $db = \Config\Database::connect();
        $linked_count = $db->table('kelas_materi')->where('id_guru', $id)->countAllResults() +
            $db->table('cbt_bank_soal')->where('bank_guru_id', $id)->countAllResults();

        if ($linked_count > 0) {
            return $this->response->setJSON(['status' => false, 'count' => $linked_count]);
        }

        $this->masterModel->deleteGuruLinked((int) $id);
        $this->logModel->saveLog(3, 'Menghapus guru ID: ' . $id);

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Force delete guru from detail page
     */
    public function forcedelete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_guru');
        $this->masterModel->deleteGuruLinked((int) $id);
        $this->logModel->saveLog(3, 'Force delete guru ID: ' . $id);

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Teacher detail warning/history page
     */
    public function detail($id_guru)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $jabatan = $this->masterModel->getDetailJabatanGuru($id_guru);
        foreach ($jabatan as $tps) {
            foreach ($tps as $guru) {
                $guru->mapel_kelas = $this->maybe_unserialize($guru->mapel_kelas ?? '[]');
                $guru->ekstra_kelas = $this->maybe_unserialize($guru->ekstra_kelas ?? '[]');
            }
        }

        $db = \Config\Database::connect();
        $guru_data = [
            'detail' => $this->masterModel->getGuruById($id_guru),
            'jabatan' => $jabatan,
            'materi' => $db->table('kelas_materi')->where('id_guru', $id_guru)->countAllResults(),
            'catatan_mapel' => $db->table('kelas_catatan_mapel')->where('id_guru', $id_guru)->countAllResults(),
            'bank_soal' => $db->table('cbt_bank_soal')->where('bank_guru_id', $id_guru)->countAllResults(),
            'pengawas' => $db->table('cbt_pengawas')->where('id_guru', $id_guru)->countAllResults(),
            'posts' => $db->table('post')->where('dari', $id_guru)->countAllResults(),
            'comments' => $db->table('post_comments')->where('dari', $id_guru)->countAllResults(),
            'replies' => $db->table('post_reply_comments')->where('dari', $id_guru)->countAllResults(),
        ];

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Detail Guru',
            'subjudul' => 'Detail Informasi Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'kelass' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'id_guru' => $id_guru,
            'guru' => $guru_data
        ];

        return view('master/guru/detail', $data);
    }

    /**
     * Import setup
     */
    public function add()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Guru',
            'subjudul' => 'Tambah Data Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
        ];

        return view('master/guru/add', $data);
    }

    /**
     * Process Excel Import
     */
    public function do_import()
    {
        $input = $this->request->getPost('guru');
        if (empty($input)) {
            return $this->response->setJSON(['status' => false, 'message' => 'No data to import']);
        }

        $data_insert = [];
        $db = \Config\Database::connect();

        foreach ($input as $guru) {
            $nama_guru = trim($guru['2'] ?? '');
            $username = trim($guru['5'] ?? '');
            $password = trim($guru['6'] ?? '');
            $nip = trim($guru['3'] ?? '');

            $namaArr = explode(' ', $nama_guru);
            $user = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'first_name' => $namaArr[0],
                'last_name' => count($namaArr) > 1 ? end($namaArr) : '',
                'active' => 1
            ];

            $db->table('users')->insert($user);
            $id_user = $db->insertID();

            $data_insert[] = [
                'id_user' => $id_user,
                'nama_guru' => $nama_guru,
                'nip' => $nip,
                'kode_guru' => trim($guru['4'] ?? ''),
                'username' => $username,
                'password' => $password,
                'foto' => null,
                // 'status' => 1 // Removed: column does not exist
            ];
        }

        $save = $this->masterModel->insertBatchData('master_guru', $data_insert);
        if ($save) {
            $this->logModel->saveLog(1, 'Mengimpor data guru sebanyak: ' . count($data_insert));
        }

        return $this->response->setJSON(['status' => $save, 'total' => count($data_insert)]);
    }

    /**
     * Job assignment page
     */
    public function editJabatan($id)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $guru = $this->masterModel->getJabatanGuru($id, $tp->id_tp, $smt->id_smt);

        if (!$guru) {
            // Fallback if not found in jabatan_guru, get from master_guru
            $guruRaw = $this->masterModel->getGuruById($id);
            $guru = (object) [
                'id_guru' => $id,
                'nama_guru' => $guruRaw->nama_guru,
                'mapel_kelas' => '[]',
                'ekstra_kelas' => '[]',
                'id_level' => null,
                'id_kelas' => null
            ];
        }

        $guru->mapel_kelas = $this->maybe_unserialize($guru->mapel_kelas ?? '[]');
        $guru->ekstra_kelas = $this->maybe_unserialize($guru->ekstra_kelas ?? '[]');

        $tps = $this->dashboardModel->getTahun();
        $smts = $this->dashboardModel->getSemester();
        $all_periods = [];
        foreach ($tps as $t) {
            foreach ($smts as $s) {
                $all_periods[] = [
                    'id_tp' => $t->id_tp,
                    'tahun' => $t->tahun,
                    'id_smt' => $s->id_smt,
                    'smt' => $s->smt,
                    'nama_smt' => $s->nama_smt ?? ($s->smt == 1 ? 'Ganjil' : 'Genap')
                ];
            }
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Jabatan Guru',
            'subjudul' => 'Edit Jabatan Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $tps,
            'smt' => $smts,
            'all_periods' => $all_periods,
            'guru' => $guru,
            'kelass' => $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'mapels' => $this->dropdownModel->getAllMapel(),
            'levels' => $this->dropdownModel->getAllLevelGuru(),
            'ekskul' => $this->dropdownModel->getAllEkskul(),
        ];

        return view('master/guru/editmapel', $data);
    }

    /**
     * AJAX get assignment source data from previous TP & SMT
     */
    public function getAssignmentSource()
    {
        $id_guru = (int)$this->request->getGet('id_guru');
        $id_tp = (int)$this->request->getGet('id_tp');
        $id_smt = (int)$this->request->getGet('id_smt');

        if (!$id_guru || !$id_tp || !$id_smt) {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter id_guru, id_tp, id_smt wajib diisi.']);
        }

        $guruData = $this->masterModel->getJabatanGuru($id_guru, $id_tp, $id_smt);
        if (!$guruData) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data penugasan guru pada periode sumber yang dipilih tidak ditemukan.'
            ]);
        }

        $mapel_kelas = $this->maybe_unserialize($guruData->mapel_kelas ?? '[]');
        $ekstra_kelas = $this->maybe_unserialize($guruData->ekstra_kelas ?? '[]');

        if (!is_array($mapel_kelas)) $mapel_kelas = [];
        if (!is_array($ekstra_kelas)) $ekstra_kelas = [];

        if (empty($mapel_kelas) && empty($ekstra_kelas) && !$guruData->id_level) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data mengajar / penugasan guru pada periode sumber yang dipilih masih kosong.'
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'id_guru' => $id_guru,
                'id_level' => $guruData->id_level,
                'id_kelas' => $guruData->id_kelas,
                'mapel_kelas' => $mapel_kelas,
                'ekstra_kelas' => $ekstra_kelas,
            ]
        ]);
    }

    /**
     * Save job assignment
     */
    public function saveJabatan()
    {
        $id_guru = $this->request->getPost('id_guru');
        $id_level = $this->request->getPost('level');
        $kelas_wali = $this->request->getPost('kelas_wali');

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $db = \Config\Database::connect();

        // If this guru is assigned as Wali Kelas (id_jabatan = 4), remove other wali kelas from this class
        if ($id_level == '4' && $kelas_wali) {
            // Remove wali kelas status from other guru for this class
            // This prevents duplicate wali kelas for the same class
            $db->table('jabatan_guru')
                ->where('id_kelas', $kelas_wali)
                ->where('id_jabatan', '4')
                ->where('id_tp', $tp->id_tp)
                ->where('id_smt', $smt->id_smt)
                ->where('id_guru !=', $id_guru)
                ->update(['id_kelas' => 0, 'id_jabatan' => '']);
        }

        $mapels = [];
        $postedMapels = $this->request->getPost('mapel');
        if ($postedMapels) {
            foreach ($postedMapels as $mapelId) {
                $nama_mapel = $this->request->getPost('nama_mapel' . $mapelId);
                $kelasmapel = $this->request->getPost('kelasmapel' . $mapelId);

                if ($kelasmapel) {
                    $selectedKelas = [];
                    foreach ($kelasmapel as $kls) {
                        $selectedKelas[] = ['kelas' => $kls];
                    }
                    $mapels[] = [
                        'id_mapel' => $mapelId,
                        'nama_mapel' => $nama_mapel,
                        'kelas_mapel' => $selectedKelas
                    ];
                }
            }
        }

        $ekstras = [];
        $postedEkstras = $this->request->getPost('ekstra');
        if ($postedEkstras) {
            foreach ($postedEkstras as $exId) {
                $nama_ekstra = $this->request->getPost('nama_ekstra' . $exId);
                $kelasekstra = $this->request->getPost('kelasekstra' . $exId);
                if ($kelasekstra) {
                    $selectedKelas = [];
                    foreach ($kelasekstra as $kls) {
                        $selectedKelas[] = ['kelas' => $kls];
                    }
                    $ekstras[] = [
                        'id_ekstra' => $exId,
                        'nama_ekstra' => $nama_ekstra,
                        'kelas_ekstra' => $selectedKelas
                    ];
                }
            }
        }

        $data = [
            'id_jabatan_guru' => $id_guru . $tp->id_tp . $smt->id_smt,
            'id_guru' => $id_guru,
            'id_jabatan' => $id_level ?: '',
            'id_kelas' => $kelas_wali ?: 0,
            'mapel_kelas' => json_encode($mapels),
            'ekstra_kelas' => json_encode($ekstras),
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt
        ];

        $save = $db->table('jabatan_guru')->replace($data);

        if ($save) {
            $this->logModel->saveLog(2, "Mengedit jabatan guru ID: $id_guru pada TP: $tp->id_tp SMT: $smt->id_smt");
        }

        return $this->response->setJSON([
            'status' => $save,
            'message' => $save ? 'Data berhasil disimpan' : 'Gagal menyimpan data'
        ]);
    }

    /**
     * Save signature image
     */
    public function save_signature()
    {
        $id_guru = $this->request->getPost('id_guru');
        $image = $this->request->getPost('image');

        if (empty($image) || empty($id_guru)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak lengkap']);
        }
        
        $guru = $this->masterModel->getGuruById((int) $id_guru);
        if (!$guru) return $this->response->setJSON(['status' => false, 'message' => 'Guru tidak ditemukan']);
        if (!$guru->id_user) return $this->response->setJSON(['status' => false, 'message' => 'User ID tidak valid']);

        if (!preg_match('/^data:image\/png;base64,/', $image)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Format gambar tidak valid']);
        }

        $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
        $image = str_replace(' ', '+', $image);
        $data = base64_decode($image);

        if ($data === false) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal decode gambar']);
        }

        $file_name = 'ttd_' . $id_guru . '_' . time() . '.png';
        $dirPath = FCPATH . 'uploads/users/' . $guru->id_user . '/profile/';
        
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }
        
        $dbPath = 'uploads/users/' . $guru->id_user . '/profile/' . $file_name;

        if (file_put_contents(FCPATH . $dbPath, $data)) {
            if (!empty($guru->ttd) && file_exists(FCPATH . $guru->ttd)) {
                @unlink(FCPATH . $guru->ttd);
            }

            $this->masterModel->updateData('master_guru', ['ttd' => $dbPath], 'id_guru', $id_guru);

            return $this->response->setJSON([
                'status' => true,
                'src' => base_url($dbPath),
                'message' => 'Tanda tangan berhasil disimpan'
            ]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file tanda tangan']);
    }

    /**
     * Delete signature
     */
    public function delete_ttd()
    {
        $id_guru = $this->request->getPost('id_guru');
        $guru = $this->masterModel->getGuruById((int) $id_guru);

        if ($guru && !empty($guru->ttd)) {
            if (file_exists(FCPATH . $guru->ttd)) {
                unlink(FCPATH . $guru->ttd);
            }
            $this->masterModel->updateData('master_guru', ['ttd' => null], 'id_guru', $id_guru);
            return $this->response->setJSON(['status' => true, 'message' => 'Tanda tangan berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Tanda tangan tidak ditemukan']);
    }

    /**
     * Upload photo
     */
    public function uploadFile($id_guru)
    {
        $file = $this->request->getFile('foto');
        $guru = $this->masterModel->getGuruById((int) $id_guru);

        if (!$guru) return $this->response->setJSON(['status' => false, 'message' => 'Guru tidak ditemukan']);

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $id_user = $guru->id_user;
            if (!$id_user) {
                 return $this->response->setJSON(['status' => false, 'message' => 'User ID null']);
            }

            $newName = $file->getRandomName();
            $pathDir = FCPATH . 'uploads/users/' . $id_user . '/profile/';
            
            if (!is_dir($pathDir)) {
                mkdir($pathDir, 0777, true);
            }
            
            $file->move($pathDir, $newName);
            $dbPath = 'uploads/users/' . $id_user . '/profile/' . $newName;

            $this->masterModel->updateData('master_guru', ['foto' => $dbPath], 'id_guru', $id_guru);
            
            // Sync to Users Table
            $db = \Config\Database::connect();
            $db->table('users')->where('id', $id_user)->update(['foto' => $dbPath]);

            $this->logModel->saveLog(2, 'Mengubah foto guru: ' . $guru->nama_guru);

            return $this->response->setJSON([
                'status' => true,
                'src' => base_url($dbPath),
                'filename' => $newName
            ]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal upload foto']);
    }

    /**
     * Delete Profile Photo
     */
    public function delete_foto($id_guru)
    {
        $guru = $this->masterModel->getGuruById((int) $id_guru);

        if ($guru && $guru->foto && file_exists(FCPATH . $guru->foto)) {
            // Jangan hapus jika itu file default di assets
            if (strpos($guru->foto, 'assets/img/') === false) {
                @unlink(FCPATH . $guru->foto);
            }
        }

        $this->masterModel->updateData('master_guru', ['foto' => null], 'id_guru', $id_guru);
        
        // Sync Users
        if ($guru && $guru->id_user) {
            $db = \Config\Database::connect();
            $db->table('users')->where('id', $guru->id_user)->update(['foto' => null]);
        }

        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Delete photo
     */
    public function deleteFile($id_guru)
    {
        $src = $this->request->getGet('src');
        $file_path = str_replace(base_url(), '', $src ?? '');

        if ($file_path != 'assets/img/guru.png' && file_exists(FCPATH . $file_path)) {
            unlink(FCPATH . $file_path);
            $this->masterModel->updateData('master_guru', ['foto' => ''], 'id_guru', $id_guru);
            $this->logModel->saveLog(3, 'Menghapus foto guru: ' . $file_path);
            return $this->response->setJSON(['status' => true]);
        }

        return $this->response->setJSON(['status' => false]);
    }

    /**
     * Delete document
     */
    public function delete_dokumen($id_guru)
    {
        $src = $this->request->getGet('src');
        $file_name = str_replace(base_url(), '', $src ?? '');

        $guru = $this->masterModel->getGuruById((int) $id_guru);
        $dokumen = json_decode($guru->dokumen ?? '{}', true);

        // Identify category based on file name prefix
        $type = basename($file_name);
        $file_type = explode('_', $type)[0];
        if ($file_type == 'sert')
            $file_type = 'sertifikat';

        if (isset($dokumen[$file_type])) {
            if (is_array($dokumen[$file_type])) {
                $dokumen[$file_type] = array_values(array_filter($dokumen[$file_type], function ($item) use ($file_name) {
                    return $item !== $file_name;
                }));
            } else {
                unset($dokumen[$file_type]);
            }
        }

        if (file_exists(FCPATH . $file_name)) {
            unlink(FCPATH . $file_name);
            $this->masterModel->updateData('master_guru', ['dokumen' => json_encode($dokumen)], 'id_guru', $id_guru);
            $this->logModel->saveLog(3, 'Menghapus dokumen ' . $file_type . ' guru: ' . $guru->nama_guru);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Dokumen berhasil dihapus',
            'dokumen' => $dokumen
        ]);
    }

    /**
     * Edit login credentials
     */
    public function editLogin()
    {
        $id_guru = $this->request->getPost('id_guru');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('new');

        $rules = [
            'username' => 'required',
            'new' => 'required|min_length[5]|matches[new_confirm]',
            'new_confirm' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $db = \Config\Database::connect();
        $guru = $db->table('master_guru')->where('id_guru', $id_guru)->get()->getRow();

        if (!$guru) {
            return $this->response->setJSON(['status' => false, 'text' => 'Guru tidak ditemukan']);
        }

        // Update identity
        $db->table('users')
            ->where('id', $guru->id_user)
            ->update([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_BCRYPT)
            ]);

        // Update master_guru for compatibility
        $db->table('master_guru')
            ->where('id_guru', $id_guru)
            ->update([
                'username' => $username,
                'password' => $password
            ]);

        $this->logModel->saveLog(2, 'Mengubah login guru: ' . $guru->nama_guru);

        return $this->response->setJSON([
            'status' => true,
            'text' => 'Berhasil mengganti username/password'
        ]);
    }

    /**
     * Update Data Guru page
     */
    public function update()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Update Data Guru',
            'subjudul' => 'Update Data & Foto Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'gurus' => $this->masterModel->getAllDataGuru($tp->id_tp, $smt->id_smt)
        ];

        return view('master/guru/update', $data);
    }

    /**
     * Download data for update
     */
    public function downloadData()
    {
        $db = \Config\Database::connect();
        $result = $db->table('master_guru')
            ->select('id_guru, nama_guru, jenis_kelamin, foto, username, password, nip')
            ->get()
            ->getResult();

        foreach ($result as $ind => $guru) {
            $guru->no = ($ind + 1);
            $guru->foto = (!empty($guru->foto) && file_exists(FCPATH . $guru->foto)) ? base_url($guru->foto) : base_url('assets/img/guru.png');
            $guru->jenis_kelamin = $guru->jenis_kelamin ?? '';
        }

        return $this->response->setJSON(['status' => true, 'guru' => $result]);
    }

    /**
     * Update all teachers from Excel
     */
    public function update_all()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false]);
        }

        $ids = $this->request->getPost('ids');
        $update_guru = $this->request->getPost('guru');

        if (empty($update_guru)) {
            return $this->response->setJSON(['status' => false, 'message' => 'No data']);
        }

        $db = \Config\Database::connect();
        $db->table('master_guru')->updateBatch($update_guru, 'id_guru');

        $ids_arr = json_decode($ids ?? '[]');
        if (!empty($ids_arr)) {
            $users = $db->table('master_guru a')
                ->select('b.id, a.username, a.password')
                ->join('users b', 'a.id_user = b.id')
                ->whereIn('a.id_guru', $ids_arr)
                ->get()
                ->getResult();

            $update_user = [];
            foreach ($users as $user) {
                $update_user[] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'password' => hashPasswordSafe($user->password)
                ];
            }
            if (!empty($update_user)) {
                $db->table('users')->updateBatch($update_user, 'id');
            }
        }

        $this->logModel->saveLog(2, 'Mengupdate data ' . count($update_guru) . ' guru');

        return $this->response->setJSON([
            'status' => true,
            'user' => count($update_guru),
        ]);
    }

    /**
     * Update photos from Word
     */
    public function update_foto()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false]);
        }

        $input = $this->request->getPost('guru');
        if (empty($input)) {
            return $this->response->setJSON(['status' => false, 'message' => 'No data']);
        }

        $siswas = [];
        foreach ($input as $value) {
            $foto = '';
            if (isset($value['foto'])) {
                $base64_image_string = $value['foto'];
                $extension = $value['ext'] ?? 'jpg';
                if ($extension == 'jpeg')
                    $extension = 'jpg';

                $output_file = trim($value['nip'] ?? uniqid()) . '.' . $extension;
                $file_path = 'uploads/profile_guru/' . $output_file;

                if (!is_dir(FCPATH . 'uploads/profile_guru/')) {
                    mkdir(FCPATH . 'uploads/profile_guru/', 0755, true);
                }

                file_put_contents(FCPATH . $file_path, base64_decode($base64_image_string));
                $foto = $file_path;
            }

            if (!empty($foto)) {
                $siswas[] = [
                    "id_guru" => $value['id_guru'],
                    "foto" => $foto
                ];
            }
        }

        if (!empty($siswas)) {
            $db = \Config\Database::connect();
            $db->table('master_guru')->updateBatch($siswas, 'id_guru');
            $this->logModel->saveLog(2, 'Mengupdate foto ' . count($siswas) . ' guru');
        }

        return $this->response->setJSON([
            'status' => true,
            'user' => count($input),
        ]);
    }
}
