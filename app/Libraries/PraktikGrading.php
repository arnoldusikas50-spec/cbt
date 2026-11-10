<?php

namespace App\Libraries;

use App\Models\CbtPraktikModel;
use App\Models\CbtPraktikHasilModel;
use App\Models\CbtPraktikRubrikModel;
use App\Models\CbtPraktikRubrikLevelModel;
use App\Traits\KktpTrait;

/**
 * Library for automatic grading of practical exams
 * Supports: pattern matching, test cases, syntax checking, similarity analysis
 * Kurikulum Merdeka: KKTP-based grading
 */
class PraktikGrading
{
    use KktpTrait;
    protected $praktikModel;
    protected $hasilModel;
    protected $rubrikModel;
    protected $levelModel;

    public function __construct()
    {
        $this->praktikModel = new CbtPraktikModel();
        $this->hasilModel = new CbtPraktikHasilModel();
        $this->rubrikModel = new CbtPraktikRubrikModel();
        $this->levelModel = new CbtPraktikRubrikLevelModel();
    }

    /**
     * Grade a submission automatically
     */
    public function gradeSubmission(int $praktikId, int $hasilId): array
    {
        $hasil = $this->hasilModel->find($hasilId);
        $praktik = $this->praktikModel->find($praktikId);

        if (!$hasil || !$praktik) {
            return ['status' => false, 'message' => 'Data not found'];
        }

        // Get rubrics configured for auto-grading
        $rubriks = $this->rubrikModel->getRubricsForAutoGrading($praktikId);

        if (empty($rubriks)) {
            return ['status' => false, 'message' => 'No auto-grading rubrics configured'];
        }

        $totalScore = 0;
        $rincianNilai = [];

        foreach ($rubriks as $rubrik) {
            $rubrikScore = $this->gradeRubrik($rubrik, $hasil, $praktik);
            $totalScore += $rubrikScore['nilai'];
            $rincianNilai[$rubrik->id] = $rubrikScore;
        }

        // Update hasil with auto-graded score
        $updateData = [
            'nilai_otomatis' => $totalScore,
            'rincian_nilai_rubrik' => json_encode($rincianNilai),
        ];

        // If no manual grading yet, set nilai_akhir to auto score
        if ($hasil->nilai_manual === null || $hasil->nilai_manual === '') {
            $updateData['nilai_akhir'] = $totalScore;
        }

        $kktp = $this->getKktp($totalScore);

        $this->hasilModel->update($hasilId, $updateData);

        return [
            'status' => true,
            'nilai_otomatis' => $totalScore,
            'kktp' => $kktp,
            'predikat' => $kktp['predikat'],
            'singkatan' => $kktp['singkatan'],
            'tercapai' => $this->isTuntas($totalScore, $this->getKkm($praktik)),
            'rincian_nilai' => $rincianNilai,
            'message' => 'Auto-grading completed'
        ];
    }

    /**
     * Grade a single rubric criterion
     */
    private function gradeRubrik(object $rubrik, object $hasil, object $praktik): array
    {
        $levels = $this->levelModel->getLevelsByRubrik($rubrik->id);
        $jawaban = $hasil->kode_jawaban ?? $hasil->jawaban_teks ?? '';

        $result = [
            'rubrik_id' => $rubrik->id,
            'kriteria' => $rubrik->nama_kriteria,
            'bobot' => $rubrik->bobot_persen,
            'nilai' => 0,
            'level' => null,
            'catatan' => '',
            'indikator_terpenuhi' => [],
        ];

        // Pattern matching grading
        if (!empty($levels)) {
            foreach ($levels as $level) {
                $indikator = json_decode($level->indikator_otomatis ?? '{}', true);
                
                if (empty($indikator)) {
                    continue;
                }

                $matched = $this->checkIndikator($jawaban, $indikator, $praktik->jenis_praktik);
                
                if ($matched) {
                    $result['nilai'] = ($level->nilai_max * $rubrik->bobot_persen) / 100;
                    $result['level'] = $level->level;
                    $result['catatan'] = "Mencapai level: {$level->nama_level}";
                    $result['indikator_terpenuhi'] = $matched;
                    break;
                }
            }
        }

        // Syntax checking for coding
        if ($praktik->jenis_praktik === 'coding' && !empty($hasil->kode_jawaban)) {
            $syntaxScore = $this->checkSyntax($hasil->kode_jawaban, $hasil->bahasa_pemrograman);
            if ($syntaxScore['valid']) {
                $result['catatan'] .= " | Syntax valid";
            } else {
                $result['catatan'] .= " | Syntax error: " . $syntaxScore['error'];
            }
        }

        return $result;
    }

