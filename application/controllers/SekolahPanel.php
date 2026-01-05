<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SekolahPanel extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }

        // Pastikan hanya admin sekolah yang bisa akses (atau admin pusat yang nyasar)
        // Tapi logic utama adalah memisahkan dashboard mereka.
        $school_uuid = $this->session->userdata('school_uuid');
        if (!$school_uuid) {
            if ($this->auth->is_admin())
                redirect('dashboard');
            else
                redirect('auth');
        }

        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Elearning_model', 'elearning');
        $this->load->model('Dropdown_model', 'dropdown');
    }

    public function index()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();

        // Token & Other Data (Dummy/Safe defaults for School Dashboard)
        $token = new stdClass();
        $token->token = '-';
        // Try getting token if exists
        if (method_exists($this->cbt, 'getToken')) {
            $t = $this->cbt->getToken();
            if ($t)
                $token = $t;
        }

        $box = $this->admin_box($this->dashboard->getSetting(), $tp->id_tp, $smt->id_smt);
        $ujian_box = $this->ujian_box($tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'judul' => 'Dashboard Sekolah',
            'subjudul' => 'Overview',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'info_box' => $box,
            'ujian_box' => $ujian_box, // Corrected key name
            'token' => $token,
            'ada_ujian' => [],
            'jadwal_kbm' => [],
            'kelases' => [],
            'running_text' => $this->dashboard->getRunningText()
        ];

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('members/sekolah/dashboard', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    public function admin_box($setting, $tp, $smt)
    {
        $where = '';
        if ($setting->jenjang == "1") {
            $where = 'jenjang=0 OR jenjang=1';
        } elseif ($setting->jenjang == "2") {
            $where = 'jenjang=2 OR jenjang=1';
        }

        // Note: Dashboard_model->total() already filters by school_uuid from session
        $box = [
            [
                'box' => 'blue',
                'total' => $this->master->getSiswaTotalPage($tp, $smt, '0'),
                'title' => 'Siswa',
                'url' => 'datasiswa',
                'icon' => 'users'
            ],
            [
                'box' => 'cyan',
                'total' => $this->dashboard->total('master_kelas', 'id_tp=' . $tp . ' AND id_smt=' . $smt),
                'title' => 'Rombel',
                'url' => 'datakelas',
                'icon' => 'bell'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboard->total('master_guru'),
                'title' => 'Guru',
                'url' => 'dataguru',
                'icon' => 'user-circle'
            ],
            [
                'box' => 'fuchsia',
                'total' => $this->dashboard->totalWaliKelas($tp, $smt),
                'title' => 'Wali Kelas',
                'url' => 'dataguru',
                'icon' => 'user-o'
            ],
            [
                'box' => 'success',
                'total' => $this->dashboard->total('master_mapel', $where),
                'title' => 'Mapel',
                'url' => 'datamapel',
                'icon' => 'book'
            ],
            [
                'box' => 'info',
                'total' => $this->dashboard->total('master_ekstra'),
                'title' => 'Ekstrakurikuler',
                'url' => 'dataekstra',
                'icon' => 'universal-access'
            ],
        ];
        return json_decode(json_encode($box), FALSE);
    }

    public function ujian_box($tp, $smt)
    {
        // Note: Filters applied in model
        $box = [
            [
                'box' => 'indigo',
                'total' => $this->dashboard->total('cbt_ruang'),
                'title' => 'Ruang',
                'url' => 'cbtruang',
                'icon' => 'school'
            ],
            [
                'box' => 'maroon',
                'total' => $this->dashboard->total('cbt_sesi'),
                'title' => 'Sesi',
                'url' => 'cbtsesi',
                'icon' => 'clock'
            ],
            [
                'box' => 'green',
                'total' => $this->dashboard->total('cbt_bank_soal'),
                'title' => 'Bank Soal',
                'url' => 'cbtbanksoal',
                'icon' => 'folder-open'
            ],
            [
                'box' => 'yellow',
                'total' => $this->dashboard->totalJadwal($tp, $smt),
                'title' => 'Jadwal',
                'url' => 'cbtjadwal',
                'icon' => 'calendar'
            ],
            [
                'box' => 'purple', // was red
                'total' => $this->dashboard->totalPengawas(),
                'title' => 'Pengawas',
                'url' => 'cbtpengawas',
                'icon' => 'briefcase'
            ],
        ];
        return json_decode(json_encode($box), FALSE);
    }
}
