<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikHasilModel extends Model
{
    protected $table = 'cbt_praktik_hasil';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'siswa_id', 'kelas_id', 'file_path', 'jawaban_teks',
        'url_simulasi', 'kode_jawaban', 'bahasa_pemrograman', 'waktu_mulai',
        'waktu_submit', 'durasi_pengerjaan_detik', 'status_pengerjaan',
        'status_penilaian', 'status_revisi', 'sisa_kesempatan_revisi',
        'batas_waktu_revisi', 'instruksi_revisi', 'mode_pengumpulan',
        'nilai_otomatis', 'nilai_manual', 'nilai_akhir', 'nilai_formatif', 'nilai_sumatif',
        'rincian_nilai_rubrik', 'feedback_guru', 'catatan_siswa',
        'status_plagiasi', 'persentase_plagiasi', 'ip_address', 'user_agent'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Status constants
    const STATUS_BELUM_MULAI = 'belum_mulai';
    const STATUS_SEDANG_MENGERJAKAN = 'sedang_mengerjakan';
    const STATUS_SELESAI = 'selesai';
    const STATUS_TERLAMBAT = 'terlambat';

    const STATUS_PENILAIAN_BELUM = 'belum_dinilai';
    const STATUS_PENILAIAN_SUDAH = 'sudah_dinilai';

    /**
     * Get submission with student details
     */
    public function getHasilByPraktik(int $praktikId, bool $withFiles = true): array
    {
        $sql = "
            SELECT 
                h.*,
                s.nama as nama_siswa,
                s.nis,
                k.nama_kelas,
                COUNT(DISTINCT l.id) as total_log_aktivitas,
                SUM(CASE WHEN l.tipe_aktivitas IN ('keluar_fullscreen', 'buka_tab_baru', 'copy_paste', 'pelanggaran_kecurangan') THEN 1 ELSE 0 END) as total_pelanggaran
            FROM {$this->table} h
            JOIN master_siswa s ON h.siswa_id = s.id_siswa
            LEFT JOIN master_kelas k ON h.kelas_id = k.id_kelas
            LEFT JOIN cbt_praktik_log l ON h.praktik_id = l.praktik_id AND h.siswa_id = l.siswa_id
            WHERE h.praktik_id = ?
            GROUP BY h.id
            ORDER BY s.nama ASC
        ";

        $query = $this->db->query($sql, [$praktikId]);
        $results = $query ? $query->getResult() : [];

        if ($withFiles && !empty($results)) {
            $fileModel = new CbtPraktikFileModel();
            foreach ($results as &$result) {
                $result->files = $fileModel->getFilesByHasil($result->id);
            }
        }

        return $results;
    }

    /**
     * Get single submission with all details
     */
    public function getHasilDetail(int $hasilId): ?object
    {
        $sql = "
            SELECT 
                h.*,
                s.nama as nama_siswa,
                s.nis,
                s.email,
                k.nama_kelas,
                p.judul as judul_praktik,
                p.jenis_praktik,
                p.mapel_id
            FROM {$this->table} h
            JOIN master_siswa s ON h.siswa_id = s.id_siswa
            LEFT JOIN master_kelas k ON h.kelas_id = k.id_kelas
            JOIN cbt_praktik p ON h.praktik_id = p.id
            WHERE h.id = ?
        ";

        $query = $this->db->query($sql, [$hasilId]);
        $result = $query ? $query->getRow() : null;

        if ($result) {
            $fileModel = new CbtPraktikFileModel();
            $result->files = $fileModel->getFilesByHasil($hasilId);

            if ($result->rincian_nilai_rubrik) {
                $result->rincian_nilai = json_decode($result->rincian_nilai_rubrik, true);
            }
        }

        return $result;
    }

    /**
     * Get submission by student and practical exam
     */
    public function getHasilSiswa(int $praktikId, int $siswaId): ?object
    {
        return $this->where(['praktik_id' => $praktikId, 'siswa_id' => $siswaId])->first();
    }

    /**
     * Start exam for student
     */
    public function mulaiUjian(int $praktikId, int $siswaId, int $kelasId): ?object
    {
        $existing = $this->getHasilSiswa($praktikId, $siswaId);
        
        if ($existing) {
            if ($existing->status_pengerjaan === self::STATUS_SELESAI) {
                return null; // Already finished
            }
            return $existing; // Continue existing
        }

        $request = service('request');
        $data = [
            'praktik_id' => $praktikId,
            'siswa_id' => $siswaId,
            'kelas_id' => $kelasId,
            'waktu_mulai' => date('Y-m-d H:i:s'),
            'status_pengerjaan' => self::STATUS_SEDANG_MENGERJAKAN,
            'status_penilaian' => self::STATUS_PENILAIAN_BELUM,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
        ];

        $id = $this->insert($data);
        return $this->find($id);
    }

    /**
     * Submit exam
     */
    public function submitUjian(int $hasilId, array $data): bool
    {
        $hasil = $this->find($hasilId);
        if (!$hasil || $hasil->status_pengerjaan === self::STATUS_SELESAI) {
            return false;
        }

        $waktuSubmit = date('Y-m-d H:i:s');
        $durasi = strtotime($waktuSubmit) - strtotime($hasil->waktu_mulai);

        $updateData = [
            'waktu_submit' => $waktuSubmit,
            'durasi_pengerjaan_detik' => $durasi,
            'status_pengerjaan' => self::STATUS_SELESAI,
        ];

        // Merge with submitted data
        $allowedFields = ['jawaban_teks', 'url_simulasi', 'kode_jawaban', 'bahasa_pemrograman', 'catatan_siswa'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        return $this->update($hasilId, $updateData);
    }

    /**
     * Save grading
     */
    public function simpanPenilaian(int $hasilId, array $data): bool
    {
        $updateData = [
            'nilai_manual' => $data['nilai_manual'] ?? null,
            'nilai_akhir' => $data['nilai_akhir'] ?? $data['nilai_manual'] ?? null,
            'rincian_nilai_rubrik' => isset($data['rincian_nilai']) ? json_encode($data['rincian_nilai']) : null,
            'feedback_guru' => $data['feedback'] ?? null,
            'status_penilaian' => self::STATUS_PENILAIAN_SUDAH,
        ];

        return $this->update($hasilId, $updateData);
    }

    /**
     * Get statistics for a practical exam
     */
    public function getStatistics(int $praktikId): object
    {
        $sql = "
            SELECT 
                COUNT(*) as total_peserta,
                COUNT(CASE WHEN status_pengerjaan = 'selesai' THEN 1 END) as total_selesai,
                COUNT(CASE WHEN status_pengerjaan = 'sedang_mengerjakan' THEN 1 END) as total_sedang_mengerjakan,
                COUNT(CASE WHEN status_penilaian = 'sudah_dinilai' THEN 1 END) as total_dinilai,
                AVG(nilai_akhir) as rata_nilai,
                MAX(nilai_akhir) as nilai_tertinggi,
                MIN(nilai_akhir) as nilai_terendah
            FROM {$this->table}
            WHERE praktik_id = ?
        ";

        $query = $this->db->query($sql, [$praktikId]);
        return $query ? $query->getRow() : (object)[];
    }

    /**
     * Get submissions for report
     */
    public function getDataForReport(int $praktikId): array
    {
        $sql = "
            SELECT 
                h.*,
                s.nama as nama_siswa,
                s.nis,
                s.jenis_kelamin as jk,
                k.nama_kelas,
                g.nama_guru,
                m.nama_mapel,
                p.judul,
                p.jenis_praktik,
                p.bobot,
                p.kkm
            FROM {$this->table} h
            JOIN master_siswa s ON h.siswa_id = s.id_siswa
            LEFT JOIN master_kelas k ON h.kelas_id = k.id_kelas
            JOIN cbt_praktik p ON h.praktik_id = p.id
            LEFT JOIN master_guru g ON p.created_by = g.id_guru
            LEFT JOIN master_mapel m ON p.mapel_id = m.id_mapel
            WHERE h.praktik_id = ?
            ORDER BY k.nama_kelas ASC, s.nama ASC
        ";

        $query = $this->db->query($sql, [$praktikId]);
        return $query ? $query->getResultArray() : [];
    }

    /**
     * Check plagiarism against other submissions
     */
    public function checkPlagiarism(int $praktikId, int $excludeSiswaId, string $text): array
    {
        $sql = "
            SELECT 
                h.id,
                h.siswa_id,
                h.jawaban_teks,
                s.nama as nama_siswa,
                k.nama_kelas
            FROM {$this->table} h
            JOIN master_siswa s ON h.siswa_id = s.id_siswa
            LEFT JOIN master_kelas k ON h.kelas_id = k.id_kelas
            WHERE h.praktik_id = ?
            AND h.siswa_id != ?
            AND h.jawaban_teks IS NOT NULL
            AND h.status_pengerjaan = 'selesai'
        ";

        $submissions = $this->db->query($sql, [$praktikId, $excludeSiswaId])->getResult();
        $results = [];

        foreach ($submissions as $submission) {
            $similarity = $this->calculateSimilarity($text, $submission->jawaban_teks);
            if ($similarity > 50) { // Only return if suspiciously similar
                $results[] = [
                    'hasil_id' => $submission->id,
                    'siswa_id' => $submission->siswa_id,
                    'nama_siswa' => $submission->nama_siswa,
                    'nama_kelas' => $submission->nama_kelas,
                    'similarity' => $similarity
                ];
            }
        }

        return $results;
    }

    /**
     * Calculate text similarity using similar_text
     */
    private function calculateSimilarity(string $text1, string $text2): float
    {
        if (empty($text1) || empty($text2)) {
            return 0;
        }

        $len1 = strlen($text1);
        $len2 = strlen($text2);
        
        if ($len1 === 0 || $len2 === 0) {
            return 0;
        }

        similar_text($text1, $text2, $percent);
        return round($percent, 2);
    }

    /**
     * Get progress for all students in practical exam
     */
    public function getProgressSiswa(int $praktikId, array $siswaIds): array
    {
        if (empty($siswaIds)) {
            return [];
        }

        $results = $this->where('praktik_id', $praktikId)
                       ->whereIn('siswa_id', $siswaIds)
                       ->findAll();

        $progress = [];
        foreach ($results as $result) {
            $progress[$result->siswa_id] = $result;
        }

        // Fill in missing students as "not started"
        foreach ($siswaIds as $siswaId) {
            if (!isset($progress[$siswaId])) {
                $progress[$siswaId] = (object) [
                    'status_pengerjaan' => self::STATUS_BELUM_MULAI,
                    'status_penilaian' => self::STATUS_PENILAIAN_BELUM,
                    'nilai_akhir' => null
                ];
            }
        }

        return $progress;
    }
}
