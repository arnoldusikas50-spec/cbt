<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Smt_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_smt';
        $this->primary_key = 'id_smt';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}