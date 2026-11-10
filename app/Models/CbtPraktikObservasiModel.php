<?php
namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikObservasiModel extends Model
{
    protected $table = 'cbt_praktik_observasi';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'hasil_id', 'siswa_id', 'guru_id',
        'aspek_observasi', 'catatan_observasi', 'skor_observasi',
        'dimensi_ppp', 'status'
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
                'guru_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'aspek_observasi' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'catatan_observasi' => ['type' => 'TEXT', 'null' => true],
                'skor_observasi' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'dimensi_ppp' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['aktif', 'selesai'], 'default' => 'aktif'],
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

    public function getObservasiByHasil(int $hasilId): array
    {
        return $this->where('hasil_id', $hasilId)->orderBy('created_at', 'ASC')->findAll();
    }
}
