<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Tp_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_tp';
        $this->primary_key = 'id_tp';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}