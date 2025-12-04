<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_pengawas_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_pengawas';
        $this->primary_key = 'id_pengawas';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}