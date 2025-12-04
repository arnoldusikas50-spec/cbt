<?php


class Siswa extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        $this->load->library('upload');
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->library('user_agent');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function index()
    {
    }

    public function getPost()
    {
        $this->load->model('Post_model', 'post');
        $kode = $this->input->get('kelas', true);
        //$this->input->get()->getPostForUser("'%siswa%'")
        $post = $this->post->getPostForUser("'%siswa%'", "'%" . $kode . "%'");
        $this->output_json($post);
    }

    public function getComment($id_post, $page)
    {
        $perPage = 5;
        $offset = $page * $perPage;
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml');
        $this->db->from('post_comments a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_post', $id_post);
        $this->db->limit($perPage, $offset);
        $comment = $this->db->get()->result();

        $this->output_json($comment);
    }

    public function getReplies($id_comment, $page)
    {
        $perPage = 5;
        $offset = $page * $perPage;
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa');
        $this->db->from('post_reply a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_comment', $id_comment);
        $this->db->limit($perPage, $offset);
        $replies = $this->db->get()->result();

        $this->output_json($replies);
    }

    public function saveKomentar()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $dari = $siswa->id_siswa;
        $dari_group = 3;
        $data = [
            'type' => '1',
            'id_post' => $this->input->post('id_post'),
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $this->input->post('text'),
        ];

        $insert = $this->db->replace('post_comments', $data);

        $id = $this->db->insert_id();
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml');
        $this->db->from('post_comments a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_comment', $id);
        $comment = $this->db->get()->result();

        $this->output_json($comment);
    }

    public function saveBalasan()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Post_model', 'post');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $dari = $siswa->id_siswa;
        $dari_group = 3;
        $data = [
            'id_comment' => $this->input->post('id_comment'),
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $this->input->post('text'),
        ];

        $insert = $this->db->replace('post_reply', $data);

        $id = $this->db->insert_id();
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa');
        $this->db->from('post_reply a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_reply', $id);
        $replies = $this->db->get()->result();

        $this->output_json($replies);
    }

    public function seminggu()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');

        $id_siswa = $this->input->get('id_siswa', true);
        $id_kelas = $this->input->get('id_kelas', true);
        $tgl = $this->input->get('tgl', true);
        $jenis = $this->input->get('jenis', true);

        $mapels = $this->dropdown->getAllMapel();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $today = date($tgl);
        $numday = date('N', strtotime($tgl));
        $jadwal = $this->kelas->loadJadwalSiswaHariIni($tp->id_tp, $smt->id_smt, $id_kelas, $numday);

        $materi_hari_ini = $this->kelas->getMateriSiswa($id_kelas, $today, $jenis);
        $materi = [];
        foreach ($jadwal as $key => $value) {
            $materi['materi'][$key] = $materi_hari_ini[$key] ?? [
                'id_mapel' => $value->id_mapel,
                'id_jadwal' => $value->id_jadwal,
                'nama_mapel' => $mapels[$value->id_mapel] ?? ""
            ];
        };

        $arrIdKjm = [];
        foreach ($materi['materi'] as $mtr) {
            if (isset($mtr->id_kjm)) $arrIdKjm[] = $mtr->id_kjm;
        }

        if (count($arrIdKjm) > 0)
            $materi['logs'] = (array)$this->kelas->getStatusMateriSiswaByJadwal($id_siswa, $arrIdKjm);

        $materi['jadwal'] = $jadwal;

        $jadk = $this->kelas->getJadwalKbm($tp->id_tp, $smt->id_smt, $id_kelas);
        $jadk->istirahat = $this->maybe_unserialize($jadk->istirahat ?? '');
        $materi['kbm'] = $jadk;
        $materi['seminggu'] = $this->kelas->loadJadwalSiswaSeminggu($tp->id_tp, $smt->id_smt, $id_kelas);

        $this->output_json($materi);
    }

    function uploadFile()
    {
        $max_size = $this->input->post('max-size', true);
        if (isset($_FILES["file_uploads"]["name"])) {
            $config['upload_path'] = './uploads/file_siswa/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4|avi|doc|docx|xls|xlsx|ppt|pptx|csv|pdf|rtf|txt';
            //$config['encrypt_name'] = TRUE;
            $config['max_size'] = $max_size;
            $config['overwrite'] = FALSE;

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file_uploads')) {
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            } else {
                $result = $this->upload->data();
                $data['src'] = 'uploads/file_siswa/' . $result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;
            }

            $data['type'] = $_FILES['file_uploads']['type'];
            $data['size'] = $_FILES['file_uploads']['size'];
        }
        $this->output_json($data);
    }

    function deleteFile()
    {
        $src = $this->input->post('src');
        //$file_name = str_replace(base_url(), '', $src);
        if (unlink($src)) {
            echo 'File Delete Successfully';
        }
    }

    public function perpus()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul'     => 'Perpustakaan Digital',
            'subjudul'  => 'Daftar Perpustakaan',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $pustaka = $this->db->get('kelas_libraries')->result();
        $data['pustaka'] = $pustaka;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/elearning/perpus', $data);
    }

    public function catatan()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Catatan',
            'subjudul' => 'Catatan Dari Guru',
            'setting' => $this->dashboard->getSetting()
        ];

        $catatan_mapel = $this->kelas->getCatatanMapelBySiswa($siswa->id_kelas, $tp->id_tp, $smt->id_smt);
        $catatan = [];
        foreach ($catatan_mapel as $cat) {
            if (($cat->type == '2' && $cat->id_siswa == $siswa->id_siswa) || ($cat->type == '1' && $cat->id_kelas == $siswa->id_kelas)) {
                $catatan[] = [
                    'id_catatan' => $cat->id_catatan,
                    'nama_guru' => $cat->nama_guru,
                    'foto_guru' => $cat->foto && file_exists($cat->foto) ? $cat->foto : 'uploads/profile_guru/'.$cat->nip. (file_exists('uploads/profile_guru/'.$cat->nip.'.jpg') ? '.jpg':'.png'),
                    'id_siswa' => $siswa->id_siswa,
                    'tgl' => $cat->tgl,
                    'table' => 'mapel',
                    'level' => $cat->level,
                    'type' => $cat->type,
                    'readed' => $cat->readed,
                    'reading' => $this->maybe_unserialize($cat->reading ?? '')
                ];
            }
        }

        $catatan_siswa = $this->kelas->getCatatanSiswaBySiswa($siswa->id_kelas, $tp->id_tp, $smt->id_smt);
        foreach ($catatan_siswa as $cat) {
            if (($cat->type == '2' && $cat->id_siswa == $siswa->id_siswa) || ($cat->type == '1' && $cat->id_kelas == $siswa->id_kelas)) {
                $catatan[] = [
                    'id_catatan' => $cat->id_catatan,
                    'nama_guru' => $cat->nama_guru,
                    'foto_guru' => $cat->foto && file_exists($cat->foto) ? $cat->foto : 'uploads/profile_guru/'.$cat->nip. (file_exists('uploads/profile_guru/'.$cat->nip.'.jpg') ? '.jpg':'.png'),
                    'id_siswa' => $siswa->id_siswa,
                    'tgl' => $cat->tgl,
                    'table' => 'wali',
                    'level' => $cat->level,
                    'readed' => $cat->readed,
                    'type' => $cat->type,
                    'reading' => $this->maybe_unserialize($cat->reading ?? '')
                ];
            }
        }

        rsort($catatan);
        $data['catatan'] = (array)json_decode(json_encode($catatan));
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/catatan/data', $data);
    }

    public function detailCatatan($table, $id_catatan)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        if ($siswa && $table == 'mapel') {
            $detail = $this->kelas->getCatatanMapelSiswaDetail($id_catatan);
        } else {
            $detail = $this->kelas->getCatatanKelasSiswaDetail($id_catatan);
        }
        $reading = [];
        if ($detail) {
            $detail->id_siswa = $siswa->id_siswa;
            $reading = $detail->reading != null ? $this->maybe_unserialize($detail->reading ?? '') : [];
        }
        $this->output_json(['reading' => $reading, 'detail' => $detail]);
    }

    public function readed($table, $id_catatan)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if ($table == 'mapel') {
            $tbl = 'kelas_catatan_mapel';
        } else {
            $tbl = 'kelas_catatan_wali';
        }
        $cat = $this->kelas->getReading($tbl, $id_catatan);
        $readed = $cat->readed == '0' ? date('Y-m-d H:i:s') : '0';
        if ($cat->type == '1') {
            $reading = $this->maybe_unserialize($cat->reading ?? '');
            if (!in_array($siswa->id_siswa, $reading)) {
                $reading[] = $siswa->id_siswa;
            }
            $this->db->set('reading', json_encode($reading));
        } else {
            $this->db->set('readed', $readed);
        }
        $this->db->where('id_catatan', $id_catatan);
        $update = $this->db->update($tbl);

        $this->output_json($update);
    }

    public function getTimer($id_siswa, $id_jadwal)
    {
        $this->load->model('Cbt_model', 'cbt');
        $data['durasi'] = $this->cbt->getDurasiSiswa($id_siswa .'0'. $id_jadwal);
        $this->output_json($data);
    }

    function total_hari($id_day, $bulan, $taun) {
        $days = 0;
        $dates = [];
        ///$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $taun);
        $total_days = date('t', strtotime("$taun-$bulan-01"));
        $idday = $id_day == '7' ? 0 : $id_day;
        for($i=1;$i<$total_days;$i++) {
            if (date('N', strtotime($taun.'-'.$bulan.'-'.$i)) == $idday) {
                $days ++;
                $dates[] = date('Y-m-d', strtotime($taun . '-' . $bulan . '-' . $i));
            }
        }
        return $dates;//array('days' =>$days, 'dates'=>$dates);
    }
}
