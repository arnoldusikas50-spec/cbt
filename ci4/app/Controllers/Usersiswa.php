<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Usersiswa Controller - Migrated from CI3
 */
class Usersiswa extends BaseController
{
    protected AuthModel $authModel;
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'User Siswa',
            'subjudul' => 'Kelola Akun Siswa',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'kelases' => $tp && $smt ? $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt) : [],
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('users/siswa/data', $data)
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized', 'data' => []]);
        }

        try {
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();
            $idKelas = $this->request->getGet('id_kelas');

            if (!$tp || !$smt) {
                return $this->response->setJSON(['data' => []]);
            }

            $db = \Config\Database::connect();
            $builder = $db->table('master_siswa a')
                ->select('a.*, b.id_kelas, c.nama_kelas')
                ->join('kelas_siswa b', 'a.id_siswa = b.id_siswa')
                ->join('master_kelas c', 'b.id_kelas = c.id_kelas')
                ->where('b.id_tp', $tp->id_tp)
                ->where('b.id_smt', $smt->id_smt);

            if ($idKelas) {
                $builder->where('b.id_kelas', $idKelas);
            }

            $siswas = $builder->get()->getResult();

            $data = [];
            foreach ($siswas as $i => $s) {
                $data[] = [
                    'no' => $i + 1,
                    'id_siswa' => $s->id_siswa,
                    'nama' => $s->nama,
                    'nis' => $s->nis ?? '-',
                    'kelas' => $s->nama_kelas ?? '-',
                    'username' => $s->username ?? '-',
                    'has_password' => !empty($s->password),
                ];
            }

            return $this->response->setJSON(['data' => $data]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function resetPassword()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $idSiswa = (int) $this->request->getPost('id_siswa');
        $password = $this->request->getPost('password');

        if (!$password) {
            return $this->response->setJSON(['status' => false, 'message' => 'Password wajib diisi']);
        }

        $result = $this->masterModel->updateSiswa($idSiswa, [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        return $result
            ? $this->response->setJSON(['status' => true, 'message' => 'Password berhasil direset'])
            : $this->response->setJSON(['status' => false, 'message' => 'Gagal reset password']);
    }

    public function resetAll()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $idKelas = $this->request->getPost('id_kelas');
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tahun/Semester tidak aktif']);
        }

        $db = \Config\Database::connect();

        // Get siswa in kelas
        $siswaIds = $db->table('kelas_siswa')
            ->select('id_siswa')
            ->where('id_kelas', $idKelas)
            ->where('id_tp', $tp->id_tp)
            ->where('id_smt', $smt->id_smt)
            ->get()
            ->getResultArray();

        $ids = array_column($siswaIds, 'id_siswa');

        if (empty($ids)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada siswa di kelas ini']);
        }

        // Reset password to NIS for all
        foreach ($ids as $id) {
            $siswa = $this->masterModel->getSiswa((int) $id);
            if ($siswa && $siswa->nis) {
                $db->table('master_siswa')
                    ->where('id_siswa', $id)
                    ->update(['password' => password_hash($siswa->nis, PASSWORD_DEFAULT)]);
            }
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Password berhasil direset ke NIS']);
    }
}
