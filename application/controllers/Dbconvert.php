<?php


class Dbconvert extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
		} else if (!$this->auth->is_admin()) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul' => 'Image Converter',
			'subjudul' => 'Convert Image BASE_64 ke File',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();

        $tbl_soal = $this->db->select('id_soal')->get('cbt_soal')->num_rows();
        $tbl_cbtsiswa = $this->db->select('id_cbt_siswa')->get('cbt_siswa')->num_rows();
        $tbl_rekap_jadwal = $this->db->select('id_rekap')->get('cbt_rekap_jadwal')->num_rows();
        $tbl_rekap_siswa = $this->db->select('id_rekap_siswa')->get('cbt_rekap_siswa')->num_rows();
        $data['tables'] = [
            'cbt_soal' => $tbl_soal,
            'cbt_siswa' => $tbl_cbtsiswa,
            'cbt_rekap_jadwal' => $tbl_rekap_jadwal,
            'cbt_rekap_siswa' => $tbl_rekap_siswa
        ];

		$this->render_view(1, 'setting/converter', $data);
	}

    public function convert() {
        $table = $this->input->post('table', true);
        $limit = (int) $this->input->post('limit', true);
        $offset = (int) $this->input->post('offset', true);

        if (!$table || $limit < 1) {
            $this->output_json([
                'success' => false,
            ]);
        }
        $tables = [
            'cbt_soal'=> 'id_soal,jenis,soal,opsi_a,opsi_b,opsi_c,opsi_d,opsi_e,jawaban,nomor_soal,bank_id',
            'cbt_siswa'=> 'id_cbt_siswa,jawaban',
            'cbt_rekap_jadwal'=> 'id_rekap,soal_pg,soal_kompleks,soal_jodohkan,soal_isian,soal_esai',
            'cbt_rekap_siswa'=> 'id_rekap_siswa,jawaban'
        ];

        // Query data sesuai limit dan offset
        $rows = $this->db->select($tables[$table])->get($table, $limit, $offset)->result();

        if ($table == 'cbt_soal') {
            foreach ($rows as $row) {
                $nomor = $row->id_soal;
                $jenis = $row->jenis;
                $idBank = $row->bank_id;
                $row->soal = $this->decode_data($row->soal, $idBank, $jenis, $nomor);
                if ($jenis == '1') {
                    $row->opsi_a = $this->decode_data($row->opsi_a, $idBank, $jenis, $nomor);
                    $row->opsi_b = $this->decode_data($row->opsi_b, $idBank, $jenis, $nomor);
                    $row->opsi_c = $this->decode_data($row->opsi_c, $idBank, $jenis, $nomor);
                    $row->opsi_d = $this->decode_data($row->opsi_d, $idBank, $jenis, $nomor);
                    $row->opsi_e = $this->decode_data($row->opsi_e, $idBank, $jenis, $nomor);
                } elseif ($jenis == '2') {
                    $opsi_a = $this->maybe_unserialize($row->opsi_a);
                    if (is_array($opsi_a)) {
                        foreach ($opsi_a as $key => $value) {
                            $opsi_a[$key] = $this->decode_data($value, $idBank, $jenis, $nomor.$key);
                        }
                    }
                    $row->opsi_a = json_encode($opsi_a);
                } elseif ($jenis == '3') {
                    $jawaban = $this->maybe_unserialize($row->jawaban);
                    if (isset($jawaban['jawaban']) && is_array($jawaban['jawaban'])) {
                        foreach ($jawaban['jawaban'] as $key => $values) {
                            if (is_array($values)) {
                                foreach ($values as $key2 => $value) {
                                    $jawaban['jawaban'][$key][$key2] = $this->decode_data($value, $idBank, $jenis, $nomor . $key . $key2);
                                }
                            }
                        }
                    }
                    $row->jawaban = json_encode($jawaban);
                } elseif ($jenis == '4') {
                    $row->jawaban = $this->decode_data($row->jawaban, $idBank, $jenis, $nomor);
                } elseif ($jenis == '5') {
                    $row->jawaban = $this->decode_data($row->jawaban, $idBank, $jenis, $nomor);
                }
            }
            $this->db->update_batch($table, $rows, 'id_soal');
        }

        if ($table == 'cbt_siswa') {
            foreach ($rows as $row) {
                $jawabans = $this->maybe_unserialize($row->jawaban);
                foreach ($jawabans as $key1=>$jawaban) {
                    if ($jawaban->jenis == '3') {
                        $nomor = $jawaban->id_soal;
                        $jenis = $jawaban->jenis;
                        $idBank = $jawaban->id_bank;
                        if (isset($jawaban->jawaban_benar->jawaban)) {
                            foreach ($jawaban->jawaban_benar->jawaban as $key => $values) {
                                if (is_array($values)) {
                                    foreach ($values as $key2 => $value) {
                                        $jawabans[$key1]->jawaban_benar->jawaban[$key][$key2] = $this->decode_data($value, $idBank, $jenis, $nomor . $key . $key2);
                                    }
                                }
                            }
                        }

                        if (isset($jawaban->jawaban_siswa->jawaban)) {
                            foreach ($jawaban->jawaban_siswa->jawaban as $key => $values) {
                                if (is_array($values)) {
                                    foreach ($values as $key2 => $value) {
                                        $jawabans[$key1]->jawaban_siswa->jawaban[$key][$key2] = $this->decode_data($value, $idBank, $jenis, $nomor . $key . $key2);
                                    }
                                }
                            }
                        }
                    }
                }
                $row->jawaban = json_encode($jawabans);
            }
            $this->db->update_batch($table, $rows, 'id_cbt_siswa');
        }

        if ($table == 'cbt_rekap_jadwal') {
            foreach ($rows as $row) {
                $soal_pg = $this->maybe_unserialize($row->soal_pg);
                foreach ($soal_pg as $keypg=>$pg) {
                    $nomor = $pg->id_soal;
                    $jenis = $pg->jenis;
                    $idBank = $pg->bank_id;
                    $soal_pg[$keypg]->soal = $this->decode_data($pg->soal, $idBank, $jenis, $nomor);
                    $soal_pg[$keypg]->opsi_a = $this->decode_data($pg->opsi_a, $idBank, $jenis, $nomor);
                    $soal_pg[$keypg]->opsi_b = $this->decode_data($pg->opsi_b, $idBank, $jenis, $nomor);
                    $soal_pg[$keypg]->opsi_c = $this->decode_data($pg->opsi_c, $idBank, $jenis, $nomor);
                    $soal_pg[$keypg]->opsi_d = $this->decode_data($pg->opsi_d, $idBank, $jenis, $nomor);
                    $soal_pg[$keypg]->opsi_e = $this->decode_data($pg->opsi_e, $idBank, $jenis, $nomor);
                }
                $row->soal_pg = json_encode($soal_pg);

                $soal_kompleks = $this->maybe_unserialize($row->soal_kompleks);
                foreach ($soal_kompleks as $keykompleks=>$kompleks) {
                    $nomor = $kompleks->id_soal;
                    $jenis = $kompleks->jenis;
                    $idBank = $kompleks->bank_id;
                    $soal_kompleks[$keykompleks]->soal = $this->decode_data($kompleks->soal, $idBank, $jenis, $nomor);
                    if (isset($kompleks->opsi_a) && is_array($kompleks->opsi_a)) {
                        foreach ($kompleks->opsi_a as $key => $value) {
                            $soal_kompleks[$keykompleks]->opsi_a[$key] = $this->decode_data($value, $idBank, $jenis, $nomor . $key);
                        }
                    }
                }
                $row->soal_kompleks = json_encode($soal_kompleks);

                $soal_jodohkan = $this->maybe_unserialize($row->soal_jodohkan);
                foreach ($soal_jodohkan as $keyjodohkan=>$jodohkan) {
                    $nomor = $jodohkan->id_soal;
                    $jenis = $jodohkan->jenis;
                    $idBank = $jodohkan->bank_id;
                    $soal_jodohkan[$keyjodohkan]->soal = $this->decode_data($jodohkan->soal, $idBank, $jenis, $nomor);
                    if (isset($soal_jodohkan[$keyjodohkan]->jawaban->jawaban) && is_array($soal_jodohkan[$keyjodohkan]->jawaban->jawaban)) {
                        foreach ($soal_jodohkan[$keyjodohkan]->jawaban->jawaban as $key => $values) {
                            if (is_array($values)) {
                                foreach ($values as $key2 => $value) {
                                    $soal_jodohkan[$keyjodohkan]->jawaban->jawaban[$key][$key2] = $this->decode_data($value, $idBank, $jenis, $nomor . $key . $key2);
                                }
                            }
                        }
                    }
                }
                $row->soal_jodohkan = json_encode($soal_jodohkan);

                $soal_isian = $this->maybe_unserialize($row->soal_isian);
                foreach ($soal_isian as $keyisian=>$isian) {
                    $nomor = $isian->id_soal;
                    $jenis = $isian->jenis;
                    $idBank = $isian->bank_id;
                    $soal_isian[$keyisian]->soal = $this->decode_data($isian->soal, $idBank, $jenis, $nomor);
                    $soal_isian[$keyisian]->jawaban = $this->decode_data($isian->jawaban, $idBank, $jenis, $nomor);
                }
                $row->soal_isian = json_encode($soal_isian);

                $soal_esai = $this->maybe_unserialize($row->soal_esai);
                foreach ($soal_esai as $keyesai=>$esai) {
                    $nomor = $esai->id_soal;
                    $jenis = $esai->jenis;
                    $idBank = $esai->bank_id;
                    $soal_esai[$keyesai]->soal = $this->decode_data($esai->soal, $idBank, $jenis, $nomor);
                    $soal_esai[$keyesai]->jawaban = $this->decode_data($esai->jawaban, $idBank, $jenis, $nomor);
                }
                $row->soal_esai = json_encode($soal_esai);
            }
            $this->db->update_batch($table, $rows, 'id_rekap');
        }

        if ($table == 'cbt_rekap_siswa') {
            foreach ($rows as $row) {
                $jawabans = $this->maybe_unserialize($row->jawaban);
                foreach ($jawabans as $key1=>$jawaban) {
                    if ($jawaban->jenis == '3') {
                        $nomor = $jawaban->id_soal;
                        $jenis = $jawaban->jenis;
                        $idBank = $jawaban->id_bank;
                        foreach ($jawaban->jawaban_benar->jawaban as $key => $values) {
                            if (is_array($values)) {
                                foreach ($values as $key2 => $value) {
                                    $jawabans[$key1]->jawaban_benar->jawaban[$key][$key2] = $this->decode_data($value, $idBank, $jenis, $nomor . $key . $key2);
                                }
                            }
                        }
                    }
                }
                $row->jawaban = json_encode($jawabans);
            }
            $this->db->update_batch($table, $rows, 'id_rekap_siswa');
        }

        // Kembalikan hasil ke client
        $this->output_json([
            'success' => true,
            'page' => ($offset / $limit) + 1,
            'count' => count($rows),
            'data' => $rows
        ]);
    }

    function isHtml($string) {
        $string = ltrim($string);
        // Cek apakah string diawali dengan tag HTML
        return preg_match('/^<\s*\w+.*?>/', $string) == 1;
    }

    function decode_data($html, $id_bank, $jenis, $nomor) {
        if (!$this->isHtml($html)) {
            return $html;
        }
        libxml_use_internal_errors(true);
        try {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

            $images = $dom->getElementsByTagName('img');
            if ($images) {
                $numbrimg = 1;
                foreach ($images as $image) {
                    $src = $image->getAttribute('src');
                    if (substr($src, 0, 5) == "data:") {
                        $base64_image_string = $image->getAttribute('src');
                        $splited = explode(',', substr($base64_image_string, 5), 2);
                        $mime = $splited[0];
                        $data = $splited[1];

                        $mime_split_without_base64 = explode(';', $mime, 2);
                        $mime_split = explode('/', $mime_split_without_base64[0], 2);
                        if (count($mime_split) == 2) {
                            $extension = $mime_split[1];
                            if ($extension == 'jpeg') $extension = 'jpg';
                            $output_file = 'img_' . $id_bank . $jenis . $nomor . '_' . 'converted' . $numbrimg . '.' . $extension;
                            file_put_contents('./uploads/bank_soal/' . $output_file, base64_decode($data));
                            $image->setAttribute('src', 'uploads/bank_soal/' . $output_file);
                            $numbrimg++;
                        }
                    } else {
                        $image->setAttribute('src', str_replace(base_url(), '', $src ?? ''));
                    }
                }
                $res = $dom->saveHTML();
                return str_replace('<?xml encoding="UTF-8">', '', $res ?? '');
            }
        } catch (Exception $e) {

        }
        libxml_clear_errors();
        return $html;
    }
}
