<?php

namespace App\Services;

/**
 * ScoringService — Engine penyekoran soal terpadu (CBT)
 *
 * Satu-satunya sumber kebenaran untuk evaluasi jawaban per tipe soal.
 * CbtModel::evaluateAnswer() dan TkaScoringService::evaluateAnswer()
 * mendelegasikan ke service ini agar kedua modul memakai logika yang sama.
 *
 * Tipe internal:
 *   - pg       : pilihan ganda (jenis lama 1)
 *   - pgk      : pilihan ganda kompleks / multi-kunci (jenis lama 2)
 *   - pgkk     : pilihan ganda kompleks Benar/Salah
 *   - jodohkan : menjodohkan (jenis lama 3)
 *   - isian    : isian singkat (jenis lama 4)
 *   - esai     : uraian / penilaian manual (jenis lama 5)
 *   - urutan   : mengurutkan (jenis lama 6)
 *   - cloze    : isian rumpang (jenis lama 7)
 */
class ScoringService
{
    /** Map jenis lama (cbt_soal.jenis, int/string) → tipe internal. */
    public const JENIS_TO_TIPE = [
        '1' => 'pg',
        '2' => 'pgk',
        '3' => 'jodohkan',
        '4' => 'isian',
        '5' => 'esai',
        '6' => 'urutan',
        '7' => 'cloze',
    ];

    /** Map tipe internal → jenis lama. */
    public const TIPE_TO_JENIS = [
        'pg'       => '1',
        'pgk'      => '2',
        'jodohkan' => '3',
        'isian'    => '4',
        'esai'     => '5',
        'urutan'   => '6',
        'cloze'    => '7',
    ];

    /** Tipe yang bisa dinilai otomatis penuh (benar/salah atau skor parsial). */
    public const TIPE_AUTO = ['pg', 'pgk', 'pgkk', 'jodohkan', 'isian', 'urutan', 'cloze'];

    /** Tipe yang dinilai manual (esai/uraian). */
    public const TIPE_MANUAL = ['esai'];

    private static ?ScoringService $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Evaluasi satu jawaban siswa terhadap kunci.
     *
     * @param string|int|null $tipe  Tipe internal ('pgk') atau jenis lama ('2'/'2.0').
     * @param string|null     $jawab Jawaban siswa (raw dari cbt_jawaban.jawaban).
     * @param string|null     $kunci Kunci jawaban (raw dari cbt_soal.jawaban).
     * @return array{skor: float, is_correct: bool}
     */
    public function skorSoal($tipe, ?string $jawab, ?string $kunci): array
    {
        $tipe = $this->normalizeTipe($tipe);

        if ($jawab === null || $jawab === '' || $tipe === '') {
            return ['skor' => 0.0, 'is_correct' => false];
        }

        switch ($tipe) {
            case 'pg':       return $this->skorPg($jawab, $kunci);
            case 'pgk':      return $this->skorPgk($jawab, $kunci);
            case 'pgkk':     return $this->skorPgkk($jawab, $kunci);
            case 'jodohkan': return $this->skorJodohkan($jawab, $kunci);
            case 'isian':    return $this->skorIsian($jawab, $kunci);
            case 'esai':     return $this->skorEsai($jawab, $kunci);
            case 'urutan':   return $this->skorUrutan($jawab, $kunci);
            case 'cloze':    return $this->skorCloze($jawab, $kunci);
        }

        return ['skor' => 0.0, 'is_correct' => false];
    }

    /**
     * Konversi jenis lama / tipe internal → tipe internal kanonik.
     */
    public function normalizeTipe($tipe): string
    {
        $raw = strtolower(trim((string) ($tipe ?? '')));
        if ($raw === '') {
            return '';
        }
        // Terima integer/float yang dikirim sebagai string ('2' atau '2.0')
        $numeric = (string) (int) $raw;
        if (isset(self::JENIS_TO_TIPE[$numeric])) {
            return self::JENIS_TO_TIPE[$numeric];
        }
        return $raw;
    }

    // ──────────────────────────────────────────────────────────────────
    // PER TIPE
    // ──────────────────────────────────────────────────────────────────

    private function skorPg(string $jawab, ?string $kunci): array
    {
        $correct = $this->normalizeAnswer($jawab) === $this->normalizeAnswer($kunci);
        return ['skor' => $correct ? 1.0 : 0.0, 'is_correct' => $correct];
    }

