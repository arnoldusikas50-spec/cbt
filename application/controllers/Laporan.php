<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->auth->is_admin() && !$this->auth->in_group('panitia') && $this->session->userdata('role') != '4') {
            show_error('Akses Terlarang', 403);
        }
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dashboard_model', 'dashboard');
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
        if (!$this->db->field_exists('status', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'status' => ['type' => 'INT', 'constraint' => 1, 'default' => 0, 'comment' => '0=Checking, 1=Approved, 2=Correction']
            ]);
        }
        if (!$this->db->field_exists('tgl_approval', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'tgl_approval' => ['type' => 'DATETIME', 'null' => TRUE]
            ]);
        }
        if (!$this->db->field_exists('approved_by', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'approved_by' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE]
            ]);
        }
        if (!$this->db->field_exists('content', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'content' => ['type' => 'TEXT', 'null' => TRUE, 'comment' => 'JSON data for specific forms']
            ]);
        }
        if (!$this->db->field_exists('file_2', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'file_2' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE]
            ]);
        }
        if (!$this->db->field_exists('file_3', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'file_3' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE]
            ]);
        }
        if (!$this->db->field_exists('alasan_correction', 'cbt_laporan')) {
            $this->dbforge->add_column('cbt_laporan', [
                'alasan_correction' => ['type' => 'TEXT', 'null' => TRUE, 'comment' => 'Reason for rejection/correction']
            ]);
        }
        $this->load->library(['upload']);
    }

    public function index()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt'); // Load CBT model for Mapel
        $user = $this->auth->user();
        $tp_active = $this->dashboard->getTahunActive();
        $smt_active = $this->dashboard->getSemesterActive();

        // Fetch Ruang
        $ruang_list = $this->db->get('cbt_ruang')->result();

        // Fetch Sesi
        $sesi_list = $this->db->get('cbt_sesi')->result();

        // Fetch Jadwal aktif dengan mapel (via cbt_bank_soal)
        $jadwal_list = $this->db->select('c.id_mapel, c.nama_mapel')
            ->from('cbt_jadwal a')
            ->join('cbt_bank_soal b', 'b.id_bank = a.id_bank', 'left')
            ->join('master_mapel c', 'c.id_mapel = b.bank_mapel_id', 'left')
            ->where('a.id_tp', $tp_active->id_tp)
            ->where('a.id_smt', $smt_active->id_smt)
            ->where('a.status', '1')
            ->where('c.id_mapel IS NOT NULL')
            ->group_by('c.id_mapel, c.nama_mapel')
            ->order_by('c.nama_mapel', 'ASC')
            ->get()->result();

        // Fallback: jika tidak ada jadwal aktif, ambil semua mapel
        if (empty($jadwal_list)) {
            $jadwal_list = $this->db->select('id_mapel, nama_mapel')
                ->order_by('nama_mapel', 'ASC')
                ->get('master_mapel')->result();
        }

        $data = [
            'user' => $user,
            'judul' => 'Laporan',
            'subjudul' => 'Arsip Laporan',
            'setting' => $this->dashboard->getSetting(),
            'tp_active' => $tp_active,
            'smt_active' => $smt_active,
            'ruang_list' => $ruang_list,
            'sesi_list' => $sesi_list,
            'jadwal_list' => $jadwal_list,
            'siswa_list' => $this->db->select('id_siswa, nama, username')
                ->order_by('nama', 'ASC')
                ->get('master_siswa')->result() // Fetch Siswa for lookup
        ];

        // Fetch Panitia data
        $panitia = null;
        $is_pengawas = false;
        if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);
            $data['panitia'] = $panitia;
            if ($panitia && $panitia->jabatan === 'Pengawas') {
                $is_pengawas = true;
            }
        }
        $data['is_pengawas'] = $is_pengawas;
        $data['is_admin'] = $this->auth->is_admin();

        // Prepare query
        $this->db->select('a.*, b.first_name, b.last_name, c.first_name as approver_first, c.last_name as approver_last');
        $this->db->from('cbt_laporan a');
        $this->db->join('users b', 'a.id_user = b.id', 'left');
        $this->db->join('users c', 'a.approved_by = c.id', 'left');

        if ($this->auth->is_admin()) {
            $layout = 1;
        } elseif ($panitia) {
            // Limit access for Pengawas
            if ($is_pengawas) {
                $this->db->where('a.id_user', $user->id);
            }
            $layout = 4;
        } else {
            $layout = 1;
        }

        $this->db->order_by('created_at', 'DESC');
        $laporans = $this->db->get()->result();
        $data['laporans'] = $laporans;

        // Hitung ringkasan
        $summary = [
            'total' => count($laporans),
            'jenis' => []
        ];
        foreach ($laporans as $lap) {
            $jns = $lap->jenis ?? 'Lain-Lain';
            if (!isset($summary['jenis'][$jns])) {
                $summary['jenis'][$jns] = 0;
            }
            $summary['jenis'][$jns]++;
        }
        $data['summary'] = $summary;

        // Check if user has Pakta Integritas
        $has_pakta = $this->db->get_where('cbt_laporan', [
            'id_user' => $user->id,
            'jenis' => 'Pakta Integritas'
        ])->row();
        $data['has_pakta_integritas'] = $has_pakta ? true : false;

        $this->render_view($layout, 'cbt/laporan/data', $data);
    }

    public function upload()
    {
        $config['upload_path'] = './uploads/laporan/';
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_laporan')) {
            $error = $this->upload->display_errors('', '');
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
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 0 // Pending
            ];
            $this->db->insert('cbt_laporan', $data);
            $this->output_json(['status' => true, 'msg' => 'Laporan berhasil diupload']);
        }
    }

    public function save_berita_acara()
    {
        $config['upload_path'] = './uploads/laporan/';
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        $files = [];
        $errors = [];

        // Upload 3 photos
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_FILES['foto_' . $i]['name'])) {
                if ($this->upload->do_upload('foto_' . $i)) {
                    $files['file' . ($i == 1 ? '' : '_' . $i)] = $this->upload->data('file_name');
                } else {
                    $errors[] = "Foto $i: " . $this->upload->display_errors('', '');
                }
            }
        }

        if (empty($files) && !empty($_FILES['foto_1']['name']) && !empty($errors)) {
            $this->output_json(['status' => false, 'msg' => implode(', ', $errors)]);
            return;
        }

        // Get siswa tidak hadir details
        $siswa_ids = $this->input->post('siswa_tidak_hadir');
        $siswa_tidak_hadir = [];
        if ($siswa_ids && is_array($siswa_ids)) {
            $siswa_data = $this->db->select('id_siswa, nama, username')
                ->where_in('id_siswa', $siswa_ids)
                ->get('master_siswa')->result();
            foreach ($siswa_data as $s) {
                $siswa_tidak_hadir[] = [
                    'id' => $s->id_siswa,
                    'nama' => $s->nama,
                    'no_peserta' => $s->username
                ];
            }
        }

        // Prepare JSON content
        $content = [
            'mapel' => $this->input->post('mapel'),
            'mapel_nama' => $this->input->post('mapel_nama'),
            'ruang' => $this->input->post('ruang'),
            'ruang_nama' => $this->input->post('ruang_nama'),
            'sesi' => $this->input->post('sesi'),
            'sesi_nama' => $this->input->post('sesi_nama'),
            'waktu_ujian' => $this->input->post('waktu_ujian'),
            'jam_mulai' => $this->input->post('jam_mulai'),
            'jam_selesai' => $this->input->post('jam_selesai'),
            'jml_seharusnya' => $this->input->post('jml_seharusnya'),
            'jml_hadir' => $this->input->post('jml_hadir'),
            'jml_tidak_hadir' => $this->input->post('jml_tidak_hadir'),
            'siswa_tidak_hadir' => $siswa_tidak_hadir, // Array of absent students
            'uraian' => $this->input->post('uraian'),
            'kendala' => $this->input->post('kendala'), // text
            'solusi' => $this->input->post('solusi'), // text
        ];

        $data = [
            'id_user' => $this->auth->user()->id,
            'jenis' => 'Berita Acara',
            'judul' => 'Berita Acara - ' . $this->input->post('mapel_nama'),
            'keterangan' => $this->input->post('uraian') . ' - ' . $this->input->post('kendala') . ' - ' . $this->input->post('solusi'),
            'content' => json_encode($content),
            'file' => $files['file'] ?? null,
            'file_2' => $files['file_2'] ?? null,
            'file_3' => $files['file_3'] ?? null,
            'type' => 'form', // Marker for generic view
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 0
        ];

        $this->db->insert('cbt_laporan', $data);
        $this->output_json(['status' => true, 'msg' => 'Berita Acara berhasil disimpan']);
    }

    public function save_pakta_integritas()
    {
        $user_id = $this->auth->user()->id;

        // Check if user already has a Pakta Integritas
        $existing = $this->db->get_where('cbt_laporan', [
            'id_user' => $user_id,
            'jenis' => 'Pakta Integritas'
        ])->row();

        if ($existing) {
            $this->output_json([
                'status' => false,
                'msg' => 'Anda sudah memiliki Pakta Integritas. Setiap user hanya dapat membuat 1 Pakta Integritas.',
                'existing_id' => $existing->id_laporan
            ]);
            return;
        }

        // Prepare JSON content
        $content = [
            'nama' => $this->input->post('nama'),
            'nip' => $this->input->post('nip'),
            'jabatan' => $this->input->post('jabatan'),
            'instansi' => $this->input->post('instansi'),
            'no_hp' => $this->input->post('no_hp'),
            'email' => $this->input->post('email'),
            'provinsi' => $this->input->post('provinsi'),
            'kota' => $this->input->post('kota'),
            'tempat' => $this->input->post('tempat'),
            'tanggal_pelaksanaan' => $this->input->post('tanggal_pelaksanaan'),
            'tanggal_ttd' => date('d F Y'),
        ];

        $data = [
            'id_user' => $user_id,
            'jenis' => 'Pakta Integritas',
            'judul' => 'Pakta Integritas - ' . $this->input->post('nama') . ' - ' . date('d/m/Y'),
            'keterangan' => 'Pakta Integritas Pelaksanaan Ujian',
            'content' => json_encode($content),
            'type' => 'form',
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 0
        ];

        $this->db->insert('cbt_laporan', $data);
        $this->output_json(['status' => true, 'msg' => 'Pakta Integritas berhasil disimpan']);
    }

    public function cetak_berita_acara($id)
    {
        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            show_error('Laporan tidak ditemukan', 404);
        }

        // Get school settings for header
        $this->load->model('Cbt_model', 'cbt');
        $setting = $this->cbt->getSettingKopBeritaAcara();

        // Get active jenis ujian
        $jenis_ujian = $this->db->select('nama_jenis, kode_jenis')
            ->where('status', '1')
            ->get('cbt_jenis')
            ->row();

        // Get uploader profile (for signature)
        $uploader = null;
        if ($laporan->id_user) {
            // Check if user is panitia
            $uploader = $this->db->select('p.*, u.first_name, u.last_name')
                ->from('master_panitia p')
                ->join('users u', 'u.id = p.id_user', 'left')
                ->where('p.id_user', $laporan->id_user)
                ->get()->row();

            if (!$uploader) {
                // Fallback to guru
                $uploader = $this->db->select('g.*, u.first_name, u.last_name')
                    ->from('master_guru g')
                    ->join('users u', 'u.id = g.id_user', 'left')
                    ->where('g.id_user', $laporan->id_user)
                    ->get()->row();
            }
        }

        $data['laporan'] = $laporan;
        $data['content'] = $laporan->content ? json_decode($laporan->content) : (object) [];
        $data['setting'] = $setting;
        $data['uploader'] = $uploader;
        $data['kop'] = $setting; // Alias for template compatibility
        $data['jenis_ujian'] = $jenis_ujian;

        $this->load->view('cbt/laporan/cetak_berita_acara', $data);
    }

    public function cetak_pakta_integritas($id)
    {
        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            show_404();
            return;
        }

        // Get school settings
        $setting = $this->cbt->getSettingKopBeritaAcara();

        // Get active TP
        $tp_active = $this->dashboard->getTahunActive();

        // Get active jenis ujian
        $jenis_ujian = $this->db->select('nama_jenis, kode_jenis')
            ->where('status', '1')
            ->get('cbt_jenis')->row();

        // Get uploader info
        $uploader = null;
        if ($laporan->id_user) {
            $uploader = $this->db->select('p.*, u.first_name, u.last_name, u.email')
                ->from('master_panitia p')
                ->join('users u', 'u.id = p.id_user', 'left')
                ->where('p.id_user', $laporan->id_user)
                ->get()->row();

            if (!$uploader) {
                $uploader = $this->db->select('g.*, u.first_name, u.last_name, u.email')
                    ->from('master_guru g')
                    ->join('users u', 'u.id = g.id_user', 'left')
                    ->where('g.id_user', $laporan->id_user)
                    ->get()->row();
            }
        }

        $data['laporan'] = $laporan;
        $data['content'] = $laporan->content ? json_decode($laporan->content) : (object) [];
        $data['setting'] = $setting;
        $data['uploader'] = $uploader;
        $data['tp_active'] = $tp_active;
        $data['jenis_ujian'] = $jenis_ujian;

        $this->load->view('cbt/laporan/cetak_pakta_integritas', $data);
    }

    public function delete()
    {
        $id = $this->input->post('id_laporan');

        // Validate ID
        if (empty($id)) {
            $this->output_json([
                'status' => false,
                'msg' => 'ID Laporan tidak valid',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $user = $this->auth->user();

        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            $this->output_json([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Check Permissions
        $can_delete = false;
        if ($this->auth->is_admin()) {
            $can_delete = true;
        } else {
            // Check Panitia role
            $this->load->model('Dashboard_model', 'dashboard');
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && $panitia->jabatan !== 'Pengawas') {
                // Approver/Panitia can delete
                $can_delete = true;
            } elseif ($laporan->id_user == $user->id) {
                // Pengawas can only delete own file IF NOT approved
                if ($laporan->status != 1) { // 1 = Approved
                    $can_delete = true;
                } else {
                    $this->output_json([
                        'status' => false,
                        'msg' => 'Dokumen sudah disetujui, tidak dapat dihapus.',
                        'csrf_token' => $this->security->get_csrf_hash()
                    ]);
                    return;
                }
            }
        }

        if ($can_delete) {
            // Delete associated files
            if (!empty($laporan->file)) {
                $path = './uploads/laporan/' . $laporan->file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            if (!empty($laporan->file_2)) {
                $path = './uploads/laporan/' . $laporan->file_2;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            if (!empty($laporan->file_3)) {
                $path = './uploads/laporan/' . $laporan->file_3;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            // Delete from database
            $this->db->delete('cbt_laporan', ['id_laporan' => $id]);

            $this->output_json([
                'status' => true,
                'msg' => 'Laporan berhasil dihapus',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk menghapus dokumen ini',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        }
    }

    public function approve()
    {
        if (!$this->check_approver_access()) {
            $this->output_json([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk menyetujui dokumen',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $id = $this->input->post('id_laporan');

        // Validate ID
        if (empty($id)) {
            $this->output_json([
                'status' => false,
                'msg' => 'ID Laporan tidak valid',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Check if laporan exists
        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            $this->output_json([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Check if already approved
        if ($laporan->status == 1) {
            $this->output_json([
                'status' => false,
                'msg' => 'Dokumen sudah disetujui sebelumnya',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $data = [
            'status' => 1,
            'approved_by' => $this->auth->user()->id,
            'tgl_approval' => date('Y-m-d H:i:s'),
            'alasan_correction' => null
        ];

        $this->db->where('id_laporan', $id);
        $update_result = $this->db->update('cbt_laporan', $data);

        if ($update_result) {
            $this->output_json([
                'status' => true,
                'msg' => 'Dokumen berhasil disetujui',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'msg' => 'Gagal menyetujui dokumen. Silakan coba lagi.',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        }
    }

    public function reject()
    {
        if (!$this->check_approver_access()) {
            $this->output_json([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk menolak dokumen',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $id = $this->input->post('id_laporan');
        $alasan = $this->input->post('alasan');

        // Validate ID
        if (empty($id)) {
            $this->output_json([
                'status' => false,
                'msg' => 'ID Laporan tidak valid',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        if (empty($alasan)) {
            $this->output_json([
                'status' => false,
                'msg' => 'Alasan perbaikan harus diisi',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        // Check if laporan exists
        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            $this->output_json([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $data = [
            'status' => 2,
            'approved_by' => $this->auth->user()->id,
            'tgl_approval' => date('Y-m-d H:i:s'),
            'alasan_correction' => $alasan
        ];

        $this->db->where('id_laporan', $id);
        $update_result = $this->db->update('cbt_laporan', $data);

        if ($update_result) {
            $this->output_json([
                'status' => true,
                'msg' => 'Dokumen dikembalikan untuk perbaikan',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'msg' => 'Gagal menolak dokumen. Silakan coba lagi.',
                'csrf_token' => $this->security->get_csrf_hash()
            ]);
        }
    }

    /**
     * Get laporan data for editing
     */
    public function get_laporan($id)
    {
        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            $this->output_json([
                'status' => false,
                'msg' => 'Laporan tidak ditemukan'
            ]);
            return;
        }

        // Check permission - user can only edit their own document if not approved
        $user = $this->auth->user();
        $can_edit = false;

        if ($this->auth->is_admin()) {
            $can_edit = true;
        } else {
            $this->load->model('Dashboard_model', 'dashboard');
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && $panitia->jabatan !== 'Pengawas') {
                // Approver/Panitia can edit all non-approved documents
                if ($laporan->status != 1) {
                    $can_edit = true;
                }
            } elseif ($laporan->id_user == $user->id && $laporan->status != 1) {
                // Owner can edit their own non-approved document
                $can_edit = true;
            }
        }

        if (!$can_edit) {
            $this->output_json([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk mengedit dokumen ini atau dokumen sudah disetujui'
            ]);
            return;
        }

        // Parse content if exists
        $content = null;
        if ($laporan->content) {
            $content = json_decode($laporan->content);
        }

        $this->output_json([
            'status' => true,
            'data' => $laporan,
            'content' => $content
        ]);
    }

    /**
     * Update Berita Acara
     */
    public function update_berita_acara()
    {
        $id = $this->input->post('id_laporan');

        if (empty($id)) {
            $this->output_json(['status' => false, 'msg' => 'ID Laporan tidak valid']);
            return;
        }

        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            $this->output_json(['status' => false, 'msg' => 'Laporan tidak ditemukan']);
            return;
        }

        // Check permission
        $user = $this->auth->user();
        $can_edit = false;

        if ($this->auth->is_admin()) {
            $can_edit = true;
        } else {
            $this->load->model('Dashboard_model', 'dashboard');
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && $panitia->jabatan !== 'Pengawas') {
                if ($laporan->status != 1) {
                    $can_edit = true;
                }
            } elseif ($laporan->id_user == $user->id && $laporan->status != 1) {
                $can_edit = true;
            }
        }

        if (!$can_edit) {
            $this->output_json([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk mengedit dokumen ini'
            ]);
            return;
        }

        // Handle file uploads
        $config['upload_path'] = './uploads/laporan/';
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 5120;
        $config['encrypt_name'] = TRUE;

        $this->upload->initialize($config);

        $files = [
            'file' => $laporan->file,
            'file_2' => $laporan->file_2,
            'file_3' => $laporan->file_3
        ];

        // Upload new photos if provided
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_FILES['foto_' . $i]['name'])) {
                if ($this->upload->do_upload('foto_' . $i)) {
                    // Delete old file
                    $old_key = $i == 1 ? 'file' : 'file_' . $i;
                    if (!empty($files[$old_key])) {
                        $old_path = './uploads/laporan/' . $files[$old_key];
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                    $files[$old_key] = $this->upload->data('file_name');
                }
            }
        }

        // Get siswa tidak hadir details
        $siswa_ids = $this->input->post('siswa_tidak_hadir');
        $siswa_tidak_hadir = [];
        if ($siswa_ids && is_array($siswa_ids)) {
            $siswa_data = $this->db->select('id_siswa, nama, username')
                ->where_in('id_siswa', $siswa_ids)
                ->get('master_siswa')->result();
            foreach ($siswa_data as $s) {
                $siswa_tidak_hadir[] = [
                    'id' => $s->id_siswa,
                    'nama' => $s->nama,
                    'no_peserta' => $s->username
                ];
            }
        }

        // Prepare JSON content
        $content = [
            'mapel' => $this->input->post('mapel'),
            'mapel_nama' => $this->input->post('mapel_nama'),
            'ruang' => $this->input->post('ruang'),
            'ruang_nama' => $this->input->post('ruang_nama'),
            'sesi' => $this->input->post('sesi'),
            'sesi_nama' => $this->input->post('sesi_nama'),
            'waktu_ujian' => $this->input->post('waktu_ujian'),
            'jam_mulai' => $this->input->post('jam_mulai'),
            'jam_selesai' => $this->input->post('jam_selesai'),
            'jml_seharusnya' => $this->input->post('jml_seharusnya'),
            'jml_hadir' => $this->input->post('jml_hadir'),
            'jml_tidak_hadir' => $this->input->post('jml_tidak_hadir'),
            'siswa_tidak_hadir' => $siswa_tidak_hadir,
            'uraian' => $this->input->post('uraian'),
            'kendala' => $this->input->post('kendala'),
            'solusi' => $this->input->post('solusi'),
        ];

        $data = [
            'judul' => 'Berita Acara - ' . $this->input->post('mapel_nama'),
            'keterangan' => $this->input->post('uraian') . ' - ' . $this->input->post('kendala') . ' - ' . $this->input->post('solusi'),
            'content' => json_encode($content),
            'file' => $files['file'],
            'file_2' => $files['file_2'],
            'file_3' => $files['file_3'],
            'status' => 0 // Reset to pending after edit
        ];

        $this->db->where('id_laporan', $id);
        $this->db->update('cbt_laporan', $data);

        $this->output_json(['status' => true, 'msg' => 'Berita Acara berhasil diperbarui']);
    }

    /**
     * Update Pakta Integritas
     */
    public function update_pakta_integritas()
    {
        $id = $this->input->post('id_laporan');

        if (empty($id)) {
            $this->output_json(['status' => false, 'msg' => 'ID Laporan tidak valid']);
            return;
        }

        $laporan = $this->db->get_where('cbt_laporan', ['id_laporan' => $id])->row();
        if (!$laporan) {
            $this->output_json(['status' => false, 'msg' => 'Laporan tidak ditemukan']);
            return;
        }

        // Check permission
        $user = $this->auth->user();
        $can_edit = false;

        if ($this->auth->is_admin()) {
            $can_edit = true;
        } else {
            $this->load->model('Dashboard_model', 'dashboard');
            $tp_active = $this->dashboard->getTahunActive();
            $smt_active = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

            if ($panitia && $panitia->jabatan !== 'Pengawas') {
                if ($laporan->status != 1) {
                    $can_edit = true;
                }
            } elseif ($laporan->id_user == $user->id && $laporan->status != 1) {
                $can_edit = true;
            }
        }

        if (!$can_edit) {
            $this->output_json([
                'status' => false,
                'msg' => 'Anda tidak memiliki akses untuk mengedit dokumen ini'
            ]);
            return;
        }

        // Prepare JSON content
        $content = [
            'nama' => $this->input->post('nama'),
            'nip' => $this->input->post('nip'),
            'jabatan' => $this->input->post('jabatan'),
            'instansi' => $this->input->post('instansi'),
            'no_hp' => $this->input->post('no_hp'),
            'email' => $this->input->post('email'),
            'provinsi' => $this->input->post('provinsi'),
            'kota' => $this->input->post('kota'),
            'tempat' => $this->input->post('tempat'),
            'tanggal_pelaksanaan' => $this->input->post('tanggal_pelaksanaan'),
            'tanggal_ttd' => date('d F Y'),
        ];

        $data = [
            'judul' => 'Pakta Integritas - ' . $this->input->post('nama') . ' - ' . date('d/m/Y'),
            'keterangan' => 'Pakta Integritas Pelaksanaan Ujian',
            'content' => json_encode($content),
            'status' => 0 // Reset to pending after edit
        ];

        $this->db->where('id_laporan', $id);
        $this->db->update('cbt_laporan', $data);

        $this->output_json(['status' => true, 'msg' => 'Pakta Integritas berhasil diperbarui']);
    }

    private function check_approver_access()
    {
        $user = $this->auth->user();
        if ($this->auth->is_admin())
            return true;

        $this->load->model('Dashboard_model', 'dashboard');
        $tp_active = $this->dashboard->getTahunActive();
        $smt_active = $this->dashboard->getSemesterActive();
        $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);

        // Only Panitia (Not Pengawas) can approve/reject
        return ($panitia && $panitia->jabatan !== 'Pengawas');
    }
}
