<?php

namespace App\Models;

use App\Services\ScoringService;
use App\Traits\SiswaAktifTrait;
use CodeIgniter\Model;

/**
 * CbtModel - Migrated from CI3 Cbt_model
 * Core CBT functionality: jadwal, bank soal, token
 */
class CbtModel extends Model
{
    use SiswaAktifTrait;

    protected $db;
    protected $cache;

    // Cache keys
    protected const CACHE_TOKEN = 'cbt_token';
    protected const CACHE_TTL_TOKEN = 60; // 1 minute for token (frequently updated)

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db = \Config\Database::connect();
            $this->cache = \Config\Services::cache();
            $this->ensurePerformanceIndexes();
        } catch (\Throwable $e) {
            // Database tidak tersedia: biarkan $this->db null sehingga controller
            // tetap bisa di-instansiasi. Method yang butuh DB akan gagal
            // dengan pesan yang informatif melalui try-catch masing-masing.
            log_message('warning', '[CbtModel] Database tidak tersedia saat konstruktor: ' . $e->getMessage());
            $this->db = null;
            $this->cache = null;
        }
    }

    private function ensurePerformanceIndexes(): void
    {
        if ($this->db === null) {
            return;
        }

        if (strtolower((string) ($this->db->DBDriver ?? '')) === 'sqlite3') {
            return;
        }

        $indexes = [
            'cbt_bank_soal' => [
                'idx_bank_guru_scope' => 'ALTER TABLE cbt_bank_soal ADD INDEX idx_bank_guru_scope (bank_mapel_id, bank_guru_id, id_tp, id_smt)'
            ],
            'cbt_soal' => [
                'idx_soal_bank_jenis' => 'ALTER TABLE cbt_soal ADD INDEX idx_soal_bank_jenis (bank_id, jenis)'
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $name => $sql) {
                try {
                    $query = $this->db->table('information_schema.STATISTICS')
                        ->select('INDEX_NAME')
                        ->where('TABLE_SCHEMA', $this->db->getDatabase())
                        ->where('TABLE_NAME', $table)
                        ->where('INDEX_NAME', $name)
                        ->limit(1)
                        ->get();
                    
                    $exists = $query ? $query->getRow() : null;

                    if (!$exists) {
                        $this->db->query($sql);
                    }
                } catch (\Throwable $e) {
                    // Silently fail if ALTER is not permitted
                }
            }
        }
    }

    // ==========================================
    // TOKEN FUNCTIONS
    // ==========================================

    /**
     * Get current token with caching
     */
    public function getToken()
    {
        // Try cache first
        $cached = $this->cache->get(self::CACHE_TOKEN);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $query = $this->db->table('cbt_token')
                ->limit(1)
                ->get();
            
            $result = $query ? $query->getRow() : null;

            // Save to cache (short TTL as token changes frequently)
            $this->cache->save(self::CACHE_TOKEN, $result, self::CACHE_TTL_TOKEN);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'getToken error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear token cache
     */
    public function clearTokenCache(): void
    {
        $this->cache->delete(self::CACHE_TOKEN);
    }

    /**
     * Update or create token (single token only - no history)
     * - If token exists: UPDATE it
     * - If no token: INSERT new one
     */
    public function saveToken(array $data)
    {
        try {
            // Filter out columns that might not exist in DB
            if (isset($data['status'])) {
                unset($data['status']);
            }
            if (isset($data['updated'])) {
                unset($data['updated']);
            }
            if (isset($data['now'])) {
                unset($data['now']);
            }

            $existing = $this->getToken();

            // If token already exists, always UPDATE (no new inserts)
            if ($existing) {
                // Find the primary key column
                $pk = isset($existing->id_token) ? 'id_token' : 'token';
                $val = isset($existing->id_token) ? $existing->id_token : $existing->token;

                $result = $this->db->table('cbt_token')->where($pk, $val)->update($data);
                
                // Clear cache after update
                $this->clearTokenCache();
                
                return $result;
            }

            // Only INSERT if table is completely empty
            $result = $this->db->table('cbt_token')->insert($data);
            
            // Clear cache after insert
            $this->clearTokenCache();
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'saveToken error: ' . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // JADWAL FUNCTIONS
    // ==========================================

    /**
     * Get jadwal by ID
     */
    public function getJadwal(int $idJadwal): ?object
    {
        $query = $this->db->table('cbt_jadwal a')
            ->select('a.*, c.nama_mapel, c.kode as kode_mapel, b.bank_kode, b.bank_nama, b.bank_mapel_id as id_mapel, b.bank_kelas')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left')
            ->where('a.id_jadwal', $idJadwal)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get all jadwal by TP and semester
     */
    public function getAllJadwal(int $tp, int $smt): array
    {
        return $this->db->table('cbt_jadwal a')
            ->select('a.*, c.nama_mapel, c.kode as kode_mapel, b.bank_kode, b.bank_nama, b.bank_kelas, b.bank_mapel_id, b.bank_guru_id')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('a.tgl_mulai', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Get distinct dates from jadwal
     */
    public function getAllJadwalDates(int $tp, int $smt): array
    {
        $result = $this->db->table('cbt_jadwal')
            ->select('tgl_mulai')
            ->distinct()
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->orderBy('tgl_mulai', 'DESC')
            ->get()
            ->getResult();

        return array_column($result, 'tgl_mulai');
    }

    /**
     * Get distinct ruang and sesi based on class IDs
     * Port from CI3 getDistinctRuang
     */
    public function getDistinctRuangSesi(int $tp, int $smt, array $arrKelas): array
    {
        $ruangs = [];
        $sesis = [];

        if (empty($arrKelas)) {
            return ['ruang' => $ruangs, 'sesi' => $sesis];
        }

        $result = $this->db->table('cbt_sesi_siswa a')
            ->distinct()
            ->select('a.ruang_id, a.sesi_id, b.id_ruang, b.kode_ruang, b.nama_ruang, c.id_sesi, c.kode_sesi, c.nama_sesi')
            ->join('cbt_ruang b', 'b.id_ruang = a.ruang_id')
            ->join('cbt_sesi c', 'c.id_sesi = a.sesi_id')
            ->where('a.tp_id', $tp)
            ->where('a.smt_id', $smt)
            ->whereIn('a.kelas_id', $arrKelas)
            ->orderBy('b.nama_ruang', 'ASC')
            ->orderBy('c.nama_sesi', 'ASC')
            ->get()
            ->getResult();

        if ($result) {
            foreach ($result as $row) {
                $ruangs[$row->ruang_id] = $row->nama_ruang;
                $sesis[$row->sesi_id] = $row->nama_sesi;
            }
        }

        return ['ruang' => $ruangs, 'sesi' => $sesis];
    }

    /**
     * Get jadwal by date
     */
    public function getDataJadwalByTgl(string $tanggal): array
    {
        return $this->db->table('cbt_jadwal a')
            ->select('a.*, b.bank_kode, b.bank_nama, b.bank_kelas, c.nama_mapel')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left')
            ->where('DATE(a.tgl_mulai)', $tanggal)
            ->orderBy('a.jam_ke', 'ASC')
            ->get()
            ->getResult();
    }

    public function getAllJadwalByTanggal($tp, $smt, $jenis, $guru = null)
    {
        $builder = $this->db->table('cbt_jadwal a');
        $builder->select('a.*, b.bank_kode, b.bank_kelas, b.bank_mapel_id, b.bank_level, b.bank_jenis_id, c.nama_mapel, c.kode as kode_mapel, b.bank_mapel_id as id_mapel');
        $builder->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left');
        $builder->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left');

        $builder->where('a.id_tp', $tp);
        $builder->where('a.id_smt', $smt);
        if ($jenis) {
            $builder->where('a.id_jenis', $jenis);
        }
        if ($guru) {
            $builder->where('b.bank_guru_id', $guru);
        }

        $builder->orderBy('a.tgl_mulai', 'ASC');
        $builder->orderBy('a.jam_ke', 'ASC');

        $result = $builder->get()->getResult();

        foreach ($result as &$row) {
            $row->bank_kelas = $this->maybeUnserialize($row->bank_kelas ?? '');
        }

        return $result;
    }

    /**
     * Get active jadwal for today
     */
    public function getActiveJadwal(int $tp, int $smt): array
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->table('cbt_jadwal a')
            ->select('a.*, b.bank_kode, b.bank_nama, c.nama_mapel')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->where('a.tgl_mulai <=', $now)
            ->where('a.tgl_selesai >=', $now)
            ->get()
            ->getResult();
    }

    // ==========================================
    // RUANG & SESI FUNCTIONS
    // ==========================================

    /**
     * Get all ruang
     */
    public function getAllRuang(): array
    {
        return $this->db->table('cbt_ruang')
            ->orderBy('nama_ruang', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get ruang and sesi mapping for exam
     */
    public function getRuangSesi(int $tp, int $smt): array
    {
        $builder = $this->db->table('cbt_sesi_siswa a');
        $builder->select('a.siswa_id, a.sesi_id, a.ruang_id, a.kelas_id, ' .
            'b.nama_ruang, b.kode_ruang, c.nama_sesi, c.kode_sesi, d.nama_kelas');
        $builder->where('a.tp_id', $tp);
        $builder->where('a.smt_id', $smt);
        $builder->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $builder->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        $builder->join('master_kelas d', 'd.id_kelas=a.kelas_id');
        $builder->orderBy('b.nama_ruang', 'ASC');
        $builder->orderBy('c.nama_sesi', 'ASC');
        $result = $builder->get()->getResult();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->sesi_id][$row->ruang_id][$row->kelas_id] = $row->nama_kelas;
            }
        }
        return $ret;
    }

    /**
     * Get ruang by ID
     */
    public function getRuangById(int $id): ?object
    {
        $query = $this->db->table('cbt_ruang')
            ->where('id_ruang', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get distinct ruang/sesi combinations helper
     */
    public function getDistinctRuang(int $tp, int $smt, array $arrKelas): array
    {
        $builder = $this->db->table('cbt_sesi_siswa a');
        $builder->select('a.ruang_id, a.sesi_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi, c.waktu_mulai, c.waktu_akhir, c.istirahat');
        $builder->distinct();
        $builder->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $builder->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        $builder->where('a.tp_id', $tp);
        $builder->where('a.smt_id', $smt);
        if (!empty($arrKelas)) {
            $builder->whereIn('a.kelas_id', $arrKelas);
        }
        $builder->orderBy('b.nama_ruang', 'ASC');
        $builder->orderBy('c.nama_sesi', 'ASC');
        $result = $builder->get()->getResult();

        $sesis = [];
        if ($result) {
            foreach ($result as $row) {
                $sesis[$row->sesi_id] = $row;
            }
        }
        return $sesis;
    }

    /**
     * Get all sesi
     */
    public function getAllSesi(): array
    {
        return $this->db->table('cbt_sesi')
            ->orderBy('nama_sesi', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get sesi by ID
     */
    public function getSesiById(int $id): ?object
    {
        $query = $this->db->table('cbt_sesi')
            ->where('id_sesi', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get all kode sesi
     */
    public function getAllKodeSesi(): array
    {
        $result = $this->db->table('cbt_sesi')
            ->select('id_sesi, nama_sesi, kode_sesi, waktu_mulai, waktu_akhir')
            ->get()
            ->getResult();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->kode_sesi] = $row;
            }
        }
        return $ret;
    }

    /**
     * Get distinct ruang/sesi combinations
     */
    public function getDistinctSesi(int $tp, int $smt, array $idsKelas): array
    {
        if (empty($idsKelas)) {
            return [];
        }

        $builder = $this->db->table('cbt_sesi_siswa a')
            ->select('a.ruang_id, a.sesi_id, b.nama_ruang, b.kode_ruang, c.nama_sesi, c.kode_sesi, ' .
                'c.waktu_mulai, c.waktu_akhir, c.istirahat, ' .
                'GROUP_CONCAT(DISTINCT a.kelas_id ORDER BY a.kelas_id) as kelas_ids, ' .
                'GROUP_CONCAT(DISTINCT d.nama_kelas ORDER BY a.kelas_id) as nama_kelases, ' .
                'COUNT(DISTINCT a.siswa_id) as peserta')
            ->join('cbt_ruang b', 'a.ruang_id = b.id_ruang') // INNER JOIN to match CI3
            ->join('cbt_sesi c', 'a.sesi_id = c.id_sesi')   // INNER JOIN to match CI3
            ->join('master_kelas d', 'a.kelas_id = d.id_kelas', 'left')
            ->where('a.tp_id', $tp)
            ->where('a.smt_id', $smt)
            ->where('a.sesi_id IS NOT NULL')   // Exclude NULL sesi
            ->where('a.ruang_id IS NOT NULL')  // Exclude NULL ruang
            ->whereIn('a.kelas_id', $idsKelas)
            ->groupBy(['a.ruang_id', 'a.sesi_id']);

        // Jumlah peserta hanya menghitung siswa berstatus AKTIF.
        $this->onlySiswaAktif($builder, 'a', 'bi', 'siswa_id');

        $result = $builder->get()->getResult();

        foreach ($result as $row) {
            $ids = explode(',', $row->kelas_ids ?? '');
            $names = explode(',', $row->nama_kelases ?? '');
            $row->kelas_id = $ids;

            // Map className by ID for JS consumption keys
            $row->nama_kelas = [];
            foreach ($ids as $idx => $id) {
                if (isset($names[$idx])) {
                    $row->nama_kelas[$id] = $names[$idx];
                }
            }
        }

        return $result;
    }

    /**
     * Get students with their assigned ruang and sesi
     *
     * Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang ditampilkan,
     * sehingga siswa LULUS/PINDAH/KELUAR tidak bisa ditugaskan ke ruang/sesi.
     */
    public function getRuangSesiSiswa(int $kls, int $tp, int $smt): array
    {
        return $this->onlySiswaAktif($this->db->table('kelas_siswa a')
            ->select('a.id_siswa as siswa_id, a.id_kelas as kelas_id, b.nama, b.nis, b.nisn, c.id_sesi_siswa, c.ruang_id, c.sesi_id')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('cbt_sesi_siswa c', 'a.id_siswa = c.siswa_id AND c.tp_id = ' . $tp . ' AND c.smt_id = ' . $smt, 'left'), 'a')
            ->where('a.id_kelas', $kls)
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('b.nama', 'ASC')
            ->get()
            ->getResult();
    }

    // ==========================================
    // JENIS UJIAN FUNCTIONS
    // ==========================================

    /**
     * Get all jenis ujian
     */
    public function getAllJenisUjian(bool $activeOnly = false): array
    {
        $builder = $this->db->table('cbt_jenis');

        if ($activeOnly) {
            $builder->where('aktif', '1');
        }

        return $builder->orderBy('id_jenis', 'ASC')->get()->getResult();
    }

    /**
     * Get active jenis ujian ID
     */
    public function getActiveJenisId(): ?int
    {
        $query = $this->db->table('cbt_jenis')
            ->select('id_jenis')
            ->where('status', '1')
            ->limit(1)
            ->get();
        
        $result = $query ? $query->getRow() : null;

        return $result ? (int) $result->id_jenis : null;
    }

    /**
     * Get jenis ujian for Datatable
     */
    public function getJenis(): array
    {
        $result = $this->db->table('cbt_jenis')->get()->getResult();
        $data = [];
        foreach ($result as $i => $row) {
            $data[] = [
                'no' => $i + 1,
                'id_jenis' => $row->id_jenis,
                'nama_jenis' => $row->nama_jenis,
                'kode_jenis' => $row->kode_jenis,
                'status' => $row->status
            ];
        }
        return ['data' => $data];
    }

    /**
     * Update jenis ujian from request
     */
    public function updateJenis(int $id, array $data): bool
    {
        return $this->db->table('cbt_jenis')->where('id_jenis', $id)->update($data);
    }

    // ==========================================
    // BANK SOAL FUNCTIONS
    // ==========================================

    /**
     * Get all bank soal for specific TP and semester
     */
    public function getAllBankSoal(int $tp, int $smt): array
    {
        return $this->db->table('cbt_bank_soal a')
            ->select('a.*, b.nama_mapel')
            ->join('master_mapel b', 'a.bank_mapel_id = b.id_mapel', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->get()
            ->getResult();
    }

    /**
     * Get bank soal by ID with details
     */
    public function getBankSoalById(int $idBank): ?object
    {
        $query = $this->db->table('cbt_bank_soal a')
            ->select('a.*, b.nama_mapel, b.kode as kode_mapel, c.nama_guru, d.nama_jurusan, d.kode_jurusan,
                (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan')
            ->join('master_mapel b', 'a.bank_mapel_id = b.id_mapel', 'left')
            ->join('master_guru c', 'a.bank_guru_id = c.id_guru', 'left')
            ->join('master_jurusan d', 'a.bank_jurusan_id = d.id_jurusan', 'left')
            ->where('a.id_bank', $idBank)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get all bank soal with details and filtering
     */
    public function getDataBank(?int $guru = null, ?int $mapel = null, ?int $level = null, ?string $subtes = null): array
    {
        $builder = $this->db->table('cbt_bank_soal a')
            ->select('a.*, b.nama_mapel, b.kode as kode, c.nama_guru, k.judul as kartu_judul,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.tampilkan = "1") AS total_tampil,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="1") AS total_pg,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="2") AS total_kompleks,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="3") AS total_jodohkan,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="4") AS total_isian,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="5") AS total_esai,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="6") AS total_urutan,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="7") AS total_cloze,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.ai_generated = 1) AS total_ai,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND (cbt_soal.ai_generated = 0 OR cbt_soal.ai_generated IS NULL)) AS total_manual,
                (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan')
            ->join('master_mapel b', 'a.bank_mapel_id = b.id_mapel', "left")
            ->join('master_guru c', 'a.bank_guru_id = c.id_guru', "left")
            ->join('cbt_kartu_soal k', 'a.kartu_soal_id = k.id_kartu', "left");

        if ($guru !== null)
            $builder->where('a.bank_guru_id', $guru);
        if ($mapel !== null)
            $builder->where('a.bank_mapel_id', $mapel);
        if ($level !== null)
            $builder->where('a.bank_level', $level);
        if ($subtes !== null && $subtes !== '')
            $builder->where('a.subtes_code', $subtes);

        $result = $builder->orderBy('a.bank_level', 'ASC')->get()->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_tp][$row->id_smt][] = $row;
        }
        return $data;
    }

    /**
     * Get all soal by bank ID
     */
    public function getAllSoalByBank(int $idBank, ?int $jenis = null): array
    {
        $builder = $this->db->table('cbt_soal a')
            ->select('a.*, MIN(b.id_item) as kartu_item_id, c.judul as kartu_judul')
            ->join('cbt_kartu_soal_item b', 'a.id_soal = b.soal_id', 'left')
            ->join('cbt_kartu_soal c', 'b.kartu_id = c.id_kartu', 'left')
            ->where('a.bank_id', $idBank)
            ->groupBy('a.id_soal');

        if ($jenis !== null)
            $builder->where('a.jenis', $jenis);

        return $builder->orderBy('a.nomor_soal', 'ASC')->get()->getResult();
    }

    /**
     * Count soal per type in bank
     */
    public function getJumlahJenisSoal(int $idBank): array
    {
        return $this->db->table('cbt_soal')
            ->select('jenis, COUNT(*) as jumlah')
            ->where('bank_id', $idBank)
            ->groupBy('jenis')
            ->get()
            ->getResult();
    }

    // ==========================================
    // PENGAWAS FUNCTIONS
    // ==========================================

    /**
     * Get all pengawas
     */
    public function getAllPengawas(int $tp, int $smt, ?int $ruang = null, ?int $sesi = null): array
    {
        $builder = $this->db->table('cbt_pengawas');
        $builder->where('id_tp', $tp);
        $builder->where('id_smt', $smt);
        if ($ruang != null)
            $builder->where('id_ruang', $ruang);
        if ($sesi != null)
            $builder->where('id_sesi', $sesi);
        //$builder->orderBy('jam_ke', 'ASC');
        $result = $builder->get()->getResult();

        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                // Determine structure: [id_jadwal][id_ruang][id_sesi] = Object
                // If multiple supervisors map to same slot, logic needs to handle it (currently simplified)
                $ret[$row->id_jadwal][$row->id_ruang][$row->id_sesi] = $row;
            }
        }
        return $ret;
    }

    /**
     * Map pengawas untuk sekumpulan jadwal (id_jadwal).
     *
     * Baris duplikat pada kombinasi (jadwal, ruang, sesi) digabung menjadi satu
     * entri supaya tampilan UI dan proses simpan selalu konsisten.
     *
     * @param  int[] $idJadwals
     * @return array<int, array<int, array<int, array{id_guru: string, jam_ke: int|null, id_pengawas: int[]}>>>
     */
    public function getPengawasMapByJadwal(int $tp, int $smt, array $idJadwals): array
    {
        $ret = [];

        if (empty($idJadwals)) {
            return $ret;
        }

        $rows = $this->db->table('cbt_pengawas')
            ->select('id_pengawas, id_jadwal, id_ruang, id_sesi, id_guru, jam_ke')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->whereIn('id_jadwal', $idJadwals)
            ->orderBy('id_pengawas', 'ASC')
            ->get()->getResult();

        foreach ($rows as $row) {
            $jadwal = (int) $row->id_jadwal;
            $ruang  = (int) $row->id_ruang;
            $sesi   = (int) $row->id_sesi;

            if (!isset($ret[$jadwal][$ruang][$sesi])) {
                $ret[$jadwal][$ruang][$sesi] = [
                    'id_guru'     => [],
                    'jam_ke'      => null,
                    'id_pengawas' => [],
                ];
            }

            foreach (explode(',', (string) ($row->id_guru ?? '')) as $guru) {
                $guru = trim($guru);
                if ($guru === '' || $guru === '0') {
                    continue;
                }
                if (!in_array($guru, $ret[$jadwal][$ruang][$sesi]['id_guru'], true)) {
                    $ret[$jadwal][$ruang][$sesi]['id_guru'][] = $guru;
                }
            }

            $jam = ($row->jam_ke === null || $row->jam_ke === '') ? null : (int) $row->jam_ke;
            if ($jam !== null && $jam > 0) {
                $ret[$jadwal][$ruang][$sesi]['jam_ke'] = $jam;
            } elseif ($ret[$jadwal][$ruang][$sesi]['jam_ke'] === null) {
                $ret[$jadwal][$ruang][$sesi]['jam_ke'] = $jam;
            }

            $ret[$jadwal][$ruang][$sesi]['id_pengawas'][] = (int) $row->id_pengawas;
        }

        foreach ($ret as $jadwal => $byRuang) {
            foreach ($byRuang as $ruang => $bySesi) {
                foreach ($bySesi as $sesi => $data) {
                    $ret[$jadwal][$ruang][$sesi]['id_guru'] = implode(',', $data['id_guru']);
                }
            }
        }

        return $ret;
    }

    /**
     * Get pengawas by jadwal
     */
    public function getPengawasByJadwal($tp, $smt, $id_jadwal, $sesi = null, $ruang = null): array
    {
        $builder = $this->db->table('cbt_pengawas');
        $builder->select('id_pengawas, id_guru, id_jadwal, id_ruang, id_sesi, jam_ke');
        $builder->where('id_tp', $tp);
        $builder->where('id_smt', $smt);
        if (is_array($id_jadwal)) {
            $builder->whereIn('id_jadwal', $id_jadwal);
        } else {
            $builder->where('id_jadwal', $id_jadwal);
        }
        if ($sesi != null) {
            $builder->where('id_sesi', $sesi);
        }
        if ($ruang != null) {
            $builder->where('id_ruang', $ruang);
        }
        return $builder->get()->getResult();
    }

    // ==========================================
    // CBT LOG FUNCTIONS
    // ==========================================

    /**
     * Save CBT activity log
     */
    public function saveLog(int $idSiswa, int $idJadwal, int $type): bool
    {
        $request = service('request');
        $agent = $request->getUserAgent();

        $currAddress = $request->getIPAddress();
        $currAgent = $agent->getBrowser() . ' ' . $agent->getVersion();
        $currDevice = $agent->getPlatform();

        $data = [
            'id_siswa' => $idSiswa,
            'id_jadwal' => $idJadwal,
            'log_type' => $type,
            'address' => $currAddress,
            'agent' => $currAgent,
            'device' => $currDevice,
            'log_time' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('log_ujian')->insert($data);
    }

    /**
     * Get CBT logs by jadwal
     */
    public function getLogsByJadwal(int $idJadwal): array
    {
        return $this->db->table('cbt_log a')
            ->select('a.*, b.nama')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->where('a.id_jadwal', $idJadwal)
            ->orderBy('a.waktu', 'DESC')
            ->get()
            ->getResult();
    }
    public function getSoalByBank(int $idBank, string $acak = '0', int $seed = 0)
    {
        $builder = $this->db->table('cbt_soal')
            ->where('bank_id', $idBank)
            ->where('tampilkan', '1'); // Only show questions marked to display

        if ($acak == '1') {
            // Use seed for consistent randomization per student
            // RAND(seed) ensures same student sees same order each time
            if ($seed > 0) {
                $builder->orderBy("RAND($seed)");
            } else {
                $builder->orderBy('RAND()');
            }
        } else {
            $builder->orderBy('nomor_soal', 'ASC')->orderBy('id_soal', 'ASC');
        }

        return $builder->get()->getResult();
    }

    public function getJawabanSiswa(int $idSiswa, int $idJadwal): array
    {
        $result = $this->db->table('cbt_jawaban')
            ->where('id_siswa', $idSiswa)
            ->where('id_jadwal', $idJadwal)
            ->get()
            ->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_soal] = $row;
        }
        return $data;
    }

    // ==========================================
    // NOMOR PESERTA FUNCTIONS
    // ==========================================

    /**
     * Get students by multiple class IDs
     *
     * Hanya siswa berstatus AKTIF (buku_induk.status = 1) — dipakai untuk
     * daftar peserta ujian / nomor peserta.
     */
    public function getSiswaByKelasArray(int $tp, int $smt, array $kelas_ids): array
    {
        if (empty($kelas_ids))
            return [];
        return $this->onlySiswaAktif($this->db->table('kelas_siswa a')
            ->select('a.id_siswa, b.nama, b.nis, b.nisn, b.username, c.nama_kelas, c.level_id as level')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('master_kelas c', 'a.id_kelas = c.id_kelas'), 'a')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->whereIn('a.id_kelas', $kelas_ids)
            ->orderBy('b.nama', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get all nomor peserta for specific students
     */
    public function getAllNomorPeserta(int $tp, int $smt, array $siswa_ids): array
    {
        if (empty($siswa_ids))
            return [];
        $result = $this->db->table('cbt_nomor_peserta')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->whereIn('id_siswa', $siswa_ids)
            ->get()
            ->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_siswa] = $row;
        }
        return $data;
    }

    /**
     * Check if nomor peserta exists by custom ID
     */
    public function cekNomorPeserta(array $ids_nomor): array
    {
        if (empty($ids_nomor))
            return [];
        $result = $this->db->table('cbt_nomor_peserta')
            ->whereIn('id_nomor', $ids_nomor)
            ->get()
            ->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_nomor] = $row;
        }
        return $data;
    }

    /**
     * Delete duplicate nomor peserta (cleanup)
     */
    public function hapus_duplikat_nomor_peserta(): int
    {
        $sql = "DELETE t1 FROM cbt_nomor_peserta t1
                INNER JOIN cbt_nomor_peserta t2 
                WHERE t1.id_nomor < t2.id_nomor 
                AND t1.id_siswa = t2.id_siswa 
                AND t1.id_tp = t2.id_tp 
                AND t1.id_smt = t2.id_smt";
        $this->db->query($sql);
        return $this->db->affectedRows();
    }

    /**
     * Get all jurusan for dropdown
     */
    public function getAllJurusan(): array
    {
        $result = $this->db->table('master_jurusan')->get()->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_jurusan] = $row->kode_jurusan;
        }
        return $ret;
    }

    /**
     * Get classes by level and allowed IDs
     */
    public function getKelasByLevel(int $level, array $arrKelas): array
    {
        if (empty($arrKelas))
            return [];
        return $this->db->table('master_kelas')
            ->select('id_kelas, kode_kelas')
            ->where('level_id', $level)
            ->whereIn('id_kelas', $arrKelas)
            ->get()
            ->getResult();
    }

    /**
     * Create dummy bank soal object
     */
    public function dummy(string $jenjang): array
    {
        return [
            'id_bank' => '',
            'bank_jenis_id' => '',
            'bank_kode' => '',
            'bank_mapel_id' => '',
            'bank_level' => '',
            'bank_kelas' => [],
            'bank_guru_id' => '',
            'jml_soal' => '0',
            'bobot_pg' => '0',
            'tampil_pg' => '0',
            'opsi' => $jenjang == '1' ? '3' : ($jenjang == '2' ? '4' : ($jenjang == '3' ? '5' : '')),
            'jml_kompleks' => '0',
            'tampil_kompleks' => '0',
            'bobot_kompleks' => '0',
            'jml_jodohkan' => '0',
            'tampil_jodohkan' => '0',
            'bobot_jodohkan' => '0',
            'jml_isian' => '0',
            'tampil_isian' => '0',
            'bobot_isian' => '0',
            'jml_esai' => '0',
            'bobot_esai' => '0',
            'tampil_esai' => '0',
            'tampil_urutan' => '0',
            'bobot_urutan' => '0',
            'tampil_cloze' => '0',
            'bobot_cloze' => '0',
            'kkm' => '',
            'soal_agama' => '-',
            'status' => '1'
        ];
    }

    /**
     * Save/Update bank soal
     */
    public function saveBankSoal(array $data, ?int $id = null)
    {
        if ($id) {
            return $this->db->table('cbt_bank_soal')->where('id_bank', $id)->update($data);
        }
        $this->db->table('cbt_bank_soal')->insert($data);
        return $this->db->insertID();
    }

    /**
     * Get distinct jenis from jadwal
     */
    public function getDistinctJenisJadwal($tp, $smt): array
    {
        return $this->db->table('cbt_jadwal')
            ->select('id_jenis')
            ->distinct()
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->get()
            ->getResult();
    }

    /**
     * Get all jenis ujian by array of IDs
     */
    public function getAllJenisUjianByArrJenis(array $arrJenis): array
    {
        if (empty($arrJenis))
            return [];
        $result = $this->db->table('cbt_jenis')
            ->whereIn('id_jenis', $arrJenis)
            ->get()
            ->getResult();

        $ret = ['' => 'Jenis Penilaian :'];
        foreach ($result as $row) {
            $ret[$row->id_jenis] = $row->kode_jenis;
        }
        return $ret;
    }

    /**
     * Get data jadwal by mapel
     */
    public function getDataJadwalByMapel($tp, $smt, $mapel = null): array
    {
        $builder = $this->db->table('cbt_bank_soal a');
        $builder->select('b.id_jadwal, b.jam_ke, b.tgl_mulai, b.tgl_selesai,'
            . ' a.bank_kode, a.bank_level, a.bank_kelas,'
            . ' a.id_bank, a.bank_guru_id, a.soal_agama, c.kode_jenis, c.nama_jenis,'
            . ' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
        if ($mapel != null)
            $builder->where('a.bank_mapel_id', $mapel);
        $builder->where('a.id_tp', $tp);
        $builder->where('a.id_smt', $smt);
        $builder->join('cbt_jadwal b', 'a.id_bank=b.id_bank', "left");
        $builder->join('cbt_jenis c', 'c.id_jenis=b.id_jenis', "left");
        $builder->join('master_mapel d', 'd.id_mapel=a.bank_mapel_id', "left");
        $builder->join('master_guru f', 'f.id_guru=a.bank_guru_id', "left");
        return $builder->get()->getResult();
    }

    /**
     * Get jadwal by ID (comprehensive)
     */
    /**
     * Helper to ensure all scores in a result object are numeric (no NaN)
     */
    public function sanitizeScoreObject($obj)
    {
        if (is_object($obj)) {
            foreach (get_object_vars($obj) as $key => $val) {
                if (is_object($val) || is_array($val)) {
                    $obj->$key = $this->sanitizeScoreObject($val);
                } elseif ($key === 'skor_total' || $key === 'nilai_asli' || $key === 'nilai_katrol' || str_contains($key, 'skor') || str_contains($key, 'nilai')) {
                    if (is_string($val) && (strtoupper($val) === 'NAN' || strtoupper($val) === 'INF')) {
                        $obj->$key = 0;
                    } elseif (is_float($val) && (is_nan($val) || is_infinite($val))) {
                        $obj->$key = 0;
                    } elseif ($val === null) {
                        $obj->$key = 0;
                    }
                }
            }
        } elseif (is_array($obj)) {
            foreach ($obj as $key => $val) {
                if (is_object($val) || is_array($val)) {
                    $obj[$key] = $this->sanitizeScoreObject($val);
                } elseif (str_contains($key, 'skor') || str_contains($key, 'nilai')) {
                    if (is_string($val) && (strtoupper($val) === 'NAN' || strtoupper($val) === 'INF')) {
                        $obj[$key] = 0;
                    } elseif (is_float($val) && (is_nan($val) || is_infinite($val))) {
                        $obj[$key] = 0;
                    } elseif ($val === null) {
                        $obj[$key] = 0;
                    }
                }
            }
        }
        return $obj;
    }

    public function getJadwalById($id_jadwal, $sesi = null): ?object
    {
        $builder = $this->db->table('cbt_jadwal a');
        $builder->select('a.*, b.opsi, b.bank_kode, b.bank_level, b.bank_kelas,'
            . ' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai,'
            . ' b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai,'
            . ' b.id_bank, b.bank_guru_id, b.soal_agama, c.kode_jenis, c.nama_jenis,'
            . ' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
        $builder->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        $builder->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $builder->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        if ($sesi != null) {
            $builder->join('cbt_sesi e', 'e.id_sesi=' . $sesi, "left");
        }
        $builder->join('master_guru f', 'f.id_guru=b.bank_guru_id', "left");
        $builder->where('a.id_jadwal', $id_jadwal);

        return $builder->get()->getRow();
    }

    /**
     * Get jadwal by tanggal
     */
    public function getJadwalByTanggal($tgl_mulai = null): array
    {
        $builder = $this->db->table('cbt_jadwal a');
        $builder->select('a.id_jadwal,a.tgl_mulai,a.tgl_selesai,a.jam_ke,b.soal_agama,b.bank_mapel_id,b.bank_kelas');
        $builder->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        if ($tgl_mulai != null) {
            if (is_array($tgl_mulai)) {
                $builder->whereIn('tgl_mulai', $tgl_mulai);
            } else {
                $builder->where('tgl_mulai', $tgl_mulai);
            }
        }
        return $builder->get()->getResult();
    }

    /**
     * Get jadwal by level (for student nilai page)
     */
    public function getJadwalByLevel(int $tp, int $smt, int $level): array
    {
        return $this->db->table('cbt_jadwal a')
            ->select('a.*, b.bank_kode, b.bank_kelas, b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, c.nama_jenis, d.kode as kode_mapel, d.nama_mapel')
            ->join('cbt_bank_soal b', 'a.id_bank=b.id_bank', 'left')
            ->join('cbt_jenis c', 'a.id_jenis=c.id_jenis', 'left')
            ->join('master_mapel d', 'b.bank_mapel_id=d.id_mapel', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->groupStart()
            ->where('b.bank_level', $level)
            ->orWhere('b.bank_level', 0) // Support All Levels
            ->groupEnd()
            ->orderBy('a.tgl_mulai', 'DESC')
            ->get()->getResult();
    }

    public function getKelas($tp, $smt)
    {
        return $this->db->table('master_kelas')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->orderBy('nama_kelas', 'ASC')
            ->get()->getResult();
    }

    /**
     * Get students and their exam details by class
     */
    public function getRuangSiswaByKelas(int $id_tp, int $id_smt, $kelas, $sesi = null): array
    {
        $builder = $this->db->table('cbt_sesi_siswa a');
        $builder->select('a.ruang_id, a.sesi_id, b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto,' .
            ' f.id_kelas, f.nama_kelas, f.kode_kelas,' .
            ' d.nama_ruang, d.kode_ruang,' .
            ' e.kode_sesi, e.nama_sesi, e.istirahat,' .
            ' g.nomor_peserta');

        $builder->where('a.tp_id', $id_tp);
        $builder->where('a.smt_id', $id_smt);
        $this->onlySiswaAktif($builder, 'a', 'bi', 'siswa_id');
        $builder->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $builder->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $builder->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp=' . $id_tp . ' AND g.id_smt=' . $id_smt, 'left');
        $builder->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');
        $builder->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp=' . $id_tp . ' AND c.id_smt=' . $id_smt, 'left');
        $builder->join('master_kelas f', 'f.id_kelas=c.id_kelas', 'left');

        if ($kelas != 'all') {
            if (is_array($kelas)) {
                $builder->whereIn('a.kelas_id', $kelas);
            } else {
                $builder->where('a.kelas_id', $kelas);
            }
        }

        if ($sesi != null && $sesi != 'null')
            $builder->where('a.sesi_id', $sesi);
        $builder->orderBy('b.nama');

        return $builder->get()->getResult();
    }

    /**
     * Get students and their exam details by ruang
     */
    public function getSiswaByRuang(int $id_tp, int $id_smt, int $id_ruang, $sesi, $level = null): array
    {
        $builder = $this->db->table('cbt_sesi_siswa a');
        $builder->select('a.ruang_id, a.sesi_id, b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto, b.agama,' .
            ' f.id_kelas, f.nama_kelas, f.kode_kelas,' .
            ' d.nama_ruang, d.kode_ruang,' .
            ' e.kode_sesi, e.nama_sesi,' .
            ' us.active,' .
            ' (SELECT COUNT(login) FROM login_attempts WHERE login_attempts.login = b.username) AS reset,' .
            ' g.nomor_peserta');

        $builder->where('a.tp_id', $id_tp);
        $builder->where('a.smt_id', $id_smt);
        $builder->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $builder->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $builder->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp=' . $id_tp . ' AND g.id_smt=' . $id_smt, 'left');
        $builder->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');
        $builder->join('users us', 'us.username=b.username', 'left');
        $builder->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp=' . $id_tp . ' AND c.id_smt=' . $id_smt);

        if ($level == null) {
            $builder->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        } else {
            $builder->join('master_kelas f', 'f.id_kelas=c.id_kelas AND f.level_id=' . $level);
        }
        $builder->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

        $builder->where('a.ruang_id', $id_ruang);
        if ($sesi !== null && $sesi !== 'null') {
            $builder->where('a.sesi_id', $sesi);
        }
        $builder->orderBy('b.nama');

        return $builder->get()->getResult();
    }

    /**
     * Get students by class for exam
     */
    public function getSiswaByKelas(int $id_tp, int $id_smt, $id_kelas): array
    {
        $builder = $this->db->table('kelas_siswa a');
        $builder->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.agama,' .
            ' b.foto, c.kelas_id, c.ruang_id, c.sesi_id, d.kode_ruang, e.kode_sesi, f.nama_kelas, f.kode_kelas, g.nomor_peserta');
        $builder->join('master_siswa b', 'b.id_siswa=a.id_siswa', 'left');
        $builder->join('cbt_sesi_siswa c', 'c.siswa_id=a.id_siswa AND a.id_tp = c.tp_id AND a.id_smt = c.smt_id', 'left');
        $builder->join('cbt_ruang d', 'd.id_ruang=c.ruang_id', 'left');
        $builder->join('cbt_sesi e', 'e.id_sesi=c.sesi_id', 'left');
        $builder->join('master_kelas f', 'f.id_kelas=a.id_kelas', 'left');
        $builder->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp=' . $id_tp . ' AND g.id_smt=' . $id_smt, 'left');
        $builder->join('buku_induk i', 'i.id_siswa=a.id_siswa AND i.status="1"');

        $builder->where('a.id_tp', $id_tp);
        $builder->where('a.id_smt', $id_smt);
        $builder->where('a.id_siswa is NOT NULL');
        $builder->where('b.id_siswa is NOT NULL');
        $builder->where('c.siswa_id is NOT NULL');
        $builder->where('g.id_siswa is NOT NULL');

        if (is_array($id_kelas)) {
            $builder->whereIn('a.id_kelas', $id_kelas);
        } else {
            $builder->where('a.id_kelas', $id_kelas);
        }
        $builder->orderBy('b.nama', 'ASC');

        return $builder->get()->getResult();
    }

    /**
     * Get students by ruang for exam
     */
    public function getSiswaByRuangId(int $id_tp, int $id_smt, int $id_ruang): array
    {
        $builder = $this->db->table('cbt_sesi_siswa a');
        $builder->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.agama,' .
            ' b.foto, a.ruang_id, a.sesi_id, d.kode_ruang, d.nama_ruang, e.kode_sesi, f.nama_kelas, f.kode_kelas, g.nomor_peserta');

        $builder->where('a.tp_id', $id_tp);
        $builder->where('a.smt_id', $id_smt);
        $builder->where('a.ruang_id', $id_ruang);

        $builder->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $builder->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $builder->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp=' . $id_tp . ' AND g.id_smt=' . $id_smt, 'left');
        $builder->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');
        $builder->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp=' . $id_tp . ' AND c.id_smt=' . $id_smt);
        $builder->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $builder->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

        $builder->orderBy('e.kode_sesi');
        $builder->orderBy('g.nomor_peserta');
        $builder->orderBy('b.nama');

        return $builder->get()->getResult();
    }

    /**
     * Get all peserta sorted by ruang
     */
    public function getAllPesertaByRuang(int $id_tp, int $id_smt): array
    {
        $builder = $this->db->table('cbt_sesi_siswa a');
        $builder->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto, f.level_id,' .
            ' f.nama_kelas, f.kode_kelas, f.id_kelas,' .
            ' d.nama_ruang, d.kode_ruang, d.id_ruang,' .
            ' e.kode_sesi, e.nama_sesi, a.sesi_id as id_sesi,' .
            ' g.nomor_peserta');

        $builder->where('a.tp_id', $id_tp);
        $builder->where('a.smt_id', $id_smt);
        $builder->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $builder->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $builder->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp=' . $id_tp . ' AND g.id_smt=' . $id_smt, 'left');
        $builder->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');
        $builder->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp=' . $id_tp . ' AND c.id_smt=' . $id_smt);
        $builder->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $builder->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

        $builder->orderBy('d.kode_ruang');
        $builder->orderBy('e.kode_sesi');
        $builder->orderBy('f.level_id');
        $builder->orderBy('f.kode_kelas');
        $builder->orderBy('b.nama');

        $result = $builder->get()->getResult();
        if (count($result) > 0) {
            usort($result, function ($a, $b) {
                return strnatcmp($a->nomor_peserta ?? '', $b->nomor_peserta ?? '');
            });
        }
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_ruang][$row->kode_sesi][] = $row;
        }
        if (count($ret) > 0) {
            ksort($ret);
            foreach ($ret as $items) {
                ksort($items);
            }
        }
        return $ret;
    }

    /**
     * Get all peserta sorted by kelas
     */
    public function getAllPesertaByKelas(int $id_tp, int $id_smt): array
    {
        $builder = $this->db->table('cbt_sesi_siswa a');
        $builder->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto,' .
            ' f.nama_kelas, f.kode_kelas,' .
            ' d.nama_ruang, d.kode_ruang,' .
            ' e.kode_sesi, e.nama_sesi,' .
            ' g.nomor_peserta');

        $builder->where('a.tp_id', $id_tp);
        $builder->where('a.smt_id', $id_smt);
        $builder->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $builder->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $builder->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp=' . $id_tp . ' AND g.id_smt=' . $id_smt, 'left');
        $builder->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');
        $builder->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp=' . $id_tp . ' AND c.id_smt=' . $id_smt);
        $builder->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $builder->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

        $builder->orderBy('f.level_id');
        $builder->orderBy('f.kode_kelas');
        $builder->orderBy('b.nama');

        $result = $builder->get()->getResult();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_kelas][] = $row;
        }
        return $ret;
    }

    // ==========================================
    // REPORT DATA FUNCTIONS
    // ==========================================

    public function getSettingKopAbsensi(): ?object
    {
        return $this->db->table('cbt_kop_absensi a')
            ->select('a.*, b.logo_kanan, b.logo_kiri, b.kepsek, b.tanda_tangan')
            ->join('setting b', 'b.id_setting=1', 'left')
            ->get()->getRow();
    }

    public function getSettingKopBeritaAcara(): ?object
    {
        return $this->db->table('cbt_kop_berita a')
            ->select('a.*, d.logo_kanan, d.logo_kiri, d.kepsek, d.nip, d.tanda_tangan, d.sekolah, d.proktor, d.nip_proktor, d.ttdproktor, d.kopsekolah, d.alamat, d.kota')
            ->join('setting d', 'd.id_setting=1', 'left')
            ->get()->getRow();
    }

    public function getSettingKartu(): ?object
    {
        return $this->db->table('cbt_kop_kartu')->get()->getRow();
    }

    public function getBeritaAcara($id_jadwal, $id_ruang, $id_sesi): ?object
    {
        return $this->db->table('cbt_berita_acara')
            ->where('id_jadwal', $id_jadwal)
            ->where('id_ruang', $id_ruang)
            ->where('id_sesi', $id_sesi)
            ->get()->getRow();
    }

    public function getPaktaIntegritas($id_jadwal, $id_ruang, $id_sesi): ?object
    {
        $builder = $this->db->table('cbt_pakta_integritas');
        $builder->where('id_jadwal', $id_jadwal);
        if ($id_ruang != null)
            $builder->where('id_ruang', $id_ruang);
        if ($id_sesi != null)
            $builder->where('id_sesi', $id_sesi);
        return $builder->get()->getRow();
    }

    public function saveBeritaAcara(array $data)
    {
        $allowed = ['id_tp', 'id_smt', 'id_jadwal', 'id_ruang', 'id_sesi', 'header_1', 'header_2', 'header_3', 'header_4', 'nama_ujian', 'mapel', 'waktu_mulai', 'waktu_akhir', 'jml_peserta', 'jml_hadir', 'jml_tidak_hadir', 'no_tidak_hadir', 'catatan', 'pengawas_1', 'nip_1', 'pengawas_2', 'nip_2', 'kepsek', 'nip_kepsek', 'tgl_ba'];
        $insertData = [];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $insertData[$field] = $data[$field];
            }
        }
        $insertData['updated_at'] = date('Y-m-d H:i:s');

        $existing = $this->db->table('cbt_berita_acara')
            ->where('id_jadwal', $data['id_jadwal'])
            ->where('id_ruang', $data['id_ruang'])
            ->where('id_sesi', $data['id_sesi'])
            ->get()->getRow();

        if ($existing) {
            return $this->db->table('cbt_berita_acara')->where('id_ba', $existing->id_ba)->update($insertData);
        } else {
            return $this->db->table('cbt_berita_acara')->insert($insertData);
        }
    }

    public function savePaktaIntegritas(array $data)
    {
        $allowed = ['id_tp', 'id_smt', 'id_jadwal', 'id_ruang', 'id_sesi', 'nama', 'nip', 'jabatan', 'sekolah', 'tgl_pi'];
        $insertData = [];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $insertData[$field] = $data[$field];
            }
        }
        $insertData['updated_at'] = date('Y-m-d H:i:s');

        $existing = $this->db->table('cbt_pakta_integritas')
            ->where('id_jadwal', $data['id_jadwal'])
            ->where('id_ruang', $data['id_ruang'])
            ->where('id_sesi', $data['id_sesi'])
            ->get()->getRow();

        if ($existing) {
            return $this->db->table('cbt_pakta_integritas')->where('id_pi', $existing->id_pi)->update($insertData);
        } else {
            return $this->db->table('cbt_pakta_integritas')->insert($insertData);
        }
    }

    public function getAllBeritaAcara(int $tp, int $smt): array
    {
        return $this->db->table('cbt_berita_acara a')
            ->select('a.*, b.nama_ruang, c.nama_sesi, d.tgl_mulai, e.bank_kode, e.bank_level, f.nama_mapel')
            ->join('cbt_ruang b', 'b.id_ruang=a.id_ruang', 'left')
            ->join('cbt_sesi c', 'c.id_sesi=a.id_sesi', 'left')
            ->join('cbt_jadwal d', 'd.id_jadwal=a.id_jadwal', 'left')
            ->join('cbt_bank_soal e', 'e.id_bank=d.id_bank', 'left')
            ->join('master_mapel f', 'f.id_mapel=e.bank_mapel_id', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('a.updated_at', 'DESC')
            ->get()->getResult();
    }

    public function getAllPaktaIntegritas(int $tp, int $smt): array
    {
        return $this->db->table('cbt_pakta_integritas a')
            ->select('a.*, b.nama_ruang, c.nama_sesi, d.tgl_mulai, e.bank_kode, e.bank_level, f.nama_mapel')
            ->join('cbt_ruang b', 'b.id_ruang=a.id_ruang', 'left')
            ->join('cbt_sesi c', 'c.id_sesi=a.id_sesi', 'left')
            ->join('cbt_jadwal d', 'd.id_jadwal=a.id_jadwal', 'left')
            ->join('cbt_bank_soal e', 'e.id_bank=d.id_bank', 'left')
            ->join('master_mapel f', 'f.id_mapel=e.bank_mapel_id', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('a.updated_at', 'DESC')
            ->get()->getResult();
    }



    public function getKelasUjian($tp, $smt, $kelas)
    {
        $builder = $this->db->table('cbt_sesi_siswa b')
            ->select('b.siswa_id, b.kelas_id, b.ruang_id, b.sesi_id');
        $this->onlySiswaAktif($builder, 'b', 'bi', 'siswa_id');
        $result = $builder
            ->where('b.tp_id', $tp)
            ->where('b.smt_id', $smt)
            ->where('b.kelas_id', $kelas)
            ->get()->getResult();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kelas_id][$row->ruang_id][$row->sesi_id][] = $row->siswa_id;
        }
        return $ret;
    }


    public function getAllBankSoalByMapel($tp, $smt, $mapel, $guru = null)
    {
        $builder = $this->db->table('cbt_bank_soal b');
        $builder->select('b.*, m.nama_mapel, m.kode, g.nama_guru, j.nama_jenis, j.kode_jenis');
        $builder->join('master_mapel m', 'm.id_mapel = b.bank_mapel_id');
        $builder->join('master_guru g', 'g.id_guru = b.bank_guru_id');
        // $builder->join('master_level l', 'l.id_level = b.bank_level', 'left'); // Not always used
        $builder->join('cbt_jenis j', 'j.id_jenis = b.bank_jenis_id', 'left');

        $builder->where('b.id_tp', $tp);
        $builder->where('b.id_smt', $smt);
        $builder->where('b.bank_mapel_id', $mapel);
        if ($guru != null) {
            $builder->where('b.bank_guru_id', $guru);
        }
        // Kita HAPUS $builder->where('b.status', '1'); agar bank soal yang statusnya '0' (BELUM AKTIF) 
        // tetap muncul di dropdown dengan label badge "BELUM AKTIF", sehingga pengguna bisa melihat list lengkapnya.

        $result = $builder->get()->getResult();

        $ret = [];
        foreach ($result as $row) {
            $jadwal = $this->db->table('cbt_jadwal')->select('id_jadwal')->where('id_bank', $row->id_bank)->get()->getRow();
            $row->id_jadwal = $jadwal ? $jadwal->id_jadwal : null;
            $ret[$row->id_bank] = $row;
        }
        return $ret;
    }

    /**
     * Pastikan kolom remedial ada di tabel cbt_jadwal (self-migrating).
     * - enable_remedial : aktif/nonaktif fitur remedial
     * - max_remedial     : batas maksimal percobaan remedial (0 = unlimited)
     */
    private function ensureRemedialColumns(): void
    {
        $forge = \Config\Database::forge();

        if (!$this->db->fieldExists('enable_remedial', 'cbt_jadwal')) {
            $forge->addColumn('cbt_jadwal', [
                'enable_remedial' => ['type' => 'VARCHAR', 'constraint' => 1, 'default' => '1', 'null' => true]
            ]);
        }

        if (!$this->db->fieldExists('max_remedial', 'cbt_jadwal')) {
            $forge->addColumn('cbt_jadwal', [
                'max_remedial' => ['type' => 'INT', 'constraint' => 11, 'default' => 2, 'null' => false]
            ]);
        }
    }

    public function saveJadwalUjian($tp, $smt)
    {
        $request = service('request');
        $this->ensureRemedialColumns();

        $tokenEnabled = $request->getPost('token') ? '1' : '0';
        $tokenValue = $request->getPost('token_value');

        $data = [
            'id_tp' => $tp,
            'id_smt' => $smt,
            'id_bank' => $request->getPost('bank_id'),
            'id_jenis' => $request->getPost('jenis_id'),
            'tgl_mulai' => $request->getPost('tgl_mulai'),
            'tgl_selesai' => $request->getPost('tgl_selesai'),
            'durasi_ujian' => $request->getPost('durasi_ujian'),
            'acak_soal' => $request->getPost('acak_soal') ? '1' : '0',
            'acak_opsi' => $request->getPost('acak_opsi') ? '1' : '0',
            'hasil_tampil' => $request->getPost('hasil_tampil') ? '1' : '0',
            'token' => $tokenEnabled,
            'status' => $request->getPost('status') ? '1' : '0',
            'reset_login' => $request->getPost('reset_login') ? '1' : '0',
            'ulang' => $request->getPost('ulang') ? '1' : '0',
            'rekap' => '0',
            'jarak' => $request->getPost('jarak'),
            'pelanggaran' => $request->getPost('pelanggaran') ? '1' : '0',
            'enable_remedial' => $request->getPost('enable_remedial') ? '1' : '0',
            'max_remedial' => $this->getMaxRemedialInput($request)
        ];

        $id_jadwal = $request->getPost('id_jadwal');
        if (empty($id_jadwal)) {
            // INSERT baru: jam_ke mulai dari 0, token di-generate jika diperlukan
            $data['jam_ke'] = '0';
            if ($tokenEnabled === '1') {
                $data['token_value'] = $tokenValue ?: $this->generateToken();
            }
            $this->db->table('cbt_jadwal')->insert($data);
            $insert_id = $this->db->insertID();
            return ['success' => true, 'insert_id' => $insert_id, 'token_value' => $data['token_value'] ?? null];
        } else {
            // UPDATE: jam_ke TIDAK di-overwrite agar alokasi dari Cbtalokasi tetap terjaga
            if ($tokenEnabled === '1') {
                $data['token_value'] = $tokenValue ?: $this->generateToken();
            } else {
                $data['token_value'] = null;
            }
            $this->db->table('cbt_jadwal')->where('id_jadwal', $id_jadwal)->update($data);

            // Invalidate jadwal cache agar perubahan (termasuk token baru) langsung efektif
            $cache = \Config\Services::cache();
            $cache->delete("cbt_jadwal_complete_{$id_jadwal}");

            return ['success' => true, 'insert_id' => $id_jadwal, 'token_value' => $data['token_value']];
        }
    }

    /**
     * Generate token ujian (6 karakter huruf kapital + angka).
     * Panjang token disesuaikan dengan input token siswa.
     */
    private function generateToken(): string
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 6; $i++) {
            $token .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $token;
    }

    /**
     * Baca input max_remedial dari request.
     * Default 2 jika kosong, 0 = unlimited, negatif dinormalisasi ke 0.
     */
    private function getMaxRemedialInput($request): int
    {
        $val = $request->getPost('max_remedial');
        if ($val === null || $val === '') {
            return 2;
        }
        $val = (int) $val;
        return $val < 0 ? 0 : $val;
    }

    public function getAllDataJadwal($tp, $smt, $guru = null, $mapel = null, $level = null)
    {
        $builder = $this->db->table('cbt_jadwal a');
        $builder->select('a.*, b.bank_kode, b.bank_kelas, b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, ' .
            'b.bank_level, b.bank_mapel_id, b.kkm, ' .
            'c.nama_jenis, c.kode_jenis, d.kode as kode_mapel, d.nama_mapel, d.id_mapel, ' .
            'e.nama_guru, e.id_guru');
        $builder->join('cbt_bank_soal b', 'a.id_bank=b.id_bank', 'left');
        $builder->join('cbt_jenis c', 'a.id_jenis=c.id_jenis', 'left');
        $builder->join('master_mapel d', 'b.bank_mapel_id=d.id_mapel', 'left');
        $builder->join('master_guru e', 'b.bank_guru_id=e.id_guru', 'left');

        $builder->where('a.id_tp', $tp);
        $builder->where('a.id_smt', $smt);

        if ($guru)
            $builder->where('b.bank_guru_id', $guru);
        if ($mapel)
            $builder->where('b.bank_mapel_id', $mapel);
        if ($level)
            $builder->where('b.bank_level', $level);

        $builder->orderBy('a.tgl_mulai', 'ASC');
        $builder->orderBy('a.id_jadwal', 'ASC');

        $result = $builder->get()->getResult();

        $jadwals = [];
        foreach ($result as $row) {
            $row->bank_kelas = $this->maybeUnserialize($row->bank_kelas ?? '[]');
            
            $kode_jenis = $row->kode_jenis ?? 'UNKNOWN';
            $tgl_display = $row->tgl_mulai ? date('d-m-Y', strtotime($row->tgl_mulai)) : 'N/A';
            $title = $kode_jenis . ' (' . $tgl_display . ')';
            
            $bank_level = $row->bank_level ?? 'Unset';
            $lvl = "Level " . $bank_level;
            
            $jadwals[$title][$lvl][] = $row;
        }
        return $jadwals;
    }

    public function replaceKopKartu(array $data)
    {
        return $this->db->table('cbt_kop_kartu')->replace($data);
    }

    public function replaceKopAbsensi(array $data)
    {
        return $this->db->table('cbt_kop_absensi')->replace($data);
    }

    public function replaceKopBerita(array $data)
    {
        return $this->db->table('cbt_kop_berita')->replace($data);
    }

    public function getPengawas($id_pengawas)
    {
        return $this->db->table('cbt_pengawas')->where('id_pengawas', $id_pengawas)->get()->getRow();
    }

    public function getJadwalByArrId($arrId)
    {
        if (empty($arrId))
            return [];
        return $this->db->table('cbt_jadwal')->whereIn('id_jadwal', $arrId)->get()->getResult();
    }


    /**
     * Cek Soal Ada (summary)
     */
    public function cekSoalAda($id_bank, $jenis)
    {
        return $this->db->table('cbt_soal')
            ->select('id_soal, bank_id, jenis, nomor_soal')
            ->where('bank_id', $id_bank)
            ->where('jenis', $jenis)
            ->get()
            ->getResult(); // Returns array of objects
    }

    /**
     * Cek Soal Belum Komplit
     */
    public function cekSoalBelumKomplit($jenis, $opsi_ganda, $id_bank = null)
    {
        $builder = $this->db->table('cbt_soal');
        $builder->select('id_soal, bank_id, jenis, nomor_soal'); // removed mapel_id as it might not be in cbt_soal
        $builder->where('jenis', $jenis);

        if ($id_bank !== null) {
            $builder->where('bank_id', $id_bank);
        }

        $builder->groupStart()
            ->where('soal IS NULL')
            ->orWhere('soal', '')
            ->groupEnd();

        if ($jenis == "1") {
            $builder->groupStart()
                ->where('opsi_a IS NULL')->orWhere('opsi_a', '')
                ->orWhere('opsi_b IS NULL')->orWhere('opsi_b', '')
                ->orWhere('opsi_c IS NULL')->orWhere('opsi_c', '')
                ->groupEnd();

            if ($opsi_ganda == '4') {
                $builder->groupStart()
                    ->where('opsi_d IS NULL')->orWhere('opsi_d', '')
                    ->groupEnd();
            }
            if ($opsi_ganda == '5') {
                $builder->groupStart()
                    ->where('opsi_d IS NULL')->orWhere('opsi_d', '')
                    ->orWhere('opsi_e IS NULL')->orWhere('opsi_e', '')
                    ->groupEnd();
            }
        }

        if ($jenis == "2") {
            $builder->groupStart()
                ->where('opsi_a IS NULL')->orWhere('opsi_a', '')
                ->groupEnd();
        }

        $builder->groupStart()
            ->where('jawaban IS NULL')->orWhere('jawaban', '')
            ->groupEnd();

        return $builder->get()->getResult();
    }

    /**
     * Get Nomor Soal Terbesar
     */
    public function getNomorSoalTerbesar($id_bank, $jenis)
    {
        return $this->db->table('cbt_soal')
            ->select('nomor_soal')
            ->where('bank_id', $id_bank)
            ->where('jenis', $jenis)
            ->orderBy('nomor_soal', 'DESC')
            ->get()
            ->getRow();
    }

    /**
     * Get Soal By Nomor (Single)
     */
    public function getSoalByNomor($bank_id, $nomor, $jenis)
    {
        return $this->db->table('cbt_soal a')
            ->select('a.*, b.id_item as kartu_item_id, c.judul as kartu_judul, COALESCE(b.id_tujuan, a.tujuan_id) as tujuan_id, b.materi as topik, b.indikator_soal as indikator, IFNULL(b.level_kognitif, a.kesulitan) as kesulitan, d.tujuan as tujuan_text, d.kode as tujuan_kode')
            ->join('cbt_kartu_soal_item b', 'a.id_soal = b.soal_id', 'left')
            ->join('cbt_kartu_soal c', 'b.kartu_id = c.id_kartu', 'left')
            ->join('e_tujuan_pembelajaran d', 'COALESCE(b.id_tujuan, a.tujuan_id) = d.id_tujuan', 'left')
            ->where('a.bank_id', $bank_id)
            ->where('a.nomor_soal', $nomor)
            ->where('a.jenis', $jenis)
            ->get()
            ->getRow();
    }



    public function countSiswaTerpakai($idBank)
    {
        return $this->db->table('cbt_siswa')
            ->where('id_bank', $idBank)
            ->countAllResults();
    }

    // ==========================================
    // SCORE PROCESSING FUNCTIONS
    // ==========================================

    public function maybeUnserialize($data)
    {
        if (is_array($data))
            return $data;
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

    public function olahNilai($type_query, $id_tp, $id_smt, $query, $id_jadwal, $koreksi = false)
    {
        // Saat mode ringkasan (bukan koreksi), soal tidak diperlukan -> hindari
        // fetch + unserialize puluhan soal per kelas load (hemat memory & CPU).
        $buildSoal = $koreksi === true;

        // 1. Get Jadwal with Bank Soal
        $jadwal = $this->db->table('cbt_jadwal a')
            ->select('a.*, b.bank_kode, b.bank_kelas, b.tampil_pg, b.bobot_pg, b.soal_agama,
                b.tampil_kompleks, b.bobot_kompleks, 
                b.tampil_jodohkan, b.bobot_jodohkan,
                b.tampil_isian, b.bobot_isian,
                b.tampil_esai, b.bobot_esai,
                b.tampil_urutan, b.bobot_urutan,
                b.tampil_cloze, b.bobot_cloze,
                c.nama_jenis, d.nama_mapel, d.kode as kode_mapel, e.nama_guru')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('cbt_jenis c', 'a.id_jenis = c.id_jenis', 'left')
            ->join('master_mapel d', 'b.bank_mapel_id = d.id_mapel', 'left')
            ->join('master_guru e', 'b.bank_guru_id = e.id_guru', 'left')
            ->where('a.id_jadwal', $id_jadwal)
            ->get()->getRow();

        if (!$jadwal)
            return null;

        $soals = [];
        if ($buildSoal) {
            // 2. Get Soal (hanya saat mode koreksi)
            $soals = $this->db->table('cbt_soal')->where('bank_id', $jadwal->id_bank)
                ->orderBy('nomor_soal', 'ASC')->get()->getResult();

            foreach ($soals as $s) {
                $s->opsi_a = $this->maybeUnserialize($s->opsi_a ?? '');
                $s->jawaban = $this->maybeUnserialize($s->jawaban ?? '');
            }
        }

        $bank_kelas = $this->maybeUnserialize($jadwal->bank_kelas ?? '[]');
        // Ensure bank_kelas is simple array of IDs for easier handling if it was objects
        $arrBankKelas = [];
        if (is_array($bank_kelas)) {
            foreach ($bank_kelas as $bk) {
                $arrBankKelas[] = is_object($bk) ? ($bk->kelas_id ?? '') : (is_array($bk) ? ($bk['kelas_id'] ?? '') : $bk);
            }
        }

        $jadwal->bank = (object) [
            'soal' => $soals, // kosong saat ringkasan (hemat memory)
            'bank_kelas' => $arrBankKelas,
            'bank_kode' => $jadwal->bank_kode,
            'nama_mapel' => $jadwal->nama_mapel,
            'nama_guru' => $jadwal->nama_guru,
            'soal_agama' => $jadwal->soal_agama,
            'tampil_pg' => $jadwal->tampil_pg ?? 0,
            'tampil_kompleks' => $jadwal->tampil_kompleks ?? 0,
            'tampil_jodohkan' => $jadwal->tampil_jodohkan ?? 0,
            'tampil_isian' => $jadwal->tampil_isian ?? 0,
            'tampil_esai' => $jadwal->tampil_esai ?? 0,
            'tampil_urutan' => $jadwal->tampil_urutan ?? 0,
            'tampil_cloze' => $jadwal->tampil_cloze ?? 0,
            'bobot_pg' => $jadwal->bobot_pg ?? 0,
            'bobot_kompleks' => $jadwal->bobot_kompleks ?? 0,
            'bobot_jodohkan' => $jadwal->bobot_jodohkan ?? 0,
            'bobot_isian' => $jadwal->bobot_isian ?? 0,
            'bobot_esai' => $jadwal->bobot_esai ?? 0,
            'bobot_urutan' => $jadwal->bobot_urutan ?? 0,
            'bobot_cloze' => $jadwal->bobot_cloze ?? 0,
        ];

        // 3. Get Sesi Siswa
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1) — siswa LULUS /
        // PINDAH / KELUAR tidak boleh masuk daftar nilai maupun rekap.
        $ssBuilder = $this->db->table('cbt_sesi_siswa a')
            ->select('a.siswa_id, a.kelas_id, a.ruang_id, a.sesi_id,
                b.nama, b.nis, b.agama, c.nomor_peserta,
                d.nama_kelas, e.nama_ruang, e.kode_ruang, f.nama_sesi, f.kode_sesi')
            ->join('master_siswa b', 'a.siswa_id = b.id_siswa')
            ->join('cbt_nomor_peserta c', 'a.siswa_id = c.id_siswa AND c.id_tp=' . $id_tp . ' AND c.id_smt=' . $id_smt, 'left')
            ->join('master_kelas d', 'a.kelas_id = d.id_kelas', 'left')
            ->join('cbt_ruang e', 'a.ruang_id = e.id_ruang', 'left')
            ->join('cbt_sesi f', 'a.sesi_id = f.id_sesi', 'left')
            ->where('a.tp_id', $id_tp)
            ->where('a.smt_id', $id_smt);
        $this->onlySiswaAktif($ssBuilder, 'a', 'bi', 'siswa_id');

        if ($type_query == 1) { // Class
            $ssBuilder->where('a.kelas_id', $query);
        } elseif ($type_query == 2) { // Siswa
            $ssBuilder->where('a.siswa_id', $query);
        }

        $sesiSiswa = $ssBuilder->get()->getResult();

        $jadwal->sesisiswa = [];
        $ids_siswa = [];
        foreach ($sesiSiswa as $ss) {
            // Basic object structure expected by views
            $item = new \stdClass();
            $item->siswa_id = $ss->siswa_id;
            $item->kelas_id = $ss->kelas_id;
            $item->ruang_id = $ss->ruang_id;
            $item->sesi_id = $ss->sesi_id;
            $item->siswa = (object) ['id_siswa' => $ss->siswa_id, 'nama' => $ss->nama, 'nis' => $ss->nis, 'agama' => $ss->agama];
            $item->peserta = (object) ['nomor_peserta' => $ss->nomor_peserta];
            $item->kelas = (object) ['nama_kelas' => $ss->nama_kelas];
            $item->ruang = (object) ['nama_ruang' => $ss->nama_ruang, 'kode_ruang' => $ss->kode_ruang ?? '-'];
            $item->sesi = (object) ['nama_sesi' => $ss->nama_sesi, 'kode_sesi' => $ss->kode_sesi ?? '-'];

            // Also create ruang_sesi nested structure for View compatibility
            $item->ruang_sesi = (object) [
                'ruang' => $item->ruang,
                'sesi' => $item->sesi
            ];

            // Agama Check
            if ($jadwal->soal_agama && $jadwal->soal_agama != '-' && $jadwal->soal_agama != '0' && $jadwal->soal_agama != $ss->agama) {
                continue;
            }

            $ids_siswa[] = $ss->siswa_id;
            $jadwal->sesisiswa[] = $item;
        }

        if (empty($ids_siswa))
            return $jadwal;

        // 4. Get CBT Siswa (Answers)
        $cbtSiswa = $this->db->table('cbt_siswa')
            ->where('id_jadwal', $id_jadwal)
            ->whereIn('id_siswa', $ids_siswa)
            ->get()->getResult();

        $mapCbt = [];
        foreach ($cbtSiswa as $c)
            $mapCbt[$c->id_siswa] = $c;

        // Fallback: If cbt_siswa is empty or some students missing, try cbt_rekap_siswa
        if (count($mapCbt) < count($ids_siswa)) {
            $rekapSiswa = $this->db->table('cbt_rekap_siswa')
                ->where('id_jadwal', $id_jadwal)
                ->whereIn('id_siswa', $ids_siswa)
                ->get()->getResult();
            
            foreach ($rekapSiswa as $r) {
                // Only add if not already in mapCbt from cbt_siswa
                if (!isset($mapCbt[$r->id_siswa])) {
                    $mapCbt[$r->id_siswa] = $r;
                }
            }
        }

        // --- OPTIMIZATION: Bulk fetch values for all students ---
        // 4a. Get Scores (cbt_nilai) in one query
        $allNilai = $this->db->table('cbt_nilai')
            ->where('id_jadwal', $id_jadwal)
            ->whereIn('id_siswa', $ids_siswa)
            ->get()->getResult();
        $mapNilai = [];
        foreach ($allNilai as $n) $mapNilai[$n->id_siswa] = $n;

        // 4b. Get ALL PG Answers for these students in one query
        $allJawabanPg = $this->db->table('cbt_jawaban j')
            ->select('j.id_siswa, j.id_soal, j.jawaban as jawaban_siswa, s.jawaban as kunci, s.nomor_soal')
            ->join('cbt_soal s', 'j.id_soal = s.id_soal')
            ->where('j.id_jadwal', $id_jadwal)
            ->whereIn('j.id_siswa', $ids_siswa)
            ->where('s.jenis', '1') // PG only for report overview
            ->orderBy('s.nomor_soal', 'ASC')
            ->get()->getResult();
        
        $mapJawabanPg = [];
        foreach ($allJawabanPg as $aj) {
            $mapJawabanPg[$aj->id_siswa][] = (object) [
                'nomor' => $aj->nomor_soal,
                'jawaban_siswa' => $aj->jawaban_siswa,
                'kunci' => $aj->kunci,
                'benar' => (strtoupper(trim($aj->jawaban_siswa ?? '')) === strtoupper(trim($aj->kunci ?? '')))
            ];
        }
        // --- END OPTIMIZATION ---

        // 5. Merge Data
        foreach ($jadwal->sesisiswa as $ss) {
            $ss->cbtsiswa = $mapCbt[$ss->siswa_id] ?? null;
            if (!$ss->cbtsiswa)
                $ss->cbtsiswa = (object) [];

            if (isset($ss->cbtsiswa->jawaban))
                $ss->cbtsiswa->jawaban = $this->maybeUnserialize($ss->cbtsiswa->jawaban);
            if (isset($ss->cbtsiswa->nilai))
                $ss->cbtsiswa->nilai = $this->maybeUnserialize($ss->cbtsiswa->nilai);
            if (isset($ss->cbtsiswa->nilai_input))
                $ss->cbtsiswa->nilai_input = $this->maybeUnserialize($ss->cbtsiswa->nilai_input);

            // Build nilai_akhir structure for View compatibility
            $nilaiCbt = $ss->cbtsiswa->nilai ?? (object) [];
            $nilaiInput = $ss->cbtsiswa->nilai_input ?? (object) [];

            // Optimized: Use memory maps instead of per-student queries
            $nilaiRow = $mapNilai[$ss->siswa_id] ?? null;
            $jawabanPg = $mapJawabanPg[$ss->siswa_id] ?? [];

            $extractSum = function ($inputVal, $fallbackVal) {
                if ($inputVal !== null) {
                    if (is_array($inputVal) || is_object($inputVal)) {
                        $arr = json_decode(json_encode($inputVal), true);
                        return floatval(array_sum(array_column($arr, 'koreksi')));
                    }
                    return floatval($inputVal);
                }
                return floatval($fallbackVal);
            };

            $skorPgTotal = $extractSum($nilaiInput->pg_nilai ?? null, $nilaiRow->pg_skor ?? $nilaiCbt->pg ?? 0);
            $skorKompleks = $extractSum($nilaiInput->kompleks_nilai ?? null, $nilaiRow->kompleks_skor ?? $nilaiCbt->kompleks ?? 0);
            $skorJodohkan = $extractSum($nilaiInput->jodohkan_nilai ?? null, $nilaiRow->jodohkan_skor ?? $nilaiCbt->jodohkan ?? 0);
            $skorIsian = $extractSum($nilaiInput->isian_nilai ?? null, $nilaiRow->isian_skor ?? $nilaiCbt->isian ?? 0);
            $skorEsai = $extractSum($nilaiInput->essai_nilai ?? null, $nilaiCbt->esai ?? 0);
            $skorUrutan = $extractSum($nilaiInput->urutan_nilai ?? null, $nilaiRow->urutan_skor ?? $nilaiCbt->urutan ?? 0);
            $skorCloze = $extractSum($nilaiInput->cloze_nilai ?? null, $nilaiRow->cloze_skor ?? $nilaiCbt->cloze ?? 0);

            // Calculate total score (Manual input takes precedence)
            $skorTotal = $skorPgTotal + $skorKompleks + $skorJodohkan + 
                         $skorIsian + $skorEsai + $skorUrutan + $skorCloze;

            $ss->nilai_akhir = (object) [
                'skor_pg' => (object) [
                    'skor_total' => $skorPgTotal,
                    'skor_asli' => floatval($nilaiRow->pg_nilai ?? $nilaiCbt->pg ?? 0),
                    'detail' => $jawabanPg
                ],
                'skor_kompleks' => (object) [
                    'skor_total' => $skorKompleks,
                    'skor_asli' => floatval($nilaiRow->kompleks_skor ?? $nilaiCbt->kompleks ?? 0)
                ],
                'skor_jodohkan' => (object) [
                    'skor_total' => $skorJodohkan,
                    'skor_asli' => floatval($nilaiRow->jodohkan_skor ?? $nilaiCbt->jodohkan ?? 0)
                ],
                'skor_isian' => (object) [
                    'skor_total' => $skorIsian,
                    'skor_asli' => floatval($nilaiRow->isian_skor ?? $nilaiCbt->isian ?? 0)
                ],
                'skor_essai' => (object) [
                    'skor_total' => $skorEsai,
                    'skor_asli' => floatval($nilaiCbt->esai ?? 0)
                ],
                'skor_urutan' => (object) [
                    'skor_total' => $skorUrutan,
                    'skor_asli' => floatval($nilaiRow->urutan_skor ?? $nilaiCbt->urutan ?? 0)
                ],
                'skor_cloze' => (object) [
                    'skor_total' => $skorCloze,
                    'skor_asli' => floatval($nilaiRow->cloze_skor ?? $nilaiCbt->cloze ?? 0)
                ],
                'skor_total' => $skorTotal,
                'nilai_katrol' => $nilaiRow->nilai_katrol ?? 0,
                'dikoreksi' => $nilaiInput->dikoreksi ?? 0
            ];
            $ss->nilai_akhir = $this->sanitizeScoreObject($ss->nilai_akhir);

            // Build durasi structure for View compatibility
            $mulaiRaw = $ss->cbtsiswa->mulai ?? null;
            $selesaiRaw = $ss->cbtsiswa->selesai ?? null;
            $mulaiTime = $mulaiRaw ? date('H:i', strtotime($mulaiRaw)) : null;

            // Compute the elapsed exam duration
            $lamaUjian = null;
            if ($mulaiRaw && $selesaiRaw) {
                $diff = max(0, strtotime($selesaiRaw) - strtotime($mulaiRaw));
                $mins = floor($diff / 60);
                $secs = $diff % 60;
                $lamaUjian = sprintf('%d:%02d', $mins, $secs);
            }

            $ss->durasi = (object) [
                'mulai' => $mulaiRaw,
                'mulai_ujian' => $mulaiTime, // Time only for View compatibility
                'selesai' => $selesaiRaw,
                'lama_ujian' => $lamaUjian,
                'status_ujian' => $this->getStatusUjianLabel($ss->cbtsiswa->status ?? '0', $mulaiRaw, $selesaiRaw)
            ];

            // Also expose no_peserta for easy access
            $ss->no_peserta = $ss->peserta->nomor_peserta ?? '-';
        }

        if ($koreksi) {
            // Filter Soal based on tampilkan
            $jadwal->soal_pg = array_values(array_filter($soals, function ($s) {
                return $s->jenis == '1' && $s->tampilkan == '1';
            }));
            $jadwal->soal_kompleks = array_values(array_filter($soals, function ($s) {
                return $s->jenis == '2' && $s->tampilkan == '1';
            }));
            $jadwal->soal_jodohkan = array_values(array_filter($soals, function ($s) {
                return $s->jenis == '3' && $s->tampilkan == '1';
            }));
            $jadwal->soal_isian = array_values(array_filter($soals, function ($s) {
                return $s->jenis == '4' && $s->tampilkan == '1';
            }));
            $jadwal->soal_esai = array_values(array_filter($soals, function ($s) {
                return $s->jenis == '5' && $s->tampilkan == '1';
            }));
            $jadwal->soal_urutan = array_values(array_filter($soals, function ($s) {
                return $s->jenis == '6' && $s->tampilkan == '1';
            }));
            $jadwal->soal_cloze = array_values(array_filter($soals, function ($s) {
                return $s->jenis == '7' && $s->tampilkan == '1';
            }));
        }

        return $jadwal;
    }

    public function getBeritaAcaraById($id)
    {
        return $this->db->table('cbt_berita_acara a')
            ->select('a.*, d.nama_mapel, d.kode as kode_mapel, b.tgl_mulai')
            ->join('cbt_jadwal b', 'a.id_jadwal = b.id_jadwal', 'left')
            ->join('cbt_bank_soal c', 'b.id_bank = c.id_bank', 'left')
            ->join('master_mapel d', 'c.bank_mapel_id = d.id_mapel', 'left')
            ->where('a.id_ba', $id)
            ->get()->getRow();
    }

    public function getPaktaIntegritasById($id)
    {
        return $this->db->table('cbt_pakta_integritas')
            ->where('id_pakta', $id)
            ->get()->getRow();
    }

    public function getAllBeritaAcaraDetailed()
    {
        return $this->db->table('cbt_berita_acara a')
            ->select('a.*, d.nama_mapel, d.kode as kode_mapel, b.tgl_mulai')
            ->join('cbt_jadwal b', 'a.id_jadwal = b.id_jadwal', 'left')
            ->join('cbt_bank_soal c', 'b.id_bank = c.id_bank', 'left')
            ->join('master_mapel d', 'c.bank_mapel_id = d.id_mapel', 'left')
            ->orderBy('a.updated_at', 'DESC')
            ->get()->getResult();
    }

    public function saveBeritaAcaraSimple(array $data)
    {
        return $this->db->table('cbt_berita_acara')->insert($data);
    }

    public function savePaktaSimple(array $data)
    {
        return $this->db->table('cbt_pakta_integritas')->insert($data);
    }

    public function getJenisUjianById($id)
    {
        return $this->db->table('cbt_jenis')->where('id_jenis', $id)->get()->getRow();
    }

    /**
     * Hitung Nilai Otomatis dengan Weighted Scoring
     * 
     * Type 1: PG (Pilihan Ganda) - Full credit only
     * Type 2: PG Kompleks - Partial credit based on correct answers
     * Type 3: Menjodohkan - Partial credit per correct pair
     * Type 4: Isian Singkat - Full credit with multiple acceptable answers
     * Type 5: Esai - Manual Grading (Skipped)
     * Type 6: Mengurutkan - Full credit only (order matters)
     * Type 7: Isian Rumpang - Partial credit per correct blank
     * 
     * UPDATED: Now uses weighted scoring based on bobot from bank_soal
     */
    public function hitungNilai($id_siswa, $id_jadwal, $jadwalData = null, $soalList = null, $mapJawaban = null)
    {
        $db = $this->db;

        // 1. Get Jadwal with Bank Soal (including bobot weights)
        if ($jadwalData === null) {
            $jadwalData = $db->table('cbt_jadwal j')
                ->select('j.id_jadwal, j.id_bank, j.katrol_max, j.katrol_min,
                    b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, 
                    b.bobot_esai, b.bobot_urutan, b.bobot_cloze,
                    b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian,
                    b.tampil_esai, b.tampil_urutan, b.tampil_cloze')
                ->join('cbt_bank_soal b', 'j.id_bank = b.id_bank', 'left')
                ->where('j.id_jadwal', $id_jadwal)
                ->get()->getRow();
        }

        if (!$jadwalData)
            return false;

        // 2. Get All Questions (Base of Truth)
        if ($soalList === null) {
            $soalList = $db->table('cbt_soal')
                ->select('id_soal, jenis, tipe_soal, jawaban as kunci, opsi_a')
                ->where('bank_id', $jadwalData->id_bank)
                ->where('tampilkan', '1') // Only count displayed questions
                ->get()->getResult();
        }

        if (empty($soalList))
            return false;

        // 3. Get Student Answers
        if ($mapJawaban === null) {
            $jawabanList = $db->table('cbt_jawaban')
                ->select('id_soal, jawaban as jawab')
                ->where('id_siswa', $id_siswa)
                ->where('id_jadwal', $id_jadwal)
                ->get()->getResult();

            $mapJawaban = [];
            foreach ($jawabanList as $j) {
                $mapJawaban[$j->id_soal] = $j->jawab;
            }
        }

        // Score accumulators
        $stats = [
            '1' => ['skor' => 0, 'total' => 0], // PG
            '2' => ['skor' => 0, 'total' => 0], // PG Kompleks
            '3' => ['skor' => 0, 'total' => 0], // Menjodohkan
            '4' => ['skor' => 0, 'total' => 0], // Isian Singkat
            '5' => ['skor' => 0, 'total' => 0], // Esai (manual)
            '6' => ['skor' => 0, 'total' => 0], // Mengurutkan
            '7' => ['skor' => 0, 'total' => 0], // Isian Rumpang
        ];

        foreach ($soalList as $s) {
            // tipe_soal ('pgkk') hanya dipakai untuk tipe baru yang belum punya jenis lama;
            // selain itu jenis lama (1-7) tetap otoritatif agar skor historis tidak berubah.
            $isPgkk  = !empty($s->tipe_soal) && $s->tipe_soal === 'pgkk';
            $tipeSkor = $isPgkk ? 'pgkk' : ($s->jenis ?? '1');
            $jenis    = $isPgkk ? '2' : $tipeSkor; // pgkk masuk bucket kompleks (bobot_kompleks)
            $kunci = $s->kunci ?? '';
            $jawab = $mapJawaban[$s->id_soal] ?? '';

            if (!isset($stats[$jenis]))
                $stats[$jenis] = ['skor' => 0, 'total' => 0];
            $stats[$jenis]['total']++;

            if ($jawab === '') {
                continue;
            }

            $evaluation = $this->evaluateAnswer($tipeSkor, $jawab, $kunci);
            $stats[$jenis]['skor'] += $evaluation['skor'];
        }

        // 4. Get Bobot (weights) from bank soal
        $bobotMap = [
            '1' => floatval($jadwalData->bobot_pg ?? 0),
            '2' => floatval($jadwalData->bobot_kompleks ?? 0),
            '3' => floatval($jadwalData->bobot_jodohkan ?? 0),
            '4' => floatval($jadwalData->bobot_isian ?? 0),
            '5' => floatval($jadwalData->bobot_esai ?? 0),
            '6' => floatval($jadwalData->bobot_urutan ?? 0),
            '7' => floatval($jadwalData->bobot_cloze ?? 0),
        ];

        // 5. Calculate Weighted Score
        $weightedScore = 0;
        $totalWeight = 0;
        $detailScores = [];

        foreach ($stats as $type => $data) {
            $bobot = $bobotMap[$type] ?? 0;

            if ($data['total'] > 0 && $type !== '5') { // Skip esai for auto-grading
                // Calculate percentage for this type
                $typePercentage = ($data['skor'] / $data['total']) * 100;

                // Store detail score per type
                $detailScores[$type] = [
                    'skor_mentah' => $data['skor'],
                    'total_soal' => $data['total'],
                    'persentase' => round($typePercentage, 2),
                    'bobot' => $bobot,
                    'nilai_berbobot' => $bobot > 0 ? round(($typePercentage / 100) * $bobot, 2) : 0
                ];

                if ($bobot > 0) {
                    // Apply weight: (percentage * weight)
                    $weightedScore += ($typePercentage / 100) * $bobot;
                    $totalWeight += $bobot;
                }
            }
        }

        // 6. Calculate Final Score
        // If weighted scoring is configured, use it; otherwise fallback to simple percentage
        if ($totalWeight > 0) {
            // Weighted score as percentage of total weight
            $mainScore = ($weightedScore / $totalWeight) * 100;
        } else {
            // Fallback: Simple percentage of correct answers
            $totalScore = 0;
            $totalQuestions = 0;
            foreach ($stats as $type => $data) {
                if ($data['total'] > 0 && $type !== '5') {
                    $totalScore += $data['skor'];
                    $totalQuestions += $data['total'];
                }
            }
            $mainScore = ($totalQuestions > 0) ? ($totalScore / $totalQuestions) * 100 : 0;
        }

        // 7. Apply Katrol logic removed - now manual via Cbtnilai::save_katrol.
        $nilaiAsli = $mainScore;
        // Nilai katrol TIDAK dihitung ulang di sini (dikelola manual), dan harus
        // dipertahankan saat rehitung agar tidak hilang karena update di bawah.
        $existKatrol = $db->table('cbt_nilai')
            ->select('nilai_katrol')
            ->where('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()->getRow();
        $nilaiKatrol = $existKatrol ? ($existKatrol->nilai_katrol ?? null) : null;

        // Count fully correct PG for backward compatibility
        $pgBenar = (int) round($stats['1']['skor']);

        // 8. Prepare data to save
        $dtNilai = [
            'id_siswa' => $id_siswa,
            'id_jadwal' => $id_jadwal,
            'pg_benar' => $pgBenar,
            'pg_nilai' => !is_nan($mainScore) ? number_format($mainScore, 2, '.', '') : '0.00',
            'nilai_asli' => !is_nan($nilaiAsli) ? number_format($nilaiAsli, 2, '.', '') : '0.00',
            'nilai_katrol' => $nilaiKatrol,
            'pg_skor' => isset($detailScores['1']) && !is_nan($detailScores['1']['nilai_berbobot']) ? number_format($detailScores['1']['nilai_berbobot'], 2, '.', '') : '0.00',
            'kompleks_skor' => isset($detailScores['2']) && !is_nan($detailScores['2']['nilai_berbobot']) ? number_format($detailScores['2']['nilai_berbobot'], 2, '.', '') : '0.00',
            'jodohkan_skor' => isset($detailScores['3']) && !is_nan($detailScores['3']['nilai_berbobot']) ? number_format($detailScores['3']['nilai_berbobot'], 2, '.', '') : '0.00',
            'isian_skor' => isset($detailScores['4']) && !is_nan($detailScores['4']['nilai_berbobot']) ? number_format($detailScores['4']['nilai_berbobot'], 2, '.', '') : '0.00',
            'urutan_skor' => isset($detailScores['6']) && !is_nan($detailScores['6']['nilai_berbobot']) ? number_format($detailScores['6']['nilai_berbobot'], 2, '.', '') : '0.00',
            'cloze_skor' => isset($detailScores['7']) && !is_nan($detailScores['7']['nilai_berbobot']) ? number_format($detailScores['7']['nilai_berbobot'], 2, '.', '') : '0.00',
            'detail_json' => json_encode($detailScores),
        ];

        // 9. Save to database with proper error handling
        try {
            $existN = $db->table('cbt_nilai')
                ->where('id_siswa', $id_siswa)
                ->where('id_jadwal', $id_jadwal)
                ->countAllResults();

            if ($existN > 0) {
                $db->table('cbt_nilai')
                    ->where('id_siswa', $id_siswa)
                    ->where('id_jadwal', $id_jadwal)
                    ->update($dtNilai);
            } else {
                // Use raw query with ON DUPLICATE KEY UPDATE as fallback
                $sql = "INSERT INTO cbt_nilai (id_siswa, id_jadwal, pg_benar, pg_nilai, nilai_asli, nilai_katrol, pg_skor, kompleks_skor, jodohkan_skor, isian_skor, urutan_skor, cloze_skor, detail_json) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                            pg_benar = VALUES(pg_benar),
                            pg_nilai = VALUES(pg_nilai),
                            nilai_asli = VALUES(nilai_asli),
                            nilai_katrol = VALUES(nilai_katrol),
                            pg_skor = VALUES(pg_skor),
                            kompleks_skor = VALUES(kompleks_skor),
                            jodohkan_skor = VALUES(jodohkan_skor),
                            isian_skor = VALUES(isian_skor),
                            urutan_skor = VALUES(urutan_skor),
                            cloze_skor = VALUES(cloze_skor),
                            detail_json = VALUES(detail_json)";

                $db->query($sql, [
                    $dtNilai['id_siswa'],
                    $dtNilai['id_jadwal'],
                    $dtNilai['pg_benar'],
                    $dtNilai['pg_nilai'],
                    $dtNilai['nilai_asli'],
                    $dtNilai['nilai_katrol'],
                    $dtNilai['pg_skor'],
                    $dtNilai['kompleks_skor'],
                    $dtNilai['jodohkan_skor'],
                    $dtNilai['isian_skor'],
                    $dtNilai['urutan_skor'],
                    $dtNilai['cloze_skor'],
                    $dtNilai['detail_json']
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', "hitungNilai save error: siswa={$id_siswa}, jadwal={$id_jadwal}, " . $e->getMessage());
            // Try UPDATE as last resort
            $db->table('cbt_nilai')
                ->where('id_siswa', $id_siswa)
                ->where('id_jadwal', $id_jadwal)
                ->update($dtNilai);
        }

        // 10. Log scoring for debugging
        log_message('info', "hitungNilai completed: siswa={$id_siswa}, jadwal={$id_jadwal}, " .
            "nilai_asli={$nilaiAsli}, nilai_akhir={$mainScore}, weighted={$totalWeight}");

        return true;
    }

    /**
     * Batch calculate scores for multiple students
     * More efficient than calling hitungNilai individually
     * 
     * @param array $studentIds Array of student IDs
     * @param int $idJadwal Schedule ID
     * @return array Results with success/fail counts
     */
    /**
     * Hitung Nilai Masal - Versi Teroptimasi
     * Mengambil data master (soal/jadwal) sekali, bukan di dalam loop.
     * Cocok untuk populasi siswa besar (>1000)
     */
    public function batchHitungNilai(array $studentIds, int $idJadwal): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        if (empty($studentIds)) return $results;

        // 1. Ambil Data Master Keluar Loop
        $jadwalData = $this->db->table('cbt_jadwal j')
            ->select('j.id_jadwal, j.id_bank, j.katrol_max, j.katrol_min,
                b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, 
                b.bobot_esai, b.bobot_urutan, b.bobot_cloze,
                b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian,
                b.tampil_esai, b.tampil_urutan, b.tampil_cloze')
            ->join('cbt_bank_soal b', 'j.id_bank = b.id_bank', 'left')
            ->where('j.id_jadwal', $idJadwal)
            ->get()->getRow();

        if (!$jadwalData) {
            $results['errors'][] = "Jadwal id {$idJadwal} tidak ditemukan.";
            return $results;
        }

        $soalList = $this->db->table('cbt_soal')
            ->select('id_soal, jenis, tipe_soal, jawaban as kunci, opsi_a')
            ->where('bank_id', $jadwalData->id_bank)
            ->where('tampilkan', '1')
            ->get()->getResult();

        if (empty($soalList)) {
            $results['errors'][] = "Bank soal kosong atau tidak ada soal yang ditampilkan.";
            return $results;
        }

        // 2. Ambil SEMUA Jawaban Siswa dalam satu query (Data Bulk)
        $allJawaban = $this->db->table('cbt_jawaban')
            ->select('id_siswa, id_soal, jawaban as jawab')
            ->where('id_jadwal', $idJadwal)
            ->whereIn('id_siswa', $studentIds)
            ->get()->getResult();

        // Mapping jawaban: [id_siswa][id_soal] => jawab
        $mapJawabanSiswa = [];
        foreach ($allJawaban as $j) {
            $mapJawabanSiswa[$j->id_siswa][$j->id_soal] = $j->jawab;
        }

        $this->db->transBegin();
        try {
            foreach ($studentIds as $idSiswa) {
                // Gunakan versi hitungNilai yang menerima parameter data master
                $jawabanSiswa = $mapJawabanSiswa[$idSiswa] ?? [];
                
                if ($this->hitungNilai($idSiswa, $idJadwal, $jadwalData, $soalList, $jawabanSiswa)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Gagal memproses siswa ID: {$idSiswa}";
                }
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                $results['failed'] += $results['success'];
                $results['success'] = 0;
                $results['errors'][] = "Transaksi database gagal (Rollback).";
            } else {
                $this->db->transCommit();

                // Trigger item analysis setelah semua nilai berhasil dihitung.
                // Dijalankan hanya jika ada siswa yang berhasil diproses.
                if ($results['success'] > 0) {
                    try {
                        \CodeIgniter\Events\Events::trigger('ujian_selesai', $idJadwal);
                    } catch (\Throwable $evtErr) {
                        // Gagal trigger tidak boleh merusak alur penilaian utama
                        log_message('error', "batchHitungNilai event error: " . $evtErr->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            $results['failed'] = count($studentIds);
            $results['success'] = 0;
            $results['errors'][] = "Error kritis: " . $e->getMessage();
            log_message('error', "batchHitungNilai optimized error: " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Get scoring statistics for a jadwal
     * Useful for reports and analysis
     */
    public function getScoringStats(int $idJadwal): array
    {
        $db = $this->db;

        $stats = $db->table('cbt_nilai')
            ->select('
                COUNT(*) as total_siswa,
                AVG(pg_nilai) as rata_rata,
                MAX(pg_nilai) as nilai_tertinggi,
                MIN(pg_nilai) as nilai_terendah,
                AVG(nilai_asli) as rata_rata_asli,
                SUM(CASE WHEN pg_nilai >= 75 THEN 1 ELSE 0 END) as lulus,
                SUM(CASE WHEN pg_nilai < 75 THEN 1 ELSE 0 END) as tidak_lulus
            ')
            ->where('id_jadwal', $idJadwal)
            ->get()->getRow();

        // Get distribution
        $distribution = $db->table('cbt_nilai')
            ->select("
                CASE 
                    WHEN pg_nilai >= 90 THEN 'A (90-100)'
                    WHEN pg_nilai >= 80 THEN 'B (80-89)'
                    WHEN pg_nilai >= 70 THEN 'C (70-79)'
                    WHEN pg_nilai >= 60 THEN 'D (60-69)'
                    ELSE 'E (<60)'
                END as grade,
                COUNT(*) as jumlah
            ")
            ->where('id_jadwal', $idJadwal)
            ->groupBy('grade')
            ->get()->getResult();

        return [
            'summary' => $stats,
            'distribution' => $distribution
        ];
    }

    /**
     * Normalize answer string for comparison
     * - Lowercase
     * - Trim whitespace
     * - Collapse multiple spaces
     * - Remove common punctuation variations
     * - Handle Indonesian special characters
     * - Remove quotes
     */
    private function getStatusUjianLabel($status, $mulai, $selesai): string
    {
        switch ($status) {
            case '2': return 'Selesai';
            case '1': return 'Mengerjakan';
            case '3': return 'Reset';
            default:  return $mulai ? 'Belum Selesai' : '';
        }
    }

    /**
     * Evaluate a single answer against key
     *
     * Delegasi penuh ke ScoringService (engine penyekoran terpadu CBT).
     */
    public function evaluateAnswer($jenis, $jawab, $kunci): array
    {
        return ScoringService::instance()->skorSoal($jenis, $jawab, $kunci);
    }
}
