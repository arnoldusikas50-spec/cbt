<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_sesisiswa_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_sesi_siswa';
        $this->primary_key = 'siswa_id';
        $this->timestamps = false;
        $this->soft_deletes = false;

        $this->has_one['tp'] = array('local_key'=>'tp_id', 'foreign_key'=>'id_tp', 'foreign_model'=> 'Tp_model');
        $this->has_one['smt'] = array('local_key'=>'smt_id', 'foreign_key'=>'id_smt', 'foreign_model'=> 'Smt_model');
        $this->has_one['siswa'] = array('local_key'=>'siswa_id', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Master_siswa_model');
        $this->has_one['peserta'] = array('local_key'=>'siswa_id', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Cbt_nomor_model');
        $this->has_one['kelas'] = array('local_key'=>'kelas_id', 'foreign_key'=>'id_kelas', 'foreign_model'=> 'Cbt_kelas_model');
        $this->has_one['ruang'] = array('local_key'=>'ruang_id', 'foreign_key'=>'id_ruang', 'foreign_model'=>'Cbt_ruang_model');
        $this->has_one['sesi'] = array('local_key'=>'sesi_id', 'foreign_key'=>'id_sesi', 'foreign_model'=>'Cbt_sesi_model');
        $this->has_one['cbtsiswa'] = array('local_key'=>'siswa_id', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Cbt_siswa_model');
        $this->has_many['log'] = array('local_key'=>'siswa_id', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Cbt_log_model');
		parent::__construct();
	}
}