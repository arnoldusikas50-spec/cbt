<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_log_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'log_ujian';
        $this->primary_key = 'id_log';
        $this->timestamps = false;
        $this->soft_deletes = false;
		parent::__construct();
	}
}