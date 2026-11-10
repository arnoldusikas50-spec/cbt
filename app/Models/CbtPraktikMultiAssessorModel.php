<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk Multi-Assessor Penilaian
 * Menangani penilaian dari multiple assessor dan penggabungan nilai
 */
class CbtPraktikMultiAssessorModel extends Model
{
    protected $table = 'cbt_praktik_multi_assessor';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'hasil_id', 'praktik_id', 'siswa_id', 'assessor_id',
        'nilai_manual', 'rincian_nilai_rubrik', 'feedback',
        'bobot_penilai', 'status_penilaian', 'dinilai_oleh',
        'waktu_penilaian', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';

    // Metode penggabungan nilai
    const METODE_RATA_RATA = 'rata_rata';
    const METODE_BOBOT = 'bobot';
    const METODE_MEDIAN = 'median';
    const METODE_TERENDAH = 'terendah';
    const METODE_TERTINGGI = 'tertinggi';

    /**
     * Simpan penilaian dari seorang assessor
     */
    public function simpanPenilaian(
        int $hasilId,
        int $praktikId,
        int $siswaId,
        int $assessorId,
        float $nilai,
        array $rincianRubrik,
        ?string $feedback = null,
        float $bobotPenilai = 100.0
    ): bool {
        // Cek apakah sudah ada penilaian dari assessor ini
        $existing = $this->where([
            'hasil_id' => $hasilId,
            'assessor_id' => $assessorId
        ])->first();

        $data = [
            'praktik_id' => $praktikId,
            'siswa_id' => $siswaId,
            'nilai_manual' => $nilai,
            'rincian_nilai_rubrik' => json_encode($rincianRubrik),
            'feedback' => $feedback,
            'bobot_penilai' => $bobotPenilai,
            'status_penilaian' => self::STATUS_SUBMITTED,
            'waktu_penilaian' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            return $this->update($existing->id, $data);
        }

        $data['hasil_id'] = $hasilId;
        $data['assessor_id'] = $assessorId;

        return (bool) $this->insert($data);
    }

    /**
     * Get semua penilaian untuk sebuah hasil
     */
    public function getPenilaianByHasil(int $hasilId): array
    {
        $builder = $this->db->table($this->table . ' ma');
        $builder->select('ma.*, g.nama_guru, g.nip');
        $builder->join('master_guru g', 'ma.assessor_id = g.id_guru', 'left');
        $builder->where('ma.hasil_id', $hasilId);
        $builder->orderBy('ma.waktu_penilaian', 'DESC');

        $query = $builder->get();
        $results = $query ? $query->getResult() : [];

        // Decode rincian nilai
        foreach ($results as &$result) {
            $result->rincian = $result->rincian_nilai_rubrik
                ? json_decode($result->rincian_nilai_rubrik, true)
                : [];
        }

        return $results;
    }

    /**
     * Get jumlah assessor yang sudah menilai
     */
    public function getJumlahAssessor(int $hasilId): int
    {
        return $this->where('hasil_id', $hasilId)
                    ->where('status_penilaian !=', self::STATUS_DRAFT)
                    ->countAllResults();
    }

