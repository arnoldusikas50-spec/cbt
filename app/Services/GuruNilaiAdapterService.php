<?php

namespace App\Services;

use App\Models\NilaiModel;

class GuruNilaiAdapterService
{
    private \CodeIgniter\Database\BaseConnection $db;
    private NilaiModel $nilaiModel;
    private static bool $schemaChecked = false;

    public function __construct(?NilaiModel $nilaiModel = null)
    {
        $this->db = \Config\Database::connect();
        $this->nilaiModel = $nilaiModel ?? new NilaiModel();
        // Ensure required columns exist (once per request).
        // The adapter's payload includes fields (komponen_penilaian, nilai_skala_4,
        // sync_status, etc.) that must be present in the DB for insert/update to succeed.
        if (!self::$schemaChecked) {
            $this->ensureSchema();
            self::$schemaChecked = true;
        }
    }

    public function ensureSchema(): void
    {
        $forge = \Config\Database::forge();

        if ($this->db->tableExists('nilai_proses')) {
            $this->addColumnIfMissing($forge, 'nilai_proses', 'komponen_penilaian', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pengetahuan', 'after' => 'tipe']);
            $this->addColumnIfMissing($forge, 'nilai_proses', 'kompetensi_dasar', ['type' => 'TEXT', 'null' => true, 'after' => 'komponen_penilaian']);
            $this->addColumnIfMissing($forge, 'nilai_proses', 'capaian_pembelajaran', ['type' => 'TEXT', 'null' => true, 'after' => 'kompetensi_dasar']);
            $this->addColumnIfMissing($forge, 'nilai_proses', 'indikator_pencapaian', ['type' => 'TEXT', 'null' => true, 'after' => 'capaian_pembelajaran']);
            $this->addColumnIfMissing($forge, 'nilai_proses', 'nilai_skala_4', ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0, 'after' => 'nilai']);
            $this->addColumnIfMissing($forge, 'nilai_proses', 'sync_status', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending', 'after' => 'nilai_skala_4']);
            $this->addColumnIfMissing($forge, 'nilai_proses', 'last_sync_at', ['type' => 'DATETIME', 'null' => true, 'after' => 'sync_status']);
        }

        if ($this->db->tableExists('nilai_akhir_kurmer')) {
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_pengetahuan', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'after' => 'nilai_rapor']);
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_keterampilan', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'after' => 'nilai_pengetahuan']);
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_sikap', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'after' => 'nilai_keterampilan']);
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_rapor_skala_4', ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0, 'after' => 'nilai_sikap']);
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'predikat_skala_4', ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'nilai_rapor_skala_4']);
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'kkm', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 75, 'after' => 'predikat_skala_4']);
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'sync_status', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending', 'after' => 'kkm']);
            $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'last_sync_at', ['type' => 'DATETIME', 'null' => true, 'after' => 'sync_status']);
        }

        if (!$this->db->tableExists('nilai_sync_log')) {
            $forge->addField([
                'id_sync' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'entity' => ['type' => 'VARCHAR', 'constraint' => 30],
                'entity_id' => ['type' => 'VARCHAR', 'constraint' => 80],
                'mode' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'realtime'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'queued'],
                'payload' => ['type' => 'LONGTEXT', 'null' => true],
                'response' => ['type' => 'LONGTEXT', 'null' => true],
                'attempts' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_sync', true);
            $forge->addKey(['entity', 'status']);
            $forge->createTable('nilai_sync_log', true);
        }
    }

    private function addColumnIfMissing(\CodeIgniter\Database\Forge $forge, string $table, string $column, array $definition): void
    {
        if ($this->columnExists($table, $column)) {
            return;
        }

        try {
            // SQLite does not support positional column options like "AFTER".
            if (strtolower((string) $this->db->DBDriver) === 'sqlite3' && isset($definition['after'])) {
                unset($definition['after']);
            }
            $forge->addColumn($table, [$column => $definition]);
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'duplicate column name') !== false) {
                return;
            }
            if ($this->columnExists($table, $column)) {
                return;
            }
            log_message('error', sprintf('[GuruNilaiAdapterService::ensureSchema] addColumn %s.%s failed: %s', $table, $column, $e->getMessage()));
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        if (!$this->db->tableExists($table) && !$this->db->tableExists($this->db->DBPrefix . $table)) {
            return false;
        }
        $candidates = [$table];
        $prefixed = $this->db->DBPrefix . $table;
        if ($prefixed !== $table) {
            $candidates[] = $prefixed;
        }

        foreach ($candidates as $name) {
            try {
                $existing = array_map('strtolower', $this->getColumnNames($name));
                if (in_array(strtolower($column), $existing, true)) {
                    return true;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return false;
    }

    private function getColumnNames(string $table): array
    {
        if (strtolower((string) $this->db->DBDriver) === 'sqlite3') {
            $safeTable = str_replace("'", "''", $table);
            $rows = $this->db->query("PRAGMA table_info('{$safeTable}')")->getResultArray();
            if (!empty($rows)) {
                return array_map(
                    static fn(array $row): string => (string) ($row['name'] ?? ''),
                    $rows
                );
            }
        }

        return $this->db->getFieldNames($table);
    }

    public function validateAndNormalizeProses(array $data, int $idGuru, int $idTahun, int $idSmt): array
    {
        // Halaman input mengirim id_tujuan; samakan dengan id_tp agar tidak ada dua nama field.
        if (!isset($data['id_tp']) || $data['id_tp'] === null || $data['id_tp'] === '') {
            $data['id_tp'] = $data['id_tujuan'] ?? null;
        }

        $required = ['id_siswa', 'id_tp', 'nilai'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return ['valid' => false, 'message' => 'Field wajib: ' . $field];
            }
        }

        $idSiswa = (int) $data['id_siswa'];
        $idTujuan = (int) $data['id_tp'];
        $nilai100 = $this->normalizeScore100((float) $data['nilai']);

        $tujuan = $this->db->table('e_tujuan_pembelajaran')
            ->where('id_tujuan', $idTujuan)
            ->get()
            ->getRow();

        if (!$tujuan) {
            return ['valid' => false, 'message' => 'Tujuan/CP tidak ditemukan'];
        }

        // id_mapel dapat diturunkan dari TP, sehingga input cukup mengirim id_tp.
        $idMapel = (int) ($data['id_mapel'] ?? 0);
        if ($idMapel <= 0) {
            $idMapel = (int) $tujuan->id_mapel;
        }
        if ($idMapel <= 0) {
            return ['valid' => false, 'message' => 'Field wajib: id_mapel'];
        }
        if ((int) $tujuan->id_mapel !== $idMapel) {
            return ['valid' => false, 'message' => 'Tujuan/CP tidak ditemukan pada mapel terkait'];
        }

        if (!$this->isGuruMapelSiswaConsistent($idGuru, $idMapel, $idSiswa, $idTahun, $idSmt)) {
            return ['valid' => false, 'message' => 'Kombinasi guru-mapel-siswa tidak valid'];
        }

        $komponen = strtolower((string) ($data['komponen_penilaian'] ?? 'pengetahuan'));
        if (!in_array($komponen, ['pengetahuan', 'keterampilan', 'sikap'], true)) {
            $komponen = 'pengetahuan';
        }

        return [
            'valid' => true,
            'payload' => [
                'id_siswa' => $idSiswa,
                'id_mapel' => $idMapel,
                'id_tp' => $idTujuan,
                'id_guru' => $idGuru,
                'id_tahun' => $idTahun,
                'id_smt' => $idSmt,
                'tipe' => (string) ($data['tipe'] ?? 'sumatif_lm'),
                'komponen_penilaian' => $komponen,
                'kompetensi_dasar' => (string) ($data['kompetensi_dasar'] ?? ''),
                'capaian_pembelajaran' => (string) ($data['capaian_pembelajaran'] ?? ($tujuan->tujuan ?? '')),
                'indikator_pencapaian' => (string) ($data['indikator_pencapaian'] ?? ''),
                'nilai' => $nilai100,
                'nilai_skala_4' => $this->toScale4($nilai100),
                'sync_status' => 'pending',
            ],
            'mapping' => [
                'legacy_nilai_100' => $nilai100,
                'kurmer_skala_4' => $this->toScale4($nilai100),
                'kurmer_predikat' => $this->predicate4($this->toScale4($nilai100)),
            ]
        ];
    }

    public function validateAndNormalizeAkhir(array $data, int $idGuru, int $idTahun, int $idSmt): array
    {
        $required = ['id_siswa', 'id_mapel', 'nilai_sas'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return ['valid' => false, 'message' => 'Field wajib: ' . $field];
            }
        }

        $idSiswa = (int) $data['id_siswa'];
        $idMapel = (int) $data['id_mapel'];
        if (!$this->isGuruMapelSiswaConsistent($idGuru, $idMapel, $idSiswa, $idTahun, $idSmt)) {
            return ['valid' => false, 'message' => 'Kombinasi guru-mapel-siswa tidak valid'];
        }

        $nilaiSas = $this->normalizeScore100((float) $data['nilai_sas']);
        $bobotProses = max(0, min(100, (int) ($data['bobot_proses'] ?? 50)));
        $bobotSas = max(0, min(100, (int) ($data['bobot_sas'] ?? 50)));
        $bobotSts = max(0, min(100, (int) ($data['bobot_sts'] ?? 0)));
        $bobotPraktik = max(0, min(100, (int) ($data['bobot_praktik'] ?? 0)));

        if (($bobotProses + $bobotSas + $bobotSts + $bobotPraktik) !== 100) {
            return ['valid' => false, 'message' => 'Total bobot (Proses+SAS+STS+Praktik) harus 100%'];
        }

        $prosesRows = $this->db->table('nilai_proses')
            ->where('id_siswa', $idSiswa)
            ->where('id_mapel', $idMapel)
            ->where('id_tahun', $idTahun)
            ->where('id_smt', $idSmt)
            ->get()->getResultArray();

        $agg = $this->aggregateKomponen($prosesRows);
        $nilaiProsesRata = $agg['overall'];
        
        // Fetch STS and Praktik for final calculation if weights > 0
        $nilaiSts = 0;
        if ($bobotSts > 0) {
            $sts = $this->db->table('nilai_sts')
                ->where('id_siswa', $idSiswa)
                ->where('id_mapel', $idMapel)
                ->where('id_tahun', $idTahun)
                ->where('id_smt', $idSmt)
                ->get()->getRow();
            $nilaiSts = $sts ? (float)$sts->nilai : 0;
        }

        $nilaiPraktik = 0;
        if ($bobotPraktik > 0) {
            $praktik_list = $this->db->table('cbt_praktik_hasil h')
                ->select('h.nilai_akhir, h.rincian_nilai_rubrik')
                ->join('cbt_praktik p', 'p.id = h.praktik_id')
                ->where('h.siswa_id', $idSiswa)
                ->where('p.mapel_id', $idMapel)
                ->where('p.tp_id', $idTahun)
                ->where('p.smt_id', $idSmt)
                ->where('h.status_penilaian', 'sudah_dinilai')
                ->get()->getResultArray();
            
            if (!empty($praktik_list)) {
                $total_per_ujian = 0;
                foreach ($praktik_list as $p) {
                    $nilai_ujian = (float)$p['nilai_akhir'];
                    $rincian = json_decode($p['rincian_nilai_rubrik'] ?? '[]', true);
                    
                    // Fix: Handle legacy data that was stored as sum instead of average
                    if (!empty($rincian) && is_array($rincian) && count($rincian) > 0) {
                        $sum_rincian = array_sum($rincian);
                        // Check if current nilai_akhir looks like a sum (e.g. > 100 or matches the sum of criteria)
                        if ($nilai_ujian > 100 || abs($nilai_ujian - $sum_rincian) < 0.1) {
                            $nilai_ujian = $nilai_ujian / count($rincian);
                        }
                    }
                    $total_per_ujian += $nilai_ujian;
                }
                $nilaiPraktik = $total_per_ujian / count($praktik_list);
            }
        }

        $nilaiRapor = $this->weightedFinalComplex($nilaiProsesRata, $nilaiSas, $nilaiSts, $nilaiPraktik, $bobotProses, $bobotSas, $bobotSts, $bobotPraktik);
        $nilaiRaporSkala4 = $this->toScale4($nilaiRapor);

        return [
            'valid' => true,
            'payload' => [
                'id_siswa' => $idSiswa,
                'id_mapel' => $idMapel,
                'id_guru' => $idGuru,
                'id_tahun' => $idTahun,
                'id_smt' => $idSmt,
                'nilai_sas' => $nilaiSas,
                'nilai_rapor' => $nilaiRapor,
                'bobot_proses' => $bobotProses,
                'bobot_sas' => $bobotSas,
                'bobot_sts' => $bobotSts,
                'bobot_praktik' => $bobotPraktik,
                'nilai_pengetahuan' => $agg['pengetahuan'],
                'nilai_keterampilan' => $bobotPraktik > 0 ? $nilaiPraktik : $agg['keterampilan'],
                'nilai_sikap' => $agg['sikap'],
                'nilai_rapor_skala_4' => $nilaiRaporSkala4,
                'predikat_skala_4' => $this->predicate4($nilaiRaporSkala4),
                'deskripsi_tinggi' => (string) ($data['deskripsi_tinggi'] ?? ''),
                'deskripsi_rendah' => (string) ($data['deskripsi_rendah'] ?? ''),
                'sync_status' => 'pending',
            ],
            'mapping' => [
                'legacy_nilai_rapor_100' => $nilaiRapor,
                'kurmer_nilai_akhir_4' => $nilaiRaporSkala4,
                'kurmer_predikat' => $this->predicate4($nilaiRaporSkala4),
                'komponen' => $agg,
            ]
        ];
    }

    public function saveProses(array $normalized): array
    {
        $saved = (bool) $this->nilaiModel->saveNilaiProses($normalized);
        if ($saved) {
            $this->queueSync('nilai_proses', $this->buildEntityId($normalized), 'realtime', $normalized);
            $this->mirrorToENilaiKomponen($normalized);
        }
        return [
            'status' => $saved ? 'success' : 'error',
            'message' => $saved ? 'Nilai proses tersimpan' : 'Gagal menyimpan nilai',
        ];
    }

    public function deleteProses(int $idSiswa, int $idMapel, int $idTp, string $tipe = 'sumatif_lm'): array
    {
        $deleted = (bool) $this->nilaiModel->deleteNilaiProses($idSiswa, $idMapel, $idTp, $tipe);
        return [
            'status' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Nilai dihapus' : 'Gagal menghapus nilai',
        ];
    }

    public function saveAkhir(array $normalized): array
    {
        $saved = (bool) $this->nilaiModel->saveNilaiAkhir($normalized);
        if ($saved) {
            $this->queueSync('nilai_akhir_kurmer', $this->buildEntityId($normalized), 'realtime', $normalized);
            $this->mirrorToENilaiAkhir($normalized);
        }
        return [
            'status' => $saved ? 'success' : 'error',
            'message' => $saved ? 'Nilai akhir tersimpan' : 'Gagal menyimpan nilai',
        ];
    }

    public function deleteSts(int $idSiswa, int $idMapel, int $idTahun, int $idSmt): array
    {
        $deleted = (bool) $this->nilaiModel->deleteNilaiSts($idSiswa, $idMapel, $idTahun, $idSmt);
        return [
            'status' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Nilai STS dihapus' : 'Gagal menghapus nilai',
        ];
    }

    public function saveBatchProses(array $rows, int $idGuru, int $idTahun, int $idSmt): array
    {
        $saved = 0;
        $errors = [];
        foreach ($rows as $idx => $row) {
            $val = $this->validateAndNormalizeProses($row, $idGuru, $idTahun, $idSmt);
            if (!$val['valid']) {
                $errors[] = ['index' => $idx, 'message' => $val['message']];
                continue;
            }
            $result = $this->saveProses($val['payload']);
            if ($result['status'] === 'success') {
                $saved++;
            } else {
                $errors[] = ['index' => $idx, 'message' => $result['message'] ?? 'Gagal simpan data'];
            }
        }
        return ['saved' => $saved, 'errors' => $errors, 'total' => count($rows)];
    }

    public function saveBatchAkhir(array $rows, int $idGuru, int $idTahun, int $idSmt): array
    {
        $saved = 0;
        $errors = [];
        foreach ($rows as $idx => $row) {
            $val = $this->validateAndNormalizeAkhir($row, $idGuru, $idTahun, $idSmt);
            if (!$val['valid']) {
                $errors[] = ['index' => $idx, 'message' => $val['message']];
                continue;
            }
            $result = $this->saveAkhir($val['payload']);
            if ($result['status'] === 'success') {
                $saved++;
            } else {
                $errors[] = ['index' => $idx, 'message' => $result['message'] ?? 'Gagal simpan data'];
            }
        }
        return ['saved' => $saved, 'errors' => $errors, 'total' => count($rows)];
    }

    public function migrateLegacy(
        int $idGuru,
        int $idTahun,
        int $idSmt,
        ?int $idMapel = null,
        ?int $idKelas = null,
        string $mode = 'all'
    ): array {
        $result = [
            'mode' => $mode,
            'proses' => ['updated' => 0, 'scanned' => 0],
            'akhir' => ['updated' => 0, 'scanned' => 0],
        ];

        if ($mode === 'all' || $mode === 'proses') {
            $builder = $this->db->table('nilai_proses np')
                ->select('np.*')
                ->where('np.id_tahun', $idTahun)
                ->where('np.id_smt', $idSmt)
                ->where('np.id_guru', $idGuru);
            if ($idMapel) {
                $builder->where('np.id_mapel', $idMapel);
            }
            if ($idKelas) {
                $builder->join('kelas_siswa ks', 'ks.id_siswa = np.id_siswa AND ks.id_tp = np.id_tahun AND ks.id_smt = np.id_smt');
                $builder->where('ks.id_kelas', $idKelas);
            }
            $rows = $builder->get()->getResultArray();
            $result['proses']['scanned'] = count($rows);

            foreach ($rows as $row) {
                $nilai = $this->normalizeScore100((float) ($row['nilai'] ?? 0));
                $payload = [
                    'id_siswa' => (int) $row['id_siswa'],
                    'id_mapel' => (int) $row['id_mapel'],
                    'id_tp' => (int) $row['id_tp'],
                    'id_guru' => (int) $row['id_guru'],
                    'id_tahun' => (int) $row['id_tahun'],
                    'id_smt' => (int) $row['id_smt'],
                    'tipe' => (string) ($row['tipe'] ?? 'sumatif_lm'),
                    'komponen_penilaian' => (string) ($row['komponen_penilaian'] ?? 'pengetahuan'),
                    'kompetensi_dasar' => (string) ($row['kompetensi_dasar'] ?? ''),
                    'capaian_pembelajaran' => (string) ($row['capaian_pembelajaran'] ?? ''),
                    'indikator_pencapaian' => (string) ($row['indikator_pencapaian'] ?? ''),
                    'nilai' => $nilai,
                    'nilai_skala_4' => $this->toScale4($nilai),
                    'sync_status' => 'pending',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('nilai_proses')
                    ->where('id_nilai_proses', $row['id_nilai_proses'])
                    ->update($payload);
                $this->mirrorToENilaiKomponen($payload);
                $this->queueSync('nilai_proses', (string) $row['id_nilai_proses'], 'batch', $payload);
                $result['proses']['updated']++;
            }
        }

        if ($mode === 'all' || $mode === 'akhir') {
            $builder = $this->db->table('nilai_akhir_kurmer na')
                ->select('na.*')
                ->where('na.id_tahun', $idTahun)
                ->where('na.id_smt', $idSmt)
                ->where('na.id_guru', $idGuru);
            if ($idMapel) {
                $builder->where('na.id_mapel', $idMapel);
            }
            if ($idKelas) {
                $builder->join('kelas_siswa ks', 'ks.id_siswa = na.id_siswa AND ks.id_tp = na.id_tahun AND ks.id_smt = na.id_smt');
                $builder->where('ks.id_kelas', $idKelas);
            }
            $rows = $builder->get()->getResultArray();
            $result['akhir']['scanned'] = count($rows);

            foreach ($rows as $row) {
                $nilaiRapor = $this->normalizeScore100((float) ($row['nilai_rapor'] ?? 0));
                $nilaiSas = $this->normalizeScore100((float) ($row['nilai_sas'] ?? 0));
                $payload = [
                    'id_siswa' => (int) $row['id_siswa'],
                    'id_mapel' => (int) $row['id_mapel'],
                    'id_guru' => (int) $row['id_guru'],
                    'id_tahun' => (int) $row['id_tahun'],
                    'id_smt' => (int) $row['id_smt'],
                    'nilai_sas' => $nilaiSas,
                    'nilai_rapor' => $nilaiRapor,
                    'bobot_proses' => (int) ($row['bobot_proses'] ?? 50),
                    'bobot_sas' => (int) ($row['bobot_sas'] ?? 50),
                    'nilai_pengetahuan' => (float) ($row['nilai_pengetahuan'] ?? $nilaiRapor),
                    'nilai_keterampilan' => (float) ($row['nilai_keterampilan'] ?? $nilaiRapor),
                    'nilai_sikap' => (float) ($row['nilai_sikap'] ?? $nilaiRapor),
                    'nilai_rapor_skala_4' => $this->toScale4($nilaiRapor),
                    'predikat_skala_4' => $this->predicate4($this->toScale4($nilaiRapor)),
                    'deskripsi_tinggi' => (string) ($row['deskripsi_tinggi'] ?? ''),
                    'deskripsi_rendah' => (string) ($row['deskripsi_rendah'] ?? ''),
                    'sync_status' => 'pending',
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->table('nilai_akhir_kurmer')
                    ->where('id_nilai_akhir', $row['id_nilai_akhir'])
                    ->update($payload);
                $this->mirrorToENilaiAkhir($payload);
                $this->queueSync('nilai_akhir_kurmer', (string) $row['id_nilai_akhir'], 'batch', $payload);
                $result['akhir']['updated']++;
            }
        }

        return $result;
    }

    public function toScale4(float $score100): float
    {
        $score100 = $this->normalizeScore100($score100);
        return round(($score100 / 100) * 4, 2);
    }

    public function predicate4(float $score4): string
    {
        if ($score4 >= 3.51) return 'A';
        if ($score4 >= 2.76) return 'B';
        if ($score4 >= 2.01) return 'C';
        return 'D';
    }

    public function weightedFinal(float $proses, float $sas, int $bobotProses, int $bobotSas): float
    {
        $proses = $this->normalizeScore100($proses);
        $sas = $this->normalizeScore100($sas);
        $final = ($proses * $bobotProses / 100) + ($sas * $bobotSas / 100);
        return round($final, 2);
    }

    public function weightedFinalComplex(float $proses, float $sas, float $sts, float $praktik, int $bProses, int $bSas, int $bSts, int $bPraktik): float
    {
        $proses = $this->normalizeScore100($proses);
        $sas = $this->normalizeScore100($sas);
        $sts = $this->normalizeScore100($sts);
        $praktik = $this->normalizeScore100($praktik);
        
        $final = ($proses * $bProses / 100) + ($sas * $bSas / 100) + ($sts * $bSts / 100) + ($praktik * $bPraktik / 100);
        return round($final, 2);
    }

    public function normalizeScore100(float $value): float
    {
        if ($value < 0) return 0.0;
        if ($value > 100) return 100.0;
        return round($value, 2);
    }

    private function aggregateKomponen(array $rows): array
    {
        $groups = [
            'pengetahuan' => [],
            'keterampilan' => [],
            'sikap' => [],
        ];
        foreach ($rows as $row) {
            $k = strtolower((string) ($row['komponen_penilaian'] ?? 'pengetahuan'));
            if (!isset($groups[$k])) {
                $k = 'pengetahuan';
            }
            $groups[$k][] = (float) ($row['nilai'] ?? 0);
        }
        $avg = static function (array $vals): float {
            if (empty($vals)) return 0.0;
            return round(array_sum($vals) / count($vals), 2);
        };
        $p = $avg($groups['pengetahuan']);
        $k = $avg($groups['keterampilan']);
        $s = $avg($groups['sikap']);
        $overallBase = array_filter([$p, $k, $s], static fn($v) => $v > 0);
        $overall = empty($overallBase) ? 0.0 : round(array_sum($overallBase) / count($overallBase), 2);
        return ['pengetahuan' => $p, 'keterampilan' => $k, 'sikap' => $s, 'overall' => $overall];
    }

    private function isGuruMapelSiswaConsistent(int $idGuru, int $idMapel, int $idSiswa, int $idTahun, int $idSmt): bool
    {
        $siswa = $this->db->table('kelas_siswa')
            ->where('id_siswa', $idSiswa)
            ->where('id_tp', $idTahun)
            ->where('id_smt', $idSmt)
            ->get()->getRow();
        if (!$siswa) {
            return false;
        }

        // Ensure mapel exists.
        $mapel = $this->db->table('master_mapel')->where('id_mapel', $idMapel)->get()->getRow();
        if (!$mapel) {
            return false;
        }

        // Ensure teacher has active assignment for this period.
        $jabatan = $this->db->table('jabatan_guru')
            ->where('id_guru', $idGuru)
            ->where('id_tp', $idTahun)
            ->where('id_smt', $idSmt)
            ->get()->getRow();
        if (!$jabatan) {
            return false;
        }

        $assigned = $this->decodeMapelKelas((string) ($jabatan->mapel_kelas ?? ''));
        if (empty($assigned)) {
            return false;
        }

        return $this->isMapelKelasAssigned($assigned, $idMapel, (int) ($siswa->id_kelas ?? 0));
    }

    /**
     * Decode mapel_kelas payload stored as JSON/serialized value.
     *
     * @return array<int, mixed>
     */
    private function decodeMapelKelas(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw);
        if (json_last_error() === JSON_ERROR_NONE) {
            return is_array($decoded) ? $decoded : [];
        }

        $unserialized = @unserialize($raw);
        if ($unserialized !== false || $raw === 'b:0;') {
            return is_array($unserialized) ? $unserialized : [];
        }

        return [];
    }

    /**
     * Validate requested mapel is assigned to teacher and includes student's class.
     *
     * @param array<int, mixed> $assigned
     */
    private function isMapelKelasAssigned(array $assigned, int $idMapel, int $idKelas): bool
    {
        if ($idKelas <= 0) {
            return false;
        }

        foreach ($assigned as $entry) {
            $entryMapel = (int) $this->pickMixed($entry, ['id_mapel'], 0);
            if ($entryMapel !== $idMapel) {
                continue;
            }

            $kelasMapel = $this->pickMixed($entry, ['kelas_mapel'], []);
            if (!is_array($kelasMapel)) {
                return false;
            }

            foreach ($kelasMapel as $km) {
                $kelasId = (int) $this->pickMixed($km, ['kelas', 'id_kelas'], 0);
                if ($kelasId === $idKelas) {
                    return true;
                }
            }

            // Explicit mapel exists but kelas not found in assignment.
            return false;
        }

        return false;
    }

    /**
     * Pick first available key from mixed object/array.
     *
     * @param mixed $source
     * @param array<int, string> $keys
     * @param mixed $default
     * @return mixed
     */
    private function pickMixed($source, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return $source[$key];
            }
            if (is_object($source) && property_exists($source, $key)) {
                return $source->$key;
            }
        }
        return $default;
    }

    private function queueSync(string $entity, string $entityId, string $mode, array $payload): void
    {
        try {
            $this->db->table('nilai_sync_log')->insert([
                'entity' => $entity,
                'entity_id' => $entityId,
                'mode' => $mode,
                'status' => 'queued',
                'payload' => json_encode($payload),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[GuruNilaiAdapterService::queueSync] ' . $e->getMessage());
        }
    }

    private function mirrorToENilaiKomponen(array $payload): void
    {
        if (!$this->db->tableExists('e_nilai_komponen')) {
            return;
        }
        $tp = $this->db->table('master_tp')->where('id_tp', $payload['id_tahun'])->get()->getRow();
        if (!$tp) {
            return;
        }
        $tahunPelajaran = (string) ($tp->tahun ?? '');
        $komponenId = $this->findKomponenIdByName($payload['komponen_penilaian']);
        if (!$komponenId) {
            return;
        }
        $where = [
            'siswa_id' => $payload['id_siswa'],
            'mapel_id' => $payload['id_mapel'],
            'komponen_id' => $komponenId,
            'jenis_penilaian' => 'Harian',
            'semester' => $payload['id_smt'],
            'tahun_pelajaran' => $tahunPelajaran,
        ];
        $data = [
            'siswa_id' => $payload['id_siswa'],
            'mapel_id' => $payload['id_mapel'],
            'komponen_id' => $komponenId,
            'nilai' => $payload['nilai'],
            'predikat' => $this->predicate4((float) $payload['nilai_skala_4']),
            'deskripsi' => trim(($payload['capaian_pembelajaran'] ?? '') . ' ' . ($payload['indikator_pencapaian'] ?? '')),
            'jenis_penilaian' => 'Harian',
            'tanggal_penilaian' => date('Y-m-d'),
            'guru_id' => $payload['id_guru'],
            'semester' => $payload['id_smt'],
            'tahun_pelajaran' => $tahunPelajaran,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $existing = $this->db->table('e_nilai_komponen')->where($where)->get()->getRow();
        if ($existing) {
            $this->db->table('e_nilai_komponen')->where('id_nilai', $existing->id_nilai)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('e_nilai_komponen')->insert($data);
        }
    }

    private function mirrorToENilaiAkhir(array $payload): void
    {
        if (!$this->db->tableExists('e_nilai_akhir')) {
            return;
        }
        $tp = $this->db->table('master_tp')->where('id_tp', $payload['id_tahun'])->get()->getRow();
        if (!$tp) {
            return;
        }
        $tahunPelajaran = (string) ($tp->tahun ?? '');
        $where = [
            'siswa_id' => $payload['id_siswa'],
            'mapel_id' => $payload['id_mapel'],
            'semester' => $payload['id_smt'],
            'tahun_pelajaran' => $tahunPelajaran,
        ];
        $data = [
            'siswa_id' => $payload['id_siswa'],
            'mapel_id' => $payload['id_mapel'],
            'nilai_pengetahuan' => $payload['nilai_pengetahuan'] ?? 0,
            'predikat_pengetahuan' => $this->predicate4($this->toScale4((float) ($payload['nilai_pengetahuan'] ?? 0))),
            'deskripsi_pengetahuan' => $payload['deskripsi_tinggi'] ?? '',
            'nilai_keterampilan' => $payload['nilai_keterampilan'] ?? 0,
            'predikat_keterampilan' => 'B',
            'deskripsi_keterampilan' => '',
            'nilai_sikap' => $payload['nilai_sikap'] ?? 0,
            'predikat_sikap' => $this->predicate4($this->toScale4((float) ($payload['nilai_sikap'] ?? 0))),
            'deskripsi_sikap' => $payload['deskripsi_rendah'] ?? '',
            'nilai_akhir' => $payload['nilai_rapor'],
            'predikat_akhir' => $this->predicate4((float) ($payload['nilai_rapor_skala_4'] ?? 0)),
            'deskripsi_akhir' => trim(($payload['deskripsi_tinggi'] ?? '') . ' ' . ($payload['deskripsi_rendah'] ?? '')),
            'semester' => $payload['id_smt'],
            'tahun_pelajaran' => $tahunPelajaran,
            'status' => 'Draft',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $existing = $this->db->table('e_nilai_akhir')->where($where)->get()->getRow();
        if ($existing) {
            $this->db->table('e_nilai_akhir')->where('id_nilai_akhir', $existing->id_nilai_akhir)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('e_nilai_akhir')->insert($data);
        }
    }

    private function findKomponenIdByName(string $name): ?int
    {
        if (!$this->db->tableExists('e_komponen_penilaian')) {
            return null;
        }
        $jenis = ucfirst(strtolower($name));
        if ($jenis === 'Sikap') {
            $row = $this->db->table('e_komponen_penilaian')->where('jenis_penilaian', 'Sikap')->orderBy('id_komponen', 'ASC')->get()->getRow();
            return $row ? (int) $row->id_komponen : null;
        }
        $row = $this->db->table('e_komponen_penilaian')->where('jenis_penilaian', $jenis)->orderBy('id_komponen', 'ASC')->get()->getRow();
        return $row ? (int) $row->id_komponen : null;
    }

    private function buildEntityId(array $payload): string
    {
        return implode('-', [
            (string) ($payload['id_siswa'] ?? 0),
            (string) ($payload['id_mapel'] ?? 0),
            (string) ($payload['id_tahun'] ?? 0),
            (string) ($payload['id_smt'] ?? 0),
            (string) ($payload['id_tp'] ?? 0),
        ]);
    }
}
