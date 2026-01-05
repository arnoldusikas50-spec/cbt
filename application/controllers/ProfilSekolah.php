<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProfilSekolah extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        // Verify access: usually only for school admins, but checking logged_in + checking logic in logic is enough
        $this->load->library('upload');
        $this->load->helper('directory');
    }

    public function index()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $user = $this->auth->user();

        // Ensure sync_school_uuid column exists for multi-tenancy
        if (!$this->db->field_exists('sync_school_uuid', 'setting')) {
            $this->db->query("ALTER TABLE setting ADD COLUMN sync_school_uuid VARCHAR(50) DEFAULT NULL");
        }

        // Ensure only school admins access (has school_uuid)
        $school_uuid = $this->session->userdata('school_uuid');
        if (!$school_uuid) {
            show_error('Hanya Admin Sekolah yang dapat mengakses halaman ini.', 403, 'Akses Dilarang');
            return;
        }

        $data = [
            'user' => $user,
            'judul' => 'Profile Sekolah',
            'subjudul' => 'Pengaturan Data Sekolah',
            'profile' => null,
            'setting' => $this->dashboard->getSetting() // Now returns specific school setting
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        $this->render_view(1, 'members/sekolah/profil', $data);
    }

    function uploadFile($logo)
    {
        if (isset($_FILES["logo"]["name"])) {
            $config['upload_path'] = './uploads/settings/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
            $config['overwrite'] = true;
            // Add UUID suffix to avoid filename collision between schools
            $config['file_name'] = $logo . '_' . $this->session->userdata('school_uuid');

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('logo')) {
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            } else {
                $result = $this->upload->data();
                $data['src'] = base_url() . 'uploads/settings/' . $result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME); // This returns name without extension
                $data['relative_path'] = 'uploads/settings/' . $result['file_name']; // Send relative path for saving to DB
                $data['status'] = true;
            }
            $data['type'] = $_FILES['logo']['type'];
            $data['size'] = $_FILES['logo']['size'];
        } else {
            $data['src'] = '';
        }
        $this->output_json($data);
    }

    function deleteFile()
    {
        $src = $this->input->post('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        if (file_exists($file_name) && unlink($file_name)) {
            echo 'File Delete Successfully';
        } else {
            echo 'File not found or error deleting';
        }
    }

    private function normalizeMediaPath($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        $normalized = str_replace(base_url(), '', $path);
        return ltrim($normalized, '/');
    }

    public function saveSetting()
    {
        $school_uuid = $this->session->userdata('school_uuid');
        if (!$school_uuid) {
            $this->output_json(['status' => false, 'message' => 'Akses Sekolah tidak valid']);
            return;
        }

        $sekolah = $this->input->post('nama_sekolah', true);
        $nss = $this->input->post('nss', true);
        $npsn = $this->input->post('npsn', true);
        $jenjang = $this->input->post('jenjang', true);
        $satuan_pendidikan = $this->input->post('satuan_pendidikan', true);

        $alamat = $this->input->post('alamat', true);
        $desa = $this->input->post('desa', true);
        $kec = $this->input->post('kec', true);
        $kota = $this->input->post('kota', true);
        $prov = $this->input->post('provinsi', true);
        $kodepos = $this->input->post('kode_pos', true);
        $telp = $this->input->post('tlp', true);

        $web = $this->input->post('web', true);
        $fax = $this->input->post('fax', true);
        $email = $this->input->post('email', true);

        $kepsek = $this->input->post('kepsek', true);
        $nip = $this->input->post('nip', true);
        $proktor = $this->input->post('proktor', true);
        $nip_proktor = $this->input->post('nip_proktor', true);

        $ba_aktif = $this->input->post('ba_aktif', true);
        $ba_waktu_input = $this->input->post('ba_waktu', true);
        $tkn_siswa = $this->input->post('tkn_siswa', true);
        $mode_app = $this->input->post('mode_app', true);

        $nama_aplikasi = $this->input->post('nama_aplikasi', true);
        $tanda_tangan = $this->normalizeMediaPath($this->input->post('tanda_tangan', true));
        $logo_kanan = $this->normalizeMediaPath($this->input->post('logo_kanan', true));
        $logo_kiri = $this->normalizeMediaPath($this->input->post('logo_kiri', true));
        $stampel = $this->normalizeMediaPath($this->input->post('stampel', true));
        $ttdproktor = $this->normalizeMediaPath($this->input->post('ttdproktor', true));
        $kodescan = $this->normalizeMediaPath($this->input->post('kodescan', true));
        $kopsekolah = $this->normalizeMediaPath($this->input->post('kopsekolah', true));
        $banner1 = $this->normalizeMediaPath($this->input->post('banner1', true));
        $banner2 = $this->normalizeMediaPath($this->input->post('banner2', true));
        $banner3 = $this->normalizeMediaPath($this->input->post('banner3', true));
        $api_key = $this->input->post('api_key', true);

        $ba_waktu = null;
        if (!empty($ba_waktu_input)) {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $ba_waktu_input);
            if ($dt instanceof DateTime) {
                $ba_waktu = $dt->format('Y-m-d H:i:s');
            }
        }

        $telpSanitized = $telp !== null ? preg_replace('/[^0-9]/', '', $telp) : null;
        $kodeposSanitized = $kodepos !== null ? preg_replace('/[^0-9]/', '', $kodepos) : null;

        $insert = [
            'nama_aplikasi' => $nama_aplikasi,
            'logo_kanan' => $logo_kanan,
            'logo_kiri' => $logo_kiri,
            'stampel' => $stampel,
            'tanda_tangan' => $tanda_tangan,
            'ttdproktor' => $ttdproktor,
            'kodescan' => $kodescan,
            'kopsekolah' => $kopsekolah,
            'banner1' => $banner1,
            'banner2' => $banner2,
            'banner3' => $banner3,
            'web' => $web,
            'fax' => $fax,
            'email' => $email,
            'telp' => $telpSanitized,
            'kepsek' => $kepsek,
            'nip' => $nip,
            'proktor' => $proktor,
            'nip_proktor' => $nip_proktor,
            'tkn_siswa' => $tkn_siswa,
            'mode_app' => $mode_app,
            'ba_aktif' => $ba_aktif,
            'ba_waktu' => $ba_waktu,
            'jenjang' => $jenjang,
            'satuan_pendidikan' => $satuan_pendidikan,
            'sekolah' => $sekolah,
            'nss' => $nss,
            'npsn' => $npsn,
            'alamat' => $alamat,
            'desa' => $desa,
            'kecamatan' => $kec,
            'kota' => $kota,
            'provinsi' => $prov,
            'kode_pos' => $kodeposSanitized,
            'api_key' => $api_key
        ];

        $updated = false;
        // Check if setting exists for this school
        if ($this->db->field_exists('sync_school_uuid', 'setting')) {
            $query = $this->db->get_where('setting', ['sync_school_uuid' => $school_uuid]);
            if ($query->num_rows() > 0) {
                // Update
                $this->db->where('sync_school_uuid', $school_uuid);
                $updated = $this->db->update('setting', $insert);
            } else {
                // Insert
                $insert['sync_school_uuid'] = $school_uuid;
                $updated = $this->db->insert('setting', $insert);
            }
        }
        // If field doesn't exist, we can't save safely without affecting others, so maybe fail or log error
        // But assumed it exists as planned.

        $this->output_json(['status' => (bool) $updated]);
    }
}
