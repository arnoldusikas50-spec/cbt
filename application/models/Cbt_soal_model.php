<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_soal_model extends MY_Model
{
	protected $fillable = [
		'bank_id', 'mapel_id', 'jenis', 'nomor_soal', 'soal', 'deskripsi', 'kesulitan',
		'timer', 'timer_menit', 'file', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e',
		'jawaban', 'tampilkan', 'created_on', 'updated_on'
	];

	public function __construct()
	{
        $this->table = 'cbt_soal';
        $this->primary_key = 'id_soal';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}

	public function update_selection($selected_ids = [], $unselected_ids = [])
	{
		$selected_ids = array_filter((array) $selected_ids);
		$unselected_ids = array_filter((array) $unselected_ids);

		$this->db->trans_begin();

		if (!empty($selected_ids)) {
			$this->db->where_in($this->primary_key, $selected_ids);
			$this->db->set('tampilkan', 1);
			$this->db->update($this->table);
		}

		if (!empty($unselected_ids)) {
			$this->db->where_in($this->primary_key, $unselected_ids);
			$this->db->set('tampilkan', 0);
			$this->db->update($this->table);
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();
		return count($selected_ids);
	}

	public function insert_soal(array $data)
	{
		$data['created_on'] = $data['created_on'] ?? time();
		$data['updated_on'] = $data['updated_on'] ?? time();
		return $this->db->insert($this->table, $data);
	}

	public function update_soal($id, array $data)
	{
		$data['updated_on'] = time();
		$this->db->where($this->primary_key, $id);
		return $this->db->update($this->table, $data);
	}

	public function replace_by_bank($bank_id, array $rows)
	{
		$this->db->trans_begin();
		$this->db->where('bank_id', $bank_id);
		$this->db->delete($this->table);

		if (!empty($rows)) {
			$this->db->insert_batch($this->table, $rows);
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();
		return true;
	}
}