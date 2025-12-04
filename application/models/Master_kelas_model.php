<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Master_kelas_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_kelas';
        $this->primary_key = 'id_kelas';
        $this->timestamps = false;
        $this->soft_deletes = false;

        $this->has_one['jurusan'] = array('local_key'=>'jurusan_id', 'foreign_key'=>'id_jurusan', 'foreign_model'=> 'Jurusan_model');
        $this->has_one['ketua'] = array('local_key'=>'siswa_id', 'foreign_key'=>'id_siswa', 'foreign_model'=> 'Master_siswa_model');
        $this->has_one['wali'] = array('local_key'=>'id_kelas', 'foreign_key'=>'id_kelas', 'foreign_model'=> 'Guru_model');
        $this->has_one['level'] = array('local_key'=>'level_id', 'foreign_key'=>'id_level', 'foreign_model'=> 'Level_model');
        $this->has_many['siswa'] = array('local_key'=>'id_kelas', 'foreign_key'=>'id_kelas', 'foreign_model'=> 'Kelas_siswa_model');
        parent::__construct();
	}
}