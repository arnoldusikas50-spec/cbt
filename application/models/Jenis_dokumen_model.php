<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis_dokumen_model extends CI_Model {

    public function get_all($where = null) {
        $this->db->select('id_jenis_dokumen, kode_jenis, nama_jenis, deskripsi, template_path, is_active, created_at, updated_at');
        $this->db->from('master_jenis_dokumen');
        
        if ($where) {
            $this->db->where($where);
        }
        
        $this->db->order_by('nama_jenis', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        $this->db->select('id_jenis_dokumen, kode_jenis, nama_jenis, deskripsi, template_path, is_active, created_at, updated_at');
        $this->db->from('master_jenis_dokumen');
        $this->db->where('id_jenis_dokumen', $id);
        return $this->db->get()->row();
    }

    public function get_by_kode($kode) {
        $this->db->select('id_jenis_dokumen, kode_jenis, nama_jenis, deskripsi, template_path, is_active, created_at, updated_at');
        $this->db->from('master_jenis_dokumen');
        $this->db->where('kode_jenis', $kode);
        return $this->db->get()->row();
    }

    public function get_active() {
        $this->db->select('id_jenis_dokumen, kode_jenis, nama_jenis, deskripsi, template_path, is_active, created_at, updated_at');
        $this->db->from('master_jenis_dokumen');
        $this->db->where('is_active', 1);
        $this->db->order_by('nama_jenis', 'ASC');
        return $this->db->get()->result();
    }

    public function insert($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('master_jenis_dokumen', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id_jenis_dokumen', $id);
        return $this->db->update('master_jenis_dokumen', $data);
    }

    public function delete($id) {
        $this->db->where('id_jenis_dokumen', $id);
        return $this->db->delete('master_jenis_dokumen');
    }

    public function count_all($where = null) {
        $this->db->from('master_jenis_dokumen');
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->count_all_results();
    }

    public function check_kode_exists($kode, $exclude_id = null) {
        $this->db->where('kode_jenis', $kode);
        if ($exclude_id) {
            $this->db->where('id_jenis_dokumen !=', $exclude_id);
        }
        return $this->db->count_all_results('master_jenis_dokumen') > 0;
    }

    public function check_nama_exists($nama, $exclude_id = null) {
        $this->db->where('nama_jenis', $nama);
        if ($exclude_id) {
            $this->db->where('id_jenis_dokumen !=', $exclude_id);
        }
        return $this->db->count_all_results('master_jenis_dokumen') > 0;
    }

    public function toggle_status($id) {
        $this->db->set('is_active', 'NOT is_active', FALSE);
        $this->db->where('id_jenis_dokumen', $id);
        return $this->db->update('master_jenis_dokumen');
    }
}
