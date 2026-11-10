<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CapaianPembelajaranModel
 *
 * Model untuk mengelola master Capaian Pembelajaran (CP) BSKAP.
 * Tabel ini menjadi acuan validasi kode_cp pada e_tujuan_pembelajaran
 * agar setiap TP dapat dipastikan merujuk CP yang sah.
 *
 * Referensi: Permendikbudristek No. 12 Tahun 2024, Panduan BSKAP 2023
 */
class CapaianPembelajaranModel extends Model
{
    protected $table         = 'e_capaian_pembelajaran';
    protected $primaryKey    = 'id_cp';
    protected $useAutoIncrement = true;
    protected $returnType    = 'object';
    protected $allowedFields = [
        'kode_cp',
        'mata_pelajaran',
        'fase',
        'elemen',
        'deskripsi_cp',
        'jenjang',
        'kelompok_mapel',
        'is_active',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    // ----------------------------------------------------------------
    // Pencarian & Lookup
    // ----------------------------------------------------------------

    /**
     * Cari CP berdasarkan kode unik.
     */
    public function findByKode(string $kode): ?object
    {
        if (!$this->db->tableExists($this->table)) {
            return null;
        }
        return $this->where('kode_cp', $kode)->first();
    }

    /**
     * Ambil semua CP aktif untuk satu fase tertentu.
     *
     * @param string $fase  Satu karakter: D, E, F
     */
    public function getByFase(string $fase): array
    {
        if (!$this->db->tableExists($this->table)) {
            return [];
        }
        return $this->where('fase', strtoupper($fase))
                    ->where('is_active', 1)
                    ->orderBy('mata_pelajaran', 'ASC')
                    ->orderBy('elemen', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil CP untuk dropdown berdasarkan mata pelajaran.
     *
     * @return array  [kode_cp => 'kode_cp — elemen']
     */
    public function getDropdownByMapel(string $mapel, ?string $fase = null): array
    {
        if (!$this->db->tableExists($this->table)) {
            return [];
        }

        $builder = $this->where('is_active', 1)
                        ->like('mata_pelajaran', $mapel);

        if ($fase) {
            $builder->where('fase', strtoupper($fase));
        }

        $rows = $builder->orderBy('fase', 'ASC')
                        ->orderBy('elemen', 'ASC')
                        ->findAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->kode_cp] = "[{$row->fase}] {$row->kode_cp} — {$row->elemen}";
        }
        return $result;
    }

    /**
     * Validasi apakah kode_cp valid (ada di tabel master).
     */
    public function isValidKode(string $kode): bool
    {
        if (empty(trim($kode)) || !$this->db->tableExists($this->table)) {
            return false;
        }
        return $this->where('kode_cp', trim($kode))
                    ->where('is_active', 1)
                    ->countAllResults() > 0;
    }

    /**
     * Validasi batch: kembalikan kode-kode yang TIDAK valid.
     *
     * @param  string[] $kodes
     * @return string[] Kode yang tidak ditemukan di master
     */
    public function getInvalidKodes(array $kodes): array
    {
        if (empty($kodes) || !$this->db->tableExists($this->table)) {
            return [];
        }

        $valid = $this->select('kode_cp')
                      ->whereIn('kode_cp', $kodes)
                      ->where('is_active', 1)
                      ->findAll();

        $validKodes = array_column(array_map(fn($r) => (array) $r, $valid), 'kode_cp');
        return array_values(array_diff($kodes, $validKodes));
    }

    /**
     * Statistik jumlah CP per fase dan jenjang.
     * Mengembalikan array kosong jika tabel belum ada atau query gagal.
     */
    public function getStats(): array
    {
        if (!$this->db->tableExists($this->table)) {
            return [];
        }

        try {
            $q = $this->db->table($this->table)
                ->select('fase, jenjang, COUNT(*) as jumlah')
                ->where('is_active', 1)
                ->groupBy(['fase', 'jenjang'])
                ->orderBy('jenjang', 'ASC')
                ->orderBy('fase', 'ASC')
                ->get();

            return $q ? $q->getResult() : [];
        } catch (\Throwable $e) {
            log_message('error', '[CapaianPembelajaranModel::getStats] ' . $e->getMessage());
            return [];
        }
    }

    // ----------------------------------------------------------------
    // Seeder Helper — data awal CP umum
    // ----------------------------------------------------------------

    /**
     * Seed data CP umum jika tabel masih kosong.
     * Hanya menambahkan data sampel, bukan data lengkap BSKAP.
     * Data lengkap wajib diimport dari file resmi BSKAP.
     */
    public function seedDefault(): int
    {
        if (!$this->db->tableExists($this->table)) {
            return 0;
        }

        if ($this->countAllResults() > 0) {
            return 0;
        }

        $data = [
            // Fase D (SMP)
            ['kode_cp' => 'B.IND.D.1', 'mata_pelajaran' => 'Bahasa Indonesia', 'fase' => 'D',
             'elemen' => 'Menyimak', 'deskripsi_cp' => 'Peserta didik mampu menganalisis dan mengevaluasi berbagai informasi dari teks yang didengar.', 'jenjang' => 2],
            ['kode_cp' => 'B.IND.D.2', 'mata_pelajaran' => 'Bahasa Indonesia', 'fase' => 'D',
             'elemen' => 'Membaca', 'deskripsi_cp' => 'Peserta didik mampu memahami, mengolah, dan menginterpretasi informasi teks.', 'jenjang' => 2],
            ['kode_cp' => 'MAT.D.1', 'mata_pelajaran' => 'Matematika', 'fase' => 'D',
             'elemen' => 'Bilangan', 'deskripsi_cp' => 'Peserta didik memahami bilangan bulat, pecahan, rasio, dan proporsi.', 'jenjang' => 2],
            ['kode_cp' => 'MAT.D.2', 'mata_pelajaran' => 'Matematika', 'fase' => 'D',
             'elemen' => 'Aljabar', 'deskripsi_cp' => 'Peserta didik mengenal persamaan dan pertidaksamaan linear satu variabel.', 'jenjang' => 2],
            ['kode_cp' => 'IPA.D.1', 'mata_pelajaran' => 'IPA', 'fase' => 'D',
             'elemen' => 'Pemahaman IPA', 'deskripsi_cp' => 'Peserta didik memahami konsep sel sebagai unit terkecil kehidupan.', 'jenjang' => 2],
            // Fase E (SMA Kelas X)
            ['kode_cp' => 'MAT.E.1', 'mata_pelajaran' => 'Matematika', 'fase' => 'E',
             'elemen' => 'Bilangan', 'deskripsi_cp' => 'Peserta didik memahami dan mengoperasikan bilangan riil, eksponen, dan logaritma.', 'jenjang' => 3],
            ['kode_cp' => 'B.ING.E.1', 'mata_pelajaran' => 'Bahasa Inggris', 'fase' => 'E',
             'elemen' => 'Menyimak-Berbicara', 'deskripsi_cp' => 'Peserta didik mengomunikasikan ide secara lisan dengan struktur teks yang tepat.', 'jenjang' => 3],
            // Fase F (SMA Kelas XI-XII)
            ['kode_cp' => 'MAT.F.1', 'mata_pelajaran' => 'Matematika', 'fase' => 'F',
             'elemen' => 'Kalkulus', 'deskripsi_cp' => 'Peserta didik memahami konsep limit, turunan, dan integral.', 'jenjang' => 3],
            ['kode_cp' => 'FIS.F.1', 'mata_pelajaran' => 'Fisika', 'fase' => 'F',
             'elemen' => 'Mekanika', 'deskripsi_cp' => 'Peserta didik menganalisis gerak dan gaya menggunakan hukum Newton.', 'jenjang' => 3],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($data as &$row) {
            $row['is_active']   = 1;
            $row['created_at']  = $now;
            $row['updated_at']  = $now;
        }

        $this->db->table($this->table)->insertBatch($data);
        return count($data);
    }
}
