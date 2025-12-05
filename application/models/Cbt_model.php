<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 17:34
 */

class Cbt_model extends CI_Model {

	public function get_where($table, $pk, $id, $join = null, $order = null) {
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($pk, $id);

		if($join !== null){
			foreach($join as $table => $field){
				$this->db->join($table, $field);
			}
		}

		if($order !== null){
			foreach($order as $field => $sort){
				$this->db->order_by($field, $sort);
			}
		}

		$query = $this->db->get();
		return $query;
	}

    /**
     * @param $id_siswa
     * @param $id_jadwal
     * @param $type
     * @param $desc
     * @return bool
     */
    public function saveLog($id_siswa, $id_jadwal, $type, $desc) {
		if ($this->agent->is_browser()){
			$agent = $this->agent->browser().' '.$this->agent->version();
		} elseif ($this->agent->is_mobile()){
			$agent = $this->agent->mobile();
		}else{
			return false;
		}

        $os = $this->agent->platform();
        $ip = $this->input->ip_address();
        return $this->insertLog($id_siswa, $id_jadwal, $type, $desc, $agent, $os, $ip);
    }

	/*
	 * SEMUA
	 * 1 = memulai ujian
	 * 2 = selesai ujian
	 */

    /**
     * @param $id_siswa
     * @param $id_jadwal
     * @param $type
     * @param $desc
     * @param $agent
     * @param $os
     * @param $ip
     * @return bool
     */
    private function insertLog($id_siswa, $id_jadwal, $type, $desc, $agent, $os, $ip) {
        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_jadwal', $id_jadwal);
        $this->db->where('log_type', $type);
        $log = $this->db->get('log_ujian')->row();
        if ($log != null) {
            $this->db->set('log_type', $type);
            $this->db->set('log_desc', $desc);
            $this->db->where('id_log', $log->id_log);
            $insert = $this->db->update('log_ujian');
        } else {
            $data = array(
                'id_siswa' => $id_siswa,
                'id_jadwal' => $id_jadwal,
                'log_type' => $type,
                'log_desc' => $desc,
                'address' => $ip,
                'agent' => $agent,
                'device' => $os,
            );

            $insert = $this->db->insert('log_ujian', $data);
        }
		return $insert;
	}

