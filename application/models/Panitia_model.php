<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Panitia_model extends CI_Model {

    private $table = 'master_panitia';

    public function get_all()
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('p.*, u.id as user_id, u.active, u.last_login,' .
            ' (SELECT COUNT(login) FROM login_attempts WHERE login_attempts.login = p.username) AS reset_attempts');
        $this->db->from($this->table.' p');
        $this->db->join('users u', 'p.id_user = u.id', 'left');
        $this->db->order_by('p.nama_panitia', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('p.*, u.id as user_id, u.active');
        $this->db->from($this->table.' p');
        $this->db->join('users u', 'p.id_user = u.id', 'left');
        $this->db->where('p.id_panitia', $id);
        return $this->db->get()->row();
    }

    public function get_by_username($username)
    {
        return $this->db->get_where($this->table, ['username' => $username])->row();
    }

    public function get_by_ids(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $this->db->where_in('id_panitia', $ids);
        return $this->db->get($this->table)->result();
    }

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id_panitia', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id_panitia', $id);
        return $this->db->delete($this->table);
    }

    public function save_photo($id, $path)
    {
        return $this->update($id, ['foto' => $path]);
    }

    public function username_exists($username, $exclude_id = null)
    {
        $this->db->where('username', $username);
        if ($exclude_id) {
            $this->db->where('id_panitia !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }
}
