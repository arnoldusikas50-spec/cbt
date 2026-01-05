<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CbtModel - Migrated from CI3 Cbt_model
 * Core CBT functionality: jadwal, bank soal, token
 */
class CbtModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // ==========================================
    // TOKEN FUNCTIONS
    // ==========================================

    /**
     * Get current token
     */
    public function getToken(): ?object
    {
        return $this->db->table('cbt_token')
            ->orderBy('id_token', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();
    }

    /**
     * Update or create token
     */
    public function saveToken(array $data): bool
    {
        $existing = $this->getToken();

        if ($existing) {
            return $this->db->table('cbt_token')
                ->where('id_token', $existing->id_token)
                ->update($data);
        }

        return $this->db->table('cbt_token')->insert($data);
    }

    // ==========================================
    // JADWAL FUNCTIONS
    // ==========================================

    /**
     * Get jadwal by ID
     */
    public function getJadwal(int $idJadwal): ?object
    {
        return $this->db->table('cbt_jadwal a')
            ->select('a.*, b.nama_mapel, b.kode as kode_mapel, c.bank_kode, c.bank_nama')
            ->join('master_mapel b', 'a.id_mapel = b.id_mapel', 'left')
            ->join('cbt_bank_soal c', 'a.id_bank = c.id_bank', 'left')
            ->where('a.id_jadwal', $idJadwal)
            ->get()
            ->getRow();
    }

    /**
     * Get all jadwal by TP and semester
     */
    public function getAllJadwal(int $tp, int $smt): array
    {
        return $this->db->table('cbt_jadwal a')
            ->select('a.*, b.nama_mapel, c.bank_kode, c.bank_nama')
            ->join('master_mapel b', 'a.id_mapel = b.id_mapel', 'left')
            ->join('cbt_bank_soal c', 'a.id_bank = c.id_bank', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('a.tgl_mulai', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Get jadwal by date
     */
    public function getDataJadwalByTgl(string $tanggal): array
    {
        return $this->db->table('cbt_jadwal a')
            ->select('a.*, b.bank_kode, b.bank_nama, b.bank_kelas, c.nama_mapel')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'a.id_mapel = c.id_mapel', 'left')
            ->where('a.tgl_mulai <=', $tanggal . ' 23:59:59')
            ->where('a.tgl_selesai >=', $tanggal)
            ->get()
            ->getResult();
    }

    /**
     * Get active jadwal for today
     */
    public function getActiveJadwal(int $tp, int $smt): array
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->table('cbt_jadwal a')
            ->select('a.*, b.bank_kode, b.bank_nama, c.nama_mapel')
            ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
            ->join('master_mapel c', 'a.id_mapel = c.id_mapel', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->where('a.tgl_mulai <=', $now)
            ->where('a.tgl_selesai >=', $now)
            ->get()
            ->getResult();
    }

    // ==========================================
    // RUANG & SESI FUNCTIONS
    // ==========================================

    /**
     * Get all ruang
     */
    public function getAllRuang(): array
    {
        return $this->db->table('cbt_ruang')
            ->orderBy('nama_ruang', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get ruang by ID
     */
    public function getRuangById(int $id): ?object
    {
        return $this->db->table('cbt_ruang')
            ->where('id_ruang', $id)
            ->get()
            ->getRow();
    }

    /**
     * Get all sesi
     */
    public function getAllSesi(): array
    {
        return $this->db->table('cbt_sesi')
            ->orderBy('nama_sesi', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get sesi by ID
     */
    public function getSesiById(int $id): ?object
    {
        return $this->db->table('cbt_sesi')
            ->where('id_sesi', $id)
            ->get()
            ->getRow();
    }

    /**
     * Get distinct ruang/sesi combinations
     */
    public function getDistinctSesi(int $tp, int $smt, array $idsKelas): array
    {
        if (empty($idsKelas)) {
            return [];
        }

        return $this->db->table('cbt_sesi_siswa a')
            ->select('a.ruang_id, a.sesi_id, b.nama_ruang, b.kode_ruang, c.nama_sesi, c.kode_sesi')
            ->join('cbt_ruang b', 'a.ruang_id = b.id_ruang', 'left')
            ->join('cbt_sesi c', 'a.sesi_id = c.id_sesi', 'left')
            ->where('a.tp_id', $tp)
            ->where('a.smt_id', $smt)
            ->whereIn('a.kelas_id', $idsKelas)
            ->groupBy(['a.ruang_id', 'a.sesi_id'])
            ->get()
            ->getResult();
    }

    /**
     * Get students with their assigned ruang and sesi
     */
    public function getRuangSesiSiswa(int $kls, int $tp, int $smt): array
    {
        return $this->db->table('kelas_siswa a')
            ->select('a.id_siswa as siswa_id, a.id_kelas as kelas_id, b.nama, b.nis, b.nisn, c.id_sesi_siswa, c.ruang_id, c.sesi_id')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('cbt_sesi_siswa c', 'a.id_siswa = c.siswa_id AND c.tp_id = ' . $tp . ' AND c.smt_id = ' . $smt, 'left')
            ->where('a.id_kelas', $kls)
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('b.nama', 'ASC')
            ->get()
            ->getResult();
    }

    // ==========================================
    // JENIS UJIAN FUNCTIONS
    // ==========================================

    /**
     * Get all jenis ujian
     */
    public function getAllJenisUjian(bool $activeOnly = false): array
    {
        $builder = $this->db->table('cbt_jenis');

        if ($activeOnly) {
            $builder->where('aktif', '1');
        }

        return $builder->orderBy('urut', 'ASC')->get()->getResult();
    }

    /**
     * Get active jenis ujian ID
     */
    public function getActiveJenisId(): ?int
    {
        $result = $this->db->table('cbt_jenis')
            ->select('id_jenis')
            ->where('status', '1')
            ->limit(1)
            ->get()
            ->getRow();

        return $result ? (int) $result->id_jenis : null;
    }

    /**
     * Get jenis ujian for Datatable
     */
    public function getJenis(): array
    {
        $result = $this->db->table('cbt_jenis')->get()->getResult();
        $data = [];
        foreach ($result as $i => $row) {
            $data[] = [
                'no' => $i + 1,
                'id_jenis' => $row->id_jenis,
                'nama_jenis' => $row->nama_jenis,
                'kode_jenis' => $row->kode_jenis,
                'status' => $row->status
            ];
        }
        return ['data' => $data];
    }

    /**
     * Update jenis ujian from request
     */
    public function updateJenis(int $id, array $data): bool
    {
        return $this->db->table('cbt_jenis')->where('id_jenis', $id)->update($data);
    }

    // ==========================================
    // BANK SOAL FUNCTIONS
    // ==========================================

    /**
     * Get all bank soal for specific TP and semester
     */
    public function getAllBankSoal(int $tp, int $smt): array
    {
        return $this->db->table('cbt_bank_soal a')
            ->select('a.*, b.nama_mapel')
            ->join('master_mapel b', 'a.bank_mapel_id = b.id_mapel', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->get()
            ->getResult();
    }

    /**
     * Get bank soal by ID with details
     */
    public function getBankSoalById(int $idBank): ?object
    {
        return $this->db->table('cbt_bank_soal a')
            ->select('a.*, b.nama_mapel, b.kode as kode_mapel, c.nama_guru, d.nama_jurusan, d.kode_jurusan,
                (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan')
            ->join('master_mapel b', 'a.bank_mapel_id = b.id_mapel', 'left')
            ->join('master_guru c', 'a.bank_guru_id = c.id_guru', 'left')
            ->join('master_jurusan d', 'a.bank_jurusan_id = d.id_jurusan', 'left')
            ->where('a.id_bank', $idBank)
            ->get()
            ->getRow();
    }

    /**
     * Get all bank soal with details and filtering
     */
    public function getDataBank(?int $guru = null, ?int $mapel = null, ?int $level = null): array
    {
        $builder = $this->db->table('cbt_bank_soal a')
            ->select('a.*, b.nama_mapel, b.kode as kode, c.nama_guru,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="1") AS total_pg,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="2") AS total_kompleks,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="3") AS total_jodohkan,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="4") AS total_isian,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="5") AS total_esai,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="6") AS total_urutan,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank AND cbt_soal.jenis="7") AS total_cloze,
                (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan')
            ->join('master_mapel b', 'a.bank_mapel_id = b.id_mapel', "left")
            ->join('master_guru c', 'a.bank_guru_id = c.id_guru', "left");

        if ($guru !== null)
            $builder->where('a.bank_guru_id', $guru);
        if ($mapel !== null)
            $builder->where('a.bank_mapel_id', $mapel);
        if ($level !== null)
            $builder->where('a.bank_level', $level);

        $result = $builder->orderBy('a.bank_level', 'ASC')->get()->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_tp][$row->id_smt][] = $row;
        }
        return $data;
    }

    /**
     * Get all soal by bank ID
     */
    public function getAllSoalByBank(int $idBank, ?int $jenis = null): array
    {
        $builder = $this->db->table('cbt_soal')
            ->where('bank_id', $idBank);

        if ($jenis !== null)
            $builder->where('jenis', $jenis);

        return $builder->orderBy('nomor_soal', 'ASC')->get()->getResult();
    }

    /**
     * Count soal per type in bank
     */
    public function getJumlahJenisSoal(int $idBank): array
    {
        return $this->db->table('cbt_soal')
            ->select('jenis, COUNT(*) as jumlah')
            ->where('id_bank', $idBank)
            ->groupBy('jenis')
            ->get()
            ->getResult();
    }

    // ==========================================
    // PENGAWAS FUNCTIONS
    // ==========================================

    /**
     * Get all pengawas
     */
    public function getAllPengawas(int $tp, int $smt, ?int $ruang = null, ?int $sesi = null): array
    {
        $builder = $this->db->table('cbt_pengawas a')
            ->select('a.*, b.nama_guru, c.nama_ruang, d.nama_sesi')
            ->join('master_guru b', 'a.id_guru = b.id_guru', 'left')
            ->join('cbt_ruang c', 'a.id_ruang = c.id_ruang', 'left')
            ->join('cbt_sesi d', 'a.id_sesi = d.id_sesi', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt);

        if ($ruang) {
            $builder->where('a.id_ruang', $ruang);
        }

        if ($sesi) {
            $builder->where('a.id_sesi', $sesi);
        }

        return $builder->get()->getResult();
    }

    // ==========================================
    // CBT LOG FUNCTIONS
    // ==========================================

    /**
     * Save CBT activity log
     */
    public function saveLog(int $idSiswa, int $idJadwal, int $type, string $desc): bool
    {
        $data = [
            'id_siswa' => $idSiswa,
            'id_jadwal' => $idJadwal,
            'tipe' => $type,
            'keterangan' => $desc,
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'waktu' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('cbt_log')->insert($data);
    }

    /**
     * Get CBT logs by jadwal
     */
    public function getLogsByJadwal(int $idJadwal): array
    {
        return $this->db->table('cbt_log a')
            ->select('a.*, b.nama')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->where('a.id_jadwal', $idJadwal)
            ->orderBy('a.waktu', 'DESC')
            ->get()
            ->getResult();
    }
    public function getSoalByBank(int $idBank, string $acak = '0')
    {
        $builder = $this->db->table('cbt_soal')
            ->where('id_bank', $idBank);

        if ($acak == '1') {
            $builder->orderBy('RAND()');
        } else {
            $builder->orderBy('nomor_soal', 'ASC')->orderBy('id_soal', 'ASC');
        }

        return $builder->get()->getResult();
    }

    public function getJawabanSiswa(int $idSiswa, int $idJadwal): array
    {
        $result = $this->db->table('cbt_jawaban')
            ->where('id_siswa', $idSiswa)
            ->where('id_jadwal', $idJadwal)
            ->get()
            ->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_soal] = $row;
        }
        return $data;
    }

    // ==========================================
    // NOMOR PESERTA FUNCTIONS
    // ==========================================

    /**
     * Get students by multiple class IDs
     */
    public function getSiswaByKelasArray(int $tp, int $smt, array $kelas_ids): array
    {
        if (empty($kelas_ids))
            return [];
        return $this->db->table('kelas_siswa a')
            ->select('a.id_siswa, b.nama, b.nis, b.nisn')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->whereIn('a.id_kelas', $kelas_ids)
            ->orderBy('b.nama', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get all nomor peserta for specific students
     */
    public function getAllNomorPeserta(int $tp, int $smt, array $siswa_ids): array
    {
        if (empty($siswa_ids))
            return [];
        $result = $this->db->table('cbt_nomor_peserta')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->whereIn('id_siswa', $siswa_ids)
            ->get()
            ->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_siswa] = $row;
        }
        return $data;
    }

    /**
     * Check if nomor peserta exists by custom ID
     */
    public function cekNomorPeserta(array $ids_nomor): array
    {
        if (empty($ids_nomor))
            return [];
        $result = $this->db->table('cbt_nomor_peserta')
            ->whereIn('id_nomor', $ids_nomor)
            ->get()
            ->getResult();

        $data = [];
        foreach ($result as $row) {
            $data[$row->id_nomor] = $row;
        }
        return $data;
    }

    /**
     * Delete duplicate nomor peserta (cleanup)
     */
    public function hapus_duplikat_nomor_peserta(): int
    {
        $sql = "DELETE t1 FROM cbt_nomor_peserta t1
                INNER JOIN cbt_nomor_peserta t2 
                WHERE t1.id_nomor < t2.id_nomor 
                AND t1.id_siswa = t2.id_siswa 
                AND t1.id_tp = t2.id_tp 
                AND t1.id_smt = t2.id_smt";
        $this->db->query($sql);
        return $this->db->affectedRows();
    }

    /**
     * Get all jurusan for dropdown
     */
    public function getAllJurusan(): array
    {
        $result = $this->db->table('master_jurusan')->get()->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_jurusan] = $row->kode_jurusan;
        }
        return $ret;
    }

    /**
     * Get classes by level and allowed IDs
     */
    public function getKelasByLevel(int $level, array $arrKelas): array
    {
        if (empty($arrKelas))
            return [];
        return $this->db->table('master_kelas')
            ->select('id_kelas, kode_kelas')
            ->where('level_id', $level)
            ->whereIn('id_kelas', $arrKelas)
            ->get()
            ->getResult();
    }

    /**
     * Create dummy bank soal object
     */
    public function dummy(string $jenjang): array
    {
        return [
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
            'tampil_urutan' => '0',
            'bobot_urutan' => '0',
            'tampil_cloze' => '0',
            'bobot_cloze' => '0',
            'kkm' => '',
            'soal_agama' => '-',
            'status' => '1'
        ];
    }

    /**
     * Save/Update bank soal
     */
    public function saveBankSoal(array $data, ?int $id = null)
    {
        if ($id) {
            return $this->db->table('cbt_bank_soal')->where('id_bank', $id)->update($data);
        }
        $this->db->table('cbt_bank_soal')->insert($data);
        return $this->db->insertID();
    }
}