    /**
     * Get daftar assessor yang sudah menilai
     */
    public function getAssessorsByHasil(int $hasilId): array
    {
        $builder = $this->db->table($this->table . ' ma');
        $builder->select('ma.assessor_id, g.nama_guru, ma.status_penilaian, ma.waktu_penilaian');
        $builder->join('master_guru g', 'ma.assessor_id = g.id_guru', 'left');
        $builder->where('ma.hasil_id', $hasilId);
        $builder->orderBy('ma.waktu_penilaian', 'DESC');

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Hitung nilai akhir berdasarkan metode penggabungan
     */
    public function hitungNilaiAkhir(int $hasilId, string $metode = self::METODE_RATA_RATA): ?float
    {
        $penilaian = $this->getPenilaianByHasil($hasilId);

        if (empty($penilaian)) {
            return null;
        }

        $nilaiList = array_map(fn($p) => (float)$p->nilai_manual, $penilaian);
        $bobotList = array_map(fn($p) => (float)$p->bobot_penilai, $penilaian);

        switch ($metode) {
            case self::METODE_RATA_RATA:
                return round(array_sum($nilaiList) / count($nilaiList), 2);

            case self::METODE_BOBOT:
                $totalBobot = array_sum($bobotList);
                if ($totalBobot == 0) return null;
                $weightedSum = 0;
                foreach ($nilaiList as $i => $nilai) {
                    $weightedSum += $nilai * ($bobotList[$i] / $totalBobot);
                }
                return round($weightedSum, 2);

            case self::METODE_MEDIAN:
                sort($nilaiList);
                $count = count($nilaiList);
                $middle = floor(($count - 1) / 2);
                if ($count % 2) {
                    return $nilaiList[$middle];
                }
                return round(($nilaiList[$middle] + $nilaiList[$middle + 1]) / 2, 2);

            case self::METODE_TERENDAH:
                return min($nilaiList);

            case self::METODE_TERTINGGI:
                return max($nilaiList);

            default:
                return round(array_sum($nilaiList) / count($nilaiList), 2);
        }
    }

    /**
     * Merge rincian nilai rubrik dari multiple assessor
     */
    public function mergeRincianNilai(int $hasilId, string $metode = self::METODE_RATA_RATA): array
    {
        $penilaian = $this->getPenilaianByHasil($hasilId);

        if (empty($penilaian)) {
            return [];
        }

        $merged = [];
        $rubrikIds = [];

        // Collect all rubrik IDs
        foreach ($penilaian as $p) {
            if (!empty($p->rincian)) {
                $rubrikIds = array_merge($rubrikIds, array_keys($p->rincian));
            }
        }
        $rubrikIds = array_unique($rubrikIds);

        // Calculate merged values per rubrik
        foreach ($rubrikIds as $rubrikId) {
            $nilaiRubrik = [];
            $catatanList = [];

            foreach ($penilaian as $p) {
                if (!empty($p->rincian[$rubrikId])) {
                    $nilaiRubrik[] = (float)($p->rincian[$rubrikId]['nilai'] ?? 0);
                    if (!empty($p->rincian[$rubrikId]['catatan'])) {
                        $catatanList[] = $p->rincian[$rubrikId]['catatan'];
                    }
                }
            }

            if (empty($nilaiRubrik)) continue;

            switch ($metode) {
                case self::METODE_RATA_RATA:
                    $merged[$rubrikId] = [
                        'nilai' => round(array_sum($nilaiRubrik) / count($nilaiRubrik), 2),
                        'catatan' => implode(' | ', array_unique($catatanList)),
                        'penilai_count' => count($nilaiRubrik)
                    ];
                    break;

                case self::METODE_MEDIAN:
                    sort($nilaiRubrik);
                    $count = count($nilaiRubrik);
                    $median = ($count % 2)
                        ? $nilaiRubrik[floor($count / 2)]
                        : ($nilaiRubrik[$count / 2 - 1] + $nilaiRubrik[$count / 2]) / 2;
                    $merged[$rubrikId] = [
                        'nilai' => round($median, 2),
                        'catatan' => implode(' | ', array_unique($catatanList)),
                        'penilai_count' => $count
                    ];
                    break;

                default:
                    $merged[$rubrikId] = [
                        'nilai' => round(array_sum($nilaiRubrik) / count($nilaiRubrik), 2),
                        'catatan' => implode(' | ', array_unique($catatanList)),
                        'penilai_count' => count($nilaiRubrik)
                    ];
            }
        }

        return $merged;
    }

    /**
     * Verifikasi penilaian dari assessor
     */
    public function verifikasiPenilaian(int $id, int $verifierId, bool $approve = true, ?string $alasan = null): bool
    {
        $data = [
            'status_penilaian' => $approve ? self::STATUS_VERIFIED : self::STATUS_REJECTED,
            'dinilai_oleh' => 'Verifikasi oleh ID: ' . $verifierId . ($alasan ? ' - Alasan: ' . $alasan : ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($id, $data);
    }

    /**
     * Check apakah assessor sudah menilai
     */
    public function sudahMenilai(int $hasilId, int $assessorId): bool
    {
        return $this->where([
            'hasil_id' => $hasilId,
            'assessor_id' => $assessorId
        ])->where('status_penilaian !=', self::STATUS_DRAFT)
          ->countAllResults() > 0;
    }

    /**
     * Get statistik penilaian per praktik
     */
    public function getStatistikPraktik(int $praktikId): object
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT ma.hasil_id) as total_dinilai,
                COUNT(DISTINCT ma.assessor_id) as total_assessor,
                AVG(ma.nilai_manual) as rata_rata_nilai,
                COUNT(CASE WHEN ma.status_penilaian = 'verified' THEN 1 END) as total_verified,
                COUNT(CASE WHEN ma.status_penilaian = 'submitted' THEN 1 END) as total_pending
            FROM {$this->table} ma
            WHERE ma.praktik_id = ?
        ";

        $query = $this->db->query($sql, [$praktikId]);
        $result = $query ? $query->getRow() : null;

        return $result ?: (object)[
            'total_dinilai' => 0,
            'total_assessor' => 0,
            'rata_rata_nilai' => 0,
            'total_verified' => 0,
            'total_pending' => 0
        ];
    }

    /**
     * Get summary penilaian untuk dashboard assessor
     */
    public function getAssessorSummary(int $assessorId, ?int $tpId = null, ?int $smtId = null): object
    {
        $builder = $this->db->table($this->table . ' ma');
        $builder->select('
            COUNT(*) as total_penilaian,
            COUNT(DISTINCT ma.praktik_id) as total_praktik,
            COUNT(DISTINCT ma.hasil_id) as total_siswa,
            AVG(ma.nilai_manual) as rata_rata_nilai,
            MAX(ma.waktu_penilaian) as terakhir_menilai
        ');
        $builder->join('cbt_praktik p', 'ma.praktik_id = p.id', 'left');
        $builder->where('ma.assessor_id', $assessorId);

        if ($tpId) {
            $builder->where('p.tp_id', $tpId);
        }
        if ($smtId) {
            $builder->where('p.smt_id', $smtId);
        }

        $query = $builder->get();
        $result = $query ? $query->getRow() : null;

        return $result ?: (object)[
            'total_penilaian' => 0,
            'total_praktik' => 0,
            'total_siswa' => 0,
            'rata_rata_nilai' => 0,
            'terakhir_menilai' => null
        ];
    }

    /**
     * Get daftar siswa yang belum dinilai oleh assessor tertentu
     */
    public function getBelumDinilai(int $praktikId, int $assessorId): array
    {
        $sql = "
            SELECT 
                h.id as hasil_id,
                h.siswa_id,
                s.nama as nama_siswa,
                s.nis,
                k.nama_kelas
            FROM cbt_praktik_hasil h
            JOIN master_siswa s ON h.siswa_id = s.id_siswa
            LEFT JOIN master_kelas k ON h.kelas_id = k.id_kelas
            LEFT JOIN {$this->table} ma ON h.id = ma.hasil_id AND ma.assessor_id = ?
            WHERE h.praktik_id = ?
            AND h.status_pengerjaan = 'selesai'
            AND ma.id IS NULL
            ORDER BY s.nama ASC
        ";

        $query = $this->db->query($sql, [$assessorId, $praktikId]);
        return $query ? $query->getResult() : [];
    }

    /**
     * Get progress penilaian per praktik
     */
    public function getProgressPenilaian(int $praktikId): array
    {
        // Total siswa yang selesai
        $hasilModel = new CbtPraktikHasilModel();
        $totalSelesai = $hasilModel->where([
            'praktik_id' => $praktikId,
            'status_pengerjaan' => 'selesai'
        ])->countAllResults();

        // Total yang sudah dinilai
        $sql = "
            SELECT COUNT(DISTINCT hasil_id) as total_dinilai
            FROM {$this->table}
            WHERE praktik_id = ?
            AND status_penilaian != 'draft'
        ";
        $query = $this->db->query($sql, [$praktikId]);
        $totalDinilai = $query ? $query->getRow()->total_dinilai : 0;

        return [
            'total_selesai' => $totalSelesai,
            'total_dinilai' => $totalDinilai,
            'persen_dinilai' => $totalSelesai > 0 ? round(($totalDinilai / $totalSelesai) * 100, 2) : 0,
            'belum_dinilai' => $totalSelesai - $totalDinilai
        ];
    }
}
