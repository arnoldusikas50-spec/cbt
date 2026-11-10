<?php

namespace App\Controllers;

use App\Models\DashboardModel;

class Datalogs extends BaseController
{
    protected DashboardModel $dashboard;
    protected $db;

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->getUser();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Log Aktivitas',
            'subjudul' => 'Log Aktivitas',
            'setting' => $setting,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'is_admin' => $this->isAdmin()
        ];

        // Counts
        $tableName = 'logs';
        if (!$this->db->tableExists('logs') && $this->db->tableExists('log')) {
            $tableName = 'log';
        }

        // Check if id_group exists
        $hasIdGroup = $this->db->fieldExists('id_group', $tableName);

        $counts = ['total' => 0, 'guru' => 0, 'siswa' => 0, 'admin' => 0];

        try {
            $counts['total'] = $this->db->table($tableName)->countAllResults();
            if ($hasIdGroup) {
                $counts['guru'] = $this->db->table($tableName)->where('id_group', 2)->countAllResults();
                $counts['siswa'] = $this->db->table($tableName)->where('id_group', 3)->countAllResults();
                $counts['admin'] = $this->db->table($tableName)->where('id_group', 1)->countAllResults();
            } else {
                // If no id_group, likely need join, skipping specific counts for performance or using join
                $counts['guru'] = $this->db->table($tableName . ' l')
                    ->join('users_groups ug', 'l.id_user = ug.user_id')
                    ->where('ug.group_id', 2)->countAllResults();
                $counts['siswa'] = $this->db->table($tableName . ' l')
                    ->join('users_groups ug', 'l.id_user = ug.user_id')
                    ->where('ug.group_id', 3)->countAllResults();
                $counts['admin'] = $this->db->table($tableName . ' l')
                    ->join('users_groups ug', 'l.id_user = ug.user_id')
                    ->where('ug.group_id', 1)->countAllResults();
            }
        } catch (\Exception $e) {
            // Sshh
        }

        $data['counts'] = $counts;

        if ($this->isAdmin()) {
            return $this->renderView('master/logs/data', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            return $this->renderView('master/logs/data', $data);
        }
    }

    public function getLog()
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $limit = $this->request->getGet('limit') ?? 10;
            $limit = (int) $limit;
            $filter = $this->request->getGet('filter'); // array
            $search = $this->request->getGet('search');

            if (!is_array($filter))
                $filter = [];
            $offset = ($page - 1) * $limit;

            $tableName = 'logs';
            if (!$this->db->tableExists('logs') && $this->db->tableExists('log')) {
                $tableName = 'log';
            }

            // Check columns
            $hasIdGroup = $this->db->fieldExists('id_group', $tableName);
            $hasLogDesc = $this->db->fieldExists('log_desc', $tableName);
            $hasLogTime = $this->db->fieldExists('log_time', $tableName);

            $builder = $this->db->table($tableName . ' l');

            // Select & Alias
            if (!$hasLogDesc) {
                // Assuming 'logs' schema
                $builder->select('l.id_log, l.waktu as log_time, l.id_user, l.aktivitas as log_type, l.keterangan as log_desc');
            } else {
                $builder->select('l.*');
            }

            $builder->select('u.first_name, u.last_name');
            $builder->join('users u', 'l.id_user = u.id', 'left');

            // Handle id_group
            if (!$hasIdGroup) {
                $builder->select('ug.group_id as id_group');
                $builder->join('users_groups ug', 'u.id = ug.user_id', 'left');
            }

            if (!empty($filter) && is_array($filter)) {
                if ($hasIdGroup) {
                    $builder->whereIn('l.id_group', $filter);
                } else {
                    $builder->whereIn('ug.group_id', $filter);
                }
            }

            if (!empty($search)) {
                $builder->groupStart();
                $builder->like('u.first_name', $search);
                $builder->orLike('u.last_name', $search);
                if (!$hasLogDesc) {
                    $builder->orLike('l.keterangan', $search);
                } else {
                    $builder->orLike('l.log_desc', $search);
                }
                $builder->groupEnd();
            }

            // Clone for count
            $countBuilder = clone $builder;
            $total = $countBuilder->countAllResults();

            if (!$hasLogTime) {
                $builder->orderBy('l.waktu', 'DESC');
            } else {
                $builder->orderBy('l.log_time', 'DESC');
            }

            $builder->limit($limit, $offset);
            $logs = $builder->get()->getResult();

            // Data for photos
            $gurus = [];
            if ($this->db->tableExists('master_guru')) {
                $gurus = $this->db->table('master_guru mg')
                    ->select('u.id, mg.foto')
                    ->join('users u', 'u.username = mg.username', 'left')
                    ->get()->getResult();
            }

            $siswas = [];
            if ($this->db->tableExists('master_siswa')) {
                $siswaBuilder = $this->db->table('master_siswa ms');
                $siswaBuilder->select('u.id, ms.foto');
                $siswaBuilder->join('users u', 'u.username = ms.username', 'left');
                
                // If foto doesn't exist, handle differently
                if (!$this->db->fieldExists('foto', 'master_siswa')) {
                    if ($this->db->fieldExists('foto_siswa', 'master_siswa')) {
                        $siswaBuilder->select('ms.foto_siswa as foto', false);
                    } else {
                        $siswaBuilder->select('NULL as foto', false);
                    }
                }
                
                $siswas = $siswaBuilder->get()->getResult();
            }

            $admins = $this->db->table('users u')
                ->select('u.id, u.foto')
                ->join('users_groups ug', 'u.id = ug.user_id')
                ->where('ug.group_id', 1)
                ->get()->getResult();

            return $this->response->setJSON([
                'logs' => $logs,
                'total' => $total,
                'guru' => $gurus,
                'siswa' => $siswas,
                'admins' => $admins
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    public function hapusLog($id = null)
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(false);

        $tableName = 'logs';
        if (!$this->db->tableExists('logs') && $this->db->tableExists('log')) {
            $tableName = 'log';
        }

        if ($id === 'all') {
            $this->db->table($tableName)->emptyTable();
            return $this->response->setJSON(true);
        } else if ($id === 'selected') {
            $ids = $this->request->getGet('ids');
            if (!empty($ids)) {
                $this->db->table($tableName)->whereIn('id_log', $ids)->delete();
                return $this->response->setJSON(true);
            }
        } else {
            // Delete Single
            if ($id) {
                $this->db->table($tableName)->where('id_log', $id)->delete();
                return $this->response->setJSON(true);
            }
        }
        return $this->response->setJSON(false);
    }
}
