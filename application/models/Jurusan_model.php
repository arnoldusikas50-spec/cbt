<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Jurusan_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_jurusan';
        $this->primary_key = 'id_jurusan';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}