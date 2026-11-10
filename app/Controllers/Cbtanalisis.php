<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;

/**
 * Cbtanalisis Controller - Migrated from CI3
 * Handles CBT exam question analysis
 */
class Cbtanalisis extends BaseController
{
    protected CbtModel $cbtModel;
    protected DashboardModel $dashboardModel;
    protected MasterModel $masterModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel();
    }

    /**
     * Main index page
     */
    public function index()
    {
        return redirect()->to('cbtanalisis/analisis');
    }

    /**
     * Analisis soal
     */
    public function analisis()
    {
        try {
            if (!$this->isLoggedIn()) {
                return redirect()->to('auth');
            }

            if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
                return $this->showError('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini', 403);
            }

            $user = $this->getUser();
            $setting = $this->dashboardModel->getSetting();

            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $data = [
                'user' => $user,
                'judul' => 'Analisis Soal',
                'subjudul' => 'Analisis Butir Soal',
                'setting' => $setting,
                'tp' => $this->dashboardModel->getTahun(),
                'smt' => $this->dashboardModel->getSemester(),
                'tp_active' => $tp,
                'smt_active' => $smt,
            ];

            $jadwal = $this->request->getGet('id_jadwal');
            $data['jadwal_selected'] = $jadwal;
            $data['is_rekap'] = true;

            if (!$jadwal || $jadwal === 'undefined') {
                // Show list of jadwal to select
                return redirect()->to('cbtanalysis')->with('error', 'Silakan pilih jadwal ujian terlebih dahulu dari Rekap Hasil Tes.');
            }

            $db = \Config\Database::connect();

            // Get rekap jadwal
            $rekap = $db->table('cbt_rekap_jadwal')->where('id_jadwal', $jadwal)->get()->getRow();

            if (!$rekap) {
                $data['error'] = 'Data rekap tidak ditemukan';
                return redirect()->to('cbtanalysis')->with('error', 'Data rekapitulasi nilai belum tersedia. Silakan eksekusi rekapitulasi nilai dari menu Rekap Hasil Tes terlebih dahulu.');
            }

            // Get rekap siswa
            $rekapBuilder = $db->table('cbt_rekap_siswa a')
                ->select('a.jawaban, a.nilai_input, a.id_siswa, s.nama, s.nis, s.agama')
                ->join('master_siswa s', 's.id_siswa = a.id_siswa', 'left')
                ->where('a.id_jadwal', $jadwal);
            // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
            $this->onlySiswaAktif($rekapBuilder, 'a');
            $rekap_siswa = $rekapBuilder->get()->getResult();

            // Process rekap data
            $rekap->jadwal = $this->maybeUnserialize($rekap->jadwal ?? '');
            if (!is_object($rekap->jadwal)) {
                $rekap->jadwal = (object) [];
            }
            if (!isset($rekap->jadwal->tgl_mulai)) {
                $rekap->jadwal->tgl_mulai = $rekap->tgl_mulai ?? '';
            }
            $rekap->jadwal->guru = $rekap->nama_guru ?? '';
            $rekap->jadwal->tp = $rekap->tp ?? '';
            $rekap->jadwal->smt = $rekap->smt ?? '';
            $rekap->jadwal->pengawas = $this->maybeUnserialize($rekap->pengawas ?? '[]');
            $rekap->jadwal->nama_mapel = $rekap->nama_mapel ?? '';
            $rekap->jadwal->kode_mapel = $rekap->kode_mapel ?? '';
            $rekap->jadwal->kode_jenis = $rekap->kode_jenis ?? '';

            $rekap->bank_soal = $this->maybeUnserialize($rekap->bank_soal ?? '');
            $rekap->kelas_bank = $this->maybeUnserialize($rekap->kelas_bank ?? '[]');
            $rekap->soal_pg = $this->maybeUnserialize($rekap->soal_pg ?? '[]');
            $rekap->soal_kompleks = $this->maybeUnserialize($rekap->soal_kompleks ?? '[]');
            $rekap->soal_jodohkan = $this->maybeUnserialize($rekap->soal_jodohkan ?? '[]');
            $rekap->soal_isian = $this->maybeUnserialize($rekap->soal_isian ?? '[]');
            $rekap->soal_esai = $this->maybeUnserialize($rekap->soal_esai ?? '[]');
            $rekap->soal_urutan = $this->maybeUnserialize($rekap->soal_urutan ?? '[]');
            $rekap->soal_cloze = $this->maybeUnserialize($rekap->soal_cloze ?? '[]');

            // Extract questions from Bank Soal snapshot if columns are empty (New Rekap Structure)
            if (isset($rekap->bank_soal->soal) && is_array($rekap->bank_soal->soal)) {
                $allSoal = $rekap->bank_soal->soal;
                // 1: PG, 2: Kompleks, 3: Jodoh, 4: Isian, 5: Esai, 6: Urutan, 7: Cloze
                if (empty($rekap->soal_pg))
                    $rekap->soal_pg = array_values(array_filter($allSoal, fn($s) => ($s->jenis ?? 0) == '1'));
                if (empty($rekap->soal_kompleks))
                    $rekap->soal_kompleks = array_values(array_filter($allSoal, fn($s) => ($s->jenis ?? 0) == '2'));
                if (empty($rekap->soal_jodohkan))
                    $rekap->soal_jodohkan = array_values(array_filter($allSoal, fn($s) => ($s->jenis ?? 0) == '3'));
                if (empty($rekap->soal_isian))
                    $rekap->soal_isian = array_values(array_filter($allSoal, fn($s) => ($s->jenis ?? 0) == '4'));
                if (empty($rekap->soal_esai))
                    $rekap->soal_esai = array_values(array_filter($allSoal, fn($s) => ($s->jenis ?? 0) == '5'));
                // Note: 6 & 7 need specialized handling if structured differently, but standard filter works for basic props
                if (empty($rekap->soal_urutan))
                    $rekap->soal_urutan = array_values(array_filter($allSoal, fn($s) => ($s->jenis ?? 0) == '6'));
                if (empty($rekap->soal_cloze))
                    $rekap->soal_cloze = array_values(array_filter($allSoal, fn($s) => ($s->jenis ?? 0) == '7'));
            }

            // Process student answers
            $jawabanSiswa = [];
            $jawabanSoal = [];
            $mapSoalNonPG = [];
            $maxScoresNonPG = [];
            $scoresNonPG = [];
            $totalUserScores = [];

            // Map non-PG questions
            if (is_array($rekap->soal_urutan)) {
                foreach ($rekap->soal_urutan as $s) {
                    $mapSoalNonPG[$s->id_soal ?? ''] = ['type' => '6', 'kunci' => $this->maybeUnserialize($s->jawaban ?? '')];
                    $maxScoresNonPG[$s->id_soal ?? ''] = 1;
                }
            }
            if (is_array($rekap->soal_cloze)) {
                foreach ($rekap->soal_cloze as $s) {
                    $mapSoalNonPG[$s->id_soal ?? ''] = ['type' => '7', 'kunci' => $this->maybeUnserialize($s->jawaban ?? '')];
                    $maxScoresNonPG[$s->id_soal ?? ''] = 1;
                }
            }

            $bankSoal = is_object($rekap->bank_soal) ? $rekap->bank_soal : (object) [];
            $agamaSoal = $bankSoal->soal_agama ?? '-';

            if ($rekap_siswa) {
                foreach ($rekap_siswa as $ks => $rekapsiswa) {
                    // Check agama filter
                    if ($agamaSoal != '-' && $agamaSoal != '0' && $agamaSoal !== $rekapsiswa->agama) {
                        continue;
                    }

                    $jawabans = $this->maybeUnserialize($rekapsiswa->jawaban ?? '[]');
                    $answers = [];

                    if (is_array($jawabans)) {
                        foreach ($jawabans as $jawaban) {
                            if (is_array($jawaban))
                                $jawaban = (object) $jawaban; // Normalize type
                            if (!is_object($jawaban))
                                continue;

                            $jawab_siswa = $jawaban->jawaban_siswa ?? '';
                            $id_soal = $jawaban->id_soal ?? '';
                            $answers[$id_soal] = $jawab_siswa;

                            // Count answers by option
                            if ($jawab_siswa == 'A') {
                                $jawabanSoal[$id_soal]['jawab_a'][] = $jawab_siswa;
                            } elseif ($jawab_siswa == 'B') {
                                $jawabanSoal[$id_soal]['jawab_b'][] = $jawab_siswa;
                            } elseif ($jawab_siswa == 'C') {
                                $jawabanSoal[$id_soal]['jawab_c'][] = $jawab_siswa;
                            } elseif ($jawab_siswa == 'D') {
                                $jawabanSoal[$id_soal]['jawab_d'][] = $jawab_siswa;
                            } elseif ($jawab_siswa == 'E') {
                                $jawabanSoal[$id_soal]['jawab_e'][] = $jawab_siswa;
                            }

                            // Score non-PG
                            if (isset($mapSoalNonPG[$id_soal])) {
                                $info = $mapSoalNonPG[$id_soal];
                                $pts = 0;
                                if ($info['type'] == '6') {
                                    $pts = (serialize($jawab_siswa) === serialize($info['kunci'])) ? 1 : 0;
                                } elseif ($info['type'] == '7') {
                                    $kunci = $info['kunci'];
                                    if (is_array($jawab_siswa) && is_array($kunci)) {
                                        $matches = 0;
                                        $count = count($kunci);
                                        foreach ($kunci as $k => $v) {
                                            $txtKunci = is_string($v) ? $v : ($v['jawaban'] ?? '');
                                            $txtJawab = $jawab_siswa[$k] ?? '';
                                            if (strtoupper(trim($txtJawab)) === strtoupper(trim($txtKunci))) {
                                                $matches++;
                                            }
                                        }
                                        $pts = $count > 0 ? $matches / $count : 0;
                                    }
                                }
                                $scoresNonPG[$id_soal][$rekapsiswa->id_siswa] = $pts;
                            }
                        }
                    }

                    $jawabanSiswa[] = $answers;
                    $totalUserScores[$rekapsiswa->id_siswa] = $rekapsiswa->nilai_input ?? 0;
                }
            }

            $data['siswa'] = count($rekap_siswa);

            // Build kunci jawaban for PG
            $kunci = [];
            if (is_array($rekap->soal_pg)) {
                foreach ($rekap->soal_pg as $soal_pg) {
                    $kunci[$soal_pg->id_soal ?? ''] = $soal_pg->jawaban ?? '';
                }
            }

            // Analyze data
            $normalizedSiswa = $this->normalizeData($jawabanSiswa, $kunci);
            $opsi = $bankSoal->opsi ?? 5;
            $hasilAnalisis = $this->analyzeData($normalizedSiswa, $kunci, $opsi);

            // Analyze non-PG
            $hasilAnalisisNonPG = $this->analyzeNonPG($scoresNonPG, $maxScoresNonPG, $totalUserScores);
            $hasilAnalisis = $hasilAnalisis + $hasilAnalisisNonPG;

            $data['analisis'] = $hasilAnalisis;

            // Filter visible questions
            $data['soal_pg'] = is_array($rekap->soal_pg) ? array_filter($rekap->soal_pg, fn($s) => ($s->tampilkan ?? '0') == '1') : [];
            $data['soal_kompleks'] = is_array($rekap->soal_kompleks) ? array_filter($rekap->soal_kompleks, fn($s) => ($s->tampilkan ?? '0') == '1') : [];
            $data['soal_jodohkan'] = is_array($rekap->soal_jodohkan) ? array_filter($rekap->soal_jodohkan, fn($s) => ($s->tampilkan ?? '0') == '1') : [];
            $data['soal_isian'] = is_array($rekap->soal_isian) ? array_filter($rekap->soal_isian, fn($s) => ($s->tampilkan ?? '0') == '1') : [];
            $data['soal_esai'] = is_array($rekap->soal_esai) ? array_filter($rekap->soal_esai, fn($s) => ($s->tampilkan ?? '0') == '1') : [];
            $data['soal_urutan'] = is_array($rekap->soal_urutan) ? array_filter($rekap->soal_urutan, fn($s) => ($s->tampilkan ?? '0') == '1') : [];
            $data['soal_cloze'] = is_array($rekap->soal_cloze) ? array_filter($rekap->soal_cloze, fn($s) => ($s->tampilkan ?? '0') == '1') : [];

            $data['jawab_siswa'] = $jawabanSoal;
            $data['info'] = $bankSoal;
            $data['jadwal'] = $rekap->jadwal;

            // Get class names
            $arrKelas = [];
            if (is_array($rekap->kelas_bank)) {
                foreach ($rekap->kelas_bank as $kls) {
                    if (isset($kls->kode_kelas)) {
                        $arrKelas[] = $kls->kode_kelas;
                    }
                }
            }
            $data['kelas'] = implode(', ', $arrKelas);

            // Get guru data if not admin
            if (!$this->isAdmin() && !$this->isPanitia()) {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $data['guru'] = $guru;
            }

            return view('cbt/analisis/data_analisis', $data);
        } catch (\Throwable $e) {
            die("<div style='padding:20px; color:red; font-family:monospace;'>
                <h3>ERROR ANALISIS</h3>
                <p><strong>Message:</strong> " . $e->getMessage() . "</p>
                <p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>
                <pre>" . $e->getTraceAsString() . "</pre>
            </div>");
        }
    }

    /**
     * Get jadwal by jadwal ID (AJAX)
     */
    public function getJadwalUjianByJadwal()
    {
        $jadwal = $this->request->getGet('jadwal');
        $tp = $this->request->getGet('thn');
        $smt = $this->request->getGet('smt');

        $info = $this->cbtModel->getJadwalById($jadwal);
        if (!$info) {
            return $this->respond(false, 'Jadwal tidak ditemukan');
        }

        $kelas = $this->maybeUnserialize($info->bank_kelas ?? '[]');
        $kelases = [];
        if (is_array($kelas)) {
            foreach ($kelas as $value) {
                $kelasId = $value->kelas_id ?? '';
                $namaKelas = $db->table('master_kelas')
                    ->select('nama_kelas')
                    ->where('id_tp', (int) ($info->id_tp ?? 0))
                    ->where('id_smt', (int) ($info->id_smt ?? 0))
                    ->where('id_kelas', (int) $kelasId)
                    ->get()
                    ->getRow();
                $kelases[$kelasId] = $namaKelas->nama_kelas ?? '';
            }
        }

        return $this->respond(true, '', $kelases);
    }

    /**
     * Normalize student answer data
     */
    private function normalizeData(array $jawabanSiswa, array $kunci): array
    {
        $normalized = [];
        foreach ($jawabanSiswa as $answers) {
            $norm = [];
            foreach ($answers as $idSoal => $jawab) {
                $norm[$idSoal] = (isset($kunci[$idSoal]) && $jawab == $kunci[$idSoal]) ? 1 : 0;
            }
            $normalized[] = $norm;
        }
        return $normalized;
    }

    /**
     * Analyze normalized data
     */
    private function analyzeData(array $normalizedSiswa, array $kunci, int $opsi = 5): array
    {
        $results = [];
        $totalScores = [];

        // Calculate total scores
        foreach ($normalizedSiswa as $siswa) {
            $totalScores[] = array_sum($siswa);
        }

        if (count($totalScores) == 0) {
            return $results;
        }

        // Sort for upper/lower groups
        $sortedScores = $totalScores;
        arsort($sortedScores);
        $keys = array_keys($sortedScores);
        $groupSize = max(1, ceil(count($keys) * 0.27));
        $upperKeys = array_slice($keys, 0, $groupSize);
        $lowerKeys = array_slice($keys, -$groupSize);

        foreach ($kunci as $idSoal => $kunciJawaban) {
            $correct = 0;
            $upperCorrect = 0;
            $lowerCorrect = 0;
            $itemScores = [];

            foreach ($normalizedSiswa as $idx => $siswa) {
                $score = $siswa[$idSoal] ?? 0;
                $correct += $score;
                $itemScores[] = $score;

                if (in_array($idx, $upperKeys)) {
                    $upperCorrect += $score;
                }
                if (in_array($idx, $lowerKeys)) {
                    $lowerCorrect += $score;
                }
            }

            $n = count($normalizedSiswa);
            $difficulty = $n > 0 ? $correct / $n : 0;
            $discrimination = $groupSize > 0 ? ($upperCorrect - $lowerCorrect) / $groupSize : 0;

            // Validity (correlation with total score)
            $validity = $this->calculatePearsonCorrelation($itemScores, $totalScores);

            // Labels
            $diffLabel = $difficulty >= 0.70 ? 'Mudah' : ($difficulty < 0.30 ? 'Sulit' : 'Sedang');

            if ($discrimination >= 0.70)
                $discLabel = 'Sangat Baik';
            elseif ($discrimination > 0.39)
                $discLabel = 'Baik';
            elseif ($discrimination < 0.20)
                $discLabel = 'Jelek';
            else
                $discLabel = 'Cukup';

            if ($validity >= 0.70)
                $valLabel = 'Sangat Valid';
            elseif ($validity < 0.30)
                $valLabel = 'Tidak Valid';
            else
                $valLabel = 'Valid';

            $results[$idSoal] = [
                'tingkat_kesulitan' => round($difficulty, 2),
                'level_kesulitan' => $diffLabel,
                'daya_pembeda' => round($discrimination, 2),
                'level_pembeda' => $discLabel,
                'validitas' => round($validity, 2),
                'level_validitas' => $valLabel,
            ];
        }

        return $results;
    }

    /**
     * Calculate Pearson correlation
     */
    private function calculatePearsonCorrelation(array $x, array $y): float
    {
        $n = count($x);
        if ($n == 0 || $n !== count($y))
            return 0;

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] ** 2;
            $sumY2 += $y[$i] ** 2;
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denominator = sqrt(($n * $sumX2 - $sumX ** 2) * ($n * $sumY2 - $sumY ** 2));

        return $denominator == 0 ? 0 : $numerator / $denominator;
    }

    /**
     * Analyze non-PG questions
     */
    private function analyzeNonPG(array $itemScores, array $maxScores, array $totalStudentScores): array
    {
        $count = count($totalStudentScores);
        if ($count == 0)
            return [];

        arsort($totalStudentScores);
        $keys = array_keys($totalStudentScores);
        $groupSize = max(1, ceil($count * 0.27));
        $upperKeys = array_slice($keys, 0, $groupSize);
        $lowerKeys = array_slice($keys, -$groupSize);

        $results = [];
        foreach ($itemScores as $idSoal => $scores) {
            $max = $maxScores[$idSoal] ?? 1;
            if ($max == 0)
                $max = 1;

            $sumUpper = 0;
            foreach ($upperKeys as $uid) {
                $sumUpper += $scores[$uid] ?? 0;
            }
            $meanUpper = $groupSize > 0 ? $sumUpper / $groupSize : 0;

            $sumLower = 0;
            foreach ($lowerKeys as $lid) {
                $sumLower += $scores[$lid] ?? 0;
            }
            $meanLower = $groupSize > 0 ? $sumLower / $groupSize : 0;

            $totalSum = array_sum($scores);
            $meanTotal = $count > 0 ? $totalSum / $count : 0;

            $difficulty = $meanTotal / $max;
            $discrimination = ($meanUpper - $meanLower) / $max;

            $diffLabel = $difficulty >= 0.70 ? 'Mudah' : ($difficulty < 0.30 ? 'Sulit' : 'Sedang');

            if ($discrimination >= 0.70)
                $discLabel = 'Sangat Baik';
            elseif ($discrimination > 0.39)
                $discLabel = 'Baik';
            elseif ($discrimination < 0.20)
                $discLabel = 'Jelek';
            else
                $discLabel = 'Cukup';

            $results[$idSoal] = [
                'tingkat_kesulitan' => round($difficulty, 2),
                'level_kesulitan' => $diffLabel,
                'daya_pembeda' => round($discrimination, 2),
                'level_pembeda' => $discLabel,
                'validitas' => 0,
                'level_validitas' => '-'
            ];

            if ($count > 1) {
                $x = [];
                $y = [];
                foreach ($totalStudentScores as $sid => $tot) {
                    $x[] = $scores[$sid] ?? 0;
                    $y[] = $tot;
                }
                $validity = $this->calculatePearsonCorrelation($x, $y);
                $results[$idSoal]['validitas'] = round($validity, 2);

                if ($validity >= 0.70)
                    $valLabel = 'Sangat Valid';
                elseif ($validity < 0.30)
                    $valLabel = 'Tidak Valid';
                else
                    $valLabel = 'Valid';
                $results[$idSoal]['level_validitas'] = $valLabel;
            }
        }
        return $results;
    }

    /**
     * Maybe unserialize data
     */
    private function maybeUnserialize($data)
    {
        if (is_string($data)) {
            $unserialized = @unserialize($data);
            if ($unserialized !== false || $data === 'b:0;') {
                return $unserialized;
            }
            $decoded = json_decode($data);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $data;
    }
}
