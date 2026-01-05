<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cbtberitaacara extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        $this->load->model('Cbt_berita_acara_model', 'berita_acara');
        $this->load->model('Cbt_pakta_integritas_model', 'pakta');
        $this->load->model('Cbt_jadwal_model', 'jadwal');
        $this->load->model('Cbt_ruang_model', 'cbt_ruang');
        $this->load->model('Cbt_sesi_model', 'cbt_sesi');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->library('form_validation');
        $this->load->helper('my');
        
        $this->check_tables();
    }

    private function check_tables() {
        $this->load->dbforge();
        
        // Table cbt_berita_acara
        if (!$this->db->table_exists('cbt_berita_acara')) {
            $this->dbforge->add_field(array(
                'id_berita_acara' => array('type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE),
                'id_jadwal' => array('type' => 'INT', 'constraint' => 11),
                'id_ruang' => array('type' => 'INT', 'constraint' => 11),
                'id_sesi' => array('type' => 'INT', 'constraint' => 11),
                'id_pengawas' => array('type' => 'INT', 'constraint' => 11),
                'isi_berita' => array('type' => 'TEXT', 'null' => TRUE),
                'jumlah_peserta_hadir' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
                'jumlah_peserta_tidak_hadir' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
                'peserta_tidak_hadir' => array('type' => 'TEXT', 'null' => TRUE),
                'catatan' => array('type' => 'TEXT', 'null' => TRUE),
                'tanda_tangan_pengawas' => array('type' => 'TEXT', 'null' => TRUE),
                'tanda_tangan_proktor' => array('type' => 'TEXT', 'null' => TRUE),
                'nama_proktor' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
                'nama_pengawas' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
                'nip_pengawas' => array('type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE),
                'nip_proktor' => array('type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE),
                'created_at' => array('type' => 'DATETIME', 'null' => TRUE),
                'updated_at' => array('type' => 'DATETIME', 'null' => TRUE),
            ));
            $this->dbforge->add_key('id_berita_acara', TRUE);
            $this->dbforge->create_table('cbt_berita_acara');
        } else {
            if (!$this->db->field_exists('id_berita_acara', 'cbt_berita_acara')) {
                // Table is missing primary key, recreate it to fix structure
                $this->dbforge->drop_table('cbt_berita_acara', TRUE);
                $this->dbforge->add_field(array(
                    'id_berita_acara' => array('type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE),
                    'id_jadwal' => array('type' => 'INT', 'constraint' => 11),
                    'id_ruang' => array('type' => 'INT', 'constraint' => 11),
                    'id_sesi' => array('type' => 'INT', 'constraint' => 11),
                    'id_pengawas' => array('type' => 'INT', 'constraint' => 11),
                    'isi_berita' => array('type' => 'TEXT', 'null' => TRUE),
                    'jumlah_peserta_hadir' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
                    'jumlah_peserta_tidak_hadir' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
                    'peserta_tidak_hadir' => array('type' => 'TEXT', 'null' => TRUE),
                    'catatan' => array('type' => 'TEXT', 'null' => TRUE),
                    'tanda_tangan_pengawas' => array('type' => 'TEXT', 'null' => TRUE),
                    'tanda_tangan_proktor' => array('type' => 'TEXT', 'null' => TRUE),
                    'nama_proktor' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
                    'nama_pengawas' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
                    'nip_pengawas' => array('type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE),
                    'nip_proktor' => array('type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE),
                    'created_at' => array('type' => 'DATETIME', 'null' => TRUE),
                    'updated_at' => array('type' => 'DATETIME', 'null' => TRUE),
                ));
                $this->dbforge->add_key('id_berita_acara', TRUE);
                $this->dbforge->create_table('cbt_berita_acara');
                return; // Recreated, stop checking
            }
            if (!$this->db->field_exists('id_pengawas', 'cbt_berita_acara')) {
                $this->dbforge->add_column('cbt_berita_acara', array(
                    'id_pengawas' => array('type' => 'INT', 'constraint' => 11, 'after' => 'id_sesi')
                ));
            }
            if (!$this->db->field_exists('isi_berita', 'cbt_berita_acara')) {
                $this->dbforge->add_column('cbt_berita_acara', array(
                    'isi_berita' => array('type' => 'TEXT', 'null' => TRUE, 'after' => 'id_pengawas')
                ));
            }
            if (!$this->db->field_exists('jumlah_peserta_hadir', 'cbt_berita_acara')) {
                $this->dbforge->add_column('cbt_berita_acara', array(
                    'jumlah_peserta_hadir' => array('type' => 'INT', 'constraint' => 11, 'default' => 0, 'after' => 'isi_berita')
                ));
            }
            if (!$this->db->field_exists('jumlah_peserta_tidak_hadir', 'cbt_berita_acara')) {
                $this->dbforge->add_column('cbt_berita_acara', array(
                    'jumlah_peserta_tidak_hadir' => array('type' => 'INT', 'constraint' => 11, 'default' => 0, 'after' => 'jumlah_peserta_hadir')
                ));
            }
            if (!$this->db->field_exists('peserta_tidak_hadir', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'peserta_tidak_hadir' => array('type' => 'TEXT', 'null' => TRUE, 'after' => 'jumlah_peserta_tidak_hadir')
                ));
            }
            if (!$this->db->field_exists('catatan', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'catatan' => array('type' => 'TEXT', 'null' => TRUE, 'after' => 'peserta_tidak_hadir')
                ));
            }
             if (!$this->db->field_exists('tanda_tangan_pengawas', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'tanda_tangan_pengawas' => array('type' => 'TEXT', 'null' => TRUE, 'after' => 'catatan')
                ));
            }
             if (!$this->db->field_exists('tanda_tangan_proktor', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'tanda_tangan_proktor' => array('type' => 'TEXT', 'null' => TRUE, 'after' => 'tanda_tangan_pengawas')
                ));
            }
            if (!$this->db->field_exists('nama_proktor', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'nama_proktor' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'after' => 'tanda_tangan_proktor')
                ));
            }
            if (!$this->db->field_exists('nama_pengawas', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'nama_pengawas' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'after' => 'nama_proktor')
                ));
            }
            if (!$this->db->field_exists('nip_pengawas', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'nip_pengawas' => array('type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'after' => 'nama_pengawas')
                ));
            }
            if (!$this->db->field_exists('nip_proktor', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'nip_proktor' => array('type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'after' => 'nip_pengawas')
                ));
            }
            if (!$this->db->field_exists('created_at', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'created_at' => array('type' => 'DATETIME', 'null' => TRUE, 'after' => 'nip_proktor')
                ));
            }
            if (!$this->db->field_exists('updated_at', 'cbt_berita_acara')) {
                 $this->dbforge->add_column('cbt_berita_acara', array(
                    'updated_at' => array('type' => 'DATETIME', 'null' => TRUE, 'after' => 'created_at')
                ));
            }
        }

        // Table cbt_pakta_integritas
        if (!$this->db->table_exists('cbt_pakta_integritas')) {
            $this->dbforge->add_field(array(
                'id_pakta_integritas' => array('type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE),
                'id_jadwal' => array('type' => 'INT', 'constraint' => 11),
                'id_user' => array('type' => 'INT', 'constraint' => 11),
                'nama' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
                'role' => array('type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Pengawas'),
                'isi_pakta' => array('type' => 'TEXT', 'null' => TRUE),
                'tanda_tangan' => array('type' => 'TEXT', 'null' => TRUE),
                'created_at' => array('type' => 'DATETIME', 'null' => TRUE),
                'updated_at' => array('type' => 'DATETIME', 'null' => TRUE),
            ));
            $this->dbforge->add_key('id_pakta_integritas', TRUE);
            $this->dbforge->create_table('cbt_pakta_integritas');
        } else {
            if (!$this->db->field_exists('id_pakta_integritas', 'cbt_pakta_integritas')) {
                // Table is missing primary key, recreate it to fix structure
                $this->dbforge->drop_table('cbt_pakta_integritas', TRUE);
                $this->dbforge->add_field(array(
                    'id_pakta_integritas' => array('type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE),
                    'id_jadwal' => array('type' => 'INT', 'constraint' => 11),
                    'id_user' => array('type' => 'INT', 'constraint' => 11),
                    'nama' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
                    'role' => array('type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Pengawas'),
                    'isi_pakta' => array('type' => 'TEXT', 'null' => TRUE),
                    'tanda_tangan' => array('type' => 'TEXT', 'null' => TRUE),
                    'created_at' => array('type' => 'DATETIME', 'null' => TRUE),
                    'updated_at' => array('type' => 'DATETIME', 'null' => TRUE),
                ));
                $this->dbforge->add_key('id_pakta_integritas', TRUE);
                $this->dbforge->create_table('cbt_pakta_integritas');
                return; // Recreated, stop checking
            }
            if (!$this->db->field_exists('id_user', 'cbt_pakta_integritas')) {
                $this->dbforge->add_column('cbt_pakta_integritas', array(
                    'id_user' => array('type' => 'INT', 'constraint' => 11, 'after' => 'id_jadwal')
                ));
            }
            if (!$this->db->field_exists('role', 'cbt_pakta_integritas')) {
                $this->dbforge->add_column('cbt_pakta_integritas', array(
                    'role' => array('type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Pengawas', 'after' => 'nama')
                ));
            }
            if (!$this->db->field_exists('nama', 'cbt_pakta_integritas')) {
                $this->dbforge->add_column('cbt_pakta_integritas', array(
                    'nama' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'after' => 'id_user')
                ));
            }
            if (!$this->db->field_exists('isi_pakta', 'cbt_pakta_integritas')) {
                $this->dbforge->add_column('cbt_pakta_integritas', array(
                    'isi_pakta' => array('type' => 'TEXT', 'null' => TRUE, 'after' => 'role')
                ));
            }
            if (!$this->db->field_exists('tanda_tangan', 'cbt_pakta_integritas')) {
                $this->dbforge->add_column('cbt_pakta_integritas', array(
                    'tanda_tangan' => array('type' => 'TEXT', 'null' => TRUE, 'after' => 'isi_pakta')
                ));
            }
            if (!$this->db->field_exists('created_at', 'cbt_pakta_integritas')) {
                $this->dbforge->add_column('cbt_pakta_integritas', array(
                    'created_at' => array('type' => 'DATETIME', 'null' => TRUE, 'after' => 'tanda_tangan')
                ));
            }
            if (!$this->db->field_exists('updated_at', 'cbt_pakta_integritas')) {
                $this->dbforge->add_column('cbt_pakta_integritas', array(
                    'updated_at' => array('type' => 'DATETIME', 'null' => TRUE, 'after' => 'created_at')
                ));
            }
        }
    }

    public function index() {
        $user = $this->auth->user();
        $data = [
			'user' 			=> $user,
			'judul' => 'Berita Acara & Pakta Integritas',
			'subjudul' => 'Administrasi Ujian',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;
        
        $data['ruang'] = $this->cbt_ruang->get_all();
        $data['sesi'] = $this->cbt_sesi->get_all();
        // Get schedules
        $data['jadwal'] = $this->get_jadwal_list(); 
        
        // Get existing berita acara history
        $this->db->select('b.*, j.tgl_mulai, k.bank_kode, r.nama_ruang, s.nama_sesi');
        $this->db->from('cbt_berita_acara b');
        $this->db->join('cbt_jadwal j', 'j.id_jadwal=b.id_jadwal');
        $this->db->join('cbt_bank_soal k', 'k.id_bank=j.id_bank');
        $this->db->join('cbt_ruang r', 'r.id_ruang=b.id_ruang');
        $this->db->join('cbt_sesi s', 's.id_sesi=b.id_sesi');
        $this->db->order_by('b.created_at', 'DESC');
        $data['beritas'] = $this->db->get()->result(); 

        $this->render_view(1, 'cbt/beritaacara/index', $data);
    }
    
    public function create() {
        $id_jadwal = $this->input->get('jadwal');
        $id_ruang = $this->input->get('ruang');
        $id_sesi = $this->input->get('sesi');

        if (!$id_jadwal || !$id_ruang || !$id_sesi) {
            redirect('cbtberitaacara');
        }

        $jadwal = $this->cbt->getJadwalById($id_jadwal);
        // Get existing berita acara
        $berita = $this->berita_acara->where(['id_jadwal' => $id_jadwal, 'id_ruang' => $id_ruang, 'id_sesi' => $id_sesi])->get();

        $data = [
            'user' => $this->auth->user(),
            'judul' => 'Buat Berita Acara',
            'subjudul' => 'Form Berita Acara',
            'jadwal' => $jadwal,
            'ruang' => $this->cbt_ruang->get($id_ruang),
            'sesi' => $this->cbt_sesi->get($id_sesi),
            'berita' => $berita,
            'setting' => $this->dashboard->getSetting()
        ];
        
        // Ambil data siswa di jadwal, ruang, sesi ini untuk hitung hadir/tidak
        // Logic to count students not fully implemented here as it requires complex queries, simplifying for now
        // $data['siswa'] = ... 

        $this->render_view(1, 'cbt/beritaacara/form', $data);
    }

    public function save() {
        $id_jadwal = $this->input->post('id_jadwal');
        $id_ruang = $this->input->post('id_ruang');
        $id_sesi = $this->input->post('id_sesi');
        
        $data = [
            'id_jadwal' => $id_jadwal,
            'id_ruang' => $id_ruang,
            'id_sesi' => $id_sesi,
            'id_pengawas' => $this->auth->user()->id, // Assuming current user is filling it
            'isi_berita' => $this->input->post('isi_berita'),
            'jumlah_peserta_hadir' => $this->input->post('jml_hadir'),
            'jumlah_peserta_tidak_hadir' => $this->input->post('jml_tidak_hadir'),
            'peserta_tidak_hadir' => $this->input->post('peserta_tidak_hadir'),
            'catatan' => $this->input->post('catatan'),
            'nama_proktor' => $this->input->post('nama_proktor'),
            'nama_pengawas' => $this->input->post('nama_pengawas'),
            'nip_proktor' => $this->input->post('nip_proktor'),
            'nip_pengawas' => $this->input->post('nip_pengawas'),
        ];
        
        // Handle signatures (base64)
        $ttd_pengawas = $this->input->post('ttd_pengawas');
        if ($ttd_pengawas) {
            $data['tanda_tangan_pengawas'] = $ttd_pengawas;
        }
        $ttd_proktor = $this->input->post('ttd_proktor');
        if ($ttd_proktor) {
            $data['tanda_tangan_proktor'] = $ttd_proktor;
        }

        // Check exists
        $exist = $this->berita_acara->where(['id_jadwal' => $id_jadwal, 'id_ruang' => $id_ruang, 'id_sesi' => $id_sesi])->get();
        if ($exist) {
            $this->berita_acara->update($data, ['id_berita_acara' => $exist->id_berita_acara]);
            $id = $exist->id_berita_acara;
        } else {
            $id = $this->berita_acara->insert($data);
        }

        if ($id) {
            $this->session->set_flashdata('success', 'Berita Acara berhasil disimpan');
            redirect('cbtberitaacara/cetak/'.$id);
        } else {
             $this->session->set_flashdata('error', 'Gagal menyimpan');
             redirect('cbtberitaacara');
        }
    }

    public function cetak($id) {
        $berita = $this->berita_acara->get($id);
        if (!$berita) show_404();

        $data = [
            'berita' => $berita,
            'jadwal' => $this->cbt->getJadwalById($berita->id_jadwal),
            'ruang' => $this->cbt_ruang->get($berita->id_ruang),
            'sesi' => $this->cbt_sesi->get($berita->id_sesi),
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->master->getTahunActive(),
            'smt' => $this->master->getSemesterActive(),
        ];
        
        $this->load->view('cbt/beritaacara/print', $data);
    }

    // --- Pakta Integritas Methods ---

    public function pakta() {
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Pakta Integritas',
            'subjudul' => 'Pakta Integritas Pengawas/Proktor',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];
        
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        
        $data['jadwal'] = $this->get_jadwal_list();
        // Get existing paktas history for user
        $this->db->select('p.*, j.tgl_mulai, b.bank_kode');
        $this->db->from('cbt_pakta_integritas p');
        $this->db->join('cbt_jadwal j', 'j.id_jadwal=p.id_jadwal');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=j.id_bank');
        $this->db->where('p.id_user', $user->id);
        $this->db->order_by('p.created_at', 'DESC');
        $data['paktas'] = $this->db->get()->result();

        $this->render_view(1, 'cbt/pakta/index', $data);
    }

    public function pakta_create() {
        $id_jadwal = $this->input->get('jadwal');
        if (!$id_jadwal) redirect('cbtberitaacara/pakta');

        $user = $this->auth->user();
        $jadwal = $this->cbt->getJadwalById($id_jadwal);
        
        // Check existing
        $existing = $this->pakta->where(['id_jadwal' => $id_jadwal, 'id_user' => $user->id])->get();

        $data = [
            'user' => $user,
            'judul' => 'Isi Pakta Integritas',
            'subjudul' => 'Form Pakta Integritas',
            'jadwal' => $jadwal,
            'pakta' => $existing,
            'setting' => $this->dashboard->getSetting()
        ];

        $this->render_view(1, 'cbt/pakta/form', $data);
    }

    public function pakta_save() {
        $id_jadwal = $this->input->post('id_jadwal');
        $isi_pakta = $this->input->post('isi_pakta');
        $role = $this->input->post('role');
        $nama = $this->input->post('nama');
        $ttd = $this->input->post('ttd');

        $user = $this->auth->user();
        $data = [
            'id_jadwal' => $id_jadwal,
            'id_user' => $user->id,
            'role' => $role,
            'nama' => $nama,
            'isi_pakta' => $isi_pakta,
        ];
        if ($ttd) {
            $data['tanda_tangan'] = $ttd;
        }

        $exist = $this->pakta->where(['id_jadwal' => $id_jadwal, 'id_user' => $user->id])->get();
        if ($exist) {
            $this->pakta->update($data, ['id_pakta_integritas' => $exist->id_pakta_integritas]);
            $id = $exist->id_pakta_integritas;
        } else {
            $id = $this->pakta->insert($data);
        }

        if ($id) {
            redirect('cbtberitaacara/pakta_cetak/'.$id);
        } else {
            redirect('cbtberitaacara/pakta');
        }
    }

    public function pakta_cetak($id) {
        $pakta = $this->pakta->get($id);
        if (!$pakta) show_404();

        $data = [
            'pakta' => $pakta,
            'jadwal' => $this->cbt->getJadwalById($pakta->id_jadwal),
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->master->getTahunActive(),
            'smt' => $this->master->getSemesterActive(),
        ];
        
        $this->load->view('cbt/pakta/print', $data);
    }
    private function get_jadwal_list() {
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        
        $this->db->select('j.*, b.bank_kode, b.bank_level, b.bank_kelas');
        $this->db->from('cbt_jadwal j');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=j.id_bank');
        $this->db->where('j.id_tp', $tp->id_tp);
        $this->db->where('j.id_smt', $smt->id_smt);
        $this->db->order_by('j.tgl_mulai', 'DESC');
        return $this->db->get()->result();
    }
}
