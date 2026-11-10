<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\LogModel;

class Cbtpengawas extends BaseController
{
    protected $cbt;
    protected $dashboard;
    protected $dropdown;
    protected $logging;

    public function __construct()
    {
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->dropdown = new DropdownModel();
        $this->logging = new LogModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        // Allow admin OR panitia dengan jabatan 'panitia' (not 'pengawas')
        if (!$this->checkPanitiaAccess()) {
            return $this->showError('Hanya Administrator atau Panitia dengan jabatan Panitia yang diberi hak untuk mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Atur Pengawas',
            'subjudul' => 'Pengawas Ujian/Ulangan',
            'setting' => $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['gurus'] = $this->dropdown->getAllGuru();

        $db = \Config\Database::connect();
        // Distinct id_jenis
        $id_jenis = $db->table('cbt_jadwal')
            ->select('id_jenis')
            ->distinct()
            ->where('id_tp', $tp->id_tp)
            ->where('id_smt', $smt->id_smt)
            ->get()->getResult();

        $ids = [];
        if ($id_jenis) {
            foreach ($id_jenis as $jenis) {
                $ids[] = $jenis->id_jenis;
            }
        }

        if (count($ids) > 0) {
            //$data['jenis'] = $this->cbt->getAllJenisUjianByArrJenis($ids);

            $jenisBuilder = $db->table('cbt_jenis')->whereIn('id_jenis', $ids)->get()->getResult();
            $jenisArr = ['' => '-- Pilih Jenis Penilaian --'];
            foreach ($jenisBuilder as $j) {
                $jenisArr[$j->id_jenis] = $j->nama_jenis . ' (' . $j->kode_jenis . ')';
            }
            $data['jenis'] = $jenisArr;
        } else {
            $data['jenis'] = ['' => 'belum ada jadwal ujian'];
        }

        return $this->renderView('cbt/pengawas/data', $data);
    }

    public function get_tanggal()
    {
        if (!$this->checkPanitiaAccess()) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 403);
        }

        $jenis = trim((string) $this->request->getGet('jenis'));
        if ($jenis === '') {
            return $this->response->setJSON([]);
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $db = \Config\Database::connect();
        $result = $db->table('cbt_jadwal')
            ->select('tgl_mulai')
            ->distinct()
            ->where('id_jenis', $jenis)
            ->where('id_tp', $tp->id_tp)
            ->where('id_smt', $smt->id_smt)
            ->orderBy('tgl_mulai', 'ASC')
            ->get()->getResult();

        // Key = tanggal (dipakai sebagai value <option>), Value = tanggal ISO
        $ret = [];
        foreach ($result as $row) {
            $tgl = trim((string) ($row->tgl_mulai ?? ''));
            if ($tgl === '' || isset($ret[$tgl])) {
                continue;
            }
            $ret[$tgl] = $tgl;
        }

        return $this->response->setJSON($ret);
    }

    public function get_pengawas()
    {
        if (!$this->checkPanitiaAccess()) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 403);
        }

        $jenis_selected = trim((string) $this->request->getPost('jenis'));
        $tanggal_selected = trim((string) $this->request->getPost('tanggal'));

        if ($jenis_selected === '' || $tanggal_selected === '') {
            return $this->response->setJSON([
                'error' => 'Jenis penilaian dan tanggal ujian wajib dipilih',
            ], 422);
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $db = \Config\Database::connect();

        $data = [
            'today'         => date('Y-m-d'),
            'jenis'         => $jenis_selected,
            'tanggal'       => $tanggal_selected,
            'gurus'         => (object) $this->dropdown->getAllGuru(),
            'kelases'       => (object) $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt),
            'sesisiswa'     => [],
            'cbtsiswa'      => [],
            'ruangs'        => [],
            'arr_kelas'     => [],
            'jadwals_ujian' => [],
        ];

