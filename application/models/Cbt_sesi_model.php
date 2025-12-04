<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_sesi_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_sesi';
        $this->primary_key = 'id_sesi';
        $this->timestamps = false;
        $this->soft_deletes = false;
        //$this->after_get[] = 'addvalue';
		parent::__construct();
	}

    protected function addvalue($datas){
        $new_data = [];
        foreach ($datas as $data) {
            $data['value'] = $data['id_sesi'] ?? "";
            $data['name'] = $data['kode_sesi'] ?? "";
            $data['alias'] = $data['nama_sesi'] ?? "";
            $new_data[] = $data;
        }
        return $new_data;
    }

}