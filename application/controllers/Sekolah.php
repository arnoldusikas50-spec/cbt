<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sekolah extends MY_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->auth->logged_in()){
			redirect('auth');
		}else if(!$this->auth->is_admin()){
            show_error('Hanya Admin yang boleh mengakses halaman ini', 403, 'Akses dilarang');
		}
		$this->load->library('upload');
		$this->load->helper('directory');
	}

    public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul'	=> 'Profile Sekolah',
			'subjudul'=> '',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        $this->render_view(1, 'setting/data', $data);
	}

	public function dbManager(){
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Settings_model', 'settings');

		$data = [
			'user' => $this->auth->user(),
			'judul'	=> 'Backup dan Restore',
			'subjudul'=> 'Backup dan Restore',
		];

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();
		$data['setting'] = $this->settings->getSetting();
		$data['list'] = directory_map('./backups/');

		$this->render_view(1, 'setting/db', $data);
	}

	function uploadFile($logo){
		if(isset($_FILES["logo"]["name"])){
			//$newName = $_FILES["logo"]["name"].".".$_FILES['logo']['type'];

			$config['upload_path'] = './uploads/settings/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
			$config['overwrite'] = true;
			$config['file_name'] = $logo;

			$this->upload->initialize($config);
			if(!$this->upload->do_upload('logo')){
				$data['status'] = false;
				$data['src'] = $this->upload->display_errors();
			}else{
				$result = $this->upload->data();
				$data['src'] = base_url().'uploads/settings/'.$result['file_name'];
				$data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
				$data['status'] = true;
			}
			$data['type'] = $_FILES['logo']['type'];
			$data['size'] = $_FILES['logo']['size'];
		} else {
			$data['src'] = '';
		}
		$this->output_json($data);
	}

	function deleteFile() {
		$src = $this->input->post('src');
		$file_name = str_replace(base_url(), '', $src ?? '');
		if (unlink($file_name)) {
			echo 'File Delete Successfully';
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

	public function saveSetting() {
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
		];

		$this->db->where('id_setting', 1);
		$updated = $this->db->update('setting', $insert);
		$this->output_json(['status' => (bool) $updated]);
	}
}