    /**
     * Check if answer matches indicators
     */
    private function checkIndikator(string $jawaban, array $indikator, string $jenisPraktik): ?array
    {
        $matched = [];
        $totalIndikator = count($indikator);
        $matchedCount = 0;

        foreach ($indikator as $key => $value) {
            switch ($key) {
                case 'required_patterns':
                    $patterns = is_array($value) ? $value : [$value];
                    $patternsMatched = 0;
                    foreach ($patterns as $pattern) {
                        if (preg_match('/' . preg_quote($pattern, '/') . '/i', $jawaban)) {
                            $patternsMatched++;
                        }
                    }
                    if ($patternsMatched >= count($patterns) * 0.8) { // 80% threshold
                        $matched[$key] = $patternsMatched . '/' . count($patterns);
                        $matchedCount++;
                    }
                    break;

                case 'forbidden_patterns':
                    $patterns = is_array($value) ? $value : [$value];
                    $hasForbidden = false;
                    foreach ($patterns as $pattern) {
                        if (preg_match('/' . preg_quote($pattern, '/') . '/i', $jawaban)) {
                            $hasForbidden = true;
                            break;
                        }
                    }
                    if (!$hasForbidden) {
                        $matched[$key] = 'none_found';
                        $matchedCount++;
                    }
                    break;

                case 'min_lines':
                    $lines = count(array_filter(explode("\n", $jawaban), 'trim'));
                    if ($lines >= (int)$value) {
                        $matched[$key] = $lines;
                        $matchedCount++;
                    }
                    break;

                case 'max_lines':
                    $lines = count(array_filter(explode("\n", $jawaban), 'trim'));
                    if ($lines <= (int)$value) {
                        $matched[$key] = $lines;
                        $matchedCount++;
                    }
                    break;

                case 'contains_keywords':
                    $keywords = is_array($value) ? $value : [$value];
                    $found = [];
                    foreach ($keywords as $keyword) {
                        if (stripos($jawaban, $keyword) !== false) {
                            $found[] = $keyword;
                        }
                    }
                    if (count($found) >= count($keywords) * 0.7) { // 70% threshold
                        $matched[$key] = $found;
                        $matchedCount++;
                    }
                    break;

                case 'complexity_score':
                    // Check code complexity (simple heuristic)
                    $complexity = $this->calculateComplexity($jawaban, $jenisPraktik);
                    if ($complexity >= (int)$value) {
                        $matched[$key] = $complexity;
                        $matchedCount++;
                    }
                    break;
            }
        }

        // Return matched if at least 70% indicators met
        return ($matchedCount / max($totalIndikator, 1) >= 0.7) ? $matched : null;
    }

