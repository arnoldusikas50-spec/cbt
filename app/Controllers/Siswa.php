<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\PostModel;
use App\Models\CbtModel;
use App\Models\KelasModel;
use App\Models\MasterModel;

/**
 * Siswa Controller - Migrated from CI3
 * Student profile and functions
 */
class Siswa extends BaseController
{
    protected DashboardModel $dashboardModel;
    protected PostModel $postModel;
    protected CbtModel $cbtModel;
    protected KelasModel $kelasModel;
    protected MasterModel $masterModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->postModel = new PostModel();
        $this->cbtModel = new CbtModel();
        $this->kelasModel = new KelasModel();
        $this->masterModel = new MasterModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->inGroup('siswa')) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $username = session()->get('username');

        $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        $data = [
            'judul' => 'Profil Siswa',
            'subjudul' => 'Data Profil Saya',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'siswa' => $siswa,
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('members/siswa/profil', $data);
    }

    public function getpost()
    {
        $kode = $this->request->getGet('kelas');
        $db = \Config\Database::connect();
        $post = $this->postModel->getPostForUser("'%siswa%'", "'%" . $db->escapeLikeString($kode ?? '') . "%'");
        return $this->response->setJSON($post);
    }

    public function getComment($id_post, $page)
    {
        $perPage = 5;
        $offset = $page * $perPage;

        $db = \Config\Database::connect();
        $builder = $db->table('post_comments a');
        $builder->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $builder->orderBy('a.tanggal', 'desc');
        $builder->where('a.id_post', $id_post);
        $builder->limit($perPage, $offset);
        $comment = $builder->get()->getResult();

        return $this->response->setJSON($comment);
    }

    public function getReplies($id_comment, $page)
    {
        $perPage = 5;
        $offset = $page * $perPage;

        $db = \Config\Database::connect();
        $builder = $db->table('post_reply a');
        $builder->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $builder->orderBy('a.tanggal', 'desc');
        $builder->where('a.id_comment', $id_comment);
        $builder->limit($perPage, $offset);
        $replies = $builder->get()->getResult();

        return $this->response->setJSON($replies);
    }

    public function saveKomentar()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $username = session()->get('username');
        $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        $data = [
            'type' => '1',
            'id_post' => $this->request->getPost('id_post'),
            'dari' => $siswa->id_siswa,
            'dari_group' => 3,
            'text' => $this->request->getPost('text'),
        ];

        $db = \Config\Database::connect();
        $db->table('post_comments')->replace($data);
        $id = $db->insertID();

        $builder = $db->table('post_comments a');
        $builder->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $builder->where('a.id_comment', $id);
        $comment = $builder->get()->getResult();

        return $this->response->setJSON($comment);
    }

    public function saveBalasan()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $username = session()->get('username');
        $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        $data = [
            'id_comment' => $this->request->getPost('id_comment'),
            'dari' => $siswa->id_siswa,
            'dari_group' => 3,
            'text' => $this->request->getPost('text'),
        ];

        $db = \Config\Database::connect();
        $db->table('post_reply')->replace($data);
        $id = $db->insertID();

        $builder = $db->table('post_reply a');
        $builder->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa');
        $builder->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $builder->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $builder->where('a.id_reply', $id);
        $replies = $builder->get()->getResult();

        return $this->response->setJSON($replies);
    }

    public function uploadFile()
    {
        $file = $this->request->getFile('file_uploads');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/file_siswa/', $newName);

            return $this->response->setJSON([
                'status' => true,
                'src' => 'uploads/file_siswa/' . $newName,
                'filename' => pathinfo($newName, PATHINFO_FILENAME),
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize()
            ]);
        }

        return $this->response->setJSON([
            'status' => false,
            'src' => $file ? $file->getErrorString() : 'No file uploaded'
        ]);
    }

    public function deleteFile()
    {
        $src = $this->request->getPost('src');
        if ($src && file_exists(FCPATH . $src)) {
            unlink(FCPATH . $src);
            return 'File Delete Successfully';
        }
        return 'File not found';
    }


    public function catatan()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $username = session()->get('username');
        $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        $catatan_mapel = $this->kelasModel->getCatatanMapelBySiswa($siswa->id_kelas, $tp->id_tp, $smt->id_smt);
        $catatan = [];
        foreach ($catatan_mapel as $cat) {
            if (($cat->type == '2' && $cat->id_siswa == $siswa->id_siswa) || ($cat->type == '1' && $cat->id_kelas == $siswa->id_kelas)) {
                $catatan[] = [
                    'id_catatan' => $cat->id_catatan,
                    'nama_guru' => $cat->nama_guru,
                    'foto_guru' => $this->getFotoGuru($cat->foto, $cat->nip),
                    'id_siswa' => $siswa->id_siswa,
                    'tgl' => $cat->tgl,
                    'table' => 'mapel',
                    'level' => $cat->level,
                    'type' => $cat->type,
                    'readed' => $cat->readed,
                    'reading' => maybe_unserialize($cat->reading ?? '')
                ];
            }
        }

        $catatan_siswa = $this->kelasModel->getCatatanSiswaBySiswa($siswa->id_kelas, $tp->id_tp, $smt->id_smt);
        foreach ($catatan_siswa as $cat) {
            if (($cat->type == '2' && $cat->id_siswa == $siswa->id_siswa) || ($cat->type == '1' && $cat->id_kelas == $siswa->id_kelas)) {
                $catatan[] = [
                    'id_catatan' => $cat->id_catatan,
                    'nama_guru' => $cat->nama_guru,
                    'foto_guru' => $this->getFotoGuru($cat->foto, $cat->nip),
                    'id_siswa' => $siswa->id_siswa,
                    'tgl' => $cat->tgl,
                    'table' => 'wali',
                    'level' => $cat->level,
                    'readed' => $cat->readed,
                    'type' => $cat->type,
                    'reading' => maybe_unserialize($cat->reading ?? '')
                ];
            }
        }

        usort($catatan, function ($a, $b) {
            return strtotime($b['tgl']) - strtotime($a['tgl']);
        });

        $data = [
            'user' => (object) ['first_name' => session()->get('first_name')],
            'siswa' => $siswa,
            'judul' => 'Catatan',
            'subjudul' => 'Catatan Dari Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'catatan' => json_decode(json_encode($catatan)),
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'running_text' => $this->dashboardModel->getRunningText()
        ];

        return view('members/siswa/catatan/data', $data);
    }

    private function getFotoGuru($foto, $nip)
    {
        if ($foto && file_exists(FCPATH . $foto))
            return $foto;
        $pathJpg = 'uploads/profile_guru/' . $nip . '.jpg';
        $pathPng = 'uploads/profile_guru/' . $nip . '.png';
        if (file_exists(FCPATH . $pathJpg))
            return $pathJpg;
        if (file_exists(FCPATH . $pathPng))
            return $pathPng;
        return 'assets/img/user.jpg';
    }

    public function detailCatatan($table, $id_catatan)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $username = session()->get('username');
        $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        if ($siswa && $table == 'mapel') {
            $detail = $this->kelasModel->getCatatanMapelSiswaDetail($id_catatan);
        } else {
            $detail = $this->kelasModel->getCatatanKelasSiswaDetail($id_catatan);
        }

        $reading = [];
        if ($detail) {
            $detail->id_siswa = $siswa->id_siswa;
            $reading = $detail->reading != null ? maybe_unserialize($detail->reading ?? '') : [];
        }
        return $this->response->setJSON(['reading' => $reading, 'detail' => $detail]);
    }

    public function readed($table, $id_catatan)
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $username = session()->get('username');
        $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        $tbl = ($table == 'mapel') ? 'kelas_catatan_mapel' : 'kelas_catatan_wali';

        $cat = $this->kelasModel->getReading($tbl, $id_catatan);
        $readed = ($cat->readed == '0') ? date('Y-m-d H:i:s') : '0';

        $db = \Config\Database::connect();
        $builder = $db->table($tbl);

        if ($cat->type == '1') {
            $reading = maybe_unserialize($cat->reading ?? '');
            if (!is_array($reading))
                $reading = [];
            if (!in_array($siswa->id_siswa, $reading)) {
                $reading[] = $siswa->id_siswa;
            }
            $builder->set('reading', json_encode($reading));
        } else {
            $builder->set('readed', $readed);
        }

        $builder->where('id_catatan', $id_catatan);
        $update = $builder->update();

        return $this->response->setJSON($update);
    }

    public function getTimer($id_siswa, $id_jadwal)
    {
        $durasi = $this->cbtModel->db->table('cbt_durasi_siswa')
            ->where('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()->getRow();

        return $this->response->setJSON(['durasi' => $durasi]);
    }

    public function total_hari($id_day, $bulan, $taun)
    {
        $days = 0;
        $dates = [];
        $total_days = date('t', strtotime("$taun-$bulan-01"));
        $idday = ($id_day == '7') ? 0 : $id_day;
        for ($i = 1; $i <= $total_days; $i++) {
            if (date('N', strtotime("$taun-$bulan-$i")) == $idday) {
                $days++;
                $dates[] = date('Y-m-d', strtotime("$taun-$bulan-$i"));
            }
        }
        return $this->response->setJSON($dates);
    }

    public function gantiPassword()
    {
        if (!$this->inGroup('siswa')) {
            return $this->respond(false, 'Akses ditolak');
        }

        $oldPassword = $this->request->getPost('old_password');
        $newPassword = $this->request->getPost('new_password');
        $username = session()->get('username');

        $db = \Config\Database::connect();

        // Get current siswa
        $siswa = $db->table('master_siswa')
            ->where('username', $username)
            ->get()
            ->getRow();

        if (!$siswa) {
            return $this->respond(false, 'Siswa tidak ditemukan');
        }

        // Verify old password
        if (!password_verify($oldPassword, $siswa->password ?? '')) {
            return $this->respond(false, 'Password lama salah');
        }

        // Update password
        $result = $db->table('master_siswa')
            ->where('id_siswa', $siswa->id_siswa)
            ->update(['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);

        return $result
            ? $this->respond(true, 'Password berhasil diubah')
            : $this->respond(false, 'Gagal mengubah password');
    }

    /**
     * Halaman Nilai Siswa
     */

    /**
     * Halaman Absensi Siswa
     */

    /**
     * Helper: Get kehadiran sebulan
     */
}
