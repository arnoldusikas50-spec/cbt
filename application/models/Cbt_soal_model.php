<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_soal_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_soal';
        $this->primary_key = 'id_soal';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}