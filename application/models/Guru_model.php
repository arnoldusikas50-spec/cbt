<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Guru_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_guru';
        $this->primary_key = 'id_guru';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}