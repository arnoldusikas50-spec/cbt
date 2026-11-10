<?php

namespace App\Database\Seeders;

use CodeIgniter\Database\Seeder;

/**
 * Database Seeder untuk Testing CBT Module
 * 
 * Menghasilkan data testing untuk:
 * - Audit trail logs
 * - Sync queue data
 * - Security logs
 * - Sample nilai dan jawaban
 * 
 * @version 1.0.0
 */
class CbtModuleSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Starting CBT Module Seeder...\n\n";
        
        // 1. Seed audit trail data
        $this->seedAuditTrailData();
        
        // 2. Seed sync queue data
        $this->seedSyncQueueData();
        
        // 3. Seed security logs
        $this->seedSecurityLogs();
        
        // 4. Seed sample nilai dengan detail
        $this->seedSampleNilai();
        
        echo "\n✅ Seeding completed successfully!\n";
        echo "📊 Summary:\n";
        echo "   - Audit logs: 10 records\n";
        echo "   - Sync queue: 5 records\n";
        echo "   - Security logs: 5 records\n";
        echo "   - Sample nilai: 3 records\n";
    }

    // ==========================================
    // Seed Audit Trail Data
    // ==========================================
    private function seedAuditTrailData()
    {
        echo "📝 Seeding audit trail data...\n";
        
        $data = [
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'action' => 'INSERT',
                'old_values' => null,
                'new_values' => json_encode([
                    'pg_nilai' => 85.00,
                    'nilai_asli' => 80.00,
                    'pg_skor' => 50.00
                ]),
                'changed_by' => 'system',
                'changed_by_role' => 'System',
                'ip_address' => '127.0.0.1',
                'reason' => 'Auto scoring after exam completion',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'action' => 'CORRECT',
                'old_values' => json_encode([
                    'nilai_input' => null
                ]),
                'new_values' => json_encode([
                    'nilai_input' => json_encode(['esai' => [['koreksi' => 90]]]),
                    'field' => 'esai',
                    'total' => 90
                ]),
                'changed_by' => 'admin',
                'changed_by_role' => 'Admin',
                'ip_address' => '192.168.1.100',
                'reason' => 'Koreksi manual untuk soal esai',
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 hours')),
            ],
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'action' => 'MARK_CORRECTED',
                'old_values' => json_encode([
                    'nilai_input' => json_encode(['dikoreksi' => '0'])
                ]),
                'new_values' => json_encode([
                    'nilai_input' => json_encode(['dikoreksi' => '1'])
                ]),
                'changed_by' => 'guru',
                'changed_by_role' => 'Guru',
                'ip_address' => '192.168.1.101',
                'reason' => 'Tandai sudah dikoreksi',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
            ],
            [
                'id_siswa' => 2,
                'id_jadwal' => 1,
                'action' => 'INSERT',
                'old_values' => null,
                'new_values' => json_encode([
                    'pg_nilai' => 72.50,
                    'nilai_asli' => 70.00,
                ]),
                'changed_by' => 'system',
                'changed_by_role' => 'System',
                'ip_address' => '127.0.0.1',
                'reason' => 'Auto scoring after exam completion',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'id_siswa' => 3,
                'id_jadwal' => 2,
                'action' => 'RECALCULATE',
                'old_values' => json_encode([
                    'pg_nilai' => 65.00,
                    'pg_skor' => 35.00
                ]),
                'new_values' => json_encode([
                    'pg_nilai' => 68.50,
                    'pg_skor' => 37.00
                ]),
                'changed_by' => 'system',
                'changed_by_role' => 'System',
                'ip_address' => '127.0.0.1',
                'reason' => 'Batch recalculation with new bobot',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'action' => 'UPDATE',
                'old_values' => json_encode([
                    'pg_nilai' => 85.00,
                    'pengurangan' => 0
                ]),
                'new_values' => json_encode([
                    'pg_nilai' => 82.50,
                    'pengurangan' => 2.5
                ]),
                'changed_by' => 'panitia',
                'changed_by_role' => 'Panitia',
                'ip_address' => '192.168.1.102',
                'reason' => 'Pengurangan nilai karena pelanggaran',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            ],
        ];

        // Insert sample data
        $this->db->table('cbt_nilai_audit')->insertBatch($data);
        
        // Seed jawaban audit
        $jawabanData = [
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'id_soal' => 1,
                'action' => 'INSERT',
                'old_jawaban' => null,
                'new_jawaban' => 'A',
                'jawaban_type' => 'pg',
                'changed_by' => 'siswa_1',
                'changed_by_role' => 'Siswa',
                'ip_address' => '192.168.1.150',
                'session_id' => 'sess_' . md5(uniqid()),
                'reason' => 'Jawab soal PG',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day -2 hours')),
            ],
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'id_soal' => 1,
                'action' => 'UPDATE',
                'old_jawaban' => 'A',
                'new_jawaban' => 'B',
                'jawaban_type' => 'pg',
                'changed_by' => 'siswa_1',
                'changed_by_role' => 'Siswa',
                'ip_address' => '192.168.1.150',
                'session_id' => 'sess_' . md5(uniqid()),
                'reason' => 'Ubah jawaban dari A ke B',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day -1 hour')),
            ],
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'id_soal' => 5,
                'action' => 'INSERT',
                'old_jawaban' => null,
                'new_jawaban' => json_encode(['A', 'C']),
                'jawaban_type' => 'kompleks',
                'changed_by' => 'siswa_1',
                'changed_by_role' => 'Siswa',
                'ip_address' => '192.168.1.150',
                'session_id' => 'sess_' . md5(uniqid()),
                'reason' => 'Jawab soal kompleks',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day -30 minutes')),
            ],
        ];

        $this->db->table('cbt_jawaban_audit')->insertBatch($jawabanData);
        
        echo "   ✅ Audit trail data seeded\n";
    }

    // ==========================================
    // Seed Sync Queue Data
    // ==========================================
    private function seedSyncQueueData()
    {
        echo "🔄 Seeding sync queue data...\n";
        
        $data = [
            [
                'siswa_id' => 1,
                'jadwal_id' => 1,
                'soal_id' => 10,
                'jawaban_data' => json_encode([
                    'jawaban' => 'D',
                    'jenis' => '1',
                    'ragu' => '0'
                ]),
                'status' => 'synced',
                'attempts' => 1,
                'error_message' => null,
                'client_timestamp' => time() - 3600,
                'server_timestamp' => time() - 3590,
                'synced_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ],
            [
                'siswa_id' => 1,
                'jadwal_id' => 1,
                'soal_id' => 11,
                'jawaban_data' => json_encode([
                    'jawaban' => json_encode(['A', 'B']),
                    'jenis' => '2',
                    'ragu' => '1'
                ]),
                'status' => 'pending',
                'attempts' => 0,
                'error_message' => null,
                'client_timestamp' => time() - 1800,
                'server_timestamp' => null,
                'synced_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'updated_at' => null,
            ],
            [
                'siswa_id' => 2,
                'jadwal_id' => 1,
                'soal_id' => 5,
                'jawaban_data' => json_encode([
                    'jawaban' => 'Ini jawaban esai panjang...',
                    'jenis' => '4',
                    'ragu' => '0'
                ]),
                'status' => 'failed',
                'attempts' => 3,
                'error_message' => 'Connection timeout after 3 attempts',
                'client_timestamp' => time() - 7200,
                'server_timestamp' => time() - 7100,
                'synced_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ],
            [
                'siswa_id' => 1,
                'jadwal_id' => 1,
                'soal_id' => 15,
                'jawaban_data' => json_encode([
                    'jawaban' => json_encode([['left' => 'A', 'right' => '1'], ['left' => 'B', 'right' => '2']]),
                    'jenis' => '3',
                    'ragu' => '0'
                ]),
                'status' => 'synced',
                'attempts' => 1,
                'error_message' => null,
                'client_timestamp' => time() - 5400,
                'server_timestamp' => time() - 5395,
                'synced_at' => date('Y-m-d H:i:s', strtotime('-90 minutes')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-90 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-90 minutes')),
            ],
            [
                'siswa_id' => 3,
                'jadwal_id' => 2,
                'soal_id' => 8,
                'jawaban_data' => json_encode([
                    'jawaban' => 'Esai jawaban offline',
                    'jenis' => '4',
                    'ragu' => '0'
                ]),
                'status' => 'retrying',
                'attempts' => 2,
                'error_message' => 'Server busy, will retry',
                'client_timestamp' => time() - 900,
                'server_timestamp' => time() - 800,
                'synced_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
            ],
        ];

        $this->db->table('cbt_sync_queue')->insertBatch($data);
        
        echo "   ✅ Sync queue data seeded\n";
    }

    // ==========================================
    // Seed Security Logs
    // ==========================================
    private function seedSecurityLogs()
    {
        echo "🔒 Seeding security logs...\n";
        
        $data = [
            [
                'event_type' => 'XSS_ATTEMPT',
                'severity' => 'high',
                'user_id' => null,
                'user_role' => null,
                'ip_address' => '192.168.1.200',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_url' => '/cbt/saveJawaban',
                'request_data' => json_encode(['jawaban' => '<script>alert("xss")</script>']),
                'session_id' => 'malicious_' . md5(uniqid()),
                'description' => 'XSS attempt detected and blocked',
                'resolved' => 1,
                'resolved_by' => 'admin',
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'event_type' => 'RACE_CONDITION',
                'severity' => 'medium',
                'user_id' => 1,
                'user_role' => 'Siswa',
                'ip_address' => '192.168.1.150',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_url' => '/cbt/saveJawaban',
                'request_data' => json_encode(['id_soal' => 5, 'jawaban' => 'A']),
                'session_id' => 'sess_' . md5(uniqid()),
                'description' => 'Concurrent update detected, resolved with row locking',
                'resolved' => 1,
                'resolved_by' => 'system',
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-12 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 hours')),
            ],
            [
                'event_type' => 'INVALID_ACCESS',
                'severity' => 'medium',
                'user_id' => 5,
                'user_role' => 'Siswa',
                'ip_address' => '192.168.1.155',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_url' => '/cbtnilai/koreksi',
                'request_data' => json_encode(['jadwal_id' => 1]),
                'session_id' => 'sess_' . md5(uniqid()),
                'description' => 'Student attempted to access grading interface',
                'resolved' => 1,
                'resolved_by' => 'admin',
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'event_type' => 'KATROL_INVALID',
                'severity' => 'low',
                'user_id' => null,
                'user_role' => 'System',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System',
                'request_url' => '/cbt/hitungNilai',
                'request_data' => json_encode(['katrol_min' => 80, 'katrol_max' => 60]),
                'session_id' => null,
                'description' => 'Invalid katrol config detected: min > max',
                'resolved' => 1,
                'resolved_by' => 'system',
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'event_type' => 'OFFLINE_SYNC',
                'severity' => 'low',
                'user_id' => 1,
                'user_role' => 'Siswa',
                'ip_address' => '192.168.1.150',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_url' => '/cbt/saveJawaban',
                'request_data' => json_encode(['offline' => true, 'soal_count' => 5]),
                'session_id' => 'sess_' . md5(uniqid()),
                'description' => 'Offline answers synced successfully',
                'resolved' => 1,
                'resolved_by' => 'system',
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
        ];

        $this->db->table('cbt_security_logs')->insertBatch($data);
        
        echo "   ✅ Security logs seeded\n";
    }

    // ==========================================
    // Seed Sample Nilai dengan Detail
    // ==========================================
    private function seedSampleNilai()
    {
        echo "📝 Seeding sample nilai data...\n";
        
        // Check if cbt_nilai table exists and has data
        $nilaiCount = $this->db->table('cbt_nilai')->countAllResults();
        
        if ($nilaiCount > 0) {
            echo "   ℹ️ cbt_nilai already has data, skipping...\n";
            return;
        }

        // This is just sample data - in real scenario, nilai is generated by system
        $data = [
            [
                'id_siswa' => 1,
                'id_jadwal' => 1,
                'pg_benar' => 18,
                'pg_nilai' => 90.00,
                'nilai_asli' => 85.00,
                'pg_skor' => 50.00,
                'kompleks_skor' => 15.00,
                'jodohkan_skor' => 10.00,
                'isian_skor' => 8.00,
                'urutan_skor' => 5.00,
                'cloze_skor' => 2.00,
                'detail_json' => json_encode([
                    'breakdown' => [
                        'pg' => ['benar' => 18, 'salah' => 2, 'total' => 20],
                        'kompleks' => ['skor' => 15, 'total' => 20],
                        'jodohkan' => ['skor' => 10, 'total' => 10],
                    ],
                    'katrol' => ['min' => 60, 'max' => 100, 'applied' => true]
                ]),
            ],
            [
                'id_siswa' => 2,
                'id_jadwal' => 1,
                'pg_benar' => 15,
                'pg_nilai' => 75.00,
                'nilai_asli' => 72.50,
                'pg_skor' => 40.00,
                'kompleks_skor' => 12.00,
                'jodohkan_skor' => 8.00,
                'isian_skor' => 7.00,
                'urutan_skor' => 4.00,
                'cloze_skor' => 4.00,
                'detail_json' => json_encode([
                    'breakdown' => [
                        'pg' => ['benar' => 15, 'salah' => 5, 'total' => 20],
                        'kompleks' => ['skor' => 12, 'total' => 20],
                        'jodohkan' => ['skor' => 8, 'total' => 10],
                    ],
                    'katrol' => ['min' => 60, 'max' => 100, 'applied' => true]
                ]),
            ],
            [
                'id_siswa' => 3,
                'id_jadwal' => 2,
                'pg_benar' => 12,
                'pg_nilai' => 60.00,
                'nilai_asli' => 58.00,
                'pg_skor' => 32.00,
                'kompleks_skor' => 10.00,
                'jodohkan_skor' => 6.00,
                'isian_skor' => 6.00,
                'urutan_skor' => 3.00,
                'cloze_skor' => 3.00,
                'detail_json' => json_encode([
                    'breakdown' => [
                        'pg' => ['benar' => 12, 'salah' => 8, 'total' => 20],
                        'kompleks' => ['skor' => 10, 'total' => 20],
                        'jodohkan' => ['skor' => 6, 'total' => 10],
                    ],
                    'katrol' => ['min' => 60, 'max' => 80, 'applied' => true]
                ]),
            ],
        ];

        $this->db->table('cbt_nilai')->insertBatch($data);
        
        echo "   ✅ Sample nilai data seeded\n";
    }
}
