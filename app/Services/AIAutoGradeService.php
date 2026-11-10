<?php

namespace App\Services;

use App\Models\CbtModel;
use App\Models\DashboardModel;

class AIAutoGradeService
{
    private $aiService;
    private $cbtModel;
    private $dashboard;
    private $db;

    public function __construct()
    {
        $this->aiService = new AIService();
        $this->cbtModel = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Core grading logic for a single answer
     */
    public function gradeAnswer(array $params): array
    {
        $apiKey = $params['apiKey'];
        $question = $params['question'];
        $referenceAnswer = $params['referenceAnswer'];
        $studentAnswer = $params['studentAnswer'];
        $maxPoints = (float) $params['maxPoints'];
        $jenisSoal = $params['jenisSoal'] ?? 5; // Default 5 (Essay)

        $prompt = $this->buildGradingPrompt($question, $referenceAnswer, $studentAnswer, $maxPoints, $jenisSoal);
        
        $aiResult = $this->aiService->generateContent($apiKey, $prompt);
        if (!$aiResult['success']) {
            return $aiResult;
        }

        $parsed = $this->aiService->parseJsonResponse($aiResult['data']);
        if (!$parsed['success']) {
            return $parsed;
        }

        $data = $parsed['data'];
        
        // Final score calculation ensuring it doesn't exceed maxPoints
        $scoreRaw = (float) ($data['score'] ?? 0);
        $score = min($maxPoints, max(0, $scoreRaw));
        
        return [
            'success' => true,
            'score'    => $score,
            'feedback' => (string) ($data['feedback'] ?? 'Tidak ada feedback'),
            'analysis' => (string) ($data['analysis'] ?? ''),
        ];
    }

    private function buildGradingPrompt($question, $reference, $student, $max, $jenis): string
    {
        $jenisLabel = ($jenis == 4) ? "Isian Singkat" : "Uraian/Essay";
        
        $prompt = "Kamu adalah sistem penilaian otomatis (CBT-AI Evaluator) yang objektif dan adil.\n";
        $prompt .= "Tugasmu adalah memberikan skor dan feedback korektif untuk jawaban siswa berdasarkan referensi kunci jawaban.\n\n";
        
        $prompt .= "PARAMATER PENILAIAN:\n";
        $prompt .= "- Jenis Soal: {$jenisLabel}\n";
        $prompt .= "- Pertanyaan: \"{$question}\"\n";
        $prompt .= "- Referensi Kunci Jawaban: \"{$reference}\"\n";
        $prompt .= "- Jawaban Siswa: \"{$student}\"\n";
        $prompt .= "- Skor Maksimum: {$max}\n\n";

        $prompt .= "ATURAN PENILAIAN:\n";
        $prompt .= "1. Gunakan 'Semantic Similarity' (Kesamaan Makna). Jika maknanya sama meski kata-katanya berbeda, berikan skor tinggi.\n";
        $prompt .= "2. Toleransi kesalahan ejaan (typos) yang tidak mengubah makna.\n";
        $prompt .= "3. Jika jawaban kosong atau sama sekali tidak relevan, skor adalah 0.\n";
        $prompt .= "4. Berikan skor parsial jika jawaban benar sebagian.\n";
        $prompt .= "5. Feedback harus dalam Bahasa Indonesia, bersifat memotivasi, dan menjelaskan kekurangan jika skor tidak maksimal.\n\n";

        $prompt .= "FORMAT OUTPUT (JSON):\n";
        $prompt .= "{\n";
        $prompt .= "  \"score\": (angka 0 sampai {$max}),\n";
        $prompt .= "  \"analysis\": \"(penjelasan mengapa diberi skor tersebut)\",\n";
        $prompt .= "  \"feedback\": \"(pesan untuk siswa: apa yang benar dan apa yang perlu diperbaiki)\"\n";
        $prompt .= "}\n\n";
        $prompt .= "HANYA kembalikan JSON, tanpa teks lain.";

        return $prompt;
    }

    /**
     * Bulk grade for a student in a schedule (jadwal)
     */
    public function gradeBulkForSiswa(int $idJadwal, int $idSiswa, int $userId): array
    {
        // 1. Check API Key
        $apiKey = $this->aiService->getApiKey($userId, true);
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API Key AI belum dikonfigurasi.'];
        }

        // 2. Get Student Answers for Essay (5) and Short Answer (4)
        $db = $this->db;
        $builder = $db->table('cbt_jawaban js');
        // NOTE: Uses 'id_jawaban' column in cbt_jawaban as found by describe
        $builder->select('js.id_jawaban, js.id_soal, js.jawaban, s.soal, s.jawaban as kunci, s.jenis');
        $builder->join('cbt_soal s', 's.id_soal = js.id_soal');
        $builder->where('js.id_jadwal', $idJadwal);
        $builder->where('js.id_siswa', $idSiswa);
        $builder->whereIn('s.jenis', ['4', '5']); // Isian or Essay
        
        $answers = $builder->get()->getResult();
        if (empty($answers)) {
            return ['success' => true, 'message' => 'Tidak ada jawaban Essay/Isian untuk dikoreksi.', 'graded_count' => 0];
        }

        // 3. Get Jadwal info for weighting
        $jadwal = $this->cbtModel->getJadwalById($idJadwal);
        $bank = $this->cbtModel->getBankSoalById($jadwal->id_bank);
        
        $results = [];
        $successCount = 0;

        foreach ($answers as $ans) {
            // Calculate max points for this specific question
            $maxPoints = 0;
            if ($ans->jenis == '4') { // Isian
                $maxPoints = ($bank->tampil_isian > 0) ? ($bank->bobot_isian / $bank->tampil_isian) : 0;
            } else if ($ans->jenis == '5') { // Essay
                $maxPoints = ($bank->tampil_esai > 0) ? ($bank->bobot_esai / $bank->tampil_esai) : 0;
            }

            if ($maxPoints <= 0) continue;

            $grade = $this->gradeAnswer([
                'apiKey'           => $apiKey,
                'question'         => strip_tags($ans->soal),
                'referenceAnswer'  => strip_tags($ans->kunci),
                'studentAnswer'    => strip_tags($ans->jawaban),
                'maxPoints'        => $maxPoints,
                'jenisSoal'        => $ans->jenis
            ]);

            if ($grade['success']) {
                $db->table('cbt_jawaban')
                    ->where('id_jawaban', $ans->id_jawaban)
                    ->update([
                        'ai_score'    => $grade['score'],
                        'ai_feedback' => $grade['feedback'],
                        'ai_status'   => 2 // Success
                    ]);
                $successCount++;
            } else {
                $db->table('cbt_jawaban')
                    ->where('id_jawaban', $ans->id_jawaban)
                    ->update(['ai_status' => 3]); // Failed
            }

            $results[] = [
                'id_soal' => $ans->id_soal,
                'success' => $grade['success'],
                'score'   => $grade['score'] ?? 0,
                'message' => $grade['message'] ?? 'Success'
            ];
        }

        // 4. Record history
        $db->table('cbt_ai_grading_history')->insert([
            'id_jadwal'     => $idJadwal,
            'id_siswa'      => $idSiswa,
            'graded_by'     => $userId,
            'status'        => ($successCount > 0) ? 'SUCCESS' : 'FAILED',
            'total_soal'    => count($answers),
            'success_count' => $successCount,
            'log'           => json_encode($results),
            'created_at'    => date('Y-m-d H:i:s')
        ]);

        return [
            'success'       => true,
            'graded_count'  => $successCount,
            'total_count'   => count($answers),
            'details'       => $results
        ];
    }

    /**
     * Bulk grade for all students in a schedule
     */
    public function gradeBulkForJadwal(int $idJadwal, int $userId): array
    {
        $siswaIds = $this->db->table('cbt_nilai')
            ->select('id_siswa')
            ->where('id_jadwal', $idJadwal)
            ->get()->getResult();
        
        if (empty($siswaIds)) {
            return ['success' => false, 'message' => 'Belum ada siswa yang mengerjakan ujian ini.'];
        }

        $totalGraded = 0;
        $totalSiswaSuccess = 0;
        $results = [];

        foreach ($siswaIds as $s) {
            $res = $this->gradeBulkForSiswa($idJadwal, (int)$s->id_siswa, $userId);
            if ($res['success']) {
                $totalGraded += $res['graded_count'];
                if ($res['graded_count'] > 0) $totalSiswaSuccess++;
            }
            $results[] = [
                'id_siswa' => $s->id_siswa,
                'res' => $res
            ];
        }

        return [
            'success' => true,
            'total_graded_soal' => $totalGraded,
            'total_siswa_processed' => count($siswaIds),
            'total_siswa_success' => $totalSiswaSuccess,
            'details' => $results
        ];
    }
}
