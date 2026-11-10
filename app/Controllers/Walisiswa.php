<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\MasterModel;
use CodeIgniter\Controller;

class Walisiswa extends BaseController
{
    protected $dashboard;
    protected $master;

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
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
        $kelas = $this->master->getKelasById($guru->wali_kelas);

        $data = [
            'user' => $user,
            'judul' => 'Daftar Siswa',
            'subjudul' => 'Siswa Kelas ' . ($kelas ? $kelas->nama_kelas : '-'),
            'setting' => $this->dashboard->getSetting(),
            'guru' => $guru,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'siswas' => $this->master->getDataSiswaByKelas($tp->id_tp, $smt->id_smt, $guru->wali_kelas, 0, 0),
        ];

        return view('members/guru/wali/kelas', $data);
    }

    public function list()
    {
        $page = $this->request->getPost('page') ?? 1;
        $limit = $this->request->getPost('limit') ?? 10;
        $search = $this->request->getPost('search');
        $id_kelas = $this->request->getPost('kelas');

        $offset = ($page - 1) * $limit;
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $count_siswa = $this->master->getDataSiswaByKelasPage($tp->id_tp, $smt->id_smt, $id_kelas, $search);
        $lists = $this->master->getDataSiswaByKelas($tp->id_tp, $smt->id_smt, $id_kelas, $offset, $limit, $search);

        $data = [
            'lists' => $lists,
            'total' => $count_siswa,
            'pages' => ceil($count_siswa / $limit),
            'search' => $search,
            'perpage' => $limit
        ];

        return $this->response->setJSON($data);
    }

    public function edit($id)
    {
        $siswa = $this->master->getSiswaById($id);

        $user = $this->dashboard->getUsers(session()->get('user_id'));
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Siswa',
            'subjudul' => 'Edit Data Siswa',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'siswa' => $siswa,
            'guru' => $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt),
        ];

        return view('members/guru/wali/edit', $data);
    }

    public function updatedata()
    {
        $id_siswa = $this->request->getPost('id_siswa');
        $nis = $this->request->getPost('nis');

        $siswa = $this->master->getSiswaById($id_siswa);

        // Validation logic can be more complex in CI4, but for now we follow CI3 pattern
        $input = [
            'nisn' => $this->request->getPost('nisn'),
            'nis' => $this->request->getPost('nis'),
            'nama' => $this->request->getPost('nama'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
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
            'pendidikan_wali' => $this->request->getPost('pendidikan_wali'),
            'pekerjaan_wali' => $this->request->getPost('pekerjaan_wali'),
            'nohp_wali' => $this->request->getPost('nohp_wali'),
            'alamat_wali' => $this->request->getPost('alamat_wali'),
            'tahun_masuk' => $this->request->getPost('tahun_masuk'),
            'kelas_awal' => $this->request->getPost('kelas_awal'),
            'sekolah_asal' => $this->request->getPost('sekolah_asal'),
            'tgl_lahir_ayah' => $this->request->getPost('tgl_lahir_ayah'),
            'tgl_lahir_ibu' => $this->request->getPost('tgl_lahir_ibu'),
            'tgl_lahir_wali' => $this->request->getPost('tgl_lahir_wali'),
        ];

        $action = $this->master->updateData('master_siswa', $input, 'id_siswa', $id_siswa);

        return $this->response->setJSON([
            'status' => $action,
            'text' => $action ? 'Siswa berhasil diperbaharui' : 'Gagal memperbaharui siswa'
        ]);
    }

    public function uploadfile($id_siswa)
    {
        $siswa = $this->master->getSiswaById($id_siswa);
        $file = $this->request->getFile('foto');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $siswa->nis . '.' . $file->getExtension();
            $file->move('./uploads/foto_siswa/', $newName, true);

            $this->master->updateData('master_siswa', ['foto' => 'uploads/foto_siswa/' . $newName], 'id_siswa', $id_siswa);

            return $this->response->setJSON([
                'status' => true,
                'src' => base_url('uploads/foto_siswa/' . $newName)
            ]);
        }

        return $this->response->setJSON(['status' => false]);
    }

    public function deletefile($id_siswa)
    {
        $src = $this->request->getPost('src');
        $file_name = str_replace(base_url(), '', $src ?? '');

        if ($file_name != 'assets/img/siswa.png' && !empty($file_name)) {
            if (file_exists($file_name)) {
                unlink($file_name);
            }
            $this->master->updateData('master_siswa', ['foto' => ''], 'id_siswa', $id_siswa);
            return $this->response->setJSON(['status' => true]);
        }
        return $this->response->setJSON(['status' => false]);
    }
}
