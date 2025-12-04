<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kurmer_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    private function tableExists($table) {
        return $this->db->table_exists($table);
    }

    public function getTujuanPembelajaran($id_mapel, $id_kelas, $id_tp, $id_smt) {
        if (!$this->tableExists('kurmer_tujuan_pembelajaran')) {
            return [];
        }

        $this->db->from('kurmer_tujuan_pembelajaran');
        $this->db->where('id_mapel', $id_mapel);
        if (!empty($id_kelas)) {
            $this->db->group_start()
                     ->where('id_kelas', $id_kelas)
                     ->or_where('id_kelas IS NULL', null, false)
                     ->group_end();
        }
        if (!empty($id_tp)) {
            $this->db->where('id_tp', $id_tp);
        }
        if (!empty($id_smt)) {
            $this->db->where('id_smt', $id_smt);
        }
        $this->db->order_by('fase', 'ASC');
        $this->db->order_by('kode_tp', 'ASC');
        return $this->db->get()->result();
    }

    public function getTujuanPembelajaranIndexed($id_mapel, $id_kelas, $id_tp, $id_smt) {
        $tujuan = $this->getTujuanPembelajaran($id_mapel, $id_kelas, $id_tp, $id_smt);
        $indexed = [];
        foreach ($tujuan as $tp) {
            $indexed[$tp->id] = $tp;
        }
        return $indexed;
    }

    public function getPenilaianKelas($id_mapel, $id_kelas, $id_tp, $id_smt, $id_siswa = null) {
        if (!$this->tableExists('kurmer_penilaian')) {
            return [];
        }

        $this->db->from('kurmer_penilaian');
        $this->db->where('id_mapel', $id_mapel);
        $this->db->where('id_kelas', $id_kelas);
        if (!empty($id_tp)) {
            $this->db->where('id_tp', $id_tp);
        }
        if (!empty($id_smt)) {
            $this->db->where('id_smt', $id_smt);
        }
        if (!empty($id_siswa)) {
            $this->db->where('id_siswa', $id_siswa);
        }
        $penilaian = $this->db->get()->result();

        if (empty($penilaian) || !$this->tableExists('kurmer_penilaian_detail')) {
            return $this->groupPenilaianBySiswa($penilaian);
        }

        $penilaianIds = array_column($penilaian, 'id');
        $detailMap = $this->getPenilaianDetailByIds($penilaianIds);

        foreach ($penilaian as $row) {
            $row->detail = $detailMap[$row->id] ?? [];
        }

        return $this->groupPenilaianBySiswa($penilaian);
    }

    private function groupPenilaianBySiswa($penilaian) {
        $grouped = [];
        foreach ($penilaian as $row) {
            $grouped[$row->id_siswa][$row->id_tujuan ?? 0][] = $row;
        }
        return $grouped;
    }

    private function getPenilaianDetailByIds($ids) {
        if (empty($ids)) {
            return [];
        }

        $this->db->from('kurmer_penilaian_detail');
        $this->db->where_in('id_penilaian', $ids);
        $detail = $this->db->get()->result();

        $mapped = [];
        foreach ($detail as $row) {
            $mapped[$row->id_penilaian][] = $row;
        }
        return $mapped;
    }

    public function getProyekP5ByKelas($id_kelas, $id_tp, $id_smt) {
        if (!$this->tableExists('kurmer_proyek_p5')) {
            return [];
        }

        $this->db->from('kurmer_proyek_p5');
        $this->db->where('id_kelas', $id_kelas);
        if (!empty($id_tp)) {
            $this->db->where('id_tp', $id_tp);
        }
        if (!empty($id_smt)) {
            $this->db->where('id_smt', $id_smt);
        }
        $result = $this->db->get()->result();

        $grouped = [];
        foreach ($result as $row) {
            $grouped[$row->id_siswa][] = $row;
        }
        return $grouped;
    }

    public function getRubrikLevels($id_mapel = null) {
        if (!$this->tableExists('kurmer_rubrik_level')) {
            return [];
        }

        $this->db->from('kurmer_rubrik_level');
        if (!empty($id_mapel)) {
            $this->db->where('id_mapel', $id_mapel);
        }
        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('level_kode', 'ASC');
        return $this->db->get()->result();
    }

    public function getPenilaianSetting($id_mapel, $id_kelas, $id_tp, $id_smt) {
        if (!$this->tableExists('kurmer_penilaian_setting')) {
            return null;
        }

        $this->db->from('kurmer_penilaian_setting');
        $this->db->where('id_mapel', $id_mapel);
        $this->db->where('id_kelas', $id_kelas);
        if (!empty($id_tp)) {
            $this->db->where('id_tp', $id_tp);
        }
        if (!empty($id_smt)) {
            $this->db->where('id_smt', $id_smt);
        }
        return $this->db->get()->row();
    }

    public function savePenilaianSetting($data) {
        if (!$this->tableExists('kurmer_penilaian_setting')) {
            return false;
        }
        if (!isset($data['id'])) {
            $existing = $this->getPenilaianSetting($data['id_mapel'], $data['id_kelas'], $data['id_tp'] ?? null, $data['id_smt'] ?? null);
            if ($existing) {
                $data['id'] = $existing->id;
            }
        }
        if (!empty($data['id'])) {
            $this->db->where('id', $data['id']);
            return $this->db->update('kurmer_penilaian_setting', $data);
        }
        return $this->db->insert('kurmer_penilaian_setting', $data);
    }

    public function saveRubrikLevels($id_mapel, $levels = []) {
        if (!$this->tableExists('kurmer_rubrik_level')) {
            return false;
        }
        $this->db->trans_start();
        if ($id_mapel !== null) {
            $this->db->where('id_mapel', $id_mapel)->delete('kurmer_rubrik_level');
        }
        if (!empty($levels)) {
            $this->db->insert_batch('kurmer_rubrik_level', $levels);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function insertPenilaianKurmer($penilaianData, $detailRows = [], $evidenRows = []) {
        if (!$this->tableExists('kurmer_penilaian')) {
            return false;
        }

        $this->db->trans_start();
        $this->db->insert('kurmer_penilaian', $penilaianData);
        $penilaianId = $this->db->insert_id();

        if (!empty($detailRows) && $this->tableExists('kurmer_penilaian_detail')) {
            foreach ($detailRows as &$detail) {
                $detail['id_penilaian'] = $penilaianId;
            }
            unset($detail);
            $this->db->insert_batch('kurmer_penilaian_detail', $detailRows);
        }

        if (!empty($evidenRows) && $this->tableExists('kurmer_penilaian_eviden')) {
            foreach ($evidenRows as &$eviden) {
                $eviden['id_penilaian'] = $penilaianId;
            }
            unset($eviden);
            $this->db->insert_batch('kurmer_penilaian_eviden', $evidenRows);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $penilaianId : false;
    }
}
