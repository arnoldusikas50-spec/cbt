<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikTemplateRubrikModel extends Model
{
    protected $table = 'cbt_praktik_template_rubrik';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'nama_template', 'deskripsi', 'jenis_praktik',
        'mapel_id', 'created_by', 'is_public'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get templates with filter
     * @param int|null $userId - User ID to filter by (null for admin = show all)
     * @param string|null $jenis - Filter by jenis_praktik
     * @param bool $includePublic - Whether to include public templates for non-admin
     */
    public function getTemplates(?int $userId = null, ?string $jenis = null, bool $includePublic = true): array
    {
        $db = \Config\Database::connect();
        
        $sql = "SELECT t.*, COUNT(i.id) as item_count, COALESCE(mg.nama_guru, 'Admin') as nama_guru 
                FROM cbt_praktik_template_rubrik t
                LEFT JOIN cbt_praktik_template_rubrik_item i ON t.id = i.template_id
                LEFT JOIN users u ON t.created_by = cast(u.id as signed)
                LEFT JOIN master_guru mg ON u.username = mg.username
                WHERE 1=1";
        
        $params = [];
        
        // Filter by jenis praktik if specified
        if ($jenis !== null && $jenis !== '') {
            $sql .= " AND (t.jenis_praktik = ? OR t.jenis_praktik = 'semua')";
            $params[] = $jenis;
        }
        
        // Filter by user permissions
        if ($userId !== null) {
            // Non-admin user: show their own templates + public templates
            $sql .= " AND (t.created_by = ? OR t.is_public = 1)";
            $params[] = $userId;
        }
        // If $userId is null (admin), no additional filter = show all non-deleted templates
        
        $sql .= " GROUP BY t.id ORDER BY t.nama_template ASC";
        
        try {
            $query = $db->query($sql, $params);
            return $query ? $query->getResult() : [];
        } catch (\Exception $e) {
            log_message('error', 'Error in getTemplates: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get template with items
     */
    public function getTemplateDetail(int $templateId): ?object
    {
        $template = $this->find($templateId);
        if (!$template) {
            return null;
        }

        // Load items via direct query to avoid model loading issues
        $db = \Config\Database::connect();
        $items = $db->table('cbt_praktik_template_rubrik_item')
                    ->where('template_id', $templateId)
                    ->orderBy('urutan', 'ASC')
                    ->get()
                    ->getResult();
        
        $template->items = $items ?: [];

        return $template;
    }

    /**
     * Get templates for a specific subject (includes public templates & null mapel templates)
     */
    public function getTemplatesByMapel(int $mapelId): array
    {
        $db = \Config\Database::connect();
        $sql = "SELECT t.*, COUNT(i.id) as item_count
                FROM cbt_praktik_template_rubrik t
                LEFT JOIN cbt_praktik_template_rubrik_item i ON t.id = i.template_id
                WHERE (t.mapel_id = ? OR t.mapel_id IS NULL OR t.is_public = 1)
                GROUP BY t.id
                ORDER BY t.nama_template ASC";
        $query = $db->query($sql, [$mapelId]);
        return $query ? $query->getResult() : [];
    }

    /**
     * Copy template to rubric for a practical exam
     */
    public function applyTemplate(int $templateId, int $praktikId): bool
    {
        $template = $this->getTemplateDetail($templateId);
        if (!$template || empty($template->items)) {
            return false;
        }

        $rubrikModel = new CbtPraktikRubrikModel();
        $levelModel = new CbtPraktikRubrikLevelModel();

        $this->db->transStart();

        try {
            foreach ($template->items as $item) {
                // Insert rubric
                $rubrikData = [
                    'praktik_id' => $praktikId,
                    'kode_kriteria' => $item->kode_kriteria,
                    'nama_kriteria' => $item->nama_kriteria,
                    'deskripsi' => $item->deskripsi,
                    'bobot_persen' => $item->bobot_persen,
                    'tipe_penilaian' => $item->tipe_penilaian,
                    'urutan' => $item->urutan,
                ];

                $rubrikId = $rubrikModel->insert($rubrikData);

                // Insert levels if exist
                if (!empty($item->level_detail)) {
                    $levels = json_decode($item->level_detail, true);
                    foreach ($levels as $level) {
                        $levelData = [
                            'rubrik_id' => $rubrikId,
                            'level' => $level['level'],
                            'nama_level' => $level['nama_level'],
                            'deskripsi' => $level['deskripsi'] ?? null,
                            'nilai_min' => $level['nilai_min'],
                            'nilai_max' => $level['nilai_max'],
                            'indikator_otomatis' => $level['indikator_otomatis'] ?? null,
                        ];
                        $levelModel->insert($levelData);
                    }
                }
            }

            $this->db->transComplete();
            return $this->db->transStatus();
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error applying template: ' . $e->getMessage());
            return false;
        }
    }
}

