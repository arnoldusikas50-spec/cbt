<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Cbt_bank_model extends MY_Model
{
	public $fillable = [
		'id_tp',
		'id_smt',
		'bank_kode',
		'bank_jenis_id',
		'bank_mapel_id',
		'bank_kelas',
		'bank_level',
		'bank_guru_id',
		'jml_soal',
		'tampil_pg',
		'bobot_pg',
		'opsi',
		'jml_kompleks',
		'tampil_kompleks',
		'bobot_kompleks',
		'jml_jodohkan',
		'tampil_jodohkan',
		'bobot_jodohkan',
		'jml_isian',
		'tampil_isian',
		'bobot_isian',
		'jml_esai',
		'bobot_esai',
		'tampil_esai',
		'status',
		'soal_agama',
		'tampil_urutan',
		'bobot_urutan',
		'tampil_cloze',
		'bobot_cloze'
	];
	public function __construct()
	{
		$this->table = 'cbt_bank_soal';
		$this->primary_key = 'id_bank';
		$this->timestamps = false;
		$this->soft_deletes = false;

		$this->has_one['jadwal'] = array('local_key' => 'id_bank', 'foreign_key' => 'id_bank', 'foreign_model' => 'Cbt_jadwal_model');
		$this->has_one['mapel'] = array('local_key' => 'bank_mapel_id', 'foreign_key' => 'id_mapel', 'foreign_model' => 'Mapel_model');
		$this->has_one['guru'] = array('local_key' => 'bank_guru_id', 'foreign_key' => 'id_guru', 'foreign_model' => 'Guru_model');
		$this->has_one['level'] = array('local_key' => 'bank_level', 'foreign_key' => 'id_level', 'foreign_model' => 'Level_model');
		$this->has_many['soal'] = array('local_key' => 'id_bank', 'foreign_key' => 'bank_id', 'foreign_model' => 'Cbt_soal_model');
		parent::__construct();
	}

	public function delete_with_soal($ids)
	{
		$ids = (array) $ids;
		if (count($ids) === 0) {
			return false;
		}

		$this->db->trans_begin();
		$this->db->where_in('bank_id', $ids);
		$this->db->delete('cbt_soal');

		$this->db->where_in($this->primary_key, $ids);
		$this->db->delete($this->table);

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();
		return true;
	}
}