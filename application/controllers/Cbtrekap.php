<?php


class Cbtrekap extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
        } else if ( !$this->auth->is_admin() && !$this->auth->in_group('panitia') && !$this->auth->in_group('guru')){
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->library('upload');
		$this->form_validation->set_error_delimiters('', '');
	}

    public function index() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Rekap Hasil Penilaian',
            'subjudul' => 'Penilaian',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['is_admin'] = $this->auth->is_admin();
        $data['rekaps'] = $this->cekRekapSiswa();
        $data['session_id'] = $this->session->session_id;

        if($this->auth->is_admin()){
            $this->render_view(1, 'cbt/rekap/rekap', $data);
        } else {
            $this->load->model('Kelas_model', 'kelas');
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
            $arrMapel = [];
            $arrKelasGuru = [];
            if ($mapel) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    $arrKelasGuru = array_map('strval', array_column($m->kelas_mapel, 'kelas'));
                }
            }
            $data['kelas_guru'] = $arrKelasGuru;
            $data['guru'] = $guru;
            $this->render_view(2, 'cbt/rekap/rekap', $data);
        }
    }

    public function get_jadwal()
    {
        $this->load->model('Cbt_pengawas_model', 'pengawas_model');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Master_kelas_model', 'master_kelas_model');
        $this->load->model('Guru_model', 'guru_model');

        $bank_where = [];
        if(!$this->auth->is_admin() && !$this->auth->in_group('panitia')){
            $this->load->model('Users_model', 'users');
            $user = $this->auth->user();
            $guru = $this->users->getDataGuruByUsername($user->username);
            $bank_where['bank_guru_id'] = $guru->id_guru;
        }

        $jadwals = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,rekap,pelanggaran,jam_ke,durasi_ujian,acak_soal,acak_opsi')
            ->with_tp()
            ->with_smt()
            ->with_sesisiswa([
                'where' => [],
                'with' => [
                    ['relation'=>'siswa', 'fields'=>'id_siswa,nama,nis,agama'],
                ]
            ])
            ->with_bank([
                'where' => $bank_where,
                'fields' => 'soal_agama,bank_kode,bank_level,bank_kelas,tampil_pg,tampil_kompleks,tampil_jodohkan,tampil_isian,tampil_esai,bank_guru_id',
                'with' => [
                    ['relation'=>'mapel', 'fields'=>'nama_mapel'],
                ]
            ])
            ->with_jenis()
            ->paginate(100);

        $arrPengawas = [];
        $cbtsiswa = [];
        $id_kelass = [];
        $total = 0;
        if ($jadwals) {
            $idsjadwal = array_column((array)$jadwals, 'id_jadwal');
            $total = $this->cbt_jadwal_model->where('id_jadwal', $idsjadwal)->count_rows();

            if (count($idsjadwal) > 0) {
                $cbtsiswa = $this->cbt_siswa_model
                    ->fields('id_jadwal,id_siswa,mulai,selesai,status,nilai_input,pelanggaran')
                    ->where('id_jadwal', $idsjadwal)
                    ->get_all();

                if ($cbtsiswa) {
                    foreach ($cbtsiswa as $cbt) {
                        if (isset($cbt->nilai_input) && is_string($cbt->nilai_input)) {
                            $cbt->nilai_input = $this->maybe_unserialize($cbt->nilai_input);
                            $cbt->pelanggaran = $this->maybe_unserialize($cbt->pelanggaran ?? '{}');
                        }
                    }
                }
            }

            foreach ($jadwals as $jadwal) {
                $jadwal_id = $jadwal->id_jadwal;
                $jadwal->tp = $jadwal->tp->tahun ?? '-';
                $jadwal->smt = $jadwal->smt->nama_smt ?? '-';

                $bank_kelas = $this->maybe_unserialize($jadwal->bank->bank_kelas ?? []);
                $jadwal->bank_kelas = is_array($bank_kelas) ? $bank_kelas : [];
                $id_kelas = array_map('strval', array_column($jadwal->bank_kelas, 'kelas_id'));

                // Filter siswa berdasarkan kelas_id
                $filter_siswa = array_filter((array)$jadwal->sesisiswa, static function ($s) use ($id_kelas) {
                    return in_array($s->kelas_id ?? null, $id_kelas, true);
                });

                $id_kelass = array_merge($id_kelass, $id_kelas);
                $jadwal->sesisiswa = array_values($filter_siswa);
            }

            $pengawasSesi = $this->pengawas_model
                ->where('id_jadwal', $idsjadwal)
                ->as_array()->get_all();

            $gurus = $this->guru_model->as_array()->get_all();

            if ($pengawasSesi) {
                foreach ($pengawasSesi as $pengawas) {
                    $splitGuru = isset($pengawas['id_guru']) ? explode(',', $pengawas['id_guru']) : [];

                    $ruang = [
                        'id_pengawas' => $pengawas['id_pengawas'] ?? '',
                        'id_jadwal' => $pengawas['id_jadwal'] ?? '',
                        'id_ruang' => $pengawas['id_ruang'] ?? '',
                        'id_sesi' => $pengawas['id_sesi'] ?? '',
                        'pengawas' => []
                    ];

                    foreach ($splitGuru as $idGuru) {
                        $res = null;
                        foreach ($gurus as $gr) {
                            if ($gr['id_guru'] == $idGuru) {
                                $res = $gr;
                                break;
                            }
                        }
                        if ($idGuru != "") {
                            $resGuru = [];
                            $resGuru[$idGuru] = $res ? ($res['nama_guru'] ?? '-') : '-';

                            $ruang['pengawas'][] = $resGuru;
                        }
                    }

                    $arrPengawas[] = $ruang;
                }
            }
        }

        $kelases = [];
        if (count($id_kelass) >0) {
            $kelases = $this->master_kelas_model
                ->fields('id_kelas,kode_kelas,nama_kelas')
                ->where('id_kelas', $id_kelass)
                ->as_array()
                ->get_all();
        }

        $data = [
            'success' => true,
            'jadwal' => $jadwals ? array_values(json_decode(json_encode($jadwals), true)) : [],
            'total' => $total,
            'pengawas' => $arrPengawas,
            'kelases' => $kelases,
            'cbtsiswa' => (array)$cbtsiswa
        ];

        $this->output_json($data);
    }

    public function get_rekap()
    {
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $this->load->model('Master_kelas_model', 'master_kelas_model');
        $rekap_where = [];
        if(!$this->auth->is_admin() && !$this->auth->in_group('panitia')){
            $this->load->model('Users_model', 'users');
            $user = $this->auth->user();
            $guru = $this->users->getDataGuruByUsername($user->username);
            $rekap_where['id_guru'] = $guru->id_guru;
        }
        $total = $this->rekap_jadwal->where($rekap_where)->count_rows();
        $rekaps = $this->rekap_jadwal
            ->fields('id_rekap,id_tp,tp,id_smt,smt,id_jadwal,jadwal,id_bank,id_jenis,kode_jenis,tgl_mulai,tgl_selesai,bank_soal,kode_bank,level_bank as bank_level,kelas_bank,id_guru,nama_mapel,pengawas')
            ->where($rekap_where)
            ->with_rekapsiswa(['fields' => 'id_rekap_siswa,id_siswa,siswa,nama_siswa,id_jadwal,nilai_input,mulai,selesai,id_kelas as kelas_id'])
            ->paginate(100);

        $id_kelass = [];
        if ($rekaps) {
            foreach ($rekaps as $rekap) {
                $rekap->jadwal = $this->maybe_unserialize($rekap->jadwal);
                $rekap->bank = $this->maybe_unserialize($rekap->bank_soal);
                $arr_kelas = $this->maybe_unserialize($rekap->kelas_bank);
                $rekap->pengawas = $this->maybe_unserialize($rekap->pengawas);
                unset($rekap->bank_soal);
                unset($rekap->kelas_bank);
                $klss = [];
                if ($arr_kelas) {
                    foreach ($arr_kelas as $kelas) {
                        if (isset($kelas->id_kelas)) {
                            if (!in_array($kelas->id_kelas, $id_kelass)) {
                                $id_kelass[] = $kelas->id_kelas;
                            }
                            $klss[] = [
                                'kelas_id' => $kelas->id_kelas
                            ];
                        }
                    }
                }
                $rekap->bank_kelas = $klss;
                if (isset($rekap->rekapsiswa)) {
                    $rekap->rekapsiswa = (array)$rekap->rekapsiswa;
                    foreach ($rekap->rekapsiswa as $sis) {
                        if (!is_object($sis->siswa)) {
                            $sis->siswa = $this->maybe_unserialize($sis->siswa ?? '{}');
                        }
                        if (!is_object($sis->nilai_input)) {
                            $sis->nilai_input = $this->maybe_unserialize($sis->nilai_input);
                        }
                    }
                }
            }
        } else {
            $rekaps = [];
        }

        $kelases = [];
        if (count($id_kelass) >0) {
            $kelases = $this->master_kelas_model
                ->fields('id_kelas,kode_kelas,nama_kelas')
                ->where('id_kelas', $id_kelass)
                ->as_array()
                ->get_all();
        }

        $data = [
            'success' => true,
            'rekap' => (array)$rekaps,
            'total' => $total,
            'kelases' => $kelases
        ];
        $this->output_json($data);
    }

    public function rekap() {
        $post = $this->input->post('rekap', false);
        $idsJadwal = json_decode($this->input->post('ids_jadwal', true));
        $idBank = json_decode($this->input->post('ids_bank', true));

        $this->hapusDuplikatJadwal($idsJadwal);

        $details = [];
        $updated = [];

        if ($post) {
            $this->load->model('Cbt_bank_model', 'bank_model');
            $this->load->model('Master_kelas_model', 'master_kelas_model');
            $this->load->model('Cbt_jenis_model', 'cbt_jenis_model');
            $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
            $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');
            $this->load->model('Rekap_siswa_model', 'rekap_siswa');
            $this->load->model('Cbt_log_model', 'cbt_log_model');
            $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');

            $bankSoal = $this->bank_model->where('id_bank', $idBank)
                ->with_mapel()
                ->with_guru()
                ->with_soal()
                ->as_array()
                ->get_all();

            $arrIdJadwal = [];
            foreach ($post as $detail) {
                try {
                    $inserted = [];
                    $id_bank = '';
                    $id_jenis = '';
                    $id_rekap = '';
                    foreach ($detail as $key => $value) {
                        if ($key == 'ids') {
                            $ids = json_decode($value, true);
                            if (json_last_error() !== JSON_ERROR_NONE || !is_array($ids)) {
                                throw new Exception("Error decoding JSON in 'ids' field.");
                            }
                            $id_bank = $ids['id_bank'];
                            $id_jenis = $ids['id_jenis'];
                            $id_jadwal = $ids['id_jadwal'];
                            $id_rekap = $ids['id_rekap'];
                            if (!in_array($id_jadwal, $arrIdJadwal, true)) {
                                $arrIdJadwal[] = $id_jadwal;
                            }
                            foreach ($ids as $idKey => $idValue) {
                                if ($idKey != 'id_rekap') {
                                    $inserted[$idKey] = $idValue;
                                }
                            }
                        } else {
                            $inserted[$key] = $value;
                        }
                    }
                    $idxBank = array_search($id_bank, array_column($bankSoal, 'id_bank'));
                    $bank = (object)$bankSoal[$idxBank];
                    $kelasBank = $this->maybe_unserialize($bank->bank_kelas ?? []);
                    unset($bank->bank_kelas);
                    $klsids = [];
                    foreach ($kelasBank as $kls_id) {
                        $klsids[] = $kls_id->kelas_id;
                    }

                    $kelas_bank = $this->master_kelas_model->where('id_kelas', $klsids)->with_level()->as_array()->get_all();
                    foreach ($kelas_bank as $key=>$kelas) {
                        $kelas_bank[$key]['jumlah_siswa'] = $this->maybe_unserialize($kelas['jumlah_siswa']);
                    }

                    $jenis = $this->cbt_jenis_model->where('id_jenis', $id_jenis)->get();

                    $guru = $bank->guru;
                    $mapel = $bank->mapel;
                    $soals = isset($bank->soal) ? $bank->soal : [];
                    foreach ($soals as $soal) {
                        $soal->opsi_a = $this->maybe_unserialize($soal->opsi_a ?? '');
                        $soal->jawaban = $this->maybe_unserialize($soal->jawaban ?? '');
                    }

                    $soalPg = array_filter($soals, static function($val) {return ($val->jenis == '1');});
                    $soalPg2 = array_filter($soals, static function($val) {return ($val->jenis == '2');});
                    $soalJod = array_filter($soals, static function($val) {return ($val->jenis == '3');});
                    $soalIs = array_filter($soals, static function($val) {return ($val->jenis == '4');});
                    $soalEs = array_filter($soals, static function($val) {return ($val->jenis == '5');});

                    unset($bank->guru, $bank->mapel, $bank->soal);

                    $inserted['bank_soal'] = json_encode($bank);
                    $inserted['kode_bank'] = $bank->bank_kode;
                    $inserted['kelas_bank'] =json_encode( $kelas_bank);
                    $inserted['level_bank'] = $bank->bank_level;
                    $inserted['kode_jenis'] = $jenis->kode_jenis;
                    $inserted['id_mapel'] = $mapel->id_mapel;
                    $inserted['kode_mapel'] = $mapel->kode;
                    $inserted['nama_mapel'] = $mapel->nama_mapel;
                    $inserted['soal_pg'] = json_encode(array_values($soalPg));
                    $inserted['soal_kompleks'] = json_encode(array_values($soalPg2));
                    $inserted['soal_jodohkan'] = json_encode(array_values($soalJod));
                    $inserted['soal_isian'] = json_encode(array_values($soalIs));
                    $inserted['soal_esai'] = json_encode(array_values($soalEs));
                    $inserted['id_guru'] = $guru->id_guru;
                    $inserted['nama_guru'] = $guru->nama_guru;

                    //$rekap = $detail['rekap'];
                    //$id_rekap = $detail['id_rekap'];
                    if ($id_rekap == '') {
                        $details[] = $inserted;
                        $this->db->insert('cbt_rekap_jadwal', $inserted);
                    } else {
                        $updated[] = $inserted;
                        $this->db->where('id_rekap', $id_rekap);
                        $this->db->update('cbt_rekap_jadwal', $inserted);
                    }

                    $data['success'] = true;
                    $data['message'] = 'Sukses';
                } catch (Exception $e) {
                    $data['success'] = false;
                    $data['message'] = $e->getMessage();
                    continue;
                }
            }
        } else {
            $data['success'] = false;
            $data['message'] = 'Gagal';
        }

        $data['updated'] = count($updated);
        $data['inserted'] = count($details);
        $data['post'] = count($post);
        $this->output_json($data);
    }

    public function hapusDuplikatJadwal($ids_jadwal) {
        $this->db->select_min('id_rekap');
        $this->db->where_in('id_jadwal', $ids_jadwal);
        $this->db->group_by('id_jadwal');
        $query = $this->db->get('cbt_rekap_jadwal');
        $unique_ids = array_column($query->result_array(), 'id_rekap');

        if (!empty($unique_ids)) {
            $this->db->where_not_in('id_rekap', $unique_ids);
            $this->db->delete('cbt_rekap_jadwal');
        }
    }

    private function getRekapSiswa($idJadwal)
    {
        $this->db->select('id_rekap_siswa,id_jadwal,id_siswa');
        $this->db->where_in('id_jadwal', $idJadwal);
        $results = $this->db->get('cbt_rekap_siswa')->result();

        $rekapSiswa = [];
        foreach ($results as $rekap) {
            $rekapSiswa[$rekap->id_jadwal][$rekap->id_siswa] = $rekap;
        }
        return $rekapSiswa;
    }

    private function countCbtSiswa($idJadwal)
    {
        $this->db->from('cbt_siswa a');
        $this->db->where_in('a.id_jadwal', $idJadwal);
        return $this->db->count_all_results();
    }

    private function getCbtSiswa($idJadwal, $limit, $offset)
    {
        $this->db->select('a.id_siswa,a.id_jadwal,a.id_bank,a.nilai_input,a.jawaban,a.nilai,a.mulai,a.selesai,a.status,a.lama_ujian,a.pelanggaran,'.
            'j.id_tp,j.id_smt,s.smt,s.nama_smt,t.tahun,'.
            'h.id_kelas, h.nama_kelas,'.
            'e.*,f.*,'.
            'g.nomor_peserta');
        $this->db->from('cbt_siswa a');
        $this->db->join('cbt_jadwal j', 'a.id_jadwal=j.id_jadwal');
        $this->db->join('cbt_sesi_siswa c', 'a.id_siswa=c.siswa_id AND j.id_tp=c.tp_id AND j.id_smt=c.smt_id');
        $this->db->join('cbt_ruang e', 'c.ruang_id=e.id_ruang', "left");
        $this->db->join('cbt_sesi f', 'c.sesi_id=f.id_sesi', "left");
        $this->db->join('cbt_nomor_peserta g', 'a.id_siswa=g.id_siswa AND j.id_tp=g.id_tp AND j.id_smt=g.id_smt', "left");
        $this->db->join('master_kelas h', 'c.kelas_id=h.id_kelas');
        $this->db->join('master_tp t', 'j.id_tp=t.id_tp');
        $this->db->join('master_smt s', 'j.id_smt=s.id_smt');
        $this->db->where_in('a.id_jadwal', $idJadwal);
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    private function getMasterSiswa($ids)
    {
        $this->db->select('id_siswa,nisn,nis,nama,jenis_kelamin,agama');
        $this->db->where_in('id_siswa', $ids);
        $result = $this->db->get('master_siswa')->result();

        $siswa = [];
        foreach ($result as $s) {
            $siswa[$s->id_siswa] = $s;
        }
        return $siswa;
    }

    private function updateRekapedJadwal($idJadwal)
    {
        $update = [];
        foreach ($idJadwal as $id) {
            $update[] = ['id_jadwal' => $id, 'rekap' => '1'];
        }
        $this->db->update_batch('cbt_jadwal', $update, 'id_jadwal');
    }

    public function rekap_nilai_siswa()
    {
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $this->load->model('Log_model', 'logging');

        $idJadwal = json_decode($this->input->post('idjadwal', true));
        // get rekap
        $rekapSiswa = $this->getRekapSiswa($idJadwal);

        // get information
        $cbtSiswa = []; //$this->getCbtSiswa($idJadwal);
        $limit = 500; // jumlah data per halaman
        $total_data = $this->countCbtSiswa($idJadwal);
        $total_page = ceil($total_data / $limit); // pembulatan ke atas
        for ($i = 0; $i < $total_page; $i++) {
            $page = $i + 1;
            $offset = ($page - 1) * $limit;
            $res_page = $this->getCbtSiswa($idJadwal, $limit, $offset);
            $cbtSiswa = array_merge($cbtSiswa, $res_page);
        }

        if (count($cbtSiswa) > 0) {
            $arr_ids = array_column($cbtSiswa, 'id_siswa');
            $siswa_res = $this->getMasterSiswa($arr_ids);
            $siswas = [];
            foreach ($siswa_res as $sis) {
                $siswas[$sis->id_siswa] = $sis;
            }

            $for_insert = [];
            $for_update = [];
            foreach ($cbtSiswa as $siswa) {
                if ($siswa->selesai != null) {
                    $master_siswa = $siswas[$siswa->id_siswa] ?? new stdClass();
                    $ruang_sesi = [
                        "ruang" => [
                            "id_ruang" => $siswa->id_ruang,
                            "nama_ruang" => $siswa->nama_ruang,
                            "kode_ruang" => $siswa->kode_ruang,
                        ],
                        "sesi" => [
                            "id_sesi" => $siswa->id_sesi,
                            "nama_sesi" => $siswa->nama_sesi,
                            "kode_sesi" => $siswa->kode_sesi,
                            "waktu_mulai" => $siswa->waktu_mulai,
                            "waktu_akhir" => $siswa->waktu_akhir,
                            "istirahat" => $siswa->istirahat,
                            "aktif" => $siswa->aktif,
                        ]
                    ];

                    $durasi = $this->hitungWaktu(['mulai' => $siswa->mulai, 'selesai' => $siswa->selesai, 'lama_ujian' => $siswa->lama_ujian]);

                    $id_jadwal = $siswa->id_jadwal;
                    $id_siswa = $siswa->id_siswa;
                    $insert = [
                        'id_siswa' => $id_siswa,
                        'nama_siswa' => $master_siswa->nama,
                        'agama' => $master_siswa->agama,
                        'no_peserta' => $siswa->nomor_peserta,
                        'siswa' => json_encode($master_siswa),
                        'id_tp' => $siswa->id_tp,
                        'tp' => $siswa->tahun,
                        'id_smt' => $siswa->id_smt,
                        'smt' => $siswa->smt,
                        'id_jadwal' => $id_jadwal,
                        'id_bank' => $siswa->id_bank,
                        'id_kelas' => $siswa->id_kelas,
                        'kelas' => $siswa->nama_kelas,
                        'ruang_sesi' => json_encode($ruang_sesi),
                        'jawaban' => $siswa->jawaban,
                        'nilai' => $siswa->nilai,
                        'nilai_input' => $siswa->nilai_input,
                        'status' => $siswa->status,
                        'mulai' => $siswa->mulai,
                        'selesai' => $siswa->selesai,
                        'lama_ujian' => $durasi['lama_ujian'],
                        'pelanggaran' => $siswa->pelanggaran
                    ];
                    if (isset($rekapSiswa[$id_jadwal][$id_siswa])) {
                        $insert['id_rekap_siswa'] = $rekapSiswa[$id_jadwal][$id_siswa]->id_rekap_siswa;
                        $for_update[] = $insert;
                    } else {
                        $for_insert[] = $insert;
                    }
                }
            }

            if (count($for_insert) > 0) {
                $this->db->insert_batch('cbt_rekap_siswa', $for_insert);
            }
            if (count($for_update) > 0) {
                $this->db->update_batch('cbt_rekap_siswa', $for_update, 'id_rekap_siswa');
            }

            //update status rekap tabel jadwal
            if (count($for_insert) > 0 || count($for_update) > 0) {
                $this->updateRekapedJadwal($idJadwal);
            }

            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Merekap hasil ujian');
        }

        gc_collect_cycles();

        $data['message'] = 'Berhasil';
        $data['rekap_siswa'] = count($rekapSiswa);
        $data['cbt'] = count($cbtSiswa);
        $data['success'] = true;
        $this->output_json($data);
    }

    function hitungWaktu($durasi) {
        if (!empty($durasi['mulai']) && !empty($durasi['selesai'])) {
            $lamanya = isset($durasi['lama_ujian']) ? $durasi['lama_ujian'] : null;

            if (empty($lamanya) || strpos($lamanya, ':') == false) {
                $mulai = strtotime($durasi['mulai'] ?? '');
                $selesai = strtotime($durasi['selesai'] ?? '');
                $lama = $selesai - $mulai;

                // Format ke H:i:s
                $hours = floor($lama / 3600);
                $minutes = floor(($lama % 3600) / 60);
                $seconds = $lama % 60;

                $durasi['lama_ujian'] = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }
        }
        return $durasi;
    }

    public function rekap_nilai()
    {
        $post = $this->input->post('nilai', true);
        //$idSiswa = json_decode($this->input->post('idsiswa', true));
        $idJadwal = json_decode($this->input->post('idjadwal', true));

        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');
        $this->load->model('Rekap_siswa_model', 'rekap_siswa');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $this->load->model('Log_model', 'logging');

        $cbtSiswa = $this->cbt_siswa_model
            ->fields('id_jadwal,id_siswa,jawaban,nilai,nilai_input')
            ->where('id_jadwal', $idJadwal)
            //->where('id_siswa', $idSiswa)
            ->get_all();

        $mapSiswa = [];
        foreach ($cbtSiswa as $cbt) {
            $mapSiswa[$cbt->id_jadwal][$cbt->id_siswa] = $cbt;
        }

        $id_jadwal_update = [];
        $for_insert = [];
        $for_update = [];
        foreach ($post as $detail) {
            try {
                $inserted = [];
                $is_update = 0;
                $id_siswa = '';
                $id_jadwal = '';
                foreach ($detail as $key => $value) {
                    if ($key == 'ids') {
                        $ids = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE || !is_array($ids)) {
                            throw new Exception("Error decoding JSON in 'ids' field.");
                        }
                        $id_siswa = $ids['id_siswa'];
                        $id_jadwal = $ids['id_jadwal'];
                        foreach ($ids as $idKey => $idValue) {
                            $inserted[$idKey] = $idValue;
                        }
                    } elseif ($key == 'is_update') {
                        $is_update = $value;
                    } else {
                        $inserted[$key] = $value;
                    }
                }

                if (isset($mapSiswa[$id_jadwal][$id_siswa])) {
                    $inserted['jawaban'] = $mapSiswa[$id_jadwal][$id_siswa]->jawaban;
                    $inserted['nilai'] = $mapSiswa[$id_jadwal][$id_siswa]->nilai;
                    $inserted['nilai_input'] = $mapSiswa[$id_jadwal][$id_siswa]->nilai_input;
                    if ($is_update) {
                        $for_update[] = $inserted;
                    } else {
                        $for_insert[] = $inserted;
                    }
                    $id_jadwal_update[] = $id_jadwal;
                }
            } catch (Exception $e) {
                $data['success'] = false;
                $data['message'] = $e->getMessage();
                continue;
            }
        }

        if (count($for_insert)>0) {
            $this->db->insert_batch('cbt_rekap_siswa', $for_insert);
        }
        if (count($for_update)>0) {
            $this->db->update_batch('cbt_rekap_siswa', $for_update, 'id_rekap_siswa');
        }

        //update status rekap tabel jadwal
        $updateJadwal = [];
        foreach ($id_jadwal_update as $item) {
            $updateJadwal[] =[
                'id_jadwal' => $item,
                'rekap' => '1'
            ];
        }
        $this->db->update_batch('cbt_jadwal', $updateJadwal, 'id_jadwal');

        $this->db->select('a.id_rekap_siswa, a.id_siswa, a.id_jadwal, a.nilai_input, a.mulai, a.selesai, a.id_kelas as kelas_id, c.agama');
        $this->db->join('master_siswa c', 'c.id_siswa=a.id_siswa', "left");
        $this->db->where_in('a.id_jadwal', $idJadwal);
        //$this->db->where_in('a.id_siswa', $idSiswa);
        $result = $this->db->get('cbt_rekap_siswa a')->result();
        foreach ($result as $res) {
            $res->nilai_input = $this->maybe_unserialize($res->nilai_input);
        }
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Merekap hasil ujian');

        $data['data'] = $result;

        $data['cbt'] = $mapSiswa;
        $data['for_insert'] = $for_insert;
        $data['for_update'] = $for_update;
        $data['insert'] = count($for_insert);
        $data['update'] = count($for_update);
        $data['success'] = true;
        $data['message'] = 'Berhasil';
        $this->output_json($data);
    }

    public function view_koreksi()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Rekap Nilai Siswa',
            'subjudul' => 'Koreksi Rekap',
            'setting' => $this->dashboard->getSetting()
        ];
        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $kelas_selected = $this->input->get('id_kelas');
        $jadwal_selected = $this->input->get('id_jadwal');
        $data['kelas_selected'] = $kelas_selected;
        $data['jadwal_selected'] = $jadwal_selected;

        if ($this->auth->in_group('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $id_guru = $guru->id_guru;
        } else {
            $id_guru = null;
        }

        $data['is_rekap'] = true;

        if ($this->auth->is_admin()) {
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $this->render_view(1, 'cbt/nilai/data_nilai', $data);
        } else {
            $mapel_guru = $this->kelas->getGuruMapelKelas($id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    //$arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        if ($kls->kelas) {
                            $arrKelas[$kls->kelas] = $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->kelas);
                        }
                    }
                }
            }
            $data['kelas'] = $arrKelas;
            $this->render_view(2, 'cbt/nilai/data_nilai', $data);
        }
    }

    /**
     * @throws JsonException
     */
    public function koreksi()
    {
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Koreksi Hasil Siswa',
            'subjudul' => 'Hasil Siswa',
            'setting' => $this->dashboard->getSetting(),
        ];

        $id_siswa = $this->input->get('id_siswa');
        $id_jadwal = $this->input->get('id_jadwal');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $rekap = $this->rekap_jadwal
            ->where(['id_jadwal'=>$id_jadwal])
            ->with_rekapsiswa([
                'where'=>['id_siswa'=>$id_siswa],
                'with' => [
                    ['relation'=>'siswa', 'fields'=>'id_siswa,nama,nis,agama']
                ]
            ])
            ->get();

        $rekap->jadwal = $this->maybe_unserialize($rekap->jadwal);
        $rekap->bank_soal = $this->maybe_unserialize($rekap->bank_soal);
        $rekap->kelas_bank = $this->maybe_unserialize($rekap->kelas_bank);
        $arrIdKelas = [];
        foreach ($rekap->kelas_bank as $kls) {
            if (isset($kls->id_kelas)) {
                $arrIdKelas[$kls->id_kelas] = $kls->nama_kelas;
            }
        }
        $rekap->pengawas = $this->maybe_unserialize($rekap->pengawas);
        $rekap->soal_pg = $this->maybe_unserialize($rekap->soal_pg);
        $rekap->soal_kompleks = $this->maybe_unserialize($rekap->soal_kompleks);
        $rekap->soal_jodohkan = $this->maybe_unserialize($rekap->soal_jodohkan);
        $rekap->soal_isian = $this->maybe_unserialize($rekap->soal_isian);
        $rekap->soal_esai = $this->maybe_unserialize($rekap->soal_esai ?? '');
        $rekap->rekapsiswa = (array) $rekap->rekapsiswa;
        $this->createSkorSiswa($rekap, true);

        $jadwal = $rekap->jadwal;
        $jadwal->sesisiswa = array_values($rekap->rekapsiswa)[0];
        unset($rekap->rekapsiswa);

        $soal_1 = array_filter($rekap->soal_pg, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_2 =  array_filter($rekap->soal_kompleks, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_3 = array_filter($rekap->soal_jodohkan, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_4 = array_filter($rekap->soal_isian, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_5 = array_filter($rekap->soal_esai, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $data['soal_pg'] = array_values($soal_1);
        $data['soal_kompleks'] = array_values($soal_2);
        $data['soal_jodohkan'] = array_values($soal_3);
        $data['soal_isian'] = array_values($soal_4);
        $data['soal_esai'] = array_values($soal_5);

        $data['is_rekap'] = $jadwal->sesisiswa->id_rekap_siswa;
        $data['rekap_mapel'] = $rekap->nama_mapel;
        $data['rekap_guru'] = $rekap->nama_guru;
        $data['siswa'] = $jadwal->sesisiswa;
        $data['info'] = $rekap->bank_soal;
        $data['id_jadwal'] = $rekap->id_jadwal;
        $data['id_bank'] = $jadwal->id_bank ?? null;
        $data['tp_jadwal'] = $rekap->tp;
        $data['jenis'] = (object)['id_jenis'=>$rekap->id_jenis, 'kode_jenis'=>$rekap->kode_jenis];

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/nilai/koreksi', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->render_view(2, 'cbt/nilai/koreksi', $data);
        }
    }

    private function decodeIds($post) {
        $details = [];
        foreach ($post as $detail) {
            try {
                $inserted = [];
                foreach ($detail as $key => $value) {
                    if ($key == 'ids') {
                        $ids = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE || !is_array($ids)) {
                            throw new Exception("Error decoding JSON in 'ids' field.");
                        }
                        foreach ($ids as $idKey => $idValue) {
                            $inserted[$idKey] = $idValue;
                        }
                    } else {
                        $inserted[$key] = $value;
                    }
                }
                $details[] = $inserted;
            } catch (Exception $e) {
                error_log($e->getMessage());
                continue;
            }
        }
        return $details;
    }

    /**
     * @throws JsonException
     */
    public function inputEssai()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');

        $kelas_selected = $this->input->get('id_kelas');
        $jadwal_selected = $this->input->get('id_jadwal');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Input Nilai Manual',
            'subjudul' => '',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $data['kelas_selected'] = $kelas_selected;
        $data['jadwal_selected'] = $jadwal_selected;

        //$jadwals = $this->olah_nilai(1, $tp->id_tp, $smt->id_smt, $kelas_selected, $jadwal_selected);
        //$data['jadwal'] = $jadwals;

        $rekap = $this->rekap_jadwal
            ->where(['id_jadwal'=>$jadwal_selected])
            ->with_rekapsiswa([
                'where'=>['id_kelas'=>$kelas_selected],
                'with' => [
                    ['relation'=>'siswa', 'fields'=>'id_siswa,nama,nis,agama']
                ]
            ])
            ->get();

        $rekap->jadwal = $this->maybe_unserialize($rekap->jadwal);
        $rekap->bank_soal = $this->maybe_unserialize($rekap->bank_soal);
        $rekap->kelas_bank = $this->maybe_unserialize($rekap->kelas_bank);
        $arrIdKelas = [];
        foreach ($rekap->kelas_bank as $kls) {
            if (isset($kls->id_kelas)) {
                $arrIdKelas[$kls->id_kelas] = $kls;
            }
        }
        $data['kelas'] = $arrIdKelas[$kelas_selected];
        $data['is_rekap'] = true;

        $rekap->pengawas = $this->maybe_unserialize($rekap->pengawas);
        $rekap->soal_pg = array_filter($this->maybe_unserialize($rekap->soal_pg), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $rekap->soal_kompleks = array_filter($this->maybe_unserialize($rekap->soal_kompleks), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $rekap->soal_jodohkan = array_filter($this->maybe_unserialize($rekap->soal_jodohkan), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $rekap->soal_isian = array_filter($this->maybe_unserialize($rekap->soal_isian), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $rekap->soal_esai = array_filter($this->maybe_unserialize($rekap->soal_esai), function ($soal) {
            return $soal->tampilkan == '1';
        });

        $rekap->rekapsiswa = (array) $rekap->rekapsiswa;
        foreach ($rekap->rekapsiswa as $ks=>$rekapsiswa) {
            if ($rekap->bank_soal->soal_agama != '-' && $rekap->bank_soal->soal_agama != '0'
                && $rekap->bank_soal->soal_agama !== $rekapsiswa->siswa->agama) {
                unset($rekap->rekapsiswa[$ks]);
            }
        }
        $rekap->rekapsiswa = array_values($rekap->rekapsiswa);

        $this->createSkorSiswa($rekap, false);
        $data['jadwal'] = $rekap;

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/nilai/input_nilai', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->render_view(2, 'cbt/nilai/input_nilai', $data);
        }
    }

    public function hapusRekap() {
        $this->load->model('Log_model', 'logging');
        $ids = json_decode($this->input->post('ids', true));
        //sleep(1);
        $data['total'] = count($ids);

        //delete rekap
        $this->db->where_in('id_jadwal', $ids);
        $delRekap = $this->db->delete('cbt_rekap_jadwal');

        //delete rekap nilai
        $this->db->where_in('id_jadwal', $ids);
        $delNilai = $this->db->delete('cbt_rekap_siswa');

        if ($delNilai && $delRekap) {
            $updateJadwal = [];
            foreach ($ids as $item) {
                $updateJadwal[] =[
                    'id_jadwal' => $item,
                    'rekap' => '0'
                ];
            }
            $this->db->update_batch('cbt_jadwal', $updateJadwal, 'id_jadwal');
            $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Menghapus rekap hasil ujian');
            $this->session->set_flashdata('rekapnilai', '<div id="flashdata" class="alert alert-default-success align-content-center w-100" role="alert"> Berhasil menghapus <b>'.count($ids).'</b> nilai </div>');
        } else {
            $this->session->set_flashdata('rekapnilai', '<div id="flashdata" class="alert alert-default-danger align-content-center w-100" role="alert"> Hapus nilai gagal </div>');
        }
        $data['ids_jadwal'] = $ids;
        $data['success'] = $delNilai && $delRekap;
        $this->output_json($data);
    }

    function getDataFromArray1ByUserId($array, $userId) {
        foreach($array as $key => $data) {
            if($data->id_siswa == $userId)
                return $array;
        }
        return array();
    }

    // new
    public function ekspor_rekap() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');

        $jadwal = $this->input->get('id_jadwal');
        $kelas = $this->input->get('id_kelas');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Ekspor',
            'subjudul' => 'Ekspor Rekap',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $data['jadwal_selected'] = $jadwal;
        $data['kelas_selected'] = $kelas;

        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $rekap = $this->rekap_jadwal
            ->where(['id_jadwal'=>$jadwal])
            ->with_rekapsiswa([
                'where'=>['id_kelas'=>$kelas],
                'with' => [
                    ['relation'=>'siswa', 'fields'=>'id_siswa,nama,nis,agama']
                ]
            ])
            ->get();

        if($this->auth->is_admin()){
            $data['rekap'] = $rekap;
            $this->render_view(1, 'cbt/rekap/ekspor_rekap', $data);
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            //$data['jadwal'] = $this->dropdown->getAllJadwalGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);

            $this->render_view(2, 'cbt/rekap/ekspor_rekap', $data);
        }
    }

    /**
     * @throws JsonException
     */
    public function get_nilai_rekap()
    {
        $kelas = $this->input->post('id_kelas');
        $jadwal = $this->input->post('id_jadwal');
        //$katrol = json_decode($this->input->post('katrol') ?? '[]', true);

        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');

        $rekap = $this->rekap_jadwal
            ->where(['id_jadwal'=>$jadwal])
            ->with_rekapsiswa([
                'where'=>['id_kelas'=>$kelas],
            ])
            ->get();

        $rekap->jadwal = $this->maybe_unserialize($rekap->jadwal);
        $rekap->bank_soal = $this->maybe_unserialize($rekap->bank_soal);
        $rekap->kelas_bank = $this->maybe_unserialize($rekap->kelas_bank);
        $klss = [];
        foreach ($rekap->kelas_bank as $kls) {
            if (isset($kls->id_kelas)) {
                $klss[] = [
                    'kelas_id' => $kls->id_kelas
                ];
            }
        }
        $rekap->bank_kelas = $klss;

        $soal_1 = array_filter($this->maybe_unserialize($rekap->soal_pg), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_2 = array_filter($this->maybe_unserialize($rekap->soal_kompleks), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_3 = array_filter($this->maybe_unserialize($rekap->soal_jodohkan), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_4 = array_filter($this->maybe_unserialize($rekap->soal_isian), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_5 = array_filter($this->maybe_unserialize($rekap->soal_esai), function ($soal) {
            return $soal->tampilkan == '1';
        });

        $rekap->soal_pg = array_values($soal_1);
        $rekap->soal_kompleks = array_values($soal_2);
        $rekap->soal_jodohkan = array_values($soal_3);
        $rekap->soal_isian = array_values($soal_4);
        $rekap->soal_esai = array_values($soal_5);

        $arrIdKelas = [];
        foreach ($rekap->kelas_bank as $kls) {
            if (isset($kls->id_kelas)) {
                $arrIdKelas[$kls->id_kelas] = $kls->nama_kelas;
            }
        }
        $rekap->pengawas = $this->maybe_unserialize($rekap->pengawas);

        $kunci = [];
        foreach ($rekap->soal_pg as $soal_pg) {
            $kunci[$soal_pg->id_soal] = strtoupper($soal_pg->jawaban ?? '');
        }
        $jawabanSiswa = [];
        if ($rekap->rekapsiswa) {
            foreach ($rekap->rekapsiswa as $rekapsiswa) {
                $rekapsiswa->pelanggaran = $this->maybe_unserialize($rekapsiswa->pelanggaran ?? '{}');
                $jawabans = $this->maybe_unserialize($rekapsiswa->jawaban);
                $answers = [];
                foreach ($jawabans as $jawaban) {
                    $jawab_siswa = $jawaban->jawaban_siswa;
                    $answers[$jawaban->id_soal] = $jawab_siswa;
                }
                $jawabanSiswa[] = $answers;
            }
        }
        $normalizedSiswa = $this->normalizeData($jawabanSiswa, $kunci);
        $hasilAnalisis = $this->analyzeData($normalizedSiswa, $kunci, $rekap->bank_soal->opsi);

        $arrIdSiswa = [];
        $rekap->rekapsiswa = (array)$rekap->rekapsiswa;
        foreach ($rekap->rekapsiswa as $ks=>$rekapsiswa) {
            $rekapsiswa->siswa = $this->maybe_unserialize($rekapsiswa->siswa ?? '{}');
            if ($rekap->bank_soal->soal_agama != '-' && $rekap->bank_soal->soal_agama != '0'
                && $rekap->bank_soal->soal_agama !== ($rekapsiswa->agama ?? $rekapsiswa->siswa->agama)) {
                unset($rekap->rekapsiswa[$ks]);
            } else {
                $arrIdSiswa[] = $rekapsiswa->id_siswa;
            }
        }

        $durasi = [];
        if (count($arrIdSiswa) > 0) {
            $durasies = $this->cbt_siswa_model
                ->fields('id_jadwal,id_siswa,mulai,selesai,lama_ujian')
                ->where(['id_jadwal'=>$jadwal])
                ->where('id_siswa', $arrIdSiswa)
                ->get_all();

            if ($durasies) {
                foreach ($durasies as $item) {
                    $durasi[$item->id_siswa] = $item;
                }
            }
        }

        $rekap->rekapsiswa = array_values($rekap->rekapsiswa);
        $this->createSkorSiswa($rekap, false);

        $data['analisis'] = $hasilAnalisis;
        $data['durasi_siswa'] = $durasi;
        $data['kunci'] = $kunci;
        $data['kelas'] = $arrIdKelas;
        $data['success'] = true;
        $data['arr_siswa'] = $arrIdSiswa;
        $data['rekap'] = $rekap;
        $data['kelas_selected'] = $kelas;
        $data['jadwal_selected'] = $jadwal;

        $this->output_json($data);
    }

    function unserialize_with_key($serialized) {
        $arr = $this->maybe_unserialize($serialized);
        $result = [];
        foreach ($arr as $value) {
            $result[$value['no_soal']] = $value['jawab'];
        }
        return $result;
    }

    public function export_all() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Ekspor Hasil Penilaian',
            'subjudul' => 'Ekspor Nilai',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        if($this->auth->is_admin()){
            $all_rekap = $this->rekap_jadwal
                ->fields('id_rekap, id_tp, tp, id_smt, smt, id_jadwal, id_jenis, kode_jenis, kelas_bank')
                ->get_all();

            if ($all_rekap) {
                $map_jenis = [];
                $map_kelas = [];
                $map_tahun = [];
                $map_smt = [];
                foreach ($all_rekap as $row) {
                    $map_tahun[$row->id_tp] = $row->tp;
                    $map_smt[$row->id_smt] = $row->smt;
                    $map_jenis[$row->id_tp][$row->id_smt][$row->id_jenis] = $row->kode_jenis;
                    $row->kelas_bank = $this->maybe_unserialize($row->kelas_bank);
                    foreach ($row->kelas_bank as $kls_bank) {
                        unset($kls_bank->jumlah_siswa);
                        if (isset($kls_bank->id_kelas)) $map_kelas[$row->id_tp][$row->id_smt][$row->id_jenis][$kls_bank->id_kelas] = $kls_bank->nama_kelas;
                    }
                    unset($row->kelas_bank);
                }
                $data['jenis'] = $map_jenis;//$this->cbt->getDistinctJenisUjian();
                $data['kelas'] = $map_kelas;//$this->cbt->getDistinctKelas();
                $data['tahuns'] = $map_tahun;//$this->cbt->getDistinctTahun();
                $data['semester'] = $map_smt;//$this->cbt->getDistinctSmt();

                $data['all_rekap'] = count((array)$all_rekap);
            }

            $this->render_view(1, 'cbt/rekap/ekspor_all', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $all_rekap = $this->rekap_jadwal
                ->fields('id_rekap, id_tp, tp, id_smt, smt, id_jadwal, id_jenis, kode_jenis, kelas_bank')
                ->where(['id_guru'=>$guru->id_guru])
                ->get_all();

            if ($all_rekap) {
                $map_jenis = [];
                $map_kelas = [];
                $map_tahun = [];
                $map_smt = [];
                foreach ($all_rekap as $row) {
                    $map_tahun[$row->id_tp] = $row->tp;
                    $map_smt[$row->id_smt] = $row->smt;
                    $map_jenis[$row->id_tp][$row->id_smt][$row->id_jenis] = $row->kode_jenis;
                    $row->kelas_bank = $this->maybe_unserialize($row->kelas_bank);
                    foreach ($row->kelas_bank as $kls_bank) {
                        unset($kls_bank->jumlah_siswa);
                        if (isset($kls_bank->id_kelas)) $map_kelas[$row->id_tp][$row->id_smt][$row->id_jenis][$kls_bank->id_kelas] = $kls_bank->nama_kelas;
                    }
                    unset($row->kelas_bank);
                }
                $data['jenis'] = $map_jenis;
                $data['kelas'] = $map_kelas;
                $data['tahuns'] = $map_tahun;
                $data['semester'] = $map_smt;

                $data['all_rekap'] = count((array)$all_rekap);
            }
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/rekap/ekspor_all', $data);
        }
    }

    /**
     * @throws JsonException
     */
    public function getNilaiKelas() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $this->load->model('Rekap_siswa_model', 'rekap_siswa');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');

        $kelas = $this->input->get('kelas');
        $level = $this->master->getKelasById($kelas);
        $jenis = $this->input->get('jenis');
        $tahun = $this->input->get('tahun');
        $smt = $this->input->get('smt');
        $mapel = $this->input->get('mapel');

        $user = $this->auth->user();
        $where_jadwal = [];
        if($this->auth->is_admin()){
            $where_jadwal = ['id_tp'=>$tahun, 'id_smt'=>$smt, 'id_jenis'=>$jenis];
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $where_jadwal = ['id_tp'=>$tahun, 'id_smt'=>$smt, 'id_jenis'=>$jenis, 'id_guru' => $guru->id_guru];
        }

        $rekap_jadwal = [];
        $rekap_siswa = [];
        $all_siswa = $this->sesisiswa_model
            ->where(['kelas_id'=>$kelas, 'tp_id'=>$tahun, 'smt_id'=>$smt])
            ->with_siswa(['fields'=>'id_siswa,nis,nisn,nama,agama'])
            ->with_peserta()
            ->get_all();

        $rekaps = $this->rekap_jadwal
            ->where($where_jadwal)
            ->with_rekapsiswa([
                'where'=>['id_kelas'=>$kelas],
                'with' => [
                    ['relation'=>'siswa', 'fields'=>'id_siswa,nama,nis,agama']
                ]
            ])
            ->get_all();

        foreach ($rekaps as $rekap) {
            $rekap->pengawas = $this->maybe_unserialize($rekap->pengawas);
            $rekap->jadwal = $this->maybe_unserialize($rekap->jadwal);
            $rekap->bank_soal = $this->maybe_unserialize($rekap->bank_soal);
            $rekap->kelas_bank = $this->maybe_unserialize($rekap->kelas_bank);
            $klss = [];
            foreach ($rekap->kelas_bank as $kls) {
                if (isset($kls->id_kelas)) {
                    $klss[] = [
                        'kelas_id' => $kls->id_kelas
                    ];
                }
            }
            $rekap->bank_kelas = $klss;

            $soal_1 = array_filter($this->maybe_unserialize($rekap->soal_pg), function ($soal) {
                return $soal->tampilkan == '1';
            });
            $soal_2 = array_filter($this->maybe_unserialize($rekap->soal_kompleks), function ($soal) {
                return $soal->tampilkan == '1';
            });
            $soal_3 = array_filter($this->maybe_unserialize($rekap->soal_jodohkan), function ($soal) {
                return $soal->tampilkan == '1';
            });
            $soal_4 = array_filter($this->maybe_unserialize($rekap->soal_isian), function ($soal) {
                return $soal->tampilkan == '1';
            });
            $soal_5 = array_filter($this->maybe_unserialize($rekap->soal_esai), function ($soal) {
                return $soal->tampilkan == '1';
            });

            $rekap->soal_pg = array_values($soal_1);
            $rekap->soal_kompleks = array_values($soal_2);
            $rekap->soal_jodohkan = array_values($soal_3);
            $rekap->soal_isian = array_values($soal_4);
            $rekap->soal_esai = array_values($soal_5);

            $rekap->rekapsiswa = isset($rekap->rekapsiswa) ? (array) $rekap->rekapsiswa : [];
            $this->createSkorSiswa($rekap, false);
        }

        $data['rekap_jadwal'] = array_values((array)$rekaps);
        $data['siswa'] = array_values((array)$all_siswa);
        $this->output_json($data);
    }

}