    /**
     * Check syntax for coding submissions
     */
    private function checkSyntax(string $code, ?string $language): array
    {
        $result = ['valid' => true, 'error' => ''];

        if (empty($language)) {
            return $result;
        }

        switch (strtolower($language)) {
            case 'python':
                // Basic Python syntax check using patterns
                $openBrackets = substr_count($code, '(') - substr_count($code, ')');
                $openSquare = substr_count($code, '[') - substr_count($code, ']');
                $openCurly = substr_count($code, '{') - substr_count($code, '}');
                $indentCheck = preg_match_all('/^[ \t]+/m', $code, $matches);

                if ($openBrackets !== 0) {
                    $result = ['valid' => false, 'error' => 'Unbalanced parentheses'];
                } elseif ($openSquare !== 0) {
                    $result = ['valid' => false, 'error' => 'Unbalanced brackets'];
                } elseif ($openCurly !== 0) {
                    $result = ['valid' => false, 'error' => 'Unbalanced braces'];
                }
                break;

            case 'php':
                // Check for basic PHP syntax
                if (!str_starts_with(trim($code), '<?php') && !str_starts_with(trim($code), '<?')) {
                    // Not necessarily an error, just a note
                }

                $openBrackets = substr_count($code, '{') - substr_count($code, '}');
                $openParen = substr_count($code, '(') - substr_count($code, ')');

                if ($openBrackets !== 0) {
                    $result = ['valid' => false, 'error' => 'Unbalanced braces'];
                } elseif ($openParen !== 0) {
                    $result = ['valid' => false, 'error' => 'Unbalanced parentheses'];
                }
                break;

            case 'javascript':
            case 'js':
                $openBrackets = substr_count($code, '{') - substr_count($code, '}');
                $openParen = substr_count($code, '(') - substr_count($code, ')');
                $openSquare = substr_count($code, '[') - substr_count($code, ']');

                if ($openBrackets !== 0 || $openParen !== 0 || $openSquare !== 0) {
                    $result = ['valid' => false, 'error' => 'Unbalanced brackets/parentheses'];
                }
                break;

            default:
                // Generic check
                break;
        }

        return $result;
    }

    /**
     * Calculate code complexity (simple heuristic)
     */
    private function calculateComplexity(string $code, string $language): int
    {
        $complexity = 0;

        // Count control structures
        $controlStructures = ['if', 'else', 'elseif', 'for', 'while', 'switch', 'case', 'foreach'];
        foreach ($controlStructures as $structure) {
            $complexity += substr_count(strtolower($code), $structure);
        }

        // Count functions/methods
        $complexity += substr_count($code, 'function');
        $complexity += substr_count($code, 'def '); // Python
        $complexity += substr_count($code, 'class');

        // Count lines
        $lines = count(array_filter(explode("\n", $code), 'trim'));
        $complexity += min($lines / 10, 10); // Cap at 10 points

        return (int) $complexity;
    }

    /**
     * Run test cases for coding submission
     */
    public function runTestCases(string $code, array $testCases, ?string $language): array
    {
        $results = [];
        $passed = 0;
        $total = count($testCases);

        foreach ($testCases as $test) {
            $testResult = [
                'name' => $test['name'] ?? 'Test',
                'input' => $test['input'] ?? '',
                'expected' => $test['expected'] ?? '',
                'passed' => false,
                'output' => '',
            ];

            // Simulate running test (in production, this would execute in sandbox)
            // This is a simplified version
            $testResult['output'] = $this->simulateExecution($code, $test['input'], $language);
            $testResult['passed'] = trim($testResult['output']) === trim($testResult['expected']);

            if ($testResult['passed']) {
                $passed++;
            }

            $results[] = $testResult;
        }

        return [
            'test_results' => $results,
            'passed' => $passed,
            'total' => $total,
            'score' => $total > 0 ? ($passed / $total * 100) : 0,
        ];
    }

    /**
     * Simulate code execution (placeholder for sandbox)
     */
    private function simulateExecution(string $code, string $input, ?string $language): string
    {
        // This is a placeholder - in production, use proper sandboxed execution
        // Return a mock output based on simple pattern matching
        
        if (preg_match('/print\s*\(\s*["\'](.+)["\']\s*\)/', $code, $matches)) {
            return $matches[1];
        }

        if (preg_match('/echo\s+["\']?(.+?)["\']?\s*;/', $code, $matches)) {
            return $matches[1];
        }

        if (preg_match('/console\.log\s*\(\s*["\'](.+)["\']\s*\)/', $code, $matches)) {
            return $matches[1];
        }

        return "Output simulation";
    }

