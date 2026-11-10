<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * KurikulumMerdekaModel - Model untuk Penilaian Kurikulum Merdeka
 * Sesuai Permendikbudristek No. 53/2023 tentang Standar Penilaian Pendidikan
 */
class KurikulumMerdekaModel extends Model
{
    protected $table = 'e_nilai_komponen';
    protected $primaryKey = 'id_nilai';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'siswa_id', 'mapel_id', 'komponen_id', 'nilai', 'predikat', 
        'deskripsi', 'jenis_penilaian', 'tanggal_penilaian', 'guru_id',
        'semester', 'tahun_pelajaran'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Ambil komponen penilaian sesuai Permendikbudristek 53/2023
     */
    public function getKomponenPenilaian()
    {
        return $this->db->table('e_komponen_penilaian')
            ->where('is_aktif', 1)
            ->orderBy('jenis_penilaian', 'ASC')
            ->orderBy('kode_komponen', 'ASC')
            ->get()
            ->getResult();
    }
    
    /**
     * Ambil komponen penilaian berdasarkan ID
     */
    public function getKomponenById($id)
    {
        return $this->db->table('e_komponen_penilaian')
            ->where('id_komponen', $id)
            ->where('is_aktif', 1)
            ->get()
            ->getRow();
    }
    
    /**
     * Ambil komponen penilaian berdasarkan mata pelajaran
     */
    public function getKomponenByMapel($mapelId)
    {
        return $this->db->table('e_komponen_penilaian')
            ->where('is_aktif', 1)
            ->get()
            ->getResult();
    }
    
    /**
     * Ambil bobot penilaian untuk mapel dan tingkat tertentu
     */
    public function getBobotPenilaian($mapelId, $tingkatId, $semester, $tahunPelajaran)
    {
        return $this->db->table('e_bobot_penilaian')
            ->where('mapel_id', $mapelId)
            ->where('tingkat_id', $tingkatId)
            ->where('semester', $semester)
            ->where('tahun_pelajaran', $tahunPelajaran)
            ->where('is_aktif', 1)
            ->get()
            ->getResult();
    }
    
    /**
     * Simpan nilai komponen penilaian
     */
    public function simpanNilaiKomponen($data)
    {
        // Cek apakah sudah ada nilai untuk kombinasi ini
        $existing = $this->where([
            'siswa_id' => $data['siswa_id'],
            'mapel_id' => $data['mapel_id'],
            'komponen_id' => $data['komponen_id'],
            'jenis_penilaian' => $data['jenis_penilaian'],
            'semester' => $data['semester'],
            'tahun_pelajaran' => $data['tahun_pelajaran']
        ])->first();
        
        if ($existing) {
            // Update nilai yang sudah ada
            return $this->update($existing->id_nilai, $data);
        } else {
            // Insert nilai baru
            return $this->insert($data);
        }
    }
    
