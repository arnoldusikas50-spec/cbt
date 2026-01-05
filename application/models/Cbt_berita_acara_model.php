<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_berita_acara_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_berita_acara';
        $this->primary_key = 'id_berita_acara';
        $this->timestamps = true; // MY_Model handles created_at/updated_at usually if true
        $this->soft_deletes = false;

        $this->has_one['jadwal'] = array('local_key'=>'id_jadwal', 'foreign_key'=>'id_jadwal', 'foreign_model'=> 'Cbt_jadwal_model');
        $this->has_one['ruang'] = array('local_key'=>'id_ruang', 'foreign_key'=>'id_ruang', 'foreign_model'=> 'Cbt_ruang_model');
        $this->has_one['sesi'] = array('local_key'=>'id_sesi', 'foreign_key'=>'id_sesi', 'foreign_model'=> 'Cbt_sesi_model');
        $this->has_one['pengawas'] = array('local_key'=>'id_pengawas', 'foreign_key'=>'id_guru', 'foreign_model'=> 'Guru_model'); // Assuming pengawas links to guru table
		parent::__construct();
	}
}
