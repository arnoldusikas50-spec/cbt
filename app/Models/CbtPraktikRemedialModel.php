<?php
namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikRemedialModel extends Model
{
    protected $table = 'cbt_praktik_remedial';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'hasil_id', 'siswa_id', 'guru_id',
        'nilai_awal', 'nilai_remedial', 'status_remedial',
        'jenis_remedial', 'catatan_remedial', 'tanggal_remedial',
        'batas_waktu', 'file_remedial', 'kompetensi_diperbaiki'
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
                'nilai_awal' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'nilai_remedial' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'status_remedial' => ['type' => 'ENUM', 'constraint' => ['dijadwalkan', 'dikerjakan', 'selesai', 'dibatalkan'], 'default' => 'dijadwalkan'],
                'jenis_remedial' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'catatan_remedial' => ['type' => 'TEXT', 'null' => true],
                'tanggal_remedial' => ['type' => 'DATETIME', 'null' => true],
                'batas_waktu' => ['type' => 'DATETIME', 'null' => true],
                'file_remedial' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'kompetensi_diperbaiki' => ['type' => 'TEXT', 'null' => true],
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

    public function getRemedialBySiswa(int $praktikId, int $siswaId): ?object
    {
        return $this->where('praktik_id', $praktikId)
            ->where('siswa_id', $siswaId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public function getRemedialByPraktik(int $praktikId): array
    {
        return $this->select('
                r.*, s.nama as nama_siswa, s.nis,
                g.nama_guru
            ')
            ->from($this->table . ' r', true)
            ->join('master_siswa s', 'r.siswa_id = s.id_siswa', 'left')
            ->join('master_guru g', 'r.guru_id = g.id_guru', 'left')
            ->where('r.praktik_id', $praktikId)
            ->orderBy('r.created_at', 'DESC')
            ->findAll();
    }
}
