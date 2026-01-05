<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminSekolah extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->auth->is_admin()) {
            show_error('Hanya Administrator (Pusat) yang dapat mengakses halaman ini.', 403, 'Akses Dilarang');
        }

        $this->load->model('Dashboard_model', 'dashboard');
    }

    public function index()
    {
        $user = $this->auth->user();

        // Check if sync_schools table exists
        if (!$this->db->table_exists('sync_schools')) {
            // Auto create if not exists (Basic schema)
            $this->db->query("CREATE TABLE IF NOT EXISTS `sync_schools` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `school_uuid` varchar(50) NOT NULL,
              `npsn` varchar(20) DEFAULT NULL,
              `nama_sekolah` varchar(100) NOT NULL,
              `alamat` text,
              `kota` varchar(50) DEFAULT NULL,
              `api_key` varchar(100) NOT NULL,
              `is_active` tinyint(1) DEFAULT '1',
              `last_sync` datetime DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `school_uuid` (`school_uuid`),
              UNIQUE KEY `api_key` (`api_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        $this->db->order_by('nama_sekolah', 'ASC');
        $schools = $this->db->get('sync_schools')->result();

        $data = [
            'user' => $user,
            'judul' => 'Manajemen Sekolah',
            'subjudul' => 'Daftar Sekolah & Impersonate',
            'setting' => $this->dashboard->getSetting(),
            'schools' => $schools
        ];

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('admin/sync/schools', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    public function impersonate($id_or_uuid)
    {
        // Try UUID first
        $this->db->where('school_uuid', $id_or_uuid);
        $school = $this->db->get('sync_schools')->row();

        // Fallback ID
        if (!$school) {
            $this->db->where('id', $id_or_uuid);
            $school = $this->db->get('sync_schools')->row();
        }

        if ($school) {
            $this->session->set_userdata('school_uuid', $school->school_uuid);
            $this->session->set_userdata('school_name', $school->nama_sekolah);
            $this->session->set_flashdata('message', 'Anda sekarang login sebagai: ' . $school->nama_sekolah);
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Sekolah tidak ditemukan');
            redirect('adminsekolah');
        }
    }

    public function stop_impersonate()
    {
        $this->session->unset_userdata('school_uuid');
        $this->session->unset_userdata('school_name');
        $this->session->set_flashdata('message', 'Kembali ke mode Admin Pusat');
        redirect('adminsekolah');
    }

    // Helper to add school manually if needed
    public function create()
    {
        if ($this->input->post()) {
            $data = [
                'school_uuid' => uniqid(),
                'npsn' => $this->input->post('npsn', true),
                'nama_sekolah' => $this->input->post('nama_sekolah', true),
                'kota' => $this->input->post('kota', true),
                'api_key' => bin2hex(random_bytes(16)),
                'is_active' => 1
            ];
            $this->db->insert('sync_schools', $data);
            $this->session->set_flashdata('message', 'Sekolah berhasil ditambahkan');
            redirect('adminsekolah');
        }
    }
}
