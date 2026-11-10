<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TujuanPembelajaranModel
 * 
 * Model untuk mengelola Tujuan Pembelajaran (TP) sesuai Kurikulum Merdeka
 * Mendukung 3 ranah kompetensi: Sikap, Pengetahuan, dan Keterampilan
 * Referensi: Permendikbudristek No. 12 Tahun 2024
 */
class TujuanPembelajaranModel extends Model
{
    protected $table = 'e_tujuan_pembelajaran';
    protected $primaryKey = 'id_tujuan';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_guru',
        'id_mapel',
        'id_tp',
        'id_smt',
        'tingkat',       // 7, 8, 9, 10, 11, 12
        'fase',          // D, E, F
        'kode',          // Kode TP (contoh: 10.1 atau TP-01)
        'kode_cp',       // Kode CP yang menjadi acuan
        'elemen_cp',     // Elemen/Domain CP
        'tujuan',        // Tujuan Pembelajaran (ranah pengetahuan - utama)
        'tp_sikap',      // TP ranah sikap (Profil Pelajar Pancasila)
        'tp_keterampilan', // TP ranah keterampilan/praktik
        'level_kognitif', // C1-C6 (Taksonomi Bloom)
        'materi_pokok',  // Materi pokok pembelajaran
        'kriteria_ketercapaian', // KKTP - Kriteria Ketercapaian Tujuan Pembelajaran
        'is_active',     // Status aktif/nonaktif TP
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;

