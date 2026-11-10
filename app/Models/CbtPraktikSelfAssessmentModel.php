<?php
namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikSelfAssessmentModel extends Model
{
    protected $table = 'cbt_praktik_self_assessment';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'hasil_id', 'siswa_id', 'rubrik_id',
        'nilai_diri', 'refleksi', 'kesulitan', 'strategi',
        'dimensi_ppp_teridentifikasi', 'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function initialize()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if (!$db->tableExists($this->table)) {
            $fields = [
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
                'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
                'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
                'rubrik_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'nilai_diri' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'refleksi' => ['type' => 'TEXT', 'null' => true],
                'kesulitan' => ['type' => 'TEXT', 'null' => true],
                'strategi' => ['type' => 'TEXT', 'null' => true],
                'dimensi_ppp_teridentifikasi' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'submitted'], 'default' => 'draft'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ];
            $forge->addField($fields);
            $forge->addPrimaryKey('id');
            $forge->addKey('praktik_id');
            $forge->addKey('siswa_id');
            $forge->addKey('hasil_id');
            $forge->createTable($this->table, true);
        }
    }

    public function getSelfAssessment(int $hasilId): ?object
    {
        return $this->where('hasil_id', $hasilId)->orderBy('created_at', 'DESC')->first();
    }
}