    private function skorPgk(string $jawab, ?string $kunci): array
    {
        $kunciArr = array_map([$this, 'normalizeAnswer'], $this->parseArrayAnswer($kunci));
        $jawabArr = array_map([$this, 'normalizeAnswer'], $this->parseArrayAnswer($jawab));

        $totalExpected = count($kunciArr);
        if ($totalExpected === 0) {
            return ['skor' => 0.0, 'is_correct' => false];
        }

        $correctCount = count(array_intersect($jawabArr, $kunciArr));
        $wrongCount   = count(array_diff($jawabArr, $kunciArr));
        $skor         = max(0.0, ($correctCount - $wrongCount) / $totalExpected);
        return ['skor' => round($skor, 4), 'is_correct' => $skor >= 0.99];
    }

    /**
     * PGKK: pilihan ganda kompleks Benar/Salah.
     *
     * Kunci & jawaban berupa JSON objek: {"A":"B","B":"S","C":"B","D":"S"}
     * (mendukung juga nilai boolean true/false dan '1'/'0').
     */
    private function skorPgkk(string $jawab, ?string $kunci): array
    {
        $kObj = $this->parseMapAnswer($kunci);
        $jObj = $this->parseMapAnswer($jawab);

        // Normalisasi: kunci lowercase (a/b/c) agar cocok dgn jawaban siswa A/B/C.
        $kMap = [];
        foreach ($kObj as $key => $val) {
            $kMap[strtolower(trim((string) $key))] = $this->normalizeBs($val);
        }
        $jMap = [];
        foreach ($jObj as $key => $val) {
            $jMap[strtolower(trim((string) $key))] = $this->normalizeBs($val);
        }

        $total = count($kMap);
        if ($total === 0) {
            return ['skor' => 0.0, 'is_correct' => false];
        }

        $correct = 0;
        foreach ($kMap as $key => $expected) {
            if (isset($jMap[$key]) && $jMap[$key] === $expected) {
                $correct++;
            }
        }
        $skor = $correct / $total;
        return ['skor' => round($skor, 4), 'is_correct' => $skor >= 0.99];
    }

    private function skorJodohkan(string $jawab, ?string $kunci): array
    {
        $kunciArr = $this->extractMatchingPairs($kunci);
        $jawabArr = $this->parseArrayAnswer($jawab);
        $skor = $this->calculateMatchingScore($kunciArr, $jawabArr);
        return ['skor' => round($skor, 4), 'is_correct' => $skor >= 0.99];
    }

    private function skorIsian(string $jawab, ?string $kunci): array
    {
        $acceptableAnswers = $this->parseAcceptableAnswers($kunci);
        $userAnswer = $this->normalizeAnswer($jawab);
        foreach ($acceptableAnswers as $acceptable) {
            if ($this->normalizeAnswer($acceptable) === $userAnswer) {
                return ['skor' => 1.0, 'is_correct' => true];
            }
        }
        return ['skor' => 0.0, 'is_correct' => false];
    }

    private function skorEsai(string $jawab, ?string $kunci): array
    {
        // Nilai numerik (0–100) dianggap sudah dinilai manual.
        if (is_numeric($jawab)) {
            $val = (float) $jawab;
            return [
                'skor'       => min(1.0, max(0.0, $val / 100)),
                'is_correct' => $val >= 75,
            ];
        }
        $correct = $this->normalizeAnswer($jawab) === $this->normalizeAnswer($kunci);
        return ['skor' => $correct ? 1.0 : 0.0, 'is_correct' => $correct];
    }

    private function skorUrutan(string $jawab, ?string $kunci): array
    {
        $kunciArr = array_map([$this, 'normalizeAnswer'], $this->parseArrayAnswer($kunci));
        $jawabArr = array_map([$this, 'normalizeAnswer'], $this->parseArrayAnswer($jawab));
        $correct = ($kunciArr === $jawabArr) && !empty($kunciArr);
        return ['skor' => $correct ? 1.0 : 0.0, 'is_correct' => $correct];
    }

