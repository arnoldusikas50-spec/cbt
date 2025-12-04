<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Cbt_kelas_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'master_kelas';
        $this->primary_key = 'id_kelas';
        $this->timestamps = false;
        $this->soft_deletes = false;
        $this->has_one['jurusan'] = array('local_key'=>'id_jurusan', 'foreign_key'=>'id_jurusan', 'foreign_model'=>'Data_jurusan_model');
		parent::__construct();
	}

    protected function addvalue($datas){
        $new_data = [];
        if (isset($datas['id_kelas'])) {
            $new_data = $datas;
            $new_data['value'] = $new_data['id_kelas'] ?? "";
            $new_data['name'] = $new_data['kode_kelas'] ?? '';
            $new_data['alias'] = $new_data['nama_kelas'] ?? '';
        } else {
            foreach ($datas as $data) {
                $data['value'] = $data['id_kelas'] ?? "";
                $data['name'] = $data['kode_kelas'] ?? '';
                $data['alias'] = $data['nama_kelas'] ?? '';
                $new_data[] = $data;
            }
        }
        return $new_data;
    }
}