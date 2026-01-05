<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;

/**
 * Cbtrekap Controller - Migrated from CI3
 * Handles CBT exam results recap
 */
class Cbtrekap extends BaseController
{
    protected CbtModel $cbtModel;
    protected DashboardModel $dashboardModel;
    protected MasterModel $masterModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel();
    }

    /**
     * Main index page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->inGroup('guru')) {
            return $this->showError('Hanya Administrator dan Guru yang diberi hak untuk mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Rekap Hasil Penilaian',
            'subjudul' => 'Penilaian',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'is_admin' => $this->isAdmin(),
            'rekaps' => $this->cekRekapSiswa(),
            'session_id' => session_id(),
        ];

        if (!$this->isAdmin()) {
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/rekap/rekap')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Check rekap siswa status
     */
    private function cekRekapSiswa(): array
    {
        $db = \Config\Database::connect();
        return $db->table('cbt_rekap_jadwal')->select('id_jadwal')->get()->getResult();
    }

    /**
     * Get jadwal (AJAX)
     */
    public function get_jadwal()
    {
        try {
            $db = \Config\Database::connect();

            $user = $this->getUser();
            $bank_where = [];

            if (!$this->isAdmin()) {
                $guru = $db->table('master_guru')
                    ->join('users', 'users.username = master_guru.nip')
                    ->where('users.id', $user->id)
                    ->get()->getRow();
                if ($guru) {
                    $bank_where['bank_guru_id'] = $guru->id_guru;
                }
            }

            $builder = $db->table('cbt_jadwal a');
            $builder->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.rekap, a.durasi_ujian, a.acak_soal, a.acak_opsi');
            $builder->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left');
            $builder->join('cbt_jenis j', 'a.id_jenis = j.id_jenis', 'left');

            if (!empty($bank_where)) {
                $builder->where($bank_where);
            }

            $builder->orderBy('a.tgl_mulai', 'DESC');
            $builder->limit(100);

            $jadwals = $builder->get()->getResult();

            $data = [
                'success' => true,
                'jadwal' => $jadwals,
                'total' => count($jadwals),
            ];

            return $this->respond(true, '', $data);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Get rekap (AJAX)
     */
    public function get_rekap()
    {
        try {
            $db = \Config\Database::connect();

            $user = $this->getUser();
            $rekap_where = [];

            if (!$this->isAdmin()) {
                $guru = $db->table('master_guru')
                    ->join('users', 'users.username = master_guru.nip')
                    ->where('users.id', $user->id)
                    ->get()->getRow();
                if ($guru) {
                    $rekap_where['id_guru'] = $guru->id_guru;
                }
            }

            $builder = $db->table('cbt_rekap_jadwal');
            $builder->select('id_rekap, id_tp, tp, id_smt, smt, id_jadwal, jadwal, id_bank, id_jenis, kode_jenis, tgl_mulai, tgl_selesai, bank_soal, kode_bank, level_bank, kelas_bank, id_guru, nama_mapel, pengawas');

            if (!empty($rekap_where)) {
                $builder->where($rekap_where);
            }

            $builder->limit(100);
            $rekaps = $builder->get()->getResult();

            // Unserialize data
            foreach ($rekaps as $rekap) {
                $rekap->jadwal = $this->maybeUnserialize($rekap->jadwal ?? '');
                $rekap->bank = $this->maybeUnserialize($rekap->bank_soal ?? '');
                $rekap->kelas_bank = $this->maybeUnserialize($rekap->kelas_bank ?? '[]');
                $rekap->pengawas = $this->maybeUnserialize($rekap->pengawas ?? '[]');
            }

            $data = [
                'success' => true,
                'rekap' => $rekaps,
                'total' => count($rekaps),
            ];

            return $this->respond(true, '', $data);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Save rekap
     */
    public function rekap()
    {
        try {
            $post = $this->request->getPost('rekap');
            $idsJadwal = json_decode($this->request->getPost('ids_jadwal') ?? '[]');
            $idBank = json_decode($this->request->getPost('ids_bank') ?? '[]');

            if (!$post || !is_array($post)) {
                return $this->respond(false, 'Data tidak valid');
            }

            $db = \Config\Database::connect();
            $inserted = 0;
            $updated = 0;

            foreach ($post as $detail) {
                try {
                    $id_rekap = $detail['id_rekap'] ?? '';
                    unset($detail['id_rekap']);

                    if (empty($id_rekap)) {
                        $db->table('cbt_rekap_jadwal')->insert($detail);
                        $inserted++;
                    } else {
                        $db->table('cbt_rekap_jadwal')->where('id_rekap', $id_rekap)->update($detail);
                        $updated++;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return $this->respond(true, 'Berhasil', [
                'inserted' => $inserted,
                'updated' => $updated,
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Rekap nilai siswa
     */
    public function rekap_nilai_siswa()
    {
        try {
            $idJadwal = json_decode($this->request->getPost('idjadwal') ?? '[]');

            if (empty($idJadwal)) {
                return $this->respond(false, 'ID Jadwal tidak valid');
            }

            $db = \Config\Database::connect();

            // Get CBT siswa data
            $cbtSiswa = $db->table('cbt_siswa a')
                ->select('a.id_siswa, a.id_jadwal, a.id_bank, a.nilai_input, a.jawaban, a.nilai, a.mulai, a.selesai, a.status, a.lama_ujian, a.pelanggaran, j.id_tp, j.id_smt, s.id_kelas, s.nama as nama_siswa')
                ->join('cbt_jadwal j', 'a.id_jadwal = j.id_jadwal')
                ->join('master_siswa s', 'a.id_siswa = s.id_siswa', 'left')
                ->whereIn('a.id_jadwal', $idJadwal)
                ->get()->getResult();

            $for_insert = [];
            $for_update = [];

            // Get existing rekap
            $existingRekap = $db->table('cbt_rekap_siswa')
                ->whereIn('id_jadwal', $idJadwal)
                ->get()->getResult();

            $rekapMap = [];
            foreach ($existingRekap as $rekap) {
                $rekapMap[$rekap->id_jadwal . '_' . $rekap->id_siswa] = $rekap->id_rekap_siswa;
            }

            foreach ($cbtSiswa as $siswa) {
                if ($siswa->selesai != null) {
                    $key = $siswa->id_jadwal . '_' . $siswa->id_siswa;

                    $insert = [
                        'id_siswa' => $siswa->id_siswa,
                        'nama_siswa' => $siswa->nama_siswa,
                        'id_tp' => $siswa->id_tp,
                        'id_smt' => $siswa->id_smt,
                        'id_jadwal' => $siswa->id_jadwal,
                        'id_bank' => $siswa->id_bank,
                        'id_kelas' => $siswa->id_kelas,
                        'jawaban' => $siswa->jawaban,
                        'nilai' => $siswa->nilai,
                        'nilai_input' => $siswa->nilai_input,
                        'status' => $siswa->status,
                        'mulai' => $siswa->mulai,
                        'selesai' => $siswa->selesai,
                        'lama_ujian' => $siswa->lama_ujian,
                        'pelanggaran' => $siswa->pelanggaran,
                    ];

                    if (isset($rekapMap[$key])) {
                        $insert['id_rekap_siswa'] = $rekapMap[$key];
                        $for_update[] = $insert;
                    } else {
                        $for_insert[] = $insert;
                    }
                }
            }

            if (count($for_insert) > 0) {
                $db->table('cbt_rekap_siswa')->insertBatch($for_insert);
            }
            if (count($for_update) > 0) {
                $db->table('cbt_rekap_siswa')->updateBatch($for_update, 'id_rekap_siswa');
            }

            // Update jadwal rekap status
            if (count($for_insert) > 0 || count($for_update) > 0) {
                $db->table('cbt_jadwal')->whereIn('id_jadwal', $idJadwal)->update(['rekap' => '1']);
            }

            return $this->respond(true, 'Berhasil', [
                'inserted' => count($for_insert),
                'updated' => count($for_update),
                'cbt' => count($cbtSiswa),
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * View koreksi page
     */
    public function view_koreksi()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Rekap Nilai Siswa',
            'subjudul' => 'Koreksi Rekap',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'kelas_selected' => $this->request->getGet('id_kelas'),
            'jadwal_selected' => $this->request->getGet('id_jadwal'),
            'is_rekap' => true,
        ];

        if (!$this->isAdmin()) {
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/nilai/data_nilai')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Koreksi nilai
     */
    public function koreksi()
    {
        try {
            $idJadwal = $this->request->getPost('id_jadwal');
            $idSiswa = $this->request->getPost('id_siswa');
            $nilaiInput = $this->request->getPost('nilai_input');

            if (!$idJadwal || !$idSiswa) {
                return $this->respond(false, 'Data tidak valid');
            }

            $db = \Config\Database::connect();

            // Update cbt_siswa
            $db->table('cbt_siswa')
                ->where('id_jadwal', $idJadwal)
                ->where('id_siswa', $idSiswa)
                ->update(['nilai_input' => $nilaiInput]);

            // Update cbt_rekap_siswa
            $db->table('cbt_rekap_siswa')
                ->where('id_jadwal', $idJadwal)
                ->where('id_siswa', $idSiswa)
                ->update(['nilai_input' => $nilaiInput]);

            return $this->respond(true, 'Nilai berhasil dikoreksi');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Input essai
     */
    public function inputEssai()
    {
        try {
            $idJadwal = $this->request->getPost('id_jadwal');
            $idSiswa = $this->request->getPost('id_siswa');
            $nilaiInput = $this->request->getPost('nilai_input');

            if (!$idJadwal || !$idSiswa) {
                return $this->respond(false, 'Data tidak valid');
            }

            $db = \Config\Database::connect();

            // Update cbt_siswa
            $db->table('cbt_siswa')
                ->where('id_jadwal', $idJadwal)
                ->where('id_siswa', $idSiswa)
                ->update(['nilai_input' => $nilaiInput]);

            // Update cbt_rekap_siswa
            $db->table('cbt_rekap_siswa')
                ->where('id_jadwal', $idJadwal)
                ->where('id_siswa', $idSiswa)
                ->update(['nilai_input' => $nilaiInput]);

            return $this->respond(true, 'Nilai essai berhasil disimpan');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Hapus rekap
     */
    public function hapusRekap()
    {
        try {
            $idRekap = $this->request->getPost('id_rekap');
            $idJadwal = $this->request->getPost('id_jadwal');

            if (!$idRekap) {
                return $this->respond(false, 'ID Rekap tidak valid');
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // Delete rekap siswa
            $db->table('cbt_rekap_siswa')->where('id_jadwal', $idJadwal)->delete();

            // Delete rekap jadwal
            $db->table('cbt_rekap_jadwal')->where('id_rekap', $idRekap)->delete();

            // Update jadwal
            $db->table('cbt_jadwal')->where('id_jadwal', $idJadwal)->update(['rekap' => '0']);

            $db->transComplete();

            return $this->respond($db->transStatus(), $db->transStatus() ? 'Rekap berhasil dihapus' : 'Gagal menghapus rekap');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Ekspor rekap
     */
    public function ekspor_rekap()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Ekspor Rekap',
            'subjudul' => 'Ekspor Data Rekap',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/rekap/ekspor_rekap')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Get nilai rekap (AJAX)
     */
    public function get_nilai_rekap()
    {
        try {
            $idJadwal = $this->request->getGet('id_jadwal');
            $idKelas = $this->request->getGet('id_kelas');

            if (!$idJadwal) {
                return $this->respond(false, 'ID Jadwal tidak valid');
            }

            $db = \Config\Database::connect();

            $builder = $db->table('cbt_rekap_siswa a');
            $builder->select('a.*, s.nama, s.nis, s.nisn');
            $builder->join('master_siswa s', 's.id_siswa = a.id_siswa', 'left');
            $builder->where('a.id_jadwal', $idJadwal);

            if ($idKelas) {
                $builder->where('a.id_kelas', $idKelas);
            }

            $hasil = $builder->get()->getResult();

            foreach ($hasil as $h) {
                $h->nilai_input = $this->maybeUnserialize($h->nilai_input ?? '');
            }

            return $this->respond(true, '', ['nilai' => $hasil]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Get nilai kelas (AJAX)
     */
    public function getNilaiKelas()
    {
        try {
            $idKelas = $this->request->getGet('id_kelas');
            $idJadwal = $this->request->getGet('id_jadwal');

            $db = \Config\Database::connect();

            $builder = $db->table('cbt_rekap_siswa a');
            $builder->select('a.*, s.nama, s.nis');
            $builder->join('master_siswa s', 's.id_siswa = a.id_siswa', 'left');

            if ($idKelas) {
                $builder->where('a.id_kelas', $idKelas);
            }
            if ($idJadwal) {
                $builder->where('a.id_jadwal', $idJadwal);
            }

            $hasil = $builder->get()->getResult();

            foreach ($hasil as $h) {
                $h->nilai_input = $this->maybeUnserialize($h->nilai_input ?? '');
            }

            return $this->respond(true, '', ['nilai' => $hasil]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Maybe unserialize data
     */
    private function maybeUnserialize($data)
    {
        if (is_string($data)) {
            $unserialized = @unserialize($data);
            if ($unserialized !== false || $data === 'b:0;') {
                return $unserialized;
            }
            $decoded = json_decode($data);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $data;
    }
}
