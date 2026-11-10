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

        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
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
        } elseif ($this->isPanitia()) {
            $tp_active = $tp;
            $smt_active = $smt;
            $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);
            $data['panitia'] = $panitia;
            
            $is_pengawas = false;
            if ($panitia) {
                $jabatan = strtolower($panitia->jabatan ?? '');
                $namaPanitia = strtolower($panitia->nama_panitia ?? '');
                
                // Get assignments to determine if really a supervisor
                $db_conn = \Config\Database::connect();
                $assignments = $db_conn->table('cbt_pengawas')
                    ->where('id_tp', $tp_active->id_tp)
                    ->where('id_smt', $smt_active->id_smt)
                    ->where('id_guru', 'P' . $panitia->id_panitia)
                    ->get()->getResult();

                if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas' || !empty($assignments)) {
                    $is_pengawas = true;
                }
            }
            $data['is_pengawas'] = $is_pengawas;
            $data['pengumumans'] = $this->getPostUser($db, 0);
        } else {
            $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['pengumumans'] = $this->getPostUser($db, 0);
        }

        return $this->renderView('pengumuman/data', $data);
    }

    /**
     * Guard: hanya admin yang boleh tulis/edit/hapus pengumuman.
     * Guru/panitia hanya baca. Return response 403 jika bukan admin, null jika lolos.
     */
    private function requireAdmin()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized', null, null, 401);
        }

        if (!$this->isAdmin()) {
            return $this->respond(false, 'Hanya Administrator yang dapat menambah/mengubah/menghapus pengumuman', null, null, 403);
        }

        return null;
    }

    /**
     * Helper to get current user identity for likes
     */
    private function getCurrentUserIdentity()
    {
        $dari = 0;
        $dari_group = 1;

        if (!$this->isAdmin()) {
            $user = $this->getUser();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if ($this->isPanitia()) {
                $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $panitia->id_panitia ?? 0;
                $dari_group = 4;
            } elseif ($this->inGroup('guru')) {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $guru->id_guru ?? 0;
                $dari_group = 2;
            } elseif ($this->inGroup('siswa')) {
                $siswa = $this->dashboardModel->getDataSiswaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $siswa->id_siswa ?? 0;
                $dari_group = 3;
            }
        }
        return ['dari' => $dari, 'dari_group' => $dari_group];
    }

    /**
     * Get posts by user
     */
    private function getPostUser($db, int $userId = 0): array
    {
        $identity = $this->getCurrentUserIdentity();
        $dari = $identity['dari'];
        $dari_group = $identity['dari_group'];

        $builder = $db->table('post a');
        $builder->select('a.*, b.nama_guru, b.foto, d.nama_panitia, d.foto as foto_panitia, 
            (SELECT COUNT(id_comment) FROM post_comments WHERE post_comments.id_post = a.id_post) as jml,
            (SELECT COUNT(*) FROM post_likes WHERE post_likes.id_post = a.id_post AND post_likes.id_comment IS NULL) as likes,
            (SELECT COUNT(*) FROM post_likes WHERE post_likes.id_post = a.id_post AND post_likes.id_comment IS NULL AND post_likes.dari = ' . $dari . ' AND post_likes.dari_group = ' . $dari_group . ') as liked');
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

        $identity = $this->getCurrentUserIdentity();
        $dari = $identity['dari'];
        $dari_group = $identity['dari_group'];

        $db = \Config\Database::connect();
        $builder = $db->table('post a');
        $builder->select('a.*, b.nama_guru, b.foto, d.nama_panitia, d.foto as foto_panitia, 
            (SELECT COUNT(id_comment) FROM post_comments WHERE post_comments.id_post = a.id_post) as jml,
            (SELECT COUNT(*) FROM post_likes WHERE post_likes.id_post = a.id_post AND post_likes.id_comment IS NULL) as likes,
            (SELECT COUNT(*) FROM post_likes WHERE post_likes.id_post = a.id_post AND post_likes.id_comment IS NULL AND post_likes.dari = ' . $dari . ' AND post_likes.dari_group = ' . $dari_group . ') as liked');
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

        $identity = $this->getCurrentUserIdentity();
        $dari = $identity['dari'];
        $dari_group = $identity['dari_group'];

        $db = \Config\Database::connect();
        $db->query('SET SQL_BIG_SELECTS=1');

        $builder = $db->table('post_comments a');
        $builder->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, d.nama_panitia, d.foto as foto_panitia, 
            (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml,
            (SELECT COUNT(*) FROM post_likes WHERE post_likes.id_comment = a.id_comment) as likes,
            (SELECT COUNT(*) FROM post_likes WHERE post_likes.id_comment = a.id_comment AND post_likes.dari = ' . $dari . ' AND post_likes.dari_group = ' . $dari_group . ') as liked');
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
     * Save new post — hanya admin
     */
    public function save()
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

        $kepada = $this->request->getPost('kepada');
        $dari = $this->request->getPost('dari');
        $dari_group = $dari == '0' ? 1 : 2;

        if ($this->isPanitia()) {
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
     * Get single post detail for edit (AJAX) — hanya admin
     */
    public function getDetail($id_post)
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

        $db = \Config\Database::connect();
        $post = $db->table('post')->where('id_post', $id_post)->get()->getRow();

        if (!$post) {
            return $this->respond(false, 'Pengumuman tidak ditemukan');
        }

        return $this->respond(true, '', $post);
    }

    /**
     * Update existing post — hanya admin
     */
    public function update()
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

        $id_post = $this->request->getPost('id_post');
        $kepada = $this->request->getPost('kepada');
        $text = $this->request->getPost('text');

        if (empty($id_post) || empty($text)) {
            return $this->respond(false, 'ID Pengumuman dan isi pengumuman harus diisi');
        }

        $db = \Config\Database::connect();
        $post = $db->table('post')->where('id_post', $id_post)->get()->getRow();

        if (!$post) {
            return $this->respond(false, 'Pengumuman tidak ditemukan');
        }

        $data = [
            'kepada' => is_array($kepada) ? json_encode($kepada) : $kepada,
            'text' => $text,
            'updated' => date('Y-m-d H:i:s'),
        ];

        $updated = $db->table('post')->where('id_post', $id_post)->update($data);

        return $this->respond($updated !== false, $updated !== false ? 'Pengumuman berhasil diperbarui' : 'Gagal memperbarui pengumuman');
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

            if ($this->isPanitia()) {
                $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $panitia->id_panitia ?? 0;
                $dari_group = 4;
            } else {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $guru->id_guru ?? 0;
                $dari_group = 2;
            }
        }

        $id_post = $this->request->getPost('id_post');
        $text = $this->request->getPost('text');

        if (empty($id_post) || empty($text)) {
            return $this->respond(false, 'ID Post dan text komentar harus diisi');
        }

        $data = [
            'id_post' => $id_post,
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $text,
            'tanggal' => date('Y-m-d H:i:s'),
        ];

        $db = \Config\Database::connect();

        try {
            $db->table('post_comments')->insert($data);

            if ($db->affectedRows() > 0) {
                $id = $db->insertID();

                // Return the new comment with full details
                $db->query('SET SQL_BIG_SELECTS=1');
                $comment = $db->table('post_comments a')
                    ->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, d.nama_panitia, d.foto as foto_panitia, 0 AS jml')
                    ->join('master_guru b', 'a.dari=b.id_guru', 'left')
                    ->join('master_siswa c', 'a.dari=c.id_siswa', 'left')
                    ->join('master_panitia d', 'a.dari=d.id_panitia', 'left')
                    ->where('a.id_comment', $id)
                    ->get()->getResult();

                // If comment is empty for some reason, build it manually
                if (empty($comment)) {
                    $comment = [
                        (object) [
                            'id_comment' => $id,
                            'id_post' => $id_post,
                            'dari' => $dari,
                            'dari_group' => $dari_group,
                            'text' => $text,
                            'tanggal' => date('Y-m-d H:i:s'),
                            'nama_guru' => $dari_group == 2 ? 'Guru' : null,
                            'nama_siswa' => null,
                            'nama_panitia' => $dari_group == 4 ? 'Panitia' : null,
                            'foto' => null,
                            'foto_siswa' => null,
                            'foto_panitia' => null,
                            'jml' => 0
                        ]
                    ];
                }

                return $this->respond(true, 'Komentar berhasil ditambahkan', $comment);
            } else {
                return $this->respond(false, 'Gagal menyimpan ke database (No rows affected)');
            }
        } catch (\Exception $e) {
            log_message('error', 'saveKomentar error: ' . $e->getMessage());
            return $this->respond(false, 'Database Error: ' . $e->getMessage());
        }
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

            if ($this->isPanitia()) {
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
     * Delete post — hanya admin
     */
    public function hapusPost($id_post)
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

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
     * Delete comment — hanya admin
     */
    public function hapusKomentar($id_comment)
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

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
     * Delete reply — hanya admin
     */
    public function hapusBalasan($id_reply)
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

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
     * Save running text — hanya admin
     */
    public function saveRunningText()
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

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
     * Delete running text — hanya admin
     */
    public function hapusRunningText($id)
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

        $db = \Config\Database::connect();
        $deleted = $db->table('running_text')->where('id_text', $id)->delete();

        return $this->respond($deleted, $deleted ? 'Running text berhasil dihapus' : 'Gagal menghapus running text');
    }

    /**
     * Get users for mention/tagging (AJAX) — hanya admin (dipakai saat tulis pengumuman)
     */
    public function getUsers()
    {
        if ($deny = $this->requireAdmin()) {
            return $deny;
        }

        $query = $this->request->getGet('q') ?? '';
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $db = \Config\Database::connect();
        $users = [];

        // Get Guru
        $gurus = $db->table('master_guru')
            ->select('id_guru as id, nama_guru as name, foto, "guru" as type')
            ->like('nama_guru', $query)
            ->limit(10)
            ->get()->getResult();

        foreach ($gurus as $g) {
            $users[] = [
                'id' => 'guru_' . $g->id,
                'name' => $g->name,
                'avatar' => !empty($g->foto) && file_exists(FCPATH . $g->foto)
                    ? base_url($g->foto)
                    : base_url('assets/img/guru.png'),
                'type' => 'Guru'
            ];
        }

        // Get Panitia
        $panitias = $db->table('master_panitia')
            ->select('id_panitia as id, nama_panitia as name, foto, "panitia" as type')
            ->like('nama_panitia', $query)
            ->limit(10)
            ->get()->getResult();

        foreach ($panitias as $p) {
            $users[] = [
                'id' => 'panitia_' . $p->id,
                'name' => $p->name,
                'avatar' => !empty($p->foto) && file_exists(FCPATH . $p->foto)
                    ? base_url($p->foto)
                    : base_url('assets/img/guru.png'),
                'type' => 'Panitia'
            ];
        }

        return $this->respond(true, '', $users);
    }

    /**
     * Toggle like on post or comment (AJAX)
     */
    public function toggleLike()
    {
        $id_post = $this->request->getPost('id_post');
        $id_comment = $this->request->getPost('id_comment'); // null for post likes

        if (empty($id_post)) {
            return $this->respond(false, 'ID Post harus diisi');
        }

        // Get current user info
        $dari = 0;
        $dari_group = 1;

        if (!$this->isAdmin()) {
            $user = $this->getUser();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if ($this->isPanitia()) {
                $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $panitia->id_panitia ?? 0;
                $dari_group = 4;
            } elseif ($this->inGroup('guru')) {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $guru->id_guru ?? 0;
                $dari_group = 2;
            } elseif ($this->inGroup('siswa')) {
                $siswa = $this->dashboardModel->getDataSiswaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $siswa->id_siswa ?? 0;
                $dari_group = 3;
            }
        }

        $db = \Config\Database::connect();

        // Check if already liked
        $existing = $db->table('post_likes')
            ->where('id_post', $id_post)
            ->where('id_comment', $id_comment ?: null)
            ->where('dari', $dari)
            ->where('dari_group', $dari_group)
            ->get()->getRow();

        $liked = false;

        if ($existing) {
            // Unlike - remove the like
            $db->table('post_likes')->where('id_like', $existing->id_like)->delete();
            $liked = false;
            $message = 'Like dihapus';
        } else {
            // Like - add the like
            $data = [
                'id_post' => $id_post,
                'id_comment' => $id_comment ?: null,
                'dari' => $dari,
                'dari_group' => $dari_group,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->table('post_likes')->insert($data);
            $liked = true;
            $message = 'Like ditambahkan';
        }

        // Get updated like count
        $likesCount = $db->table('post_likes')
            ->where('id_post', $id_post)
            ->where('id_comment', $id_comment ?: null)
            ->countAllResults();

        return $this->respond(true, $message, [
            'liked' => $liked,
            'count' => $likesCount,
            'id_post' => $id_post,
            'id_comment' => $id_comment
        ]);
    }

    /**
     * Get likes count and user's like status for a post/comment (AJAX)
     */
    public function getLikes($id_post, $id_comment = null)
    {
        // Get current user info
        $dari = 0;
        $dari_group = 1;

        if (!$this->isAdmin()) {
            $user = $this->getUser();
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if ($this->isPanitia()) {
                $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $panitia->id_panitia ?? 0;
                $dari_group = 4;
            } elseif ($this->inGroup('guru')) {
                $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $guru->id_guru ?? 0;
                $dari_group = 2;
            } elseif ($this->inGroup('siswa')) {
                $siswa = $this->dashboardModel->getDataSiswaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $dari = $siswa->id_siswa ?? 0;
                $dari_group = 3;
            }
        }

        $db = \Config\Database::connect();

        // Get like count
        $count = $db->table('post_likes')
            ->where('id_post', $id_post)
            ->where('id_comment', $id_comment)
            ->countAllResults();

        // Check if current user liked
        $userLiked = $db->table('post_likes')
            ->where('id_post', $id_post)
            ->where('id_comment', $id_comment)
            ->where('dari', $dari)
            ->where('dari_group', $dari_group)
            ->countAllResults() > 0;

        return $this->respond(true, '', [
            'count' => $count,
            'liked' => $userLiked,
            'id_post' => $id_post,
            'id_comment' => $id_comment
        ]);
    }
}
