<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_durasi_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_durasi_siswa';
        $this->primary_key = 'id_durasi';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}