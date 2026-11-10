<?php

namespace App\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\CbtPraktikModel;
use App\Models\CbtPraktikHasilModel;
use App\Models\CbtPraktikRubrikModel;
use App\Libraries\PraktikGrading;

/**
 * Unit Tests for CBT Praktik Module
 */
class CbtPraktikTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $seed = false;

    protected $praktikModel;
    protected $hasilModel;
    protected $rubrikModel;
    protected $grading;

    protected function setUp(): void
    {
        parent::setUp();
        $this->praktikModel = new CbtPraktikModel();
        $this->hasilModel = new CbtPraktikHasilModel();
        $this->rubrikModel = new CbtPraktikRubrikModel();
        $this->grading = new PraktikGrading();
    }

    /**
     * Test creating a practical exam
     */
    public function testCreatePraktik(): void
    {
        $data = [
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1, 2, 3]),
            'judul' => 'Test Ujian Praktik',
            'deskripsi' => 'Deskripsi test',
            'instruksi' => 'Instruksi test',
            'jenis_praktik' => 'coding',
            'format_pengumpulan' => 'kode',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'durasi_menit' => 60,
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'draft'
        ];

        $id = $this->praktikModel->insert($data);
        $this->assertIsInt($id);

        $praktik = $this->praktikModel->find($id);
        $this->assertEquals('Test Ujian Praktik', $praktik->judul);
        $this->assertEquals('coding', $praktik->jenis_praktik);
    }

    /**
     * Test rubric creation and validation
     */
    public function testRubricValidation(): void
    {
        // Create practical exam
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Rubrik',
            'jenis_praktik' => 'hands_on',
            'tanggal_mulai' => date('Y-m-d H:i:s'),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'draft'
        ]);

        // Add rubrics
        $rubrics = [
            ['praktik_id' => $praktikId, 'kode_kriteria' => 'C1', 'nama_kriteria' => 'Kriteria 1', 'bobot_persen' => 40],
            ['praktik_id' => $praktikId, 'kode_kriteria' => 'C2', 'nama_kriteria' => 'Kriteria 2', 'bobot_persen' => 30],
            ['praktik_id' => $praktikId, 'kode_kriteria' => 'C3', 'nama_kriteria' => 'Kriteria 3', 'bobot_persen' => 30],
        ];

        foreach ($rubrics as $r) {
            $this->rubrikModel->insert($r);
        }

        // Test bobot validation
        $validation = $this->rubrikModel->validateBobot($praktikId);
        $this->assertTrue($validation['valid']);
        $this->assertEquals(100, $validation['total']);
    }

    /**
     * Test student starting exam
     */
    public function testStartExam(): void
    {
        // Create practical exam
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Start',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'durasi_menit' => 60,
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'aktif'
        ]);

        // Start exam
        $hasil = $this->hasilModel->mulaiUjian($praktikId, 1, 1);
        $this->assertNotNull($hasil);
        $this->assertEquals('sedang_mengerjakan', $hasil->status_pengerjaan);

        // Test duplicate start (should return existing)
        $hasil2 = $this->hasilModel->mulaiUjian($praktikId, 1, 1);
        $this->assertEquals($hasil->id, $hasil2->id);
    }

    /**
     * Test exam submission
     */
    public function testSubmitExam(): void
    {
        // Create and start exam
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Submit',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'aktif'
        ]);

        $hasil = $this->hasilModel->mulaiUjian($praktikId, 1, 1);

        // Submit
        $data = [
            'kode_jawaban' => 'print("Hello World")',
            'bahasa_pemrograman' => 'python',
            'catatan_siswa' => 'Test submission'
        ];

        $result = $this->hasilModel->submitUjian($hasil->id, $data);
        $this->assertTrue($result);

        // Verify submission
        $updated = $this->hasilModel->find($hasil->id);
        $this->assertEquals('selesai', $updated->status_pengerjaan);
        $this->assertNotNull($updated->waktu_submit);
        $this->assertEquals('print("Hello World")', $updated->kode_jawaban);
    }

    /**
     * Test grading
     */
    public function testGrading(): void
    {
        // Create practical exam with rubric
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Grading',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'aktif'
        ]);

        // Add rubric
        $this->rubrikModel->insert([
            'praktik_id' => $praktikId,
            'kode_kriteria' => 'C1',
            'nama_kriteria' => 'Syntax',
            'bobot_persen' => 50,
            'tipe_penilaian' => 'manual'
        ]);

        $this->rubrikModel->insert([
            'praktik_id' => $praktikId,
            'kode_kriteria' => 'C2',
            'nama_kriteria' => 'Logika',
            'bobot_persen' => 50,
            'tipe_penilaian' => 'manual'
        ]);

        // Create submission
        $hasil = $this->hasilModel->mulaiUjian($praktikId, 1, 1);
        $this->hasilModel->submitUjian($hasil->id, [
            'kode_jawaban' => 'print("Hello")',
            'bahasa_pemrograman' => 'python'
        ]);

        // Manual grading
        $gradeData = [
            'nilai_manual' => 85,
            'nilai_akhir' => 85,
            'rincian_nilai' => [
                1 => ['nilai' => 40, 'level' => 3],
                2 => ['nilai' => 45, 'level' => 3]
            ],
            'feedback' => 'Bagus!'
        ];

        $result = $this->hasilModel->simpanPenilaian($hasil->id, $gradeData);
        $this->assertTrue($result);

        // Verify grade
        $graded = $this->hasilModel->find($hasil->id);
        $this->assertEquals(85, $graded->nilai_akhir);
        $this->assertEquals('sudah_dinilai', $graded->status_penilaian);
    }

    /**
     * Test plagiarism detection
     */
    public function testPlagiarismCheck(): void
    {
        // Create exam
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Plagiarism',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'aktif'
        ]);

        // Create first submission
        $hasil1 = $this->hasilModel->mulaiUjian($praktikId, 1, 1);
        $this->hasilModel->submitUjian($hasil1->id, [
            'kode_jawaban' => 'print("Hello World")\nfor i in range(10):\n    print(i)',
            'bahasa_pemrograman' => 'python'
        ]);

        // Check similarity (should find similarity with self - but exclude own submission)
        $similar = $this->hasilModel->checkPlagiarism($praktikId, 2, 'print("Hello World")');
        // Since no other submissions, should return empty array
        $this->assertIsArray($similar);
    }

    /**
     * Test availability check
     */
    public function testAvailabilityCheck(): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Create active exam
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1, 2]),
            'judul' => 'Test Availability',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'aktif'
        ]);

        // Test available for student in right class
        $avail1 = $this->praktikModel->isAvailableForSiswa($praktikId, 1, 1);
        $this->assertTrue($avail1['available']);
        $this->assertTrue($avail1['can_start']);

        // Test unavailable for student in wrong class
        $avail2 = $this->praktikModel->isAvailableForSiswa($praktikId, 99, 2);
        $this->assertFalse($avail2['available']);
    }

    /**
     * Test soft delete
     */
    public function testSoftDelete(): void
    {
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Delete',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s'),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'draft'
        ]);

        // Soft delete
        $this->praktikModel->delete($praktikId);

        // Should not appear in regular queries
        $praktik = $this->praktikModel->find($praktikId);
        $this->assertNull($praktik);

        // Should appear with deleted data
        $praktikWithDeleted = $this->praktikModel->withDeleted()->find($praktikId);
        $this->assertNotNull($praktikWithDeleted);
        $this->assertNotNull($praktikWithDeleted->deleted_at);
    }

    /**
     * Test auto-grading for coding
     */
    public function testAutoGrading(): void
    {
        // Create coding exam with auto-grading rubric
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Auto Grading',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'aktifkan_penilaian_otomatis' => 1,
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'aktif'
        ]);

        // Create submission
        $hasil = $this->hasilModel->mulaiUjian($praktikId, 1, 1);
        $this->hasilModel->submitUjian($hasil->id, [
            'kode_jawaban' => 'def main():\n    print("Hello")\n    for i in range(5):\n        print(i)\nif __name__ == "__main__":\n    main()',
            'bahasa_pemrograman' => 'python'
        ]);

        // Run auto-grading
        $result = $this->grading->gradeSubmission($praktikId, $hasil->id);
        
        $this->assertIsArray($result);
        // Since no rubrics configured for auto-grading, should fail gracefully
    }

    /**
     * Test statistics calculation
     */
    public function testStatistics(): void
    {
        $praktikId = $this->praktikModel->insert([
            'tp_id' => 1,
            'smt_id' => 1,
            'mapel_id' => 1,
            'kelas_id' => json_encode([1]),
            'judul' => 'Test Statistics',
            'jenis_praktik' => 'coding',
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bobot' => 100,
            'kkm' => 70,
            'created_by' => 1,
            'status' => 'aktif'
        ]);

        // Create multiple submissions
        for ($i = 1; $i <= 3; $i++) {
            $hasil = $this->hasilModel->mulaiUjian($praktikId, $i, 1);
            $this->hasilModel->submitUjian($hasil->id, [
                'kode_jawaban' => 'print("test")',
                'bahasa_pemrograman' => 'python'
            ]);
            
            // Grade with different scores
            $this->hasilModel->simpanPenilaian($hasil->id, [
                'nilai_manual' => 70 + ($i * 10),
                'nilai_akhir' => 70 + ($i * 10)
            ]);
        }

        $stats = $this->hasilModel->getStatistics($praktikId);
        $this->assertEquals(3, $stats->total_peserta);
        $this->assertEquals(3, $stats->total_selesai);
        $this->assertEquals(3, $stats->total_dinilai);
        $this->assertEquals(80, $stats->rata_nilai); // (80+90+100)/3
    }
}
