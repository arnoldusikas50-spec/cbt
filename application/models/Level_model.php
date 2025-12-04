<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Level_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'level_kelas';
        $this->primary_key = 'id_level';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}