    private function skorCloze(string $jawab, ?string $kunci): array
    {
        $kunciArr = $this->parseArrayAnswer($kunci);
        $jawabArr = $this->parseArrayAnswer($jawab);
        $totalBlanks = count($kunciArr);
        if ($totalBlanks === 0) {
            return ['skor' => 0.0, 'is_correct' => false];
        }

        $correctBlanks = 0;
        for ($i = 0; $i < $totalBlanks; $i++) {
            $expected = $this->normalizeAnswer($kunciArr[$i] ?? '');
            $actual   = $this->normalizeAnswer($jawabArr[$i] ?? '');
            if ($expected === $actual && $expected !== '') {
                $correctBlanks++;
            }
        }
        $skor = $correctBlanks / $totalBlanks;
        return ['skor' => round($skor, 4), 'is_correct' => $skor >= 0.99];
    }

    // ──────────────────────────────────────────────────────────────────
    // NORMALISASI & PARSING
    // ──────────────────────────────────────────────────────────────────

    private function normalizeAnswer($answer): string
    {
        if (!is_string($answer)) {
            $answer = (string) $answer;
        }

        // Remove HTML tags if any
        $answer = strip_tags($answer);

        // Decode HTML entities (e.g., &nbsp;, &amp;)
        $answer = html_entity_decode($answer, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Lowercase (use mb_strtolower for UTF-8 support)
        $answer = mb_strtolower($answer, 'UTF-8');

        // Normalize accented characters to ASCII equivalents
        $accents = ['á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ì', 'ò', 'ù', 'â', 'ê', 'î', 'ô', 'û', 'ä', 'ë', 'ï', 'ö', 'ü', 'ñ', 'ç'];
        $noAccents = ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'c'];
        $answer = str_replace($accents, $noAccents, $answer);

        // Remove various quote types (including smart quotes)
        $quotes = ['"', "'", "\u{2018}", "\u{2019}", "\u{201C}", "\u{201D}", "\u{201E}", "\u{201A}", "\u{00AB}", "\u{00BB}", "`"];
        $answer = str_replace($quotes, '', $answer);

        // Trim and collapse multiple spaces/whitespace into one
        $answer = trim(preg_replace('/\s+/u', ' ', $answer));

        // Remove trailing punctuation like . or , ; : ! ?
        $answer = rtrim($answer, '.,;:!?');

        return $answer;
    }

