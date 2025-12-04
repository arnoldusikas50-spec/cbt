<?php

class Kurmer extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        $this->load->model('Kurmer_model', 'kurmer');
        $this->load->library('form_validation');
    }

    private function requireGuru() {
        if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
            show_error('Akses ditolak', 403);
        }
    }

    public function rubrik($id_mapel = null) {
        $this->requireGuru();
        $levels = $this->kurmer->getRubrikLevels($id_mapel);
        $this->output_json(['levels' => $levels]);
    }

    public function save_rubrik() {
        $this->requireGuru();
        $payload = $this->input->post('levels');
        $id_mapel = $this->input->post('id_mapel');
        $levels = [];
        if (is_array($payload)) {
            $order = 1;
            foreach ($payload as $row) {
                $levels[] = [
                    'id_mapel' => $id_mapel,
                    'level_kode' => $row['kode'] ?? '',
                    'level_nama' => $row['nama'] ?? '',
                    'deskripsi' => $row['deskripsi'] ?? '',
                    'warna_label' => $row['warna'] ?? null,
                    'urutan' => $row['urutan'] ?? $order++,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        $status = $this->kurmer->saveRubrikLevels($id_mapel, $levels);
        $this->output_json(['status' => (bool)$status]);
    }

    public function save_setting() {
        $this->requireGuru();
        $data = [
            'id' => $this->input->post('id', true),
            'id_tp' => $this->input->post('id_tp', true),
            'id_smt' => $this->input->post('id_smt', true),
            'id_mapel' => $this->input->post('id_mapel', true),
            'id_kelas' => $this->input->post('id_kelas', true),
            'bobot_formatif' => $this->input->post('bobot_formatif', true),
            'bobot_sumatif' => $this->input->post('bobot_sumatif', true),
            'min_level_tuntas' => $this->input->post('min_level_tuntas', true),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $status = $this->kurmer->savePenilaianSetting($data);
        $this->output_json(['status' => (bool)$status]);
    }

    public function save_penilaian() {
        $this->requireGuru();
        $id_mapel = $this->input->post('id_mapel', true);
        $id_kelas = $this->input->post('id_kelas', true);
        $id_tp = $this->input->post('id_tp', true);
        $id_smt = $this->input->post('id_smt', true);
        $id_siswa = $this->input->post('id_siswa', true);
        $id_tujuan = $this->input->post('id_tujuan', true);

        $penilaian = [
            'id_tp' => $id_tp,
            'id_smt' => $id_smt,
            'id_mapel' => $id_mapel,
            'id_kelas' => $id_kelas,
            'id_siswa' => $id_siswa,
            'id_tujuan' => $id_tujuan,
            'jenis_asesmen' => $this->input->post('jenis_asesmen', true),
            'tanggal_penilaian' => $this->input->post('tanggal_penilaian', true),
            'skor' => $this->input->post('skor', true),
            'level_kelulusan' => $this->input->post('level_kelulusan', true),
            'capaian' => $this->input->post('capaian', true),
            'catatan' => $this->input->post('catatan', true),
            'bukti_penilaian' => $this->input->post('bukti_penilaian', true),
            'is_summative' => $this->input->post('is_summative', true) === '1' ? 1 : 0,
            'created_by' => $this->auth->user()->id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $detailRows = [];
        if ($this->input->post('indikator')) {
            foreach ($this->input->post('indikator') as $indikator) {
                $detailRows[] = [
                    'indikator' => $indikator['judul'] ?? '',
                    'uraian_eviden' => $indikator['uraian'] ?? '',
                    'skor_indikator' => $indikator['skor'] ?? null,
                    'level_indikator' => $indikator['level'] ?? null,
                    'catatan_tindak_lanjut' => $indikator['catatan'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        $evidenRows = [];
        $tautan = $this->input->post('tautan_bukti');
        if (!empty($tautan)) {
            $evidenRows[] = [
                'jenis_bukti' => $this->input->post('jenis_bukti', true),
                'tautan_bukti' => $tautan,
                'catatan' => $this->input->post('catatan_bukti', true),
                'tanggal_bukti' => $this->input->post('tanggal_penilaian', true),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        $insertId = $this->kurmer->insertPenilaianKurmer($penilaian, $detailRows, $evidenRows);
        $this->output_json(['status' => (bool)$insertId, 'id' => $insertId]);
    }
}
