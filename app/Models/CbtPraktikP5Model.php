<?php
namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikP5Model extends Model
{
    protected $table = 'cbt_praktik_p5';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'judul_projek', 'deskripsi_projek',
        'dimensi_ppp', 'elemen_ppp', 'sub_elemen_ppp',
        'rubrik_p5', 'status', 'created_by'
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
                'judul_projek' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'deskripsi_projek' => ['type' => 'TEXT', 'null' => true],
                'dimensi_ppp' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'elemen_ppp' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'sub_elemen_ppp' => ['type' => 'TEXT', 'null' => true],
                'rubrik_p5' => ['type' => 'TEXT', 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'aktif', 'selesai'], 'default' => 'draft'],
                'created_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ];
            $forge->addField($fields);
            $forge->addPrimaryKey('id');
            $forge->addKey('praktik_id');
            $forge->createTable($this->table, true);
        }
    }

    public function getP5ByPraktik(int $praktikId): array
    {
        return $this->where('praktik_id', $praktikId)->orderBy('created_at', 'DESC')->findAll();
    }
}
