<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Mapel_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_mapel';
        $this->primary_key = 'id_mapel';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}