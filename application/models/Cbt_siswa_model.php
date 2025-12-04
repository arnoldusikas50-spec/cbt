<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_siswa_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_siswa';
        $this->primary_key = 'id_cbt_siswa';
        $this->timestamps = false;
        $this->soft_deletes = false;
        //$this->after_get = 'decode';

        $this->has_one['jadwal'] = array('local_key'=>'id_jadwal', 'foreign_key'=>'id_jadwal', 'foreign_model'=> 'Cbt_jadwal_model');
        $this->has_one['siswa'] = array('local_key'=>'id_siswa', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Master_siswa_model');
        $this->has_one['log'] = array('local_key'=>'id_siswa', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Cbt_log_model');
        $this->has_many['logs'] = array('local_key'=>'id_siswa', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Cbt_log_model');
		parent::__construct();
	}
}