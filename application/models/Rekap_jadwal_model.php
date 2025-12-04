<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Rekap_jadwal_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_rekap_jadwal';
        $this->primary_key = 'id_rekap';
        $this->timestamps = false;
        $this->soft_deletes = false;

        $this->has_many['rekapsiswa'] = array('local_key'=>'id_jadwal', 'foreign_key'=>'id_jadwal', 'foreign_model'=> 'Rekap_siswa_model');
		parent::__construct();
	}
}