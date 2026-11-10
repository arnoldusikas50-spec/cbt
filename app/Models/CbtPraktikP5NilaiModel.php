<?php
namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikP5NilaiModel extends Model
{
    protected $table = 'cbt_praktik_p5_nilai';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'p5_id', 'praktik_id', 'siswa_id', 'hasil_id',
        'nilai_observasi', 'catatan_observasi',
        'dimensi_ppp_tercapai', 'refleksi_siswa',
        'nilai_akhir_p5', 'predikat_p5', 'status'
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
                'p5_id' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
                'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
                'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
                'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'nilai_observasi' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'catatan_observasi' => ['type' => 'TEXT', 'null' => true],
                'dimensi_ppp_tercapai' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'refleksi_siswa' => ['type' => 'TEXT', 'null' => true],
                'nilai_akhir_p5' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'predikat_p5' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'status' => ['type' => 'ENUM', 'constraint' => ['dinilai', 'selesai'], 'default' => 'dinilai'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ];
            $forge->addField($fields);
            $forge->addPrimaryKey('id');
            $forge->addKey('p5_id');
            $forge->addKey('praktik_id');
            $forge->addKey('siswa_id');
            $forge->createTable($this->table, true);
        }
    }

    public function getNilaiP5ByPraktik(int $praktikId): array
    {
        $db = \Config\Database::connect();
        $tpRow = $db->table('master_tp')->select('id_tp')->where('active', 1)->get()->getRow();
        $smtRow = $db->table('master_smt')->select('id_smt')->where('active', 1)->get()->getRow();
        $tp = $tpRow->id_tp ?? 0;
        $smt = $smtRow->id_smt ?? 0;

        return $this->from($this->table . ' n')
            ->select('
                n.*, s.nama as nama_siswa, s.nis, k.nama_kelas, p.judul_projek
            ')
            ->join('master_siswa s', 'n.siswa_id = s.id_siswa', 'left')
            ->join('kelas_siswa ks', 's.id_siswa = ks.id_siswa AND ks.id_tp = ' . (int)$tp . ' AND ks.id_smt = ' . (int)$smt, 'left')
            ->join('master_kelas k', 'ks.id_kelas = k.id_kelas', 'left')
            ->join('cbt_praktik_p5 p', 'n.p5_id = p.id', 'left')
            ->where('n.praktik_id', $praktikId)
            ->orderBy('s.nama', 'ASC')
            ->findAll();
    }
}
