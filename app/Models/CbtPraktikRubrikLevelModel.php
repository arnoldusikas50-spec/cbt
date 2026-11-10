<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikRubrikLevelModel extends Model
{
    protected $table = 'cbt_praktik_rubrik_level';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'rubrik_id', 'level', 'nama_level', 'deskripsi',
        'nilai_min', 'nilai_max', 'indikator_otomatis'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all levels for a rubric ordered by level
     */
    public function getLevelsByRubrik(int $rubrikId): array
    {
        return $this->where('rubrik_id', $rubrikId)
                    ->orderBy('level', 'ASC')
                    ->findAll();
    }

    /**
     * Get level by rubric and level number
     */
    public function getLevelByNumber(int $rubrikId, int $level): ?object
    {
        return $this->where(['rubrik_id' => $rubrikId, 'level' => $level])->first();
    }

    /**
     * Get level that matches a score
     */
    public function getLevelByScore(int $rubrikId, float $score): ?object
    {
        return $this->where('rubrik_id', $rubrikId)
                    ->where('nilai_min <=', $score)
                    ->where('nilai_max >=', $score)
                    ->first();
    }

    /**
     * Delete all levels for a rubric
     */
    public function deleteByRubrik(int $rubrikId): bool
    {
        return $this->where('rubrik_id', $rubrikId)->delete();
    }

    /**
     * Copy levels from one rubric to another
     */
    public function copyLevels(int $sourceRubrikId, int $targetRubrikId): int
    {
        $levels = $this->getLevelsByRubrik($sourceRubrikId);
        $count = 0;

        foreach ($levels as $level) {
            $data = [
                'rubrik_id' => $targetRubrikId,
                'level' => $level->level,
                'nama_level' => $level->nama_level,
                'deskripsi' => $level->deskripsi,
                'nilai_min' => $level->nilai_min,
                'nilai_max' => $level->nilai_max,
                'indikator_otomatis' => $level->indikator_otomatis,
            ];
            if ($this->insert($data)) {
                $count++;
            }
        }

        return $count;
    }
}
