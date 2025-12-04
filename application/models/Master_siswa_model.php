<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Master_siswa_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_siswa';
        $this->primary_key = 'id_siswa';
        $this->timestamps = false;
        $this->soft_deletes = false;

        $this->has_one['sesisiswa'] = array('local_key'=>'id_siswa', 'foreign_key'=>'siswa_id', 'foreign_model'=> 'Cbt_sesisiswa_model');
        $this->has_many['cbtsiswa'] = array('local_key'=>'id_siswa', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Cbt_siswa_model');
        $this->has_many['logs'] = array('local_key'=>'id_siswa', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Cbt_log_model');
        parent::__construct();
	}
}