        // Panitia pengawas ikut masuk daftar pilihan pengawas
        $panitias = $this->dropdown->getAllPanitiaPengawas();
        $panitia_map = [];
        foreach ($panitias as $p) {
            $p_id = 'P' . $p->id_panitia;
            $data['gurus']->{$p_id} = $p->nama_panitia . ' (Pengawas)';
            $ruang = trim((string) ($p->ruang ?? ''));
            if ($ruang !== '') {
                $panitia_map[$ruang][] = $p_id;
            }
        }
        $data['panitia_ruang'] = (object) $panitia_map;

        // 1. Jadwal ujian pada filter aktif
        $jadwals = $db->table('cbt_jadwal a')
            ->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.status, a.jam_ke')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt)
            ->where('a.tgl_mulai', $tanggal_selected)
            ->where('a.id_jenis', $jenis_selected)
            ->orderBy('a.jam_ke', 'ASC')
            ->orderBy('a.id_jadwal', 'ASC')
            ->get()->getResult();

        if (!$jadwals) {
            $data['pengawas'] = new \stdClass();
            return $this->response->setJSON($data);
        }

        // 2. Tempelkan bank soal & mapel, kumpulkan kelas target
        $arrKelas = [];
        $ids_jadwal = [];
        $jadwals_ujian = [];

        foreach ($jadwals as $jadwal) {
            $ids_jadwal[] = (int) $jadwal->id_jadwal;

            $bank = $db->table('cbt_bank_soal b')
                ->select('b.bank_kode, b.bank_kelas, b.soal_agama, b.bank_guru_id, m.kode as kode_mapel, m.nama_mapel')
                ->join('master_mapel m', 'b.bank_mapel_id = m.id_mapel', 'left')
                ->where('b.id_bank', $jadwal->id_bank)
                ->get()->getRow();

            // Jadwal tanpa bank soal tidak bisa dialokasikan pengawas
            if (!$bank) {
                continue;
            }

            // Reformat sebagai nested object 'bank' -> 'mapel' untuk sesuai ekspektasi JS
            $bankObj = clone $bank;
            $bankObj->mapel = (object) ['kode' => $bank->kode_mapel, 'nama_mapel' => $bank->nama_mapel];
            unset($bankObj->kode_mapel);
            unset($bankObj->nama_mapel);

            $bankKelas = $this->maybeUnserialize($bank->bank_kelas ?? []);
            $bankObj->bank_kelas = [];
            foreach ($bankKelas as $bk) {
                $kId = $this->extractKelasId($bk);
                if ($kId === null || $kId === '') {
                    continue;
                }
                $kId = (string) $kId;
                $bankObj->bank_kelas[] = $kId;
                if (!in_array($kId, $arrKelas, true)) {
                    $arrKelas[] = $kId;
                }
            }

            $jadwal->bank = $bankObj;
            $jadwals_ujian[] = $jadwal;
        }

        $data['jadwals_ujian'] = $jadwals_ujian;
        $data['arr_kelas'] = $arrKelas;