    /**
     * Batch grade all submissions for a practical exam
     */
    public function batchGrade(int $praktikId): array
    {
        $submissions = $this->hasilModel->where('praktik_id', $praktikId)
                                       ->where('status_pengerjaan', 'selesai')
                                       ->findAll();

        $results = [
            'total' => count($submissions),
            'graded' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($submissions as $submission) {
            $gradeResult = $this->gradeSubmission($praktikId, $submission->id);
            
            if ($gradeResult['status']) {
                $results['graded']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'hasil_id' => $submission->id,
                'siswa_id' => $submission->siswa_id,
                'status' => $gradeResult['status'],
                'message' => $gradeResult['message']
            ];
        }

        return $results;
    }

    /**
     * Get grading statistics for a practical exam
     */
    public function getGradingStatistics(int $praktikId): array
    {
        $submissions = $this->hasilModel->where('praktik_id', $praktikId)->findAll();

        $total = count($submissions);
        $autoGraded = 0;
        $manuallyGraded = 0;
        $pending = 0;
        $scores = [];
        $kktpDistribusi = ['Mahir' => 0, 'Cakap' => 0, 'Layak' => 0, 'Baru Berkembang' => 0];

        foreach ($submissions as $submission) {
            if (!empty($submission->nilai_otomatis)) {
                $autoGraded++;
            }
            if (!empty($submission->nilai_manual)) {
                $manuallyGraded++;
            }
            if (empty($submission->nilai_akhir)) {
                $pending++;
            }
            if (!empty($submission->nilai_akhir)) {
                $scores[] = $submission->nilai_akhir;
                $kktp = $this->getKktp($submission->nilai_akhir);
                $kktpDistribusi[$kktp['predikat']]++;
            }
        }

        return [
            'total' => $total,
            'auto_graded' => $autoGraded,
            'manually_graded' => $manuallyGraded,
            'pending' => $pending,
            'average_score' => !empty($scores) ? array_sum($scores) / count($scores) : 0,
            'min_score' => !empty($scores) ? min($scores) : 0,
            'max_score' => !empty($scores) ? max($scores) : 0,
            'kktp_distribusi' => $kktpDistribusi,
        ];
    }

    /**
     * Generate complete grade report with KKTP descriptive predicates
     */
    public function generateGradeReport(int $hasilId): array
    {
        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil) {
            return ['status' => false, 'message' => 'Hasil not found'];
        }

        $praktik = $this->praktikModel->find($hasil->praktik_id);
        $rubriks = $this->rubrikModel->getRubrikPraktik($hasil->praktik_id, false);

        $nilaiAkhir = $hasil->nilai_akhir ?? 0;
        $kktp = $this->getKktp($nilaiAkhir);

        // Analisis per rubrik
        $rincianRubrik = [];
        $rincianNilai = json_decode($hasil->rincian_nilai_rubrik ?? '{}', true);
        $deskripsiKriteria = [];

        foreach ($rubriks as $rubrik) {
            $nilaiRubrik = $rincianNilai[$rubrik->id]['nilai'] ?? 0;
            $kktpRubrik = $this->getKktp($nilaiRubrik);

            $rincianRubrik[] = [
                'kriteria' => $rubrik->nama_kriteria,
                'bobot' => $rubrik->bobot_persen,
                'skor' => $nilaiRubrik,
                'kktp' => $kktpRubrik,
                'predikat' => $kktpRubrik['predikat'],
                'singkatan' => $kktpRubrik['singkatan'],
            ];

            $deskripsiKriteria[] = "{$rubrik->nama_kriteria}: {$kktpRubrik['singkatan']} ({$nilaiRubrik})";
        }

        // Generate deskripsi naratif grade report
        $deskripsi = $this->generateDeskripsiRapor($rincianNilai, $rubriks, $nilaiAkhir);

        return [
            'status' => true,
            'hasil_id' => $hasilId,
            'siswa' => $hasil->nama_siswa ?? 'Unknown',
            'nilai_akhir' => $nilaiAkhir,
            'kktp' => $kktp,
            'predikat' => $kktp['predikat'],
            'singkatan' => $kktp['singkatan'],
            'tercapai' => $this->isTuntas($nilaiAkhir, $this->getKkm($praktik)),
            'deskripsi' => $deskripsi,
            'rincian_per_kriteria' => $rincianRubrik,
            'feedback_guru' => $hasil->feedback_guru,
            'tanggal_submit' => $hasil->waktu_submit,
        ];
    }
}
