<?php
defined('BASEPATH') or exit('No direct script access allowed');



class Migrate extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index(){
        $data = [
            'judul'			=> 'Migrasi',
            'subjudul'		=> 'Update Aplikasi',
        ];
        $this->load->view('install/header', $data);
        $this->load->view('init');
        $this->load->view('install/footer');
    }

    public function get_version() {
        $this->config->load('migration');
        $latest = $this->config->item('migration_version');
        $data['latest_version'] = $latest;

        $data['current_version'] = 0;
        if ($this->db->table_exists('migrations') &&
            $this->db->field_exists('version', 'migrations')) {
            $curr_version = $this->db->get('migrations')->first_row();
            $data['current_version'] = isset($curr_version->version) ? (int) $curr_version->version : 0;
        }

        $this->output_json($data);
    }

    public function update_version()
    {
        $post = $this->input->get('version', true);
        $old = $this->input->get('old_version', true);
        if ($post !== null && $post > 0) {
            $this->db->where('version', $old);
            $updated = $this->db->update('migrations', ['version' => $post]);
            $data['success'] = $updated;
            $data['message'] = 'Version updated to ' . $post;
        } else {
            $data['success'] = false;
            $data['message'] = 'No version specified.';
        }
        $this->output_json($data);
    }

    private function getDataKelas() {
        $this->db->select("id_kelas,id_smt,id_tp,kode_kelas,level_id,nama_kelas");
        $this->db->from('master_kelas');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_tp][$row->id_smt][$row->id_kelas] = $row;
            }
        }
        return $ret;
    }

    private function getAllJadwalHarian(){
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.jam_ke, a.id_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $result = $this->db->get()->result();

        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_tp][$row->id_smt][$row->id_hari][$row->id_kelas][] = $row;
            }
        }
        return $ret;
    }

    public function get_table() {
        $table = $this->input->get('table');
        $tableExist = $this->db->table_exists($table);
        if ($tableExist) {
            $page = $this->input->get('page');
            if ($page) {
                $limit = $this->input->get('limit');
                $offset = ((int)$page - 1) * $limit;
                $data['limit'] = $limit;
                $data['offset'] = $offset;
                $query = $this->db->limit($limit, $offset)->get($table);
            } else {
                $query = $this->db->get($table);
            }
            $result = $query->result();
            if ($table == 'cbt_bank_soal') {
                foreach ($result as $res) {
                    $res->bank_kelas = $this->maybe_unserialize($res->bank_kelas ?? []);
                }
            }
            if ($table == 'master_kelas') {
                foreach ($result as $res) {
                    $res->jumlah_siswa = $this->maybe_unserialize($res->jumlah_siswa);
                }
            }
            if ($table == 'cbt_soal') {
                foreach ($result as $res) {
                    $res->opsi_a = $this->maybe_unserialize($res->opsi_a ?? '');
                    $res->jawaban = $this->maybe_unserialize($res->jawaban ?? '');
                    unset($res->file1, $res->file_a, $res->file_b, $res->file_c, $res->file_d, $res->file_e);
                }
            }
        } else {
            $result = [];
        }
        $data['data'] = $result;
        $this->output_json($data);
    }

    public function check_mapel() {
        $fieldExist = $this->db->list_fields('kelas_jadwal_mapel');
        $data['need_convert'] = !in_array('dari', $fieldExist);
        $data['count'] = $fieldExist ? $this->db->get('kelas_jadwal_mapel')->num_rows() : 0;
        $this->output_json($data);
    }

    public function get_mapel() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;

        $this->db->select('id_jadwal, id_tp, id_smt, id_kelas, id_hari, jam_ke, id_mapel')->from('kelas_jadwal_mapel');
        $query = $this->db->limit($limit, $offset)->get();
        $result = $query->result();
        $data['data'] = $result;
        $this->output_json($data);
    }

    public function convert_mapel() {
        $post = $this->input->post('mapel', true);
        if ($post) {
            $details = $this->decodeIds($post);
            $update = $this->db->insert_batch('kelas_jadwal_mapel', $details);
            //$update = true;
            $data['success'] = $update;
            $data['jadwal'] = $details;
            $data['count'] = count($details);
        } else {
            $data['success'] = false;
            $data['message'] = '';
        }
        $this->output_json($data);
    }

    public function check_jadwalkbm() {
        $fieldExist = $this->db->list_fields('kelas_jadwal_kbm');
        $need = !in_array('kbm_jam_selesai', $fieldExist);
        $data['need_convert'] = $need;
        $data['count'] = $fieldExist ? $this->db->get('kelas_jadwal_kbm')->num_rows() : 0;
        $this->output_json($data);
    }

    public function get_jadwalkbm()
    {
        $this->load->model('Tp_model', 'tp');
        $this->load->model('Smt_model', 'smt');
        $tps = $this->tp->as_array()->get_all();
        $smts = $this->smt->as_array()->get_all();

        $this->db->select('*');
        $this->db->from('kelas_jadwal_kbm');
        $result = $this->db->get()->result();
        $jadwal_kbm = [];
        if ($result) {
            foreach ($result as $row) {
                $row->istirahat = $this->maybe_unserialize($row->istirahat);
                $jadwal_kbm[$row->id_tp][$row->id_smt][$row->id_kelas] = $row;
            }
        }

        $all_kelas = $this->getDataKelas();
        $data = [
            'kelas' => $all_kelas,
            'jadwal' => $jadwal_kbm,
            'tps' => $tps,
            'smts' => $smts
        ];
        $this->output_json($data);
    }

    public function convert_kbm() {
        $post = $this->input->post('kbm', true);
        if ($post) {
            $details = $this->decodeIds($post);
            $update = $this->db->insert_batch('kelas_jadwal_kbm', $details);
            //$update = true;
            $data['success'] = $update;
            $data['data'] = $details;
            $data['count'] = count($details);
        } else {
            $data['message'] = '';
            $data['success'] = false;
        }
        $this->output_json($data);
    }

    public function check_materi() {
        $fields = $this->db->field_data('kelas_jadwal_materi');
        $need = false;
        foreach ($fields as $field)
        {
            if (($field->name == 'id_kjm') && $field->type == 'varchar') {
                $need = true;
            }
        }
        $data['count'] = $this->db->get('kelas_jadwal_materi')->num_rows();
        $data['need_convert'] = $need;
        $data['count_log'] = $this->db->get('log_materi')->num_rows();
        $this->output_json($data);
    }

    public function get_materi() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;

        $query = $this->db->limit($limit, $offset)->get('kelas_jadwal_materi');
        $result = $query->result();
        $data['data'] = $result;
        $this->output_json($data);
    }

    public function get_log() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;

        $query = $this->db->limit($limit, $offset)->get('log_materi');
        $result = $query->result();
        $data['data'] = $result;
        $this->output_json($data);
    }

    public function convert_materi()
    {
        $post = $this->input->post('materi', true);
        if ($post) {
            $materis = $this->decodeIds($post);
            $success = $this->db->insert_batch('kelas_jadwal_materi', $materis);
            //$success = true;
            $data['success'] = $success;
            $data['data'] = $materis;
            $data['count'] = count($materis);
        } else {
            $data['message'] = '';
            $data['success'] = false;
        }
        $this->output_json($data);
    }

    public function convert_log()
    {
        $post = $this->input->post('log', true);
        if ($post) {
            $logs = $this->decodeIds($post);
            $success = $this->db->insert_batch('log_materi', $logs);
            //$success = true;
            $data['success'] = $success;
            $data['data'] = $logs;
            $data['count'] = count($logs);
        } else {
            $data['message'] = '';
            $data['success'] = false;
        }
        $this->output_json($data);
    }

    public function check_rekap() {
        $rekapExist = $this->db->table_exists('cbt_rekap_jadwal');
        $oldExist = $this->db->table_exists('cbt_rekap');
        $countRekap = $oldExist ? $this->db->get('cbt_rekap')->num_rows() : 0;

        $data['need_convert'] = !$rekapExist;
        $data['count'] = $countRekap;
        $data['count_nilai'] = $oldExist ? $this->db->get('cbt_rekap_nilai')->num_rows() : 0;

        $data['count_bank'] = $this->db->get('cbt_bank_soal')->num_rows();
        $data['count_jadwal'] = $this->db->get('cbt_jadwal')->num_rows();
        $data['count_soal'] = $this->db->get('cbt_soal')->num_rows();
        $data['count_pengawas'] = $this->db->get('cbt_pengawas')->num_rows();
        $data['count_kelas'] = $this->db->get('master_kelas')->num_rows();
        $data['count_sesi'] = $this->db->get('cbt_sesi_siswa')->num_rows();

        $this->output_json($data);
    }

    public function get_rekap_jadwal() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $query = $this->db->limit($limit, $offset)->get('cbt_rekap');
        $result = $query->result();

        foreach ($result as $row) {
            $row->bank_kelas = $this->maybe_unserialize($row->bank_kelas ?? '[]');
            $row->nama_kelas = $this->maybe_unserialize($row->nama_kelas ?? '[]');
            $row->jawaban_pg = $this->maybe_unserialize($row->jawaban_pg ?? '[]');
            $row->soal_kompleks = $this->maybe_unserialize($row->soal_kompleks ?? '[]');
            $row->soal_jodohkan = $this->maybe_unserialize($row->soal_jodohkan ?? '[]');
            $row->soal_isian = $this->maybe_unserialize($row->soal_isian ?? '[]');
            $row->soal_essai = $this->maybe_unserialize($row->soal_essai ?? '[]');
        }
        $data['data'] = $result;
        $this->output_json($data);
    }

    public function get_rekap_nilai() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $this->db->select('a.*,b.nama,b.nis,b.nisn,b.agama,b.jenis_kelamin');
        $this->db->join('master_siswa b', 'a.id_siswa=b.id_siswa', 'left');
        $query = $this->db->limit($limit, $offset)->get('cbt_rekap_nilai a');
        $result = $query->result();
        foreach ($result as $row) {
            $row->jawaban_pg = $this->maybe_unserialize($row->jawaban_pg);
            $row->soal_kompleks = $this->maybe_unserialize($row->soal_kompleks);
            $row->soal_jodohkan = $this->maybe_unserialize($row->soal_jodohkan);
            $row->soal_isian = $this->maybe_unserialize($row->soal_isian);
            $row->soal_essai = $this->maybe_unserialize($row->soal_essai);
        }
        $data['data'] = $result;
        $this->output_json($data);
    }

    public function get_bank_soal() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $query = $this->db->limit($limit, $offset)->get('cbt_bank_soal');
        $result = $query->result();

    }

    public function convert_rekap_jadwal() {
        $post = $this->input->post('jadwal');
        if ($post) {
            $siswas = $this->decodeIds($post);
            $inserted = $this->db->insert_batch('cbt_rekap_jadwal', $siswas);
            //$inserted = true;
            $data['success'] = $inserted;
            $data['data'] = $siswas;
            $data['count'] = count($siswas);
        } else {
            $data['message'] = '';
            $data['success'] = false;
        }
        $this->output_json($data);
    }

    public function convert_rekap_nilai() {
        $post = $this->input->post('nilai');
        if ($post) {
            $siswas = $this->decodeIds($post);
            $inserted = $this->db->insert_batch('cbt_rekap_siswa', $siswas);
            //$inserted = true;
            $data['success'] = $inserted;
            $data['data'] = $siswas;
            $data['count'] = count($siswas);
        } else {
            $data['message'] = '';
            $data['success'] = false;
        }
        $this->output_json($data);
    }

    public function get_soal() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;
        $query = $this->db->limit($limit, $offset)->get('cbt_soal');
        $result = $query->result();
    }

    public function check_cbt() {
        $newExist = $this->db->table_exists('cbt_siswa');
        $oldExist = $this->db->table_exists('cbt_soal_siswa');
        $count = $oldExist ? $this->db->get('cbt_soal_siswa')->num_rows() : 0;

        $data['need_convert'] = !$newExist;
        $data['count'] = $count;
        $data['count_durasi'] = $oldExist ? $this->db->get('cbt_durasi_siswa')->num_rows() : 0;
        $data['count_nilai'] = $oldExist ? $this->db->get('cbt_nilai')->num_rows() : 0;
        $data['count_log'] = $this->db->get('log_ujian')->num_rows();

        $this->output_json($data);
    }

    public function get_soal_siswa() {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $offset = ((int)$page - 1) * $limit;

        $data['limit'] = $limit;
        $data['offset'] = $offset;

        $query = $this->db->limit($limit, $offset)->get('cbt_soal_siswa');
        $result = $query->result();
        foreach ($result as $row) {
            $row->jawaban_benar = $this->maybe_unserialize($row->jawaban_benar);
            $row->jawaban_siswa = $this->maybe_unserialize($row->jawaban_siswa);
        }
        $data['data'] = $result;
        $this->output_json($data);
    }

    public function convert_soal_siswa() {
        $post = $this->input->post('siswa');
        if ($post) {
            $siswas = $this->decodeIds($post);
            $inserted = $this->db->insert_batch('cbt_siswa', $siswas);
            //$inserted = true;
            $data['success'] = $inserted;
            $data['data'] = $siswas;
            $data['count'] = count($siswas);
        } else {
            $data['message'] = '';
            $data['success'] = false;
        }
        $this->output_json($data);
    }

    public function empty_table() {
        $tabel = $this->input->get('table');
        $data['success'] = $this->db->empty_table($tabel);
        $this->output_json($data);
    }

    public function updateApp() {
        $this->load->library('migration');
        if ($this->migration->current() == FALSE) {
            $data['success'] = false;
            $data['message'] = $this->migration->error_string();
            //show_error($this->migration->error_string());
        } else {
            $data['success'] = true;
            $data['message'] = 'The migration file has executed successfully.';
        }
        $this->output_json($data);
    }

    public function undoMigration($version = NULL){
        $this->load->library('migration');
        $migrations = $this->migration->find_migrations();
        $migrationKeys = array();
        foreach($migrations as $key => $migration){
            $migrationKeys[] = $key;
        }
        if (isset($version) && array_key_exists($version,$migrations) && $this->migration->version($version)) {
            echo 'The migration was undo';
            exit;
        }

        if(isset($version) && !array_key_exists($version,$migrations)) {
            echo 'The migration with selected version doesn’t exist.';
        } else {
            $penultimate = (sizeof($migrationKeys)==1) ? 0 : $migrationKeys[sizeof($migrationKeys) - 2];
            if($this->migration->version($penultimate)){
                echo 'The migration has been reverted successfully.';
                exit;
            }

            echo 'Couldn\’t roll back the migration.';
            exit;
        }
    }

    public function resetMigration(){
        $this->load->library('migration');
        if($this->migration->current()!== FALSE){
            echo 'The migration was revert to the version set in the config file.';
            return TRUE;
        }

        echo 'Couldn\’t reset migration.';
        show_error($this->migration->error_string());
        exit;
    }

    function make_base(){

        $this->load->library('ci_migrations_generator/Sqltoci');

        // All Tables:
        //$this->sqltoci->generate();

        //Single Table:
        $this->sqltoci->generate('kelas_jadwal_kbm');

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

    function error_json() {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $res = 'JSON_ERROR_NONE';
                break;
            case JSON_ERROR_DEPTH:
                $res = 'JSON_ERROR_DEPTH';
                //echo ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $res = 'JSON_ERROR_STATE_MISMATCH';
                //echo ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $res = 'JSON_ERROR_CTRL_CHAR';
                //echo ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $res = 'JSON_ERROR_SYNTAX';
                //echo ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $res = 'JSON_ERROR_UTF8';
                //echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $res = 'UNKNOWN';
                //echo ' - Unknown error';
                break;
        }
        return $res;
    }
}