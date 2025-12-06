<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->auth->is_admin() && !$this->auth->in_group('panitia')) {
            show_error('Akses Terlarang', 403);
        }
        $this->load->dbforge();
        if (!$this->db->table_exists('cbt_laporan')) {
            $this->dbforge->add_field([
                'id_laporan' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE],
                'id_user' => ['type' => 'INT', 'constraint' => 11],
                'judul' => ['type' => 'VARCHAR', 'constraint' => 255],
                'keterangan' => ['type' => 'TEXT', 'null' => TRUE],
                'file' => ['type' => 'VARCHAR', 'constraint' => 255],
                'type' => ['type' => 'VARCHAR', 'constraint' => 100],
                'created_at' => ['type' => 'DATETIME'],
            ]);
            $this->dbforge->add_key('id_laporan', TRUE);
            $this->dbforge->create_table('cbt_laporan');
        }
        if (!$this->db->field_exists('jenis', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'jenis' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Lain-Lain']
            ]);
        }
        $this->load->library(['upload']);
    }

    public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Laporan',
            'subjudul' => 'Arsip Laporan',
            'setting' => $this->dashboard->getSetting(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt_active' => $this->dashboard->getSemesterActive(),
        ];
        
        $this->db->select('a.*, b.first_name, b.last_name');
        $this->db->from('cbt_laporan a');
        $this->db->join('users b', 'a.id_user = b.id', 'left');
        $this->db->order_by('created_at', 'DESC');
        $data['laporans'] = $this->db->get()->result();

        $layout = $this->auth->is_admin() ? 1 : 4;
        $this->render_view($layout, 'cbt/laporan/data', $data);
    }

    public function upload() {
        $config['upload_path'] = './uploads/laporan/';
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_laporan')) {
            $error = $this->upload->display_errors('','');
            $this->output_json(['status' => false, 'msg' => $error]);
        } else {
            $fileData = $this->upload->data();
            $data = [
                'id_user' => $this->auth->user()->id,
                'jenis' => $this->input->post('jenis'),
                'judul' => $this->input->post('judul'),
                'keterangan' => $this->input->post('keterangan'),
                'file' => $fileData['file_name'],
                'type' => $fileData['file_type'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('cbt_laporan', $data);
            $this->output_json(['status' => true, 'msg' => 'Laporan berhasil diupload']);
        }
    }

    public function delete() {
        $id = $this->input->post('id_laporan');
        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if ($laporan) {
            $path = './uploads/laporan/' . $laporan->file;
            if (file_exists($path)) unlink($path);
            $this->db->delete('cbt_laporan', ['id_laporan' => $id]);
            $this->output_json(['status' => true, 'msg' => 'Laporan dihapus']);
        } else {
            $this->output_json(['status' => false, 'msg' => 'Laporan tidak ditemukan']);
        }
    }
}
