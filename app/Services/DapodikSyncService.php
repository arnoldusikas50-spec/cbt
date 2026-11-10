<?php

namespace App\Services;

use App\Libraries\DapodikClient;
use Exception;
use ZipArchive;

/**
 * DapodikSyncService — Layanan pemutakhiran & penyelarasan data Dapodik dengan database aplikasi (cbt-ikas).
 * Disesuaikan untuk skema master_siswa, master_guru, master_kelas, master_mapel.
 */
class DapodikSyncService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ── SYNC ALL ─────────────────────────────────────────────────────────────

    /**
     * Sinkronisasi Seluruh Data dari API Dapodik
     */
    public function syncAllFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $resSekolah      = $this->syncSekolahFromApi($client, $userId);
        $resGtk          = $this->syncGtkFromApi($client, $userId);
        $resSiswa        = $this->syncSiswaFromApi($client, $userId);
        $resRombel       = $this->syncRombelFromApi($client, $userId);
        $resMapel        = $this->syncMapelFromApi($client, $userId);
        $resPembelajaran = $this->syncPembelajaranFromApi($client, $userId);
        $resRuang        = $this->syncRuangFromApi($client, $userId);

        $totalProcessed = ($resSekolah['processed'] ?? 0) + ($resGtk['processed'] ?? 0) + ($resSiswa['processed'] ?? 0) + ($resRombel['processed'] ?? 0) + ($resMapel['processed'] ?? 0) + ($resPembelajaran['processed'] ?? 0) + ($resRuang['processed'] ?? 0);
        $totalAdded     = ($resSekolah['added'] ?? 0) + ($resGtk['added'] ?? 0) + ($resSiswa['added'] ?? 0) + ($resRombel['added'] ?? 0) + ($resMapel['added'] ?? 0) + ($resPembelajaran['added'] ?? 0) + ($resRuang['added'] ?? 0);
        $totalUpdated   = ($resSekolah['updated'] ?? 0) + ($resGtk['updated'] ?? 0) + ($resSiswa['updated'] ?? 0) + ($resRombel['updated'] ?? 0) + ($resMapel['updated'] ?? 0) + ($resPembelajaran['updated'] ?? 0) + ($resRuang['updated'] ?? 0);

        $allSuccess = ($resSekolah['status'] ?? false) && ($resGtk['status'] ?? false) && ($resSiswa['status'] ?? false) && ($resRombel['status'] ?? false) && ($resMapel['status'] ?? false) && ($resPembelajaran['status'] ?? false) && ($resRuang['status'] ?? false);

        $failed = [];
        if (!($resSekolah['status'] ?? false)) $failed[] = 'Sekolah';
        if (!($resGtk['status'] ?? false)) $failed[] = 'GTK';
        if (!($resSiswa['status'] ?? false)) $failed[] = 'Siswa';
        if (!($resRombel['status'] ?? false)) $failed[] = 'Rombel';
        if (!($resMapel['status'] ?? false)) $failed[] = 'Mapel';
        if (!($resPembelajaran['status'] ?? false)) $failed[] = 'Pembelajaran';
        if (!($resRuang['status'] ?? false)) $failed[] = 'Ruang';

        if ($this->db->tableExists('dapodik_settings')) {
            $this->db->table('dapodik_settings')->where('id >', 0)->update([
                'last_sync_at' => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        $msg = "Sinkronisasi lengkap berhasil. {$totalProcessed} total data diproses ({$totalAdded} baru, {$totalUpdated} diperbarui).";
        if (!empty($failed)) {
            $msg .= " Gagal pada: " . implode(', ', $failed);
        }

        $this->writeLog('all', 'api', $allSuccess ? 'sukses' : 'parsial', $totalProcessed, $totalAdded, $totalUpdated, $msg, $userId);

        return [
            'status'       => $allSuccess,
            'message'      => $msg,
            'sekolah'      => $resSekolah,
            'gtk'          => $resGtk,
            'siswa'        => $resSiswa,
            'rombel'       => $resRombel,
            'mapel'        => $resMapel,
            'pembelajaran' => $resPembelajaran,
            'ruang'        => $resRuang,
            'summary'      => [
                'total_processed' => $totalProcessed,
                'total_added'     => $totalAdded,
                'total_updated'   => $totalUpdated,
            ],
        ];
    }

    // ── SYNC SEKOLAH ────────────────────────────────────────────────────────

    public function syncSekolahFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $response = $client->getSekolah();
        if (!$response['status'] || empty($response['data'])) {
            $msg = $response['message'] ?? 'Tidak ada data sekolah dari API Dapodik.';
            $this->writeLog('sekolah', 'api', 'gagal', 0, 0, 0, $msg, $userId);
            return ['status' => false, 'message' => $msg, 'processed' => 0, 'added' => 0, 'updated' => 0];
        }

        $data = $response['data'];
        if (isset($data['rows']) && is_array($data['rows'])) {
            $data = $data['rows'];
        }

        $mapping = [
            'sekolah'    => $data['nama'] ?? '',
            'npsn'       => $data['npsn'] ?? '',
            'alamat'     => $data['alamat_jalan'] ?? '',
            'desa'       => $data['desa_kelurahan'] ?? '',
            'kecamatan'  => str_replace('Kec. ', '', $data['kecamatan'] ?? ''),
            'kota'       => str_replace('Kab. ', '', $data['kabupaten_kota'] ?? ''),
            'provinsi'   => str_replace('Prov. ', '', $data['provinsi'] ?? ''),
            'kode_pos'   => $data['kode_pos'] ?? '',
            'telp'       => $data['nomor_telepon'] ?? '',
            'email'      => $data['email'] ?? '',
            'web'        => $data['website'] ?? '',
        ];

        // Tabel `setting` memakai kolom biasa (sekolah, npsn, dst.), bukan store key/value.
        $updated = 0;
        if ($this->db->tableExists('setting')) {
            $clean = [];
            foreach ($mapping as $column => $value) {
                if (empty($value) || !$this->db->fieldExists($column, 'setting')) continue;
                $clean[$column] = $value;
            }

            if (!empty($clean)) {
                $row = $this->db->table('setting')->orderBy('id_setting', 'ASC')->get()->getRowArray();
                if ($row) {
                    $this->db->table('setting')->where('id_setting', $row['id_setting'])->update($clean);
                } else {
                    $this->db->table('setting')->insert($clean);
                }
                $updated = count($clean);

                // Clear cache setting setelah sinkronisasi menulis tabel setting
                \Config\Services::cache()->delete('dashboard_setting');
                \Config\Services::cache()->delete('app_settings');
            }
        }

        $msg = "Data sekolah berhasil disinkronkan dari Dapodik. NPSN: " . ($mapping['npsn'] ?? 'N/A');
        $this->writeLog('sekolah', 'api', 'sukses', count($mapping), 0, $updated, $msg, $userId);
        $this->markLastSync();

        return ['status' => true, 'message' => $msg, 'processed' => count($mapping), 'added' => 0, 'updated' => $updated];
    }

    // ── SYNC GTK (GURU) ─────────────────────────────────────────────────────

    public function syncGtkFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $response = $client->getGtk();
        if (!$response['status'] || empty($response['data'])) {
            $msg = $response['message'] ?? 'Tidak ada data GTK dari API Dapodik.';
            $this->writeLog('gtk', 'api', 'gagal', 0, 0, 0, $msg, $userId);
            return ['status' => false, 'message' => $msg, 'processed' => 0, 'added' => 0, 'updated' => 0];
        }
        $result = $this->processGtkItems($response['data'], 'api', $userId);
        if (!empty($result['status'])) {
            $this->markLastSync();
        }
        return $result;
    }

    public function processGtkItems(array $items, string $metode = 'api', ?int $userId = null): array
    {
        $processed = 0;
        $added     = 0;
        $updated   = 0;

        foreach ($items as $item) {
            $processed++;
            try {
                $dapoId = trim($item['ptk_terdaftar_id'] ?? $item['ptk_id'] ?? '');
                $ptkId  = trim($item['ptk_id'] ?? '');
                $nama   = trim($item['nama'] ?? $item['nama_lengkap'] ?? '');
                $nuptk  = trim($item['nuptk'] ?? '');
                $nik    = trim($item['nik'] ?? '');
                $nip    = trim($item['nip'] ?? '');

                if (empty($nama)) continue;

                $existing = null;
                if (!empty($dapoId)) {
                    $existing = $this->db->table('master_guru')->where('dapo_id', $dapoId)->get()->getRowArray();
                }
                if (!$existing && !empty($ptkId)) {
                    $existing = $this->db->table('master_guru')->where('ptk_id', $ptkId)->get()->getRowArray();
                }
                if (!$existing && !empty($nuptk)) {
                    $existing = $this->db->table('master_guru')
                        ->groupStart()
                            ->where('nuptk', $nuptk)
                            ->orWhere('nip', $nuptk)
                            ->orWhere('username', $nuptk)
                        ->groupEnd()
                        ->get()->getRowArray();
                }
                if (!$existing && !empty($nip)) {
                    $existing = $this->db->table('master_guru')
                        ->groupStart()
                            ->where('nip', $nip)
                            ->orWhere('nuptk', $nip)
                            ->orWhere('username', $nip)
                        ->groupEnd()
                        ->get()->getRowArray();
                }
                if (!$existing && !empty($nik)) {
                    $existing = $this->db->table('master_guru')
                        ->groupStart()
                            ->where('no_ktp', $nik)
                            ->orWhere('nip', $nik)
                            ->orWhere('nuptk', $nik)
                        ->groupEnd()
                        ->get()->getRowArray();
                }
                if (!$existing) {
                    $existing = $this->db->table('master_guru')->where('nama_guru', $nama)->get()->getRowArray();
                }
                if (!$existing) {
                    $cleanIncoming = $this->cleanTeacherName($nama);
                    if (!empty($cleanIncoming)) {
                        $allGurus = $this->db->table('master_guru')->get()->getResultArray();
                        $bestMatch = null;
                        $highestSim = 0;
                        foreach ($allGurus as $gRow) {
                            $cleanDb = $this->cleanTeacherName($gRow['nama_guru'] ?? '');
                            if (empty($cleanDb)) continue;
                            if ($cleanIncoming === $cleanDb || strpos($cleanDb, $cleanIncoming) !== false || strpos($cleanIncoming, $cleanDb) !== false) {
                                $bestMatch = $gRow;
                                break;
                            }
                            similar_text($cleanIncoming, $cleanDb, $sim);
                            if ($sim > 70 && $sim > $highestSim) {
                                $highestSim = $sim;
                                $bestMatch = $gRow;
                            }
                        }
                        if ($bestMatch) {
                            $existing = $bestMatch;
                        }
                    }
                }

                $gender = strtoupper(trim($item['jenis_kelamin'] ?? 'L'));
                if (!in_array($gender, ['L', 'P'])) $gender = 'L';

                // Map all available GTK fields from Dapodik payload
                $tempatLahir     = trim($item['tempat_lahir'] ?? '');
                $tanggalLahir    = trim($item['tanggal_lahir'] ?? $item['tgl_lahir'] ?? '');
                $agama           = $this->parseAgama($item);
                $email           = trim($item['email'] ?? '');
                $noHp            = trim($item['nomor_telepon_seluler'] ?? $item['no_hp'] ?? $item['telepon'] ?? $item['hp'] ?? '');
                $alamatJalan     = trim($item['alamat_jalan'] ?? $item['alamat'] ?? '');
                $rt              = trim($item['rt'] ?? '');
                $rw              = trim($item['rw'] ?? '');
                $rtRw            = (!empty($rt) || !empty($rw)) ? trim($rt . '/' . $rw, '/') : trim($item['rt_rw'] ?? '');
                $dusun           = trim($item['dusun'] ?? '');
                $kelurahan       = trim($item['desa_kelurahan'] ?? $item['kelurahan'] ?? '');
                $kecamatan       = trim($item['kecamatan'] ?? '');
                $kabupaten       = trim($item['kabupaten_kota'] ?? $item['kabupaten'] ?? '');
                $provinsi        = trim($item['provinsi'] ?? '');
                $kodePos         = trim($item['kode_pos'] ?? '');
                $kewarganegaraan  = trim($item['kewarganegaraan'] ?? $item['warga_negara'] ?? '');

                $jenisPtk        = trim($item['jenis_ptk_id_str'] ?? $item['jenis_ptk'] ?? '');
                $tgsTambahan     = trim($item['tugas_tambahan_id_str'] ?? $item['tgs_tambahan'] ?? '');
                $statusPegawai   = trim($item['status_kepegawaian_id_str'] ?? $item['status_pegawai'] ?? '');
                $statusAktif     = trim($item['status_aktif'] ?? $item['status_keaktifan_id_str'] ?? '');
                $statusNikah     = trim($item['status_perkawinan'] ?? $item['status_nikah'] ?? '');
                $tmt             = trim($item['tmt_pengangkatan'] ?? $item['tmt'] ?? '');
                $npwp            = trim($item['npwp'] ?? '');

                $baseUsername = !empty($nip) ? $nip : (!empty($nuptk) ? $nuptk : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)));
                if (empty($baseUsername)) $baseUsername = 'guru' . rand(100, 999);

                $existingId = $existing['id_guru'] ?? 0;
                $username   = $baseUsername;
                $counter    = 1;
                while ($this->db->table('master_guru')->where('username', $username)->where('id_guru !=', $existingId)->countAllResults() > 0) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $gelarDepan    = trim($item['gelar_depan'] ?? '');
                $gelarBelakang = trim($item['gelar_belakang'] ?? '');

                $namaWithGelar = $nama;
                if (!empty($gelarDepan) && !str_starts_with($namaWithGelar, $gelarDepan)) {
                    $namaWithGelar = $gelarDepan . ' ' . $namaWithGelar;
                }
                if (!empty($gelarBelakang) && !str_contains($namaWithGelar, $gelarBelakang)) {
                    $namaWithGelar = $namaWithGelar . ', ' . $gelarBelakang;
                }

                // Preserve existing academic degrees if incoming name from Dapodik payload lacks titles
                if ($existing && !empty($existing['nama_guru'])) {
                    if (strpos($existing['nama_guru'], ',') !== false && strpos($namaWithGelar, ',') === false) {
                        $degreePart = trim(substr($existing['nama_guru'], strpos($existing['nama_guru'], ',')));
                        $namaWithGelar = $namaWithGelar . ', ' . ltrim($degreePart, ', ');
                    }
                }

                $data = [
                    'dapo_id'       => !empty($dapoId) ? $dapoId : ($existing['dapo_id'] ?? null),
                    'ptk_id'        => !empty($ptkId) ? $ptkId : ($existing['ptk_id'] ?? null),
                    'nama_guru'     => $namaWithGelar,
                    'nuptk'         => !empty($nuptk) ? $nuptk : ($existing['nuptk'] ?? ''),
                    'nip'           => !empty($nip) ? $nip : ($existing['nip'] ?? ''),
                    'jenis_kelamin' => $gender,
                ];

                if (!empty($nik))             $data['no_ktp']          = $nik;
                if (!empty($tempatLahir))     $data['tempat_lahir']    = $tempatLahir;
                if (!empty($tanggalLahir))    $data['tgl_lahir']       = $tanggalLahir;
                if (!empty($agama))           $data['agama']           = $agama;
                if (!empty($email))           $data['email']           = $email;
                if (!empty($noHp))            $data['no_hp']           = $noHp;
                if (!empty($alamatJalan))     $data['alamat_jalan']    = $alamatJalan;
                if (!empty($rtRw))            $data['rt_rw']           = $rtRw;
                if (!empty($dusun))           $data['dusun']           = $dusun;
                if (!empty($kelurahan))       $data['kelurahan']       = $kelurahan;
                if (!empty($kecamatan))       $data['kecamatan']       = $kecamatan;
                if (!empty($kabupaten))       $data['kabupaten']       = $kabupaten;
                if (!empty($provinsi))        $data['provinsi']        = $provinsi;
                if (!empty($kodePos))         $data['kode_pos']        = (int) $kodePos;
                if (!empty($kewarganegaraan)) $data['kewarganegaraan'] = $kewarganegaraan;

                if (!empty($jenisPtk))        $data['jenis_ptk']       = $jenisPtk;
                if (!empty($tgsTambahan))     $data['tgs_tambahan']    = $tgsTambahan;
                if (!empty($statusPegawai))   $data['status_pegawai']  = $statusPegawai;
                if (!empty($statusAktif))     $data['status_aktif']    = $statusAktif;
                if (!empty($statusNikah))     $data['status_nikah']    = $statusNikah;
                if (!empty($tmt))             $data['tmt']             = $tmt;
                if (!empty($npwp))            $data['npwp']            = $npwp;

                if ($existing) {
                    if (isset($existing['password']) && str_starts_with($existing['password'], '$2y$')) {
                        $data['password'] = $existing['username'] ?? $username;
                    }
                    $this->db->table('master_guru')->where('id_guru', $existing['id_guru'])->update($data);
                    $updated++;
                } else {
                    $data['username'] = $username;
                    $data['password'] = $username;
                    $this->db->table('master_guru')->insert($data);
                    $added++;
                }
            } catch (\Throwable $e) {
                log_message('error', 'DapodikSync GTK Error: ' . $e->getMessage());
            }
        }

        $msg = "Berhasil memproses {$processed} data GTK ({$added} ditambah, {$updated} diperbarui).";
        $this->writeLog('gtk', $metode, 'sukses', $processed, $added, $updated, $msg, $userId);

        return ['status' => true, 'message' => $msg, 'processed' => $processed, 'added' => $added, 'updated' => $updated];
    }

    // ── SYNC SISWA ──────────────────────────────────────────────────────────

    public function syncSiswaFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $response = $client->getPesertaDidik();
        if (!$response['status'] || empty($response['data'])) {
            $msg = $response['message'] ?? 'Tidak ada data Peserta Didik dari API Dapodik.';
            $this->writeLog('siswa', 'api', 'gagal', 0, 0, 0, $msg, $userId);
            return ['status' => false, 'message' => $msg, 'processed' => 0, 'added' => 0, 'updated' => 0];
        }
        $result = $this->processSiswaItems($response['data'], 'api', $userId);
        if (!empty($result['status'])) {
            $this->markLastSync();
        }
        return $result;
    }

    public function processSiswaItems(array $items, string $metode = 'api', ?int $userId = null): array
    {
        $processed = 0;
        $added     = 0;
        $updated   = 0;

        foreach ($items as $item) {
            $processed++;
            try {
                $dapoId = trim($item['peserta_didik_id'] ?? '');
                $nama   = trim($item['nama'] ?? $item['nama_lengkap'] ?? '');
                $nisn   = trim($item['nisn'] ?? '');
                $nis    = trim($item['nipd'] ?? $item['nis'] ?? '');

                if (empty($nama)) continue;

                $existing = null;
                if (!empty($dapoId)) {
                    $existing = $this->db->table('master_siswa')->where('dapo_id', $dapoId)->get()->getRowArray();
                }
                if (!$existing && !empty($nisn)) {
                    $existing = $this->db->table('master_siswa')->where('nisn', $nisn)->get()->getRowArray();
                }
                if (!$existing && !empty($nis)) {
                    $existing = $this->db->table('master_siswa')->where('nis', $nis)->get()->getRowArray();
                }
                if (!$existing) {
                    $existing = $this->db->table('master_siswa')->where('nama', $nama)->get()->getRowArray();
                }

                $gender = strtoupper(trim($item['jenis_kelamin'] ?? 'L'));
                if (!in_array($gender, ['L', 'P'])) $gender = 'L';

                // Map all available student fields from Dapodik payload
                $tempatLahir    = trim($item['tempat_lahir'] ?? '');
                $tanggalLahir   = trim($item['tanggal_lahir'] ?? $item['tgl_lahir'] ?? '');
                $nik            = trim($item['nik'] ?? '');
                $agama          = $this->parseAgama($item);
                $alamat         = trim($item['alamat_jalan'] ?? $item['alamat'] ?? '');
                $rt             = trim($item['rt'] ?? '');
                $rw             = trim($item['rw'] ?? '');
                $dusun          = trim($item['dusun'] ?? '');
                $kelurahan      = trim($item['desa_kelurahan'] ?? $item['kelurahan'] ?? '');
                $kecamatan      = trim($item['kecamatan'] ?? '');
                $kabupaten      = trim($item['kabupaten_kota'] ?? $item['kabupaten'] ?? '');
                $provinsi       = trim($item['provinsi'] ?? '');
                $kodePos        = trim($item['kode_pos'] ?? '');

                // Parents & Guardian Data
                $namaAyah       = trim($item['nama_ayah'] ?? '');
                $tglLahirAyah   = trim($item['tgl_lahir_ayah'] ?? '');
                $pendidikanAyah = trim($item['pendidikan_ayah_id_str'] ?? $item['pendidikan_ayah'] ?? '');
                $pekerjaanAyah  = trim($item['pekerjaan_ayah_id_str'] ?? $item['pekerjaan_ayah'] ?? '');
                $nohpAyah       = trim($item['nohp_ayah'] ?? $item['no_telepon_ayah'] ?? '');

                $namaIbu        = trim($item['nama_ibu_kandung'] ?? $item['nama_ibu'] ?? '');
                $tglLahirIbu    = trim($item['tgl_lahir_ibu'] ?? '');
                $pendidikanIbu  = trim($item['pendidikan_ibu_id_str'] ?? $item['pendidikan_ibu'] ?? '');
                $pekerjaanIbu   = trim($item['pekerjaan_ibu_id_str'] ?? $item['pekerjaan_ibu'] ?? '');
                $nohpIbu        = trim($item['nohp_ibu'] ?? $item['no_telepon_ibu'] ?? '');

                $namaWali       = trim($item['nama_wali'] ?? '');
                $tglLahirWali   = trim($item['tgl_lahir_wali'] ?? '');
                $pendidikanWali = trim($item['pendidikan_wali_id_str'] ?? $item['pendidikan_wali'] ?? '');
                $pekerjaanWali  = trim($item['pekerjaan_wali_id_str'] ?? $item['pekerjaan_wali'] ?? '');
                $nohpWali       = trim($item['nohp_wali'] ?? $item['no_telepon_wali'] ?? '');

                $hp             = trim($item['nomor_telepon_seluler'] ?? $item['hp'] ?? $item['no_hp'] ?? '');
                $email          = trim($item['email'] ?? '');

                $baseUsername = !empty($nisn) ? $nisn : (!empty($nis) ? $nis : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)));
                if (empty($baseUsername)) $baseUsername = 'siswa' . rand(100, 999);

                $existingId = $existing['id_siswa'] ?? 0;
                $username   = $baseUsername;
                $counter    = 1;
                while ($this->db->table('master_siswa')->where('username', $username)->where('id_siswa !=', $existingId)->countAllResults() > 0) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                $data = [
                    'dapo_id'       => !empty($dapoId) ? $dapoId : ($existing['dapo_id'] ?? null),
                    'nama'          => $nama,
                    'nisn'          => !empty($nisn) ? $nisn : ($existing['nisn'] ?? ''),
                    'nis'           => !empty($nis) ? $nis : ($existing['nis'] ?? ''),
                    'jenis_kelamin' => $gender,
                ];

                if (!empty($tempatLahir))    $data['tempat_lahir']    = $tempatLahir;
                if (!empty($tanggalLahir))   $data['tanggal_lahir']   = $tanggalLahir;
                if (!empty($nik))            $data['nik']             = $nik;
                if (!empty($agama))          $data['agama']           = $agama;
                if (!empty($alamat))         $data['alamat']          = $alamat;
                if (!empty($rt))             $data['rt']              = $rt;
                if (!empty($rw))             $data['rw']              = $rw;
                if (!empty($dusun))          $data['dusun']           = $dusun;
                if (!empty($kelurahan))      $data['kelurahan']       = $kelurahan;
                if (!empty($kecamatan))      $data['kecamatan']       = $kecamatan;
                if (!empty($kabupaten))      $data['kabupaten']       = $kabupaten;
                if (!empty($provinsi))       $data['provinsi']        = $provinsi;
                if (!empty($kodePos))        $data['kode_pos']        = (int) $kodePos;

                if (!empty($namaAyah))       $data['nama_ayah']       = $namaAyah;
                if (!empty($tglLahirAyah))   $data['tgl_lahir_ayah']   = $tglLahirAyah;
                if (!empty($pendidikanAyah)) $data['pendidikan_ayah'] = $pendidikanAyah;
                if (!empty($pekerjaanAyah))  $data['pekerjaan_ayah']  = $pekerjaanAyah;
                if (!empty($nohpAyah))       $data['nohp_ayah']       = $nohpAyah;

                if (!empty($namaIbu))        $data['nama_ibu']        = $namaIbu;
                if (!empty($tglLahirIbu))    $data['tgl_lahir_ibu']    = $tglLahirIbu;
                if (!empty($pendidikanIbu))  $data['pendidikan_ibu']  = $pendidikanIbu;
                if (!empty($pekerjaanIbu))   $data['pekerjaan_ibu']   = $pekerjaanIbu;
                if (!empty($nohpIbu))        $data['nohp_ibu']        = $nohpIbu;

                if (!empty($namaWali))       $data['nama_wali']       = $namaWali;
                if (!empty($tglLahirWali))   $data['tgl_lahir_wali']   = $tglLahirWali;
                if (!empty($pendidikanWali)) $data['pendidikan_wali'] = $pendidikanWali;
                if (!empty($pekerjaanWali))  $data['pekerjaan_wali']  = $pekerjaanWali;
                if (!empty($nohpWali))       $data['nohp_wali']       = $nohpWali;

                if (!empty($hp))            $data['hp']              = $hp;
                if (!empty($email))         $data['email']           = $email;

                if ($existing) {
                    if (isset($existing['password']) && str_starts_with($existing['password'], '$2y$')) {
                        $data['password'] = $existing['username'] ?? $username;
                    }
                    $this->db->table('master_siswa')->where('id_siswa', $existing['id_siswa'])->update($data);
                    $siswaId = $existing['id_siswa'];
                    $updated++;
                } else {
                    // Kolom NOT NULL tanpa default (nik, warga_negara, uid) wajib diisi pada insert
                    $data['username']     = $username;
                    $data['password']     = $username;
                    $data['nik']          = $nik;
                    $data['warga_negara'] = '';
                    $data['uid']          = $this->generateUid();
                    $this->db->table('master_siswa')->insert($data);
                    $siswaId = $this->db->insertID();
                    $added++;
                }

                // Daftarkan ke buku_induk jika belum
                if ($siswaId && $this->db->tableExists('buku_induk')) {
                    $checkBuku = $this->db->table('buku_induk')->where('id_siswa', $siswaId)->get()->getRowArray();
                    if (!$checkBuku) {
                        $this->db->table('buku_induk')->insert([
                            'id_siswa' => $siswaId,
                            'status'   => '1',
                        ]);
                    }
                }

                // Map kelas ke kelas_siswa jika ada nama_rombel / rombel
                $rombelNama = $item['nama_rombel'] ?? $item['rombel'] ?? '';
                if (!empty($rombelNama) && $siswaId && $this->db->tableExists('master_kelas')) {
                    [$tpAktif, $smtAktif] = $this->getActiveTpSmt();

                    $kelas = $this->db->table('master_kelas')
                        ->where('LOWER(nama_kelas)', strtolower($this->normalizeKelasName($rombelNama)))
                        ->where('id_tp', $tpAktif)
                        ->where('id_smt', $smtAktif)
                        ->get()->getRowArray();

                    if ($kelas && $this->db->tableExists('kelas_siswa')) {
                        $this->upsertKelasSiswa($siswaId, (int) $kelas['id_kelas'], (int) $tpAktif, (int) $smtAktif);
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'DapodikSync Siswa Error: ' . $e->getMessage());
            }
        }

        $msg = "Berhasil memproses {$processed} data Peserta Didik ({$added} ditambah, {$updated} diperbarui).";
        $this->writeLog('siswa', $metode, 'sukses', $processed, $added, $updated, $msg, $userId);

        return ['status' => true, 'message' => $msg, 'processed' => $processed, 'added' => $added, 'updated' => $updated];
    }

    // ── SYNC ROMBEL (KELAS) ──────────────────────────────────────────────────

    public function syncRombelFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $response = $client->getRombonganBelajar();
        if (!$response['status'] || empty($response['data'])) {
            $msg = $response['message'] ?? 'Tidak ada data Rombel dari API Dapodik.';
            $this->writeLog('rombel', 'api', 'gagal', 0, 0, 0, $msg, $userId);
            return ['status' => false, 'message' => $msg, 'processed' => 0, 'added' => 0, 'updated' => 0];
        }
        $this->syncJurusanFromRombel($response['data'], $userId);
        $result = $this->processRombelItems($response['data'], 'api', $userId);
        if (!empty($result['status'])) {
            $this->markLastSync();
        }
        return $result;
    }

    public function processRombelItems(array $items, string $metode = 'api', ?int $userId = null): array
    {
        $processed   = 0;
        $added       = 0;
        $updated     = 0;
        $skipped     = 0;
        $mappedSiswa = 0;

        [$idTp, $idSmt] = $this->getActiveTpSmt();

        // Cache jurusan & level agar tidak berulang query per rombel
        $jurusanCache = [];
        $levelCache   = [];

        // Set pemetaan valid (siswa,kelas,tp,smt) hasil sync, dipakai untuk rekon
        $validMappings = [];

        foreach ($items as $item) {
            $processed++;
            try {
                // Hanya rombel kelas reguler (jenis_rombel=1 "Kelas") yang dijadikan master_kelas.
                // Rombel mapel pilihan (jenis=16, dst.) dilewati agar tidak membuat kelas fiktif.
                if (!empty($item['jenis_rombel']) && (int) $item['jenis_rombel'] !== 1) {
                    $skipped++;
                    continue;
                }

                $dapoId = trim($item['rombongan_belajar_id'] ?? '');
                $nama   = trim($item['nama'] ?? $item['nama_rombel'] ?? '');

                if (empty($nama)) continue;

                $namaNormal = $this->normalizeKelasName($nama);

                $existing = null;
                if (!empty($dapoId)) {
                    $existing = $this->db->table('master_kelas')->where('dapo_id', $dapoId)->get()->getRowArray();
                }
                if (!$existing) {
                    $existing = $this->db->table('master_kelas')
                        ->where('LOWER(nama_kelas)', strtolower($namaNormal))
                        ->where('id_tp', $idTp)
                        ->where('id_smt', $idSmt)
                        ->get()->getRowArray();
                }

                // Level kelas dari tingkat_pendidikan_id (10/11/12 → id level 10/11/12)
                $levelId = $this->resolveLevelId(trim($item['tingkat_pendidikan_id'] ?? ''), $levelCache);

                // Jurusan dari rombel (jurusan_id/jurusan_id_str), resolusi + cache
                $jurusanDapoId = trim($item['jurusan_id'] ?? '');
                $jurusanNama   = trim($item['jurusan_id_str'] ?? '');
                $jurusanKey    = $jurusanNama !== '' ? $jurusanNama : $jurusanDapoId;
                if ($jurusanKey === '' || !isset($jurusanCache[$jurusanKey])) {
                    $jurusanCache[$jurusanKey] = (int) ($this->resolveJurusanId($jurusanDapoId, $jurusanNama)['id'] ?? 0);
                }
                $jurusanId = $jurusanCache[$jurusanKey];

                // Wali kelas dari ptk_id rombel → master_guru
                $guruId = 0;
                $waliPtkId = trim($item['ptk_id'] ?? '');
                if ($waliPtkId !== '') {
                    $wali = $this->db->table('master_guru')
                        ->where('ptk_id', $waliPtkId)
                        ->get()->getRowArray();
                    if (!$wali) {
                        $wali = $this->db->table('master_guru')
                            ->where('dapo_id', $waliPtkId)
                            ->get()->getRowArray();
                    }
                    if ($wali && !empty($wali['id_guru'])) {
                        $guruId = (int) $wali['id_guru'];
                    }
                }

                $anggotaRombel = $item['anggota_rombel'] ?? [];
                if (is_string($anggotaRombel)) {
                    $anggotaRombel = json_decode($anggotaRombel, true);
                }
                if (!is_array($anggotaRombel)) {
                    $anggotaRombel = [];
                }

                $jmlSiswa = count($anggotaRombel);

                $data = [
                    'dapo_id'      => !empty($dapoId) ? $dapoId : ($existing['dapo_id'] ?? null),
                    'nama_kelas'   => $namaNormal,
                    'kode_kelas'   => strtoupper(str_replace(' ', '', $namaNormal)),
                    'id_tp'        => $idTp,
                    'id_smt'       => $idSmt,
                    'level_id'     => $levelId,
                    'jurusan_id'   => $jurusanId,
                    'guru_id'      => $guruId,
                ];

                if ($existing) {
                    $this->db->table('master_kelas')->where('id_kelas', $existing['id_kelas'])->update($data);
                    $updated++;
                    $kelasId = (int) $existing['id_kelas'];
                } else {
                    $this->db->table('master_kelas')->insert($data);
                    $kelasId = (int) $this->db->insertID();
                    $added++;
                }

                // Wali kelas → jabatan_guru (id_jabatan=4 "Walikelas")
                if ($guruId > 0) {
                    $this->upsertWaliKelas($guruId, $kelasId, $idTp, $idSmt);
                }

                // Mapping anggota rombel (peserta_didik_id) → kelas_siswa pada TP/SMT aktif
                $anggotaIds = [];
                if (!empty($kelasId) && !empty($anggotaRombel)) {
                        foreach ($anggotaRombel as $anggota) {
                        $pdId = trim($anggota['peserta_didik_id'] ?? '');
                        $nisn = trim($anggota['nisn'] ?? '');
                        if (empty($pdId) && empty($nisn)) continue;

                        $siswa = null;
                        if (!empty($pdId)) {
                            $siswa = $this->db->table('master_siswa')->where('dapo_id', $pdId)->get()->getRowArray();
                        }
                        if (!$siswa && !empty($nisn)) {
                            $siswa = $this->db->table('master_siswa')->where('nisn', $nisn)->get()->getRowArray();
                        }

                        if ($siswa) {
                            $this->upsertKelasSiswa((int) $siswa['id_siswa'], $kelasId, $idTp, $idSmt);
                            $anggotaIds[] = (int) $siswa['id_siswa'];
                            $validMappings[(int) $siswa['id_siswa'] . '-' . $kelasId] = true;
                            $mappedSiswa++;
                        }
                    }
                }

                // jumlah_siswa disimpan sebagai JSON daftar id siswa, format sama seperti Datakelas::save
                if (!empty($anggotaIds)) {
                    $jsonSiswa = json_encode(array_map(fn ($id) => ['id' => (string) $id], $anggotaIds));
                    $this->db->table('master_kelas')->where('id_kelas', $kelasId)->update(['jumlah_siswa' => $jsonSiswa]);
                }
            } catch (\Throwable $e) {
                log_message('error', 'DapodikSync Rombel Error: ' . $e->getMessage());
            }
        }

        // Rekon kelas_siswa untuk TP/SMT aktif: hapus anggota yang tidak ada di rombel aktif,
        // agar tidak ada anggota "hantu" dari sync sebelumnya yang sudah keluar/pindah kelas.
        $removed = 0;
        if (!empty($validMappings) && $this->db->tableExists('kelas_siswa')) {
            try {
                $validKelasIds = [];
                foreach ($validMappings as $key => $v) {
                    $parts = explode('-', $key);
                    $validKelasIds[(int) ($parts[1] ?? 0)] = true;
                }
                $kelasIds = array_keys($validKelasIds);
                if (!empty($kelasIds)) {
                    $rows = $this->db->table('kelas_siswa')
                        ->where('id_tp', $idTp)
                        ->where('id_smt', $idSmt)
                        ->whereIn('id_kelas', $kelasIds)
                        ->get()->getResultArray();

                    foreach ($rows as $r) {
                        $k = (int) $r['id_siswa'] . '-' . (int) $r['id_kelas'];
                        if (!isset($validMappings[$k])) {
                            $this->db->table('kelas_siswa')->where('id_kelas_siswa', $r['id_kelas_siswa'])->delete();
                            $removed++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'DapodikSync Rombel Reconcile Error: ' . $e->getMessage());
            }
        }

        $msg = "Berhasil memproses {$processed} data Rombongan Belajar ({$added} ditambah, {$updated} diperbarui, {$skipped} dilewati, {$mappedSiswa} siswa dimapping ke kelas, {$removed} anggota usang dihapus).";
        $this->writeLog('rombel', $metode, 'sukses', $processed, $added, $updated, $msg, $userId);

        return ['status' => true, 'message' => $msg, 'processed' => $processed, 'added' => $added, 'updated' => $updated, 'skipped' => $skipped, 'mapped_siswa' => $mappedSiswa];
    }

    // ── SYNC MAPEL ──────────────────────────────────────────────────────────

    public function syncMapelFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $mapelItems = [];
        $mapelIndex = [];

        $addMapel = function (array $pembelajaran) use (&$mapelItems, &$mapelIndex) {
            $mapelNama = trim($pembelajaran['nama_mata_pelajaran'] ?? $pembelajaran['mata_pelajaran_nama'] ?? '');
            $mapelId   = trim($pembelajaran['mata_pelajaran_id'] ?? '');
            if (empty($mapelNama)) return;

            $key = $mapelId ?: $mapelNama;
            if (isset($mapelIndex[$key])) return;
            $mapelIndex[$key] = true;

            // Kelompok mapel: "Matpel Pilihan" → C (Peminatan), selainnya default A
            $statusKur   = trim($pembelajaran['status_di_kurikulum_str'] ?? '');
            $kelompok    = 'A';
            if (mb_stripos($statusKur, 'Pilih') !== false) {
                $kelompok = 'C';
            } elseif (mb_stripos($statusKur, 'Lintas Minat') !== false) {
                $kelompok = 'LM';
            } elseif (mb_stripos($statusKur, 'Muatan Lokal') !== false) {
                $kelompok = 'MULOK';
            }

            $mapelItems[] = [
                'dapo_id'  => $mapelId,
                'nama'     => $mapelNama,
                'kelompok' => $kelompok,
            ];
        };

        // 1) Coba endpoint getPembelajaran (jika tersedia di server web service)
        $resPem = $client->getPembelajaran();
        if ($resPem['status'] && !empty($resPem['data'])) {
            $pemRows = $resPem['data']['rows'] ?? $resPem['data'];
            if (is_array($pemRows)) {
                foreach ($pemRows as $pembelajaran) {
                    $addMapel((array) $pembelajaran);
                }
            }
        }

        // 2) Fallback: data mapel tersarang di field "pembelajaran" pada respons getRombonganBelajar
        if (empty($mapelItems)) {
            $resRombel = $client->getRombonganBelajar();
            if ($resRombel['status'] && !empty($resRombel['data'])) {
                $rombelRows = $resRombel['data']['rows'] ?? $resRombel['data'];
                if (is_array($rombelRows)) {
                    foreach ($rombelRows as $rombel) {
                        if (isset($rombel['pembelajaran']) && is_array($rombel['pembelajaran'])) {
                            foreach ($rombel['pembelajaran'] as $pembelajaran) {
                                $addMapel((array) $pembelajaran);
                            }
                        }
                    }
                }
            }
        }

        if (empty($mapelItems)) {
            $msg = 'Tidak ada data Mata Pelajaran dari API Dapodik.';
            $this->writeLog('mapel', 'api', 'gagal', 0, 0, 0, $msg, $userId);
            return ['status' => false, 'message' => $msg, 'processed' => 0, 'added' => 0, 'updated' => 0];
        }

        $result = $this->processMapelItems($mapelItems, 'api', $userId);
        if (!empty($result['status'])) {
            $this->markLastSync();
        }

        return $result;
    }

    public function processMapelItems(array $items, string $metode = 'api', ?int $userId = null): array
    {
        $processed = 0;
        $added     = 0;
        $updated   = 0;

        foreach ($items as $item) {
            $processed++;
            try {
                $dapoId   = trim($item['dapo_id'] ?? $item['mata_pelajaran_id'] ?? '');
                $nama     = trim($item['nama'] ?? $item['nama_mata_pelajaran'] ?? '');
                $kelompok = trim($item['kelompok'] ?? '');

                if (empty($nama)) continue;

                $existing = null;
                if (!empty($dapoId)) {
                    $existing = $this->db->table('master_mapel')->where('dapo_id', $dapoId)->get()->getRowArray();
                }
                if (!$existing) {
                    $existing = $this->db->table('master_mapel')->where('LOWER(nama_mapel)', strtolower($nama))->get()->getRowArray();
                }
                if (!$existing) {
                    $targetNorm = $this->normalizeMapelKey($nama);
                    $allMapel   = $this->db->table('master_mapel')->get()->getResultArray();
                    foreach ($allMapel as $m) {
                        if (!empty($m['nama_mapel']) && $this->normalizeMapelKey($m['nama_mapel']) === $targetNorm) {
                            $existing = $m;
                            break;
                        }
                    }
                }

                $data = [
                    'dapo_id'    => !empty($dapoId) ? $dapoId : ($existing['dapo_id'] ?? null),
                    'nama_mapel' => $nama,
                    'kode'       => !empty($existing['kode']) ? $existing['kode'] : strtoupper(substr(str_replace(' ', '', $nama), 0, 10)),
                ];

                // Kelompok mapel hanya diisi jika belum pernah diset (hindari menimpa setting manual)
                if ($kelompok !== '' && $kelompok !== '-' && (!$existing || empty($existing['kelompok']) || $existing['kelompok'] === '-')) {
                    $data['kelompok'] = $kelompok;
                }

                if ($existing) {
                    $this->db->table('master_mapel')->where('id_mapel', $existing['id_mapel'])->update($data);
                    $updated++;
                } else {
                    if ($kelompok !== '' && $kelompok !== '-') {
                        $data['kelompok'] = $kelompok;
                    }
                    $this->db->table('master_mapel')->insert($data);
                    $added++;
                }
            } catch (\Throwable $e) {
                log_message('error', 'DapodikSync Mapel Error: ' . $e->getMessage());
            }
        }

        $msg = "Berhasil memproses {$processed} data Mata Pelajaran ({$added} ditambah, {$updated} diperbarui).";
        $this->writeLog('mapel', $metode, 'sukses', $processed, $added, $updated, $msg, $userId);

        return ['status' => true, 'message' => $msg, 'processed' => $processed, 'added' => $added, 'updated' => $updated];
    }

    // ── SYNC JURUSAN ────────────────────────────────────────────────────────

    /**
     * Sinkronisasi data jurusan dari respons getRombonganBelajar (jurusan_id/jurusan_id_str).
     * Jurusan baru dibuat, jurusan yang sudah ada hanya diperbarui nama/kode-nya.
     */
    public function syncJurusanFromRombel(array $rombelItems, ?int $userId = null): array
    {
        $processed = 0;
        $added     = 0;
        $updated   = 0;

        $seen = [];
        foreach ($rombelItems as $item) {
            if (!empty($item['jenis_rombel']) && (int) $item['jenis_rombel'] !== 1) continue;

            $dapoId = trim($item['jurusan_id'] ?? '');
            $nama   = trim($item['jurusan_id_str'] ?? '');
            if ($nama === '' && $dapoId === '') continue;

            $key = $nama !== '' ? $nama : $dapoId;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $processed++;
            $res = $this->resolveJurusanId($dapoId, $nama);
            if (!empty($res['created'])) {
                $added++;
            } elseif (!empty($res['id'])) {
                $updated++;
            }
        }

        $msg = "Berhasil memproses {$processed} data Jurusan ({$added} ditambah, {$updated} diperbarui).";
        $this->writeLog('rombel', 'api', 'sukses', $processed, $added, $updated, $msg, $userId);

        return ['status' => true, 'message' => $msg, 'processed' => $processed, 'added' => $added, 'updated' => $updated];
    }

    // ── OFFLINE DUMP IMPORT ─────────────────────────────────────────────────
    public function processOfflineDump(string $filePath, string $entitas, ?int $userId = null): array
    {
        if (!file_exists($filePath)) {
            return ['status' => false, 'message' => "File tidak ditemukan: {$filePath}"];
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'zip') {
            return $this->processZipDump($filePath, $userId);
        }

        if ($ext === 'json') {
            $content = file_get_contents($filePath);
            $json = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status' => false, 'message' => 'Format file JSON tidak valid.'];
            }

            $items = $json['rows'] ?? $json['data'] ?? $json;
            if (!is_array($items)) {
                return ['status' => false, 'message' => 'Struktur data JSON tidak valid.'];
            }

            switch ($entitas) {
                case 'gtk':
                    return $this->processGtkItems($items, 'offline_json', $userId);
                case 'siswa':
                    return $this->processSiswaItems($items, 'offline_json', $userId);
            case 'rombel':
                foreach ($items as &$rombelItem) {
                    if (isset($rombelItem['anggota_rombel']) && is_string($rombelItem['anggota_rombel'])) {
                        $decoded = json_decode($rombelItem['anggota_rombel'], true);
                        if (is_array($decoded)) {
                            $rombelItem['anggota_rombel'] = $decoded;
                        }
                    }
                }
                unset($rombelItem);
                return $this->processRombelItems($items, 'offline_json', $userId);
                case 'mapel':
                    return $this->processMapelItems($items, 'offline_json', $userId);
                default:
                    return ['status' => false, 'message' => "Entitas '{$entitas}' tidak didukung untuk import JSON."];
            }
        }

        return ['status' => false, 'message' => 'Format file tidak didukung. Harap upload file .json atau .zip.'];
    }

    protected function processZipDump(string $zipPath, ?int $userId = null): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['status' => false, 'message' => 'Gagal membuka file ZIP.'];
        }

        $tmpDir = sys_get_temp_dir() . '/dapo_zip_' . uniqid();
        mkdir($tmpDir, 0777, true);
        $zip->extractTo($tmpDir);
        $zip->close();

        $results = [];
        $files = scandir($tmpDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $full = $tmpDir . '/' . $file;
            $lower = strtolower($file);

            if (str_contains($lower, 'gtk') || str_contains($lower, 'guru')) {
                $results['gtk'] = $this->processOfflineDump($full, 'gtk', $userId);
            } elseif (str_contains($lower, 'siswa') || str_contains($lower, 'pesertadidik')) {
                $results['siswa'] = $this->processOfflineDump($full, 'siswa', $userId);
            } elseif (str_contains($lower, 'rombel') || str_contains($lower, 'kelas')) {
                $results['rombel'] = $this->processOfflineDump($full, 'rombel', $userId);
            } elseif (str_contains($lower, 'mapel') || str_contains($lower, 'pembelajaran')) {
                $results['mapel'] = $this->processOfflineDump($full, 'mapel', $userId);
            }
        }

        // Cleanup
        array_map('unlink', glob("{$tmpDir}/*.*"));
        rmdir($tmpDir);

        return [
            'status'  => true,
            'message' => 'Import paket ZIP Dapodik selesai.',
            'results' => $results,
        ];
    }

    // ── HELPER UMUM ─────────────────────────────────────────────────────────

    /**
     * Resolusi id level_kelas dari tingkat_pendidikan_id Dapodik (10/11/12).
     * Jika tabel level_kelas ada dan id level cocok, gunakan; jika tidak, fallback ke id 10-12.
     */
    protected function resolveLevelId(string $tingkat, array &$cache = []): int
    {
        $tingkat = (int) $tingkat;
        if ($tingkat > 0 && isset($cache[$tingkat])) {
            return $cache[$tingkat];
        }

        $levelId = 10;
        if ($this->db->tableExists('level_kelas')) {
            if ($tingkat > 0) {
                $row = $this->db->table('level_kelas')->where('level', $tingkat)->get()->getRowArray();
                if ($row && !empty($row['id_level'])) {
                    $levelId = (int) $row['id_level'];
                }
            }
        } elseif ($tingkat >= 10 && $tingkat <= 12) {
            $levelId = $tingkat;
        }

        if ($tingkat > 0) {
            $cache[$tingkat] = $levelId;
        }

        return $levelId;
    }

    /**
     * Cari (atau buat) id_jurusan dari data rombel Dapodik (jurusan_id/jurusan_id_str).
     * Mengembalikan ['id' => int, 'created' => bool].
     */
    protected function resolveJurusanId(string $dapoJurusanId, string $namaJurusan): array
    {
        $nama = trim($namaJurusan);
        $kode = trim($dapoJurusanId);

        if ($nama === '' && $kode === '') {
            return ['id' => 0, 'created' => false];
        }

        $existing = null;
        if ($nama !== '') {
            $existing = $this->db->table('master_jurusan')
                ->where('LOWER(nama_jurusan)', strtolower($nama))
                ->get()->getRowArray();
        }
        if (!$existing && $kode !== '') {
            $existing = $this->db->table('master_jurusan')
                ->where('kode_jurusan', strtoupper($kode))
                ->get()->getRowArray();
        }
        // Fallback: "Umum" / "M0000000" → "NON JURUSAN" jika sudah ada
        if (!$existing && in_array(strtoupper($kode), ['M0000000', 'M000000'], true)) {
            $existing = $this->db->table('master_jurusan')
                ->where('LOWER(nama_jurusan)', 'non jurusan')
                ->get()->getRowArray();
        }

        if ($existing) {
            $update = [];
            if ($nama !== '' && strcasecmp($existing['nama_jurusan'] ?? '', $nama) !== 0) {
                $update['nama_jurusan'] = $nama;
            }
            if ($kode !== '' && strtoupper($existing['kode_jurusan'] ?? '') !== strtoupper($kode)) {
                $update['kode_jurusan'] = strtoupper(substr($kode, 0, 10));
            }
            $update['sync_at'] = date('Y-m-d H:i:s');
            $this->db->table('master_jurusan')->where('id_jurusan', $existing['id_jurusan'])->update($update);

            return ['id' => (int) $existing['id_jurusan'], 'created' => false];
        }

        $kodeFinal = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $kode !== '' ? $kode : $nama), 0, 10));
        if ($kodeFinal === '') $kodeFinal = 'JRS';

        $this->db->table('master_jurusan')->insert([
            'nama_jurusan' => $nama !== '' ? $nama : ('Jurusan ' . $kode),
            'kode_jurusan' => $kodeFinal,
            'status'       => 1,
            'deletable'    => 1,
            'sync_at'      => date('Y-m-d H:i:s'),
        ]);

        return ['id' => (int) $this->db->insertID(), 'created' => true];
    }

    /**
     * Simpan/update wali kelas pada tabel jabatan_guru (id_jabatan=4 "Walikelas").
     * Mempertahankan mapel_kelas & ekstra_kelas yang sudah ada.
     */
    protected function upsertWaliKelas(int $idGuru, int $idKelas, int $idTp, int $idSmt): void
    {
        if (!$idGuru || !$idKelas || !$this->db->tableExists('jabatan_guru')) return;

        try {
            $key = $idGuru . $idTp . $idSmt;
            $existing = $this->db->table('jabatan_guru')
                ->where('id_jabatan_guru', $key)
                ->get()->getRowArray();

            if ($existing) {
                $update = ['id_kelas' => $idKelas, 'id_jabatan' => 4];
                // Hanya timpa id_jabatan jika memang untuk kelas berbeda / belum wali
                $this->db->table('jabatan_guru')->where('id_jabatan_guru', $key)->update($update);
                return;
            }

            $this->db->table('jabatan_guru')->insert([
                'id_jabatan_guru' => $key,
                'id_guru'         => $idGuru,
                'id_jabatan'      => 4,
                'id_kelas'        => $idKelas,
                'mapel_kelas'     => 'a:0:{}',
                'ekstra_kelas'    => 'a:0:{}',
                'id_tp'           => $idTp,
                'id_smt'          => $idSmt,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'upsertWaliKelas Error: ' . $e->getMessage());
        }
    }

    /**
     * Normalisasi nama kelas agar Dapodik ("X-1") cocok dengan master_kelas lokal ("X 1").
     */
    protected function normalizeKelasName(string $name): string
    {
        $name = strtoupper(trim($name));
        $name = str_replace(['-', '_', '.', '/'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim((string) $name);
    }

    /**
     * Ambil id_tp & id_smt yang sedang aktif (kolom active, bukan status).
     */
    protected function getActiveTpSmt(): array
    {
        $tp  = 1;
        $smt = 1;

        if ($this->db->tableExists('master_tp') && $this->db->fieldExists('active', 'master_tp')) {
            $row = $this->db->table('master_tp')->where('active', '1')->get()->getRowArray();
            if ($row && !empty($row['id_tp'])) {
                $tp = (int) $row['id_tp'];
            }
        }

        if ($this->db->tableExists('master_smt') && $this->db->fieldExists('active', 'master_smt')) {
            $row = $this->db->table('master_smt')->where('active', '1')->get()->getRowArray();
            if ($row && !empty($row['id_smt'])) {
                $smt = (int) $row['id_smt'];
            }
        }

        return [$tp, $smt];
    }

    /**
     * Perbarui kolom last_sync_at pada tabel dapodik_settings.
     */
    protected function markLastSync(): void
    {
        if ($this->db->tableExists('dapodik_settings')) {
            try {
                $this->db->table('dapodik_settings')->where('id >', 0)->update([
                    'last_sync_at' => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'markLastSync Dapodik Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Generate UUID v4 sederhana untuk kolom uid.
     */
    protected function generateUid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate id_kelas_siswa (tabel tidak memakai auto_increment).
     */
    protected function generateKelasSiswaId(): int
    {
        $row = $this->db->table('kelas_siswa')->selectMax('id_kelas_siswa')->get()->getRowArray();
        return (int) ($row['id_kelas_siswa'] ?? 0) + 1;
    }

    /**
     * Upsert pemetaan siswa → kelas pada TP/SMT tertentu di tabel kelas_siswa.
     */
    protected function upsertKelasSiswa(int $idSiswa, int $idKelas, int $idTp, int $idSmt): void
    {
        if (!$idSiswa || !$idKelas || !$this->db->tableExists('kelas_siswa')) return;

        try {
            $existing = $this->db->table('kelas_siswa')
                ->where('id_siswa', $idSiswa)
                ->where('id_tp', $idTp)
                ->where('id_smt', $idSmt)
                ->get()->getRowArray();

            if ($existing) {
                if ((int) $existing['id_kelas'] !== $idKelas) {
                    $this->db->table('kelas_siswa')->where('id_kelas_siswa', $existing['id_kelas_siswa'])->update(['id_kelas' => $idKelas]);
                }
                return;
            }

            $this->db->table('kelas_siswa')->insert([
                'id_kelas_siswa' => $this->generateKelasSiswaId(),
                'id_tp'          => $idTp,
                'id_smt'         => $idSmt,
                'id_siswa'       => $idSiswa,
                'id_kelas'       => $idKelas,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'upsertKelasSiswa Error: ' . $e->getMessage());
        }
    }

    // ── HELPER LOG ──────────────────────────────────────────────────────────

    protected function writeLog(string $entitas, string $metode, string $status, int $processed, int $added, int $updated, string $keterangan, ?int $userId = null): void
    {
        if (!$this->db->tableExists('dapodik_log')) return;

        try {
            $this->db->table('dapodik_log')->insert([
                'entitas'          => $entitas,
                'metode'           => $metode,
                'status'           => $status,
                'total_diperoses'  => $processed,
                'total_ditambah'   => $added,
                'total_diperbarui' => $updated,
                'keterangan'       => $keterangan,
                'created_by'       => $userId,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'writeLog Dapodik Error: ' . $e->getMessage());
        }
    }

    /**
     * Parsing & standaridasi data Agama dari Dapodik (sesuai referensi resmi Kemendikbud Dapodik)
     */
    private function parseAgama(array $item): string
    {
        $val = trim((string)($item['agama_id_str'] ?? $item['agama'] ?? $item['nama_agama'] ?? $item['agama_id'] ?? ''));

        // Standar ID Referensi Web Service Dapodik Kemendikbud
        $map = [
            '1'  => 'Islam',
            '2'  => 'Kristen',
            '3'  => 'Katolik',
            '4'  => 'Hindu',
            '5'  => 'Buddha',
            '6'  => 'Khonghucu',
            '7'  => 'Kepercayaan thd Tuhan YME',
            '99' => 'Lainnya',
        ];

        if (isset($map[$val])) {
            return $map[$val];
        }

        if (empty($val)) return '';

        $lower = strtolower($val);
        if (str_contains($lower, 'islam')) return 'Islam';
        if (str_contains($lower, 'kristen') || str_contains($lower, 'protestan')) return 'Kristen';
        if (str_contains($lower, 'katolik') || str_contains($lower, 'katholik')) return 'Katolik';
        if (str_contains($lower, 'hindu')) return 'Hindu';
        if (str_contains($lower, 'buddha') || str_contains($lower, 'budha')) return 'Buddha';
        if (str_contains($lower, 'khonghucu') || str_contains($lower, 'konghucu')) return 'Khonghucu';
        if (str_contains($lower, 'kepercayaan')) return 'Kepercayaan thd Tuhan YME';

        return mb_substr($val, 0, 50);
    }

    /**
     * Normalisasi nama mata pelajaran untuk deteksi duplikasi fuzzy
     */
    private function normalizeMapelKey(string $name): string
    {
        $clean = strtolower(trim($name));
        $clean = preg_replace('/\([^)]*\)/', '', $clean);
        $clean = preg_replace('/[^a-z0-9]/', '', $clean);

        $synonyms = [
            'pendidikanagama' => 'agama',
            'danbudipekerti' => '',
            'danbudi'        => '',
            'danolahraga'    => '',
            'dankesehatan'   => '',
            'olahraga'       => 'jasmani',
            'olah'           => '',
            'raga'           => '',
            'kewarganegaraan'=> '',
        ];

        foreach ($synonyms as $find => $rep) {
            $clean = str_replace($find, $rep, $clean);
        }

        return $clean;
    }

    // ── SYNC PEMBELAJARAN ───────────────────────────────────────────────────

    public function syncPembelajaranFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $response = $client->getPembelajaran();
        if (!$response['status'] || empty($response['data'])) {
            $msg = 'Versi Web Service Dapodik lokal di sekolah Anda belum menyediakan rincian jam mengajar. Penugasan mata pelajaran per kelas dapat dikelola secara mandiri pada menu Penugasan Guru.';
            $this->writeLog('pembelajaran', 'api', 'sukses', 0, 0, 0, $msg, $userId);
            return ['status' => true, 'message' => $msg, 'processed' => 0, 'added' => 0, 'updated' => 0];
        }

        $items = $response['data'];
        if (isset($items['rows']) && is_array($items['rows'])) {
            $items = $items['rows'];
        }

        return $this->processPembelajaranItems($items, 'api', $userId);
    }

    public function processPembelajaranItems(array $items, string $source = 'api', ?int $userId = null): array
    {
        $processed = count($items);
        $updated = 0;

        $assignments = [];
        foreach ($items as $item) {
            $ptkId = $item['ptk_id'] ?? $item['ptk_terdaftar_id'] ?? null;
            if (!$ptkId) continue;

            $mapelId  = $item['mata_pelajaran_id'] ?? null;
            $rombelId = $item['rombongan_belajar_id'] ?? null;

            if (!$mapelId || !$rombelId) continue;

            $mapelRow = $this->db->table('master_mapel')->where('dapo_id', $mapelId)->get()->getRow();
            $kelasRow = $this->db->table('master_kelas')->where('dapo_id', $rombelId)->get()->getRow();

            if ($mapelRow && $kelasRow) {
                if (!isset($assignments[$ptkId])) {
                    $assignments[$ptkId] = [];
                }
                $assignments[$ptkId][] = [
                    'id_mapel' => $mapelRow->id_mapel,
                    'id_kelas' => $kelasRow->id_kelas
                ];
            }
        }

        foreach ($assignments as $ptkId => $list) {
            $guru = $this->db->table('master_guru')->where('dapo_id', $ptkId)->orWhere('ptk_id', $ptkId)->get()->getRow();
            if ($guru) {
                $groupedMapel = [];
                foreach ($list as $assign) {
                    $mId = $assign['id_mapel'];
                    $kId = $assign['id_kelas'];
                    if (!isset($groupedMapel[$mId])) {
                        $groupedMapel[$mId] = [
                            'id_mapel' => $mId,
                            'kelas_mapel' => []
                        ];
                    }
                    $groupedMapel[$mId]['kelas_mapel'][] = ['kelas' => $kId];
                }

                $jsonMapelKelas = json_encode(array_values($groupedMapel));
                $this->db->table('master_guru')->where('id_guru', $guru->id_guru)->update([
                    'mapel_kelas' => $jsonMapelKelas
                ]);
                $updated++;
            }
        }

        $this->writeLog('pembelajaran', $source, 'sukses', $processed, 0, $updated, "Data pembelajaran diselaraskan ke {$updated} guru.", $userId);
        return ['status' => true, 'message' => "{$updated} guru berhasil diselaraskan penugasan pengampunya.", 'processed' => $processed, 'added' => 0, 'updated' => $updated];
    }

    // ── SYNC RUANG / SARPRAS ────────────────────────────────────────────────

    public function syncRuangFromApi(DapodikClient $client, ?int $userId = null): array
    {
        $response = $client->getRuang();
        if (!$response['status'] || empty($response['data'])) {
            $response = $client->getSarpras();
        }

        if (!$response['status'] || empty($response['data'])) {
            $msg = 'Versi Web Service Dapodik lokal di sekolah Anda belum menyediakan rincian sarpras ruang. Data ruang ujian CBT dapat dikelola secara mandiri pada menu Ruang CBT.';
            $this->writeLog('ruang', 'api', 'sukses', 0, 0, 0, $msg, $userId);
            return ['status' => true, 'message' => $msg, 'processed' => 0, 'added' => 0, 'updated' => 0];
        }

        $items = $response['data'];
        if (isset($items['rows']) && is_array($items['rows'])) {
            $items = $items['rows'];
        }

        return $this->processRuangItems($items, 'api', $userId);
    }

    public function processRuangItems(array $items, string $source = 'api', ?int $userId = null): array
    {
        $processed = count($items);
        $added = 0;
        $updated = 0;

        if (!$this->db->tableExists('cbt_ruang')) {
            return ['status' => false, 'message' => 'Tabel cbt_ruang tidak ditemukan.', 'processed' => 0, 'added' => 0, 'updated' => 0];
        }

        foreach ($items as $item) {
            $ruangId   = $item['ruang_id'] ?? $item['id_ruang'] ?? $item['prasarana_id'] ?? null;
            $namaRuang = $item['nama_ruang'] ?? $item['nama'] ?? '';
            $kodeRuang = $item['kode_ruang'] ?? substr(strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $namaRuang)), 0, 10);

            if (empty($namaRuang)) continue;

            $existing = null;
            if (!empty($ruangId)) {
                $existing = $this->db->table('cbt_ruang')->where('sync_uuid', $ruangId)->get()->getRow();
            }
            if (!$existing) {
                $existing = $this->db->table('cbt_ruang')->where('LOWER(nama_ruang)', strtolower($namaRuang))->get()->getRow();
            }

            $payload = [
                'nama_ruang'  => $namaRuang,
                'kode_ruang'  => $kodeRuang ?: 'RUANG',
                'sync_uuid'   => $ruangId ?: null,
                'sync_status' => 'synced',
                'sync_at'     => date('Y-m-d H:i:s'),
                'sync_origin' => 'pusat'
            ];

            if ($existing) {
                $this->db->table('cbt_ruang')->where('id_ruang', $existing->id_ruang)->update($payload);
                $updated++;
            } else {
                $this->db->table('cbt_ruang')->insert($payload);
                $added++;
            }
        }

        $this->writeLog('ruang', $source, 'sukses', $processed, $added, $updated, "Data ruang diselaraskan ({$added} baru, {$updated} diperbarui).", $userId);
        return ['status' => true, 'message' => "{$processed} ruang diproses ({$added} baru, {$updated} diperbarui).", 'processed' => $processed, 'added' => $added, 'updated' => $updated];
    }

    protected function cleanTeacherName(string $name): string
    {
        // Remove leading titles like Drs. / Dra. / H. / Hj.
        $name = preg_replace('/^(drs|dra|h|hj)\.?\s+/i', '', $name);
        // Remove trailing degrees after comma, e.g. ", S.Pd.,Gr" or ", S.IP" or ", SE"
        $name = preg_replace('/,\s*(s\.?\w+|m\.?\w+|a\.?md|se|st|gr)\.?.*/i', '', $name);
        // Remove standalone degree tokens
        $name = preg_replace('/\b(s\.?pd|s\.?ip|s\.?e|s\.?t|s\.?kom|s\.?ag|s\.?si|m\.?pd|m\.?si|m\.?m|a\.?md|gr)\b/i', '', $name);
        // Remove non-alphanumeric except spaces
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
