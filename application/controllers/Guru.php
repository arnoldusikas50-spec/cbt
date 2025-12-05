<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Guru extends MY_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->auth->logged_in()){
			redirect('auth');
		}
		$this->load->library('upload');
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('', '');

		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Master_model', 'master');

        // Check if ttd column exists and add it if missing
        if (!$this->db->field_exists('ttd', 'master_guru')) {
            $this->load->dbforge();
            $this->dbforge->add_column('master_guru', [
                'ttd' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE, 'default' => NULL, 'after' => 'foto']
            ]);
        }
	}

    /**
     * @throws JsonException
     */
    public function index() {
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
		$user = $this->auth->user();
        $guru = $this->dashboard->getDetailGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        //$guru->dokumen = json_decode($guru->dokumen);
        if ($user == null) {
            redirect('auth');
        }

		$data = [
			'user' 		=> $user,
			'judul'	=> 'Profile',
			'subjudul'=> 'Profile Saya',
			'setting'		=> $this->dashboard->getSetting()
		];

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;
		$data['guru'] = $guru;

		$inputsProfile = [
			[
				'label' 	=> 'Nama Lengkap',
				'name'		=> 'nama_guru',
				'value'		=> $guru->nama_guru ?? '',
				'icon'		=> 'far fa-user',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Email',
				'name'		=> 'email',
				'value'		=> $guru->email ?? '',
				'icon'		=> 'far fa-envelope',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'NIP / NUPTK',
				'name'		=> 'nip',
				'value'		=> $guru->nip ?? '',
				'icon'		=> 'far fa-id-card',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Jenis Kelamin',
				'name'		=> 'jenis_kelamin',
				'value'		=> $guru->jenis_kelamin ?? '',
				'icon'		=> 'fas fa-venus-mars',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'No. Handphone',
				'name'		=> 'no_hp',
				'value'		=> $guru->no_hp ?? '',
				'icon'		=> 'fa fa-phone',
				'type'		=> 'number'
			],
			[
				'label' 	=> 'Agama',
				'name'		=> 'agama',
				'value'		=> $guru->agama ?? '',
				'icon'		=> 'far fa-user',
				'type'		=> 'text'
			],
		];
		$inputsAlamat = [
			[
				'label' 	=> 'NIK',
				'name'		=> 'no_ktp',
				'value'		=> $guru->no_ktp ?? '',
				'icon'		=> 'far fa-id-card',
				'type'		=> 'number'
			],
			[
				'label' 	=> 'Tempat Lahir',
				'name'		=> 'tempat_lahir',
				'value'		=> $guru->tempat_lahir ?? '',
				'icon'		=> 'fa fa-map-marker',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Tgl. Lahir',
				'name'		=> 'tgl_lahir',
				'value'		=> $guru->tgl_lahir ?? '',
				'icon'		=> 'fa fa-calendar',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Alamat',
				'name'		=> 'alamat_jalan',
				'value'		=> $guru->alamat_jalan ?? '',
				'icon'		=> 'fa fa-map-marker',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Kecamatan',
				'name'		=> 'kecamatan',
				'value'		=> $guru->kecamatan ?? '',
				'icon'		=> 'fa fa-map-marker',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Kota/Kab.',
				'name'		=> 'kabupaten',
				'value'		=> $guru->kabupaten ?? '',
				'icon'		=> 'fa fa-map-marker',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Provinsi',
				'name'		=> 'provinsi',
				'value'		=> $guru->provinsi ?? '',
				'icon'		=> 'fa fa-map-marker',
				'type'		=> 'text'
			],
			[
				'label' 	=> 'Kode Pos',
				'name'		=> 'kode_pos',
				'value'		=> $guru->kode_pos ?? '',
				'icon'		=> 'fa fa-envelope',
				'type'		=> 'number'
			],
		];

        $inputsDokumen = [
            [
                'label' 	=> 'KTP',
                'id'		=> 'ktp_id',
                'name'		=> 'ktp_id',
                'value'		=> '',
                'type'		=> 'file'
            ],
            [
                'label' 	=> 'KK',
                'id'		=> 'kk_id',
                'name'		=> 'kk_id',
                'value'		=> '',
                'type'		=> 'file'
            ],
            [
                'label' 	=> 'Sertifikat',
                'id'		=> 'sertifikat',
                'name'		=> 'sertifikat[]',
                'value'		=> '',
                'type'		=> 'file'
            ]
        ];

		$data['input_profile'] = json_decode(json_encode($inputsProfile), FALSE);
		$data['input_alamat'] = json_decode(json_encode($inputsAlamat), FALSE);
        $data['input_dokumen'] = json_decode(json_encode($inputsDokumen), FALSE);

		$this->render_view(2, 'members/guru/profile', $data);
    }

    /**
     * @throws \Random\RandomException
     */
    public function save() {
        $this->load->model('Log_model', 'logging');

		$id_guru		= $this->input->post('id_guru', true);
		$nip			= $this->input->post('nip', true);
		$nama_guru		= $this->input->post('nama_guru', true);
		$email			= $this->input->post('email', true);
		$jenis_kelamin 	= $this->input->post('jenis_kelamin', true);
		$no_hp 			= $this->input->post('no_hp', true);
		$agama 			= $this->input->post('agama', true);
		$no_ktp 		= $this->input->post('no_ktp', true);
		$tempat_lahir 	= $this->input->post('tempat_lahir', true);
		$tgl_lahir 		= $this->input->post('tgl_lahir', true);
		$alamat_jalan 	= $this->input->post('alamat_jalan', true);
		$kecamatan 		= $this->input->post('kecamatan', true);
		$kabupaten 		= $this->input->post('kabupaten', true);
		$provinsi 		= $this->input->post('provinsi', true);
		$kode_pos 		= $this->input->post('kode_pos', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
		$dbdata 	= $this->master->getGuruById($id_guru, $tp->id_tp, $smt->id_smt);
		$u_nip		= $dbdata->nip == $nip ? "" : "|is_unique[master_guru.nip]";

		$this->form_validation->set_rules('nip', 'NIP', 'required|trim|min_length[8]|max_length[30]' . $u_nip);
		$this->form_validation->set_rules('nama_guru', 'Nama Guru', 'required|trim|min_length[1]|max_length[50]');

        // Konfigurasi upload
        $config['upload_path'] = './uploads/profile_guru/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
        $config['max_size']      = 2048; // Ukuran maksimum file dalam KB (2MB)

		if ($this->form_validation->run() == FALSE) {
			$data = [
				'status'	=> false,
				'errors'	=> [
					'nip' => form_error('nip'),
					'nama_guru' => form_error('nama_guru'),
				]
			];
			$this->output_json($data);
		} else {
            $input = [
                'nip'			=> $nip,
                'nama_guru' 	=> $nama_guru,
                'email' 		=> $email,
                'jenis_kelamin' => $jenis_kelamin,
                'no_hp'			=> $no_hp,
                'agama'			=> $agama,
                'no_ktp'		=> $no_ktp,
                'tempat_lahir'	=> $tempat_lahir,
                'tgl_lahir'		=> $this->strContains($tgl_lahir, '0000-') ? null : $tgl_lahir,
                'alamat_jalan'	=> $alamat_jalan,
                'kecamatan'		=> $kecamatan,
                'kabupaten'		=> $kabupaten,
                'provinsi'		=> $provinsi,
                'kode_pos'		=> $kode_pos
            ];

            $uploaded_files = json_decode($dbdata->dokumen ?? '[]', true);
            $errors = [];
            if (isset($_FILES["ktp_id"]["name"]) && !empty($_FILES['ktp_id']['name'][0])) {
                $bytes1 = random_bytes(10);
                $custom1 = 'ktp_'.$nip.'_'.bin2hex($bytes1);
                $config['file_name'] = $custom1;
                $this->upload->initialize($config);

                if ($this->upload->do_upload('ktp_id')) {
                    $result1 = $this->upload->data();
                    $uploaded_files['ktp'] = 'uploads/profile_guru/'.$result1['file_name'];
                } else {
                    $errors['ktp'] = $this->upload->display_errors();
                }
            }

            if (isset($_FILES["kk_id"]["name"]) && !empty($_FILES['kk_id']['name'][0])) {
                $bytes2 = random_bytes(10);
                $custom2 = 'kk_'.$nip.'_'.bin2hex($bytes2);
                $config['file_name'] = $custom2;
                $this->upload->initialize($config);
                if ($this->upload->do_upload('kk_id')) {
                    $result2 = $this->upload->data();
                    $uploaded_files['kk'] = 'uploads/profile_guru/'.$result2['file_name'];
                } else {
                    $errors['kk'] = $this->upload->display_errors();
                }
            }

            if (isset($_FILES["serti"]["name"]) && !empty($_FILES['serti']['name'][0])) {
                $filesCount = count($_FILES['serti']['name']);
                for ($i = 0; $i < $filesCount; $i++) {
                    $bytes3 = random_bytes(10);
                    $custom3 = 'sert_'.$nip.'_'.bin2hex($bytes3);
                    $config['file_name'] = $custom3;
                    $this->upload->initialize($config);

                    $_FILES['sfile']['name']     = $_FILES['serti']['name'][$i];
                    $_FILES['sfile']['type']     = $_FILES['serti']['type'][$i];
                    $_FILES['sfile']['tmp_name'] = $_FILES['serti']['tmp_name'][$i];
                    $_FILES['sfile']['error']    = $_FILES['serti']['error'][$i];
                    $_FILES['sfile']['size']     = $_FILES['serti']['size'][$i];

                    if ($this->upload->do_upload('sfile')) {
                        $result3 = $this->upload->data();
                        if (!isset($uploaded_files['sertifikat'])) {
                            $uploaded_files['sertifikat'] = [];
                        }
                        $uploaded_files['sertifikat'][] = 'uploads/profile_guru/'.$result3['file_name'];
                    } else {
                        $errors['sertifikat'][] = $this->upload->display_errors();
                    }
                }
            }

            if (count($uploaded_files)>0) {
                $input['dokumen'] = json_encode($uploaded_files);
            }

            $action = $this->master->update('master_guru', $input, 'id_guru', $id_guru);
			if ($action) {
				$this->output_json(['status' => true, 'dokumen' => $uploaded_files, 'errors' => $errors]);
                $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengubah data profile guru: '.$nama_guru);
			} else {
				$this->output_json(['status' => false]);
			}
		}
	}

	function strContains($string, $val) {
        return strpos($string, $val) !== false;
    }

	function uploadFile($id_guru){
        $this->load->model('Log_model', 'logging');
	    $guru = $this->master->getGuruById($id_guru);
        if(isset($_FILES["foto"]["name"])){

			$config['upload_path'] = './uploads/profile_guru/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
			$config['overwrite'] = true;
			//$config['encrypt_name'] = TRUE;
            $config['file_name'] = $guru->nip;

			$this->upload->initialize($config);
			if(!$this->upload->do_upload('foto')){
				$data['status'] = false;
				$data['src'] = $this->upload->display_errors();
			}else{
				$result = $this->upload->data();
				$data['src'] = base_url().'uploads/profile_guru/'.$result['file_name'];
				$data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
				$data['status'] = true;

				$this->db->set('foto', 'uploads/profile_guru/'.$result['file_name']);
				$this->db->where('id_guru', $id_guru);
				$this->db->update('master_guru');

                $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengubah foto guru: '.$guru->nama_guru);
			}

			$data['type'] = $_FILES['foto']['type'];
			$data['size'] = $_FILES['foto']['size'];
		} else {
			$data['src'] = '';
		}
		$this->output_json($data);
	}

	function deleteFile($id_guru) {
        $this->load->model('Log_model', 'logging');
		$src = $this->input->get('src');
		$file_name = str_replace(base_url(), '', $src ?? '');
		if ($file_name != 'guru.png') {
			if (file_exists($file_name) && unlink($file_name)) {
				$this->db->set('foto', '');
				$this->db->where('id_guru', $id_guru);
				$this->db->update('master_guru');
                $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Menghapus foto guru: '.$file_name);
				echo 'File Delete Successfully';
			}
		}
	}

    function delete_dokumen($id_guru) {
        $this->load->model('Log_model', 'logging');
        $src = $this->input->get('src');
        $file_name = str_replace(base_url(), '', $src ?? '');

        $file_split = explode('/', $file_name);
        $type = end($file_split);
        $split_type = explode('_', $type);
        $file_type = $split_type[0];
        if ($file_type == 'sert') {
            $file_type = 'sertifikat';
        }

        $this->db->select('id_guru, nama_guru, dokumen');
        $this->db->from('master_guru');
        $this->db->where('id_guru', $id_guru);
        $query = $this->db->get();
        $dok = $query->row();

        $dokumen = json_decode($dok->dokumen, true);
        if (isset($dokumen[$file_type]) && is_array($dokumen[$file_type])) {
            $dokumen[$file_type] = array_filter($dokumen[$file_type], function($item) use ($file_name) {
                return $item !== $file_name;
            });
        } else {
            unset($dokumen[$file_type]);
        }

        if (isset($dokumen[$file_type])) {
            $dokumen[$file_type] = array_values($dokumen[$file_type]);
        }

        if (file_exists($file_name) && unlink($file_name)) {
            $this->db->set('dokumen', json_encode($dokumen));
            $this->db->where('id_guru', $id_guru);
            $this->db->update('master_guru');
            $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Menghapus dokumen guru: '.$file_type.' - '.$dok->nama_guru);
        }


        $this->output_json([
            'status' => true,
            'message' => 'Dokumen deleted successfully.',
            'dokumen' => $dokumen
        ]);
    }

    public function save_signature() {
        $id_guru = $this->input->post('id_guru');
        $image = $this->input->post('image');

        if (empty($image) || empty($id_guru)) {
            $this->output_json(['status' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
        $image = str_replace(' ', '+', $image);
        $data = base64_decode($image);

        $file_name = 'ttd_' . $id_guru . '_' . time() . '.png';
        $file_path = 'uploads/profile_guru/' . $file_name;
        
        if (!is_dir('./uploads/profile_guru/')) {
            mkdir('./uploads/profile_guru/', 0777, true);
        }

        if (file_put_contents('./' . $file_path, $data)) {
            $guru = $this->db->get_where('master_guru', ['id_guru' => $id_guru])->row();
            if ($guru && !empty($guru->ttd) && file_exists($guru->ttd)) {
                unlink($guru->ttd);
            }

            $this->db->where('id_guru', $id_guru);
            $this->db->update('master_guru', ['ttd' => $file_path]);

            $this->output_json([
                'status' => true,
                'src' => base_url().$file_path,
                'message' => 'Tanda tangan berhasil disimpan'
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal menyimpan file'
            ]);
        }
    }

    public function delete_ttd() {
        $id_guru = $this->input->post('id_guru');
        $guru = $this->db->get_where('master_guru', ['id_guru' => $id_guru])->row();

        if ($guru && !empty($guru->ttd)) {
            if (file_exists($guru->ttd)) {
                unlink($guru->ttd);
            }
            $this->db->where('id_guru', $id_guru);
            $this->db->update('master_guru', ['ttd' => null]);
            
            $this->output_json(['status' => true, 'message' => 'Tanda tangan berhasil dihapus']);
        } else {
            $this->output_json(['status' => false, 'message' => 'Tanda tangan tidak ditemukan']);
        }
    }

    public function upload_ttd() {
        $id_guru = $this->input->post('id_guru');
        $guru = $this->db->get_where('master_guru', ['id_guru' => $id_guru])->row();
        
        if(isset($_FILES["file"]["name"])){
			$config['upload_path'] = './uploads/profile_guru/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
			$config['overwrite'] = true;
			$config['encrypt_name'] = TRUE; // Random name

			$this->upload->initialize($config);
			if(!$this->upload->do_upload('file')){
				$data['status'] = false;
				$data['message'] = $this->upload->display_errors();
			} else {
				$result = $this->upload->data();
				$file_path = 'uploads/profile_guru/'.$result['file_name'];
				
                if ($guru && !empty($guru->ttd) && file_exists($guru->ttd)) {
                    unlink($guru->ttd);
                }

				$this->db->where('id_guru', $id_guru);
				$this->db->update('master_guru', ['ttd' => $file_path]);

                $data = [
    				'status' => true,
    				'src' => base_url().$file_path,
    				'message' => 'Tanda tangan berhasil diupload'
                ];
			}
		} else {
			$data = ['status' => false, 'message' => 'File tidak ditemukan'];
		}
		$this->output_json($data);
    }
}
