<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Cbt_jadwal_model extends MY_Model
{
	public $fillable = [
		'id_tp',
		'id_smt',
		'id_bank',
		'id_jenis',
		'tgl_mulai',
		'tgl_selesai',
		'durasi_ujian',
		'jarak',
		'acak_soal',
		'acak_opsi',
		'hasil_tampil',
		'token',
		'status',
		'reset_login',
		'pelanggaran',
		'katrol_max',
		'katrol_min'
	];

	public function __construct()
	{
		$this->table = 'cbt_jadwal';
		$this->primary_key = 'id_jadwal';
		$this->timestamps = false;
		$this->soft_deletes = false;

		$this->has_one['jenis'] = array('local_key' => 'id_jenis', 'foreign_key' => 'id_jenis', 'foreign_model' => 'Cbt_jenis_model');
		$this->has_one['bank'] = array('local_key' => 'id_bank', 'foreign_key' => 'id_bank', 'foreign_model' => 'Cbt_bank_model');
		$this->has_one['tp'] = array('local_key' => 'id_tp', 'foreign_key' => 'id_tp', 'foreign_model' => 'Tp_model');
		$this->has_one['smt'] = array('local_key' => 'id_smt', 'foreign_key' => 'id_smt', 'foreign_model' => 'Smt_model');
		$this->has_one['rekap'] = array('local_key' => 'id_jadwal', 'foreign_key' => 'id_jadwal', 'foreign_model' => 'Rekap_jadwal_model');

		$this->has_many['sesisiswa'] = array('local_key' => 'id_tp', 'foreign_key' => 'tp_id', 'foreign_model' => 'Cbt_sesisiswa_model');
		$this->has_many['cbtsiswa'] = array('local_key' => 'id_jadwal', 'foreign_key' => 'id_jadwal', 'foreign_model' => 'Cbt_siswa_model');
		$this->has_many['pengawas'] = array('local_key' => 'id_jadwal', 'foreign_key' => 'id_jadwal', 'foreign_model' => 'Cbt_pengawas_model');
		parent::__construct();
	}

	public function save_jadwal($data, $id = null)
	{
		$this->db->trans_begin();

		if ($this->is_bank_in_use($data['id_bank'], $id)) {
			$this->db->trans_rollback();
			return [
				'success' => false,
				'errors' => 'Bank soal sudah digunakan pada jadwal lain'
			];
		}

		if (!$id) {
			$this->db->insert($this->table, $data);
			$insert_id = $this->db->insert_id();
		} else {
			$this->db->where($this->primary_key, $id);
			$this->db->update($this->table, $data);
			$insert_id = $id;
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return [
				'success' => false,
				'errors' => 'Gagal menyimpan jadwal'
			];
		}

		$this->db->trans_commit();
		return [
			'success' => true,
			'insert_id' => $insert_id,
			'errors' => []
		];
	}

	private function is_bank_in_use($id_bank, $exclude_id = null)
	{
		$this->db->from($this->table);
		$this->db->where('id_bank', $id_bank);
		if ($exclude_id !== null) {
			$this->db->where($this->primary_key . ' !=', $exclude_id);
		}
		return $this->db->count_all_results() > 0;
	}
}