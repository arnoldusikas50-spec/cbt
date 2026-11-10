<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikUmpanBalikModel extends Model
{
    protected $table = 'cbt_praktik_umpan_balik';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'hasil_id', 'praktik_id', 'siswa_id', 'dari_user_id',
        'tipe_umpan_balik', 'isi_umpan_balik', 'konteks',
        'telah_dibaca', 'dibalas_oleh_siswa', 'balasan_siswa',
        'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    /**
     * Kirim umpan balik konstruktif dari guru
     */
    public function kirimUmpanBalik(array $data): ?object
    {
        $data['telah_dibaca'] = 0;
        $data['dibalas_oleh_siswa'] = 0;
        
        $id = $this->insert($data);
        return $id ? $this->find($id) : null;
    }

    /**
     * Siswa membalas umpan balik
     */
    public function balasUmpanBalik(int $umpanBalikId, string $balasan): bool
    {
        return $this->update($umpanBalikId, [
            'balasan_siswa' => $balasan,
            'dibalas_oleh_siswa' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Tandai umpan balik telah dibaca siswa
     */
    public function tandaiDibaca(int $umpanBalikId): bool
    {
        return $this->update($umpanBalikId, [
            'telah_dibaca' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Ambil semua umpan balik untuk satu hasil
     */
    public function getUmpanBalikByHasil(int $hasilId): array
    {
        return $this->where('hasil_id', $hasilId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Ambil umpan balik yang belum dibaca siswa
     */
    public function getUmpanBalikBelumDibaca(int $hasilId): array
    {
        return $this->where('hasil_id', $hasilId)
            ->where('telah_dibaca', 0)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Hitung statistik umpan balik
     */
    public function getStatistikUmpanBalik(int $hasilId): object
    {
        $total = $this->where('hasil_id', $hasilId)->countAllResults();
        $dibaca = $this->where('hasil_id', $hasilId)->where('telah_dibaca', 1)->countAllResults();
        $dibalas = $this->where('hasil_id', $hasilId)->where('dibalas_oleh_siswa', 1)->countAllResults();

        return (object)[
            'total' => $total,
            'dibaca' => $dibaca,
            'belum_dibaca' => $total - $dibaca,
            'dibalas' => $dibalas
        ];
    }

    /**
     * Generate feedback template berdasarkan tipe
     */
    public function getTemplateFeedback(string $tipe): array
    {
        $templates = [
            'positif' => [
                'Bagus sekali! Cara kamu menyelesaikan ini menunjukkan pemahaman yang baik.',
                'Saya suka pendekatan yang kamu gunakan, sangat kreatif!',
                'Kerja bagus! Lanjutkan dengan gaya yang sama.'
            ],
            'perbaikan' => [
                'Coba perhatikan lagi bagian [area]. Mungkin bisa diperbaiki dengan [saran].',
                'Bagus progresnya! Untuk lebih baik, coba pertimbangkan [saran].',
                'Sudah bagus, tapi ada ruang untuk perbaikan di [area].'
            ],
            'pertanyaan' => [
                'Bisa jelaskan kenapa kamu memilih pendekatan ini?',
                'Apa pertimbanganmu saat memutuskan [langkah]?',
                'Bagaimana kalau kita coba [alternatif]?'
            ],
            'saran' => [
                'Coba eksplorasi [sumber] untuk referensi tambahan.',
                'Pertimbangkan untuk menambahkan [elemen] agar lebih lengkap.',
                'Mungkin bisa mencoba pendekatan [metode] untuk variasi.'
            ],
            'motivasi' => [
                'Kamu sudah di jalur yang benar, tetap semangat!',
                'Progressmu bagus, tinggal sedikit lagi!',
                'Yakinlah dengan kemampuanmu, kamu bisa!'
            ]
        ];

        return $templates[$tipe] ?? [];
    }
}
