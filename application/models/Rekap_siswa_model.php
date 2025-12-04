<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Rekap_siswa_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_rekap_siswa';
        $this->primary_key = 'id_rekap_siswa';
        $this->timestamps = false;
        $this->soft_deletes = false;

        $this->has_one['siswa'] = array('local_key'=>'id_siswa', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Master_siswa_model');
		parent::__construct();
	}
}