    public function getDataSiswa($username, $id_tp, $id_smt) {
        $this->db->select('a.id_siswa, a.nisn, a.nis, a.nama, a.jenis_kelamin, a.username, a.password, a.agama, a.foto,'.
            ' b.id_kelas_siswa, b.id_tp, b.id_smt, b.id_siswa, b.id_kelas,'.
            ' c.nama_kelas, c.kode_kelas, c.level_id, '.
            ' d.kelas_id, d.ruang_id, d.sesi_id');
        $this->db->from('master_siswa a');
        $this->db->join('kelas_siswa b', 'a.id_siswa=b.id_siswa AND b.id_tp='.$id_tp.' AND b.id_smt='.$id_smt, 'left');
        $this->db->join('master_kelas c', 'b.id_kelas=c.id_kelas AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt, 'left');
        $this->db->join('cbt_sesi_siswa d', 'a.id_siswa=d.siswa_id AND d.tp_id='.$id_tp.' AND d.smt_id='.$id_smt, 'left');
        $this->db->where('username', $username);
        return $this->db->get()->row();
    }

	public function getAllRuang() {
        $this->db->select('id_ruang, nama_ruang, kode_ruang');
		$result = $this->db->get('cbt_ruang')->result();
		$ret = [];
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_ruang] = $row->kode_ruang;
			}
		}
		return $ret;
	}

	public function getKelasByLevel($level, $arrKelas) {
		$this->db->select('id_kelas, kode_kelas');
		$this->db->where('level_id', $level);
		$this->db->where_in('id_kelas', $arrKelas);
        return $this->db->get('master_kelas')->result();
	}

	public function getAllJurusan() {
		$result = $this->db->get('master_jurusan')->result();
        $ret = [];
        if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_jurusan] = $row->kode_jurusan;
			}
		}
		return $ret;
	}

    public function getPengawas($id_pengawas) {
        $this->db->select('id_pengawas, id_jadwal, id_tp, id_smt, id_ruang, id_sesi, id_guru, jam_ke');
        $this->db->from('cbt_pengawas');
        $this->db->where('id_pengawas', $id_pengawas);
        return $this->db->get()->row();
    }

    public function getPengawasByGuru($tp, $smt, $id_guru) {
        $this->db->select('a.id_pengawas, a.id_jadwal, a.id_tp, a.id_smt, a.id_ruang, a.id_sesi, a.id_guru,a.jam_ke'.
            ' b.id_jadwal, b.tgl_mulai, b.tgl_selesai, c.bank_kode, d.kode_jenis');
        $this->db->from('cbt_pengawas a');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->like('a.id_guru', $id_guru);
        $this->db->join('cbt_jadwal b', 'b.id_jadwal=a.id_jadwal');
        $this->db->join('cbt_bank_soal c', 'b.id_bank=c.id_bank');
        $this->db->join('cbt_jenis d', 'd.id_jenis=b.id_jenis', "left");
        return $this->db->get()->result();
    }

    public function getPengawasByJadwal($tp, $smt, $id_jadwal, $sesi=null, $ruang=null) {
        $this->db->select('id_pengawas, id_guru, id_jadwal, id_ruang, id_sesi, jam_ke');
        $this->db->from('cbt_pengawas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        if (is_array($id_jadwal)) {
            $this->db->where_in('id_jadwal', $id_jadwal);
        } else {
            $this->db->where('id_jadwal', $id_jadwal);
        }
        if ($sesi != null) {
            $this->db->where('id_sesi', $sesi);
        }
        if ($ruang != null) {
            $this->db->where('id_ruang', $ruang);
        }
        return $this->db->get()->result();
    }

    public function getAllPengawas($tp, $smt, $ruang=null, $sesi=null) {
        $this->db->select('id_pengawas, id_jadwal, id_ruang, id_sesi, id_guru, jam_ke');
        $this->db->from('cbt_pengawas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        if ($ruang!=null) $this->db->where('id_ruang', $ruang);
        if ($sesi!=null) $this->db->where('id_sesi', $sesi);
        $this->db->order_by('jam_ke', 'ASC');
		$result = $this->db->get()->result();
		$ret = [];
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_jadwal][$row->id_ruang][$row->id_sesi] = $row;
			}
		}
		return $ret;
	}

    public function getArrPengawas($tp, $smt, $ruang=null, $sesi=null) {
        $this->db->select('id_pengawas, id_jadwal, id_ruang, id_sesi, id_guru, jam_ke');
        $this->db->from('cbt_pengawas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        if ($ruang!=null) $this->db->where('id_ruang', $ruang);
        if ($sesi!=null) $this->db->where('id_sesi', $sesi);
        return $this->db->get()->result();
    }

    public function getDistinctRuang($tp, $smt, $arrKelas) {
        $this->db->distinct('a.ruang_id');
        $this->db->select('a.ruang_id, a.sesi_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $this->db->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        $this->db->where("a.tp_id", $tp);
        $this->db->where("a.smt_id", $smt);

        if (count($arrKelas)>0) $this->db->where_in('kelas_id', $arrKelas);
        $this->db->order_by('b.nama_ruang', 'ASC');
        $this->db->order_by('c.nama_sesi', 'ASC');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->ruang_id][$row->sesi_id] = $row;
            }
        }
        return $ret;
    }

    public function getDistinctSesi($tp, $smt, $ids_kelas) {
        $this->db->select('a.siswa_id, a.ruang_id, a.sesi_id, a.kelas_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi, c.waktu_mulai, c.waktu_akhir, c.istirahat, d.nama_kelas');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $this->db->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        $this->db->join('master_kelas d', 'd.id_kelas=a.kelas_id');
        $this->db->where('a.tp_id', $tp);
        $this->db->where('a.smt_id', $smt);
        if ($ids_kelas) {
            $this->db->where_in('a.kelas_id', $ids_kelas);
        }
        $this->db->order_by('b.nama_ruang', 'ASC');
        $this->db->order_by('c.nama_sesi', 'ASC');
        $result = $this->db->get()->result();
        $sesis = [];
        if ($result) {
            foreach ($result as $row) {
                $key = $row->sesi_id.'-'.$row->ruang_id;
                if (!isset($sesis[$key])) {
                    $sesis[$key] = (object)[
                        'sesi_id' => $row->sesi_id,
                        'kode_sesi' => $row->kode_sesi,
                        'nama_sesi' => $row->nama_sesi,
                        'waktu_mulai' => $row->waktu_mulai,
                        'waktu_akhir' => $row->waktu_akhir,
                        'ruang_id' => $row->ruang_id,
                        'kode_ruang' => $row->kode_ruang,
                        'nama_ruang' => $row->nama_ruang,
                        'istirahat' => $row->istirahat,
                        'kelas_id' => [],
                        'nama_kelas' => [],
                        'ids_siswa' => [],
                        'peserta' => 0
                    ];
                }
                if (!isset($sesis[$key]->nama_kelas[$row->kelas_id])) {
                    $sesis[$key]->nama_kelas[$row->kelas_id] = $row->nama_kelas;
                    $sesis[$key]->kelas_id[] = $row->kelas_id;
                }
                $sesis[$key]->ids_siswa[] = $row->siswa_id;
                $sesis[$key]->peserta += 1;
            }
        }
        return $sesis;
    }

    public function getKelasUjian($tp, $smt, $kelas_id) {
        //$this->db->distinct('kelas_id');
        $this->db->select('kelas_id, ruang_id, sesi_id');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('kelas_siswa b', 'a.siswa_id=b.id_siswa');
        $this->db->where('a.tp_id', $tp);
        $this->db->where('a.smt_id', $smt);
        if (is_array($kelas_id)) {
            $this->db->where_in('b.id_kelas', $kelas_id);
        } else {
            $this->db->where('b.id_kelas', $kelas_id);
        }
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->kelas_id][$row->ruang_id][$row->sesi_id][] = $row->kelas_id;
            }
        }
        return $ret;
    }

    public function getDistinctKelasLevel($tp, $smt, $arrLevel) {
        $this->db->select('id_kelas, level_id');
        $this->db->distinct();
        $this->db->from('master_kelas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->where_in('level_id', $arrLevel);
        return $this->db->get()->result();
    }

    public function getAllJenisUjian() {
        $this->db->select('id_jenis, nama_jenis, kode_jenis');
		$result = $this->db->get('cbt_jenis')->result();
		$ret [''] = 'Jenis Penilaian :';
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_jenis] = $row->kode_jenis;
			}
		}
		return $ret;
	}

    public function getAllJenisUjianByArrJenis($arrJenis) {
        $this->db->where_in('id_jenis', $arrJenis);
        $result = $this->db->get('cbt_jenis')->result();
        $ret [''] = 'Jenis Penilaian :';
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_jenis] = $row->kode_jenis;
            }
        }
        return $ret;
    }

    public function getPengawasHariIni($tgl) {
        $this->db->from('cbt_jadwal a');
        $this->db->where("a.tgl_mulai <= '$tgl' AND a.tgl_selesai >= '$tgl'");
        $this->db->join('cbt_pengawas b', 'b.id_jadwal=a.id_jadwal');
        $this->db->where('status', '1');
        return $this->db->get()->result();
    }

    public function getJadwalGuru($tp, $smt, $guru) {
        $this->db->select('a.id_jadwal, a.tgl_mulai, b.bank_kode, b.bank_kelas');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank AND b.bank_guru_id='.$guru);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        return $this->db->get()->result();
    }

    public function getJadwalKelas($tp, $smt) {
        $this->db->select('a.id_jadwal, a.tgl_mulai, b.bank_kode, b.bank_kelas');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        return $this->db->get()->result();
    }

    public function getJadwalByJenis($tp, $smt, $jenis, $dari = null, $sampai = null, $level = null) {
        $this->db->select('a.id_jadwal, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.jam_ke,'.
            ' c.bank_kode, c.bank_level, c.bank_kelas, b.kode_jenis, b.nama_jenis, d.kode, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->where('a.id_jenis', $jenis);
        if ($level != null) $this->db->where('c.bank_level', $level);
        if ($dari != null) $this->db->where('a.tgl_mulai >=', $dari);
        if ($sampai != null) $this->db->where('a.tgl_mulai <=', $sampai);
        $this->db->order_by('a.tgl_mulai', 'ASC');
        $this->db->order_by('a.jam_ke', 'ASC');

        return $this->db->get()->result();
    }

    public function getAllJadwalByJenis($tp, $smt, $jenis = null) {
        $this->db->select('a.id_jadwal, a.id_jenis, a.tgl_mulai, '.
            'c.bank_kode, c.bank_level, c.bank_kelas, c.soal_agama, b.kode_jenis, b.nama_jenis, d.id_mapel, d.kode, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        if ($jenis != null) $this->db->where('a.id_jenis', $jenis);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->order_by('a.tgl_mulai', 'ASC');
        $this->db->order_by('a.jam_ke', 'ASC');
        $this->db->order_by('c.bank_level', 'ASC');

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->tgl_mulai][$row->id_mapel][] = $row;
            }
        }
        return $ret;
    }

    public function getAllJadwalByTanggal($tp, $smt, $jenis, $id_guru = null) {
        $this->db->select('a.id_jadwal, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.jam_ke, '.
            'c.bank_kode, c.bank_level, c.bank_kelas, c.bank_guru_id, c.soal_agama, b.kode_jenis, b.nama_jenis, d.id_mapel, d.kode, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        if ($id_guru != null) $this->db->where('c.bank_guru_id', $id_guru);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->where('a.id_jenis', $jenis);
        $this->db->order_by('a.tgl_mulai', 'ASC');
        //$this->db->order_by('a.jam_ke', 'ASC');
        $this->db->order_by('c.bank_level', 'ASC');

        return $this->db->get()->result();
    }

    public function getAllBankSoalByMapel($id_tp, $id_smt, $mapel, $guru = null) {
        $this->db->select('a.id_bank, a.bank_kode, a.bank_kelas, a.bank_mapel_id, a.tampil_pg, a.tampil_kompleks,'.
            ' a.tampil_jodohkan, a.tampil_isian, a.tampil_esai, a.status, b.id_jadwal');
        $this->db->from('cbt_bank_soal a');
        $this->db->join('cbt_jadwal b', 'b.id_bank=a.id_bank', 'left');
        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.bank_mapel_id', $mapel);
        $this->db->where('a.status', '1');
        if ($guru) {
            $this->db->where('a.bank_guru_id', $guru);
        }
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_bank] = $row;
            }
        }
        return $ret;
    }

    public function getJumlahJenisSoal($id_bank) {
        $this->db->select('id_soal, jenis');
        $this->db->from('cbt_soal');
        $this->db->where('bank_id', $id_bank);
        $this->db->where('tampilkan', '1');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret [$row->jenis][] = $row;
            }
        }
        return $ret;
    }

    public function getJenis() {
        $this->datatables->select('*');
        $this->datatables->from('cbt_jenis');
        return $this->datatables->generate();
    }

    public function getJenisById($id) {
		$this->db->select('id_jenis, nama_jenis, kode_jenis');
		$this->db->from('cbt_jenis');
		$this->db->where(['id_jenis' => $id]);
		return $this->db->get()->row();
	}

	function updateJenis() {
		$id = $this->input->post('id_jenis');
		$name = $this->input->post('nama_jenis', true);
		$kode = $this->input->post('kode_jenis', true);

		$this->db->set('nama_jenis', $name);
		$this->db->set('kode_jenis', $kode);
		$this->db->where('id_jenis', $id);
		return $this->db->update('cbt_jenis');
	}

    public function getRuang() {
        $this->datatables->select('*, (SELECT COUNT(id_sesi) FROM cbt_sesi) AS jum_sesi');
        $this->datatables->from('cbt_ruang');
        return $this->datatables->generate();
    }

    public function getRuangById($id) {
		$this->db->select('id_ruang, nama_ruang, kode_ruang');
		$this->db->from('cbt_ruang');
		$this->db->where(['id_ruang' => $id]);
		return $this->db->get()->row();
	}

    public function getRuangSesi($tp, $smt) {
        $this->db->select('a.siswa_id, a.sesi_id, a.ruang_id, a.kelas_id, '.
            'b.nama_ruang, b.kode_ruang, c.nama_sesi, c.kode_sesi, d.nama_kelas');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->where('a.tp_id', $tp);
        $this->db->where('a.smt_id', $smt);
        $this->db->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $this->db->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        $this->db->join('master_kelas d', 'd.id_kelas=a.kelas_id');
        $this->db->order_by('b.nama_ruang', 'ASC');
        $this->db->order_by('c.nama_sesi', 'ASC');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->sesi_id][$row->ruang_id][$row->kelas_id] = $row->nama_kelas;
            }
        }
        return $ret;
    }

	function updateRuang() {
		$id = $this->input->post('id_ruang');
		$name = $this->input->post('nama_ruang', true);
		$kode = $this->input->post('kode_ruang', true);

		$this->db->set('nama_ruang', $name);
		$this->db->set('kode_ruang', $kode);
		$this->db->where('id_ruang', $id);
		return $this->db->update('cbt_ruang');
	}

    public function getSesi() {
        $this->datatables->select('*');
        $this->datatables->from('cbt_sesi c');
        return $this->datatables->generate();
    }

    public function getAllKodeSesi() {
        $this->db->select('id_sesi, nama_sesi, kode_sesi, waktu_mulai, waktu_akhir');
        $this->db->from('cbt_sesi');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret [$row->kode_sesi] = $row;
            }
        }
        return $ret;
    }

	public function getSesiById($id) {
		$this->db->select('id_sesi, nama_sesi, kode_sesi, waktu_mulai, waktu_akhir, istirahat');
		$this->db->from('cbt_sesi');
		$this->db->where(['id_sesi' => $id]);
		return $this->db->get()->row();
	}

	function updateSesi() {
		$id = $this->input->post('id_sesi');
		$name = $this->input->post('nama_sesi', true);
		$kode = $this->input->post('kode_sesi', true);
		$mulai = $this->input->post('waktu_mulai', true);
		$akhir = $this->input->post('waktu_akhir', true);
        $istirahat = $this->input->post('istirahat', true);

		$this->db->set('nama_sesi', $name);
		$this->db->set('kode_sesi', $kode);
		$this->db->set('waktu_mulai', $mulai);
		$this->db->set('waktu_akhir', $akhir);
        $this->db->set('istirahat', $istirahat);
		$this->db->set('aktif', 1);
		$this->db->where('id_sesi', $id);
		return $this->db->update('cbt_sesi');
	}

    public function getSiswaCbtInfo($id_siswa, $id_tp, $id_smt) {
        $this->db->select('a.id_kelas_siswa, a.id_tp, a.id_smt, a.id_siswa, a.id_kelas,'.
            ' b.siswa_id, b.kelas_id, b.ruang_id, b.sesi_id,'.
            ' rk.id_ruang, rk.nama_ruang, rk.kode_ruang,'.
            ' sk.id_sesi, sk.nama_sesi, sk.kode_sesi, sk.waktu_mulai, sk.waktu_akhir');
        $this->db->from('kelas_siswa a');
        $this->db->join('cbt_sesi_siswa b', 'a.id_siswa=b.siswa_id AND a.id_tp = b.tp_id AND a.id_smt = b.smt_id', 'left');
        $this->db->join('cbt_ruang rk', 'b.ruang_id=rk.id_ruang', 'left');
        $this->db->join('cbt_sesi sk', 'b.sesi_id=sk.id_sesi', 'left');
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->where("a.id_tp", $id_tp);
        $this->db->where("a.id_smt", $id_smt);

        return $this->db->get()->row();
    }

    public function getRuangSesiSiswa($id_kelas, $id_tp, $id_smt){
		$this->db->select('a.id_siswa, a.id_kelas,'.
            ' b.nama, b.nis, b.username,'.
            ' c.nama_kelas, c.kode_kelas,'.
			' e.id_sesi_siswa, e.siswa_id, e.sesi_id, e.ruang_id,'.
			' rk.id_ruang, rk.kode_ruang,'.
			' sk.id_sesi, sk.kode_sesi');
		$this->db->from('kelas_siswa a');
		$this->db->join('master_siswa b', 'a.id_siswa=b.id_siswa', 'left');
		$this->db->join('master_kelas c', 'a.id_kelas=c.id_kelas', 'left');
        $this->db->join('cbt_sesi_siswa e', 'a.id_siswa=e.siswa_id AND a.id_tp = e.tp_id AND a.id_smt = e.smt_id', 'left');
		$this->db->join('cbt_ruang rk', 'e.ruang_id=rk.id_ruang', 'left');
		$this->db->join('cbt_sesi sk', 'e.sesi_id=sk.id_sesi', 'left');
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND i.status="1"');

		$this->db->where("a.id_kelas", $id_kelas);
		$this->db->where("a.id_tp", $id_tp);
		$this->db->where("a.id_smt", $id_smt);
        $this->db->order_by('b.nama', 'ASC');

		return $this->db->get()->result();
	}

	public function getSiswaByKelas($id_tp, $id_smt, $id_kelas) {
		$this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.agama,'.
			' b.foto, c.kelas_id, c.ruang_id, c.sesi_id, d.kode_ruang, e.kode_sesi, f.nama_kelas, f.kode_kelas, g.nomor_peserta');
		$this->db->from('kelas_siswa a');
		$this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa', 'left');
        $this->db->join('cbt_sesi_siswa c', 'c.siswa_id=a.id_siswa AND a.id_tp = c.tp_id AND a.id_smt = c.smt_id', 'left');
		$this->db->join('cbt_ruang d', 'd.id_ruang=c.ruang_id', 'left');
		$this->db->join('cbt_sesi e', 'e.id_sesi=c.sesi_id', 'left');
		$this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas', 'left');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp='.$id_tp. ' AND g.id_smt='.$id_smt, 'left');
        $this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND i.status="1"');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_siswa is NOT NULL', NULL, FALSE);
        $this->db->where('b.id_siswa is NOT NULL', NULL, FALSE);
        $this->db->where('c.siswa_id is NOT NULL', NULL, FALSE);
        //$this->db->where('f.siswa_id is NOT NULL', NULL, FALSE);
        $this->db->where('g.id_siswa is NOT NULL', NULL, FALSE);

		if (is_array($id_kelas)) {
            $this->db->where_in('a.id_kelas', $id_kelas);
        } else {
            $this->db->where('a.id_kelas', $id_kelas);
        }
        $this->db->order_by('b.nama', 'ASC');

		return $this->db->get()->result();
	}

    public function getAllPesertaByRuang($id_tp, $id_smt) {
        $this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto, f.level_id,'.
            ' f.nama_kelas, f.kode_kelas, f.id_kelas,'.
            ' d.nama_ruang, d.kode_ruang, d.id_ruang,'.
            ' e.kode_sesi, e.nama_sesi, id_sesi,'.
            ' g.nomor_peserta');

        $this->db->from('cbt_sesi_siswa a');
        $this->db->where('a.tp_id', $id_tp);
        $this->db->where('a.smt_id', $id_smt);

        $this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp. ' AND g.id_smt='.$id_smt, 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

        $this->db->order_by('d.kode_ruang');
        $this->db->order_by('e.kode_sesi');
        $this->db->order_by('f.level_id');
        $this->db->order_by('f.kode_kelas');
        $this->db->order_by('b.nama');

        $result = $this->db->get()->result();
        if (count($result) > 0) {
            usort($result, function($a, $b) {
                return strnatcmp($a->nomor_peserta, $b->nomor_peserta);
            });
        }
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_ruang][$row->kode_sesi][] = $row;
        }
        if (count($ret) > 0) {
            ksort($ret);
            foreach ($ret as $items) {
                ksort($items);
            }
        }
        return $ret;

    }

    public function getAllPesertaByKelas($id_tp, $id_smt) {
        $this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto,'.
            ' f.nama_kelas, f.kode_kelas,'.
            ' d.nama_ruang, d.kode_ruang,'.
            ' e.kode_sesi, e.nama_sesi,'.
            ' g.nomor_peserta');

        $this->db->from('cbt_sesi_siswa a');
        $this->db->where('a.tp_id', $id_tp);
        $this->db->where('a.smt_id', $id_smt);
        $this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp. ' AND g.id_smt='.$id_smt, 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

        $this->db->order_by('f.level_id');
        $this->db->order_by('f.kode_kelas');
        //$this->db->order_by('d.kode_ruang');
        //$this->db->order_by('e.kode_sesi');
        $this->db->order_by('b.nama');

        $result = $this->db->get()->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_kelas][] = $row;
        }
        return $ret;

    }

    public function getSiswaByRuang($id_tp, $id_smt, $id_ruang, $sesi, $level = null) {
		$this->db->select('a.ruang_id, a.sesi_id, b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto, b.agama,'.
			' f.id_kelas, f.nama_kelas, f.kode_kelas,'.
            ' d.nama_ruang, d.kode_ruang,'.
			' e.kode_sesi, e.nama_sesi,'.
            ' us.active,'.
            ' (SELECT COUNT(login) FROM login_attempts WHERE login_attempts.login = b.username) AS reset,'.
			' g.nomor_peserta');

		$this->db->from('cbt_sesi_siswa a');
        $this->db->where('a.tp_id', $id_tp);
        $this->db->where('a.smt_id', $id_smt);

        $this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
		$this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp. ' AND g.id_smt='.$id_smt, 'left');
		$this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');
        $this->db->join('users us', 'us.username=b.username');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        if ($level == null) {
            $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        } else {
            $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas'.' AND f.level_id='.$level.'');
        }
        $this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

		$this->db->where('a.ruang_id', $id_ruang);
		// Make sesi optional - only filter by sesi if provided
		if ($sesi !== null && $sesi !== 'null') {
			$this->db->where('a.sesi_id', $sesi);
		}
		$this->db->order_by('b.nama');

		return $this->db->get()->result();
	}

	public function getRuangSiswaByKelas($id_tp, $id_smt, $kelas, $sesi = null) {
        $this->db->select('a.ruang_id, a.sesi_id, b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto,'.
            ' f.id_kelas, f.nama_kelas, f.kode_kelas,'.
            ' d.nama_ruang, d.kode_ruang,'.
            ' e.kode_sesi, e.nama_sesi, e.istirahat,'.
            ' g.nomor_peserta');

        $this->db->from('cbt_sesi_siswa a');
        $this->db->where('a.tp_id', $id_tp);
        $this->db->where('a.smt_id', $id_smt);

        $this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp. ' AND g.id_smt='.$id_smt, 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        //$this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND i.status="1"');

        $this->db->where_in('a.kelas_id', $kelas);
        if ($sesi!=null) $this->db->where('a.sesi_id', $sesi);
        $this->db->order_by('b.nama');

        return $this->db->get()->result();
	}

	public function getSiswaByKelasArray($id_tp, $id_smt, $arr_kelas) {
		$this->db->select('a.id_siswa, a.id_kelas,'.
            ' b.nama, b.nis, b.nisn, b.username, b.password, b.agama,'.
            ' f.nama_kelas, f.kode_kelas, l.level, g.nomor_peserta');
		$this->db->from('kelas_siswa a');
		$this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa');
		$this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas');
		$this->db->join('level_kelas l', 'l.id_level=f.level_id');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp='.$id_tp. ' AND g.id_smt='.$id_smt, 'left');
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND i.status="1"');
		if (!in_array('Semua', $arr_kelas)) {
			$this->db->where_in('a.id_kelas', $arr_kelas);
		}
		$this->db->where('a.id_tp', $id_tp);
		$this->db->where('a.id_smt', $id_smt);
		$this->db->order_by('l.level', 'ASC');
        $this->db->order_by('f.kode_kelas', 'ASC');
        $this->db->order_by('b.nama', 'ASC');

		return $this->db->get()->result();
	}

	public function getKelas($tp = null, $smt = null) {
		$this->db->select('a.id_kelas, a.nama_kelas, a.kode_kelas, b.level');
		$this->db->from('master_kelas a');
        $this->db->join('level_kelas b', 'b.id_level=a.level_id', 'left');

        if ($tp != null) $this->db->where('a.id_tp', $tp);
        if ($smt != null) $this->db->where('a.id_smt', $smt);
		$this->db->order_by('a.nama_kelas', 'ASC');
		return $this->db->get()->result();
	}

    public function getDataTableBank($guru = null) {
        $this->datatables->select('a.id_bank, a.bank_kode, a.bank_level, a.tampil_pg, a.tampil_esai, a.status, b.nama_mapel, c.nama_guru');
        $this->datatables->from('cbt_bank_soal a');
        $this->datatables->join('master_mapel b', 'b.id_mapel=a.bank_mapel_id', "left");
        $this->datatables->join('master_guru c', 'c.id_guru=a.bank_guru_id', "left");
        $this->datatables->join('master_jurusan d', 'd.id_jurusan=a.bank_jurusan_id', "left");
        $this->datatables->join('cbt_jenis e', 'e.id_jenis=a.bank_jenis_id', "left");
        if ($guru !== null) {
            $this->datatables->where('a.bank_guru_id', $guru);
        }
        return $this->datatables->generate();
    }

	public function getDataBank($guru = null, $mapel=null, $level=null) {
		$this->db->select('a.id_bank, a.id_tp, a.id_smt, a.bank_kode, a.bank_level, a.bank_kelas, a.date, a.status,'.
            ' a.tampil_pg, a.tampil_kompleks, a.tampil_jodohkan, a.tampil_isian, a.tampil_esai, a.bank_guru_id, a.soal_agama,'.
            ' b.nama_mapel, c.id_guru, c.nama_guru,'.
            ' (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal,'.
            ' (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan');
		$this->db->from('cbt_bank_soal a');
		$this->db->join('master_mapel b', 'b.id_mapel=a.bank_mapel_id', "left");
		$this->db->join('master_guru c', 'c.id_guru=a.bank_guru_id', "left");
		if ($guru !== null) {
			$this->db->where('a.bank_guru_id', $guru);
		}
        if ($mapel !== null) {
            $this->db->where('a.bank_mapel_id', $mapel);
        }
        if ($level !== null) {
            $this->db->where('a.bank_level', $level);
        }
        $this->db->order_by('a.bank_level', 'ASC');
        //$this->db->order_by('a.date', 'ASC');
		$result = $this->db->get()->result();
		//return $query->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_tp][$row->id_smt][] = $row;
        }
        return $ret;

    }

	public function getDataBankById($id) {
		$this->db->select('a.*, b.nama_mapel, b.kode, c.nama_guru, d.nama_jurusan, d.kode_jurusan,'.
            ' (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan');
		$this->db->from('cbt_bank_soal a');
		$this->db->join('master_mapel b', 'b.id_mapel=a.bank_mapel_id', "left");
		$this->db->join('master_guru c', 'c.id_guru=a.bank_guru_id', "left");
		$this->db->join('master_jurusan d', 'd.id_jurusan=a.bank_jurusan_id', "left");
		//$this->db->join('cbt_soal e', 'e.bank_id=a.id_bank', "left");
		$this->db->where('a.id_bank', $id);
		return $this->db->get()->row();
	}

    public function getBankByKode($bank_kode, $id_tp = null, $id_smt = null) {
        $this->db->select('id_bank, id_tp, id_smt, bank_kode');
        $this->db->from('cbt_bank_soal');
        if ($id_tp) $this->db->where('id_tp', $id_tp);
        if ($id_smt) $this->db->where('id_smt', $id_smt);
        $this->db->where('id_smt', $id_smt);
        $this->db->where('bank_kode', $bank_kode);
        return $this->db->get()->row();
    }

    public function getNomorSoalById($id_soal) {
        $this->db->select('nomor_soal, jenis, bank_id');
        $this->db->where('id_soal', $id_soal);
        return $this->db->get('cbt_soal')->row();
    }

    public function getFileSoalById($id_soal) {
		$this->db->select('file');
        $this->db->where('id_soal', $id_soal);
		return $this->db->get('cbt_soal')->row();
	}

    public function getAllSoalByBank($id_bank, $jenis=null) {
        $this->db->select('id_soal, bank_id, mapel_id, jenis, nomor_soal, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan');
        $this->db->where('bank_id', $id_bank);
        if ($jenis!=null) {
            $this->db->where('jenis', $jenis);
        }
        $this->db->order_by('nomor_soal', 'ASC');
        return $this->db->get('cbt_soal')->result();
    }

	public function getSoalByNomor($id_bank, $nomor, $jenis) {
		$this->db->select('*');
		$this->db->where('bank_id', $id_bank);
		$this->db->where('nomor_soal', $nomor);
		$this->db->where('jenis', $jenis);
		return $this->db->get('cbt_soal')->row();
	}

    public function getNomorSoalByBankJenis($id_bank, $jenis) {
        $this->db->select('id_soal, jenis, nomor_soal');
        $this->db->where('bank_id', $id_bank);
        $this->db->where('jenis', $jenis);
        $result = $this->db->get('cbt_soal')->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->nomor_soal] = $row;
        }
        return $ret;
    }

	public function cekSoalAda($id_bank, $jenis) {
        $this->db->select('id_soal, bank_id, jenis, nomor_soal');
		$this->db->where('bank_id', $id_bank);
        $this->db->where('jenis', $jenis);
		return $this->db->get('cbt_soal')->result();
	}

    public function cekSoalBelumKomplit($jenis, $opsi_ganda) {
        $this->db->select('id_soal, bank_id, jenis, nomor_soal, mapel_id');
        $this->db->from('cbt_soal');
        $this->db->where('jenis', $jenis);
        $this->db->where('soal IS NULL')->or_where('soal =""');

        if ($jenis == "1") {
            $this->db->where('opsi_a IS NULL')->or_where('opsi_a =""');
            $this->db->where('opsi_b IS NULL')->or_where('opsi_b =""');
            $this->db->where('opsi_c IS NULL')->or_where('opsi_c =""');
            if ($opsi_ganda == '4') {
                $this->db->where('opsi_d IS NULL')->or_where('opsi_d =""');
            }
            if ($opsi_ganda == '5') {
                $this->db->where('opsi_d IS NULL')->or_where('opsi_d =""');
                $this->db->where('opsi_e IS NULL')->or_where('opsi_e =""');
            }
        }

        if ($jenis == "2") {
            $this->db->where('opsi_a IS NULL')->or_where('opsi_a =""');
        }

        $this->db->where('jawaban IS NULL')->or_where('jawaban =""');
        $ret = [];
        $result = $this->db->get()->result();
        foreach ($result as $key => $row) {
            $ret[$row->bank_id][] = $row;
        }
        return $ret;
    }

    public function getNomorSoalTerbesar($id_bank, $jenis) {
        $this->db->select('nomor_soal');
        $this->db->where('bank_id', $id_bank)->where('jenis', $jenis);
        $this->db->order_by('nomor_soal', 'DESC');
        return $this->db->get('cbt_soal')->row();
    }

	public function dummy($jenjang) {
		$data = array(
			'id_bank' => '',
			'bank_jenis_id' => '',
			'bank_kode' => '',
			'bank_mapel_id' => '',
			'bank_level' => '',
			'bank_kelas' => [],
			'bank_guru_id' => '',
			'jml_soal' => '0',
			'bobot_pg' => '0',
			'tampil_pg' => '0',
			'opsi' => $jenjang == '1' ? '3' : ($jenjang == '2' ? '4' : ($jenjang == '3' ? '5' : '')),

            'jml_kompleks' => '0',
            'tampil_kompleks' => '0',
            'bobot_kompleks' => '0',

            'jml_jodohkan' => '0',
            'tampil_jodohkan' => '0',
            'bobot_jodohkan' => '0',

            'jml_isian' => '0',
            'tampil_isian' => '0',
            'bobot_isian' => '0',

            'jml_esai' => '0',
			'bobot_esai' => '0',
			'tampil_esai' => '0',
			'kkm' => '',
            'soal_agama' => '-',
			'status' => '1'
		);
		return $data;
	}

    public function saveBankSoal($tp, $smt) {
		$id = $this->input->post('id_bank', true);
		$rows = count($this->input->post('kelas', true));
		//$jml_pg = strip_tags($this->input->post('jml_soal', TRUE));
		//$jml_esai = strip_tags($this->input->post('jml_esai', TRUE));

		$this->load->model('Cbt_bank_model', 'bank_model');

		$kelas = [];
		for ($i = 0; $i <= $rows; $i++) {
			$kelas[] = [
				'kelas_id' => $this->input->post('kelas[' . $i . ']', true)
			];
		}
		$jumlah = json_encode($kelas);
		$data = array(
		    'id_tp' => $tp,
			'id_smt' => $smt,
			'bank_kode' => strip_tags($this->input->post('kode', TRUE) ?? ''),
			'bank_jenis_id' => strip_tags($this->input->post('jenis', TRUE) ?? ''),
			'bank_mapel_id' => strip_tags($this->input->post('mapel', TRUE) ?? ''),
			'bank_kelas' => $jumlah,
			'bank_level' => $this->input->post('level', TRUE),
			'bank_guru_id' => strip_tags($this->input->post('guru', TRUE) ?? ''),
			'jml_soal' => strip_tags($this->input->post('tampil_pg', TRUE) ?? ''),
            'tampil_pg' => strip_tags($this->input->post('tampil_pg', TRUE) ?? ''),
			'bobot_pg' => strip_tags($this->input->post('bobot_pg', TRUE) ?? ''),
			'opsi' => strip_tags($this->input->post('opsi', TRUE) ?? ''),

            'jml_kompleks' => strip_tags($this->input->post('tampil_kompleks', TRUE) ?? ''),
            'tampil_kompleks' => strip_tags($this->input->post('tampil_kompleks', TRUE) ?? ''),
            'bobot_kompleks' => strip_tags($this->input->post('bobot_kompleks', TRUE) ?? ''),

            'jml_jodohkan' => strip_tags($this->input->post('tampil_jodohkan', TRUE) ?? ''),
            'tampil_jodohkan' => strip_tags($this->input->post('tampil_jodohkan', TRUE) ?? ''),
            'bobot_jodohkan' => strip_tags($this->input->post('bobot_jodohkan', TRUE) ?? ''),

            'jml_isian' => strip_tags($this->input->post('tampil_isian', TRUE) ?? ''),
            'tampil_isian' => strip_tags($this->input->post('tampil_isian', TRUE) ?? ''),
            'bobot_isian' => strip_tags($this->input->post('bobot_isian', TRUE) ?? ''),

			'jml_esai' => strip_tags($this->input->post('bobot_esai', TRUE) ?? ''),
			'bobot_esai' => strip_tags($this->input->post('bobot_esai', TRUE) ?? ''),
			'tampil_esai' => strip_tags($this->input->post('tampil_esai', TRUE) ?? ''),
			//'kkm' => strip_tags($this->input->post('kkm', TRUE)),
			'status' => strip_tags($this->input->post('status', TRUE) ?? ''),
            'soal_agama' => strip_tags($this->input->post('soal_agama', TRUE) ?? ''),
		);

		$this->db->trans_begin();

		if (!$id) {
			$result = $this->bank_model->insert($data);
		} else {
			$result = $this->bank_model->update($data, $id);
		}

		if ($this->db->trans_status() === false || $result === false) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();
		return $result;
	}

	public function dummyJadwal() {
        return array(
            'id_bank' => '',
            'id_jadwal' => '',
            'id_jenis' => '',
            'tgl_mulai' => '',
            'tgl_selesai' => '',
            'durasi_ujian' => '',
            'bank_kelas' => json_encode([]),
            //'pengawas' => json_encode([]),
            'acak_soal' => '',
            'acak_opsi' => '',
            'hasil_tampil' => '',
            'token' => '',
            'status' => '',
            'ulang' => '',
            'jarak' => '',
            'reset_login' => '',
            'pelanggaran' => '',
        );
	}

    public function getDistinctJenisJadwal($tp, $smt) {
        $this->db->select('id_jenis');
        $this->db->distinct();
        $this->db->from('cbt_jadwal');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        return $this->db->get()->result();
    }

    public function getAllDataJadwal($tp, $smt, $guru = null, $mapel=null, $level=null) {
        $this->db->select('a.id_jadwal, a.tgl_mulai, a.tgl_selesai, a.status, a.durasi_ujian, a.acak_soal,'.
            ' a.acak_opsi,a.id_bank,a.id_jenis,a.hasil_tampil,a.status,a.ulang,a.reset_login,a.rekap,a.pelanggaran,'.
            ' a.jam_ke, a.token, e.tahun, f.nama_smt, g.level, b.bank_kode, b.bank_level, b.bank_kelas, c.kode_jenis, d.kode, d.nama_mapel,'.
            ' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, b.bank_guru_id, b.soal_agama,'.
            ' (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank');
        $this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        $this->db->join('master_tp e', 'a.id_tp=e.id_tp');
        $this->db->join('master_smt f', 'a.id_smt=f.id_smt');
        $this->db->join('level_kelas g', 'b.bank_level=g.id_level');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);

        if ($guru !== null) {
            $this->db->where('b.bank_guru_id', $guru);
        }
        if ($mapel !== null) {
            $this->db->where('b.bank_mapel_id', $mapel);
        }
        if ($level !== null) {
            $this->db->where('b.bank_level', $level);
        }
        //$this->db->order_by('a.tgl_mulai', 'ASC');
        $this->db->order_by('b.bank_level', 'ASC');
        $this->db->order_by('a.id_tp', 'DESC');
        $this->db->order_by('a.id_smt', 'DESC');
        $this->db->order_by('a.tgl_mulai', 'ASC');
        $query = $this->db->get()->result();
        $ret = [];
        foreach ($query as $key => $row) {
            $ret['<b>'.$row->kode_jenis . '</b>  ' .$row->tahun. ' smt ' .$row->nama_smt][$row->level][] = $row;
        }
        return $ret;
        //return $query;
    }

    public function getJadwalById($id_jadwal, $sesi = null) {
        $this->db->select('a.*, b.opsi, b.bank_kode, b.bank_level, b.bank_kelas,'
            .' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai,'
            .' b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai,'
            .' b.id_bank, b.bank_guru_id, b.soal_agama, c.kode_jenis, c.nama_jenis,'
            .' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
		$this->db->from('cbt_jadwal a');
		$this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
		$this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
		$this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
		if ($sesi != null) {
			$this->db->join('cbt_sesi e', 'e.id_sesi='.$sesi, "left");
		}
        $this->db->join('master_guru f', 'f.id_guru=b.bank_guru_id', "left");
		$this->db->where('a.id_jadwal', $id_jadwal);

        return $this->db->get()->row();
	}

    public function getJadwalByTanggal($tgl_mulai=null) {
        $this->db->select('a.id_jadwal,a.tgl_mulai,a.tgl_selesai,a.jam_ke,b.soal_agama,b.bank_mapel_id,b.bank_kelas');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        if ($tgl_mulai != null) {
            if (is_array($tgl_mulai)) {
                $this->db->where_in('tgl_mulai', $tgl_mulai);
            } else {
                $this->db->where('tgl_mulai', $tgl_mulai);
            }
        }
        return $this->db->get()->result();
    }

    public function getJadwalByArrId($arr_id_jadwal, $sesi = null) {
        //$this->db->select('*');
        $this->db->select('a.*, b.opsi, b.bank_kode, b.bank_level, b.bank_kelas,'
            .' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai,'
            .' b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai,'
            .' b.id_bank, b.bank_guru_id, b.soal_agama, c.kode_jenis, c.nama_jenis,'
            .' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        $this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        if ($sesi != null) {
            $this->db->join('cbt_sesi e', 'e.id_sesi='.$sesi, "left");
        }
        $this->db->join('master_guru f', 'f.id_guru=b.bank_guru_id', "left");
        $this->db->where_in('a.id_jadwal', $arr_id_jadwal);

        return $this->db->get()->result();
    }

	public function cekJadwalBankSoal($id_bank) {
	    $this->db->select('id_bank');
	    $this->db->from('cbt_jadwal');
	    if (is_array($id_bank)) $this->db->where_in('id_bank', $id_bank);
	    else $this->db->where('id_bank', $id_bank);
        return $this->db->get()->num_rows();
	}

	public function saveJadwalUjian($id_tp, $id_smt) {
		$id = $this->input->post('id_jadwal', true);
		$acak_soal = $this->input->post('acak_soal', TRUE);
		$acak_opsi = $this->input->post('acak_opsi', TRUE);
		$hasil_tampil = $this->input->post('hasil_tampil', TRUE);
		$token = $this->input->post('token', TRUE);
		$status = $this->input->post('status', TRUE);
		$reset_login = $this->input->post('reset_login', TRUE);
        $pelanggaran = $this->input->post('pelanggaran', TRUE);

        $bank_id = strip_tags($this->input->post('bank_id', TRUE) ?? '');
        $jenis_id = strip_tags($this->input->post('jenis_id', TRUE) ?? '');
        $mulai = strip_tags($this->input->post('tgl_mulai', TRUE) ?? '');
        $selesai = strip_tags($this->input->post('tgl_selesai', TRUE) ?? '');
        $durasi = strip_tags($this->input->post('durasi_ujian', TRUE) ?? '');
        $jarak = strip_tags($this->input->post('jarak', TRUE) ?? '');

        $data = array(
            'id_tp' => $id_tp,
            'id_smt' => $id_smt,
            'id_bank' => $bank_id,
            'id_jenis' => $jenis_id,
            'tgl_mulai' => $mulai,
            'tgl_selesai' => $selesai,
            'durasi_ujian' => $durasi,
            'jarak' => $jarak,
            'acak_soal' => !$acak_soal ? '0' : $acak_soal,
            'acak_opsi' => !$acak_opsi ? '0' : $acak_opsi,
            'hasil_tampil' => !$hasil_tampil ? '0' : $hasil_tampil,
            'token' => !$token ? '0' : $token,
            'status' => !$status ? '0' : $status,
            'reset_login' => !$reset_login ? '0' : $reset_login,
            'pelanggaran' => !$pelanggaran ? '0' : $pelanggaran
        );

		$this->load->model('Cbt_jadwal_model', 'jadwal_model');

		if ($id == '') {
			$this->form_validation->set_rules('bank_id', 'Id Bank', 'required|trim');
			if (!$this->form_validation->run()) {
				return [
					'success' => false,
					'errors' => form_error('bank_id')
				];
			}
		}

		return $this->jadwal_model->save_jadwal($data, $id ?: null);
	}

	public function getDataJadwalByTgl($tgl) {
		$this->db->distinct();
		$this->db->select('tgl_mulai, tgl_selesai');
		$this->db->from('cbt_jadwal');
        $this->db->where("tgl_mulai <= '$tgl' AND tgl_selesai >= '$tgl'");
        return $this->db->get()->result();
	}

    public function getDataJadwalByMapel($tp, $smt, $mapel = null) {
        $this->db->select('b.id_jadwal, b.jam_ke, b.tgl_mulai, b.tgl_selesai,'
            .' a.bank_kode, a.bank_level, a.bank_kelas,'
            .' a.id_bank, a.bank_guru_id, a.soal_agama, c.kode_jenis, c.nama_jenis,'
            .' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
        $this->db->from('cbt_bank_soal a');
        if ($mapel != null) $this->db->where('a.bank_mapel_id', $mapel);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->join('cbt_jadwal b', 'a.id_bank=b.id_bank', "left");
        $this->db->join('cbt_jenis c', 'c.id_jenis=b.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=a.bank_mapel_id', "left");
        $this->db->join('master_guru f', 'f.id_guru=a.bank_guru_id', "left");
        $query = $this->db->get();
        return $query->result();
    }

	public function getDataGuru() {
		$this->db->select('a.id_guru, a.nama_guru, b.id_pengawas, b.id_jadwal, b.jam_ke');
		$this->db->from('master_guru a');
		$this->db->join('cbt_pengawas b', 'b.id_guru = a.id_guru', "left");
		//$this->db->join('cbt_jadwal c', 'c.id_jadwal=b.id_jadwal', "left");
        return $this->db->get()->result();
	}

	public function saveToken($post_token) {
        $id = isset($post_token->id_token) ? $post_token->id_token : false;
        $tkn = $post_token->token;
        $auto = $post_token->auto;
        $jarak = $post_token->jarak;
        $data = array(
            'token'     => $tkn,
            'auto'      => $auto,
            'jarak'     => $jarak,
            'updated'   => $post_token->updated
        );
		if (!$id) {
			$this->db->insert('cbt_token', $data);
            return $this->db->insert_id();
		}

        $this->db->where('id_token', $id);
        return $this->db->update('cbt_token', $data);
    }

	public function getToken() {
        return $this->db->get('cbt_token')->row();
	}

	public function getNamaKelasById($id) {
		$this->db->select('nama_kelas');
		$this->db->from('master_kelas');
		$this->db->where(['id_kelas' => $id]);
		return $this->db->get()->row()->nama_kelas;
	}

	public function getSettingKartu() {
		$this->db->select('*');
		$this->db->from('cbt_kop_kartu');
		return $this->db->get()->row();
	}

	public function getSettingKopAbsensi() {
		$this->db->select('a.*, b.logo_kanan, b.logo_kiri, b.kepsek, b.tanda_tangan');
		$this->db->from('cbt_kop_absensi a');
		$this->db->join('setting b', 'b.id_setting=1', 'left');
		return $this->db->get()->row();
	}

	public function getSettingKopBeritaAcara() {
		$this->db->select('a.*, d.logo_kanan, d.logo_kiri, d.kepsek, d.nip, d.tanda_tangan, d.sekolah');
		$this->db->from('cbt_kop_berita a');
		//$this->db->join('cbt_kop_absensi b', 'b.id_kop=a.id_kop', 'left');
		$this->db->join('setting d', 'd.id_setting=1', 'left');
		return $this->db->get()->row();
	}

	public function getDurasiSiswa($id) {
		return $this->db->get_where('cbt_durasi_siswa', 'id_durasi='.$id)->row();
	}

    public function getJadwalByKelas($id_tp, $id_smt, $kelas) {
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai,'.
            ' a.durasi_ujian, a.pengawas, a.acak_soal, a.acak_opsi, a.hasil_tampil, a.token, a.status, a.ulang,'.
            ' a.reset_login, a.rekap, a.jam_ke, a.jarak,a.pelanggaran,'.
            ' c.bank_kode, c.bank_level, c.bank_kelas, c.tampil_pg, c.tampil_kompleks, c.tampil_jodohkan,'.
            ' c.tampil_isian, c.tampil_esai, c.soal_agama, '.
            ' c.bobot_pg, c.bobot_kompleks, c.bobot_jodohkan, c.bobot_isian, c.bobot_esai, b.kode_jenis,'.
            ' b.nama_jenis, d.kode, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.status', '1');
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('c.status', '1');
        $this->db->where('c.status_soal', '1');
        $this->db->like('c.bank_kelas', $kelas);
        $this->db->order_by('a.jam_ke');

        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_jadwal] = $row;
        }
        return $retur;
    }

    public function getDurasiSiswaByJadwal($id_jadwal, $id_siswa = null) {
        $this->db->select('id_durasi, id_siswa, id_jadwal, status, lama_ujian, mulai, selesai, reset');
        $this->db->from('cbt_durasi_siswa');
        $this->db->where('id_jadwal', $id_jadwal);
        if ($id_siswa != null) $this->db->where('id_siswa', $id_siswa);
        return $this->db->get()->result();
    }

    public function getLogUjianByJadwal($id_jadwal) {
        $this->db->select('id_log, log_time, id_siswa, id_jadwal, log_type, log_desc, address, agent, device, reset');
        $this->db->from('log_ujian');
        $this->db->where('id_jadwal=', $id_jadwal);
        return $this->db->get()->result();
    }

    public function getAllNomorPeserta($id_tp, $id_smt, $ids_siswa){
        $this->db->select('*');
        $this->db->where('id_tp', $id_tp);
        $this->db->where('id_smt', $id_smt);
        $this->db->where_in('id_siswa', $ids_siswa);
        $result = $this->db->get('cbt_nomor_peserta')->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_siswa] = $row;
        }
        return $ret;
    }

    public function cekNomorPeserta($ids_nomor){
        $this->db->select('*');
        $this->db->where_in('id_nomor', $ids_nomor);
        $result = $this->db->get('cbt_nomor_peserta')->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_nomor] = $row;
        }
        return $ret;
    }
}
