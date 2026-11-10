<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function initialize()
    {
        $forge = \Config\Database::forge();

        // 1. Tabel Nilai Proses (Per Tujuan Pembelajaran)
        if (!$this->db->tableExists('nilai_proses')) {
            $forge->addField([
                'id_nilai_proses' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11],
                'id_tp' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
                'id_guru' => ['type' => 'INT', 'constraint' => 11],
                'id_tahun' => ['type' => 'INT', 'constraint' => 11],
                'id_smt' => ['type' => 'INT', 'constraint' => 11],
                'tipe' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'sumatif_lm'],
                'nilai' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true]
            ]);
            $forge->addKey('id_nilai_proses', true);
            $forge->addKey(['id_siswa', 'id_mapel', 'id_tp', 'tipe']);
            $forge->createTable('nilai_proses', true);
        }

        // 2. Tabel Nilai Akhir (Rapor)
        if (!$this->db->tableExists('nilai_akhir_kurmer')) {
            $forge->addField([
                'id_nilai_akhir' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11],
                'id_guru' => ['type' => 'INT', 'constraint' => 11],
                'id_tahun' => ['type' => 'INT', 'constraint' => 11],
                'id_smt' => ['type' => 'INT', 'constraint' => 11],
                'nilai_sas' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'nilai_rapor' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'bobot_proses' => ['type' => 'INT', 'constraint' => 3, 'default' => 40],
                'bobot_sts' => ['type' => 'INT', 'constraint' => 3, 'default' => 20],
                'bobot_sas' => ['type' => 'INT', 'constraint' => 3, 'default' => 20],
                'bobot_praktik' => ['type' => 'INT', 'constraint' => 3, 'default' => 20],
                'deskripsi_tinggi' => ['type' => 'TEXT', 'null' => true],
                'deskripsi_rendah' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true]
            ]);
            $forge->addKey('id_nilai_akhir', true);
            $forge->addUniqueKey(['id_siswa', 'id_mapel', 'id_tahun', 'id_smt']);
            $forge->createTable('nilai_akhir_kurmer', true);
        } else {
            // Ensure unique key exists
            try {
                $this->db->query("ALTER TABLE nilai_akhir_kurmer ADD UNIQUE INDEX IF NOT EXISTS unique_siswa_mapel_tahun_smt (id_siswa, id_mapel, id_tahun, id_smt)");
            } catch (\Throwable $e) {}

            // Add new columns if not exist
            $existingFields = array_map('strtolower', $this->db->getFieldNames('nilai_akhir_kurmer'));
            $newFields = [];
            if (!in_array('bobot_proses', $existingFields)) {
                $newFields['bobot_proses'] = ['type' => 'INT', 'constraint' => 3, 'default' => 40, 'after' => 'nilai_rapor'];
            }
            if (!in_array('bobot_sts', $existingFields)) {
                $newFields['bobot_sts'] = ['type' => 'INT', 'constraint' => 3, 'default' => 20, 'after' => 'bobot_proses'];
            }
            if (!in_array('bobot_sas', $existingFields)) {
                $newFields['bobot_sas'] = ['type' => 'INT', 'constraint' => 3, 'default' => 20, 'after' => 'bobot_sts'];
            }
            if (!in_array('bobot_praktik', $existingFields)) {
                $newFields['bobot_praktik'] = ['type' => 'INT', 'constraint' => 3, 'default' => 20, 'after' => 'bobot_sas'];
            }
            if (!empty($newFields)) {
                try {
                    $forge->addColumn('nilai_akhir_kurmer', $newFields);
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        }

        // 3. Tabel Nilai STS (Per Siswa - 1 nilai saja, bukan per TP)
        if (!$this->db->tableExists('nilai_sts')) {
            $forge->addField([
                'id_nilai_sts' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11],
                'id_guru' => ['type' => 'INT', 'constraint' => 11],
                'id_tahun' => ['type' => 'INT', 'constraint' => 11],
                'id_smt' => ['type' => 'INT', 'constraint' => 11],
                'nilai' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true]
            ]);
            $forge->addKey('id_nilai_sts', true);
            $forge->addKey(['id_siswa', 'id_mapel', 'id_tahun', 'id_smt'], true); // Unique index
            $forge->createTable('nilai_sts', true);
        }
    }

    // --- Core Operations ---

    public function getNilaiProses($id_siswa, $id_mapel, $id_tp, $tipe = 'sumatif_lm')
    {
        return $this->db->table('nilai_proses')
            ->where('id_siswa', $id_siswa)
            ->where('id_mapel', $id_mapel)
            ->where('id_tp', $id_tp)
            ->where('tipe', $tipe)
            ->get()->getRow();
    }

    public function getAllNilaiProsesKelas($id_kelas, $id_mapel, $id_tp = null, $tipe = 'sumatif_lm')
    {
        $builder = $this->db->table('nilai_proses np');
        $builder->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa');
        $builder->where('ks.id_kelas', $id_kelas);
        $builder->where('np.id_mapel', $id_mapel);
        if ($id_tp !== null) {
            $builder->where('np.id_tp', $id_tp);
        }
        $builder->where('np.tipe', $tipe);
        return $builder->get()->getResult();
    }

    public function saveNilaiProses($data)
    {
        try {
            $existing = $this->getNilaiProses($data['id_siswa'], $data['id_mapel'], $data['id_tp'], $data['tipe']);

            // Filter data to only include columns that exist in the table
            $allowedColumns = $this->db->getFieldNames('nilai_proses');
            $filteredData = array_intersect_key($data, array_flip($allowedColumns));

            // DEBUG: Log everything
            log_message('debug', '[saveNilaiProses] allowedColumns: ' . json_encode($allowedColumns));
            log_message('debug', '[saveNilaiProses] filteredData keys: ' . json_encode(array_keys($filteredData)));
            log_message('debug', '[saveNilaiProses] filteredData: ' . json_encode($filteredData));
            log_message('debug', '[saveNilaiProses] existing: ' . ($existing ? 'YES id=' . $existing->id_nilai_proses : 'NO'));

            if ($existing) {
                $filteredData['updated_at'] = date('Y-m-d H:i:s');
                $result = $this->db->table('nilai_proses')->where('id_nilai_proses', $existing->id_nilai_proses)->update($filteredData);
                log_message('debug', '[saveNilaiProses] UPDATE result: ' . var_export($result, true) . ' affected: ' . $this->db->affectedRows());
                return $result;
            } else {
                $filteredData['created_at'] = date('Y-m-d H:i:s');
                $result = $this->db->table('nilai_proses')->insert($filteredData);
                log_message('debug', '[saveNilaiProses] INSERT result: ' . var_export($result, true) . ' insertID: ' . $this->db->insertID() . ' error: ' . json_encode($this->db->error()));
                return $result;
            }
        } catch (\Throwable $e) {
            log_message('error', '[NilaiModel::saveNilaiProses] EXCEPTION: ' . $e->getMessage() . ' Data: ' . json_encode(array_keys($data)));
            return false;
        }
    }

    /**
     * Delete nilai proses (for when user clears the input)
     */
    public function deleteNilaiProses($id_siswa, $id_mapel, $id_tp, $tipe = 'sumatif_lm')
    {
        try {
            $existing = $this->getNilaiProses($id_siswa, $id_mapel, $id_tp, $tipe);
            if ($existing) {
                return $this->db->table('nilai_proses')
                    ->where('id_nilai_proses', $existing->id_nilai_proses)
                    ->delete();
            }
            return true; // Nothing to delete, considered success
        } catch (\Throwable $e) {
            log_message('error', '[NilaiModel::deleteNilaiProses] EXCEPTION: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch save nilai proses untuk multiple siswa sekaligus
     */
    public function batchSaveNilaiProses($dataArray)
    {
        $saved = 0;
        $errors = 0;
        foreach ($dataArray as $data) {
            try {
                $this->saveNilaiProses($data);
                $saved++;
            } catch (\Throwable $e) {
                $errors++;
                log_message('error', '[NilaiModel::batchSaveNilaiProses] Error: ' . $e->getMessage());
            }
        }
        return ['saved' => $saved, 'errors' => $errors];
    }

    public function getNilaiAkhir($id_siswa, $id_mapel, $id_tahun, $id_smt)
    {
        return $this->db->table('nilai_akhir_kurmer')
            ->where('id_siswa', $id_siswa)
            ->where('id_mapel', $id_mapel)
            ->where('id_tahun', $id_tahun)
            ->where('id_smt', $id_smt)
            ->get()->getRow();
    }

    public function saveNilaiAkhir($data)
    {
        try {
            $existing = $this->getNilaiAkhir($data['id_siswa'], $data['id_mapel'], $data['id_tahun'], $data['id_smt']);

            // Filter data to only include columns that exist in the table
            $allowedColumns = $this->db->getFieldNames('nilai_akhir_kurmer');
            $filteredData = array_intersect_key($data, array_flip($allowedColumns));

            if ($existing) {
                $filteredData['updated_at'] = date('Y-m-d H:i:s');
                return $this->db->table('nilai_akhir_kurmer')->where('id_nilai_akhir', $existing->id_nilai_akhir)->update($filteredData);
            } else {
                $filteredData['created_at'] = date('Y-m-d H:i:s');
                return $this->db->table('nilai_akhir_kurmer')->insert($filteredData);
            }
        } catch (\Throwable $e) {
            log_message('error', '[NilaiModel::saveNilaiAkhir] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch save nilai akhir untuk seluruh kelas
     */
    public function batchSaveNilaiAkhir($dataArray)
    {
        $saved = 0;
        $errors = 0;
        foreach ($dataArray as $data) {
            try {
                $this->saveNilaiAkhir($data);
                $saved++;
            } catch (\Throwable $e) {
                $errors++;
                log_message('error', '[NilaiModel::batchSaveNilaiAkhir] Error: ' . $e->getMessage());
            }
        }
        return ['saved' => $saved, 'errors' => $errors];
    }

    // --- Progress & Statistics ---

    /**
     * Hitung progress input nilai untuk mapel-kelas tertentu
     */
    public function getProgressInput($id_kelas, $id_mapel, $id_tahun, $id_smt)
    {
        // Hitung jumlah siswa di kelas
        $totalSiswa = $this->db->table('kelas_siswa')
            ->where('id_kelas', $id_kelas)
            ->where('id_tp', $id_tahun)
            ->where('id_smt', $id_smt)
            ->countAllResults();

        if ($totalSiswa == 0) return ['persen' => 0, 'total_siswa' => 0, 'sudah_input' => 0, 'total_tp' => 0, 'persen_akhir' => 0];

        // Hitung jumlah TP yang ada untuk mapel ini
        $totalTP = $this->db->table('e_tujuan_pembelajaran')
            ->where('id_mapel', $id_mapel)
            ->where('id_tp', $id_tahun)
            ->countAllResults();

        if ($totalTP == 0) return ['persen' => 0, 'total_siswa' => $totalSiswa, 'sudah_input' => 0, 'total_tp' => 0, 'persen_akhir' => 0];

        // Hitung total cells yang sudah diisi
        $filledCells = $this->db->table('nilai_proses np')
            ->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa')
            ->where('ks.id_kelas', $id_kelas)
            ->where('np.id_mapel', $id_mapel)
            ->where('np.tipe', 'sumatif_lm')
            ->where('np.nilai >', 0)
            ->countAllResults();

        $totalCells = $totalSiswa * $totalTP;
        $persen = $totalCells > 0 ? round(($filledCells / $totalCells) * 100) : 0;

        // Hitung siswa yang sudah ada nilai akhir
        $sudahAkhir = $this->db->table('nilai_akhir_kurmer nak')
            ->join('kelas_siswa ks', 'ks.id_siswa = nak.id_siswa')
            ->where('ks.id_kelas', $id_kelas)
            ->where('nak.id_mapel', $id_mapel)
            ->where('nak.id_tahun', $id_tahun)
            ->where('nak.id_smt', $id_smt)
            ->where('nak.nilai_rapor >', 0)
            ->countAllResults();

        return [
            'persen' => $persen,
            'total_siswa' => $totalSiswa,
            'sudah_input' => $filledCells,
            'total_cells' => $totalCells,
            'total_tp' => $totalTP,
            'sudah_akhir' => $sudahAkhir,
            'persen_akhir' => $totalSiswa > 0 ? round(($sudahAkhir / $totalSiswa) * 100) : 0
        ];
    }

    /**
     * Statistik nilai per kelas untuk dashboard
     */
    public function getStatistikKelas($id_kelas, $id_mapel, $id_tahun, $id_smt)
    {
        $builder = $this->db->table('nilai_akhir_kurmer nak');
        $builder->select('
            COUNT(nak.id_nilai_akhir) as total_siswa,
            ROUND(AVG(nak.nilai_rapor), 1) as rata_rata,
            MAX(nak.nilai_rapor) as tertinggi,
            MIN(nak.nilai_rapor) as terendah,
            SUM(CASE WHEN nak.nilai_rapor >= 75 THEN 1 ELSE 0 END) as tuntas,
            SUM(CASE WHEN nak.nilai_rapor < 75 THEN 1 ELSE 0 END) as belum_tuntas
        ');
        $builder->join('kelas_siswa ks', 'ks.id_siswa = nak.id_siswa');
        $builder->where('ks.id_kelas', $id_kelas);
        $builder->where('nak.id_mapel', $id_mapel);
        $builder->where('nak.id_tahun', $id_tahun);
        $builder->where('nak.id_smt', $id_smt);
        $builder->where('nak.nilai_rapor >', 0);

        return $builder->get()->getRow();
    }

    // --- Report Card Queries ---

    public function getRaporSiswa($id_siswa, $id_tahun, $id_smt)
    {
        return $this->db->table('nilai_akhir_kurmer na')
            ->select('na.*, m.nama_mapel, m.kode as kode_mapel, kel.nama_kel_mapel as kelompok')
            ->join('master_mapel m', 'm.id_mapel = na.id_mapel')
            ->join('master_kelompok_mapel kel', 'm.kelompok = kel.kode_kel_mapel', 'left')
            ->where('na.id_siswa', $id_siswa)
            ->where('na.id_tahun', $id_tahun)
            ->where('na.id_smt', $id_smt)
            ->orderBy('m.kelompok', 'ASC')
            ->orderBy('m.no_urut', 'ASC')
            ->get()->getResult();
    }

    // --- Utility: Predikat Kurikulum Merdeka ---

    /**
     * Mendapatkan predikat berdasarkan nilai (standar Kurikulum Merdeka)
     * A = 90-100 (Sangat Baik)
     * B = 80-89  (Baik)
     * C = 70-79  (Cukup)
     * D = < 70   (Perlu Bimbingan)
     */
    public static function getPredikat($nilai)
    {
        if ($nilai >= 90) return ['huruf' => 'A', 'label' => 'Sangat Baik', 'color' => 'success'];
        if ($nilai >= 80) return ['huruf' => 'B', 'label' => 'Baik', 'color' => 'primary'];
        if ($nilai >= 70) return ['huruf' => 'C', 'label' => 'Cukup', 'color' => 'warning'];
        return ['huruf' => 'D', 'label' => 'Perlu Bimbingan', 'color' => 'danger'];
    }

    /**
     * Generate deskripsi capaian kompetensi otomatis
     */
    public static function generateDeskripsi($nama_siswa, $highest_tp, $lowest_tp, $nilai_rapor)
    {
        $predikat = self::getPredikat($nilai_rapor);

        $desc_tinggi = '';
        $desc_rendah = '';

        if (!empty($highest_tp)) {
            switch ($predikat['huruf']) {
                case 'A':
                    $desc_tinggi = "Ananda {$nama_siswa} menunjukkan penguasaan yang sangat baik dan konsisten dalam " . lcfirst($highest_tp);
                    break;
                case 'B':
                    $desc_tinggi = "Ananda {$nama_siswa} menunjukkan penguasaan yang baik dalam " . lcfirst($highest_tp);
                    break;
                case 'C':
                    $desc_tinggi = "Ananda {$nama_siswa} cukup mampu memahami " . lcfirst($highest_tp);
                    break;
                default:
                    $desc_tinggi = "Ananda {$nama_siswa} sudah mulai memahami " . lcfirst($highest_tp);
            }
        }

        if (!empty($lowest_tp)) {
            switch ($predikat['huruf']) {
                case 'A':
                case 'B':
                    $desc_rendah = "Perlu peningkatan lebih lanjut dalam " . lcfirst($lowest_tp);
                    break;
                case 'C':
                    $desc_rendah = "Memerlukan bimbingan tambahan dalam " . lcfirst($lowest_tp);
                    break;
                default:
                    $desc_rendah = "Sangat membutuhkan pendampingan intensif dalam " . lcfirst($lowest_tp);
            }
        }

        return ['tinggi' => $desc_tinggi, 'rendah' => $desc_rendah];
    }

    /**
     * Get nilai STS per siswa (1 nilai saja, bukan per TP)
     */
    public function getNilaiSts($id_siswa, $id_mapel, $id_tahun, $id_smt)
    {
        // Cek apakah tabel sudah ada
        if (!$this->db->tableExists('nilai_sts')) {
            return null;
        }
        
        return $this->db->table('nilai_sts')
            ->where('id_siswa', $id_siswa)
            ->where('id_mapel', $id_mapel)
            ->where('id_tahun', $id_tahun)
            ->where('id_smt', $id_smt)
            ->get()->getRow();
    }

    /**
     * Save nilai STS per siswa
     */
    public function saveNilaiSts($data)
    {
        // Cek dan buat tabel jika belum ada
        if (!$this->db->tableExists('nilai_sts')) {
            $this->initialize();
        }
        
        // Cek lagi setelah initialize
        if (!$this->db->tableExists('nilai_sts')) {
            return false;
        }
        
        try {
            $existing = $this->getNilaiSts($data['id_siswa'], $data['id_mapel'], $data['id_tahun'], $data['id_smt']);

            // Filter data to only include columns that exist in the table
            $allowedColumns = $this->db->getFieldNames('nilai_sts');
            $filteredData = array_intersect_key($data, array_flip($allowedColumns));

            if ($existing) {
                $filteredData['updated_at'] = date('Y-m-d H:i:s');
                return $this->db->table('nilai_sts')->where('id_nilai_sts', $existing->id_nilai_sts)->update($filteredData);
            } else {
                $filteredData['created_at'] = date('Y-m-d H:i:s');
                return $this->db->table('nilai_sts')->insert($filteredData);
            }
        } catch (\Throwable $e) {
            log_message('error', '[NilaiModel::saveNilaiSts] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all nilai STS untuk kelas tertentu
     */
    public function getAllNilaiStsKelas($id_kelas, $id_mapel, $id_tahun, $id_smt)
    {
        // Cek apakah tabel sudah ada
        if (!$this->db->tableExists('nilai_sts')) {
            return [];
        }
        
        $builder = $this->db->table('nilai_sts ns');
        $builder->join('kelas_siswa ks', 'ks.id_siswa = ns.id_siswa');
        $builder->where('ks.id_kelas', $id_kelas);
        $builder->where('ns.id_mapel', $id_mapel);
        $builder->where('ns.id_tahun', $id_tahun);
        $builder->where('ns.id_smt', $id_smt);
        $result = $builder->get();
        return $result ? $result->getResult() : [];
    }

    /**
     * Delete nilai STS (for when user clears the input)
     */
    public function deleteNilaiSts($id_siswa, $id_mapel, $id_tahun, $id_smt)
    {
        try {
            if (!$this->db->tableExists('nilai_sts')) {
                return true;
            }

            $existing = $this->db->table('nilai_sts')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $id_mapel)
                ->where('id_tahun', $id_tahun)
                ->where('id_smt', $id_smt)
                ->get()->getRow();

            if ($existing) {
                return $this->db->table('nilai_sts')
                    ->where('id_nilai_sts', $existing->id_nilai_sts)
                    ->delete();
            }
            return true;
        } catch (\Throwable $e) {
            log_message('error', '[NilaiModel::deleteNilaiSts] EXCEPTION: ' . $e->getMessage());
            return false;
        }
    }
}
