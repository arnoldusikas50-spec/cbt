<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtKartuSoalAuditModel extends Model
{
    protected $table = 'cbt_kartu_soal_audit';
    protected $primaryKey = 'id_audit';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'kartu_id',
        'user_id',
        'aksi',
        'detail',
        'ip_address',
        'user_agent',
        'waktu',
    ];
    protected $useTimestamps = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('cbt_kartu_soal_audit')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'id_audit' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'kartu_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'aksi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'detail' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'waktu' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $forge->addKey('id_audit', true);
        $forge->addKey(['kartu_id', 'waktu']);
        $forge->addKey(['user_id', 'waktu']);
        $forge->createTable('cbt_kartu_soal_audit', true);
    }

    public function add(?int $kartuId, string $aksi, ?string $detail = null, ?int $userId = null): bool
    {
        $userId = $userId ?? session()->get('user_id');
        $data = [
            'kartu_id' => $kartuId,
            'user_id' => $userId,
            'aksi' => $aksi,
            'detail' => $detail,
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'waktu' => date('Y-m-d H:i:s'),
        ];
        return $this->insert($data) !== false;
    }
}
