<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\LogModel;
use App\Models\AuthModel;
use App\Models\MasterModel;

class Cbtactivate extends BaseController
{
    protected $cbt;
    protected $dashboard;
    protected $dropdown;
    protected $logging;
    protected $auth;
    protected $master;

    public function __construct()
    {
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->dropdown = new DropdownModel();
        $this->logging = new LogModel();
        $this->auth = new AuthModel();
        $this->master = new MasterModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        if (!$this->isAdmin() && !$this->isGuru()) {
            return $this->showError('Hanya Administrator yang diberi hak untuk mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'Aktivasi Peserta',
            'subjudul' => 'Aktifkan/Nonaktifkan Akun Siswa',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $db = \Config\Database::connect();

        if ($this->isAdmin()) {
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();

            $jadwals = $this->cbt->getAllJadwal($tp->id_tp, $smt->id_smt);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $bankKelas = $this->maybeUnserialize($jad->bank_kelas ?? '[]');
                foreach ($bankKelas as $kl) {
                    if (isset($kl['kelas_id'])) {
                        $arrKls[] = $kl['kelas_id'];
                    }
                }
            }
            $data['ruangs'] = $this->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);
            return $this->renderView('cbt/aktivasi/data', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();
            $data['pengawas'] = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt);

            $jadwals = $db->table('cbt_jadwal a')
                ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank')
                ->where('a.id_tp', $tp->id_tp)
                ->where('a.id_smt', $smt->id_smt)
                ->where('b.bank_guru_id', $guru->id_guru)
                ->get()->getResult();

            $arrKls = [];
            foreach ($jadwals as $jad) {
                $bankKelas = $this->maybeUnserialize($jad->bank_kelas ?? '[]');
                foreach ($bankKelas as $kl) {
                    if (isset($kl['kelas_id'])) {
                        $arrKls[] = $kl['kelas_id'];
                    }
                }
            }
            $data['ruangs'] = $this->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);
            return $this->renderView('cbt/aktivasi/data', $data);
        }
    }

    /**
     * Guard endpoint agar hanya admin/guru yang boleh mengubah status aktivasi.
     */
    private function authorize(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return $this->isAdmin() || $this->isGuru();
    }

    private function denyAccess()
    {
        return $this->response->setStatusCode(403)->setJSON([
            'status' => false,
            'msg' => 'Anda tidak memiliki akses untuk melakukan aksi ini.'
        ]);
    }

    private function getDistinctRuang($tp, $smt, $arrKelas)
    {
        if (empty($arrKelas)) {
            return [];
        }

        $db = \Config\Database::connect();
        $builder = $db->table('cbt_sesi_siswa a');
        $builder->distinct();
        $builder->select('a.ruang_id, b.kode_ruang, b.nama_ruang, a.sesi_id, c.kode_sesi, c.nama_sesi');
        $builder->join('cbt_ruang b', 'a.ruang_id = b.id_ruang');
        $builder->join('cbt_sesi c', 'a.sesi_id = c.id_sesi');
        $builder->where('a.tp_id', $tp);
        $builder->where('a.smt_id', $smt);
        $builder->whereIn('a.kelas_id', $arrKelas);
        $builder->orderBy('b.nama_ruang', 'ASC');
        $builder->orderBy('c.nama_sesi', 'ASC');

        $result = $builder->get()->getResult();
        $ret = [];
        if ($result) {
            foreach ($result as $value) {
                $ret[$value->ruang_id][] = $value;
            }
        }
        return $ret;
    }

    public function getSiswaRuang()
    {
        if (!$this->authorize()) {
            return $this->denyAccess();
        }

        $ruang = $this->request->getGet('ruang');
        $sesi = $this->request->getGet('sesi');

        if (empty($ruang) || empty($sesi)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Ruang dan sesi harus diisi.']);
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $db = \Config\Database::connect();
        $siswaBuilder = $db->table('cbt_sesi_siswa a')
            ->select('a.siswa_id, a.ruang_id, a.sesi_id, a.kelas_id, b.nama, b.nis, b.username, b.password, b.foto, c.nama_kelas, d.id as user_id, d.active as user_active')
            ->join('master_siswa b', 'a.siswa_id = b.id_siswa')
            ->join('master_kelas c', 'a.kelas_id = c.id_kelas')
            ->join('users d', 'b.username = d.username', 'left')
            ->where('a.ruang_id', $ruang)
            ->where('a.sesi_id', $sesi)
            ->where('a.tp_id', $tp->id_tp)
            ->where('a.smt_id', $smt->id_smt)
            ->orderBy('b.nama', 'ASC');
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
        $this->onlySiswaAktif($siswaBuilder, 'a', 'bi', 'siswa_id');
        $siswas = $siswaBuilder->get()->getResult();

        foreach ($siswas as $siswa) {
            // Jangan ekspos hash password ke client.
            $password = $siswa->password ?? '';
            $siswa->password_plain = null;
            $siswa->password_default = ($password === '' || strpos($password, '$2y$') === 0) ? null : $password;
            unset($siswa->password);

            $siswa->has_account = !empty($siswa->user_id);

            $siswa->reset = '0';
            $attempts = $db->table('login_attempts')->where('login', $siswa->username)->countAllResults();
            if ($attempts > 0) {
                $siswa->reset = '1';
            }
        }

        return $this->response->setJSON(['status' => true, 'siswa' => $siswas]);
    }

    public function aktifkan_semua()
    {
        if (!$this->authorize()) {
            return $this->denyAccess();
        }

        $posts = $this->request->getPost('ids');
        $active = $this->request->getPost('active');

        if (!$posts || !is_array($posts)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Tidak ada siswa dipilih']);
        }

        $ids = array_filter(array_map('intval', $posts), fn ($v) => $v > 0);

        if (empty($ids)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data siswa tidak valid']);
        }

        $result = $this->applyActivation($ids, $active === 'aktifkan');

        $this->logging->saveLog(3, ($active === 'aktifkan' ? 'Mengaktifkan' : 'Menonaktifkan') . " {$result['jumlah']} siswa");

        return $this->response->setJSON([
            'status' => true,
            'jumlah' => $result['jumlah'],
            'msg' => $result['msg']
        ]);
    }

    public function aktifkan_sesi()
    {
        if (!$this->authorize()) {
            return $this->denyAccess();
        }

        $posts = $this->request->getPost('ids');
        $active = $this->request->getPost('active');

        if (!$posts || !is_array($posts)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Tidak ada sesi dipilih']);
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $arrRuang = [];
        $arrSesi = [];
        foreach ($posts as $post) {
            $split = explode('-', (string) $post);
            if (count($split) == 2) {
                $arrRuang[] = (int) $split[0];
                $arrSesi[] = (int) $split[1];
            }
        }

        if (empty($arrRuang)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data tidak valid']);
        }

        $db = \Config\Database::connect();
        $siswaBuilder = $db->table('cbt_sesi_siswa a')
            ->select('a.siswa_id')
            ->join('master_siswa b', 'a.siswa_id = b.id_siswa')
            ->where('a.tp_id', $tp->id_tp)
            ->where('a.smt_id', $smt->id_smt)
            ->whereIn('a.ruang_id', $arrRuang)
            ->whereIn('a.sesi_id', $arrSesi);
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
        $this->onlySiswaAktif($siswaBuilder, 'a', 'bi', 'siswa_id');
        $siswas = $siswaBuilder->get()->getResult();

        if (!$siswas) {
            return $this->response->setJSON(['status' => true, 'msg' => 'Tidak ada siswa di sesi terpilih']);
        }

        $ids = array_unique(array_map(fn ($s) => (int) $s->siswa_id, $siswas));
        $result = $this->applyActivation($ids, $active === 'aktifkan');

        $this->logging->saveLog(3, ($active === 'aktifkan' ? 'Mengaktifkan' : 'Menonaktifkan') . " {$result['jumlah']} siswa sesi");

        return $this->response->setJSON([
            'status' => true,
            'jumlah' => $result['jumlah'],
            'msg' => $result['msg']
        ]);
    }

    /**
     * Terapkan status aktif/nonaktif untuk daftar id siswa (master_siswa).
     * Siswa yang belum punya akun di tabel users akan didaftarkan otomatis
     * (dengan hashPasswordSafe agar tidak double-hash password dari Dapodik).
     *
     * @param int[] $siswaIds
     * @param bool  $active
     * @return array{jumlah:int,msg:string}
     */
    private function applyActivation(array $siswaIds, bool $active): array
    {
        $db = \Config\Database::connect();
        $success = 0;
        $created = 0;
        $skipped = 0;

        foreach ($siswaIds as $id_siswa) {
            $siswa = $this->master->getSiswa((int) $id_siswa);
            if (!$siswa || empty($siswa->username)) {
                $skipped++;
                continue;
            }

            $user = $this->auth->isExist($siswa->username);

            if ($user) {
                $this->master->updateData('users', ['active' => $active ? '1' : '0'], 'id', $user->id);
                $success++;
            } elseif ($active) {
                // Belum ada akun -> daftarkan baru (group 3 = siswa)
                $user_data = [
                    'username' => $siswa->username,
                    'password' => hashPasswordSafe($siswa->password ?? '123456'),
                    'first_name' => $siswa->nama,
                    'active' => '1'
                ];
                try {
                    $this->auth->register($user_data, '3');
                    $created++;
                    $success++;
                } catch (\Exception $e) {
                    $skipped++;
                    log_message('error', 'Cbtactivate: gagal daftar siswa ' . $siswa->username . ' - ' . $e->getMessage());
                }
            } else {
                // Tidak aktif dan belum punya akun -> tidak ada yang perlu dinonaktifkan
                $skipped++;
            }
        }

        $jumlah = $success + $created;
        if ($active) {
            $msg = "{$jumlah} siswa diaktifkan";
        } else {
            $msg = "{$jumlah} siswa dinonaktifkan";
        }
        if ($created > 0) {
            $msg .= " ({$created} akun baru dibuat)";
        }
        if ($skipped > 0) {
            $msg .= ", {$skipped} dilewati";
        }

        return ['jumlah' => $jumlah, 'msg' => $msg];
    }

    private function maybeUnserialize($data)
    {
        if (is_array($data))
            return $data;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            $unserialized = @unserialize($data);
            if ($unserialized !== false) {
                return $unserialized;
            }
        }
        return [];
    }
}
