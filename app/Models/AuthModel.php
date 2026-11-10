<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AuthModel - Migrated from CI3 Auth_model
 * Handles user authentication, login attempts, and user management
 */
class AuthModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'ip_address',
        'username',
        'password',
        'email',
        'activation_code',
        'forgotten_password_code',
        'forgotten_password_time',
        'remember_code',
        'created_on',
        'last_login',
        'active',
        'first_name',
        'last_name',
        'company',
        'phone',
        'foto',
        'nomor_peserta'
    ];

    // Dates
    protected $useTimestamps = false;

    /**
     * Check if user is logged in
     */
    public function loggedIn(): bool
    {
        return (bool) session()->get('is_login');
    }

    /**
     * Check if current user is in specified group
     */
    public function inGroup(string $group): bool
    {
        $role = session()->get('role_name');
        return strtolower($role ?? '') === strtolower($group);
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool
    {
        return session()->get('role') === '1';
    }

    /**
     * Get user by ID
     */
    public function user(?int $id = null): ?object
    {
        $userId = $id ?? session()->get('user_id');

        $user = $this->find($userId);

        if ($user) {
            if ($user->first_name === $user->last_name) {
                $user->nama_lengkap = $user->first_name;
            } else {
                $user->nama_lengkap = $user->first_name . ' ' . $user->last_name;
            }
        }

        return $user;
    }

    /**
     * Get user profile
     */
    public function userProfile(int $userId): ?object
    {
        return $this->find($userId);
    }

    /**
     * Get all profiles by user IDs
     */
    public function allProfiles(array $userIds): array
    {
        return $this->whereIn('id', $userIds)->findAll();
    }

    /**
     * Register new user
     */
    public function register(array $data, string $group): int
    {
        $data['ip_address'] = service('request')->getIPAddress();
        $data['created_on'] = time();
        $data['active'] = '1';

        $existingUser = $this->isExist($data['username']);

        if ($existingUser) {
            $this->update($existingUser->id, $data);
            $id = $existingUser->id;

            $db = \Config\Database::connect();
            $db->table('users_groups')
                ->where('user_id', $id)
                ->update(['group_id' => $group]);
        } else {
            $this->insert($data);
            $id = $this->getInsertID();

            $db = \Config\Database::connect();
            $db->table('users_groups')->insert([
                'user_id' => $id,
                'group_id' => $group
            ]);
        }

        return $id;
    }

    /**
     * Check if username exists
     */
    public function isExist(string $username): ?object
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Login - get user with role information
     */
    public function login(string $username): ?object
    {
        $db = \Config\Database::connect();

        $query = $db->table('users a')
            ->select('a.*, b.user_id, b.group_id, c.id as role, c.name as role_name, c.description')
            ->join('users_groups b', 'a.id = b.user_id')
            ->join('groups c', 'b.group_id = c.id')
            ->where('a.username', $username)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Record failed login attempt
     */
    public function errorLogin(array $data): bool
    {
        $db = \Config\Database::connect();
        return $db->table('login_attempts')->insert($data);
    }

    /**
     * Check failed login attempts
     */
    public function cekErrorLogin(string $username): array
    {
        $db = \Config\Database::connect();
        return $db->table('login_attempts')
            ->where('login', $username)
            ->get()
            ->getResult();
    }

    /**
     * Reset failed login attempts
     */
    public function resetErrorLogin(string $username): bool
    {
        $db = \Config\Database::connect();
        return $db->table('login_attempts')
            ->where('login', $username)
            ->delete();
    }

    /**
     * Check if student is active
     */
    public function isAktif(string $username): ?object
    {
        $db = \Config\Database::connect();

        $query = $db->table('master_siswa a')
            ->select('a.id_siswa, b.status')
            ->join('buku_induk b', 'a.id_siswa = b.id_siswa', 'left')
            ->where('a.username', $username)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Check username availability
     */
    public function usernameCheck(string $username, ?int $id = null): bool
    {
        $result = $this->where('username', $username)->findAll();

        if ($id === null) {
            return count($result) > 0;
        }

        return count($result) > 0 && $result[0]->id != $id;
    }

    /**
     * Update user data
     */
    public function changeUser(int $id, array $userData): bool
    {
        return $this->update($id, $userData);
    }

    /**
     * Delete user and their group associations
     */
    public function deleteUser(int $id): bool
    {
        $db = \Config\Database::connect();

        $db->query('SET FOREIGN_KEY_CHECKS=0');

        $deleted1 = $this->delete($id);
        $deleted2 = $db->table('users_groups')->where('user_id', $id)->delete();

        $db->query('SET FOREIGN_KEY_CHECKS=1');

        return $deleted1 && $deleted2;
    }

    /**
     * Get all user groups
     */
    public function groups(): array
    {
        $db = \Config\Database::connect();
        return $db->table('users_groups')->get()->getResult();
    }

    /**
     * Get groups for specific user
     */
    public function getUsersGroups(int $userId): array
    {
        $db = \Config\Database::connect();

        return $db->table('users_groups a')
            ->join('groups b', 'a.group_id = b.id')
            ->where('a.user_id', $userId)
            ->get()
            ->getResult();
    }

    /**
     * Check user level by username
     */
    public function checkLevel(string $username): ?object
    {
        $db = \Config\Database::connect();

        $query = $db->table('users a')
            ->select('a.id, a.username, b.group_id')
            ->join('users_groups b', 'b.user_id = a.id')
            ->where('a.username', $username)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Change user password
     */
    public function changePassword(string $username, string $oldPassword, string $newPassword): bool
    {
        $user = $this->where('username', $username)->first();

        if ($user && password_verify($oldPassword, $user->password)) {
            return $this->where('username', $username)
                ->set('password', password_hash($newPassword, PASSWORD_BCRYPT))
                ->update();
        }

        return false;
    }
}
