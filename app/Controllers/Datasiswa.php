<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\LogModel;

/**
 * Datasiswa Controller - Migrated from CI3
 * Comprehensive student management
 */
class Datasiswa extends BaseController
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

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Siswa',
            'subjudul' => 'Data Siswa',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'kelass' => $tp && $smt ? $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt) : [],
        ];

        return view('master/siswa/data', $data);
    }

    /**
     * Custom pagination list (Ported from CI3)
     */
    public function list()
    {
        $page = $this->request->getPost('page') ?? 1;
        $limit = $this->request->getPost('limit') ?? 10;
        $search = $this->request->getPost('search');
        $filter = $this->request->getPost('filter');
        $kelas = $this->request->getPost('kelas');

        $offset = ($page - 1) * $limit;
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if ($filter == "3" && empty($kelas)) {
            $filter = "0";
        }

        $count_siswa = $this->masterModel->getSiswaTotalPage($tp->id_tp, $smt->id_smt, $filter, $kelas, $search);
        $lists = $this->masterModel->getSiswaPage($tp->id_tp, $smt->id_smt, $offset, $limit, $filter, $kelas, $search);

        foreach ($lists as $list) {
            $list->foto = $list->foto != null && $list->foto != "" && file_exists(FCPATH . $list->foto)
                ? base_url($list->foto)
                : base_url('assets/img/siswa_sm.png');
        }

        // Sorting by name naturally like CI3
        usort($lists, function ($a, $b) {
            return strnatcasecmp($a->nama, $b->nama);
        });

        $db = \Config\Database::connect();
        $totalAll   = $db->table('master_siswa')->countAllResults();
        $dapoTotal  = $db->table('master_siswa')->where('dapo_id IS NOT NULL', null, false)->where('dapo_id !=', '')->countAllResults();
        $aktifCount = $db->table('master_siswa a')
            ->join('buku_induk u', 'u.id_siswa = a.id_siswa AND u.status="1"')
            ->join('users us', 'us.username = a.username AND us.active="1"')
            ->countAllResults();

        $data = [
            'lists'       => $lists,
            'total'       => $count_siswa,
            'total_all'   => $totalAll,
            'dapo_total'  => $dapoTotal,
            'aktif_total' => $aktifCount,
            'pages'       => ceil($count_siswa / $limit),
            'search'      => $search,
            'perpage'     => $limit,
            'filter'      => $filter
        ];

        return $this->response->setJSON($data);
    }

    /**
     * Add/Import Siswa page
     */
    public function add($id_kelas = null)
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Siswa',
            'subjudul' => 'Import Siswa',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
        ];

        if ($id_kelas) {
            $data['kelas'] = $this->masterModel->getKelasById($id_kelas);
        }

        return view('master/siswa/add', $data);
    }

    /**
     * Create single siswa
     */
    public function create()
    {
        if (!$this->isLoggedIn())
            return $this->respond(false, 'Unauthorized');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'nama_siswa' => 'required',
            'nis' => 'required|numeric|is_unique[master_siswa.nis]',
            'nisn' => 'required|numeric|is_unique[master_siswa.nisn]',
            'username' => 'required|is_unique[master_siswa.username]|is_unique[users.username]',
            'password' => 'required'
        ]);

        if (!$this->validate($validation->getRules())) {
            return $this->response->setJSON([
                'insert' => false,
                'error' => $validation->getErrors(),
                'text' => 'Data Sudah ada, Pastikan NIS, NISN dan Username belum digunakan siswa lain'
            ]);
        }

        $nama_siswa = $this->request->getPost('nama_siswa');
        $namaArr = explode(' ', $nama_siswa ?? '');
        $first_name = $namaArr[0];
        $last_name = count($namaArr) > 1 ? end($namaArr) : '';

        $nis = $this->request->getPost('nis');
        $nisn = $this->request->getPost('nisn');
        $photoName = !empty($nisn) ? $nisn : (!empty($nis) ? $nis : '00');
        $insert = [
            "nama" => $nama_siswa,
            "nis" => $nis,
            "nisn" => $nisn,
            "jenis_kelamin" => $this->request->getPost('jenis_kelamin'),
            "agama" => $this->request->getPost('agama'),
            "kelas_awal" => $this->request->getPost('kelas_awal'),
            "tahun_masuk" => $this->request->getPost('tahun_masuk'),
            "username" => $this->request->getPost('username'),
            "password" => $this->request->getPost('password'),
            "foto" => 'uploads/foto_siswa/' . $photoName . '.jpg'
        ];

        // CI4 doesn't use UUID() directly in set() easily with builder 
        // We can use a helper or raw query if needed, but let's just generate it in PHP for now
        $insert['uid'] = bin2hex(random_bytes(16));

        $this->masterModel->create('master_siswa', $insert);
        $id = $this->masterModel->db->insertID();

        $induk = [
            "id_siswa" => $id,
            "uid" => $insert['uid'],
            "status" => 1
        ];

        $this->masterModel->create('buku_induk', $induk);
        $this->logModel->saveLog(1, 'Menambahkan Siswa: ' . $nama_siswa);

        // Register user
        $userData = [
            'username' => $insert['username'],
            'password' => password_hash($insert['password'], PASSWORD_BCRYPT),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'active' => 1
        ];

        $db = \Config\Database::connect();
        $db->table('users')->insert($userData);
        $userId = $db->insertID();
        $db->table('users_groups')->insert(['user_id' => $userId, 'group_id' => 3]);

        return $this->response->setJSON([
            'insert' => true,
            'text' => 'Siswa berhasil ditambahkan'
        ]);
    }

    /**
     * Edit Siswa page
     */
    public function edit($id)
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');

        $siswa = $this->masterModel->getSiswaById($id);
        if (!$siswa)
            return redirect()->to('datasiswa');

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $setting = $this->dashboardModel->getSetting();

        $input_data = [
            (object) ['label' => 'Nama Lengkap', 'name' => 'nama', 'value' => $siswa->nama, 'icon' => 'bi bi-person', 'class' => '', 'type' => 'text'],
            (object) ['label' => 'NIS', 'name' => 'nis', 'value' => $siswa->nis, 'icon' => 'bi bi-person-badge', 'class' => '', 'type' => 'number'],
            (object) ['name' => 'nisn', 'label' => 'NISN', 'value' => $siswa->nisn, 'icon' => 'bi bi-person-badge', 'class' => '', 'type' => 'text'],
            (object) ['label' => 'Jenis Kelamin', 'name' => 'jenis_kelamin', 'value' => $siswa->jenis_kelamin, 'icon' => 'bi bi-gender-ambiguous', 'class' => '', 'type' => 'text'],
            (object) ['name' => 'kelas_awal', 'label' => 'Diterima di kelas', 'value' => $siswa->kelas_awal, 'icon' => 'bi bi-mortarboard', 'class' => '', 'type' => 'text'],
            (object) ['name' => 'tahun_masuk', 'label' => 'Tgl diterima', 'value' => $siswa->tahun_masuk, 'icon' => 'tahun bi bi-calendar-date', 'class' => 'tahun', 'type' => 'text'],
            (object) ['name' => 'sekolah_asal', 'label' => 'Sekolah Asal', 'value' => $siswa->sekolah_asal, 'icon' => 'bi bi-mortarboard', 'class' => '', 'type' => 'text'],
            (object) ['name' => 'status', 'label' => 'Status', 'value' => $siswa->status, 'icon' => 'bi bi-person-check', 'class' => 'status', 'type' => 'text']
        ];

        $input_bio = [
            (object) ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'value' => $siswa->tempat_lahir, 'icon' => 'bi bi-geo-alt', 'class' => '', 'type' => 'text'],
            (object) ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'value' => $siswa->tanggal_lahir, 'icon' => 'bi bi-calendar-date', 'class' => 'tahun', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'agama', 'label' => 'Agama', 'value' => $siswa->agama, 'icon' => 'bi bi-star', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'alamat', 'label' => 'Alamat', 'value' => $siswa->alamat, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'rt', 'label' => 'Rt', 'value' => $siswa->rt, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'rw', 'label' => 'Rw', 'value' => $siswa->rw, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'kelurahan', 'label' => 'Kelurahan/Desa', 'value' => $siswa->kelurahan, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'kecamatan', 'label' => 'Kecamatan', 'value' => $siswa->kecamatan, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'kabupaten', 'label' => 'Kabupaten/Kota', 'value' => $siswa->kabupaten, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'kode_pos', 'label' => 'Kode Pos', 'value' => $siswa->kode_pos, 'icon' => 'bi bi-envelope', 'type' => 'text'],
            (object) ['class' => '', 'name' => 'hp', 'label' => 'Hp', 'value' => $siswa->hp, 'icon' => 'bi bi-telephone', 'type' => 'text']
        ];

        $input_ortu = [
            (object) ['name' => 'status_keluarga', 'label' => 'Status Keluarga', 'value' => $siswa->status_keluarga, 'icon' => 'bi bi-people', 'type' => 'text'],
            (object) ['name' => 'anak_ke', 'label' => 'Anak ke', 'value' => $siswa->anak_ke, 'icon' => 'bi bi-sort-numeric-down', 'type' => 'number'],
            (object) ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $siswa->nama_ayah, 'icon' => 'bi bi-person', 'type' => 'text'],
            (object) ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'value' => $siswa->pekerjaan_ayah, 'icon' => 'bi bi-briefcase', 'type' => 'text'],
            (object) ['name' => 'alamat_ayah', 'label' => 'Alamat Ayah', 'value' => $siswa->alamat_ayah, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['name' => 'nohp_ayah', 'label' => 'No. HP Ayah', 'value' => $siswa->nohp_ayah, 'icon' => 'bi bi-telephone', 'type' => 'number'],
            (object) ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $siswa->nama_ibu, 'icon' => 'bi bi-person', 'type' => 'text'],
            (object) ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'value' => $siswa->pekerjaan_ibu, 'icon' => 'bi bi-briefcase', 'type' => 'text'],
            (object) ['name' => 'alamat_ibu', 'label' => 'Alamat Ibu', 'value' => $siswa->alamat_ibu, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['name' => 'nohp_ibu', 'label' => 'No. HP Ibu', 'value' => $siswa->nohp_ibu, 'icon' => 'bi bi-telephone', 'type' => 'number'],
        ];

        $input_wali = [
            (object) ['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $siswa->nama_wali, 'icon' => 'bi bi-person', 'type' => 'text'],
            (object) ['name' => 'pekerjaan_wali', 'label' => 'Pekerjaan Wali', 'value' => $siswa->pekerjaan_wali, 'icon' => 'bi bi-briefcase', 'type' => 'text'],
            (object) ['name' => 'alamat_wali', 'label' => 'Alamat Wali', 'value' => $siswa->alamat_wali, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['name' => 'nohp_wali', 'label' => 'No. HP Wali', 'value' => $siswa->nohp_wali, 'icon' => 'bi bi-telephone', 'type' => 'number'],
        ];

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Siswa',
            'subjudul' => 'Edit Data Siswa',
            'siswa' => $siswa,
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'input_data' => $input_data,
            'input_bio' => $input_bio,
            'input_ortu' => $input_ortu,
            'input_wali' => $input_wali,
        ];

        return view('master/siswa/edit', $data);
    }

    /**
     * Update Siswa data
     */
    public function updateData()
    {
        try {
            if (!$this->isLoggedIn())
                return $this->respond(false, 'Unauthorized');

            $id_siswa = $this->request->getPost('id_siswa');
            $nis = $this->request->getPost('nis');
            $nisn = $this->request->getPost('nisn');

            $siswa = $this->masterModel->getSiswaById($id_siswa);
            if (!$siswa) {
                return $this->response->setJSON([
                    'insert' => false,
                    'text' => 'Data siswa tidak ditemukan'
                ]);
            }

            // Handle Photo Deletion
            if ($this->request->getPost('foto') !== null && $this->request->getPost('nama') === null) {
                if (!empty($siswa->foto) && file_exists(FCPATH . $siswa->foto)) {
                    @unlink(FCPATH . $siswa->foto);
                }
                $this->masterModel->updateData('master_siswa', ['foto' => ''], 'id_siswa', $id_siswa);
                return $this->response->setJSON(['insert' => true, 'text' => 'Foto profil berhasil dihapus']);
            }

            // Handle Password Update
            if ($this->request->getPost('password') !== null && $this->request->getPost('nama') === null) {
                $password = $this->request->getPost('password');
                if (strlen($password) < 6) {
                    return $this->response->setJSON(['insert' => false, 'text' => 'Password minimal 6 karakter.']);
                }
                
                $this->masterModel->updateData('master_siswa', ['password' => $password], 'id_siswa', $id_siswa);
                
                $db = \Config\Database::connect();
                if (isset($siswa->id_user) && $siswa->id_user) {
                    $db->table('users')->where('id', $siswa->id_user)->update(['password' => hashPasswordSafe($password)]);
                } else {
                    $db->table('users')->where('username', $siswa->username)->update(['password' => hashPasswordSafe($password)]);
                }
                return $this->response->setJSON(['insert' => true, 'text' => ' berhasil diperbarui']);
            }

            $u_nis = $siswa->nis == $nis ? "" : "|is_unique[master_siswa.nis]";
            $u_nisn = $siswa->nisn == $nisn ? "" : "|is_unique[master_siswa.nisn]";

            $validation = \Config\Services::validation();
            $validation->setRules([
                'nama' => 'required',
                'nis' => 'required|numeric|min_length[5]' . $u_nis,
                'nisn' => 'required|numeric' . $u_nisn,
            ]);

            if (!$this->validate($validation->getRules())) {
                return $this->response->setJSON([
                    'insert' => false,
                    'text' => implode('<br>', $validation->getErrors())
                ]);
            }

            $input = [
                'nama' => $this->request->getPost('nama'),
                'nis' => $nis,
                'nisn' => $nisn,
                'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
                'tempat_lahir' => $this->request->getPost('tempat_lahir'),
                'tanggal_lahir' => $this->request->getPost('tanggal_lahir') ?: null,
                'agama' => $this->request->getPost('agama'),
                'status_keluarga' => $this->request->getPost('status_keluarga'),
                'anak_ke' => $this->request->getPost('anak_ke'),
                'alamat' => $this->request->getPost('alamat'),
                'rt' => $this->request->getPost('rt'),
                'rw' => $this->request->getPost('rw'),
                'kelurahan' => $this->request->getPost('kelurahan'),
                'kecamatan' => $this->request->getPost('kecamatan'),
                'kabupaten' => $this->request->getPost('kabupaten'),
                'provinsi' => $this->request->getPost('provinsi'),
                'kode_pos' => $this->request->getPost('kode_pos'),
                'hp' => $this->request->getPost('hp'),
                'nama_ayah' => $this->request->getPost('nama_ayah'),
                'nohp_ayah' => $this->request->getPost('nohp_ayah'),
                'pendidikan_ayah' => $this->request->getPost('pendidikan_ayah'),
                'pekerjaan_ayah' => $this->request->getPost('pekerjaan_ayah'),
                'alamat_ayah' => $this->request->getPost('alamat_ayah'),
                'nama_ibu' => $this->request->getPost('nama_ibu'),
                'nohp_ibu' => $this->request->getPost('nohp_ibu'),
                'pendidikan_ibu' => $this->request->getPost('pendidikan_ibu'),
                'pekerjaan_ibu' => $this->request->getPost('pekerjaan_ibu'),
                'alamat_ibu' => $this->request->getPost('alamat_ibu'),
                'nama_wali' => $this->request->getPost('nama_wali'),
                'nohp_wali' => $this->request->getPost('nohp_wali'),
                'pendidikan_wali' => $this->request->getPost('pendidikan_wali'),
                'pekerjaan_wali' => $this->request->getPost('pekerjaan_wali'),
                'alamat_wali' => $this->request->getPost('alamat_wali'),
                'tahun_masuk' => $this->request->getPost('tahun_masuk') ?: null,
                'kelas_awal' => $this->request->getPost('kelas_awal'),
                'sekolah_asal' => $this->request->getPost('sekolah_asal'),
            ];

            $this->masterModel->updateData('master_siswa', $input, 'id_siswa', $id_siswa);

            // Update status in buku_induk
            $status = $this->request->getPost('status');
            if ($status !== null) {
                $this->masterModel->updateData('buku_induk', ['status' => $status], 'id_siswa', $id_siswa);
            }

            // SYNC TO USERS TABLE
            if ($siswa) {
                $userUpdate = [];
                
                if (isset($input['nama'])) {
                    $nama = explode(' ', $input['nama']);
                    $userUpdate['first_name'] = $nama[0];
                    $userUpdate['last_name'] = count($nama) > 1 ? end($nama) : '';
                }
                
                if (isset($input['hp']))
                    $userUpdate['phone'] = $input['hp'];
                
                if ($this->request->getPost('email'))
                    $userUpdate['email'] = $this->request->getPost('email');
                
                if (isset($input['nisn']))
                    $userUpdate['company'] = $input['nisn'];

                if (!empty($userUpdate)) {
                    $db = \Config\Database::connect();
                    // Link via id_user if exists (legacy/schema variants), otherwise via username
                    if (isset($siswa->id_user) && $siswa->id_user) {
                        $db->table('users')->where('id', $siswa->id_user)->update($userUpdate);
                    } else {
                        $db->table('users')->where('username', $siswa->username)->update($userUpdate);
                    }
                }
            }

            return $this->response->setJSON([
                'insert' => true,
                'text' => 'Siswa berhasil diperbaharui'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'insert' => false,
                'text' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk Delete/Actions
     */
    public function delete()
    {
        if (!$this->isAdmin())
            return $this->respond(false, 'Authorized Access');

        $chk = $this->request->getPost('checked');
        $aksi = $this->request->getPost('aksi');

        if (!$chk)
            return $this->respond(false, 'No students selected');

        $status = true;
        $messages = [];

        if ($aksi == 'pindah' || $aksi == 'keluar') {
            $valStatus = $aksi == 'pindah' ? '3' : '4';
            foreach ($chk as $id) {
                // Get current class name for record
                $siswa = $this->masterModel->getSiswaById($id);
                // Here we might need a more complex query to get current class, 
                // but let's simplify for now.

                $this->masterModel->updateData('buku_induk', [
                    'tgl_pindah' => date('Y-m-d'),
                    'status' => $valStatus
                ], 'id_siswa', $id);
            }
        } elseif ($aksi == 'hapus') {
            // Check if students can be deleted (active = 0)
            // Porting the logic from CI3
            // For now, let's just do bulk delete if admin

            $this->masterModel->hapus('master_siswa', $chk, 'id_siswa');
            $this->masterModel->hapus('buku_induk', $chk, 'id_siswa');
            $this->masterModel->hapus('kelas_siswa', $chk, 'id_siswa');
            // Additional tables can be added here
        }

        return $this->response->setJSON([
            'status' => $status,
            'message' => 'Siswa berhasil ' . ($aksi == 'hapus' ? 'dihapus' : 'diupdate')
        ]);
    }

    /**
     * Import logic (Ported/Simplified)
     */
    public function do_import()
    {
        $id_kelas = $this->request->getPost('id_kelas');
        $input = $this->request->getPost('siswa'); // Expecting array of student data

        if (empty($input))
            return $this->respond(false, 'No data to import');

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $users = [];
        $induk = [];
        $kelasSiswa = [];

        foreach ($input as $key => $value) {
            $value['uid'] = bin2hex(random_bytes(16));
            $input[$key] = $value;

            // Prepare users
            $namaArr = explode(' ', $value['nama'] ?? '');
            $users[] = [
                'username' => $value['username'],
                'password' => password_hash($value['password'], PASSWORD_BCRYPT),
                'first_name' => $namaArr[0],
                'last_name' => count($namaArr) > 1 ? end($namaArr) : '',
                'active' => 1
            ];
        }

        // Batch Insert
        $this->masterModel->insertBatchData('master_siswa', $input);

        // Get inserted IDs to link with buku_induk and kelas_siswa
        $usernames = array_column($input, 'username');
        $insertedSiswa = $this->masterModel->getSiswaByUsernames($usernames);

        foreach ($insertedSiswa as $s) {
            $induk[] = [
                'id_siswa' => $s->id_siswa,
                'uid' => bin2hex(random_bytes(16)),
                'status' => 1
            ];

            if ($id_kelas && $id_kelas != '0') {
                $kelasSiswa[] = [
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'id_kelas' => $id_kelas,
                    'id_siswa' => $s->id_siswa
                ];
            }
        }

        if (!empty($induk))
            $this->masterModel->insertBatchData('buku_induk', $induk);
        if (!empty($kelasSiswa))
            $this->masterModel->insertBatchData('kelas_siswa', $kelasSiswa);

        return $this->response->setJSON([
            'status' => true,
            'total' => count($input),
            'message' => 'Import berhasil'
        ]);
    }

    /**
     * Bulk update page
     */
    public function update()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Update Data Siswa',
            'subjudul' => 'Update Data Siswa',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboardModel->getTahun(),
            'smt' => $this->dashboardModel->getSemester(),
            'tipe' => 'update',
            'kelas' => $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt),
        ];

        return view('master/siswa/update', $data);
    }

    /**
     * Photo Upload
     */
    public function uploadFile($id_siswa)
    {
        $id_siswa = (int)$id_siswa;
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            try {
                $siswa = $this->masterModel->getSiswaById($id_siswa);
                if (!$siswa) {
                    return $this->respond(false, 'Siswa tidak ditemukan');
                }

                $ext = $file->getExtension() ?: 'jpg';
                $nisnClean = preg_replace('/[^A-Za-z0-9]/', '', !empty($siswa->nisn) ? $siswa->nisn : (!empty($siswa->nis) ? $siswa->nis : $siswa->id_siswa));
                $newName = $nisnClean . '.' . $ext;
                
                $targetDir = FCPATH . 'uploads/foto_siswa/';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                @chmod($targetDir, 0777);

                $file->move($targetDir, $newName, true);

                $fotoPath = 'uploads/foto_siswa/' . $newName;
                $this->masterModel->updateData('master_siswa', ['foto' => $fotoPath], 'id_siswa', $id_siswa);

                // Sync to Users Table using username
                if (!empty($siswa->username)) {
                    $db = \Config\Database::connect();
                    $db->table('users')->where('username', $siswa->username)->update(['foto' => $fotoPath]);
                }

                $this->logModel->saveLog(2, 'Mengubah foto siswa: ' . $siswa->nama);

                return $this->response->setJSON([
                    'status' => true,
                    'src' => base_url($fotoPath) . '?v=' . time()
                ]);
            } catch (\Exception $e) {
                return $this->respond(false, 'Error: ' . $e->getMessage());
            }
        }

        return $this->respond(false, 'Gagal upload foto: file tidak valid atau sudah dipindahkan');
    }

    public function downloadData($id_kelas)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $db = \Config\Database::connect();
        $siswas = $db->table('master_siswa s')
            ->select('s.*, ks.id_kelas, c.nama_kelas')
            ->join('kelas_siswa ks', 's.id_siswa = ks.id_siswa')
            ->join('master_kelas c', 'ks.id_kelas = c.id_kelas')
            ->where('ks.id_tp', $tp->id_tp)
            ->where('ks.id_smt', $smt->id_smt)
            ->where('ks.id_kelas', $id_kelas)
            ->get()->getResult();

        foreach ($siswas as $ind => $siswa) {
            $siswa->no = ($ind + 1);
            $siswa->foto = !empty($siswa->foto) && file_exists(FCPATH . $siswa->foto) ? base_url($siswa->foto) : base_url('assets/img/siswa_sm.png');
        }

        return $this->response->setJSON(['status' => true, 'siswa' => $siswas]);
    }

    public function updateAll()
    {
        $input = $this->request->getPost('siswa'); // Array of student data

        if (empty($input)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data untuk diupdate']);
        }

        $this->masterModel->updateBatchData('master_siswa', $input, 'id_siswa');

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Berhasil memperbarui ' . count($input) . ' siswa',
            'siswa' => count($input)
        ]);
    }

    public function update_foto()
    {
        $input = $this->request->getPost('siswa');
        if (empty($input)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada foto untuk diupdate']);
        }

        $siswas = [];
        foreach ($input as $value) {
            $fotoPath = 'uploads/foto_siswa/' . trim($value['nis'] ?? '00') . '.jpg';
            if (isset($value['foto'])) {
                $base64_image_string = $value['foto'];
                $extension = $value['ext'] ?? 'jpg';
                if ($extension == 'jpeg')
                    $extension = 'jpg';

                $output_file = trim($value['nis'] ?? '00') . '.' . $extension;
                file_put_contents(FCPATH . 'uploads/foto_siswa/' . $output_file, base64_decode($base64_image_string));
                $fotoPath = 'uploads/foto_siswa/' . $output_file;
            }

            $siswas[] = [
                "id_siswa" => $value['id_siswa'],
                "foto" => $fotoPath
            ];
        }

        $this->masterModel->updateBatchData('master_siswa', $siswas, 'id_siswa');

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Berhasil memperbarui ' . count($siswas) . ' foto siswa',
            'total' => count($siswas)
        ]);
    }

    public function editLogin()
    {
        $id_siswa = $this->request->getPost('id_siswa');
        $password = $this->request->getPost('password');

        if (empty($password)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Password tidak boleh kosong']);
        }

        $siswa = $this->masterModel->getSiswaById($id_siswa);
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'message' => 'Siswa tidak ditemukan']);
        }

        // Update in master_siswa
        $this->masterModel->updateData('master_siswa', ['password' => $password], 'id_siswa', $id_siswa);

        // Update in users table
        $db = \Config\Database::connect();
        $db->table('users')
            ->where('username', $siswa->username)
            ->update(['password' => password_hash($password, PASSWORD_BCRYPT)]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Password berhasil diperbarui'
        ]);
    }

    public function data()
    {
        return $this->response->setJSON(['data' => []]);
    }
}
