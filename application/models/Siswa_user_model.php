<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Siswa_user_model extends MY_Model
{
    public $fillable = [
        'nis', 'nisn', 'nama', 'username', 'password', 'jenis_kelamin', 'agama', 'email', 'foto'
    ];

    public function __construct()
    {
        $this->table = 'master_siswa';
        $this->primary_key = 'id_siswa';
        $this->timestamps = false;
        $this->soft_deletes = false;
        parent::__construct();
    }

    public function is_username_available($username, $exclude_id = null)
    {
        $this->db->from($this->table);
        $this->db->where('username', $username);
        if ($exclude_id !== null) {
            $this->db->where($this->primary_key . ' !=', $exclude_id);
        }
        return $this->db->count_all_results() === 0;
    }

    public function update_credentials($id_siswa, $new_username, $new_password_plain = null, $hashed_password = null)
    {
        $current = $this->db->get_where($this->table, [$this->primary_key => $id_siswa])->row();
        if (!$current) {
            return false;
        }

        $this->db->trans_begin();
        $update_master = [];
        if ($new_username !== null) {
            $update_master['username'] = $new_username;
        }
        if ($new_password_plain !== null) {
            $update_master['password'] = $new_password_plain;
        }
        if (!empty($update_master)) {
            parent::update($update_master, $id_siswa);
        }

        $user_update = [];
        if ($new_username !== null && $new_username !== $current->username) {
            $user_update['username'] = $new_username;
        }
        if ($hashed_password !== null) {
            $user_update['password'] = $hashed_password;
        }
        if (!empty($user_update)) {
            $this->db->where('username', $current->username);
            $this->db->update('users', $user_update);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }
}
