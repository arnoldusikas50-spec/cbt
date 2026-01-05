<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_pakta_integritas_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_pakta_integritas';
        $this->primary_key = 'id_pakta_integritas';
        $this->timestamps = true;
        $this->soft_deletes = false;

        $this->has_one['jadwal'] = array('local_key'=>'id_jadwal', 'foreign_key'=>'id_jadwal', 'foreign_model'=> 'Cbt_jadwal_model');
        // id_user can be guru or other users
		parent::__construct();
	}
}