        // 3. Ruang/sesi allotment untuk kelas target
        $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKelas);
        $data['ruangs'] = array_values($ruangs);

        // 4. Pengawas yang sudah tersimpan (duplikat digabung)
        $data['pengawas'] = $this->buildPengawasObject(
            $this->cbt->getPengawasMapByJadwal($tp->id_tp, $smt->id_smt, $ids_jadwal)
        );

        return $this->response->setJSON($data);
    }

    public function savePengawas()
    {
        if (!$this->checkPanitiaAccess()) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 403);
        }

        $inputRaw = $this->request->getPost('data');
        $input = json_decode((string) $inputRaw, true);

        if (!is_array($input)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data pengawas tidak valid',
            ], 422);
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $id_tp = $tp->id_tp;
        $id_smt = $smt->id_smt;

        $db = \Config\Database::connect();

        // 1. Kumpulkan kombinasi (jadwal, ruang, sesi) yang dikirim
        $combos = [];
        $idJadwals = [];
        foreach ($input as $d) {
            $d = (array) $d;
            $jadwal = (int) ($d['jadwal'] ?? 0);
            $ruang  = (int) ($d['ruang'] ?? 0);
            $sesi   = (int) ($d['sesi'] ?? 0);

            if ($jadwal <= 0 || $ruang <= 0 || $sesi <= 0) {
                continue;
            }

            $guru = [];
            foreach ((array) ($d['guru'] ?? []) as $g) {
                $g = trim((string) $g);
                if ($g === '' || $g === '0') {
                    continue;
                }
                $guru[$g] = true;
            }

            $key = $jadwal . '-' . $ruang . '-' . $sesi;
            $combos[$key] = [
                'id_jadwal' => $jadwal,
                'id_ruang'  => $ruang,
                'id_sesi'   => $sesi,
                'id_guru'   => array_keys($guru),
            ];
            $idJadwals[$jadwal] = true;
        }

        if (empty($combos)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tidak ada alokasi pengawas yang dikirim',
                'insert'  => [],
                'update'  => [],
                'delete'  => 0,
            ], 422);
        }

        $idJadwals = array_keys($idJadwals);

        // 2. Assignment yang sudah ada untuk jadwal-jadwal tersebut
        $existing = [];
        foreach ($db->table('cbt_pengawas')
            ->select('id_pengawas, id_jadwal, id_ruang, id_sesi, id_guru, jam_ke')
            ->where('id_tp', $id_tp)
            ->where('id_smt', $id_smt)
            ->whereIn('id_jadwal', $idJadwals)
            ->orderBy('id_pengawas', 'ASC')
            ->get()->getResult() as $row) {
            $key = (int) $row->id_jadwal . '-' . (int) $row->id_ruang . '-' . (int) $row->id_sesi;
            $existing[$key][] = $row;
        }

        // 3. Pengawas yang valid: guru + panitia pengawas.
        //    ID yang sudah tersimpan sebelumnya tetap dipertahankan
        //    (mis. guru yang akunnya sudah dihapus) agar data tidak hilang.
        $validGuru = [];
        foreach (array_keys($this->dropdown->getAllGuru()) as $id) {
            $validGuru[(string) $id] = true;
        }
        foreach ($this->dropdown->getAllPanitiaPengawas() as $p) {
            $validGuru['P' . $p->id_panitia] = true;
        }
        foreach ($existing as $rows) {
            foreach ($rows as $row) {
                foreach (explode(',', (string) ($row->id_guru ?? '')) as $g) {
                    $g = trim($g);
                    if ($g !== '' && $g !== '0') {
                        $validGuru[$g] = true;
                    }
                }
            }
        }

        // Buang id pengawas yang tidak dikenal
        foreach ($combos as $key => $combo) {
            $guru = [];
            foreach ($combo['id_guru'] as $g) {
                if (isset($validGuru[$g])) {
                    $guru[$g] = true;
                }
            }
            $combos[$key]['id_guru'] = array_keys($guru);
        }

        // 4. Jam ke mengikuti jadwal (sumber kebenaran), minimal 1
        $jamMap = [];
        foreach ($db->table('cbt_jadwal')->select('id_jadwal, jam_ke')->whereIn('id_jadwal', $idJadwals)->get()->getResult() as $row) {
            $jam = (int) ($row->jam_ke ?? 0);
            $jamMap[(int) $row->id_jadwal] = $jam > 0 ? $jam : 1;
        }

        $for_insert = [];
        $for_update = [];
        $to_delete = [];

        $db->transStart();

        foreach ($combos as $key => $c) {
            $rows = $existing[$key] ?? [];
            $idGuru = implode(',', $c['id_guru']);
            $jamKe = $jamMap[$c['id_jadwal']] ?? 1;

            // Tanpa pengawas -> assignment dihapus, bukan disimpan kosong
            if ($idGuru === '') {
                foreach ($rows as $row) {
                    $to_delete[] = (int) $row->id_pengawas;
                }
                continue;
            }

            $dataRow = [
                'id_jadwal' => $c['id_jadwal'],
                'id_tp'     => $id_tp,
                'id_smt'    => $id_smt,
                'id_ruang'  => $c['id_ruang'],
                'id_sesi'   => $c['id_sesi'],
                'id_guru'   => $idGuru,
                'jam_ke'    => $jamKe,
            ];

            if ($rows) {
                // Baris pertama menjadi induk, duplikat lain dibersihkan
                $primary = array_shift($rows);
                $dataRow['id_pengawas'] = (int) $primary->id_pengawas;
                $for_update[] = $dataRow;
                foreach ($rows as $row) {
                    $to_delete[] = (int) $row->id_pengawas;
                }
            } else {
                $for_insert[] = $dataRow;
            }
        }

        // Assignment yatim: jadwal yang masih sama tetapi ruang/sesi tidak lagi dipakai
        foreach ($existing as $key => $rows) {
            if (isset($combos[$key])) {
                continue;
            }
            foreach ($rows as $row) {
                $to_delete[] = (int) $row->id_pengawas;
            }
        }

        if (!empty($for_insert)) {
            $db->table('cbt_pengawas')->insertBatch($for_insert);
        }
        if (!empty($for_update)) {
            $db->table('cbt_pengawas')->updateBatch($for_update, 'id_pengawas');
        }
        if (!empty($to_delete)) {
            $db->table('cbt_pengawas')->whereIn('id_pengawas', array_values(array_unique($to_delete)))->delete();
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal menyimpan alokasi pengawas',
                'insert'  => [],
                'update'  => [],
                'delete'  => 0,
            ], 500);
        }

        $deleted = count(array_unique($to_delete));
        $changed = count($for_insert) + count($for_update) + $deleted;

        if ($changed > 0) {
            $keterangan = 'Menyimpan alokasi pengawas ujian';
            if (!empty($for_insert)) {
                $keterangan = 'Menambah pengawas ujian';
            } elseif (!empty($for_update)) {
                $keterangan = 'Mengupdate pengawas ujian';
            }
            $this->logging->saveLog(count($for_insert) > 0 ? 2 : 3, $keterangan);
        }

        return $this->response->setJSON([
            'status'  => $changed > 0,
            'message' => $changed > 0 ? 'Alokasi pengawas disimpan' : 'Tidak ada perubahan data pengawas',
            'insert'  => $for_insert,
            'update'  => $for_update,
            'delete'  => $deleted,
        ]);
    }

    private function checkPanitiaAccess(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->isPanitia()) {
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $user = $this->getUser();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            
            if ($panitia) {
                $jabatan = strtolower($panitia->jabatan ?? '');
                // Allow 'panitia' jabatan or empty jabatan (default to panitia)
                if ($jabatan === 'panitia' || $jabatan === '') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Ubah map bersarang [jadwal][ruang][sesi] menjadi object agar
     * struktur JSON selalu berupa objek (bukan array) dan bisa diakses
     * langsung dari JS.
     */
    private function buildPengawasObject(array $map): \stdClass
    {
        $out = new \stdClass();

        foreach ($map as $jadwal => $byRuang) {
            $objRuang = new \stdClass();
            foreach ($byRuang as $ruang => $bySesi) {
                $objSesi = new \stdClass();
                foreach ($bySesi as $sesi => $val) {
                    $objSesi->{$sesi} = (object) $val;
                }
                $objRuang->{$ruang} = $objSesi;
            }
            $out->{$jadwal} = $objRuang;
        }

        return $out;
    }

    /**
     * Ambil id kelas dari berbagai format bank_kelas
     * (array assoc, object, atau nilai scalar).
     */
    private function extractKelasId($bk)
    {
        if (is_array($bk)) {
            return $bk['kelas_id'] ?? null;
        }
        if (is_object($bk)) {
            return $bk->kelas_id ?? null;
        }
        return $bk;
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
