<?php

class Auth_model extends CI_Model
{
    public function logged_in()
    {
        return $this->session->userdata('is_login');
    }

    public function in_group($group)
    {
        $role = $this->session->userdata('role_name');
        return $role == $group;
    }

    public function is_admin()
    {
        return $this->session->userdata('role') == '1';
    }

    public function user($id = NULL)
    {
        $user_id = $id ?? $this->session->userdata('user_id');
        $this->db->where('id', $user_id);
        $user = $this->db->get('users')->row();
        if ($user) {
            if ($user->first_name == $user->last_name) {
                $user->nama_lengkap = $user->first_name;
            } else {
                $user->nama_lengkap = $user->first_name . ' ' . $user->last_name;
            }
        }
        return $user;
    }

    public function user_profile($user_id)
    {
        $this->db->where('id', $user_id);
        return $this->db->get('users');
    }

    public function all_profiles($user_ids)
    {
        $this->db->where_in('id', $user_ids);
        return $this->db->get('users')->result();
    }

    public function register($data, $group) {
        $data['ip_address'] = $this->input->ip_address();
        $data['created_on'] = time();
        $data['active'] = '1';

        $user = $this->isExist($data['username']);
        if ($user) {
            $this->db->where('username', $data['username']);
            $this->db->update('users', $data);
            $id = $user->id;
            $group = [
                'user_id' => $id,
                'group_id' => $group
            ];
            $this->db->where('user_id', $id);
            $this->db->update('users_groups', $group);
        } else {
            $this->db->insert('users', $data);
            $id = $this->db->insert_id();
            $group = [
                'user_id' => $id,
                'group_id' => $group
            ];
            $this->db->insert('users_groups', $group);
        }
        return $id;
    }

    public function isExist($username)
    {
        $this->db->where('username', $username);
        return $this->db->get('users')->row();
    }

    public function registerAll(array $users, string $group)
    {
        $inserted = [];
        foreach ($users as $user) {
            $id_user = $this->register($user, $group);
            $inserted[] = [
                'id' => $id_user,
                'username' => $user['username']
            ];
        }
        return $inserted;
    }

    public function login($username) {
        return $this->db->select('a.*, b.user_id, b.group_id, c.id as role, c.name as role_name, c.description')
            ->from('users a')
            ->where(['a.username' => $username])
            ->join('users_groups b', 'a.id=b.user_id')
            ->join('groups c', 'b.group_id=c.id')
            ->get()->row();
    }

    public function errorLogin($data)
    {
        $this->db->insert('login_attempts', $data);
    }

    public function cekErrorLogin($username) {
        return $this->db->get_where('login_attempts', ['login' => $username])->result();
    }

    public function resetErrorLogin($username) {
        $this->db->where('login', $username);
        $this->db->delete('login_attempts');
    }

    public function userSiswa($id_siswa) {
        $this->db->select('a.id_siswa, a.nama, a.password, a.username, d.id');
        $this->db->where('id_siswa', $id_siswa);
        $this->db->join('users d', 'a.username=d.username');
        return $this->db->get('master_siswa a')->row();
    }

    public function aktifkanSiswa($is_active, $id_siswa) {
        $this->db->select('a.id_siswa, a.username, a.password, d.id');
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->join('users d', 'a.username=d.username');
        $result = $this->db->get('master_siswa a')->row();

        $active = $is_active == 'aktifkan' ? '1' : '0';
        if ($result) {
            $group = [
                'user_id' => $result->id,
                'group_id' => '3'
            ];
            $this->db->update('users_groups', $group, ['user_id' => $result->id]);

            if ($active == '1') {
                $update = [
                    'username' => trim($result->username ?? ''),
                    'password' => password_hash(trim($result->password ?? ''), PASSWORD_BCRYPT),
                    'active' => $active
                ];
            } else {
                $update = [
                    'active' => $active
                ];
            }
            return $this->db->update('users', $update, ['id' => $result->id]);
        } else {
            return false;
        }
    }

    public function usersSiswa($ids_siswa) {
        $this->db->select('a.id_siswa, a.nama, a.password, a.username, d.id, d.active');
        $this->db->from('master_siswa a');
        $this->db->where_in('a.id_siswa', $ids_siswa);
        $this->db->join('users d', 'a.username=d.username');
        return $this->db->get()->result();
    }

    public function isAktif($username)
    {
        $this->db
            ->select('a.id_siswa, b.status')
            ->from('master_siswa a')
            ->where('a.username', $username)
            ->join('buku_induk b', 'a.id_siswa = b.id_siswa', 'left');
        return $this->db->get()->row();
    }

    public function allSiswa($ids_user) {
        $this->db->select('a.id_siswa, a.nama, a.password, a.username, a.foto, d.id, d.active');
        $this->db->from('users d');
        $this->db->where_in('d.id', $ids_user);
        $this->db->join('master_siswa a', 'a.username=d.username');
        return $this->db->get()->result();
    }

