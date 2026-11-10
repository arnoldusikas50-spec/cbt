<?php

namespace App\Traits;

/**
 * SiswaAktifTrait - Helper untuk membatasi query agar hanya menyertakan
 * siswa yang berstatus AKTIF.
 *
 * Status siswa disimpan pada tabel `buku_induk.status` (buku induk = data
 * akademik siswa):
 *
 *   1 = AKTIF  -> boleh mengikuti ujian
 *   2 = LULUS  -> sudah lulus, tidak boleh ikut ujian
 *   3 = PINDAH -> pindah sekolah, tidak boleh ikut ujian
 *   4 = KELUAR -> keluar sekolah, tidak boleh ikut ujian
 *
 * `buku_induk.id_siswa` adalah PRIMARY KEY sehingga relasinya 1:1 dengan
 * `master_siswa` — join dengan filter status tidak pernah menggandakan baris.
 *
 * JOIN placed di ON-clause (bukan WHERE) supaya aman dipakai pada query
 * dengan LEFT JOIN: siswa tanpa baris `buku_induk` tetap bisa dikembalikan
 * lewat LEFT JOIN biasa, dan hanya yang berstatus non-AKTIF yang tersaring
 * ketika method ini dipakai.
 *
 * CATATAN PENTING:
 * - Gunakan method ini HANYA untuk daftar siswa yang boleh ujian / dinilai /
 *   dimonitor / dihitung hadir. Untuk keperluan administrasi (master data,
 *   import, roster kelas, rapor) JANGAN gunakan — agar admin tetap bisa
 *   melihat dan mengubah status siswa.
 */
trait SiswaAktifTrait
{
    /**
     * Batasi query ke siswa berstatus AKTIF saja.
     *
     * @param \CodeIgniter\Database\BaseBuilder $builder
     * @param string $alias Alias tabel yang Holds kolom id_siswa.
     *                        Contoh: 'a', 'ms', 'b', 's', 'cs'.
     * @param string $aliasBuku Alias untuk tabel buku_induk (default 'bi').
     * @param string $kolom Nama kolom yang mereferensensi id_siswa pada $alias.
     *                      Default 'id_siswa'; tabel `cbt_sesi_siswa` memakai
     *                      kolom 'siswa_id'.
     *
     * @return \CodeIgniter\Database\BaseBuilder
     */
    protected function onlySiswaAktif($builder, string $alias = 'a', string $aliasBuku = 'bi', string $kolom = 'id_siswa')
    {
        return $builder->join(
            'buku_induk ' . $aliasBuku,
            $aliasBuku . '.id_siswa = ' . $alias . '.' . $kolom . ' AND ' . $aliasBuku . '.status = "1"'
        );
    }

    /**
     * Daftar kode status siswa yang TIDAK boleh mengikuti ujian.
     *
     * @return array<int, string>
     */
    protected function statusSiswaTidakAktif(): array
    {
        return ['2', '3', '4'];
    }
}