    public function initialize()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if (!$db->tableExists($this->table)) {
            $forge->addField([
                'id_tujuan' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'id_guru' => ['type' => 'INT', 'constraint' => 11],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11],
                'id_tp' => ['type' => 'INT', 'constraint' => 11],
                'id_smt' => ['type' => 'INT', 'constraint' => 11],
                'tingkat' => ['type' => 'INT', 'constraint' => 2, 'default' => 0],
                'fase' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
                'kode' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'kode_cp' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'comment' => 'Kode Capaian Pembelajaran acuan'],
                'elemen_cp' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'comment' => 'Elemen/domain CP'],
                'tujuan' => ['type' => 'TEXT', 'comment' => 'TP ranah pengetahuan (utama)'],
                'tp_sikap' => ['type' => 'TEXT', 'null' => true, 'comment' => 'TP ranah sikap - Profil Pelajar Pancasila'],
                'tp_keterampilan' => ['type' => 'TEXT', 'null' => true, 'comment' => 'TP ranah keterampilan/praktik'],
                'level_kognitif' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true, 'comment' => 'C1-C6 Taksonomi Bloom'],
                'materi_pokok' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'kriteria_ketercapaian' => ['type' => 'TEXT', 'null' => true, 'comment' => 'KKTP - Kriteria Ketercapaian TP'],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Status aktif/nonaktif TP'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true]
            ]);
            $forge->addKey('id_tujuan', true);
            $forge->addKey('id_guru');
            $forge->addKey(['id_mapel', 'id_guru']);
            $forge->addKey('kode_cp');
            $forge->createTable($this->table, true);
        } else {
            // Migrate: tambahkan kolom baru jika belum ada
            $fieldsToAdd = [
                'tingkat'               => ['type' => 'INT', 'constraint' => 2, 'default' => 0, 'after' => 'id_smt'],
                'fase'                  => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true, 'after' => 'tingkat'],
                'kode_cp'               => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'kode', 'comment' => 'Kode Capaian Pembelajaran acuan'],
                'elemen_cp'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'kode_cp', 'comment' => 'Elemen/domain CP'],
                'tp_sikap'              => ['type' => 'TEXT', 'null' => true, 'after' => 'tujuan', 'comment' => 'TP ranah sikap'],
                'tp_keterampilan'       => ['type' => 'TEXT', 'null' => true, 'after' => 'tp_sikap', 'comment' => 'TP ranah keterampilan'],
                'level_kognitif'        => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true, 'after' => 'tp_keterampilan'],
                'materi_pokok'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'level_kognitif'],
                'kriteria_ketercapaian' => ['type' => 'TEXT', 'null' => true, 'after' => 'materi_pokok', 'comment' => 'KKTP'],
                'is_active'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'kriteria_ketercapaian', 'comment' => 'Status aktif/nonaktif TP'],
            ];

            $existingFields = array_map('strtolower', $db->getFieldNames($this->table));
            $fieldsToAddReally = [];
            foreach ($fieldsToAdd as $field => $def) {
                if (!in_array(strtolower($field), $existingFields)) {
                    $fieldsToAddReally[$field] = $def;
                }
            }

            if (!empty($fieldsToAddReally)) {
                try {
                    $forge->addColumn($this->table, $fieldsToAddReally);
                } catch (\Throwable $e) {
                    log_message('debug', '[TujuanPembelajaranModel::initialize] ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Ambil TP milik guru berdasarkan filter
     * @param int $id_guru ID guru
     * @param int $id_tp ID tahun pelajaran
     * @param int $id_smt ID semester
     * @param mixed $id_mapel ID mapel (opsional)
     * @param mixed $tingkat Tingkat kelas (opsional)
     * @param mixed $status Status 'aktif', 'nonaktif', atau null untuk semua
    /**
     * Ambil data Tujuan Pembelajaran berdasarkan guru dan filter opsional
     * Mendukung panggilan 5/6 parameter (signature baru) dan 7 parameter (signature legacy)
     * 
     * @param int $id_guru ID Guru
     * @param mixed $id_smt ID Semester (atau ID TP pada panggilan legacy 7-param)
     * @param mixed $id_mapel ID Mapel (atau ID Smt pada panggilan legacy 7-param)
     * @param mixed $tingkat Tingkat (atau ID Mapel pada panggilan legacy 7-param)
     * @param mixed $status Status (atau Tingkat pada panggilan legacy 7-param)
     * @param mixed $activeOnly Boolean activeOnly atau Status pada panggilan legacy 7-param
     * @return array
     */
    public function getTujuanByGuru(int $id_guru, $id_smt = null, $id_mapel = null, $tingkat = null, $status = null, $activeOnly = false): array
    {
        // Mendukung panggilan legacy 7 parameter: getTujuanByGuru($id_guru, $id_tp, $id_smt, $id_mapel, $tingkat, $status, $activeOnly)
        if (func_num_args() >= 7) {
            $args = func_get_args();
            $id_smt     = $args[2];
            $id_mapel   = $args[3];
            $tingkat    = $args[4];
            $status     = $args[5];
            $activeOnly = (bool)($args[6] ?? false);
        } else {
            $activeOnly = (bool)$activeOnly;
        }

        $builder = $this->db->table($this->table . ' t');
        $builder->select("t.*, IFNULL(m.nama_mapel, '-') as nama_mapel, m.kode as kode_mapel");
        
        // Add usage check — hanya hitung kartu soal tidak soft-deleted & submitted/approved, soal tidak soft-deleted
        if ($this->db->tableExists('cbt_kartu_soal_item') && $this->db->tableExists('cbt_kartu_soal') && $this->db->fieldExists('workflow_status', 'cbt_kartu_soal')) {
            $builder->select("(SELECT COUNT(*) FROM cbt_kartu_soal_item ksi INNER JOIN cbt_kartu_soal ks ON ksi.kartu_id = ks.id_kartu WHERE ksi.id_tujuan = t.id_tujuan AND ks.deleted_at IS NULL AND ks.workflow_status IN ('submitted','approved')) as used_in_kartu", false);
        } elseif ($this->db->tableExists('cbt_kartu_soal_item')) {
            $builder->select('(SELECT COUNT(*) FROM cbt_kartu_soal_item WHERE id_tujuan = t.id_tujuan) as used_in_kartu', false);
        } else {
            $builder->select('0 as used_in_kartu', false);
        }
        
        if ($this->db->tableExists('cbt_soal') && $this->db->fieldExists('tujuan_id', 'cbt_soal')) {
            $usedSoalSub = "(SELECT COUNT(*) FROM cbt_soal WHERE tujuan_id = t.id_tujuan";
            if ($this->db->fieldExists('deleted_at', 'cbt_soal')) {
                $usedSoalSub .= " AND deleted_at IS NULL";
            }
            $usedSoalSub .= ") as used_in_soal";
            $builder->select($usedSoalSub, false);
        } else {
            $builder->select('0 as used_in_soal', false);
        }

        // Pemakaian TP di Sebaran Materi (denormalisasi snapshot JSON materi_units)
        if ($this->db->tableExists('perangkat_sebaran_materi') && $this->db->fieldExists('materi_units', 'perangkat_sebaran_materi')) {
            $hasSoftDel = $this->db->fieldExists('deleted_at', 'perangkat_sebaran_materi');
            $condLike = "(ps.materi_units LIKE CONCAT('%\"id_tujuan\":', t.id_tujuan, ',%') OR ps.materi_units LIKE CONCAT('%\"id_tujuan\":', t.id_tujuan, '}%'))";
            $usedSbrSub = '(SELECT COUNT(*) FROM perangkat_sebaran_materi ps WHERE '
                . ($hasSoftDel ? 'ps.deleted_at IS NULL AND ' : '')
                . $condLike . ') as used_in_sebaran';
            $builder->select($usedSbrSub, false);
        } else {
            $builder->select('0 as used_in_sebaran', false);
        }

        // Pemakaian TP sebagai dokumen Perangkat Pembelajaran (RPM/LKPD/Bahan Ajar/Penilaian)
        $perangkatParts = [];
        foreach (['perangkat_lesson_plan', 'perangkat_worksheet', 'perangkat_teaching_material', 'perangkat_assessment'] as $pt) {
            if (!$this->db->tableExists($pt)) continue;
            $alias = 'pp_' . substr(md5($pt), 0, 4);
            $soft = $this->db->fieldExists('deleted_at', $pt) ? "{$alias}.deleted_at IS NULL AND " : '';
            $perangkatParts[] = "(SELECT COUNT(*) FROM {$pt} {$alias} WHERE {$soft}{$alias}.id_tujuan = t.id_tujuan)";
        }
        if ($perangkatParts !== []) {
            $builder->select('(' . implode(' + ', $perangkatParts) . ') as used_in_perangkat', false);
        } else {
            $builder->select('0 as used_in_perangkat', false);
        }

        $builder->join('master_mapel m', 'm.id_mapel = t.id_mapel', 'left');
        $builder->where('t.id_guru', $id_guru);

        if ($id_smt !== null && $id_smt !== '' && $id_smt !== 'all') {
            $builder->where('t.id_smt', (int)$id_smt);
        }

        if ($id_mapel) {
            $builder->where('t.id_mapel', $id_mapel);
        }
        if ($tingkat !== null && $tingkat !== '' && $tingkat !== '0') {
            $builder->where('t.tingkat', (int) $tingkat);
        }
        
        $hasActive = $this->db->fieldExists('is_active', $this->table);
        if ($hasActive) {
            if ($status === 'aktif') {
                $builder->where('t.is_active', 1);
            } elseif ($status === 'nonaktif') {
                $builder->where('t.is_active', 0);
            }
            if ($activeOnly) {
                $builder->where('t.is_active', 1);
            }
        }

        $builder->orderBy('m.nama_mapel', 'ASC');
        $builder->orderBy('t.id_smt', 'ASC');
        $builder->orderBy('t.tingkat', 'ASC');
        $builder->orderBy('t.kode', 'ASC');

        $query = $builder->get();
        if ($query === false) {
            $error = $this->db->error();
            error_log("Database Error in getTujuanByGuru: " . $error['message']);
            // Fallback for debugging, remove or change for production
            if (ENVIRONMENT !== 'production') {
                throw new \RuntimeException("SQL Error in getTujuanByGuru: " . $error['message']);
            }
            return [];
        }
        return $query->getResult();
    }

    /**
     * Ambil TP dengan informasi CP terkait
     */
    public function getTujuanWithCpInfo(int $id_guru, int $id_tp, int $id_smt, $id_mapel = null): array
    {
        $builder = $this->db->table($this->table . ' t');
        $builder->select("t.*, IFNULL(m.nama_mapel, '-') as nama_mapel, m.kode as kode_mapel, cp.deskripsi_cp, cp.elemen as elemen_cp_detail");
        $builder->join('master_mapel m', 'm.id_mapel = t.id_mapel', 'left');
        $builder->join('e_capaian_pembelajaran cp', 'cp.kode_cp = t.kode_cp', 'left');
        $builder->where('t.id_guru', $id_guru);
        $builder->where('t.id_tp', $id_tp);

        if ($id_mapel) {
            $builder->where('t.id_mapel', $id_mapel);
        }
        $builder->orderBy('m.nama_mapel', 'ASC');
        $builder->orderBy('t.tingkat', 'ASC');
        $builder->orderBy('t.kode', 'ASC');

        return $builder->get()->getResult();
    }

    /**
     * Ambil distribusi level kognitif untuk analisis kualitas TP
     */
    public function getDistribusiLevelKognitif(int $id_guru, int $id_tp, $id_mapel = null): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('level_kognitif, COUNT(*) as jumlah');
        $builder->where('id_guru', $id_guru);
        $builder->where('id_tp', $id_tp);
        if ($id_mapel) {
            $builder->where('id_mapel', $id_mapel);
        }
        $builder->where('level_kognitif IS NOT NULL', null, false);
        $builder->groupBy('level_kognitif');
        $builder->orderBy('level_kognitif', 'ASC');

        return $builder->get()->getResult();
    }

    /**
     * Cek kelengkapan 3 ranah kompetensi TP
     * Returns statistik: berapa persen TP yang memiliki semua 3 ranah
     */
    public function getKelengkapanRanah(int $id_guru, int $id_tp, $id_mapel = null): array
    {
        $builder = $this->db->table($this->table);
        $builder->where('id_guru', $id_guru);
        $builder->where('id_tp', $id_tp);
        if ($id_mapel) {
            $builder->where('id_mapel', $id_mapel);
        }

        $total = $builder->countAllResults(false);

        $lengkap = $this->db->table($this->table)
            ->where('id_guru', $id_guru)
            ->where('id_tp', $id_tp)
            ->where('tujuan IS NOT NULL', null, false)
            ->where('tp_sikap IS NOT NULL', null, false)
            ->where('tp_keterampilan IS NOT NULL', null, false);
        if ($id_mapel) {
            $lengkap->where('id_mapel', $id_mapel);
        }
        $totalLengkap = $lengkap->countAllResults();

        $denganCp = $this->db->table($this->table)
            ->where('id_guru', $id_guru)
            ->where('id_tp', $id_tp)
            ->where('kode_cp IS NOT NULL', null, false);
        if ($id_mapel) {
            $denganCp->where('id_mapel', $id_mapel);
        }
        $totalDenganCp = $denganCp->countAllResults();

        return [
            'total'         => $total,
            'lengkap_ranah' => $totalLengkap,
            'dengan_cp'     => $totalDenganCp,
            'persen_lengkap' => $total > 0 ? round(($totalLengkap / $total) * 100, 1) : 0,
            'persen_cp'      => $total > 0 ? round(($totalDenganCp / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Validasi kualitas TP berdasarkan standar Kurikulum Merdeka
     */
    public function validateTpKualitas(object $tp): array
    {
        $issues = [];

        // Cek ranah pengetahuan (wajib)
        if (empty($tp->tujuan)) {
            $issues[] = ['level' => 'error', 'msg' => 'Tujuan Pembelajaran (ranah pengetahuan) belum diisi'];
        }

        // Cek kata kerja operasional (KKO)
        if (!empty($tp->tujuan)) {
            $kkoFound = $this->cekKataKerjaOperasional($tp->tujuan);
            if (!$kkoFound) {
                $issues[] = ['level' => 'warning', 'msg' => 'TP belum menggunakan KKO Taksonomi Bloom yang tepat'];
            }
        }

        // Cek ranah sikap
        if (empty($tp->tp_sikap)) {
            $issues[] = ['level' => 'info', 'msg' => 'Ranah sikap (Profil Pelajar Pancasila) belum diisi'];
        }

        // Cek ranah keterampilan
        if (empty($tp->tp_keterampilan)) {
            $issues[] = ['level' => 'info', 'msg' => 'Ranah keterampilan belum diisi'];
        }

        // Cek kode CP
        if (empty($tp->kode_cp)) {
            $issues[] = ['level' => 'info', 'msg' => 'Belum dikaitkan dengan Capaian Pembelajaran (CP)'];
        } else {
            // Validasi kode CP ke tabel master (jika tabel sudah ada)
            if ($this->db->tableExists('e_capaian_pembelajaran')) {
                $cpModel = new \App\Models\CapaianPembelajaranModel();
                if (!$cpModel->isValidKode((string) $tp->kode_cp)) {
                    $issues[] = [
                        'level' => 'warning',
                        'msg'   => "Kode CP '{$tp->kode_cp}' tidak ditemukan di master Capaian Pembelajaran BSKAP. "
                                   . 'Pastikan kode sesuai dokumen resmi atau tambahkan di menu Master CP.',
                    ];
                }
            }
        }

        // Cek KKTP
        if (empty($tp->kriteria_ketercapaian)) {
            $issues[] = ['level' => 'info', 'msg' => 'KKTP (Kriteria Ketercapaian TP) belum diisi'];
        }

        return $issues;
    }

    /**
     * Cek apakah TP menggunakan KKO yang valid
     */
    private function cekKataKerjaOperasional(string $tujuan): bool
    {
        $kkoList = [
            // C1
            'menyebutkan', 'mengidentifikasi', 'mendaftar', 'mengenali', 'memilih',
            // C2
            'menjelaskan', 'membandingkan', 'mengklasifikasikan', 'merangkum', 'menguraikan',
            'mendeskripsikan', 'menafsirkan',
            // C3
            'menghitung', 'menerapkan', 'menggunakan', 'melaksanakan', 'melakukan',
            'memecahkan', 'mengoperasikan',
            // C4
            'menganalisis', 'membedakan', 'mengorganisasikan', 'menghubungkan', 'menguraikan',
            'mengkategorikan', 'membandingkan',
            // C5
            'menilai', 'mengkritisi', 'menyimpulkan', 'membuktikan', 'mempertahankan',
            'mengevaluasi', 'mengkritik',
            // C6
            'merancang', 'menghasilkan', 'merumuskan', 'menciptakan', 'membangun',
            'mengembangkan', 'menyusun', 'membuat',
        ];

        $tujuanLower = mb_strtolower($tujuan);
        foreach ($kkoList as $kko) {
            if (str_starts_with($tujuanLower, $kko) || str_contains($tujuanLower, ' ' . $kko . ' ')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Duplikasi TP ke semester berbeda
     */
    public function duplikasiTpKeSemester(int $idTujuan, int $idSmtBaru): bool
    {
        $tp = $this->find($idTujuan);
        if (!$tp) return false;

        $data = (array) $tp;
        unset($data['id_tujuan'], $data['created_at'], $data['updated_at']);
        $data['id_smt'] = $idSmtBaru;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return (bool) $this->insert($data);
    }

    /**
     * Cek apakah TP sedang digunakan di Kartu Soal atau Bank Soal
     * Hanya menghitung kartu soal yang: tidak soft-deleted, workflow submitted/approved
     * Hanya menghitung soal yang: tidak soft-deleted
     */
    public function isUsed(int $id_tujuan): bool
    {
        $usedInKartu = 0;
        if ($this->db->tableExists('cbt_kartu_soal_item') && $this->db->tableExists('cbt_kartu_soal')) {
            $builder = $this->db->table('cbt_kartu_soal_item ksi');
            $builder->join('cbt_kartu_soal ks', 'ksi.kartu_id = ks.id_kartu');
            $builder->where('ksi.id_tujuan', $id_tujuan);
            $builder->where('ks.deleted_at IS NULL');
            if ($this->db->fieldExists('workflow_status', 'cbt_kartu_soal')) {
                $builder->whereIn('ks.workflow_status', ['submitted', 'approved']);
            }
            $usedInKartu = $builder->countAllResults();
        } elseif ($this->db->tableExists('cbt_kartu_soal_item')) {
            $usedInKartu = $this->db->table('cbt_kartu_soal_item')->where('id_tujuan', $id_tujuan)->countAllResults();
        }
        
        $usedInSoal = 0;
        if ($this->db->tableExists('cbt_soal') && $this->db->fieldExists('tujuan_id', 'cbt_soal')) {
            $builder = $this->db->table('cbt_soal');
            $builder->where('tujuan_id', $id_tujuan);
            if ($this->db->fieldExists('deleted_at', 'cbt_soal')) {
                $builder->where('deleted_at IS NULL');
            }
            $usedInSoal = $builder->countAllResults();
        }
        
        return ($usedInKartu > 0 || $usedInSoal > 0);
    }
}
