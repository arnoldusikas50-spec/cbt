<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikPortofolioModel extends Model
{
    protected $table = 'cbt_praktik_portofolio';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'hasil_id', 'praktik_id', 'siswa_id', 'judul_item', 'deskripsi_item',
        'tipe_item', 'path_item', 'thumbnail_path', 'urutan', 'is_highlight',
        'catatan_siswa', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    /**
     * Tambah item portofolio
     */
    public function tambahItem(array $data): ?object
    {
        $id = $this->insert($data);
        return $id ? $this->find($id) : null;
    }

    /**
     * Ambil semua item portofolio untuk satu hasil
     */
    public function getPortofolioByHasil(int $hasilId): array
    {
        return $this->where('hasil_id', $hasilId)
            ->orderBy('is_highlight', 'DESC')
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }

    /**
     * Update urutan item
     */
    public function updateUrutan(int $itemId, int $urutanBaru): bool
    {
        return $this->update($itemId, [
            'urutan' => $urutanBaru,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Toggle highlight item
     */
    public function toggleHighlight(int $itemId): bool
    {
        $item = $this->find($itemId);
        if (!$item) return false;

        return $this->update($itemId, [
            'is_highlight' => $item->is_highlight ? 0 : 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Hapus item portofolio
     */
    public function hapusItem(int $itemId): bool
    {
        return $this->delete($itemId);
    }

    /**
     * Generate thumbnail untuk file
     */
    public function generateThumbnail(string $filePath, string $tipeFile): ?string
    {
        // Untuk gambar, generate thumbnail kecil
        if (in_array($tipeFile, ['image/jpeg', 'image/png', 'image/gif'])) {
            // Implementasi thumbnail generation bisa ditambahkan di sini
            // atau menggunakan library Image Manipulation CodeIgniter
            return null; // Placeholder
        }

        return null;
    }
}
