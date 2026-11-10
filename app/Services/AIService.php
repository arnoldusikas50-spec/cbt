<?php

namespace App\Services;

use App\Models\DashboardModel;

class AIService
{
    protected $db;
    protected $dashboard;

    public function __construct()
    {
        $this->dashboard = new \App\Models\DashboardModel();
        $this->db        = \Config\Database::connect();
    }

    private function logToDb(string $level, string $msg)
    {
        try {
            $this->db->table('ai_debug_logs')->insert([
                'level' => $level,
                'message' => $msg,
            ]);
        } catch (\Throwable $e) {}
    }

    public function getApiKey(int $userId = 0, bool $isGuru = false): string
    {
        $setting = $this->dashboard->getSetting();
        $apiKey = trim((string) ($setting->api_key ?? ''));

        if ($isGuru && $userId > 0) {
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $guru = $this->dashboard->getDataGuruByUserId($userId, (int) ($tp->id_tp ?? 0), (int) ($smt->id_smt ?? 0));
            if ($guru && !empty($guru->api_key)) {
                $apiKey = trim((string) $guru->api_key);
            }
        }

        return $apiKey;
    }

    public function generateContent(string $apiKey, string $prompt, string $modelName = ''): array
    {
        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'API Key is empty',
                'data' => null
            ];
        }

        $modelAliasMap = [
            // Seluruh lini model 1.x/2.x sudah disunset (404/410 di v1beta).
            // Google merekomendasikan gemini-3.5-flash-lite sebagai pengganti.
            'gemini-3.6-flash'                  => 'gemini-3.5-flash-lite',
            'gemini-3.5-flash-lite'             => 'gemini-3.5-flash-lite',
            'gemini-3.5-flash'                  => 'gemini-3.5-flash-lite',
            'gemini-2.5-flash'                  => 'gemini-3.5-flash-lite',
            'gemini-2.5-pro'                    => 'gemini-3.5-flash-lite',
            'gemini-2.0-flash'                  => 'gemini-3.5-flash-lite',
            'gemini-2.0-flash-lite'             => 'gemini-3.5-flash-lite',
            'gemini-1.5-flash-8b'              => 'gemini-3.5-flash-lite',
            'gemini-1.5-pro-latest'             => 'gemini-3.5-flash-lite',
            'gemini-1.5-flash'                  => 'gemini-3.5-flash-lite',
            'gemini-1.5-pro'                    => 'gemini-3.5-flash-lite',
        ];

        if (isset($modelAliasMap[$modelName])) {
            $modelName = $modelAliasMap[$modelName];
        }

        $models = [
            'gemini-3.5-flash-lite',
            'gemini-3.6-flash',
        ];

        // Specific requested model priority
        $requestedModel = $modelName;
        if ($requestedModel !== '' && in_array($requestedModel, $models)) {
            $models = array_values(array_diff($models, [$requestedModel]));
            array_unshift($models, $requestedModel);
        }
        
        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.6,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 16384,
                'responseMimeType' => 'application/json',
            ],
        ];

        $primaryError = '';
        $lastError = '';
        foreach ($models as $idx => $model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

            $startTime = microtime(true);
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($error) {
                    $errText = 'Curl error (' . $model . '): ' . $error;
                    if ($idx === 0) $primaryError = $errText;
                    $lastError = $errText;
                    break;
                }
                
                if ($httpCode === 200 && is_string($response)) {
                    $elapsed = round(microtime(true) - $startTime, 2);
                    $this->logToDb('SUCCESS', "[AIService] Handled by $model in {$elapsed}s" . ($idx > 0 ? " (Fallback)" : ""));
                    return [
                        'success' => true,
                        'data' => $response,
                        'model' => $model,
                        'elapsed' => $elapsed,
                        'message' => 'Success'
                    ];
                }

                $decoded = json_decode((string) $response, true);
                $msg = (string) ($decoded['error']['message'] ?? 'Unknown error');
                $errText = 'HTTP ' . $httpCode . ' (' . $model . '): ' . $msg;
                
                if ($idx === 0) $primaryError = $errText;
                $lastError = $errText;

                if ($httpCode === 429 && $attempt === 1) {
                    sleep(2);
                    continue;
                }
                break;
            }
        }

        // Use primary error (from requested model) if it was a quota issue, 
        // as fallback errors (like 404) are just confusing noise.
        $finalError = (str_contains($primaryError, '429')) ? $primaryError : $lastError;

        $waitSeconds = 0;
        if (str_contains($finalError, '429') && preg_match('/retry in ([\d\.]+)s/i', $finalError, $matches)) {
            $waitSeconds = (int) ceil((float) $matches[1]);
        }

        $userMsg = $finalError;
        if (str_contains($finalError, '429')) {
            $userMsg = 'Batas Kuota Penggunaan API Gemini Tercapai (HTTP 429).' . 
                ($waitSeconds > 0 ? " Silakan tunggu {$waitSeconds} detik sebelum mencoba lagi." : " Silakan periksa atau ganti API Key Gemini di menu Pengaturan.");
        }

        return [
            'success' => false,
            'message' => $userMsg,
            'raw_error' => $finalError,
            'data' => null,
            'wait_seconds' => $waitSeconds
        ];
    }

    public function parseJsonResponse(string $response): array
    {
        $decoded = json_decode($response, true);
        if (isset($decoded['error'])) {
            return [
                'success' => false,
                'message' => is_array($decoded['error']) ? ($decoded['error']['message'] ?? 'AI error') : (string) $decoded['error'],
                'data' => null
            ];
        }

        $text = (string) ($decoded['candidates'][0]['content']['parts'][0]['text'] ?? '');
        if ($text === '') {
            $finishReason = $decoded['candidates'][0]['finishReason'] ?? 'UNKNOWN';
            $this->logToDb('ERROR', '[AIService] Empty response content. Reason: ' . $finishReason . ' | Full response: ' . substr($response, 0, 2000));
            log_message('error', '[AIService] AI response blocked or empty. FinishReason: ' . $finishReason . ' | Raw: ' . substr($response, 0, 500));
            return [
                'success' => false,
                'message' => 'AI response is empty or blocked (Reason: ' . $finishReason . ').',
                'data' => null
            ];
        }

        // Clean markdown blocks
        $text = preg_replace('/^\s*```(?:json)?\s*/si', '', $text);
        $text = preg_replace('/\s*```\s*$/si', '', $text);
        $text = trim($text);

        // Try to parse as-is first
        $parsed = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'success' => true,
                'data' => $parsed,
                'message' => 'Success'
            ];
        }

        // Sanitize string to prevent JSON_ERROR_CTRL_CHAR due to raw newlines or tabs from AI
        // Use JSON_UNESCAPED approach - preserve escaped sequences within strings
        $sanitized = $text;
        
        // Remove control characters except allowed whitespace
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sanitized);
        
        // Normalize line endings that are not part of JSON string values
        $sanitized = str_replace(["\r\n", "\r"], "\n", $sanitized);
        
        // For multi-line JSON arrays with newlines between objects, normalize to single line
        // But preserve newlines within string values (they should be escaped as \n)
        $sanitized = preg_replace('/\n\s*([\]\}])/', '$1', $sanitized); // newline before closing bracket
        $sanitized = preg_replace('/([\{\[,])\s*\n/', '$1', $sanitized); // newline after opening bracket or comma
        
        // Remove trailing commas before closing brackets
        $sanitized = preg_replace('/,\s*([\]\}])/', '$1', $sanitized);
        
        // Collapse multiple spaces to single space
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        $sanitized = trim($sanitized);

        $parsed = json_decode($sanitized, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'success' => true,
                'data' => $parsed,
                'message' => 'Recovered from whitespace'
            ];
        }

        // Recovery for partial JSON if bracketed - extract just the array
        if (preg_match('/\[.*\{.*\}.*\]/s', $text, $matches)) {
            $candidate = $matches[0];
            // Clean up the candidate
            $candidate = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $candidate);
            $candidate = preg_replace('/,\s*\]/', ']', $candidate);
            $candidate = preg_replace('/\s+/', ' ', $candidate);
            
            $parsed = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'success' => true,
                    'data' => $parsed,
                    'message' => 'Recovered from malformed response'
                ];
            }
        }

        // Last resort: try to fix common JSON syntax errors
        $fixed = $text;
        // Remove BOM if present
        $fixed = preg_replace('/^\xEF\xBB\xBF/', '', $fixed);
        // Fix escaped quotes that might be double-escaped
        $fixed = str_replace('\\"', '"', $fixed);
        // Ensure proper array brackets
        if (!str_starts_with(trim($fixed), '[')) {
            $fixed = '[' . $fixed . ']';
        }
        
        $parsed = json_decode($fixed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'success' => true,
                'data' => $parsed,
                'message' => 'Recovered with fixes'
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid JSON format: ' . json_last_error_msg(),
            'data' => null,
            'raw' => substr($text, 0, 1000)
        ];
    }

    private function maybeUnserialize($data)
    {
        if (is_string($data)) {
            $trimmed = trim($data);
            if (str_starts_with($trimmed, 'a:') || str_starts_with($trimmed, 'O:')) {
                $un = @unserialize($trimmed);
                if ($un !== false) {
                    return $un;
                }
            }
            $json = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        return $data;
    }

    /**
     * Bangun prompt untuk membuat varian soal dari soal sumber (paket paralel).
     */
    public function buildVariantPrompt($soal, $jenis, $subtipePg2, $jumlah)
    {
        $soalHtml = (string) ($soal->soal ?? '');
        $soalTeks = trim(strip_tags(html_entity_decode($soalHtml, ENT_QUOTES, 'UTF-8')));
        if ($soalTeks === '') {
            $soalTeks = $soalHtml;
        }

        $intro = "Kamu adalah penyusun soal profesional. Buatlah {$jumlah} VARIAN baru dari satu soal berikut, untuk pembuatan PAKET SOAL PARALEL (paket A, B, dst).\n";
        $intro .= "RUANG LINGKUP MATERI, KOMPETENSI, dan KUNCI LOGIKA WAJIB SAMA. Namun NARASI, NAMA, ANGKA, KONTEKS, dan KALIMAT dibuat BERBEDA agar soal varian tidak identik dengan sumbernya.\n";
        $intro .= "Jangan mengubah substansi materi. Pastikan setiap varian berdiri sendiri (tidak butuh soal sumber).\n\n";
        $intro .= "SOAL SUMBER:\n" . $soalTeks . "\n\n";

        $format = '';
        $aturan = '';

        switch ($jenis) {
            case 1:
                $opsi = [];
                foreach (['a', 'b', 'c', 'd', 'e'] as $h) {
                    $key = 'opsi_' . $h;
                    $opsi[$h] = trim(strip_tags(html_entity_decode((string) ($soal->{$key} ?? ''), ENT_QUOTES, 'UTF-8')));
                }
                $opsiTeks = [];
                foreach ($opsi as $h => $txt) {
                    if ($txt !== '') {
                        $opsiTeks[] = strtoupper($h) . ". " . $txt;
                    }
                }
                $intro .= "OPSI SUMBER:\n" . implode("\n", $opsiTeks) . "\n\n";
                $intro .= "KUNCI SUMBER: " . strtoupper((string) $soal->jawaban) . "\n\n";
                $format = '[{"soal": "...", "opsi_a": "...", "opsi_b": "...", "opsi_c": "...", "opsi_d": "...", "opsi_e": "...", "jawaban": "b"}]';
                $aturan = "Kunci jawaban varian tersebar bervariasi (a/b/c/d/e), jangan selalu di posisi yang sama dgn sumber.";
                break;

            case 2:
                $opsi = $this->maybeUnserialize((string) ($soal->opsi_a ?? ''));
                if (is_string($opsi)) {
                    $opsi = json_decode($opsi, true);
                }
                if (!is_array($opsi)) {
                    $opsi = [];
                }
                $jawaban = $this->maybeUnserialize((string) ($soal->jawaban ?? ''));
                if (is_string($jawaban)) {
                    $jawaban = json_decode($jawaban, true);
                }
                $opsiTeks = [];
                foreach ($opsi as $k => $v) {
                    $opsiTeks[] = strtoupper($k) . ". " . trim(strip_tags(html_entity_decode((string) $v, ENT_QUOTES, 'UTF-8')));
                }
                $intro .= "PERNYATAAN SUMBER:\n" . implode("\n", $opsiTeks) . "\n\n";
                if ($subtipePg2 === 'pgkk') {
                    $intro .= "KUNCI SUMBER (Benar/Salah per pernyataan): " . json_encode($jawaban, JSON_UNESCAPED_UNICODE) . "\n\n";
                    $format = '[{"soal": "...", "opsi": {"a": "Pernyataan 1", "b": "Pernyataan 2", "c": "Pernyataan 3", "d": "Pernyataan 4", "e": "Pernyataan 5"}, "jawaban": {"a": "B", "b": "S", "c": "B", "d": "S", "e": "B"}}]';
                    $aturan = "Jenis PGKK (Benar/Salah): jawaban adalah MAP {huruf: \"B\"|\"S\"} untuk SETIAP pernyataan, dan pola benar/salah bervariasi antar varian.";
                } else {
                    $intro .= "KUNCI SUMBER (hurut opsi benar): " . json_encode(is_array($jawaban) ? array_values($jawaban) : $jawaban, JSON_UNESCAPED_UNICODE) . "\n\n";
                    $format = '[{"soal": "...", "opsi": {"a": "Pernyataan 1", "b": "Pernyataan 2", "c": "Pernyataan 3", "d": "Pernyataan 4", "e": "Pernyataan 5"}, "jawaban": ["a", "c", "e"]}]';
                    $aturan = "Jenis PGK (pilih beberapa benar): jawaban adalah ARRAY berisi 2-4 huruf opsi yang benar; posisi kunci bervariasi antar varian.";
                }
                break;

            case 3:
                $jawaban = $this->maybeUnserialize((string) ($soal->jawaban ?? ''));
                if (is_string($jawaban)) {
                    $jawaban = json_decode($jawaban, true);
                }
                $intro .= "KUNCI SUMBER (matriks menjodohkan): " . json_encode($jawaban, JSON_UNESCAPED_UNICODE) . "\n\n";
                $format = '[{"soal": "...", "pairs": [{"kiri": "Premis A", "kanan": "Respons B"}]}]';
                $aturan = "Menjodohkan: output pairs (pasangan kiri-kanan) yang benar; pasangan/urutan boleh diacak.";
                break;

            case 4:
                $intro .= "KUNCI SUMBER (isian): " . (string) ($soal->jawaban ?? '') . "\n\n";
                $format = '[{"soal": "...", "jawaban": "kata/kalimat kunci"}]';
                $aturan = "Isian singkat: jawaban harus spesifik & tidak ambigu; variasikan kata/kalimat soalnya.";
                break;

            case 5:
                $intro .= "KUNCI/RUBRIK SUMBER (esai): " . (string) ($soal->jawaban ?? '') . "\n\n";
                $format = '[{"soal": "...", "jawaban": "rubrik/pedoman penskoran"}]';
                $aturan = "Esai/uraian: jawaban = rubrik penskoran/kriteria kunci yang jelas.";
                break;

            case 6:
                $opsi = $this->maybeUnserialize((string) ($soal->opsi_a ?? ''));
                if (is_string($opsi)) {
                    $opsi = json_decode($opsi, true);
                }
                $items = is_array($opsi) && isset($opsi['items']) ? $opsi['items'] : [];
                $intro .= "URUTAN SUMBER: " . json_encode($items, JSON_UNESCAPED_UNICODE) . "\n\n";
                $format = '[{"soal": "...", "items": ["Langkah 1", "Langkah 2", "Langkah 3", "Langkah 4"]}]';
                $aturan = "Urutan: items berisi urutan yang BENAR (berurutan).";
                break;

            case 7:
                $intro .= "KUNCI SUMBER (rumpang): " . (string) ($soal->jawaban ?? '') . "\n\n";
                $format = '[{"soal": "... (gunakan [[jawaban]] untuk setiap rumpang)", "jawaban": "..."}]';
                $aturan = "Isian rumpang: teks soal WAJIB berisi penanda [[jawaban]] untuk tiap rumpang.";
                break;
        }

        $prompt = $intro;
        $prompt .= "FORMAT OUTPUT: HANYA JSON array (tanpa markdown, tanpa teks lain):\n" . $format . "\n\n";
        $prompt .= "ATURAN:\n";
        $prompt .= "- Output {$jumlah} objek varian.\n";
        $prompt .= "- " . $aturan . "\n";
        $prompt .= "- Kunci jawaban PASTI benar terhadap isi varian (jangan salah salin dari sumber).\n";
        $prompt .= "- Bahasa sesuai soal sumber (Indonesia, kecuali sumber berbahasa Inggris).\n";
        $prompt .= "- JANGAN salin soal sumber secara utuh.\n";

        return $prompt;
    }

    /**
     * Bangun prompt untuk membuat VARIAN untuk satu paket (sekumpulan soal sumber)
     * dalam satu call AI. Output: array varian dgn urutan SAMA seperti soal sumber.
     */

    /**
     * Bangun prompt untuk membuat SOAL MIRIP dari satu soal sumber.
     * Beda dari buildVariantPrompt:
     *   - Variant  → narasi/angka berbeda, STRUKTUR soal sama (untuk paket paralel)
     *   - Similar  → SKENARIO berbeda TOTAL, kompetensi & level Bloom TETAP sama
     *               (untuk memperkaya bank soal tanpa duplikasi skenario)
     */
    public function buildSimilarPrompt($soal, int $jenis, string $subtipePg2, int $jumlah): string
    {
        $soalHtml = (string) ($soal->soal ?? '');
        $soalTeks = trim(strip_tags(html_entity_decode($soalHtml, ENT_QUOTES, 'UTF-8')));
        if ($soalTeks === '') {
            $soalTeks = $soalHtml;
        }

        $intro  = "Kamu adalah penyusun soal profesional untuk asesmen nasional.\n";
        $intro .= "Berikut adalah SOAL REFERENSI yang dijadikan ACUAN KOMPETENSI (bukan template):\n\n";
        $intro .= "--- SOAL REFERENSI ---\n" . $soalTeks . "\n";

        $formatOpsi  = '';
        $aturanJenis = '';

        switch ($jenis) {
            case 1:
                $opsiTeks = [];
                foreach (['a', 'b', 'c', 'd', 'e'] as $h) {
                    $key = 'opsi_' . $h;
                    $txt = trim(strip_tags(html_entity_decode((string) ($soal->{$key} ?? ''), ENT_QUOTES, 'UTF-8')));
                    if ($txt !== '') {
                        $opsiTeks[] = strtoupper($h) . '. ' . $txt;
                    }
                }
                if (!empty($opsiTeks)) {
                    $intro .= "OPSI REFERENSI:\n" . implode("\n", $opsiTeks) . "\n";
                }
                $intro .= "KUNCI REFERENSI: " . strtoupper((string) ($soal->jawaban ?? '')) . "\n";
                $formatOpsi  = '[{"soal": "...", "opsi_a": "...", "opsi_b": "...", "opsi_c": "...", "opsi_d": "...", "opsi_e": "...", "jawaban": "b"}]';
                $aturanJenis = "Pilihan Ganda: 5 opsi (a-e), satu jawaban benar, posisi kunci bervariasi.";
                break;

            case 2:
                $opsi = $this->maybeUnserialize((string) ($soal->opsi_a ?? ''));
                if (is_string($opsi)) { $opsi = json_decode($opsi, true); }
                if (!is_array($opsi)) { $opsi = []; }
                $opsiTeks = [];
                foreach ($opsi as $k => $v) {
                    $opsiTeks[] = strtoupper($k) . '. ' . trim(strip_tags(html_entity_decode((string) $v, ENT_QUOTES, 'UTF-8')));
                }
                $intro .= "PERNYATAAN REFERENSI:\n" . implode("\n", $opsiTeks) . "\n";
                if ($subtipePg2 === 'pgkk') {
                    $formatOpsi  = '[{"soal": "...", "opsi": {"a": "Pernyataan 1", "b": "Pernyataan 2", "c": "Pernyataan 3", "d": "Pernyataan 4", "e": "Pernyataan 5"}, "jawaban": {"a": "B", "b": "S", "c": "B", "d": "S", "e": "B"}}]';
                    $aturanJenis = "PG Kompleks Benar/Salah: jawaban MAP {huruf: \"B\"|\"S\"} untuk setiap pernyataan.";
                } else {
                    $formatOpsi  = '[{"soal": "...", "opsi": {"a": "Pernyataan 1", "b": "Pernyataan 2", "c": "Pernyataan 3", "d": "Pernyataan 4", "e": "Pernyataan 5"}, "jawaban": ["a", "c"]}]';
                    $aturanJenis = "PG Kompleks (pilih beberapa benar): jawaban ARRAY huruf opsi yang benar, 2-4 kunci.";
                }
                break;

            case 3:
                $formatOpsi  = '[{"soal": "...", "pairs": [{"kiri": "Premis A", "kanan": "Respons B"}]}]';
                $aturanJenis = "Menjodohkan: output pairs pasangan kiri-kanan yang benar.";
                break;

            case 4:
                $formatOpsi  = '[{"soal": "...", "jawaban": "kata/kalimat kunci"}]';
                $aturanJenis = "Isian singkat: jawaban spesifik & tidak ambigu.";
                break;

            case 5:
                $formatOpsi  = '[{"soal": "...", "jawaban": "rubrik/pedoman penskoran"}]';
                $aturanJenis = "Esai/uraian: jawaban berisi rubrik penskoran yang jelas.";
                break;

            case 6:
                $formatOpsi  = '[{"soal": "...", "items": ["Langkah 1", "Langkah 2", "Langkah 3", "Langkah 4"]}]';
                $aturanJenis = "Urutan: items berisi urutan langkah yang BENAR (berurutan).";
                break;

            case 7:
                $formatOpsi  = '[{"soal": "... (gunakan [[jawaban]] untuk setiap rumpang)", "jawaban": "..."}]';
                $aturanJenis = "Isian rumpang: teks soal WAJIB berisi penanda [[jawaban]] untuk tiap rumpang.";
                break;
        }

        $intro .= "--- AKHIR SOAL REFERENSI ---\n\n";

        $hasTable = stripos($soalHtml, '<table') !== false || stripos($soalTeks, 'tabel') !== false;
        $hasSvgOrChart = stripos($soalHtml, '<svg') !== false || stripos($soalTeks, 'grafik') !== false || stripos($soalTeks, 'diagram') !== false;

        $prompt  = $intro;
        $prompt .= "TUGAS: Buat {$jumlah} soal BARU yang memenuhi semua kriteria berikut:\n\n";
        $prompt .= "WAJIB SAMA dengan soal referensi:\n";
        $prompt .= "  - Kompetensi yang diuji (indikator dan tujuan pembelajaran)\n";
        $prompt .= "  - Level berpikir Bloom (C1-C6) dan kedalaman analisis\n";
        $prompt .= "  - Tingkat kesulitan dan kompleksitas soal\n";
        $prompt .= "  - Jenis/format soal\n\n";
        $prompt .= "WAJIB BERBEDA TOTAL dari soal referensi:\n";
        $prompt .= "  - Skenario, konteks, dan latar cerita\n";
        $prompt .= "  - Tokoh, lokasi, waktu, dan objek yang digunakan\n";
        $prompt .= "  - Pendekatan penyajian soal\n\n";
        $prompt .= "DILARANG:\n";
        $prompt .= "  - Hanya mengganti angka, nama, atau kata kunci dari referensi\n";
        $prompt .= "  - Memparafrase soal referensi dengan kalimat berbeda tapi struktur sama\n";
        $prompt .= "  - Menyalin sebagian besar narasi soal referensi\n\n";

        if ($hasTable) {
            $prompt .= "PANDUAN KHUSUS TABEL DATA (STANDAR NASIONAL PUSMENDIK):\n";
            $prompt .= "- Soal referensi menyajikan data tabel. Soal baru WAJIB menyajikan data dalam bentuk tabel HTML responsif yang rapi:\n";
            $prompt .= "  <div class='fw-bold text-dark mb-1' style='font-size:13px;'>Tabel 1. [Judul Data Baru]</div>\n";
            $prompt .= "  <div class='table-responsive my-2'><table class='table table-bordered table-sm text-center align-middle' style='width:100%;max-width:100%;border-collapse:collapse;margin:8px 0;font-size:13px;background:#fff;'>\n";
            $prompt .= "  <thead style='background:#e2e8f0;color:#1e293b;font-weight:bold;'><tr><th style='padding:6px 10px;border:1px solid #cbd5e1;'>...</th></tr></thead>\n";
            $prompt .= "  <tbody><tr><td style='padding:6px 10px;border:1px solid #cbd5e1;'>...</td></tr></tbody></table></div>\n";
            $prompt .= "  <div class='text-muted fst-italic mt-1' style='font-size:11px;'>Sumber: [Nama Instansi/Diolah 2026]</div>\n";
            $prompt .= "- Gunakan variabel, kategori, dan angka kuantitatif baru yang realistis, logis, dan setara tingkat kesulitannya.\n\n";
        }
        if ($hasSvgOrChart) {
            $prompt .= "PANDUAN KHUSUS GRAFIK / DIAGRAM VISUAL (STANDAR NASIONAL INLINE SVG):\n";
            $prompt .= "- Soal referensi berbasis visual. Soal baru WAJIB menyajikan visual vektor setara menggunakan tag:\n";
            $prompt .= "  <svg viewBox='0 0 500 260' xmlns='http://www.w3.org/2000/svg' style='max-width:100%;width:100%;height:auto;display:block;margin:12px auto;background:#ffffff;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-family:system-ui,-apple-system,sans-serif;'>\n";
            $prompt .= "  <text x='250' y='24' text-anchor='middle' font-size='13' font-weight='bold' fill='#1e293b'>[Judul Grafik / Diagram Baru]</text>\n";
            $prompt .= "  [Gambarkan elemen visual: sumbu, gridline, data bar/line dengan label angka jelas di atasnya, bentuk geometri/diagram]\n";
            $prompt .= "  </svg>\n";
            $prompt .= "- Pastikan label data tertera jelas di atas batang/titik data, warna modern kontras, dan proporsi simetris.\n\n";
        }

        $prompt .= "PANDUAN KREASI SKENARIO:\n";
        $prompt .= "  Jika referensi menggunakan skenario A (mis: harga daging naik saat Lebaran),\n";
        $prompt .= "  buat skenario B yang berbeda konteks tapi menguji KONSEP YANG SAMA\n";
        $prompt .= "  (mis: kelangkaan BBM saat banjir, atau lonjakan harga tiket mudik).\n\n";
        $prompt .= "FORMAT OUTPUT: HANYA JSON array (tanpa markdown, tanpa teks lain):\n" . $formatOpsi . "\n\n";
        $prompt .= "ATURAN FORMAT:\n";
        $prompt .= "- Output tepat {$jumlah} objek soal.\n";
        $prompt .= "- " . $aturanJenis . "\n";
        $prompt .= "- Kunci jawaban PASTI benar sesuai konten soal yang dibuat.\n";
        $prompt .= "- Bahasa sesuai soal referensi (Indonesia kecuali referensi berbahasa Inggris).\n";
        $prompt .= "- Setiap soal berdiri sendiri, dapat dipahami tanpa soal referensi.\n";

        return $prompt;
    }

    public function buildVariantPaketPrompt(array $soals, string $paketLabel): string
    {
        $intro = "Kamu adalah penyusun soal profesional. Buatlah VARIAN untuk {$paketLabel} dari daftar soal berikut, untuk pembuatan PAKET SOAL PARALEL.\n";
        $intro .= "RUANG LINGKUP MATERI, KOMPETENSI, dan KUNCI LOGIKA setiap soal WAJIB SAMA dengan sumbernya. Namun NARASI, NAMA, ANGKA, KONTEKS, dan KALIMAT dibuat BERBEDA agar tidak identik.\n";
        $intro .= "Jangan mengubah substansi materi. Setiap varian berdiri sendiri (tidak butuh soal sumber).\n\n";
        $intro .= "Jumlah soal sumber: " . count($soals) . ". Output harus array dengan jumlah objek YANG SAMA dan URUTAN SAMA persis seperti input.\n\n";

        $daftar = [];
        $idx = 0;
        foreach ($soals as $s) {
            $idx++;
            $jenis = (int) ($s->jenis ?? 1);
            $subtipePg2 = strtolower(trim((string) ($s->tipe_soal ?? 'pgk')));
            $soalTeks = trim(strip_tags(html_entity_decode((string) ($s->soal ?? ''), ENT_QUOTES, 'UTF-8')));
            if ($soalTeks === '') {
                $soalTeks = (string) ($s->soal ?? '');
            }

            $jenisLabel = [
                1 => 'Pilihan Ganda (jawaban: huruf a-e)',
                2 => 'PG Kompleks (' . ($subtipePg2 === 'pgkk' ? 'Benar/Salah, jawaban: map {huruf:"B"|"S"}' : 'pilih beberapa benar, jawaban: array huruf') . ')',
                3 => 'Menjodohkan (output: pairs [{kiri, kanan}])',
                4 => 'Isian Singkat (jawaban: kata/kunci)',
                5 => 'Esai/Uraian (jawaban: rubrik penskoran)',
                6 => 'Urutan (output: items urutan benar)',
                7 => 'Isian Rumpang (teks pakai [[jawaban]], jawaban: teks)',
            ];
            $label = $jenisLabel[$jenis] ?? 'Pilihan Ganda';

            $blok = "### SOAL {$idx} (JENIS: {$label})\nSoal: {$soalTeks}\n";

            if ($jenis === 1) {
                $opsiTeks = [];
                foreach (['a', 'b', 'c', 'd', 'e'] as $h) {
                    $key = 'opsi_' . $h;
                    $txt = trim(strip_tags(html_entity_decode((string) ($s->{$key} ?? ''), ENT_QUOTES, 'UTF-8')));
                    if ($txt !== '') {
                        $opsiTeks[] = strtoupper($h) . ". " . $txt;
                    }
                }
                $blok .= "Opsi:\n" . implode("\n", $opsiTeks) . "\n";
                $blok .= "Kunci: " . strtoupper((string) ($s->jawaban ?? 'a')) . "\n";
            } elseif ($jenis === 2) {
                $opsi = $this->maybeUnserialize((string) ($s->opsi_a ?? ''));
                if (is_string($opsi)) $opsi = json_decode($opsi, true);
                if (!is_array($opsi)) {
                    // Fallback bank hasil kloning kartu: opsi tersimpan terpisah di opsi_a..opsi_e
                    $opsi = [];
                    foreach (['a', 'b', 'c', 'd', 'e'] as $h) {
                        $key = 'opsi_' . $h;
                        $txt = trim(strip_tags(html_entity_decode((string) ($s->{$key} ?? ''), ENT_QUOTES, 'UTF-8')));
                        if ($txt !== '') {
                            $opsi[$h] = $txt;
                        }
                    }
                }
                $jawaban = $this->maybeUnserialize((string) ($s->jawaban ?? ''));
                if (is_string($jawaban)) $jawaban = json_decode($jawaban, true);
                $opsiTeks = [];
                foreach ($opsi as $k => $v) {
                    $opsiTeks[] = strtoupper($k) . ". " . trim(strip_tags(html_entity_decode((string) $v, ENT_QUOTES, 'UTF-8')));
                }
                $blok .= "Pernyataan:\n" . implode("\n", $opsiTeks) . "\n";
                $blok .= "Kunci: " . json_encode($jawaban, JSON_UNESCAPED_UNICODE) . "\n";
            } elseif ($jenis === 6) {
                $opsi = $this->maybeUnserialize((string) ($s->opsi_a ?? ''));
                if (is_string($opsi)) $opsi = json_decode($opsi, true);
                $items = is_array($opsi) && isset($opsi['items']) ? $opsi['items'] : [];
                if (empty($items)) {
                    // Fallback bank hasil kloning kartu: urutan tersedia di opsi_a..opsi_e (atau jawaban berupa indeks)
                    $items = [];
                    foreach (['a', 'b', 'c', 'd', 'e'] as $h) {
                        $key = 'opsi_' . $h;
                        $txt = trim(strip_tags(html_entity_decode((string) ($s->{$key} ?? ''), ENT_QUOTES, 'UTF-8')));
                        if ($txt !== '') {
                            $items[] = $txt;
                        }
                    }
                }
                $blok .= "Urutan benar: " . json_encode($items, JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                $blok .= "Kunci: " . (string) ($s->jawaban ?? '') . "\n";
            }

            $daftar[] = $blok;
        }

        $intro .= implode("\n", $daftar) . "\n\n";

        $intro .= "FORMAT OUTPUT: HANYA JSON array (tanpa markdown, tanpa teks lain), jumlah objek = " . count($soals) . ":\n";
        $intro .= '[{"soal": "...", "opsi_a": "...", "opsi_b": "...", "opsi_c": "...", "opsi_d": "...", "opsi_e": "...", "jawaban": "b"}, ...]' . "\n";
        $intro .= "- Untuk jenis 2 (PGK): pakai {\"soal\", \"opsi\": {\"a\":...,\"b\":...}, \"jawaban\": [\"a\",\"c\"]}; PGKK: \"jawaban\": {\"a\":\"B\",\"b\":\"S\"}.\n";
        $intro .= "- Untuk jenis 3: {\"soal\", \"pairs\": [{\"kiri\":..., \"kanan\":...}]}.\n";
        $intro .= "- Untuk jenis 4/5/7: {\"soal\", \"jawaban\": \"...\"}.\n";
        $intro .= "- Untuk jenis 6: {\"soal\", \"items\": [\"Langkah 1\", ...]}.\n";
        $intro .= "ATURAN:\n";
        $intro .= "- Kunci jawaban PASTI benar terhadap isi varian, posisinya bervariasi antar varian.\n";
        $intro .= "- Bahasa sesuai soal sumber.\n";
        $intro .= "- JANGAN salin soal sumber secara utuh.\n";

        return $intro;
    }

    /**
     * Normalisasi satu item varian (hasil AI) menjadi row siap-insert untuk cbt_soal.
     * Konsisten dengan save_ai_generated di Cbtbanksoal.
     */
    public function variantToSoalRow(array $g, int $jenis, string $subtipePg2, int $bankId, int $tujuanId, string $subtesCode, ?object $src = null): array
    {
        $item = [
            'bank_id'     => $bankId,
            'jenis'       => $jenis,
            'soal'        => !empty($g['soal']) ? (string) $g['soal'] : (string) ($src->soal ?? 'Soal Varian'),
            'subtes_code' => $subtesCode,
            'bobot_irt'   => 1.0,
            'tampilkan'   => 1,
            'ai_generated' => 1,
            'tujuan_id'   => $tujuanId > 0 ? $tujuanId : null,
            'opsi_a'      => '',
            'opsi_b'      => '',
            'opsi_c'      => '',
            'opsi_d'      => '',
            'opsi_e'      => '',
            'jawaban'     => ''
        ];

        if ($jenis == 1) {
            $rawOpsiA = $g['opsi_a'] ?? $g['opsi_A'] ?? $g['opsi']['a'] ?? $g['opsi']['A'] ?? $g['options']['a'] ?? $g['options']['A'] ?? (isset($g['opsi'][0]) ? $g['opsi'][0] : (isset($g['options'][0]) ? $g['options'][0] : null));
            $rawOpsiB = $g['opsi_b'] ?? $g['opsi_B'] ?? $g['opsi']['b'] ?? $g['opsi']['B'] ?? $g['options']['b'] ?? $g['options']['B'] ?? (isset($g['opsi'][1]) ? $g['opsi'][1] : (isset($g['options'][1]) ? $g['options'][1] : null));
            $rawOpsiC = $g['opsi_c'] ?? $g['opsi_C'] ?? $g['opsi']['c'] ?? $g['opsi']['C'] ?? $g['options']['c'] ?? $g['options']['C'] ?? (isset($g['opsi'][2]) ? $g['opsi'][2] : (isset($g['options'][2]) ? $g['options'][2] : null));
            $rawOpsiD = $g['opsi_d'] ?? $g['opsi_D'] ?? $g['opsi']['d'] ?? $g['opsi']['D'] ?? $g['options']['d'] ?? $g['options']['D'] ?? (isset($g['opsi'][3]) ? $g['opsi'][3] : (isset($g['options'][3]) ? $g['options'][3] : null));
            $rawOpsiE = $g['opsi_e'] ?? $g['opsi_E'] ?? $g['opsi']['e'] ?? $g['opsi']['E'] ?? $g['options']['e'] ?? $g['options']['E'] ?? (isset($g['opsi'][4]) ? $g['opsi'][4] : (isset($g['options'][4]) ? $g['options'][4] : null));

            $item['opsi_a'] = (string) ($rawOpsiA ?? ($src->opsi_a ?? ''));
            $item['opsi_b'] = (string) ($rawOpsiB ?? ($src->opsi_b ?? ''));
            $item['opsi_c'] = (string) ($rawOpsiC ?? ($src->opsi_c ?? ''));
            $item['opsi_d'] = (string) ($rawOpsiD ?? ($src->opsi_d ?? ''));
            $item['opsi_e'] = (string) ($rawOpsiE ?? ($src->opsi_e ?? ''));

            $pgKey = strtolower(trim((string) ($g['jawaban'] ?? ($src->jawaban ?? 'a'))));
            $pgKey = preg_replace('/[^a-e]/', '', $pgKey);
            $item['jawaban'] = $pgKey !== '' ? $pgKey : (string) ($src->jawaban ?? 'a');
        } elseif ($jenis == 2) {
            $alpha = range('a', 'z');
            $rawOpsi = $g['opsi'] ?? $g['options'] ?? null;
            if (!is_array($rawOpsi)) {
                $rawOpsi = [];
                foreach (['a', 'b', 'c', 'd', 'e'] as $h) {
                    $val = $g['opsi_' . $h] ?? $g['opsi_' . strtoupper($h)] ?? null;
                    if ($val !== null && $val !== '') {
                        $rawOpsi[$h] = $val;
                    }
                }
            }
            if (empty($rawOpsi) && $src) {
                $srcOpsi = $this->maybeUnserialize((string) ($src->opsi_a ?? ''));
                if (is_string($srcOpsi)) $srcOpsi = json_decode($srcOpsi, true);
                if (is_array($srcOpsi)) $rawOpsi = $srcOpsi;
            }

            $opsi = [];
            $i = 0;
            foreach ($rawOpsi as $k => $v) {
                $char = $alpha[$i] ?? $k;
                $opsi[$char] = $v;
                $i++;
            }
            $item['opsi_a'] = json_encode($opsi, JSON_UNESCAPED_UNICODE);
            $item['tipe_soal'] = $subtipePg2;
            if ($subtipePg2 === 'pgkk') {
                $bsMap = [];
                $j = 0;
                $rawJawaban = $g['jawaban'] ?? [];
                if (!is_array($rawJawaban) && $src) {
                    $rawJawaban = json_decode((string) ($src->jawaban ?? ''), true) ?: [];
                }
                foreach ($rawOpsi as $k => $v) {
                    $char = $alpha[$j] ?? $k;
                    $raw = $rawJawaban[$char] ?? $rawJawaban[strtoupper($char)] ?? $rawJawaban[$k] ?? $rawJawaban[strtoupper($k)] ?? 'B';
                    $bsMap[$char] = strtoupper((string) $raw) === 'S' ? 'S' : 'B';
                    $j++;
                }
                $item['jawaban'] = json_encode($bsMap, JSON_UNESCAPED_UNICODE);
            } else {
                $kuncis = $g['jawaban'] ?? [];
                if (!is_array($kuncis) && $src) {
                    $kuncis = json_decode((string) ($src->jawaban ?? ''), true) ?: [];
                }
                $mappedKuncis = [];
                $j = 0;
                foreach ($rawOpsi as $k => $v) {
                    $char = $alpha[$j] ?? $k;
                    if (in_array($char, $kuncis, true) || in_array(strtolower($char), $kuncis, true)
                        || in_array($k, $kuncis, true) || in_array(strtolower($k), $kuncis, true)) {
                        $mappedKuncis[] = $char;
                    }
                    $j++;
                }
                $item['jawaban'] = json_encode($mappedKuncis, JSON_UNESCAPED_UNICODE);
            }
        } elseif ($jenis == 3) {
            $pairs = $g['pairs'] ?? [];
            if (empty($pairs) && $src) {
                $item['jawaban'] = (string) ($src->jawaban ?? '');
            } else {
                $rightSide = [];
                foreach ($pairs as $p) {
                    if (isset($p['kanan'])) {
                        $rightSide[] = $p['kanan'];
                    }
                }
                $rightSide = array_values(array_unique($rightSide));
                $matrix = [];
                $header = ['#'];
                foreach ($rightSide as $r) $header[] = $r;
                $matrix[] = $header;
                foreach ($pairs as $p) {
                    $row = [$p['kiri'] ?? ''];
                    foreach ($rightSide as $rs) {
                        $row[] = (($p['kanan'] ?? '') == $rs) ? '1' : '0';
                    }
                    $matrix[] = $row;
                }
                $item['jawaban'] = json_encode(['model' => '1', 'type' => '1', 'jawaban' => $matrix], JSON_UNESCAPED_UNICODE);
            }
        } elseif ($jenis == 4 || $jenis == 5) {
            $item['jawaban'] = (string) ($g['jawaban'] ?? ($src->jawaban ?? ''));
        } elseif ($jenis == 6) {
            $items = $g['items'] ?? $g['opsi'] ?? $g['options'] ?? [];
            if (!is_array($items) || count($items) < 2) {
                if ($src) {
                    $srcOpsi = $this->maybeUnserialize((string) ($src->opsi_a ?? ''));
                    if (is_string($srcOpsi)) $srcOpsi = json_decode($srcOpsi, true);
                    if (is_array($srcOpsi) && isset($srcOpsi['items'])) {
                        $items = $srcOpsi['items'];
                    }
                }
            }
            if (!is_array($items) || count($items) < 2) {
                $items = ['Langkah 1', 'Langkah 2'];
            }
            $item['opsi_a'] = json_encode(['items' => array_values($items)], JSON_UNESCAPED_UNICODE);
            $item['jawaban'] = json_encode(range(0, count($items) - 1));
        } elseif ($jenis == 7) {
            $soalText = $g['soal'] ?? ($src->soal ?? '');
            preg_match_all('/\[\[(.*?)\]\]/', $soalText, $matches);
            $answers = [];
            if (!empty($matches[1])) {
                foreach ($matches[1] as $mi => $val) {
                    $answers[($mi + 1)] = $val;
                }
            } else if ($src) {
                $answers = json_decode((string) ($src->jawaban ?? ''), true) ?: [];
            }
            $item['jawaban'] = json_encode($answers, JSON_UNESCAPED_UNICODE);
        }

        return $item;
    }

    /**
     * Buat bank paket varian (B..E) dari sekumpulan soal sumber, satu call AI per chunk (≤10 soal).
     * Dipakai bersama oleh Cbtkartusoal (generateBankFromKartu) dan Cbtbanksoal (generate_bank_variants).
     *
     * @param object $db          Koneksi CodeIgniter DB
     * @param string $apiKey      API Key Gemini
     * @param string $aiModel     Model AI
     * @param array  $sourceSoals Hasil get() soal sumber (urutan nomor_soal ASC)
     * @param array  $bankTemplate Template data bank varian (tanpa kode/nama/id)
     * @param string $baseKode    Prefix kode, mis. 'PAS-MAT11'
     * @param string $baseNama    Prefix nama, mis. 'PAS Matematika Kelas 11'
     * @param int    $jumlahPaket Jumlah paket total (2-5)
     * @param string $subtesCode  Kode sub-tes untuk soal varian
     * @return array {variant_banks:[], errors:[], total_soal:int}
     */
    public function generateVariantBanks($db, string $apiKey, string $aiModel, array $sourceSoals, array $bankTemplate, string $baseKode, string $baseNama, int $jumlahPaket, string $subtesCode): array
    {
        $variantBanks = [];
        $errors = [];
        $totalSoal = 0;

        if (empty($sourceSoals)) {
            return ['variant_banks' => $variantBanks, 'errors' => ['Tidak ada soal sumber untuk dijadikan varian.'], 'total_soal' => $totalSoal];
        }

        $letters = ['B', 'C', 'D', 'E'];
        for ($p = 2; $p <= max(2, min(5, $jumlahPaket)); $p++) {
            $letter = $letters[$p - 2] ?? 'X';
            $vKode = trim($baseKode) . '-' . $letter;
            $vNama = !empty($baseNama) ? "Paket Soal {$letter} - {$baseNama}" : $vKode;

            $existVariant = $db->table('cbt_bank_soal')->where('bank_kode', $vKode)->get()->getRow();
            if ($existVariant) {
                $errors[] = "Paket {$letter} ({$vKode}) sudah ada — dilewati.";
                continue;
            }

            $vBankData = $bankTemplate;
            $vBankData['bank_kode'] = $vKode;
            $vBankData['bank_nama'] = $vNama;
            $db->table('cbt_bank_soal')->insert($vBankData);
            $vBankId = (int) $db->insertID();
            if ($vBankId <= 0) {
                $errors[] = "Paket {$letter}: gagal membuat bank.";
                continue;
            }
            $variantBanks[] = ['bank_id' => $vBankId, 'kode' => $vKode, 'letter' => $letter];

            // Pecah jadi chunk kecil agar output JSON tidak melebihi batas token AI
            $varianItems = [];
            $aiOk = true;
            $chunks = array_chunk($sourceSoals, 10);
            foreach ($chunks as $chunkIdx => $chunk) {
                $promptV = $this->buildVariantPaketPrompt($chunk, $vNama . ' (bagian ' . ($chunkIdx + 1) . ')');
                $aiV = $this->generateContent($apiKey, $promptV, $aiModel);
                if (!$aiV['success']) {
                    $errors[] = "Paket {$letter}: AI gagal di bagian " . ($chunkIdx + 1) . " (" . $aiV['message'] . ')';
                    $aiOk = false;
                    break;
                }
                $parseV = $this->parseJsonResponse($aiV['data']);
                if (!$parseV['success'] || !is_array($parseV['data'])) {
                    $errors[] = "Paket {$letter}: output AI tidak valid di bagian " . ($chunkIdx + 1) . " (" . ($parseV['message'] ?? 'parse error') . ')';
                    $aiOk = false;
                    break;
                }
                $varianItems = array_merge($varianItems, array_values($parseV['data']));
            }
            if (!$aiOk) {
                // Hapus bank kosong agar tidak muncul di daftar bank soal
                $db->table('cbt_bank_soal')->where('id_bank', $vBankId)->delete();
                foreach ($variantBanks as $vk => $vb) {
                    if ($vb['bank_id'] === $vBankId) {
                        unset($variantBanks[$vk]);
                    }
                }
                $variantBanks = array_values($variantBanks);
                continue;
            }

            $noVByJenis = [];
            $insertedV = 0;
            foreach ($sourceSoals as $si => $src) {
                $g = $varianItems[$si] ?? null;
                if (!is_array($g)) {
                    $g = [
                        'soal'    => $src->soal ?? '',
                        'opsi_a'  => $src->opsi_a ?? '',
                        'opsi_b'  => $src->opsi_b ?? '',
                        'opsi_c'  => $src->opsi_c ?? '',
                        'opsi_d'  => $src->opsi_d ?? '',
                        'opsi_e'  => $src->opsi_e ?? '',
                        'jawaban' => $src->jawaban ?? '',
                    ];
                }

                $jenisV = (int) ($src->jenis ?? 1);
                $subtipeV = strtolower(trim((string) ($src->tipe_soal ?? 'pgk')));
                if ($jenisV !== 2) $subtipeV = 'pgk';
                if (!in_array($subtipeV, ['pgk', 'pgkk'], true)) $subtipeV = 'pgk';

                $tujuanV = (int) ($src->tujuan_id ?? 0);
                if ($tujuanV <= 0 && !empty($src->id_soal)) {
                    $ksi = $db->table('cbt_kartu_soal_item')->where('soal_id', (int) $src->id_soal)->get()->getRow();
                    if ($ksi && !empty($ksi->id_tujuan)) {
                        $tujuanV = (int) $ksi->id_tujuan;
                    }
                }

                $row = $this->variantToSoalRow($g, $jenisV, $subtipeV, $vBankId, $tujuanV, $subtesCode, $src);

                // Garansi nomor urut soal selalu berurutan PER JENIS (1, 2, 3... N per jenis),
                // konsisten dengan konvensi nomor_soal bank soal (per-jenis, bukan global).
                $row['nomor_soal'] = $noVByJenis[$jenisV] = ($noVByJenis[$jenisV] ?? 0) + 1;
                $row['created_on'] = time();
                $row['updated_on'] = time();
                $row['mapel_id']   = (int) ($src->mapel_id ?? 0);
                $row['deskripsi']  = trim((string) ($src->deskripsi ?? ''));
                $row['kesulitan']  = (int) ($src->kesulitan ?? 2);

                $db->table('cbt_soal')->insert($row);
                $newSoalId = (int) $db->insertID();
                $insertedV++;
            }

            $totalSoal += $insertedV;
            if ($insertedV === 0) {
                $errors[] = "Paket {$letter}: varian tidak berhasil dibuat (0 soal).";
                $db->table('cbt_bank_soal')->where('id_bank', $vBankId)->delete();
                foreach ($variantBanks as $vk => $vb) {
                    if ($vb['bank_id'] === $vBankId) {
                        unset($variantBanks[$vk]);
                    }
                }
                $variantBanks = array_values($variantBanks);
            }
        }

        return ['variant_banks' => $variantBanks, 'errors' => $errors, 'total_soal' => $totalSoal];
    }

    public function generateImage(string $apiKey, string $prompt, string $aspectRatio = '1:1'): array
    {
        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'API Key is empty',
                'data' => null
            ];
        }

        // Mapping aspect ratio to what Imagen expects (if different)
        // Public API expects "1:1", "16:9", "3:4", etc.
        
        $payload = [
            'instances' => [
                ['prompt' => $prompt]
            ],
            'parameters' => [
                'sampleCount' => 1,
                'aspectRatio' => $aspectRatio
            ]
        ];

        // Some accounts use v1beta, some v1. 
        // We'll try v1beta first as it's the standard for Imagen 3 for now.
        // If it fails with "model not found", it might be a region or access issue.
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-001:predict?key=' . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'Network error (cURL): ' . $error,
                'data' => null
            ];
        }

        $decoded = json_decode((string) $response, true);

        if ($httpCode === 200 && isset($decoded['predictions'][0]['bytesBase64Encoded'])) {
            return [
                'success' => true,
                'data' => 'data:image/jpeg;base64,' . $decoded['predictions'][0]['bytesBase64Encoded'],
                'message' => 'Success'
            ];
        }

        // Handle errors
        $errorMsg = $decoded['error']['message'] ?? 'Unknown API error';
        
        // If not found, try a simpler model name or version as fallback
        if ($httpCode === 404 && strpos($errorMsg, 'not found') !== false) {
             // Fallback attempt with imagen-3.0-generate-002 if 001 failed, or vice versa?
             // Actually let's just report the error clearly.
             $errorMsg = "Model Imagen 3.0 Not Found. Pastikan API Key Anda memiliki akses ke Generative AI Image Generation (Imagen 3).";
        }

        if ($httpCode === 403) {
            $errorMsg = "Akses ditolak (403). Pastikan API Key benar dan Billing/Kuota mencukupi.";
        }

        return [
            'success' => false,
            'message' => $errorMsg . ' (HTTP ' . $httpCode . ')',
            'data' => null
        ];
    }
}
