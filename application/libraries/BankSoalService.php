<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bank Soal Service
 * Menangani business logic untuk Bank Soal
 */
class BankSoalService
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Cbt_model', 'cbt');
        $this->CI->load->model('Master_model', 'master');
    }

    /**
     * Unserialize data (replacement for MY_Controller::maybe_unserialize)
     * @param mixed $data
     * @return mixed
     */
    private function unserialize($data)
    {
        if (is_string($data)) {
            $unserialized = @unserialize($data);
            if ($unserialized !== false || $data === 'b:0;') {
                return $unserialized;
            }
            // Try JSON decode
            $json = @json_decode($data);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        return $data;
    }

    /**
     * Get bank soal with filters
     * @param int $id_tp - Required
     * @param int $id_smt - Required
     * @param int|null $id_guru - Optional
     * @param int|null $id_mapel - Optional
     * @param int|null $id_level - Optional
     * @return array
     */
    public function getBanksWithUsage($id_tp, $id_smt, $id_guru = null, $id_mapel = null, $id_level = null)
    {
        $banks = $this->CI->cbt->getDataBank($id_guru, $id_mapel, $id_level);
        
        if (!$banks || count($banks) === 0) {
            return [
                'banks' => [],
                'total_siswa' => []
            ];
        }

        // Collect all bank IDs
        $ids = [];
        foreach ($banks as $bank) {
            foreach ($bank as $tp) {
                foreach ($tp as $smt) {
                    $ids[] = $smt->id_bank;
                    $smt->bank_kelas = $this->unserialize($smt->bank_kelas ?? '[]');
                }
            }
        }

        // Get usage statistics
        $jadwal_terpakai = [];
        if (count($ids) > 0) {
            $terpakai = $this->getBankUsageByIds($ids);
            foreach ($terpakai as $idj => $rows) {
                if ($rows) {
                    $jadwal_terpakai[$idj] = count($rows);
                }
            }
        }

        return [
            'banks' => $banks,
            'total_siswa' => $jadwal_terpakai
        ];
    }

    /**
     * Get bank usage statistics
     * @param array $id_banks
     * @return array
     */
    public function getBankUsageByIds($id_banks)
    {
        $this->CI->db->select('id_bank,id_siswa');
        $this->CI->db->from('cbt_siswa');
        $this->CI->db->where_in('id_bank', $id_banks);
        $result = $this->CI->db->get()->result();
        
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_bank][$row->id_siswa] = $row;
        }
        return $ret;
    }

    /**
     * Copy bank soal to current period
     * @param int $id_bank
     * @param int $id_tp_target
     * @param int $id_smt_target
     * @return bool
     */
    public function copyBankSoal($id_bank, $id_tp_target, $id_smt_target)
    {
        $this->CI->load->model('Dropdown_model', 'dropdown');
        
        $bank = $this->CI->db->get_where('cbt_bank_soal', ['id_bank' => $id_bank])->row();
        if (!$bank) {
            return false;
        }

        $soals = $this->CI->cbt->getAllSoalByBank($id_bank);

        // Map old classes to new classes
        $arrKls = $this->unserialize($bank->bank_kelas);
        $kelasLalu = $this->getKelasByIds(array_column($arrKls, 'kelas_id'));
        
        $map_lalu = [];
        foreach ($kelasLalu as $item) {
            $kode = $this->normalize($item->kode_kelas);
            $map_lalu[] = $kode;
        }

        $kelas_now = $this->CI->dropdown->getAllKodeKelas($id_tp_target, $id_smt_target);
        $kelasSkrg = [];
        foreach ($kelas_now as $ids => $kode_now) {
            $kode_n = $this->normalize($kode_now);
            if (in_array($kode_n, $map_lalu)) {
                $kelasSkrg[] = ['kelas_id' => $ids];
            }
        }

        // Create new bank
        $unik = $this->generateUniqueId();
        $bank->id_tp = $id_tp_target;
        $bank->id_smt = $id_smt_target;
        $bank->bank_kode = $bank->bank_kode . '_' . $unik;
        $bank->bank_kelas = json_encode($kelasSkrg);
        $bank->date = date('Y-m-d H:i:s');
        unset($bank->id_bank);

        $result = $this->CI->db->insert('cbt_bank_soal', $bank);
        $id = $this->CI->db->insert_id();

        // Copy soals
        if ($soals && count($soals) > 0) {
            foreach ($soals as $soal) {
                unset($soal->id_soal);
                $soal->bank_id = $id;
                $soal->created_on = time();
                $soal->updated_on = time();
            }
            $this->CI->db->insert_batch('cbt_soal', $soals);
        }

        return $result;
    }

    /**
     * Get classes by IDs
     * @param array $ids
     * @return array
     */
    public function getKelasByIds($ids)
    {
        $this->CI->db->select('id_kelas, nama_kelas, kode_kelas');
        $this->CI->db->where_in('id_kelas', $ids);
        return $this->CI->db->get('master_kelas')->result();
    }

    /**
     * Normalize string for comparison
     * @param string $str
     * @return string
     */
    private function normalize($str)
    {
        return strtolower(preg_replace('/[\s.\-_]/', '', $str ?? ''));
    }

    /**
     * Generate unique ID
     * @return string
     */
    private function generateUniqueId()
    {
        return substr(md5(uniqid(rand(), true)), 0, 6);
    }

    /**
     * Validate bank can be deleted
     * @param int|array $id_banks
     * @return array ['can_delete' => bool, 'message' => string]
     */
    public function validateBankDeletion($id_banks)
    {
        $count = $this->CI->cbt->cekJadwalBankSoal($id_banks);
        
        if ($count > 0) {
            return [
                'can_delete' => false,
                'message' => 'Ada jadwal ujian yang menggunakan bank soal ini'
            ];
        }

        return [
            'can_delete' => true,
            'message' => ''
        ];
    }

    /**
     * Prepare data for add/edit bank form - for Guru
     * @param object $guru
     * @param int $id_tp
     * @param int $id_smt
     * @return array
     */
    public function prepareGuruBankFormData($guru, $id_tp, $id_smt)
    {
        $this->CI->load->model('Kelas_model', 'kelas');
        $this->CI->load->model('Dropdown_model', 'dropdown');

        $mapel_guru = $this->CI->kelas->getGuruMapelKelas($guru->id_guru, $id_tp, $id_smt);
        $mapel = $this->unserialize($mapel_guru->mapel_kelas ?? '[]');

        $arrMapel = [];
        $arrKelas = [];
        
        if ($mapel !== false && $mapel !== null) {
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
                foreach ($m->kelas_mapel as $kls) {
                    $arrKelas[$m->id_mapel][] = [
                        'id_kelas' => $kls->kelas,
                        'nama_kelas' => $this->CI->dropdown->getNamaKelasById($id_tp, $id_smt, $kls->kelas)
                    ];
                }
            }
        }

        // Get kelas IDs for first mapel
        $arrId = [];
        if ($mapel && count($mapel) > 0) {
            foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                $arrId[] = $id_mapel->kelas;
            }
        }

        return [
            'mapel_guru' => $mapel_guru,
            'mapel' => $arrMapel,
            'arrkelas' => $arrKelas,
            'kelas' => count($arrId) > 0 ? 
                $this->CI->dropdown->getAllKelasByArrayId($id_tp, $id_smt, $arrId) : [],
            'gurus' => [$guru->id_guru => $guru->nama_guru],
            'guru' => $guru,
            'id_guru' => $guru->id_guru
        ];
    }

    /**
     * Prepare data for buat soal form
     * @param int $id_bank
     * @param int $jenis - Jenis soal (1=PG, 2=Kompleks, 3=Jodohkan, 4=Isian, 5=Esai)
     * @return array
     */
    public function prepareBuatSoalData($id_bank, $jenis)
    {
        $bank = $this->CI->cbt->getDataBankById($id_bank);
        
        if (!$bank) {
            return null;
        }

        $soal_ada = $this->CI->cbt->cekSoalAda($id_bank, $jenis);
        $data_komplit = $this->CI->cbt->cekSoalBelumKomplit($jenis, $bank->opsi);
        
        $result = [
            'bank' => $bank,
            'soal_ada' => $soal_ada,
            'soal_belum_komplit' => isset($data_komplit[$id_bank]) ? $data_komplit[$id_bank] : [],
            'soals' => $this->CI->cbt->getAllSoalByBank($id_bank, $jenis)
        ];

        // Add jenis-specific data
        switch ($jenis) {
            case '1':
                $result['jml_pg'] = $this->CI->cbt->getNomorSoalTerbesar($id_bank, 1);
                break;
            case '2':
                $result['jml_pg2'] = $this->CI->cbt->getNomorSoalTerbesar($id_bank, 2);
                break;
            case '3':
                $result['jml_jodohkan'] = $this->CI->cbt->getNomorSoalTerbesar($id_bank, 3);
                break;
            case '4':
                $result['jml_isian'] = $this->CI->cbt->getNomorSoalTerbesar($id_bank, 4);
                break;
            case '5':
                $result['jml_essai'] = $this->CI->cbt->getNomorSoalTerbesar($id_bank, 5);
                break;
        }

        return $result;
    }

    /**
     * Process mapel data for admin/guru
     * @param int $id_guru
     * @param int $id_tp
     * @param int $id_smt
     * @return array
     */
    public function getMapelGuruData($id_guru, $id_tp, $id_smt)
    {
        $this->CI->load->model('Kelas_model', 'kelas');
        
        $mapel_guru = $this->CI->kelas->getGuruMapelKelas($id_guru, $id_tp, $id_smt);
        $mapel = $this->unserialize($mapel_guru->mapel_kelas ?? '[]');

        $arrMapel = [];
        if ($mapel !== false && $mapel !== null) {
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
            }
        }

        return [
            'mapel_guru' => $mapel_guru,
            'mapel' => $arrMapel
        ];
    }

    /**
     * Get kelas by level with mapel filter
     * @param int $level
     * @param int $id_guru
     * @param int $id_mapel
     * @param int $id_tp
     * @param int $id_smt
     * @return array
     */
    public function getKelasByLevelAndMapel($level, $id_guru, $id_mapel, $id_tp, $id_smt)
    {
        $this->CI->load->model('Kelas_model', 'kelas');
        
        $mapel_guru = $this->CI->kelas->getGuruMapelKelas($id_guru, $id_tp, $id_smt);
        $arrKelas = [];
        $arrMapel = [];
        
        $mapel = $this->unserialize($mapel_guru->mapel_kelas ?? '[]');
        if ($mapel !== false) {
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
                if ($id_mapel == $m->id_mapel) {
                    foreach ($m->kelas_mapel as $kls) {
                        array_push($arrKelas, $kls->kelas);
                    }
                }
            }
        }

        return [
            'mapel' => $arrMapel,
            'kelas' => count($arrKelas) > 0 ? 
                $this->CI->cbt->getKelasByLevel($level, $arrKelas) : []
        ];
    }
}