    /**
     * Hitung dan simpan nilai akhir berdasarkan bobot komponen
     */
    public function hitungDanSimpanNilaiAkhir($siswaId, $mapelId, $tpId, $smtId)
    {
        $tp = $this->db->table('master_tp')->where('id_tp', $tpId)->get()->getRow();
        $smt = $this->db->table('master_smt')->where('id_smt', $smtId)->get()->getRow();
        
        if (!$tp || !$smt) {
            return false;
        }
        
        // Ambil semua nilai komponen untuk siswa dan mapel ini
        $nilaiKomponen = $this->where([
            'siswa_id' => $siswaId,
            'mapel_id' => $mapelId,
            'semester' => $smtId,
            'tahun_pelajaran' => $tp->tahun
        ])->findAll();
        
        // Hitung nilai per aspek
        $nilaiPengetahuan = 0;
        $nilaiKeterampilan = 0;
        $nilaiSikap = 0;
        $bobotPengetahuan = 0;
        $bobotKeterampilan = 0;
        $bobotSikap = 0;
        
        foreach ($nilaiKomponen as $nilai) {
            $komponen = $this->getKomponenById($nilai->komponen_id);
            if ($komponen) {
                switch ($komponen->jenis_penilaian) {
                    case 'Pengetahuan':
                        $nilaiPengetahuan += ($nilai->nilai * $komponen->bobot_minimal);
                        $bobotPengetahuan += $komponen->bobot_minimal;
                        break;
                    case 'Keterampilan':
                        $nilaiKeterampilan += ($nilai->nilai * $komponen->bobot_minimal);
                        $bobotKeterampilan += $komponen->bobot_minimal;
                        break;
                    case 'Sikap':
                        $nilaiSikap += ($nilai->nilai * $komponen->bobot_minimal);
                        $bobotSikap += $komponen->bobot_minimal;
                        break;
                }
            }
        }
        
        // Hitung rata-rata per aspek
        $rataPengetahuan = $bobotPengetahuan > 0 ? $nilaiPengetahuan / $bobotPengetahuan : 0;
        $rataKeterampilan = $bobotKeterampilan > 0 ? $nilaiKeterampilan / $bobotKeterampilan : 0;
        $rataSikap = $bobotSikap > 0 ? $nilaiSikap / $bobotSikap : 0;
        
        // Hitung nilai akhir dengan bobot standar
        // Bobot: Pengetahuan 40%, Keterampilan 40%, Sikap 20%
        $nilaiAkhir = ($rataPengetahuan * 0.4) + ($rataKeterampilan * 0.4) + ($rataSikap * 0.2);
        
        // Tentukan predikat
        $predikatPengetahuan = $this->tentukanPredikat($rataPengetahuan, 'Pengetahuan');
        $predikatKeterampilan = $this->tentukanPredikat($rataKeterampilan, 'Keterampilan');
        $predikatSikap = $this->tentukanPredikat($rataSikap, 'Sikap');
        $predikatAkhir = $this->tentukanPredikat($nilaiAkhir, 'Pengetahuan');
        
        // Buat deskripsi
        $deskripsiPengetahuan = $this->buatDeskripsi($rataPengetahuan, 'Pengetahuan');
        $deskripsiKeterampilan = $this->buatDeskripsi($rataKeterampilan, 'Keterampilan');
        $deskripsiSikap = $this->buatDeskripsi($rataSikap, 'Sikap');
        $deskripsiAkhir = $this->buatDeskripsi($nilaiAkhir, 'Pengetahuan');
        
        // Simpan nilai akhir
        $nilaiAkhirData = [
            'siswa_id' => $siswaId,
            'mapel_id' => $mapelId,
            'nilai_pengetahuan' => round($rataPengetahuan, 2),
            'predikat_pengetahuan' => $predikatPengetahuan,
            'deskripsi_pengetahuan' => $deskripsiPengetahuan,
            'nilai_keterampilan' => round($rataKeterampilan, 2),
            'predikat_keterampilan' => $predikatKeterampilan,
            'deskripsi_keterampilan' => $deskripsiKeterampilan,
            'nilai_sikap' => round($rataSikap, 2),
            'predikat_sikap' => $predikatSikap,
            'deskripsi_sikap' => $deskripsiSikap,
            'nilai_akhir' => round($nilaiAkhir, 2),
            'predikat_akhir' => $predikatAkhir,
            'deskripsi_akhir' => $deskripsiAkhir,
            'semester' => $smtId,
            'tahun_pelajaran' => $tp->tahun,
            'status' => 'Draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        // Cek apakah sudah ada nilai akhir
        $existing = $this->db->table('e_nilai_akhir')
            ->where('siswa_id', $siswaId)
            ->where('mapel_id', $mapelId)
            ->where('semester', $smtId)
            ->where('tahun_pelajaran', $tp->tahun)
            ->first();
        
        if ($existing) {
            return $this->db->table('e_nilai_akhir')->where('id_nilai_akhir', $existing->id_nilai_akhir)->update($nilaiAkhirData);
        } else {
            return $this->db->table('e_nilai_akhir')->insert($nilaiAkhirData);
        }
    }
    
    /**
     * Tentukan predikat berdasarkan nilai dan jenis penilaian
     */
    private function tentukanPredikat($nilai, $jenisPenilaian)
    {
        if ($jenisPenilaian == 'Pengetahuan' || $jenisPenilaian == 'Sikap') {
            if ($nilai >= 90) return 'A';
            if ($nilai >= 80) return 'B';
            if ($nilai >= 70) return 'C';
            return 'D';
        } elseif ($jenisPenilaian == 'Keterampilan') {
            if ($nilai >= 90) return 'SB'; // Sangat Baik
            if ($nilai >= 80) return 'B';  // Baik
            if ($nilai >= 70) return 'C';  // Cukup
            return 'K'; // Kurang
        }
        return null;
    }
    
    /**
     * Buat deskripsi capaian berdasarkan nilai
     */
    private function buatDeskripsi($nilai, $jenisPenilaian)
    {
        $predikat = $this->tentukanPredikat($nilai, $jenisPenilaian);
        
        $deskripsi = [
            'Pengetahuan' => [
                'A' => 'Sangat baik dalam memahami dan menguasai konsep',
                'B' => 'Baik dalam memahami dan menguasai konsep',
                'C' => 'Cukup dalam memahami dan menguasai konsep',
                'D' => 'Perlu bimbingan dalam memahami konsep'
            ],
            'Keterampilan' => [
                'SB' => 'Sangat terampil dalam menerapkan konsep',
                'B' => 'Terampil dalam menerapkan konsep',
                'C' => 'Cukup terampil dalam menerapkan konsep',
                'K' => 'Perlu latihan dalam menerapkan konsep'
            ],
            'Sikap' => [
                'A' => 'Sangat baik dalam menunjukkan sikap positif',
                'B' => 'Baik dalam menunjukkan sikap positif',
                'C' => 'Cukup dalam menunjukkan sikap positif',
                'D' => 'Perlu pembinaan dalam sikap'
            ]
        ];
        
        return $deskripsi[$jenisPenilaian][$predikat] ?? 'Deskripsi tidak tersedia';
    }
    
    /**
     * Ambil data portofolio digital siswa
     */
    public function getPortofolioBySiswa($siswaId, $tpId, $smtId)
    {
        return $this->db->table('e_portofolio_digital')
            ->where('siswa_id', $siswaId)
            ->where('created_at >=', $tpId . '-01-01')
            ->where('created_at <=', $tpId . '-12-31')
            ->orderBy('tanggal_kegiatan', 'DESC')
            ->get()
            ->getResult();
    }
    
    /**
     * Simpan portofolio digital
     */
    public function simpanPortofolio($data)
    {
        return $this->db->table('e_portofolio_digital')->insert($data);
    }
    
    /**
     * Ambil data P5 report
     */
    public function getP5ReportBySiswa($siswaId, $tpId, $smtId)
    {
        return $this->db->table('e_p5_report')
            ->where('siswa_id', $siswaId)
            ->where('semester', $smtId)
            ->where('tahun_pelajaran', $tpId)
            ->orderBy('tanggal_mulai', 'DESC')
            ->get()
            ->getResult();
    }
    
    /**
     * Ambil data P5 yang belum disync
     */
    public function getP5BelumSync($tpId, $smtId)
    {
        return $this->db->table('e_p5_report')
            ->where('sync_status', 'Belum_Sync')
            ->where('semester', $smtId)
            ->where('tahun_pelajaran', $tpId)
            ->get()
            ->getResult();
    }
    
    /**
     * Ambil data P5 yang sudah disync
     */
    public function getP5SudahSync($tpId, $smtId)
    {
        return $this->db->table('e_p5_report')
            ->where('sync_status', 'Sudah_Sync')
            ->where('semester', $smtId)
            ->where('tahun_pelajaran', $tpId)
            ->get()
            ->getResult();
    }
    
    /**
     * Update status sync P5
     */
    public function updateSyncStatus($idReport, $status, $data = [])
    {
        $updateData = array_merge(['sync_status' => $status], $data);
        return $this->db->table('e_p5_report')->where('id_report', $idReport)->update($updateData);
    }
    
    /**
     * Ambil data audit log
     */
    public function getAuditLog($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->db->table('e_data_audit')
            ->where('waktu_aksi >=', $startDate . ' 00:00:00')
            ->orderBy('waktu_aksi', 'DESC')
            ->get()
            ->getResult();
    }
    
    /**
     * Simpan audit log
     */
    public function simpanAuditLog($data)
    {
        return $this->db->table('e_data_audit')->insert($data);
    }
    
    /**
     * Ambil statistik kepatuhan
     */
    public function getKepatuhanStats()
    {
        $total = $this->db->table('e_data_audit')->countAll();
        $valid = $this->db->table('e_data_audit')->where('status_kepatuhan', 'Valid')->countAllResults();
        $warning = $this->db->table('e_data_audit')->where('status_kepatuhan', 'Warning')->countAllResults();
        $violation = $this->db->table('e_data_audit')->where('status_kepatuhan', 'Violation')->countAllResults();
        
        return [
            'total' => $total,
            'valid' => $valid,
            'warning' => $warning,
            'violation' => $violation,
            'persentase_valid' => $total > 0 ? round(($valid / $total) * 100, 2) : 0,
            'persentase_warning' => $total > 0 ? round(($warning / $total) * 100, 2) : 0,
            'persentase_violation' => $total > 0 ? round(($violation / $total) * 100, 2) : 0,
        ];
    }
    
    /**
     * Ambil potensi pelanggaran
     */
    public function getPotensiPelanggaran()
    {
        // Ambil audit log dengan status warning atau violation dalam 7 hari terakhir
        $startDate = date('Y-m-d', strtotime('-7 days'));
        
        return $this->db->table('e_data_audit')
            ->where('waktu_aksi >=', $startDate . ' 00:00:00')
            ->whereIn('status_kepatuhan', ['Warning', 'Violation'])
            ->orderBy('waktu_aksi', 'DESC')
            ->limit(10)
            ->get()
            ->getResult();
    }
    
    /**
     * Ambil statistik penilaian
     */
    public function getStatistikPenilaian($tpId, $smtId)
    {
        $tp = $this->db->table('master_tp')->where('id_tp', $tpId)->get()->getRow();
        
        if (!$tp) {
            return [];
        }
        
        // Hitung jumlah penilaian per komponen
        $penilaianPerKomponen = $this->db->table('e_nilai_komponen')
            ->select('jenis_penilaian, COUNT(*) as jumlah')
            ->where('semester', $smtId)
            ->where('tahun_pelajaran', $tp->tahun)
            ->groupBy('jenis_penilaian')
            ->get()
            ->getResult();
        
        // Hitung jumlah portofolio
        $jumlahPortofolio = $this->db->table('e_portofolio_digital')
            ->where('created_at >=', $tpId . '-01-01')
            ->where('created_at <=', $tpId . '-12-31')
            ->countAllResults();
        
        // Hitung jumlah P5 report
        $jumlahP5 = $this->db->table('e_p5_report')
            ->where('semester', $smtId)
            ->where('tahun_pelajaran', $tp->tahun)
            ->countAllResults();
        
        // Hitung jumlah nilai akhir
        $jumlahNilaiAkhir = $this->db->table('e_nilai_akhir')
            ->where('semester', $smtId)
            ->where('tahun_pelajaran', $tp->tahun)
            ->countAllResults();
        
        return [
            'penilaian_per_komponen' => $penilaianPerKomponen,
            'jumlah_portofolio' => $jumlahPortofolio,
            'jumlah_p5' => $jumlahP5,
            'jumlah_nilai_akhir' => $jumlahNilaiAkhir,
        ];
    }
    
    /**
     * Ambil siswa berdasarkan guru
     */
    public function getSiswaByGuru($guruId, $tpId, $smtId)
    {
        return $this->db->table('master_siswa s')
            ->select('s.*, k.nama_kelas')
            ->join('master_kelas k', 'k.id_kelas = s.id_kelas')
            ->where('s.id_tp', $tpId)
            ->where('s.id_smt', $smtId)
            ->where('k.id_guru', $guruId)
            ->orderBy('s.nama', 'ASC')
            ->get()
            ->getResult();
    }
    
    /**
     * Ambil siswa berdasarkan kelas
     */
    public function getSiswaByKelas($kelasId, $tpId, $smtId)
    {
        return $this->db->table('master_siswa')
            ->where('id_kelas', $kelasId)
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('status', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();
    }
    
    /**
     * Ambil siswa berdasarkan user ID
     */
    public function getSiswaByUserId($userId, $tpId, $smtId)
    {
        return $this->db->table('master_siswa')
            ->where('id_user', $userId)
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('status', 1)
            ->get()
            ->getRow();
    }
    
    /**
     * Ambil siswa berdasarkan ID
     */
    public function getSiswaById($siswaId)
    {
        return $this->db->table('master_siswa s')
            ->select('s.*, k.nama_kelas, j.nama_jurusan')
            ->join('master_kelas k', 'k.id_kelas = s.id_kelas', 'left')
            ->join('master_jurusan j', 'j.id_jurusan = s.id_jurusan', 'left')
            ->where('s.id_siswa', $siswaId)
            ->get()
            ->getRow();
    }
    
    /**
     * Ambil nilai berdasarkan kelas dan mapel
     */
    public function getNilaiByKelasMapel($kelasId, $mapelId, $tpId, $smtId)
    {
        $tp = $this->db->table('master_tp')->where('id_tp', $tpId)->get()->getRow();
        
        return $this->db->table('e_nilai_komponen n')
            ->select('n.*, s.nama as nama_siswa, s.nis, k.nama as nama_komponen, k.jenis_penilaian')
            ->join('master_siswa s', 's.id_siswa = n.siswa_id')
            ->join('e_komponen_penilaian k', 'k.id_komponen = n.komponen_id')
            ->where('s.id_kelas', $kelasId)
            ->where('n.mapel_id', $mapelId)
            ->where('n.semester', $smtId)
            ->where('n.tahun_pelajaran', $tp->tahun)
            ->orderBy('s.nama', 'ASC')
            ->get()
            ->getResult();
    }
}