    /**
     * Parse jawaban yang bisa berupa JSON array, serialized, atau string polos.
     */
    private function parseArrayAnswer($answer): array
    {
        if (empty($answer)) {
            return [];
        }

        if (is_string($answer)) {
            // Try JSON first
            $decoded = json_decode($answer, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Try unserialize
            $unserialized = @unserialize($answer);
            if ($unserialized !== false && is_array($unserialized)) {
                return $unserialized;
            }

            // Return as single-element array
            return [$answer];
        }

        return is_array($answer) ? $answer : [$answer];
    }

    /**
     * Parse jawaban PGKK: JSON objek {"A":"B",...} / {"A":true,...}.
     * Fallback: array indeks → key A,B,C,...  atau nilai tunggal.
     */
    private function parseMapAnswer($answer): array
    {
        $arr = $this->parseArrayAnswer($answer);
        if (empty($arr)) {
            return [];
        }
        // JSON objek (assoc array)
        if (!array_is_list($arr)) {
            return $arr;
        }
        // Array indeks → key A,B,C,...
        $map = [];
        foreach ($arr as $i => $val) {
            $map[chr(65 + $i)] = $val;
        }
        return $map;
    }

    /**
     * Parse acceptable answers untuk isian singkat (dipisah '|').
     */
    private function parseAcceptableAnswers($kunci): array
    {
        if (empty($kunci)) {
            return [];
        }

        $arr = $this->parseArrayAnswer($kunci);
        $results = [];

        foreach ($arr as $item) {
            if (is_string($item) && strpos($item, '|') !== false) {
                $split = explode('|', $item);
                $results = array_merge($results, array_map('trim', $split));
            } else {
                $results[] = is_string($item) ? trim($item) : $item;
            }
        }

        return $results;
    }

    /**
     * Normalisasi nilai B/S untuk PGKK. Terima 'B','benar','true','1', 1, true.
     */
    private function normalizeBs($val): string
    {
        if (is_bool($val)) {
            return $val ? 'b' : 's';
        }
        $v = strtolower(trim((string) $val));
        if ($v === '1' || $v === 'true' || $v === 'benar' || $v === 'b') {
            return 'b';
        }
        if ($v === '0' || $v === 'false' || $v === 'salah' || $v === 's') {
            return 's';
        }
        return $v;
    }

    /**
     * Hitung skor parsial menjodohkan (0–1), port langsung dari CbtModel.
     */
    private function calculateMatchingScore($kunci, $jawab): float
    {
        if (!is_array($kunci) || !is_array($jawab)) {
            return ($this->normalizeAnswer($kunci) === $this->normalizeAnswer($jawab)) ? 1 : 0;
        }

        $totalPairs = count($kunci);
        if ($totalPairs === 0) {
            return 0;
        }

        $correctPairs = 0;

        if (isset($kunci[0]) && is_object($kunci[0])) {
            foreach ($kunci as $kPair) {
                $kLeft = $this->normalizeAnswer($kPair->left ?? $kPair->kiri ?? '');
                $kRight = $this->normalizeAnswer($kPair->right ?? $kPair->kanan ?? '');
                foreach ($jawab as $jPair) {
                    if (!is_object($jPair)) {
                        continue;
                    }
                    $jLeft = $this->normalizeAnswer($jPair->left ?? $jPair->kiri ?? '');
                    $jRight = $this->normalizeAnswer($jPair->right ?? $jPair->kanan ?? '');
                    if ($kLeft === $jLeft && $kRight === $jRight) {
                        $correctPairs++;
                        break;
                    }
                }
            }
        } elseif (isset($kunci[0]) && is_array($kunci[0])) {
            foreach ($kunci as $kPair) {
                $kLeft = $this->normalizeAnswer($kPair['left'] ?? $kPair['kiri'] ?? $kPair[0] ?? '');
                $kRight = $this->normalizeAnswer($kPair['right'] ?? $kPair['kanan'] ?? $kPair[1] ?? '');
                foreach ($jawab as $jPair) {
                    if (!is_array($jPair)) {
                        continue;
                    }
                    $jLeft = $this->normalizeAnswer($jPair['left'] ?? $jPair['kiri'] ?? $jPair[0] ?? '');
                    $jRight = $this->normalizeAnswer($jPair['right'] ?? $jPair['kanan'] ?? $jPair[1] ?? '');
                    if ($kLeft === $jLeft && $kRight === $jRight) {
                        $correctPairs++;
                        break;
                    }
                }
            }
        } else {
            for ($i = 0; $i < $totalPairs; $i++) {
                $kVal = $this->normalizeAnswer($kunci[$i] ?? '');
                $jVal = $this->normalizeAnswer($jawab[$i] ?? '');
                if ($kVal === $jVal && $kVal !== '') {
                    $correctPairs++;
                }
            }
        }

        return $correctPairs / $totalPairs;
    }

    /**
     * Ekstrak pasangan kunci menjodohkan dari beragam format penyimpanan (matrix tabel, JSON map, array pairs).
     */
    private function extractMatchingPairs($kunci): array
    {
        $arr = $this->parseArrayAnswer($kunci);
        if (empty($arr)) return [];

        // Matrix tabel di dalam kunci (model CBT IKAS/ANBK)
        $matrix = $arr['jawaban'] ?? $arr['tabel'] ?? null;
        if ($matrix === null && isset($arr[0]) && is_array($arr[0])) {
            $matrix = $arr;
        }

        if (is_array($matrix) && count($matrix) >= 2) {
            $headers = $matrix[0]; // e.g. ['', 'Header1', 'Header2', ...]
            $pairs = [];
            for ($r = 1; $r < count($matrix); $r++) {
                $row = $matrix[$r];
                $leftText = $row[0] ?? '';
                for ($c = 1; $c < count($row); $c++) {
                    if (!empty($row[$c]) && ($row[$c] === 1 || $row[$c] === '1' || $row[$c] === true)) {
                        $rightText = $headers[$c] ?? '';
                        $pairs[] = [
                            'left'  => $leftText,
                            'right' => $rightText,
                            'kiri'  => $leftText,
                            'kanan' => $rightText,
                        ];
                    }
                }
            }
            if (!empty($pairs)) return $pairs;
        }

        // Format pairs atau map langsung
        return $arr;
    }
}