    public function aktifkanAllSiswa($is_active) {
        $this->db->select('a.username, a.password, a.nama, c.id, c.active');
        $this->db->join('users c', 'a.username=c.username', 'left');
        $result = $this->db->get('master_siswa a')->result();

        $where = $is_active == 'aktifkan' ? '0' : '1';
        $active = $is_active == 'aktifkan' ? '1' : '0';
        $registres = [];
        $updates = [];
        $groups = [];
        foreach ($result as $rows) {
            if ($rows->id == null) {
                $nama = explode(' ', $rows->nama ?? '');
                $first_name = $nama[0];
                $last_name = end($nama);

                $registres[] = [
                    'username' => trim($rows->username ?? ''),
                    'password' => password_hash(trim($rows->password ?? ''), PASSWORD_BCRYPT),
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
            } else {
                $groups[] = [
                    'user_id' => $rows->id,
                    'group_id' => '3'
                ];
                if ($rows->active == $where) {
                    if ($active == '1') {
                        $updates[] = [
                            'id' => $rows->id,
                            'username' => trim($rows->username ?? ''),
                            'password' => password_hash(trim($rows->password ?? ''), PASSWORD_BCRYPT),
                            'active' => $active
                        ];
                    } else {
                        $updates[] = [
                            'id' => $rows->id,
                            'active' => $active
                        ];
                    }
                }
            }
        }

        if (count($updates)>0) {
            $this->db->update_batch('users', $updates, 'id');
        }

        if (count($registres)>0) {
            $this->registerAll($registres, '3');
        }

        if (count($groups)>0) {
            $this->db->update_batch('users_groups', $groups, 'user_id');
        }

        return array_merge($updates, $registres);
    }

    public function userGuru($id_guru) {
        $this->db->select('a.id_guru, a.nama_guru, a.password, a.username, d.id');
        $this->db->where('id_guru', $id_guru);
        $this->db->join('users d', 'a.id_user=d.id');
        return $this->db->get('master_guru a')->row();
    }

    public function aktifkanGuru($is_active, $id_guru) {
        $this->db->select('a.id_guru, a.username, a.password, d.id');
        $this->db->where('a.id_guru', $id_guru);
        $this->db->join('users d', 'a.id_user=d.id');
        $result = $this->db->get('master_guru a')->row();

        $active = $is_active == 'aktifkan' ? '1' : '0';
        if ($result) {
            if ($active == '1') {
                $update = [
                    'username' => trim($result->username ?? ''),
                    'password' => password_hash(trim($result->password ?? ''), PASSWORD_BCRYPT),
                    'active' => $active
                ];
            } else {
                $update = [
                    'active' => $active
                ];
            }
            return $this->db->update('users', $update, ['id' => $result->id]);
        } else {
            return false;
        }
    }

    public function usersGuru($ids_guru) {
        $this->db->select('a.id_guru, a.nama_guru, a.password, a.username, d.id');
        $this->db->where_in('a.id_guru', $ids_guru);
        $this->db->join('users d', 'a.id_user=d.id');
        return $this->db->get('master_guru a')->result();
    }

    public function allGuru($ids_user) {
        $this->db->select('a.id_guru, a.nama_guru, a.password, a.username, a.foto, d.id');
        $this->db->where_in('a.id_user', $ids_user);
        $this->db->join('users d', 'a.id_user=d.id');
        return $this->db->get('master_guru a')->result();
    }

    /**
     * @param $is_active
     * @return array|array[]|object|object[]
     */
    public function aktifkanAllGuru($is_active) {
        $this->db->select('a.username, a.password, a.nama_guru, c.id, c.active');
        $this->db->join('users c', 'a.id_user=c.id', 'left');
        $result = $this->db->get('master_guru a')->result();

        $where = $is_active == 'aktifkan' ? '0' : '1';
        $active = $is_active == 'aktifkan' ? '1' : '0';
        $updates = [];
        $registres = [];
        foreach ($result as $rows) {
            if ($rows->id == null) {
                $nama = explode(' ', $rows->nama_guru ?? '');
                $first_name = $nama[0];
                $last_name = end($nama);

                $registres[] = [
                    'username' => trim($rows->username ?? ''),
                    'password' => password_hash(trim($rows->password ?? ''), PASSWORD_BCRYPT),
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
            } else {
                if ($rows->active == $where) {
                    if ($active == '1') {
                        $updates[] = [
                            'id' => $rows->id,
                            'username' => trim($rows->username ?? ''),
                            'password' => password_hash(trim($rows->password ?? ''), PASSWORD_BCRYPT),
                            'active' => $active
                        ];
                    } else {
                        $updates[] = [
                            'id' => $rows->id,
                            'active' => $active
                        ];
                    }
                }
            }
        }

        if (count($updates)>0) {
            $this->db->update_batch('users', $updates, 'id');
        }

        if (count($registres)>0) {
            $this->registerAll($registres, '2');
        }

        return array_merge($updates, $registres);
    }

    public function userPanitia($id_panitia)
    {
        $this->db->select('a.id_panitia, a.nama_panitia, a.password, a.username, u.id');
        $this->db->from('master_panitia a');
        $this->db->join('users u', 'a.username=u.username', 'left');
        $this->db->where('a.id_panitia', $id_panitia);
        return $this->db->get()->row();
    }

    public function aktifkanPanitia($is_active, $id_panitia)
    {
        $this->db->select('a.id_panitia, a.username, a.password, u.id as user_id, u.active');
        $this->db->from('master_panitia a');
        $this->db->join('users u', 'a.username=u.username', 'left');
        $this->db->where('a.id_panitia', $id_panitia);
        $result = $this->db->get()->row();

        $active = $is_active == 'aktifkan' ? '1' : '0';
        if ($result && $result->user_id) {
            if ($active == '1') {
                $update = [
                    'username' => trim($result->username ?? ''),
                    'password' => password_hash(trim($result->password ?? ''), PASSWORD_BCRYPT),
                    'active' => $active
                ];
            } else {
                $update = [
                    'active' => $active
                ];
            }

            $group = [
                'user_id' => $result->user_id,
                'group_id' => '4'
            ];
            $existGroup = $this->db->get_where('users_groups', ['user_id' => $result->user_id])->row();
            if ($existGroup) {
                $this->db->update('users_groups', $group, ['user_id' => $result->user_id]);
            } else {
                $this->db->insert('users_groups', $group);
            }

            return $this->db->update('users', $update, ['id' => $result->user_id]);
        }

        return false;
    }

    public function usersPanitia($ids_panitia)
    {
        if (empty($ids_panitia)) {
            return [];
        }

        $this->db->select('a.id_panitia, a.nama_panitia, a.password, a.username, u.id');
        $this->db->from('master_panitia a');
        $this->db->where_in('a.id_panitia', $ids_panitia);
        $this->db->join('users u', 'a.username=u.username', 'left');
        return $this->db->get()->result();
    }

    public function aktifkanAllPanitia($is_active)
    {
        $this->db->select('a.username, a.password, a.nama_panitia, c.id, c.active');
        $this->db->from('master_panitia a');
        $this->db->join('users c', 'a.username=c.username', 'left');
        $result = $this->db->get()->result();

        $where = $is_active == 'aktifkan' ? '0' : '1';
        $active = $is_active == 'aktifkan' ? '1' : '0';
        $updates = [];
        $registres = [];
        $groups = [];

        foreach ($result as $rows) {
            if ($rows->id == null) {
                $nama = explode(' ', $rows->nama_panitia ?? '');
                $first_name = $nama[0];
                $last_name = end($nama);

                $registres[] = [
                    'username' => trim($rows->username ?? ''),
                    'password' => password_hash(trim($rows->password ?? ''), PASSWORD_BCRYPT),
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
            } else {
                $groups[] = [
                    'user_id' => $rows->id,
                    'group_id' => '4'
                ];
                if ($rows->active == $where) {
                    if ($active == '1') {
                        $updates[] = [
                            'id' => $rows->id,
                            'username' => trim($rows->username ?? ''),
                            'password' => password_hash(trim($rows->password ?? ''), PASSWORD_BCRYPT),
                            'active' => $active
                        ];
                    } else {
                        $updates[] = [
                            'id' => $rows->id,
                            'active' => $active
                        ];
                    }
                }
            }
        }

        if (count($updates)>0) {
            $this->db->update_batch('users', $updates, 'id');
        }

        if (count($registres)>0) {
            $this->registerAll($registres, '4');
        }

        if (count($groups)>0) {
            $this->db->update_batch('users_groups', $groups, 'user_id');
        }

        return array_merge($updates, $registres);
    }

    public function delete_user($id)
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->where('id', $id);
        $deleted1 = $this->db->delete('users');

        $this->db->where('user_id', $id);
        $deleted2 = $this->db->delete('users_groups');
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        return $deleted1 && $deleted2;
    }

    public function username_check($username, $id = NULL)
    {
        $check = $this->db->get_where('users', ['username' => $username])->result();
        if ($id == null) {
            return count($check)>0; // username sudah ada
        } else {
            // ada username yang sama, dan $user->id tidak sama dengan id yang sedang diedit
            return count($check)>0 && $check[0]->id != $id;
        }
    }

    public function changeUser($id, $user) {
        $this->db->where('id', $id);
        return $this->db->update('users', $user);
    }

    public function groups()
    {
        return $this->db->get('users_groups');
    }

    public function get_users_groups($user_id)
    {
        $this->db->join('groups b', 'a.group_id=b.id');
        return $this->db->where(['a.user_id' => $user_id])->get('users_groups a');
    }

    public function check_level($username)
    {
        $this->db->select('a.id, a.username, b.group_id');
        $this->db->from('users a');
        $this->db->join('users_groups b', 'b.user_id=a.id');
        return $this->db->where(['a.username' => $username])->get()->row();
    }

    public function change_password($username, $old, $new)
    {
        $check = $this->db->get_where('users', ['username' => $username])->result();
        if (password_verify($old, $check->password)) {
            return $this->db->update('users', ['password' => password_hash($new, PASSWORD_BCRYPT)], ['username' => $username]);
        } else {
            return false;
        }
    }

}