<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_ruang_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'cbt_ruang';
        $this->primary_key = 'id_ruang';
        $this->timestamps = false;
        $this->soft_deletes = false;
        //$this->after_get[] = 'addvalue';
		parent::__construct();
	}

    protected function addvalue($datas){
        $new_data = [];
        foreach ($datas as $data) {
            $data['value'] = $data['id_ruang'] ?? "";
            $data['name'] = $data['kode_ruang'] ?? "";
            $data['alias'] = $data['nama_ruang'] ?? "";
            $new_data[] = $data;
        }
        return $new_data;
    }

}