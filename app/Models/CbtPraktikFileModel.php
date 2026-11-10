<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikFileModel extends Model
{
    protected $table = 'cbt_praktik_file';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'hasil_id', 'praktik_id', 'siswa_id', 'nama_file',
        'nama_file_asli', 'tipe_file', 'ukuran_file',
        'path_file', 'keterangan', 'created_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    /**
     * Get files by submission result
     */
    public function getFilesByHasil(int $hasilId): array
    {
        return $this->where('hasil_id', $hasilId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Get files by student and practical exam
     */
    public function getFilesBySiswaPraktik(int $siswaId, int $praktikId): array
    {
        return $this->where('siswa_id', $siswaId)
                    ->where('praktik_id', $praktikId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Get total file size by submission
     */
    public function getTotalFileSize(int $hasilId): int
    {
        $result = $this->selectSum('ukuran_file')
                      ->where('hasil_id', $hasilId)
                      ->get()
                      ->getRow();
        return (int) ($result->ukuran_file ?? 0);
    }

    /**
     * Delete file record and return path for physical deletion
     */
    public function deleteFile(int $fileId): ?string
    {
        $file = $this->find($fileId);
        if (!$file) {
            return null;
        }

        $path = $file->path_file;
        $this->delete($fileId);

        return $path;
    }

    /**
     * Delete all files for a submission
     */
    public function deleteByHasil(int $hasilId): array
    {
        $files = $this->getFilesByHasil($hasilId);
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->path_file;
            $this->delete($file->id);
        }

        return $paths;
    }

    /**
     * Get file statistics for a practical exam
     */
    public function getFileStats(int $praktikId): object
    {
        $sql = "
            SELECT 
                COUNT(*) as total_files,
                COUNT(DISTINCT hasil_id) as total_pengumpulan,
                SUM(ukuran_file) as total_ukuran,
                AVG(ukuran_file) as rata_ukuran
            FROM {$this->table}
            WHERE praktik_id = ?
        ";

        return $this->db->query($sql, [$praktikId])->getRow();
    }

    /**
     * Format file size to human readable
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Validate file type
     */
    public static function validateFileType(string $filename, array $allowedExtensions): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions);
    }
}
