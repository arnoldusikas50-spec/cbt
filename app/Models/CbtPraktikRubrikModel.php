<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikRubrikModel extends Model
{
    protected $table = 'cbt_praktik_rubrik';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'kode_kriteria', 'nama_kriteria', 'deskripsi',
        'bobot_persen', 'tipe_penilaian', 'urutan',
        'dimensi_penilaian', 'dimensi_ppp'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get rubrics by practical exam with levels
     */
    public function getRubrikPraktik(int $praktikId, bool $withLevels = true): array
    {
        $query = $this->where('praktik_id', $praktikId)
                       ->orderBy('urutan', 'ASC')
                       ->get();
        $rubrics = $query ? $query->getResult() : [];

        if ($withLevels && !empty($rubrics)) {
            $levelModel = new CbtPraktikRubrikLevelModel();
            foreach ($rubrics as &$rubrik) {
                $rubrik->levels = $levelModel->getLevelsByRubrik($rubrik->id);
            }
        }

        return $rubrics;
    }

    /**
     * Get single rubric with levels
     */
    public function getRubrikDetail(int $rubrikId): ?object
    {
        $rubrik = $this->find($rubrikId);
        if (!$rubrik) {
            return null;
        }

        $levelModel = new CbtPraktikRubrikLevelModel();
        $rubrik->levels = $levelModel->getLevelsByRubrik($rubrikId);

        return $rubrik;
    }

    /**
     * Get total bobot percentage for validation
     */
    public function getTotalBobot(int $praktikId): float
    {
        $result = $this->selectSum('bobot_persen')
                      ->where('praktik_id', $praktikId)
                      ->get()
                      ->getRow();

        return (float) ($result->bobot_persen ?? 0);
    }

    /**
     * Validate bobot total equals 100%
     */
    public function validateBobot(int $praktikId): array
    {
        $total = $this->getTotalBobot($praktikId);

        return [
            'valid' => $total == 100,
            'total' => $total,
            'difference' => 100 - $total
        ];
    }

    /**
     * Delete rubric and its levels
     */
    public function deleteRubrik(int $rubrikId): bool
    {
        $this->db->transStart();

        // Delete levels first
        $levelModel = new CbtPraktikRubrikLevelModel();
        $levelModel->deleteByRubrik($rubrikId);

        // Delete rubric
        $result = $this->delete($rubrikId);

        $this->db->transComplete();

        return $result && $this->db->transStatus();
    }

    /**
     * Delete all rubrics for a practical exam
     */
    public function deleteByPraktik(int $praktikId): bool
    {
        $this->db->transStart();
        
        $rubrics = $this->where('praktik_id', $praktikId)->findAll();
        $levelModel = new CbtPraktikRubrikLevelModel();
        
        foreach ($rubrics as $rubrik) {
            $levelModel->deleteByRubrik($rubrik->id);
            $this->delete($rubrik->id);
        }
        
        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Reorder rubrics
     */
    public function reorderRubrics(array $rubrikIds): bool
    {
        $this->db->transStart();

        foreach ($rubrikIds as $index => $rubrikId) {
            $this->update($rubrikId, ['urutan' => $index + 1]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Copy rubrics from one exam to another
     */
    public function copyRubrics(int $sourcePraktikId, int $targetPraktikId): int
    {
        $rubrics = $this->getRubrikPraktik($sourcePraktikId, true);
        $count = 0;

        $this->db->transStart();

        foreach ($rubrics as $rubrik) {
            $data = [
                'praktik_id' => $targetPraktikId,
                'kode_kriteria' => $rubrik->kode_kriteria,
                'nama_kriteria' => $rubrik->nama_kriteria,
                'deskripsi' => $rubrik->deskripsi,
                'bobot_persen' => $rubrik->bobot_persen,
                'tipe_penilaian' => $rubrik->tipe_penilaian,
                'urutan' => $rubrik->urutan,
            ];

            $newRubrikId = $this->insert($data);

            // Copy levels
            if (!empty($rubrik->levels)) {
                $levelModel = new CbtPraktikRubrikLevelModel();
                foreach ($rubrik->levels as $level) {
                    $levelData = [
                        'rubrik_id' => $newRubrikId,
                        'level' => $level->level,
                        'nama_level' => $level->nama_level,
                        'deskripsi' => $level->deskripsi,
                        'nilai_min' => $level->nilai_min,
                        'nilai_max' => $level->nilai_max,
                        'indikator_otomatis' => $level->indikator_otomatis,
                    ];
                    $levelModel->insert($levelData);
                }
            }

            $count++;
        }

        $this->db->transComplete();

        return $count;
    }

    /**
     * Get rubrics for auto-grading
     */
    public function getRubricsForAutoGrading(int $praktikId): array
    {
        return $this->where('praktik_id', $praktikId)
                    ->whereIn('tipe_penilaian', ['otomatis', 'hybrid'])
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }
}
