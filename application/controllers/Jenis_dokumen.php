<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis_dokumen extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('panitia')) {
            show_error('Hanya Administrator dan Panitia yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }

        $this->load->model('Jenis_dokumen_model', 'jenis_dokumen');
        $this->load->library(['form_validation', 'upload']);
        $this->load->helper(['form', 'url']);
    }

    public function index()
    {
        $data['title'] = 'Jenis Dokumen';
        $data['subtitle'] = 'Manajemen Jenis Dokumen';
        $data['breadcrumb'] = [
            ['title' => 'Dashboard', 'link' => base_url('dashboard')],
            ['title' => 'Jenis Dokumen', 'link' => base_url('jenis_dokumen')]
        ];

        $this->template->render('jenis_dokumen/index', $data);
    }

    public function data()
    {
        $this->load->library(['datatables', 'datatables_custom']);

        $this->datatables->select('
            id_jenis_dokumen,
            kode_jenis,
            nama_jenis,
            deskripsi,
            template_path,
            is_active,
            created_at,
            updated_at
        ');
        $this->datatables->from('master_jenis_dokumen');

        $this->datatables->add_column('status', function ($row) {
            $status = $row['is_active'] ?
                '<span class="badge badge-success">Aktif</span>' :
                '<span class="badge badge-danger">Tidak Aktif</span>';
            return $status;
        });

        $this->datatables->add_column('action', function ($row) {
            $btn = '';
            if ($this->auth->is_admin() || $this->auth->in_group('panitia')) {
                $btn .= '<button type="button" class="btn btn-sm btn-info btn-edit" data-id="' . $row['id_jenis_dokumen'] . '">
                    <i class="bi bi-pencil"></i>
                </button> ';

                $btn .= '<button type="button" class="btn btn-sm btn-' . ($row['is_active'] ? 'warning' : 'success') . ' btn-toggle" data-id="' . $row['id_jenis_dokumen'] . '">
                    <i class="bi bi-' . ($row['is_active'] ? 'eye-slash' : 'eye') . '"></i>
                </button> ';

                $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row['id_jenis_dokumen'] . '">
                    <i class="bi bi-trash"></i>
                </button>';
            }
            return $btn;
        });

        echo $this->datatables->generate();
    }

    public function add()
    {
        $this->form_validation->set_rules('kode_jenis', 'Kode Jenis', 'required|trim|min_length[2]|max_length[20]|callback_check_kode');
        $this->form_validation->set_rules('nama_jenis', 'Nama Jenis', 'required|trim|min_length[3]|max_length[100]|callback_check_nama');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim|max_length[500]');

        if ($this->form_validation->run() == FALSE) {
            $errors = [
                'kode_jenis' => form_error('kode_jenis'),
                'nama_jenis' => form_error('nama_jenis'),
                'deskripsi' => form_error('deskripsi')
            ];

            $this->output_json([
                'status' => false,
                'errors' => $errors,
                'message' => 'Validasi gagal. Silakan periksa kembali input Anda.'
            ]);
        } else {
            $data = [
                'kode_jenis' => strtoupper($this->input->post('kode_jenis')),
                'nama_jenis' => $this->input->post('nama_jenis'),
                'deskripsi' => $this->input->post('deskripsi'),
                'is_active' => 1,
                'created_by' => $this->session->userdata('user_id')
            ];

            if ($this->jenis_dokumen->insert($data)) {
                $this->output_json([
                    'status' => true,
                    'message' => 'Jenis dokumen berhasil ditambahkan.'
                ]);
            } else {
                $this->output_json([
                    'status' => false,
                    'message' => 'Gagal menambahkan jenis dokumen.'
                ]);
            }
        }
    }

    public function edit($id)
    {
        $jenis_dokumen = $this->jenis_dokumen->get_by_id($id);

        if (!$jenis_dokumen) {
            $this->output_json([
                'status' => false,
                'message' => 'Jenis dokumen tidak ditemukan.'
            ]);
            return;
        }

        $this->output_json([
            'status' => true,
            'data' => $jenis_dokumen
        ]);
    }

    public function update()
    {
        $id = $this->input->post('id_jenis_dokumen');

        $this->form_validation->set_rules('kode_jenis', 'Kode Jenis', 'required|trim|min_length[2]|max_length[20]|callback_check_kode_edit[' . $id . ']');
        $this->form_validation->set_rules('nama_jenis', 'Nama Jenis', 'required|trim|min_length[3]|max_length[100]|callback_check_nama_edit[' . $id . ']');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim|max_length[500]');

        if ($this->form_validation->run() == FALSE) {
            $errors = [
                'kode_jenis' => form_error('kode_jenis'),
                'nama_jenis' => form_error('nama_jenis'),
                'deskripsi' => form_error('deskripsi')
            ];

            $this->output_json([
                'status' => false,
                'errors' => $errors,
                'message' => 'Validasi gagal. Silakan periksa kembali input Anda.'
            ]);
        } else {
            $data = [
                'kode_jenis' => strtoupper($this->input->post('kode_jenis')),
                'nama_jenis' => $this->input->post('nama_jenis'),
                'deskripsi' => $this->input->post('deskripsi'),
                'updated_by' => $this->session->userdata('user_id')
            ];

            if ($this->jenis_dokumen->update($id, $data)) {
                $this->output_json([
                    'status' => true,
                    'message' => 'Jenis dokumen berhasil diperbarui.'
                ]);
            } else {
                $this->output_json([
                    'status' => false,
                    'message' => 'Gagal memperbarui jenis dokumen.'
                ]);
            }
        }
    }

    public function delete($id)
    {
        $jenis_dokumen = $this->jenis_dokumen->get_by_id($id);

        if (!$jenis_dokumen) {
            $this->output_json([
                'status' => false,
                'message' => 'Jenis dokumen tidak ditemukan.'
            ]);
            return;
        }

        if ($this->jenis_dokumen->delete($id)) {
            $this->output_json([
                'status' => true,
                'message' => 'Jenis dokumen berhasil dihapus.'
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal menghapus jenis dokumen.'
            ]);
        }
    }

    public function toggle_status($id)
    {
        $jenis_dokumen = $this->jenis_dokumen->get_by_id($id);

        if (!$jenis_dokumen) {
            $this->output_json([
                'status' => false,
                'message' => 'Jenis dokumen tidak ditemukan.'
            ]);
            return;
        }

        if ($this->jenis_dokumen->toggle_status($id)) {
            $status_text = $jenis_dokumen->is_active ? 'dinonaktifkan' : 'diaktifkan';
            $this->output_json([
                'status' => true,
                'message' => 'Jenis dokumen berhasil ' . $status_text . '.'
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal mengubah status jenis dokumen.'
            ]);
        }
    }

    // Form validation callbacks
    public function check_kode($kode)
    {
        if ($this->jenis_dokumen->check_kode_exists($kode)) {
            $this->form_validation->set_message('check_kode', 'Kode jenis sudah digunakan.');
            return FALSE;
        }
        return TRUE;
    }

    public function check_nama($nama)
    {
        if ($this->jenis_dokumen->check_nama_exists($nama)) {
            $this->form_validation->set_message('check_nama', 'Nama jenis sudah digunakan.');
            return FALSE;
        }
        return TRUE;
    }

    public function check_kode_edit($kode, $id)
    {
        if ($this->jenis_dokumen->check_kode_exists($kode, $id)) {
            $this->form_validation->set_message('check_kode_edit', 'Kode jenis sudah digunakan.');
            return FALSE;
        }
        return TRUE;
    }

    public function check_nama_edit($nama, $id)
    {
        if ($this->jenis_dokumen->check_nama_exists($nama, $id)) {
            $this->form_validation->set_message('check_nama_edit', 'Nama jenis sudah digunakan.');
            return FALSE;
        }
        return TRUE;
    }
}
