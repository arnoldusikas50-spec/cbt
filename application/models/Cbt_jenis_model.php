<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_jenis_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_jenis';
        $this->primary_key = 'id_jenis';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}