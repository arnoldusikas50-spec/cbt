<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;
use CodeIgniter\Files\File;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Laporan Controller - Complete port from CI3
 * Handles document management with approval workflow
 */
class Laporan extends BaseController
{
    protected $cbt;
    protected $dashboard;
    protected $master;
    protected $db;
    protected $forge;

    public function __construct()
    {
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->master = new MasterModel();
        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();

        // Ensure table exists
        $this->_checkTable();
    }

    private function _checkTable()
    {
        if (!$this->db->tableExists('cbt_laporan')) {
            $this->forge->addField([
                'id_laporan' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_user' => ['type' => 'INT', 'constraint' => 11],
                'judul' => ['type' => 'VARCHAR', 'constraint' => 255],
                'keterangan' => ['type' => 'TEXT', 'null' => true],
                'file' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'type' => ['type' => 'VARCHAR', 'constraint' => 100],
                'created_at' => ['type' => 'DATETIME'],
                'jenis' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Lain-Lain'],
                'status' => ['type' => 'INT', 'constraint' => 1, 'default' => 0, 'comment' => '0=Checking, 1=Approved, 2=Correction'],
                'tgl_approval' => ['type' => 'DATETIME', 'null' => true],
                'approved_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'content' => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON data for specific forms'],
                'file_2' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'file_3' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'alasan_correction' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Reason for rejection/correction'],
            ]);
            $this->forge->addKey('id_laporan', true);
            $this->forge->createTable('cbt_laporan');
        }
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->showError('Akses Terlarang', 403);
        }

        $user = $this->getUser();
        $tp_active = $this->dashboard->getTahunActive();
        $smt_active = $this->dashboard->getSemesterActive();

        // Fetch Ruang
        $ruang_list = $this->db->table('cbt_ruang')->get()->getResult();

        // Fetch Sesi
        $sesi_list = $this->db->table('cbt_sesi')->get()->getResult();

        // Fetch Jadwal aktif dengan mapel
        $jadwal_list = $this->db->table('cbt_jadwal a')
            ->select('c.id_mapel, c.nama_mapel')
            ->join('cbt_bank_soal b', 'b.id_bank = a.id_bank', 'left')
            ->join('master_mapel c', 'c.id_mapel = b.bank_mapel_id', 'left')
            ->where('a.id_tp', $tp_active->id_tp)
            ->where('a.id_smt', $smt_active->id_smt)
            ->where('a.status', '1')
            ->where('c.id_mapel IS NOT NULL')
            ->groupBy('c.id_mapel, c.nama_mapel')
            ->orderBy('c.nama_mapel', 'ASC')
            ->get()->getResult();

        // Fallback: jika tidak ada jadwal aktif
        if (empty($jadwal_list)) {
            $jadwal_list = $this->db->table('master_mapel')
                ->select('id_mapel, nama_mapel')
                ->orderBy('nama_mapel', 'ASC')
                ->get()->getResult();
        }

        $data = [
            'user' => $user,
            'judul' => 'Laporan',
            'subjudul' => 'Arsip Laporan',
            'setting' => $this->dashboard->getSetting(),
            'tp_active' => $tp_active,
            'smt_active' => $smt_active,
            'ruang_list' => $ruang_list,
            'sesi_list' => $sesi_list,
            'jadwal_list' => $jadwal_list,
            'siswa_list' => $this->db->table('master_siswa')
                ->select('id_siswa, nama, username')
                ->orderBy('nama', 'ASC')
                ->get()->getResult()
        ];

        // Fetch Panitia data
        $panitia = null;
        $is_pengawas = false;
        if ($this->isPanitia()) {
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);
            $data['panitia'] = $panitia;
            
            if ($panitia) {
                $jabatan = strtolower($panitia->jabatan ?? '');
                $namaPanitia = strtolower($panitia->nama_panitia ?? '');
                
                // Get assignments to determine if really a supervisor
                $assignments = $this->db->table('cbt_pengawas')
                    ->where('id_tp', $tp_active->id_tp)
                    ->where('id_smt', $smt_active->id_smt)
                    ->where('id_guru', 'P' . $panitia->id_panitia)
                    ->get()->getResult();

                if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas' || !empty($assignments)) {
                    $is_pengawas = true;
                }
            }
        }
        $data['is_pengawas'] = $is_pengawas;
        $data['is_admin'] = $this->isAdmin();

        // Query laporan
        $builder = $this->db->table('cbt_laporan a')
            ->select('a.*, b.first_name, b.last_name, c.first_name as approver_first, c.last_name as approver_last')
            ->join('users b', 'a.id_user = b.id', 'left')
            ->join('users c', 'a.approved_by = c.id', 'left');

        if ($this->isAdmin()) {
            // Admin sees all
        } elseif ($is_pengawas) {
            // Supervisors see only their own
            $builder->where('a.id_user', $user->id);
        } elseif ($panitia) {
            // Core committee members see all to facilitate approval
        } else {
            // Fallback for others (shouldn't happen with current gate)
            $builder->where('a.id_user', $user->id);
        }

        $builder->orderBy('created_at', 'DESC');
        $laporans = $builder->get()->getResult();
        $data['laporans'] = $laporans;

        // Summary
        $summary = [
            'total' => count($laporans),
            'jenis' => []
        ];
        foreach ($laporans as $lap) {
            $jns = $lap->jenis ?? 'Lain-Lain';
            if (!isset($summary['jenis'][$jns])) {
                $summary['jenis'][$jns] = 0;
            }
            $summary['jenis'][$jns]++;
        }
        $data['summary'] = $summary;

        // Check if user has Pakta Integritas
        $has_pakta = $this->db->table('cbt_laporan')
            ->where('id_user', $user->id)
            ->where('jenis', 'Pakta Integritas')
            ->get()->getRow();
        $data['has_pakta_integritas'] = $has_pakta ? true : false;

        return $this->renderView('cbt/laporan/data', $data);
    }

    public function upload()
    {
        $validationRule = [
            'file_laporan' => [
                'rules' => 'uploaded[file_laporan]|max_size[file_laporan,5120]|ext_in[file_laporan,pdf]',
                'errors' => [
                    'uploaded' => 'File harus diupload',
                    'max_size' => 'Ukuran file maksimal 5MB',
                    'ext_in' => 'File harus berformat PDF'
                ]
            ]
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => $this->validator->getErrors()['file_laporan'] ?? 'Validation error'
            ]);
        }

        $file = $this->request->getFile('file_laporan');
        $uploadPath = WRITEPATH . '../public/uploads/laporan/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $data = [
            'id_user' => session()->get('user_id'),
            'jenis' => $this->request->getPost('jenis'),
            'judul' => $this->request->getPost('judul'),
            'keterangan' => $this->request->getPost('keterangan'),
            'file' => $newName,
            'type' => $file->getMimeType(),
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 0
        ];

        $this->db->table('cbt_laporan')->insert($data);

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Laporan berhasil diupload'
        ]);
    }

    /**
     * Daftar peserta pada ruang + sesi tertentu (untuk form Berita Acara).
     */
    public function get_siswa_ruang()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Sesi login berakhir.'], 401);
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Anda tidak memiliki akses.'], 403);
        }

        $idRuang = (int) $this->request->getGet('ruang');
        $idSesi  = (int) $this->request->getGet('sesi');

        if ($idRuang <= 0 || $idSesi <= 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Ruang dan sesi wajib dipilih.']);
        }

        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $rowsBuilder = $this->db->table('cbt_sesi_siswa s')
            ->select('s.siswa_id, s.kelas_id, m.nama, m.username, m.nis, m.nisn, k.nama_kelas')
            ->join('master_siswa m', 's.siswa_id = m.id_siswa', 'left')
            ->join('master_kelas k', 's.kelas_id = k.id_kelas', 'left')
            ->where('s.tp_id', $tp->id_tp)
            ->where('s.smt_id', $smt->id_smt)
            ->where('s.ruang_id', $idRuang)
            ->where('s.sesi_id', $idSesi)
            ->orderBy('k.nama_kelas', 'ASC')
            ->orderBy('m.nama', 'ASC');
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang absen.
        $this->onlySiswaAktif($rowsBuilder, 's', 'bi', 'siswa_id');
        $rows = $rowsBuilder->get()->getResult();

        $siswa  = [];
        $kelas  = [];
        foreach ($rows as $r) {
            $siswa[] = [
                'id'         => (int) $r->siswa_id,
                'nama'       => $r->nama,
                'no_peserta' => $r->username,
                'nis'        => $r->nis,
                'nisn'       => $r->nisn,
                'kelas'      => $r->nama_kelas,
            ];
            if (! empty($r->nama_kelas) && ! in_array($r->nama_kelas, $kelas, true)) {
                $kelas[] = $r->nama_kelas;
            }
        }

        return $this->response->setJSON([
            'status'          => true,
            'siswa'           => $siswa,
            'kelas'           => $kelas,
            'jml_seharusnya'  => count($siswa),
        ]);
    }

    /**
     * Daftar mata pelajaran yang memiliki jadwal ujian pada tanggal tertentu.
     */
    public function get_mapel_ujian()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Sesi login berakhir.'], 401);
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Anda tidak memiliki akses.'], 403);
        }

        $tanggal = trim((string) $this->request->getGet('tanggal'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Tanggal ujian belum diisi.']);
        }

        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $rows = $this->db->table('cbt_jadwal a')
            ->select('c.id_mapel, c.nama_mapel, c.kode, COUNT(DISTINCT a.id_jadwal) AS jml_jadwal')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt)
            ->where('DATE(a.tgl_mulai)', $tanggal)
            ->where('c.id_mapel IS NOT NULL', null, false)
            ->groupBy(['c.id_mapel', 'c.nama_mapel', 'c.kode'])
            ->orderBy('c.nama_mapel', 'ASC')
            ->get()->getResult();

        $mapel = [];
        foreach ($rows as $r) {
            $mapel[] = [
                'id_mapel'   => (int) $r->id_mapel,
                'nama_mapel' => $r->nama_mapel,
                'kode'       => $r->kode,
                'jml_jadwal' => (int) $r->jml_jadwal,
            ];
        }

        return $this->response->setJSON([
            'status' => true,
            'mapel'  => $mapel,
        ]);
    }

    /**
     * Daftar ruang + sesi yang memiliki jadwal ujian untuk mata pelajaran & tanggal tertentu.
     */
    public function get_ruang_sesi()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Sesi login berakhir.'], 401);
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Anda tidak memiliki akses.'], 403);
        }

        $idMapel = (int) $this->request->getGet('mapel');
        $tanggal = trim((string) $this->request->getGet('tanggal'));

        if ($idMapel <= 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Mata pelajaran belum dipilih.']);
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Tanggal ujian belum diisi.']);
        }

        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Kelas target dari bank soal jadwal pada tanggal tersebut
        $rows = $this->db->table('cbt_jadwal a')
            ->select('b.bank_kelas')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt)
            ->where('DATE(a.tgl_mulai)', $tanggal)
            ->get()->getResult();

        $kelasIds = [];
        foreach ($rows as $r) {
            $bank = json_decode((string) ($r->bank_kelas ?? '[]'), true);
            foreach ((is_array($bank) ? $bank : []) as $k) {
                $kid = is_array($k) ? ($k['kelas_id'] ?? null) : (is_object($k) ? ($k->kelas_id ?? null) : $k);
                if ($kid !== null && $kid !== '') {
                    $kelasIds[(string) $kid] = true;
                }
            }
        }

        // Filter kelas-kelas tersebut ke dalam mapel yang dipilih
        $kelasMapel = $this->db->table('cbt_bank_soal b')
            ->select('b.bank_kelas')
            ->where('b.bank_mapel_id', $idMapel)
            ->get()->getResult();

        $idsMapel = [];
        foreach ($kelasMapel as $r) {
            $bank = json_decode((string) ($r->bank_kelas ?? '[]'), true);
            foreach ((is_array($bank) ? $bank : []) as $k) {
                $kid = is_array($k) ? ($k['kelas_id'] ?? null) : (is_object($k) ? ($k->kelas_id ?? null) : $k);
                if ($kid !== null && $kid !== '' && isset($kelasIds[(string) $kid])) {
                    $idsMapel[(string) $kid] = true;
                }
            }
        }

        if (empty($idsMapel)) {
            return $this->response->setJSON([
                'status' => true,
                'ruang'  => [],
                'pesan'  => 'Tidak ada jadwal ujian untuk mata pelajaran dan tanggal tersebut.',
            ]);
        }

        // Ruang + sesi beserta jumlah pesertanya
        $alokasiBuilder = $this->db->table('cbt_sesi_siswa s')
            ->select('s.ruang_id, s.sesi_id, r.nama_ruang, r.kode_ruang, se.nama_sesi, se.kode_sesi, se.waktu_mulai, se.waktu_akhir, COUNT(DISTINCT s.siswa_id) AS jml_siswa')
            ->join('cbt_ruang r', 's.ruang_id = r.id_ruang', 'left')
            ->join('cbt_sesi se', 's.sesi_id = se.id_sesi', 'left')
            ->where('s.tp_id', $tp->id_tp)
            ->where('s.smt_id', $smt->id_smt)
            ->whereIn('s.kelas_id', array_keys($idsMapel))
            ->groupBy(['s.ruang_id', 's.sesi_id'])
            ->orderBy('r.nama_ruang', 'ASC')
            ->orderBy('se.nama_sesi', 'ASC');
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dihitung.
        $this->onlySiswaAktif($alokasiBuilder, 's', 'bi', 'siswa_id');
        $alokasi = $alokasiBuilder->get()->getResult();

        $ruang = [];
        foreach ($alokasi as $r) {
            $ruang[] = [
                'id_ruang'    => (int) $r->ruang_id,
                'nama_ruang'  => $r->nama_ruang,
                'kode_ruang'  => $r->kode_ruang,
                'id_sesi'     => (int) $r->sesi_id,
                'nama_sesi'   => $r->nama_sesi,
                'kode_sesi'   => $r->kode_sesi,
                'waktu_mulai' => $r->waktu_mulai ? substr((string) $r->waktu_mulai, 0, 5) : null,
                'waktu_akhir' => $r->waktu_akhir ? substr((string) $r->waktu_akhir, 0, 5) : null,
                'jml_peserta' => (int) $r->jml_siswa,
            ];
        }

        return $this->response->setJSON([
            'status' => true,
            'ruang'  => $ruang,
        ]);
    }

    /**
     * Susun konten Berita Acara dari input form.
     *
     * - Validasi sesi ujian (mata pelajaran + ruang + sesi harus punya jadwal)
     * - Menghitung jumlah peserta dari data alokasi ruang/sesi
     * - Mencatat alasan ketidakhadiran tiap siswa (Sakit / Ijin / Tanpa Berita)
     * - Mencegah pembuatan berita acara ganda untuk sesi yang sama
     *
     * @throws \RuntimeException
     */
    private function buildBeritaAcaraContent(array $post, ?int $idLaporan = null): array
    {
        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $idMapel = (int) ($post['mapel'] ?? 0);
        $idRuang = (int) ($post['ruang'] ?? 0);
        $idSesi  = (int) ($post['sesi'] ?? 0);
        $tanggal = trim((string) ($post['waktu_ujian'] ?? ''));

        if ($idMapel <= 0 || $idRuang <= 0 || $idSesi <= 0) {
            throw new \RuntimeException('Mata pelajaran, ruang, dan sesi wajib dipilih.');
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            throw new \RuntimeException('Tanggal ujian tidak valid.');
        }

        // Nama taken dari database (bukan dari input client)
        $mapel = $this->db->table('master_mapel')->select('id_mapel, nama_mapel')->where('id_mapel', $idMapel)->get()->getRow();
        $ruang = $this->db->table('cbt_ruang')->select('id_ruang, nama_ruang')->where('id_ruang', $idRuang)->get()->getRow();
        $sesi  = $this->db->table('cbt_sesi')->select('id_sesi, nama_sesi, waktu_mulai, waktu_akhir')->where('id_sesi', $idSesi)->get()->getRow();

        if (! $mapel) {
            throw new \RuntimeException('Mata pelajaran tidak ditemukan.');
        }
        if (! $ruang) {
            throw new \RuntimeException('Ruang tidak ditemukan.');
        }
        if (! $sesi) {
            throw new \RuntimeException('Sesi tidak ditemukan.');
        }

        // Jam pelaksanaan: dari input, jika kosong memakai waktu sesi
        $jamMulai   = trim((string) ($post['jam_mulai'] ?? ''));
        $jamSelesai = trim((string) ($post['jam_selesai'] ?? ''));
        if ($jamMulai === '' && $sesi->waktu_mulai) {
            $jamMulai = substr((string) $sesi->waktu_mulai, 0, 5);
        }
        if ($jamSelesai === '' && $sesi->waktu_akhir) {
            $jamSelesai = substr((string) $sesi->waktu_akhir, 0, 5);
        }
        // Validasi format & urutan jam
        foreach (['jam_mulai' => $jamMulai, 'jam_selesai' => $jamSelesai] as $label => $nilai) {
            if ($nilai !== '' && ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $nilai)) {
                throw new \RuntimeException('Format jam ' . str_replace('_', ' ', $label) . ' harus HH:MM (24 jam).');
            }
        }
        if ($jamMulai !== '' && $jamSelesai !== '' && $jamSelesai <= $jamMulai) {
            throw new \RuntimeException('Jam selesai harus lebih besar dari jam mulai.');
        }


        // Peserta yang terdaftar pada ruang + sesi ini
        $baBuilder = $this->db->table('cbt_sesi_siswa s')
            ->select('s.siswa_id, s.kelas_id, m.nama, m.username, m.nis, m.nisn, k.nama_kelas')
            ->join('master_siswa m', 's.siswa_id = m.id_siswa', 'left')
            ->join('master_kelas k', 's.kelas_id = k.id_kelas', 'left')
            ->where('s.tp_id', $tp->id_tp)
            ->where('s.smt_id', $smt->id_smt)
            ->where('s.ruang_id', $idRuang)
            ->where('s.sesi_id', $idSesi);
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dihitung
        // sebagai peserta seharusnya hadir.
        $this->onlySiswaAktif($baBuilder, 's', 'bi', 'siswa_id');
        $rows = $baBuilder->get()->getResult();

        $siswaRuang = [];
        $kelasList  = [];
        foreach ($rows as $r) {
            $siswaRuang[(int) $r->siswa_id] = [
                'id'         => (int) $r->siswa_id,
                'nama'       => $r->nama,
                'no_peserta' => $r->username,
                'nis'        => $r->nis,
                'nisn'       => $r->nisn,
                'kelas'      => $r->nama_kelas,
            ];
            if (! empty($r->nama_kelas) && ! in_array($r->nama_kelas, $kelasList, true)) {
                $kelasList[] = $r->nama_kelas;
            }
        }
        $jmlSeharusnya = count($siswaRuang);

        // Jadwal ujian untuk sesi ini (digunakan untuk data pengawas & jam ke)
        $jadwal = $this->cariJadwalSesi($idMapel, $idRuang, $idSesi, $tanggal);

        // Pengawas yang ditugaskan pada sesi ini
        $pengawas = [];
        if ($jadwal) {
            $pengawas = $this->getPengawasSesi((int) $jadwal->id_jadwal, $idRuang, $idSesi);
        }

        // ---------------------------------------------------------------
        // Siswa tidak hadir + alasan (Sakit / Ijin / Tanpa Berita)
        // ---------------------------------------------------------------
        $absenInput = $post['absen'] ?? [];
        $absenInput = is_array($absenInput) ? $absenInput : [];

        $alasanValid = ['Sakit', 'Ijin', 'Tanpa Berita', 'Lainnya'];

        $siswaTidakHadir = [];
        $alasanSummary   = ['Sakit' => 0, 'Ijin' => 0, 'Tanpa Berita' => 0, 'Lainnya' => 0];

        foreach ($absenInput as $idSiswa => $detail) {
            $idSiswa = (int) $idSiswa;
            if ($idSiswa <= 0) {
                continue;
            }
            if (! isset($siswaRuang[$idSiswa])) {
                // Siswa tidak terdaftar pada ruang/sesi ini
                $nama = $this->db->table('master_siswa')->select('nama')->where('id_siswa', $idSiswa)->get()->getRow();
                throw new \RuntimeException('Siswa "' . ($nama->nama ?? $idSiswa) . '" tidak terdaftar pada ruang/sesi yang dipilih.');
            }

            $detail  = (array) $detail;
            $alasan  = trim((string) ($detail['alasan'] ?? ''));
            $ket     = trim((string) ($detail['keterangan'] ?? ''));

            if ($alasan === '') {
                $alasan = 'Tanpa Berita';
            }
            if (! in_array($alasan, $alasanValid, true)) {
                throw new \RuntimeException('Alasan ketidakhadiran tidak valid untuk siswa ' . $siswaRuang[$idSiswa]['nama'] . '.');
            }
            if ($alasan === 'Lainnya' && $ket === '') {
                throw new \RuntimeException('Keterangan wajib diisi untuk siswa ' . $siswaRuang[$idSiswa]['nama'] . ' (alasan: Lainnya).');
            }

            $alasanSummary[$alasan]++;

            $siswaTidakHadir[] = [
                'id'         => $idSiswa,
                'nama'       => $siswaRuang[$idSiswa]['nama'],
                'no_peserta' => $siswaRuang[$idSiswa]['no_peserta'],
                'kelas'      => $siswaRuang[$idSiswa]['kelas'],
                'alasan'     => $alasan,
                'keterangan' => $ket,
            ];
        }

        // Cegah berita acara ganda untuk sesi yang sama
        // (data lama menyimpan angka sebagai string, data baru sebagai number)
        $kandidat = $this->db->table('cbt_laporan')
            ->select('id_laporan, content')
            ->where('jenis', 'Berita Acara')
            ->like('content', '"waktu_ujian":"' . $tanggal . '"')
            ->get()->getResult();

        foreach ($kandidat as $d) {
            $isi = json_decode((string) ($d->content ?? '{}'), true);
            if (! is_array($isi)) {
                continue;
            }
            if (
                (int) ($isi['mapel'] ?? 0) === $idMapel
                && (int) ($isi['ruang'] ?? 0) === $idRuang
                && (int) ($isi['sesi'] ?? 0) === $idSesi
                && (string) ($isi['waktu_ujian'] ?? '') === $tanggal
            ) {
                if ($idLaporan !== null && (int) $d->id_laporan === $idLaporan) {
                    continue;
                }
                throw new \RuntimeException('Berita acara untuk mata pelajaran, ruang, sesi, dan tanggal yang sama sudah ada. Silakan edit dokumen yang tersedia.');
            }
        }

        $jmlTidakHadir = count($siswaTidakHadir);

        if ($jmlTidakHadir > $jmlSeharusnya) {
            throw new \RuntimeException('Jumlah siswa tidak hadir (' . $jmlTidakHadir . ') melebihi jumlah peserta terdaftar (' . $jmlSeharusnya . ').');
        }

        // Jumlah peserta boleh disesuaikan petugas bila data alokasi tidak sama dengan kenyataan
        $jmlSeharusnyaSistem = $jmlSeharusnya;
        $jmlSeharusnyaInput  = $post['jml_seharusnya'] ?? null;
        if ($jmlSeharusnyaInput !== null && $jmlSeharusnyaInput !== '' && is_numeric($jmlSeharusnyaInput)) {
            $jmlSeharusnyaInput = (int) $jmlSeharusnyaInput;
            if ($jmlSeharusnyaInput >= $jmlTidakHadir) {
                $jmlSeharusnya = $jmlSeharusnyaInput;
            }
        }
        $selisihPeserta = $jmlSeharusnya - $jmlSeharusnyaSistem;

        $jmlHadir = max(0, $jmlSeharusnya - $jmlTidakHadir);

        return [
            'mapel'            => $idMapel,
            'mapel_nama'       => $mapel->nama_mapel,
            'ruang'            => $idRuang,
            'ruang_nama'       => $ruang->nama_ruang,
            'sesi'             => $idSesi,
            'sesi_nama'        => $sesi->nama_sesi,
            'waktu_ujian'      => $tanggal,
            'jam_mulai'        => $jamMulai,
            'jam_selesai'      => $jamSelesai,
            'jml_seharusnya' => $jmlSeharusnya,
            'jml_seharusnya_sistem' => $jmlSeharusnyaSistem,
            'selisih_peserta' => $selisihPeserta,
            'jml_hadir'        => $jmlHadir,
            'jml_tidak_hadir'  => $jmlTidakHadir,
            'siswa_tidak_hadir' => $siswaTidakHadir,
            'alasan_summary'   => $alasanSummary,
            'kelas_terlibat'   => $kelasList,
            'id_jadwal'        => $jadwal ? (int) $jadwal->id_jadwal : null,
            'jam_ke'           => $jadwal ? (int) $jadwal->jam_ke : null,
            'durasi_ujian'     => $jadwal ? (int) $jadwal->durasi_ujian : null,
            'bank_kode'        => $jadwal ? $jadwal->bank_kode : null,
            'pengawas'         => $pengawas,
            'uraian'           => trim((string) ($post['uraian'] ?? '')),
            'kendala'          => trim((string) ($post['kendala'] ?? '')),
            'solusi'           => trim((string) ($post['solusi'] ?? '')),
        ];
    }

    /**
     * Cari jadwal ujian yang sesuai dengan (mata pelajaran, ruang, sesi, tanggal).
     */
    private function cariJadwalSesi(int $idMapel, int $idRuang, int $idSesi, string $tanggal): ?object
    {
        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $kelasIds = $this->db->table('cbt_sesi_siswa')
            ->select('kelas_id')
            ->distinct()
            ->where('tp_id', $tp->id_tp)
            ->where('smt_id', $smt->id_smt)
            ->where('ruang_id', $idRuang)
            ->where('sesi_id', $idSesi)
            ->where('kelas_id IS NOT NULL', null, false)
            ->get()->getResultArray();

        if (empty($kelasIds)) {
            return null;
        }

        $idsKelas = array_map('strval', array_column($kelasIds, 'kelas_id'));

        $rows = $this->db->table('cbt_jadwal a')
            ->select('a.id_jadwal, a.jam_ke, a.tgl_mulai, a.tgl_selesai, a.durasi_ujian, b.bank_kelas, b.bank_kode')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt)
            ->where('DATE(a.tgl_mulai)', $tanggal)
            ->get()->getResult();

        foreach ($rows as $r) {
            $bank = json_decode((string) ($r->bank_kelas ?? '[]'), true);
            $bankIds = [];
            foreach ((is_array($bank) ? $bank : []) as $k) {
                $kid = is_array($k) ? ($k['kelas_id'] ?? null) : (is_object($k) ? ($k->kelas_id ?? null) : $k);
                if ($kid !== null && $kid !== '') {
                    $bankIds[] = (string) $kid;
                }
            }
            if (! empty(array_intersect($bankIds, $idsKelas))) {
                return (object) [
                    'id_jadwal'     => (int) $r->id_jadwal,
                    'jam_ke'        => (int) $r->jam_ke,
                    'bank_kode'     => $r->bank_kode,
                    'durasi_ujian'  => (int) ($r->durasi_ujian ?? 0),
                ];
            }
        }

        return null;
    }

    /**
     * Daftar pengawas yang ditugaskan pada satu sesi ujian.
     */
    private function getPengawasSesi(int $idJadwal, int $idRuang, int $idSesi): array
    {
        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $rows = $this->db->table('cbt_pengawas a')
            ->select('a.id_guru')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt)
            ->where('a.id_jadwal', $idJadwal)
            ->where('a.id_ruang', $idRuang)
            ->where('a.id_sesi', $idSesi)
            ->get()->getResult();

        $ids = [];
        foreach ($rows as $r) {
            foreach (explode(',', (string) ($r->id_guru ?? '')) as $id) {
                $id = trim($id);
                if ($id !== '' && $id !== '0') {
                    $ids[$id] = true;
                }
            }
        }
        if (empty($ids)) {
            return [];
        }

        $gurus    = $this->master->getDropdownGuru();
        $panitias = $this->master->getDropdownPanitia();

        $pengawas = [];
        foreach (array_keys($ids) as $id) {
            $pengawas[] = [
                'id_guru'   => $id,
                'nama_guru' => $gurus[$id] ?? ($panitias[$id] ?? 'Tidak Diketahui'),
            ];
        }

        return $pengawas;
    }

    public function save_berita_acara()
    {
        $uploadPath = WRITEPATH . '../public/uploads/laporan/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $files = [];
        $errors = [];

        // Upload 3 photos
        for ($i = 1; $i <= 3; $i++) {
            $file = $this->request->getFile('foto_' . $i);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $validationRule = [
                    'foto_' . $i => 'uploaded[foto_' . $i . ']|max_size[foto_' . $i . ',5120]|is_image[foto_' . $i . ']'
                ];

                if ($this->validate($validationRule)) {
                    $newName = $file->getRandomName();
                    $file->move($uploadPath, $newName);
                    
                    // Compress image
                    try {
                        \Config\Services::image()
                            ->withFile($uploadPath . $newName)
                            ->resize(1280, 1280, true, 'auto')
                            ->save($uploadPath . $newName, 60);
                    } catch (\Exception $e) {
                        log_message('error', 'Image compression failed: ' . $e->getMessage());
                    }

                    $files['file' . ($i == 1 ? '' : '_' . $i)] = $newName;
                } else {
                    $errors[] = "Foto $i: " . ($this->validator->getError('foto_' . $i) ?? 'Error');
                }
            }
        }

        if (empty($files) && !empty($errors)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => implode(', ', $errors)
            ]);
        }

        try {
            $content = $this->buildBeritaAcaraContent($this->request->getPost());
        } catch (\RuntimeException $e) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => $e->getMessage()
            ], 422);
        }

        $data = [
            'id_user' => session()->get('user_id'),
            'jenis' => 'Berita Acara',
            'judul' => 'Berita Acara - ' . $content['mapel_nama'],
            'keterangan' => $content['uraian'] . ' - ' . $content['kendala'] . ' - ' . $content['solusi'],
            'content' => json_encode($content),
            'file' => $files['file'] ?? '',
            'file_2' => $files['file_2'] ?? '',
            'file_3' => $files['file_3'] ?? '',
            'type' => 'form',
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 0
        ];

        if (! $this->db->table('cbt_laporan')->insert($data)) {
            $err = $this->db->error();

            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Gagal menyimpan berita acara: ' . ($err['message'] ?? 'terjadi kesalahan pada database.')
            ], 500);
        }

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Berita Acara berhasil disimpan'
        ]);
    }

    public function save_pakta_integritas()
    {
        $user_id = session()->get('user_id');

        // Check if user already has a Pakta Integritas
        $existing = $this->db->table('cbt_laporan')
            ->where('id_user', $user_id)
            ->where('jenis', 'Pakta Integritas')
            ->get()->getRow();

        if ($existing) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Anda sudah memiliki Pakta Integritas. Setiap user hanya dapat membuat 1 Pakta Integritas.',
                'existing_id' => $existing->id_laporan
            ]);
        }

        // Prepare JSON content
        $content = [
            'nama' => $this->request->getPost('nama'),
            'nip' => $this->request->getPost('nip'),
            'jabatan' => $this->request->getPost('jabatan'),
            'instansi' => $this->request->getPost('instansi'),
            'no_hp' => $this->request->getPost('no_hp'),
            'email' => $this->request->getPost('email'),
            'provinsi' => $this->request->getPost('provinsi'),
            'kota' => $this->request->getPost('kota'),
            'tempat' => $this->request->getPost('tempat'),
            'tanggal_pelaksanaan' => $this->request->getPost('tanggal_pelaksanaan'),
            'tanggal_ttd' => date('d F Y'),
        ];

        $data = [
            'id_user' => $user_id,
            'jenis' => 'Pakta Integritas',
            'judul' => 'Pakta Integritas - ' . $this->request->getPost('nama') . ' - ' . date('d/m/Y'),
            'keterangan' => 'Pakta Integritas Pelaksanaan Ujian',
            'content' => json_encode($content),
            'file' => '',
            'type' => 'form',
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 0
        ];

        if (! $this->db->table('cbt_laporan')->insert($data)) {
            $err = $this->db->error();

            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Gagal menyimpan Pakta Integritas: ' . ($err['message'] ?? 'terjadi kesalahan pada database.')
            ], 500);
        }

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Pakta Integritas berhasil disimpan'
        ]);
    }

    public function update_berita_acara()
    {
        $id = $this->request->getPost('id_laporan');

        if (!$id) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'ID Laporan tidak valid'
            ]);
        }

        // Check permission
        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();
        if (!$laporan) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan'
            ]);
        }

        $user = $this->getUser();
        $can_edit = false;

        if ($this->isAdmin()) {
            $can_edit = true;
        } else {
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && strtolower($panitia->jabatan) !== 'pengawas' && $laporan->status != 1) {
                $can_edit = true;
            } elseif ($laporan->id_user == $user->id && $laporan->status != 1) {
                $can_edit = true;
            }
        }

        if (!$can_edit) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk mengedit dokumen ini'
            ]);
        }

        $uploadPath = WRITEPATH . '../public/uploads/laporan/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $files = [];
        // Handle optional file uploads (only if new files provided)
        for ($i = 1; $i <= 3; $i++) {
            $file = $this->request->getFile('foto_' . $i);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);
                
                // Compress image
                try {
                    \Config\Services::image()
                        ->withFile($uploadPath . $newName)
                        ->resize(1280, 1280, true, 'auto')
                        ->save($uploadPath . $newName, 60);
                } catch (\Exception $e) {
                    log_message('error', 'Image compression failed: ' . $e->getMessage());
                }

                $files['file' . ($i == 1 ? '' : '_' . $i)] = $newName;

                // Delete old file
                $oldFile = $i == 1 ? $laporan->file : ($i == 2 ? $laporan->file_2 : $laporan->file_3);
                if ($oldFile && file_exists($uploadPath . $oldFile)) {
                    unlink($uploadPath . $oldFile);
                }
            }
        }

        try {
            $content = $this->buildBeritaAcaraContent($this->request->getPost(), (int) $id);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => $e->getMessage()
            ], 422);
        }

        $data = [
            'judul' => 'Berita Acara - ' . $content['mapel_nama'],
            'keterangan' => $content['uraian'] . ' - ' . $content['kendala'] . ' - ' . $content['solusi'],
            'content' => json_encode($content),
            'status' => 0 // Reset to checking when edited
        ];

        // Only update file fields if new files were uploaded
        if (!empty($files['file']))
            $data['file'] = $files['file'];
        if (!empty($files['file_2']))
            $data['file_2'] = $files['file_2'];
        if (!empty($files['file_3']))
            $data['file_3'] = $files['file_3'];

        $this->db->table('cbt_laporan')->where('id_laporan', $id)->update($data);

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Berita Acara berhasil diupdate'
        ]);
    }

    public function update_pakta_integritas()
    {
        $id = $this->request->getPost('id_laporan');

        if (!$id) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'ID Laporan tidak valid'
            ]);
        }

        // Check permission
        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();
        if (!$laporan) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan'
            ]);
        }

        $user = $this->getUser();
        $can_edit = false;

        if ($this->isAdmin()) {
            $can_edit = true;
        } else {
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && strtolower($panitia->jabatan) !== 'pengawas' && $laporan->status != 1) {
                $can_edit = true;
            } elseif ($laporan->id_user == $user->id && $laporan->status != 1) {
                $can_edit = true;
            }
        }

        if (!$can_edit) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk mengedit dokumen ini'
            ]);
        }

        // Prepare JSON content
        $content = [
            'nama' => $this->request->getPost('nama'),
            'nip' => $this->request->getPost('nip'),
            'jabatan' => $this->request->getPost('jabatan'),
            'instansi' => $this->request->getPost('instansi'),
            'no_hp' => $this->request->getPost('no_hp'),
            'email' => $this->request->getPost('email'),
            'provinsi' => $this->request->getPost('provinsi'),
            'kota' => $this->request->getPost('kota'),
            'tempat' => $this->request->getPost('tempat'),
            'tanggal_pelaksanaan' => $this->request->getPost('tanggal_pelaksanaan'),
            'tanggal_ttd' => date('d F Y'),
        ];

        $data = [
            'judul' => 'Pakta Integritas - ' . $this->request->getPost('nama') . ' - ' . date('d/m/Y'),
            'keterangan' => 'Pakta Integritas Pelaksanaan Ujian',
            'content' => json_encode($content),
            'status' => 0 // Reset to checking when edited
        ];

        $this->db->table('cbt_laporan')->where('id_laporan', $id)->update($data);

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Pakta Integritas berhasil diupdate'
        ]);
    }

    public function delete()
    {
        $id = $this->request->getPost('id_laporan');

        if (empty($id)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'ID Laporan tidak valid'
            ]);
        }

        $user = $this->getUser();
        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();

        if (!$laporan) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan'
            ]);
        }

        // Check Permissions
        $can_delete = false;
        if ($this->isAdmin()) {
            $can_delete = true;
        } else {
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && strtolower($panitia->jabatan) !== 'pengawas') {
                $can_delete = true;
            } elseif ($laporan->id_user == $user->id) {
                if ($laporan->status != 1) {
                    $can_delete = true;
                } else {
                    return $this->response->setJSON([
                        'status' => false,
                        'msg' => 'Dokumen sudah disetujui, tidak dapat dihapus.'
                    ]);
                }
            }
        }

        if ($can_delete) {
            // Delete files
            $uploadPath = WRITEPATH . '../public/uploads/laporan/';
            if (!empty($laporan->file)) {
                $path = $uploadPath . $laporan->file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            if (!empty($laporan->file_2)) {
                $path = $uploadPath . $laporan->file_2;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            if (!empty($laporan->file_3)) {
                $path = $uploadPath . $laporan->file_3;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $this->db->table('cbt_laporan')->where('id_laporan', $id)->delete();

            return $this->response->setJSON([
                'status' => true,
                'msg' => 'Laporan berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk menghapus dokumen ini'
            ]);
        }
    }

    public function approve()
    {
        if (!$this->check_approver_access()) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk menyetujui dokumen'
            ]);
        }

        $id = $this->request->getPost('id_laporan');

        if (empty($id)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'ID Laporan tidak valid'
            ]);
        }

        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();
        if (!$laporan) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan'
            ]);
        }

        if ($laporan->status == 1) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Dokumen sudah disetujui sebelumnya'
            ]);
        }

        $data = [
            'status' => 1,
            'approved_by' => session()->get('user_id'),
            'tgl_approval' => date('Y-m-d H:i:s'),
            'alasan_correction' => null
        ];

        $this->db->table('cbt_laporan')->where('id_laporan', $id)->update($data);

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Dokumen berhasil disetujui'
        ]);
    }

    public function reject()
    {
        if (!$this->check_approver_access()) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk menolak dokumen'
            ]);
        }
        $id = $this->request->getPost('id_laporan');
        $alasan = $this->request->getPost('alasan');

        if (empty($id)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'ID Laporan tidak valid'
            ]);
        }

        if (empty($alasan)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Alasan perbaikan harus diisi'
            ]);
        }

        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();
        if (!$laporan) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan'
            ]);
        }

        $data = [
            'status' => 2,
            'approved_by' => session()->get('user_id'),
            'tgl_approval' => date('Y-m-d H:i:s'),
            'alasan_correction' => $alasan
        ];

        $this->db->table('cbt_laporan')->where('id_laporan', $id)->update($data);

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Dokumen dikembalikan untuk perbaikan'
        ]);
    }

    private function check_approver_access()
    {
        if ($this->isAdmin()) {
            return true;
        }

        $user = $this->getUser();
        $tp_active = $this->dashboard->getTahunActive();
        $smt_active = $this->dashboard->getSemesterActive();
        $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

        if ($panitia && strtolower($panitia->jabatan) !== 'pengawas') {
            return true;
        }

        return false;
    }

    public function get_laporan($id)
    {
        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();
        if (!$laporan) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan'
            ]);
        }

        // Check permission
        $user = $this->getUser();
        $can_edit = false;

        if ($this->isAdmin()) {
            $can_edit = true;
        } else {
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && strtolower($panitia->jabatan) !== 'pengawas') {
                if ($laporan->status != 1) {
                    $can_edit = true;
                }
            } elseif ($laporan->id_user == $user->id && $laporan->status != 1) {
                $can_edit = true;
            }
        }

        if (!$can_edit) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk mengedit dokumen ini'
            ]);
        }

        $content = null;
        if ($laporan->content) {
            $content = json_decode($laporan->content);
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => $laporan,
            'content' => $content
        ]);
    }

    public function cetak_berita_acara($id)
    {
        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();
        if (!$laporan) {
            return $this->showError('Laporan tidak ditemukan', 404);
        }

        $setting = $this->cbt->getSettingKopBeritaAcara();

        $jenis_ujian = $this->db->table('cbt_jenis')
            ->select('nama_jenis, kode_jenis')
            ->where('status', '1')
            ->get()->getRow();

        $uploader = null;
        if ($laporan->id_user) {
            $uploader = $this->db->table('master_panitia p')
                ->select('p.*, u.first_name, u.last_name')
                ->join('users u', 'u.id = p.id_user', 'left')
                ->where('p.id_user', $laporan->id_user)
                ->get()->getRow();

            if (!$uploader) {
                $uploader = $this->db->table('master_guru g')
                    ->select('g.*, u.first_name, u.last_name')
                    ->join('users u', 'u.id = g.id_user', 'left')
                    ->where('g.id_user', $laporan->id_user)
                    ->get()->getRow();
            }
        }

        $content = $laporan->content ? json_decode($laporan->content) : (object) [];

        // Lengkapi kelas siswa yang tidak hadir (data lama belum menyimpan kelas)
        if (! empty($content->siswa_tidak_hadir) && is_array($content->siswa_tidak_hadir)) {
            $tp  = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            foreach ($content->siswa_tidak_hadir as $s) {
                if (! empty($s->kelas)) {
                    continue;
                }
                $sid = $s->id ?? null;
                if (! $sid) {
                    continue;
                }
                $row = $this->db->table('kelas_siswa ks')
                    ->select('mk.nama_kelas')
                    ->join('master_kelas mk', 'ks.id_kelas = mk.id_kelas')
                    ->where('ks.id_siswa', $sid)
                    ->where('ks.id_tp', $tp->id_tp)
                    ->where('ks.id_smt', $smt->id_smt)
                    ->get()->getRow();
                $s->kelas = $row ? $row->nama_kelas : '-';
            }
        }

        $data = [
            'laporan' => $laporan,
            'content' => $content,
            'setting' => $setting,
            'uploader' => $uploader,
            'kop' => $setting,
            'jenis_ujian' => $jenis_ujian
        ];

        // Check if PDF is requested
        if ($this->request->getGet('type') === 'pdf') {
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);
            $data['is_pdf'] = true;
            $html = view('cbt/laporan/cetak_berita_acara', $data);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Berita_Acara_' . preg_replace('/[^a-zA-Z0-9]/', '_', $laporan->judul) . '.pdf';

            $dompdf->stream($filename, ['Attachment' => 0]);
            exit();
        }

        return view('cbt/laporan/cetak_berita_acara', $data);
    }

    public function cetak_pakta_integritas($id)
    {
        $laporan = $this->db->table('cbt_laporan')->where('id_laporan', $id)->get()->getRow();
        if (!$laporan) {
            return $this->showError('Laporan tidak ditemukan', 404);
        }

        $setting = $this->cbt->getSettingKopBeritaAcara();
        $tp_active = $this->dashboard->getTahunActive();

        $jenis_ujian = $this->db->table('cbt_jenis')
            ->select('nama_jenis, kode_jenis')
            ->where('status', '1')
            ->get()->getRow();

        $uploader = null;
        if ($laporan->id_user) {
            $uploader = $this->db->table('master_panitia p')
                ->select('p.*, u.first_name, u.last_name, u.email')
                ->join('users u', 'u.id = p.id_user', 'left')
                ->where('p.id_user', $laporan->id_user)
                ->get()->getRow();

            if (!$uploader) {
                $uploader = $this->db->table('master_guru g')
                    ->select('g.*, u.first_name, u.last_name, u.email')
                    ->join('users u', 'u.id = g.id_user', 'left')
                    ->where('g.id_user', $laporan->id_user)
                    ->get()->getRow();
            }
        }

        $data = [
            'laporan' => $laporan,
            'content' => $laporan->content ? json_decode($laporan->content) : (object) [],
            'setting' => $setting,
            'uploader' => $uploader,
            'tp_active' => $tp_active,
            'jenis_ujian' => $jenis_ujian
        ];

        // Check if PDF is requested
        if ($this->request->getGet('type') === 'pdf') {
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);
            $data['is_pdf'] = true;
            $html = view('cbt/laporan/cetak_pakta_integritas', $data);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Pakta_Integritas_' . preg_replace('/[^a-zA-Z0-9]/', '_', $laporan->judul) . '.pdf';

            $dompdf->stream($filename, ['Attachment' => 0]);
            exit();
        }

        return view('cbt/laporan/cetak_pakta_integritas', $data);
    }
    public function harian()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->showError('Akses Terlarang', 403);
        }

        $user = $this->getUser();
        $tp_active = $this->dashboard->getTahunActive();
        $smt_active = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Laporan Harian',
            'subjudul' => 'Laporan Pelaksanaan Ujian Harian',
            'setting' => $this->dashboard->getSetting(),
            'tp_active' => $tp_active,
            'smt_active' => $smt_active,
            'is_admin' => $this->isAdmin()
        ];

        return $this->renderView('cbt/laporan/harian', $data);
    }

    /**
     * API data untuk halaman Laporan Harian.
     */
    public function get_harian_data()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Sesi login berakhir, silakan login kembali.'], 401);
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Anda tidak memiliki akses ke laporan ini.'], 403);
        }

        $tanggal = trim((string) $this->request->getGet('tanggal'));
        if (!$tanggal || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Tanggal tidak valid.']);
        }

        try {
            $laporan = $this->buildLaporanHarian($tanggal);

            return $this->response->setJSON([
                'status' => true,
                'data'   => $laporan,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'get_harian_data: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Gagal menyusun laporan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Susun data laporan pelaksanaan ujian harian.
     *
     * Satu sumber data untuk tampilan web maupun cetak PDF, dengan satuan
     * laporan = satu sesi ujian (mata pelajaran + ruang + sesi).
     *
     * @return array<string, mixed>
     */
    public function buildLaporanHarian(string $tanggal): array
    {
        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // ---------- 1. Jadwal ujian pada tanggal terpilih ----------
        $jadwal_list = $this->db->table('cbt_jadwal a')
            ->select('a.id_jadwal, a.id_jenis, a.id_bank, a.jam_ke, a.tgl_mulai, a.tgl_selesai, a.durasi_ujian, b.bank_kode, b.bank_nama, b.bank_kelas, c.nama_mapel, c.id_mapel')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left')
            ->where('a.id_tp', $tp->id_tp)
            ->where('a.id_smt', $smt->id_smt)
            ->where('DATE(a.tgl_mulai)', $tanggal)
            ->orderBy('a.jam_ke', 'ASC')
            ->orderBy('a.id_jadwal', 'ASC')
            ->get()->getResult();

        // Kelas target tiap jadwal
        $kelasCache  = [];
        $allKelasIds = [];
        foreach ($jadwal_list as &$j) {
            $j->kelas_ids   = [];
            $j->kelas_names = [];
            $decoded        = is_string($j->bank_kelas ?? '') ? json_decode($j->bank_kelas, true) : $j->bank_kelas;
            foreach ((is_array($decoded) ? $decoded : []) as $k) {
                $kid = is_array($k) ? ($k['kelas_id'] ?? null) : (is_object($k) ? ($k->kelas_id ?? null) : $k);
                if ($kid === null || $kid === '') {
                    continue;
                }
                $kid = (string) $kid;
                $j->kelas_ids[]   = $kid;
                $allKelasIds[$kid] = true;
                if (! isset($kelasCache[$kid])) {
                    $row = $this->db->table('master_kelas')->select('nama_kelas')->where('id_kelas', $kid)->get()->getRow();
                    $kelasCache[$kid] = $row ? $row->nama_kelas : $kid;
                }
                $j->kelas_names[] = $kelasCache[$kid];
            }
        }
        unset($j);

        // ---------- 2. Alokasi ruang & sesi untuk kelas target ----------
        $alokasi = [];
        if (! empty($allKelasIds)) {
            $alokasiBuilder = $this->db->table('cbt_sesi_siswa s')
                ->select('s.kelas_id, s.ruang_id, s.sesi_id, r.nama_ruang, r.kode_ruang, se.nama_sesi, se.kode_sesi, se.waktu_mulai, se.waktu_akhir, COUNT(s.siswa_id) AS jml_siswa')
                ->join('cbt_ruang r', 's.ruang_id = r.id_ruang', 'left')
                ->join('cbt_sesi se', 's.sesi_id = se.id_sesi', 'left')
                ->where('s.tp_id', $tp->id_tp)
                ->where('s.smt_id', $smt->id_smt)
                ->whereIn('s.kelas_id', array_keys($allKelasIds))
                ->groupBy(['s.kelas_id', 's.ruang_id', 's.sesi_id']);
            // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dihitung.
            $this->onlySiswaAktif($alokasiBuilder, 's', 'bi', 'siswa_id');
            $rows = $alokasiBuilder->get()->getResult();

            foreach ($rows as $r) {
                $alokasi[(string) $r->kelas_id][(int) $r->ruang_id . '-' . (int) $r->sesi_id] = $r;
            }
        }

        // ---------- 3. Sesi ujian (mata pelajaran + ruang + sesi) ----------
        $sesiUjian = [];
        foreach ($jadwal_list as $j) {
            if (empty($j->id_mapel)) {
                continue;
            }
            foreach ($j->kelas_ids as $kid) {
                foreach (($alokasi[$kid] ?? []) as $a) {
                    $key = (int) $j->id_mapel . '-' . (int) $a->ruang_id . '-' . (int) $a->sesi_id;

                    if (! isset($sesiUjian[$key])) {
                        $sesiUjian[$key] = [
                            'id_mapel'    => (int) $j->id_mapel,
                            'nama_mapel'  => $j->nama_mapel ?? '-',
                            'id_ruang'    => (int) $a->ruang_id,
                            'nama_ruang'  => $a->nama_ruang ?? '-',
                            'id_sesi'     => (int) $a->sesi_id,
                            'nama_sesi'   => $a->nama_sesi ?? '-',
                            'jam_ke'      => (int) $j->jam_ke,
                            'waktu_mulai' => $a->waktu_mulai ?? null,
                            'waktu_akhir' => $a->waktu_akhir ?? null,
                            'kelas'       => [],
                            'jml_siswa'   => 0,
                            'id_jadwals'  => [],
                        ];
                    }

                    $sesiUjian[$key]['kelas'][(string) $kid] = $kelasCache[$kid] ?? $kid;
                    $sesiUjian[$key]['jml_siswa'] += (int) $a->jml_siswa;
                    if (! in_array((int) $j->id_jadwal, $sesiUjian[$key]['id_jadwals'], true)) {
                        $sesiUjian[$key]['id_jadwals'][] = (int) $j->id_jadwal;
                    }
                }
            }
        }

        // ---------- 4. Berita acara pada tanggal terpilih ----------
        $laporans = $this->db->table('cbt_laporan')
            ->where('jenis', 'Berita Acara')
            ->like('content', '"waktu_ujian":"' . $tanggal . '"')
            ->orderBy('id_laporan', 'ASC')
            ->get()->getResult();

        $baIndex = [];
        foreach ($laporans as $lap) {
            $content = json_decode((string) ($lap->content ?? '{}'), true);
            if (! is_array($content) || ($content['waktu_ujian'] ?? '') !== $tanggal) {
                continue;
            }

            $content['id_laporan'] = (int) $lap->id_laporan;
            $content['id_user']    = $lap->id_user;
            $content['file']      = $lap->file ?? null;
            $content['file_2']    = $lap->file_2 ?? null;
            $content['file_3']    = $lap->file_3 ?? null;

            $uploader = $this->db->table('users')->select('first_name, last_name')->where('id', $lap->id_user)->get()->getRow();
            $content['uploader'] = $uploader ? trim(($uploader->first_name ?? '') . ' ' . ($uploader->last_name ?? '')) : 'Tidak diketahui';

            $key = (int) ($content['mapel'] ?? 0) . '-' . (int) ($content['ruang'] ?? 0) . '-' . (int) ($content['sesi'] ?? 0);
            $baIndex[$key][] = $content;
        }

        // ---------- 5. Rekap kehadiran per sesi ujian ----------
        $rekap   = [];
        $ordinal = 0;
        foreach ($sesiUjian as $key => $s) {
            $listBA = $baIndex[$key] ?? [];
            $ba     = $listBA ? $listBA[count($listBA) - 1] : null;

            $tidakHadir = null;
            $seharusnya = null;
            $hadir      = null;

            if ($ba) {
                $absen      = is_array($ba['siswa_tidak_hadir'] ?? null) ? $ba['siswa_tidak_hadir'] : [];
                $tidakHadir = is_numeric($ba['jml_tidak_hadir'] ?? null) ? (int) $ba['jml_tidak_hadir'] : count($absen);
                $seharusnya = is_numeric($ba['jml_seharusnya'] ?? null) ? (int) $ba['jml_seharusnya'] : $s['jml_siswa'];
                $hadir      = is_numeric($ba['jml_hadir'] ?? null) ? (int) $ba['jml_hadir'] : null;

                // Hadir dihitung dari jumlah peserta dikurangi yang tidak hadir
                if ($hadir === null && $seharusnya !== null) {
                    $hadir = max(0, $seharusnya - (int) $tidakHadir);
                }
            }

            $selisih = null;
            if ($ba && $hadir !== null && $tidakHadir !== null && $seharusnya !== null) {
                $selisih = ($hadir + $tidakHadir) - $seharusnya;
            }

            // Rincian alasan ketidakhadiran pada sesi ini
            $alasanSesi = ['Sakit' => 0, 'Ijin' => 0, 'Tanpa Berita' => 0, 'Lainnya' => 0];
            if ($ba && ! empty($ba['siswa_tidak_hadir']) && is_array($ba['siswa_tidak_hadir'])) {
                foreach ($ba['siswa_tidak_hadir'] as $absen) {
                    $alasan = trim((string) ($absen['alasan'] ?? ''));
                    if ($alasan === '') {
                        $alasan = 'Tanpa Berita';
                    }
                    if (! isset($alasanSesi[$alasan])) {
                        $alasanSesi['Lainnya']++;
                        continue;
                    }
                    $alasanSesi[$alasan]++;
                }
            }

            $jmlDaftarAbsen = $ba && ! empty($ba['siswa_tidak_hadir']) && is_array($ba['siswa_tidak_hadir'])
                ? count($ba['siswa_tidak_hadir'])
                : 0;

            $rekap[] = [
                'no'               => ++$ordinal,
                'jml_daftar_absen' => $jmlDaftarAbsen,
                'id_mapel'         => $s['id_mapel'],
                'nama_mapel'       => $s['nama_mapel'],
                'id_ruang'         => $s['id_ruang'],
                'nama_ruang'       => $s['nama_ruang'],
                'id_sesi'          => $s['id_sesi'],
                'nama_sesi'        => $s['nama_sesi'],
                'jam_ke'           => $s['jam_ke'],
                'kelas'            => array_values($s['kelas']),
                'kelas_display'    => implode(', ', $s['kelas']),
                'jml_sistem'       => $s['jml_siswa'],
                'ada_berita_acara' => $ba !== null,
                'jml_seharusnya'   => $seharusnya,
                'jml_hadir'        => $hadir,
                'jml_tidak_hadir'  => $tidakHadir,
                'alasan_summary'   => $alasanSesi,
                'selisih'          => $selisih,
                'jam_mulai'        => $ba['jam_mulai'] ?? null,
                'jam_selesai'      => $ba['jam_selesai'] ?? null,
                'waktu_mulai'      => $s['waktu_mulai'],
                'waktu_akhir'      => $s['waktu_akhir'],
                'uraian'           => $ba['uraian'] ?? null,
                'kendala'          => $ba['kendala'] ?? null,
                'solusi'           => $ba['solusi'] ?? null,
                'id_laporan'       => $ba['id_laporan'] ?? null,
                'uploader'         => $ba['uploader'] ?? null,
                'jml_berita_acara' => count($listBA),
                'file'             => $ba['file'] ?? null,
                'file_2'           => $ba['file_2'] ?? null,
                'file_3'           => $ba['file_3'] ?? null,
            ];
        }

        // Berita acara yang tidak punya sesi ujian (mis. jadwal sudah diubah)
        $alasanSummaryLuar = ['Sakit' => 0, 'Ijin' => 0, 'Tanpa Berita' => 0, 'Lainnya' => 0];
        foreach ($baIndex as $key => $listBA) {
            if (isset($sesiUjian[$key])) {
                continue;
            }
            $ba = $listBA[count($listBA) - 1];
            if (! empty($ba['siswa_tidak_hadir']) && is_array($ba['siswa_tidak_hadir'])) {
                foreach ($ba['siswa_tidak_hadir'] as $absen) {
                    $alasan = trim((string) ($absen['alasan'] ?? ''));
                    if ($alasan === '') {
                        $alasan = 'Tanpa Berita';
                    }
                    if (! isset($alasanSummaryLuar[$alasan])) {
                        $alasanSummaryLuar['Lainnya']++;
                        continue;
                    }
                    $alasanSummaryLuar[$alasan]++;
                }
            }
            $rekap[] = [
                'no'               => ++$ordinal,
                'id_mapel'         => (int) ($ba['mapel'] ?? 0),
                'nama_mapel'       => $ba['mapel_nama'] ?? '-',
                'id_ruang'         => (int) ($ba['ruang'] ?? 0),
                'nama_ruang'       => $ba['ruang_nama'] ?? '-',
                'id_sesi'          => (int) ($ba['sesi'] ?? 0),
                'nama_sesi'        => $ba['sesi_nama'] ?? '-',
                'jam_ke'           => 0,
                'kelas'            => [],
                'kelas_display'    => '-',
                'jml_sistem'       => 0,
                'ada_berita_acara' => true,
                'jml_seharusnya'   => is_numeric($ba['jml_seharusnya'] ?? null) ? (int) $ba['jml_seharusnya'] : null,
                'jml_hadir'        => is_numeric($ba['jml_hadir'] ?? null) ? (int) $ba['jml_hadir'] : null,
                'jml_tidak_hadir'  => is_numeric($ba['jml_tidak_hadir'] ?? null) ? (int) $ba['jml_tidak_hadir'] : null,
                'alasan_summary'   => $alasanSummaryLuar,
                'selisih'          => null,
                'jam_mulai'        => $ba['jam_mulai'] ?? null,
                'jam_selesai'      => $ba['jam_selesai'] ?? null,
                'waktu_mulai'      => null,
                'waktu_akhir'      => null,
                'uraian'           => $ba['uraian'] ?? null,
                'kendala'          => $ba['kendala'] ?? null,
                'solusi'           => $ba['solusi'] ?? null,
                'id_laporan'       => $ba['id_laporan'] ?? null,
                'uploader'         => $ba['uploader'] ?? null,
                'jml_berita_acara' => count($listBA),
                'file'             => $ba['file'] ?? null,
                'file_2'           => $ba['file_2'] ?? null,
                'file_3'           => $ba['file_3'] ?? null,
                'di_luar_jadwal'   => true,
            ];
        }

        // Urutkan: jam ke -> ruang -> mapel
        usort($rekap, static function ($a, $b) {
            return [$a['jam_ke'], $a['nama_ruang'], $a['nama_mapel']] <=> [$b['jam_ke'], $b['nama_ruang'], $b['nama_mapel']];
        });
        foreach ($rekap as $i => $r) {
            $rekap[$i]['no'] = $i + 1;
        }

        // ---------- 6. Rekap per mata pelajaran (Bagian B) ----------
        $mapelSummary = [];
        foreach ($rekap as $r) {
            $mid = $r['id_mapel'];
            if (! isset($mapelSummary[$mid])) {
                $mapelSummary[$mid] = [
                    'id_mapel'    => $mid,
                    'nama_mapel'  => $r['nama_mapel'],
                    'kelas'       => [],
                    'jml_peserta' => 0,
                    'jml_sesi'    => 0,
                    'jml_terisi'  => 0,
                ];
            }
            foreach ($r['kelas'] as $k) {
                $mapelSummary[$mid]['kelas'][(string) $k] = $k;
            }
            $mapelSummary[$mid]['jml_peserta'] += (int) $r['jml_sistem'];
            $mapelSummary[$mid]['jml_sesi']++;
            if ($r['ada_berita_acara']) {
                $mapelSummary[$mid]['jml_terisi']++;
            }
        }
        $mapelSummary = array_values($mapelSummary);
        foreach ($mapelSummary as $i => $m) {
            $mapelSummary[$i]['kelas_display'] = implode(', ', $m['kelas']);
            $mapelSummary[$i]['kelas']        = array_values($m['kelas']);
        }

        // ---------- 7. Daftar siswa tidak hadir (Bagian D) ----------
        // Deduplikasi per siswa: satu siswa absen di beberapa mapel tetap tampil 1 baris
        $tidakHadirAgg = [];
        foreach ($baIndex as $key => $listBA) {
            $ba = $listBA[count($listBA) - 1];
            if (empty($ba['siswa_tidak_hadir']) || ! is_array($ba['siswa_tidak_hadir'])) {
                continue;
            }
            $sesi = null;
            foreach ($rekap as $r) {
                if ($r['id_mapel'] . '-' . $r['id_ruang'] . '-' . $r['id_sesi'] === $key) {
                    $sesi = $r;
                    break;
                }
            }
            foreach ($ba['siswa_tidak_hadir'] as $s) {
                $sid   = $s['id'] ?? ($s['id_siswa'] ?? null);
                $kelas = $s['nama_kelas'] ?? '-';
                if ($kelas === '-' && $sid) {
                    $row = $this->db->table('kelas_siswa ks')
                        ->select('mk.nama_kelas')
                        ->join('master_kelas mk', 'ks.id_kelas = mk.id_kelas')
                        ->where('ks.id_siswa', $sid)
                        ->where('ks.id_tp', $tp->id_tp)
                        ->where('ks.id_smt', $smt->id_smt)
                        ->get()->getRow();
                    $kelas = $row ? $row->nama_kelas : '-';
                }

                $nama  = $s['nama'] ?? '-';
                $keySis = strtolower($nama . '|' . $kelas);
                if (! isset($tidakHadirAgg[$keySis])) {
                    $tidakHadirAgg[$keySis] = [
                        'nama'     => $nama,
                        'kelas'    => $kelas,
                        'mapel'    => [],
                        'ruang'    => [],
                        'sesi'     => [],
                        'alasan'   => $s['alasan'] ?? '-',
                    ];
                }
                $mapel = $sesi['nama_mapel'] ?? ($ba['mapel_nama'] ?? '-');
                $ruang = $sesi['nama_ruang'] ?? ($ba['ruang_nama'] ?? '-');
                $sesiNm = $sesi['nama_sesi'] ?? ($ba['sesi_nama'] ?? '-');
                if (! in_array($mapel, $tidakHadirAgg[$keySis]['mapel'], true)) {
                    $tidakHadirAgg[$keySis]['mapel'][] = $mapel;
                }
                if (! in_array($ruang, $tidakHadirAgg[$keySis]['ruang'], true)) {
                    $tidakHadirAgg[$keySis]['ruang'][] = $ruang;
                }
                if (! in_array($sesiNm, $tidakHadirAgg[$keySis]['sesi'], true)) {
                    $tidakHadirAgg[$keySis]['sesi'][] = $sesiNm;
                }
                if (empty($tidakHadirAgg[$keySis]['alasan']) || $tidakHadirAgg[$keySis]['alasan'] === '-') {
                    $tidakHadirAgg[$keySis]['alasan'] = $s['alasan'] ?? '-';
                }
            }
        }

        $tidakHadirList = array_values($tidakHadirAgg);
        foreach ($tidakHadirList as $i => $t) {
            $tidakHadirList[$i]['nama_mapel'] = implode(', ', $t['mapel']);
            $tidakHadirList[$i]['nama_ruang'] = implode(', ', $t['ruang']);
            $tidakHadirList[$i]['nama_sesi']  = implode(', ', $t['sesi']);
            $tidakHadirList[$i]['jml_mapel']  = count($t['mapel']);
            unset($tidakHadirList[$i]['mapel'], $tidakHadirList[$i]['ruang'], $tidakHadirList[$i]['sesi']);
        }

        // ---------- 8. Data pengawas (Bagian E) ----------
        $pengawas = [];
        $idJadwals = array_column($jadwal_list, 'id_jadwal');
        if (! empty($idJadwals)) {
            $rows = $this->db->table('cbt_pengawas a')
                ->select('a.id_guru, a.id_ruang, a.id_sesi, r.nama_ruang, c.nama_mapel, c.id_mapel')
                ->join('cbt_jadwal j', 'a.id_jadwal = j.id_jadwal', 'left')
                ->join('cbt_bank_soal bs', 'j.id_bank = bs.id_bank', 'left')
                ->join('master_mapel c', 'bs.bank_mapel_id = c.id_mapel', 'left')
                ->join('cbt_ruang r', 'a.id_ruang = r.id_ruang', 'left')
                ->where('a.id_tp', $tp->id_tp)
                ->where('a.id_smt', $smt->id_smt)
                ->whereIn('a.id_jadwal', $idJadwals)
                ->get()->getResult();

            $gurus    = $this->master->getDropdownGuru();
            $panitias = $this->master->getDropdownPanitia();

            // Ambil NIP sekali jalan
            $nipMap   = [];
            $idsGuru  = [];
            $idsPanitia = [];
            foreach ($rows as $p) {
                foreach (explode(',', (string) ($p->id_guru ?? '')) as $id) {
                    $id = trim($id);
                    if ($id === '') {
                        continue;
                    }
                    if (strpos($id, 'P') === 0) {
                        $idsPanitia[] = (int) substr($id, 1);
                    } else {
                        $idsGuru[] = (int) $id;
                    }
                }
            }
            if (! empty($idsGuru)) {
                foreach ($this->db->table('master_guru')->select('id_guru, nip')->whereIn('id_guru', array_unique($idsGuru))->get()->getResult() as $g) {
                    $nipMap[(string) $g->id_guru] = $g->nip ?? '-';
                }
            }
            if (! empty($idsPanitia)) {
                foreach ($this->db->table('master_panitia')->select('id_panitia, nip')->whereIn('id_panitia', array_unique($idsPanitia))->get()->getResult() as $p) {
                    $nipMap['P' . $p->id_panitia] = $p->nip ?? '-';
                }
            }

            $seen = [];
            foreach ($rows as $p) {
                foreach (explode(',', (string) ($p->id_guru ?? '')) as $id) {
                    $id = trim($id);
                    if ($id === '') {
                        continue;
                    }
                    $nama = $gurus[$id] ?? ($panitias[$id] ?? 'Tidak Diketahui');
                    $key  = $nama . '|' . ($p->nama_mapel ?? '-') . '|' . ($p->nama_ruang ?? '-');
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;

                    // Kelompokkan per (nama, ruang) agar tidak tampil berulang untuk tiap mapel
                    $keyPg = $nama . '|' . ($p->nama_ruang ?? '-');
                    if (! isset($pengawas[$keyPg])) {
                        $pengawas[$keyPg] = [
                            'id_guru'    => $id,
                            'nama_guru'  => $nama,
                            'nip'        => $nipMap[$id] ?? '-',
                            'nama_mapel' => [],
                            'nama_ruang' => $p->nama_ruang ?? '-',
                        ];
                    }
                    $mapel = $p->nama_mapel ?? '-';
                    if ($mapel !== '-' && ! in_array($mapel, $pengawas[$keyPg]['nama_mapel'], true)) {
                        $pengawas[$keyPg]['nama_mapel'][] = $mapel;
                    }
                }
            }
            foreach ($pengawas as $keyPg => $pg) {
                $pengawas[$keyPg]['nama_mapel'] = implode(', ', $pg['nama_mapel']);
            }
            $pengawas = array_values($pengawas);
            usort($pengawas, static function ($a, $b) {
                return [$a['nama_ruang'], $a['nama_guru']] <=> [$b['nama_ruang'], $b['nama_guru']];
            });
        }

        // ---------- 9. Kendala selama ujian (Bagian G) ----------
        $kendala = [];
        foreach ($rekap as $r) {
            if (empty($r['kendala']) || trim((string) $r['kendala']) === '') {
                continue;
            }
            $kendala[] = [
                'waktu'     => $r['jam_mulai'] ?? '-',
                'nama_mapel' => $r['nama_mapel'],
                'nama_ruang' => $r['nama_ruang'],
                'kendala'   => $r['kendala'],
                'solusi'    => $r['solusi'] ?? '-',
            ];
        }

        // ---------- 10. Angka rekap & kesimpulan ----------
        $totalSe_should   = 0;
        $totalHadir       = 0;
        $totalTidakHadir  = 0;
        $totalSistem      = 0;
        $sesiTerisi       = 0;
        $sesiKosong       = 0;
        $selisihList      = [];
        $daftarTidakLengkap = [];
        foreach ($rekap as $r) {
            $totalSistem += (int) $r['jml_sistem'];
            if (! $r['ada_berita_acara']) {
                $sesiKosong++;
                continue;
            }
            $sesiTerisi++;
            $totalSe_should  += (int) ($r['jml_seharusnya'] ?? 0);
            $totalHadir      += (int) ($r['jml_hadir'] ?? 0);
            $totalTidakHadir += (int) ($r['jml_tidak_hadir'] ?? 0);
            if (! empty($r['selisih'])) {
                $selisihList[] = $r;
            }
            // Daftar nama siswa tidak hadir tidak sama dengan jumlah yang dilaporkan
            if ($r['ada_berita_acara']
                && (int) ($r['jml_tidak_hadir'] ?? 0) !== (int) ($r['jml_daftar_absen'] ?? 0)) {
                $daftarTidakLengkap[] = $r;
            }
        }
        $persentase = $totalSe_should > 0 ? round(($totalHadir / $totalSe_should) * 100, 1) : 0.0;

        // Total alasan ketidakhadiran
        $totalAlasan = ['Sakit' => 0, 'Ijin' => 0, 'Tanpa Berita' => 0, 'Lainnya' => 0];
        foreach ($rekap as $r) {
            foreach (($r['alasan_summary'] ?? []) as $alasan => $jml) {
                if (! isset($totalAlasan[$alasan])) {
                    $totalAlasan['Lainnya'] += (int) $jml;
                    continue;
                }
                $totalAlasan[$alasan] += (int) $jml;
            }
        }

        // ---------- 11. Waktu & tempat pelaksanaan ----------
        $mulai   = [];
        $selesai = [];
        $rooms   = [];
        foreach ($rekap as $r) {
            $start = $r['jam_mulai'] ?? $r['waktu_mulai'];
            $end   = $r['jam_selesai'] ?? $r['waktu_akhir'];
            if (! empty($start)) {
                $mulai[] = substr((string) $start, 0, 5);
            }
            if (! empty($end)) {
                $selesai[] = substr((string) $end, 0, 5);
            }
            if (! empty($r['nama_ruang']) && ! in_array($r['nama_ruang'], $rooms, true)) {
                $rooms[] = $r['nama_ruang'];
            }
        }
        sort($mulai);
        sort($selesai);
        $jamMulai   = $mulai ? $mulai[0] : '-';
        $jamSelesai = $selesai ? $selesai[count($selesai) - 1] : '-';

        // ---------- 12. Catatan penting ----------
        $catatan = [];
        $notes   = [];
        foreach ($rekap as $r) {
            if (empty($r['uraian']) || trim((string) $r['uraian']) === '') {
                continue;
            }
            $clean = trim(preg_replace('/[.\s]+$/', '', (string) $r['uraian']));
            $key   = strtolower($clean);
            // Normalisasi: buang tanda baca & kata umum agar uraian yang sama tidak terulang
            $key   = preg_replace('/[^a-z0-9\s]/', ' ', $key);
            $key   = preg_replace('/\b(dengan|secara|yang|dan|di|ke|dari|untuk|pada|telah|sudah|adalah|ini|hari|berjalan|terlaksana|tertib|lancar|aman|baik)\b/', ' ', $key);
            $key   = preg_replace('/\s+/', ' ', trim($key));
            if ($key === '' || isset($notes[$key])) {
                continue;
            }
            $notes[$key] = true;
            $catatan[]   = ucfirst($clean) . '.';
        }
        if ($sesiKosong > 0) {
            $catatan[] = "Terdapat {$sesiKosong} sesi ujian yang belum diisi Berita Acaranya.";
        }
        if (! empty($selisihList)) {
            $catatan[] = 'Jumlah kehadiran pada ' . count($selisihList) . ' sesi tidak konsisten (hadir + tidak hadir tidak sama dengan jumlah peserta).';
        }
        if (! empty($daftarTidakLengkap)) {
            $catatan[] = 'Pada ' . count($daftarTidakLengkap) . ' sesi, jumlah peserta tidak hadir tidak sama dengan jumlah nama siswa yang tidak hadir tercatat (daftar belum lengkap).';
        }

        // ---------- 13. Jenis ujian ----------
        $jenisNames = [];
        foreach ($jadwal_list as $j) {
            if (! empty($j->id_jenis)) {
                $jenisNames[(int) $j->id_jenis] = true;
            }
        }
        $jenisUjian = 'Ujian Harian';
        if (! empty($jenisNames)) {
            $rows = $this->db->table('cbt_jenis')->select('id_jenis, nama_jenis, kode_jenis')->whereIn('id_jenis', array_keys($jenisNames))->get()->getResult();
            $labels = [];
            foreach ($rows as $r) {
                $labels[] = $r->nama_jenis . (! empty($r->kode_jenis) ? ' (' . $r->kode_jenis . ')' : '');
            }
            $jenisUjian = $labels ? implode(' / ', $labels) : 'Ujian Harian';
        }

        // ---------- 14. Lampiran foto ----------
        $lampiranFoto = [];
        foreach ($rekap as $r) {
            if (empty($r['id_laporan'])) {
                continue;
            }
            $keterangan = ! empty($r['uraian']) ? $r['uraian'] : 'Dokumentasi pelaksanaan ujian';
            $files      = ['file', 'file_2', 'file_3'];
            $no         = 1;
            foreach ($files as $f) {
                if (! empty($r[$f])) {
                    $lampiranFoto[] = [
                        'file'       => $r[$f],
                        'keterangan' => $keterangan . ' (Foto ' . $no . ')',
                        'mapel'      => $r['nama_mapel'],
                        'ruang'      => $r['nama_ruang'],
                    ];
                    $no++;
                }
            }
        }
        if (count($lampiranFoto) > 12) {
            shuffle($lampiranFoto);
            $lampiranFoto = array_slice($lampiranFoto, 0, 12);
        }

        return [
            'tanggal'       => $tanggal,
            'jenis_ujian'   => $jenisUjian,
            'jadwal'        => $jadwal_list,
            'mapel'         => $mapelSummary,
            'rekap'         => $rekap,
            'tidak_hadir'   => $tidakHadirList,
            'pengawas'      => $pengawas,
            'kendala'       => $kendala,
            'setting'       => $this->cbt->getSettingKopBeritaAcara(),
            'tp'            => $tp,
            'smt'           => $smt,
            'ringkasan'     => [
                'jml_sesi'          => count($rekap),
                'sesi_terisi'       => $sesiTerisi,
                'sesi_kosong'       => $sesiKosong,
                'total_hadir'       => $totalHadir,
                'total_tidak_hadir' => $totalTidakHadir,
                'total_peserta'     => $totalSe_should,
                'total_sistem'      => $totalSistem,
                'persentase_hadir'  => $persentase,
                'jam_mulai'         => $jamMulai,
                'jam_selesai'       => $jamSelesai,
                'ruang'             => $rooms,
                'catatan'           => $catatan ? implode(' ', $catatan) : 'Ujian berjalan lancar dan tertib.',
                'alasan_summary'    => $totalAlasan,
                'kondisi'           => $kendala ? 'Terkendala' : 'Lancar dan tertib',
                'selisih'           => count($selisihList),
                'daftar_tidak_lengkap' => count($daftarTidakLengkap),
            ],
            'lampiran_foto' => $lampiranFoto,
        ];
    }

    public function cetak_harian()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->showError('Akses Terlarang', 403);
        }

        $tanggal = trim((string) $this->request->getGet('tanggal'));
        if (! $tanggal || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return $this->showError('Tanggal tidak valid', 400);
        }

        try {
            $laporan = $this->buildLaporanHarian($tanggal);
        } catch (\Throwable $e) {
            log_message('error', 'cetak_harian: ' . $e->getMessage());

            return $this->showError('Gagal menyusun laporan: ' . $e->getMessage(), 500);
        }

        // Format tanggal Indonesia
        $bulan_indo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $hari_indo  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $date_obj   = new \DateTime($tanggal);
        $laporan['tanggal_format'] = $hari_indo[$date_obj->format('w')] . ', ' . $date_obj->format('j') . ' ' . $bulan_indo[$date_obj->format('n') - 1] . ' ' . $date_obj->format('Y');
        $laporan['tanggal_ttd']    = $date_obj->format('j') . ' ' . $bulan_indo[$date_obj->format('n') - 1] . ' ' . $date_obj->format('Y');
        $laporan['kota']           = $laporan['setting']->kota ?? 'Kota';

        // Generate PDF
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);
        $html = view('cbt/laporan/cetak_harian', $laporan);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        try {
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'Laporan_Harian_' . str_replace('-', '_', $tanggal) . '.pdf';
            $pdfContent = $dompdf->output();

            if (empty($pdfContent)) {
                throw new \Exception('PDF content is empty');
            }

            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($pdfContent);
        } catch (\Throwable $e) {
            log_message('error', 'PDF generation failed: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Gagal generate PDF: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}
