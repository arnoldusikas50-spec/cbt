<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikModel extends Model
{
    protected $table = 'cbt_praktik';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'tp_id', 'smt_id', 'mapel_id', 'kelas_id', 'judul', 'deskripsi',
        'instruksi', 'tujuan_pembelajaran', 'jenis_praktik', 'format_pengumpulan',
        'batasan_file', 'tanggal_mulai', 'tanggal_selesai', 'durasi_menit',
        'bobot', 'kkm', 'kktp_mode', 'aktifkan_penilaian_otomatis', 'aktifkan_anti_cheat',
        'batas_tab_browser', 'batas_copy_paste', 'batas_screenshot',
        'wajib_webcam', 'wajib_fullscreen', 'created_by', 'assessors', 'template_id',
        'capaian_pembelajaran_id', 'alur_tujuan_pembelajaran_id',
        'capaian_pembelajaran', 'alur_tujuan_pembelajaran',
        'tipe_instruksi', 'panduan_kreativitas', 'contoh_hasil', 'sumber_belajar', 'izinkan_kolaborasi',
        'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function initialize()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if ($db->tableExists($this->table)) {
            $fields = $db->getFieldNames($this->table);
            $newFields = [];
            if (!in_array('assessors', $fields)) {
                $newFields['assessors'] = ['type' => 'TEXT', 'null' => true, 'after' => 'created_by'];
            }
            if (!in_array('template_id', $fields)) {
                $newFields['template_id'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'assessors'];
            }
            if (!in_array('capaian_pembelajaran_id', $fields)) {
                $newFields['capaian_pembelajaran_id'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'template_id'];
            }
            if (!in_array('alur_tujuan_pembelajaran_id', $fields)) {
                $newFields['alur_tujuan_pembelajaran_id'] = ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'capaian_pembelajaran_id'];
            }
            if (!in_array('kktp_mode', $fields)) {
                $newFields['kktp_mode'] = ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'kkm', 'null' => false, 'after' => 'alur_tujuan_pembelajaran_id'];
            }
            if (!in_array('capaian_pembelajaran', $fields)) {
                $newFields['capaian_pembelajaran'] = ['type' => 'TEXT', 'null' => true, 'after' => 'kktp_mode'];
            }
            if (!in_array('alur_tujuan_pembelajaran', $fields)) {
                $newFields['alur_tujuan_pembelajaran'] = ['type' => 'TEXT', 'null' => true, 'after' => 'capaian_pembelajaran'];
            }
            if (!in_array('tipe_instruksi', $fields)) {
                $newFields['tipe_instruksi'] = ['type' => 'ENUM', 'constraint' => ['terbuka', 'tertutup'], 'default' => 'tertutup', 'after' => 'instruksi'];
            }
            if (!in_array('panduan_kreativitas', $fields)) {
                $newFields['panduan_kreativitas'] = ['type' => 'TEXT', 'null' => true, 'after' => 'tipe_instruksi'];
            }
            if (!in_array('contoh_hasil', $fields)) {
                $newFields['contoh_hasil'] = ['type' => 'TEXT', 'null' => true, 'after' => 'panduan_kreativitas'];
            }
            if (!in_array('sumber_belajar', $fields)) {
                $newFields['sumber_belajar'] = ['type' => 'TEXT', 'null' => true, 'after' => 'contoh_hasil'];
            }
            if (!in_array('izinkan_kolaborasi', $fields)) {
                $newFields['izinkan_kolaborasi'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'sumber_belajar'];
            }
            if (!empty($newFields)) {
                $forge->addColumn($this->table, $newFields);
            }
        }
    }

    /**
     * Get practical exam data with joins
     */
    public function getDataPraktik(?int $idGuru = null, ?int $idMapel = null, ?string $status = null)
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('
            a.*, 
            b.nama_mapel, 
            c.nama_guru,
            COUNT(DISTINCT h.id) as total_peserta,
            COUNT(DISTINCT CASE WHEN h.status_pengerjaan = "selesai" THEN h.id END) as total_selesai,
            COUNT(DISTINCT CASE WHEN h.status_penilaian = "sudah_dinilai" THEN h.id END) as total_dinilai
        ');
        $builder->join('master_mapel b', 'a.mapel_id = b.id_mapel', 'left');
        $builder->join('master_guru c', 'a.created_by = c.id_guru', 'left');
        $builder->join('cbt_praktik_hasil h', 'a.id = h.praktik_id', 'left');
        $builder->where('a.deleted_at IS NULL');
        $builder->groupBy('a.id');

        if ($idGuru !== null) {
            $builder->groupStart()
                    ->where('a.created_by', $idGuru)
                    ->orLike('a.assessors', '"' . $idGuru . '"')
                    ->groupEnd();
        }

        if ($idMapel !== null) {
            $builder->where('a.mapel_id', $idMapel);
        }

        if ($status !== null) {
            $builder->where('a.status', $status);
        }

        $query = $builder->orderBy('a.id', 'DESC')->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get single practical exam with details
     */
    public function getDetailPraktik(int $praktikId): ?object
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('
            a.*, 
            b.nama_mapel, 
            c.nama_guru,
            c.nip as nip_guru,
            c.email as email_guru
        ');
        $builder->join('master_mapel b', 'a.mapel_id = b.id_mapel', 'left');
        $builder->join('master_guru c', 'a.created_by = c.id_guru', 'left');
        $builder->where('a.id', $praktikId);
        $builder->where('a.deleted_at IS NULL');

        $result = $builder->get()->getRow();
        
        if ($result && $result->kelas_id) {
            $result->kelas_ids = $this->decodeKelasId($result->kelas_id);
        }

        if ($result && $result->assessors) {
            $result->assessor_ids = json_decode($result->assessors, true) ?: [];
        } else {
            $result->assessor_ids = [];
        }

        if ($result && $result->tujuan_pembelajaran) {
            $decodedTP = json_decode($result->tujuan_pembelajaran, true);
            $result->tujuan_pembelajaran_ids = is_array($decodedTP) ? $decodedTP : [$result->tujuan_pembelajaran];
        } else {
            $result->tujuan_pembelajaran_ids = [];
        }

        return $result;
    }

    /**
     * Get active practical exams for a student
     */
    public function getActivePraktikForSiswa(int $siswaId, int $kelasId): array
    {
        $now = date('Y-m-d H:i:s');

        $sql = "
            SELECT 
                p.*,
                m.nama_mapel,
                h.id as hasil_id,
                h.status_pengerjaan,
                h.status_penilaian,
                h.nilai_akhir,
                h.waktu_submit
            FROM {$this->table} p
            JOIN master_mapel m ON p.mapel_id = m.id_mapel
            LEFT JOIN cbt_praktik_hasil h ON p.id = h.praktik_id AND h.siswa_id = ?
            WHERE p.status IN ('aktif', 'sedang_berlangsung')
            AND (p.kelas_id LIKE ? OR p.kelas_id LIKE ?)
            AND p.tanggal_mulai <= ?
            AND p.tanggal_selesai >= ?
            AND p.deleted_at IS NULL
            ORDER BY p.tanggal_mulai ASC
        ";

        $kelasPattern1 = '%"' . $kelasId . '"%';
        $kelasPattern2 = '%' . $kelasId . ',%';

        $query = $this->db->query($sql, [$siswaId, $kelasPattern1, $kelasPattern2, $now, $now]);
        return $query ? $query->getResult() : [];
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(?int $tpId = null, ?int $smtId = null): object
    {
        $builder = $this->db->table($this->table);
        $builder->select('
            COUNT(*) as total_praktik,
            COUNT(CASE WHEN status = "aktif" THEN 1 END) as total_aktif,
            COUNT(CASE WHEN status = "sedang_berlangsung" THEN 1 END) as total_berlangsung,
            COUNT(CASE WHEN status = "selesai" THEN 1 END) as total_selesai
        ');
        $builder->where('deleted_at IS NULL');

        if ($tpId !== null) {
            $builder->where('tp_id', $tpId);
        }
        if ($smtId !== null) {
            $builder->where('smt_id', $smtId);
        }

        $query = $builder->get();
        return $query ? $query->getRow() : (object)[
            'total_praktik' => 0,
            'total_aktif' => 0,
            'total_berlangsung' => 0,
            'total_selesai' => 0
        ];
    }

    /**
     * Check if practical exam is available for student
     */
    public function isAvailableForSiswa(int $praktikId, int $kelasId, ?int $siswaId = null): array
    {
        $praktik = $this->find($praktikId);
        $now = date('Y-m-d H:i:s');
        
        $result = [
            'available' => false,
            'message' => '',
            'can_start' => false,
            'can_continue' => false
        ];

        if (!$praktik) {
            $result['message'] = 'Ujian praktik tidak ditemukan';
            return $result;
        }

        if ($praktik->deleted_at !== null) {
            $result['message'] = 'Ujian praktik telah dihapus';
            return $result;
        }

        if (!in_array($praktik->status, ['aktif', 'sedang_berlangsung'])) {
            $result['message'] = 'Ujian praktik belum aktif atau sudah selesai';
            return $result;
        }

        $kelasIds = $this->decodeKelasId($praktik->kelas_id);
        if (!in_array($kelasId, $kelasIds)) {
            $result['message'] = 'Anda tidak termasuk dalam kelas peserta';
            return $result;
        }

        if ($now < $praktik->tanggal_mulai) {
            $result['message'] = 'Ujian praktik belum dimulai';
            return $result;
        }

        if ($now > $praktik->tanggal_selesai) {
            $result['message'] = 'Ujian praktik sudah berakhir';
            return $result;
        }

        // Check existing submission
        if ($siswaId !== null) {
            $hasilModel = new CbtPraktikHasilModel();
            $hasil = $hasilModel->getHasilSiswa($praktikId, $siswaId);

            if ($hasil) {
                if ($hasil->status_pengerjaan === 'selesai') {
                    $result['message'] = 'Anda sudah menyelesaikan ujian ini';
                    return $result;
                }
                $result['can_continue'] = true;
                $result['hasil_id'] = $hasil->id;
            } else {
                $result['can_start'] = true;
            }
        }

        $result['available'] = true;
        $result['message'] = 'Ujian tersedia';
        return $result;
    }

    /**
     * Update status based on time
     */
    public function updateStatusByTime(): int
    {
        $now = date('Y-m-d H:i:s');

        // Activate exams
        $this->where('status', 'draft')
             ->where('tanggal_mulai <=', $now)
             ->where('tanggal_selesai >=', $now)
             ->set(['status' => 'aktif'])
             ->update();

        // Mark as ongoing when students start
        // This is handled when first student starts

        // Mark as ended
        $this->whereIn('status', ['aktif', 'sedang_berlangsung'])
             ->where('tanggal_selesai <', $now)
             ->set(['status' => 'selesai'])
             ->update();

        return $this->db->affectedRows();
    }

    /**
     * Get upcoming exams
     */
    public function getUpcomingExams(int $limit = 5): array
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('tanggal_mulai >', $now)
                    ->where('deleted_at IS NULL')
                    ->whereIn('status', ['draft', 'aktif'])
                    ->orderBy('tanggal_mulai', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Decode kelas_id from JSON or comma separated
     */
    public function decodeKelasId(?string $kelasId): array
    {
        if (empty($kelasId)) {
            return [];
        }

        $decoded = json_decode($kelasId, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return array_map('intval', explode(',', $kelasId));
    }
}
