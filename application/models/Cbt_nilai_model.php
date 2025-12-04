<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_nilai_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_nilai';
        $this->primary_key = 'id_nilai';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}