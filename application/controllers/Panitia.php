<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Panitia extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        // Allow ONLY panitia (and admin)
        if (!$this->auth->in_group('panitia') && !$this->auth->is_admin() && $this->session->userdata('role') != '4') {
            redirect('dashboard');
        }

        $this->load->library(['upload', 'form_validation']);
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');
        $this->load->model('Cbt_model', 'cbt');
    }

    public function index()
    {
        $user = $this->auth->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Get Panitia Data
        $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);

        // Helper to check TTD column existence
        if (!$this->db->field_exists('ttd', 'master_panitia')) {
            $this->db->query("ALTER TABLE master_panitia ADD COLUMN ttd VARCHAR(255) DEFAULT NULL AFTER foto");
            // Refresh data
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
        }

        $data = [
            'user' => $user,
            'judul' => 'Profil Panitia',
            'subjudul' => 'Edit Profil',
            'setting' => $this->dashboard->getSetting(),
            'panitia' => $panitia,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'ruangs' => $this->cbt->getAllRuang()
        ];

        $this->render_view(4, 'members/panitia/profile', $data);
    }

    public function save()
    {
        $id_panitia = $this->input->post('id_panitia', true);
        $nama = $this->input->post('nama_panitia', true);
        $nip = $this->input->post('nip', true);
        $email = $this->input->post('email', true);
        $no_hp = $this->input->post('no_hp', true);
        $instansi = $this->input->post('instansi', true);
        $ruang = $this->input->post('ruang', true);
        $jenis_kelamin = $this->input->post('jenis_kelamin', true);
        $agama = $this->input->post('agama', true);
        $api_key = $this->input->post('api_key', true);
        $password = $this->input->post('password');

        $this->form_validation->set_rules('nama_panitia', 'Nama Panitia', 'required|trim');
        $this->form_validation->set_rules('nip', 'NIP', 'trim');

        if ($this->form_validation->run() === FALSE) {
            $this->output_json([
                'status' => false,
                'errors' => [
                    'nama_panitia' => form_error('nama_panitia'),
                ]
            ]);
            return;
        }

        // Auto create missing columns if needed
        if (!$this->db->field_exists('jenis_kelamin', 'master_panitia')) {
            $this->db->query("ALTER TABLE master_panitia ADD COLUMN jenis_kelamin VARCHAR(2) DEFAULT NULL");
        }
        if (!$this->db->field_exists('agama', 'master_panitia')) {
            $this->db->query("ALTER TABLE master_panitia ADD COLUMN agama VARCHAR(50) DEFAULT NULL");
        }
        if (!$this->db->field_exists('api_key', 'master_panitia')) {
            $this->db->query("ALTER TABLE master_panitia ADD COLUMN api_key TEXT DEFAULT NULL");
        }

        $data_update = [
            'nama_panitia' => $nama,
            'nip' => $nip,
            'email' => $email,
            'no_hp' => $no_hp,
            'instansi' => $instansi,
            'ruang' => $ruang,
            'jenis_kelamin' => $jenis_kelamin,
            'agama' => $agama,
            'api_key' => $api_key
        ];

        // Update basic info
        $this->db->where('id_panitia', $id_panitia);
        $updated = $this->db->update('master_panitia', $data_update);

        // Update User info (username/password) if changed
        $panitia = $this->dashboard->getDataPanitia($id_panitia);
        $user_update = [
            'first_name' => $nama
        ];

        if (!empty($password)) {
            $user_update['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->db->where('id', $panitia->id_user);
        $this->db->update('users', $user_update);

        $this->logging->saveLog(4, 'memperbarui profil panitia');

        $this->output_json([
            'status' => true,
            'message' => 'Profil berhasil diperbarui'
        ]);
    }

    public function upload_foto()
    {
        $id_panitia = $this->input->post('id_panitia');
        $field = $this->input->post('field'); // 'foto' or 'ttd'

        if (!in_array($field, ['foto', 'ttd'])) {
            $this->output_json(['status' => false, 'message' => 'Invalid field']);
            return;
        }

        $config['upload_path'] = './uploads/foto_panitia/';
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        if ($this->upload->do_upload('file')) {
            $upload_data = $this->upload->data();
            $file_path = 'uploads/foto_panitia/' . $upload_data['file_name'];

            // Delete old file if exists
            $panitia = $this->dashboard->getDataPanitia($id_panitia);
            if ($panitia && !empty($panitia->$field) && file_exists($panitia->$field)) {
                unlink($panitia->$field);
            }

            $this->db->where('id_panitia', $id_panitia);
            $this->db->update('master_panitia', [$field => $file_path]);

            // Sync foto to users table if field is foto
            if ($field === 'foto' && $panitia->id_user) {
                $this->db->where('id', $panitia->id_user);
                $this->db->update('users', ['foto' => $file_path]);
            }

            $this->output_json([
                'status' => true,
                'src' => base_url() . $file_path,
                'message' => 'Upload berhasil'
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => $this->upload->display_errors()
            ]);
        }
    }

    public function save_signature()
    {
        $id_panitia = $this->input->post('id_panitia');
        $image = $this->input->post('image');

        if (empty($image) || empty($id_panitia)) {
            $this->output_json(['status' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        // Remove base64 header using regex to be safe
        $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
        $image = str_replace(' ', '+', $image);
        $data = base64_decode($image);

        // Pastikan nama file .png untuk mendukung transparansi
        $file_name = 'ttd_' . $id_panitia . '_' . time() . '.png';
        $file_path = 'uploads/foto_panitia/' . $file_name;

        if (!is_dir('./uploads/foto_panitia/')) {
            mkdir('./uploads/foto_panitia/', 0777, true);
        }

        if (file_put_contents('./' . $file_path, $data)) {
            // Delete old file
            $panitia = $this->db->get_where('master_panitia', ['id_panitia' => $id_panitia])->row();
            if ($panitia && !empty($panitia->ttd) && file_exists($panitia->ttd)) {
                unlink($panitia->ttd);
            }

            $this->db->where('id_panitia', $id_panitia);
            $this->db->update('master_panitia', ['ttd' => $file_path]);

            $this->output_json([
                'status' => true,
                'src' => base_url() . $file_path,
                'message' => 'Tanda tangan berhasil disimpan'
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal menyimpan file'
            ]);
        }
    }

    public function delete_ttd()
    {
        $id_panitia = $this->input->post('id_panitia');
        $panitia = $this->db->get_where('master_panitia', ['id_panitia' => $id_panitia])->row();

        if ($panitia && !empty($panitia->ttd)) {
            if (file_exists($panitia->ttd)) {
                unlink($panitia->ttd);
            }
            $this->db->where('id_panitia', $id_panitia);
            $this->db->update('master_panitia', ['ttd' => null]);

            $this->output_json(['status' => true, 'message' => 'Tanda tangan berhasil dihapus']);
        } else {
            $this->output_json(['status' => false, 'message' => 'Tanda tangan tidak ditemukan']);
        }
    }

    public function check_username($username, $id)
    {
        $exist = $this->db->get_where('master_panitia', ['username' => $username, 'id_panitia !=' => $id])->num_rows();
        if ($exist > 0) {
            $this->form_validation->set_message('check_username', '{field} sudah digunakan');
            return FALSE;
        }
        return TRUE;
    }
}
