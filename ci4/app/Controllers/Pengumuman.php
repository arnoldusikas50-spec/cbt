<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\LogModel;

/**
 * Pengumuman Controller - Migrated from CI3
 * Handles announcements and notifications
 */
class Pengumuman extends BaseController
{
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Main index page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->inGroup('panitia')) {
            return $this->showError('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini', 403);
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Pengumuman',
            'subjudul' => 'Semua Pengumuman',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'gurus' => $this->masterModel->getAllGuru(),
            'kelas' => $this->masterModel->getAllKelas($tp->id_tp, $smt->id_smt),
            'running_text' => $this->getRunningTextData(),
            'is_admin' => $this->isAdmin(),
        ];

        $db = \Config\Database::connect();

        if ($this->isAdmin()) {
            $data['pengumumans'] = $this->getPostUser($db, 0);
        } elseif ($this->inGroup('panitia')) {
            $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['panitia'] = $panitia;
            $data['pengumumans'] = $this->getPostUser($db, 0);
        } else {
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['pengumumans'] = $this->getPostUser($db, $guru->id_guru ?? 0);
        }

        return $this->renderView('pengumuman/data', $data);
    }

    /**
     * Get posts by user
     */
    private function getPostUser($db, int $userId = 0): array
    {
        $builder = $db->table('post a');
        $builder->select('a.*, b.nama_guru, b.foto, d.nama_panitia, d.foto as foto_panitia');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_panitia d', 'a.dari=d.id_panitia', 'left');
        if ($userId > 0) {
            $builder->where('a.dari', $userId);
        }
        $builder->orderBy('a.tanggal', 'desc');
        return $builder->get()->getResult();
    }

    /**
     * Get posts paginated (AJAX)
     */
    public function getPost($page = 0)
    {
        $perPage = 5;
        $offset = (int) $page * $perPage;

        $db = \Config\Database::connect();
        $builder = $db->table('post a');
        $builder->select('a.*, b.nama_guru, b.foto, d.nama_panitia, d.foto as foto_panitia');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_panitia d', 'a.dari=d.id_panitia', 'left');
        $builder->orderBy('a.tanggal', 'desc');
        $builder->limit($perPage, $offset);
        $posts = $builder->get()->getResult();

        foreach ($posts as $post) {
            if (($post->dari ?? 0) > 0) {
                $post->foto = file_exists(FCPATH . ($post->foto ?? '')) ? $post->foto : 'assets/img/guru.png';
            }
        }

        return $this->respond(true, '', $posts);
    }

    /**
     * Get comments for a post (AJAX)
     */
    public function getComment($id_post, $page = 0)
    {
        $perPage = 5;
        $offset = (int) $page * $perPage;

        $db = \Config\Database::connect();
        $db->query('SET SQL_BIG_SELECTS=1');

        $builder = $db->table('post_comments a');
        $builder->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, d.nama_panitia, d.foto as foto_panitia, (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $builder->join('master_panitia d', 'a.dari=d.id_panitia', 'left');
        $builder->where('a.id_post', $id_post);
        $builder->orderBy('a.tanggal', 'desc');
        $builder->limit($perPage, $offset);

        $comment = $builder->get()->getResult();

        return $this->respond(true, '', $comment);
    }

    /**
     * Get replies for a comment (AJAX)
     */
    public function getReplies($id_comment, $page = 0)
    {
        $perPage = 5;
        $offset = (int) $page * $perPage;

        $db = \Config\Database::connect();
        $db->query('SET SQL_BIG_SELECTS=1');

        $builder = $db->table('post_reply a');
        $builder->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, d.nama_panitia, d.foto as foto_panitia');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $builder->join('master_panitia d', 'a.dari=d.id_panitia', 'left');
        $builder->where('a.id_comment', $id_comment);
        $builder->orderBy('a.tanggal', 'desc');
        $builder->limit($perPage, $offset);

        $replies = $builder->get()->getResult();

        return $this->respond(true, '', $replies);
    }

    /**
     * Save new post
     */
    public function save()
    {
        $kepada = $this->request->getPost('kepada');
        $dari = $this->request->getPost('dari');
        $dari_group = $dari == '0' ? 1 : 2;

        if ($this->inGroup('panitia')) {
            $dari_group = 4;
        }

        $data = [
            'kepada' => is_array($kepada) ? json_encode($kepada) : $kepada,
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $this->request->getPost('text'),
            'tanggal' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
        ];

        $db = \Config\Database::connect();
        $insert = $db->table('post')->replace($data);

        return $this->respond($insert, $insert ? 'Pengumuman berhasil disimpan' : 'Gagal menyimpan pengumuman');
    }

    /**
     * Save comment
     */
    public function saveKomentar()
    {
        $dari = 0;
        $dari_group = 1;

        if (!$this->isAdmin()) {
            $user = $this->getUser();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if ($this->inGroup('panitia')) {
                $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $panitia->id_panitia ?? 0;
                $dari_group = 4;
            } else {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $guru->id_guru ?? 0;
                $dari_group = 2;
            }
        }

        $data = [
            'id_post' => $this->request->getPost('id_post'),
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $this->request->getPost('text'),
            'tanggal' => date('Y-m-d H:i:s'),
        ];

        $db = \Config\Database::connect();
        $db->table('post_comments')->insert($data);
        $id = $db->insertID();

        // Return the new comment
        $db->query('SET SQL_BIG_SELECTS=1');
        $comment = $db->table('post_comments a')
            ->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, d.nama_panitia, d.foto as foto_panitia, 0 AS jml')
            ->join('master_guru b', 'a.dari=b.id_guru', 'left')
            ->join('master_siswa c', 'a.dari=c.id_siswa', 'left')
            ->join('master_panitia d', 'a.dari=d.id_panitia', 'left')
            ->where('a.id_comment', $id)
            ->get()->getResult();

        return $this->respond(true, 'Komentar berhasil ditambahkan', $comment);
    }

    /**
     * Save reply
     */
    public function saveBalasan()
    {
        $dari = 0;
        $dari_group = 1;

        if (!$this->isAdmin()) {
            $user = $this->getUser();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if ($this->inGroup('panitia')) {
                $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $panitia->id_panitia ?? 0;
                $dari_group = 4;
            } else {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $guru->id_guru ?? 0;
                $dari_group = 2;
            }
        }

        $data = [
            'id_comment' => $this->request->getPost('id_comment'),
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $this->request->getPost('text'),
            'tanggal' => date('Y-m-d H:i:s'),
        ];

        $db = \Config\Database::connect();
        $db->table('post_reply')->insert($data);
        $id = $db->insertID();

        // Return the new reply
        $db->query('SET SQL_BIG_SELECTS=1');
        $reply = $db->table('post_reply a')
            ->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, d.nama_panitia, d.foto as foto_panitia')
            ->join('master_guru b', 'a.dari=b.id_guru', 'left')
            ->join('master_siswa c', 'a.dari=c.id_siswa', 'left')
            ->join('master_panitia d', 'a.dari=d.id_panitia', 'left')
            ->where('a.id_reply', $id)
            ->get()->getResult();

        return $this->respond(true, 'Balasan berhasil ditambahkan', $reply);
    }

    /**
     * Delete post
     */
    public function hapusPost($id_post)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Get comments
        $comments = $db->table('post_comments')->where('id_post', $id_post)->get()->getResult();

        // Delete replies
        foreach ($comments as $comment) {
            $db->table('post_reply')->where('id_comment', $comment->id_comment)->delete();
        }

        // Delete comments
        $db->table('post_comments')->where('id_post', $id_post)->delete();

        // Delete post
        $deleted = $db->table('post')->where('id_post', $id_post)->delete();

        $db->transComplete();

        return $this->respond($db->transStatus(), $db->transStatus() ? 'Pengumuman berhasil dihapus' : 'Gagal menghapus pengumuman');
    }

    /**
     * Delete comment
     */
    public function hapusKomentar($id_comment)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Delete replies
        $db->table('post_reply')->where('id_comment', $id_comment)->delete();

        // Delete comment
        $db->table('post_comments')->where('id_comment', $id_comment)->delete();

        $db->transComplete();

        return $this->respond($db->transStatus(), $db->transStatus() ? 'Komentar berhasil dihapus' : 'Gagal menghapus komentar');
    }

    /**
     * Delete reply
     */
    public function hapusBalasan($id_reply)
    {
        $db = \Config\Database::connect();
        $deleted = $db->table('post_reply')->where('id_reply', $id_reply)->delete();

        return $this->respond($deleted, $deleted ? 'Balasan berhasil dihapus' : 'Gagal menghapus balasan');
    }

    /**
     * Get running text
     */
    public function getRunningText()
    {
        $data = $this->getRunningTextData();
        return $this->respond(true, '', ['running_text' => $data]);
    }

    /**
     * Get running text data
     */
    private function getRunningTextData(): array
    {
        $db = \Config\Database::connect();
        return $db->table('running_text')->get()->getResult();
    }

    /**
     * Save running text
     */
    public function saveRunningText()
    {
        $input = $this->request->getPost('text');
        if ($input) {
            $input = json_decode($input);
        }

        if (!$input || !is_array($input)) {
            return $this->respond(false, 'Data tidak valid');
        }

        $db = \Config\Database::connect();
        $updates = [];

        foreach ($input as $d) {
            $data = [
                'id_text' => $d->id_text ?? null,
                'text' => $d->text ?? '',
            ];
            $update = $db->table('running_text')->replace($data);
            $updates[] = $update;
        }

        return $this->respond(true, 'Running text berhasil disimpan', ['status' => $updates]);
    }

    /**
     * Delete running text
     */
    public function hapusRunningText($id)
    {
        $db = \Config\Database::connect();
        $deleted = $db->table('running_text')->where('id_text', $id)->delete();

        return $this->respond($deleted, $deleted ? 'Running text berhasil dihapus' : 'Gagal menghapus running text');
    }
}
