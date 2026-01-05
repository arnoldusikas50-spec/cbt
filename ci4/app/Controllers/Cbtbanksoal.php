<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\CbtModel;
use App\Models\LogModel;

class Cbtbanksoal extends BaseController
{
    protected MasterModel $master;
    protected DashboardModel $dashboard;
    protected CbtModel $cbt;
    protected LogModel $logging;

    public function __construct()
    {
        $this->master = new MasterModel();
        $this->dashboard = new DashboardModel();
        $this->cbt = new CbtModel();
        $this->logging = new LogModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Bank Soal',
            'subjudul' => 'Soal',
            'setting' => $setting,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'levels' => $this->master->getLevelsByJenjang($setting->jenjang),
        ];

        $type = $this->request->getGet('type');
        $mode = $this->request->getGet('mode') ?? '1';
        $data['mode'] = $mode;
        $data['id_filter'] = $type ?? '';

        if ($this->isAdmin() || $this->inGroup('panitia')) {
            $data['mapels'] = $this->master->getAllMapel();
            $data['gurus'] = $this->master->getAllGuru();
            $data['kelas'] = $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt);
            $data['filters'] = ['0' => 'Semua', '1' => 'Guru', '2' => 'Mapel', '3' => 'Level'];

            $id_guru = null;
            $id_mapel = null;
            $id_level = null;

            if ($type == '1') {
                $id_guru = $this->request->getGet('id');
                $data['id_guru'] = $id_guru;
            } elseif ($type == '2') {
                $id_mapel = $this->request->getGet('id');
                $data['id_mapel'] = $id_mapel;
            } elseif ($type == '3') {
                $id_level = $this->request->getGet('id');
                $data['id_level'] = $id_level;
            }

            if ($type !== null && $type !== '0') {
                $data['banks'] = $this->cbt->getDataBank($id_guru, $id_mapel, $id_level);
            } else {
                $data['banks'] = $this->cbt->getDataBank();
            }

            $data['is_panitia'] = $this->inGroup('panitia');
            return view('_templates/dashboard/_header', $data)
                . view('_templates/dashboard/navbar', $data)
                . view('_templates/dashboard/sidebar', $data)
                . view('cbt/banksoal/data', $data)
                . view('_templates/dashboard/_footer');
        } else if ($this->inGroup('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);
            $data['guru'] = $guru;
            $data['filters'] = ['0' => 'Semua', '2' => 'Mapel', '3' => 'Level'];

            $id_mapel = null;
            $id_level = null;

            if ($type == '2') {
                $id_mapel = $this->request->getGet('id');
                $data['id_mapel'] = $id_mapel;
            } elseif ($type == '3') {
                $id_level = $this->request->getGet('id');
                $data['id_level'] = $id_level;
            }

            if ($type !== null) {
                $data['banks'] = $this->cbt->getDataBank((int) $guru->id_guru, $id_mapel, $id_level);
            } else {
                $data['banks'] = $this->cbt->getDataBank((int) $guru->id_guru);
            }

            $data['is_panitia'] = $this->inGroup('panitia');
            return view('_templates/dashboard/_header', $data)
                . view('_templates/dashboard/navbar', $data)
                . view('_templates/dashboard/sidebar', $data)
                . view('cbt/banksoal/data', $data)
                . view('_templates/dashboard/_footer');
        }

        return redirect()->to('dashboard');
    }

    public function get_detail()
    {
        try {
            $id = $this->request->getPost('id_bank');
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $bank = $this->cbt->getBankSoalById((int) $id);
            if ($bank) {
                $bank->bank_kelas = json_decode($bank->bank_kelas ?? '[]');
            }

            $soals = $this->cbt->getAllSoalByBank((int) $id);
            foreach ($soals as $soal) {
                if ($soal->jenis == '2' || $soal->jenis == '3') {
                    $soal->opsi_a = json_decode($soal->opsi_a ?? '[]');
                    $soal->jawaban = json_decode($soal->jawaban ?? '[]');
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'bank' => $bank,
                'soals' => $soals,
                'kelas' => $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getMapelGuru()
    {
        try {
            $id_guru = $this->request->getGet('id_guru');
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $mapel_guru = $this->master->getGuruMapelKelas((int) $id_guru, (int) $tp->id_tp, (int) $smt->id_smt);
            if (!$mapel_guru)
                return $this->response->setJSON([]);

            $mapel = json_decode($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                }
            }

            return $this->response->setJSON($arrMapel);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getKelasLevel()
    {
        try {
            $level = $this->request->getGet('level');
            $id_guru = $this->request->getGet('id_guru');
            $id_mapel = $this->request->getGet('mapel');

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $mapel_guru = $this->master->getGuruMapelKelas((int) $id_guru, (int) $tp->id_tp, (int) $smt->id_smt);

            $arrKelas = [];
            $arrMapel = [];
            if ($mapel_guru) {
                $mapel = json_decode($mapel_guru->mapel_kelas ?? '[]');
                if ($mapel) {
                    foreach ($mapel as $m) {
                        $arrMapel[$m->id_mapel] = $m->nama_mapel;
                        if ($id_mapel == $m->id_mapel) {
                            foreach ($m->kelas_mapel as $kls) {
                                $arrKelas[] = $kls->kelas;
                            }
                        }
                    }
                }
            }

            return $this->response->setJSON([
                'mapel' => $arrMapel,
                'kelas' => count($arrKelas) > 0 ? $this->cbt->getKelasByLevel((int) $level, $arrKelas) : []
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function addBank()
    {
        if ($this->inGroup('panitia')) {
            return redirect()->to('dashboard')->with('error', 'Akses Terlarang');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Bank Soal',
            'subjudul' => 'Buat Bank Soal',
            'setting' => $setting,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'bank' => (object) $this->cbt->dummy($setting->jenjang),
            'jenis' => $this->cbt->getAllJenisUjian(),
            'jurusan' => $this->cbt->getAllJurusan(),
            'level' => $this->master->getLevelsByJenjang($setting->jenjang),
            'mapel_agama' => $this->master->getAgamaSiswa(),
            'is_admin' => $this->isAdmin()
        ];

        if ($this->isAdmin()) {
            $data['kelas'] = $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt);
            $data['id_guru'] = '';
            $data['gurus'] = $this->master->getAllGuru();
            $data['mapel'] = $this->master->getAllMapel();
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);
            $data['gurus'] = [$guru->id_guru => $guru->nama_guru];
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->master->getGuruMapelKelas((int) $guru->id_guru, (int) $tp->id_tp, (int) $smt->id_smt);
            $mapel = json_decode($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $arrKelas[$m->id_mapel][] = [
                            'id_kelas' => $kls->kelas,
                            'nama_kelas' => $this->master->getKelas((int) $kls->kelas)->nama_kelas ?? '-'
                        ];
                    }
                }
            }

            $arrId = [];
            if (count($mapel) > 0) {
                foreach ($mapel[0]->kelas_mapel as $km) {
                    $arrId[] = $km->kelas;
                }
            }

            $data['mapel_guru'] = $mapel_guru;
            $data['mapel'] = $arrMapel;
            $data['arrkelas'] = $arrKelas;
            $data['kelas'] = count($arrId) > 0 ? $this->master->getAllKelasByArrayId((int) $tp->id_tp, (int) $smt->id_smt, $arrId) : [];
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('cbt/banksoal/add', $data)
            . view('_templates/dashboard/_footer');
    }

    public function editBank($id_bank)
    {
        $id_guru = $this->request->getGet('id_guru');
        $setting = $this->dashboard->getSetting();
        $user = $this->authModel->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Edit Bank Soal',
            'subjudul' => 'Edit Bank Soal',
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'setting' => $setting,
            'jenis' => $this->cbt->getAllJenisUjian(),
            'jurusan' => $this->cbt->getAllJurusan(),
            'level' => $this->master->getLevelsByJenjang($setting->jenjang),
            'kelas' => $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt),
            'mapel_agama' => $this->master->getAgamaSiswa(),
            'is_admin' => $this->isAdmin()
        ];

        $bank = $this->cbt->getBankSoalById((int) $id_bank);
        $bank->bank_kelas = json_decode($bank->bank_kelas ?? '[]');
        $data['bank'] = $bank;

        if ($this->isAdmin()) {
            $data['id_guru'] = $id_guru;
            $data['gurus'] = $this->master->getAllGuru();
            $data['mapel'] = $this->master->getAllMapel();
            $data['mapel_guru'] = $this->master->getGuruMapelKelas((int) $id_guru, (int) $tp->id_tp, (int) $smt->id_smt);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);
            $data['gurus'] = [$guru->id_guru => $guru->nama_guru];
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->master->getGuruMapelKelas((int) $guru->id_guru, (int) $tp->id_tp, (int) $smt->id_smt);
            $mapel = json_decode($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            if ($mapel) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                }
            }

            $data['mapel_guru'] = $mapel_guru;
            $data['mapel'] = $arrMapel;
        }

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('cbt/banksoal/add', $data)
            . view('_templates/dashboard/_footer');
    }

    public function saveBank()
    {
        try {
            if ($this->request->getPost()) {
                $tp = $this->dashboard->getTahunActive();
                $smt = $this->dashboard->getSemesterActive();
                $id_bank = $this->request->getPost('id_bank');

                $kelasPost = $this->request->getPost('kelas');
                $arrKelas = [];
                if ($kelasPost) {
                    foreach ($kelasPost as $k) {
                        $arrKelas[] = ['kelas_id' => $k];
                    }
                }

                $data = [
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'bank_kode' => $this->request->getPost('kode'),
                    'bank_jenis_id' => $this->request->getPost('jenis'),
                    'bank_mapel_id' => $this->request->getPost('mapel'),
                    'bank_kelas' => json_encode($arrKelas),
                    'bank_level' => $this->request->getPost('level'),
                    'bank_guru_id' => $this->request->getPost('guru'),
                    'jml_soal' => $this->request->getPost('tampil_pg'),
                    'tampil_pg' => $this->request->getPost('tampil_pg'),
                    'bobot_pg' => $this->request->getPost('bobot_pg'),
                    'opsi' => $this->request->getPost('opsi'),
                    'jml_kompleks' => $this->request->getPost('tampil_kompleks'),
                    'tampil_kompleks' => $this->request->getPost('tampil_kompleks'),
                    'bobot_kompleks' => $this->request->getPost('bobot_kompleks'),
                    'jml_jodohkan' => $this->request->getPost('tampil_jodohkan'),
                    'tampil_jodohkan' => $this->request->getPost('tampil_jodohkan'),
                    'bobot_jodohkan' => $this->request->getPost('bobot_jodohkan'),
                    'jml_isian' => $this->request->getPost('tampil_isian'),
                    'tampil_isian' => $this->request->getPost('tampil_isian'),
                    'bobot_isian' => $this->request->getPost('bobot_isian'),
                    'jml_esai' => $this->request->getPost('bobot_esai'),
                    'bobot_esai' => $this->request->getPost('bobot_esai'),
                    'tampil_esai' => $this->request->getPost('tampil_esai'),
                    'tampil_urutan' => $this->request->getPost('tampil_urutan'),
                    'bobot_urutan' => $this->request->getPost('bobot_urutan'),
                    'tampil_cloze' => $this->request->getPost('tampil_cloze'),
                    'bobot_cloze' => $this->request->getPost('bobot_cloze'),
                    'status' => $this->request->getPost('status'),
                    'soal_agama' => $this->request->getPost('soal_agama'),
                ];

                $result = $this->cbt->saveBankSoal($data, $id_bank ? (int) $id_bank : null);

                if ($id_bank) {
                    $this->logging->saveLog(4, 'mengedit bank soal');
                } else {
                    $this->logging->saveLog(3, 'menambah bank soal');
                }

                return $this->response->setJSON(['status' => (bool) $result, 'message' => $result ? 'Berhasil menyimpan bank soal' : 'Gagal menyimpan bank soal']);
            }
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data yang dikirim']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteBank()
    {
        try {
            $id = $this->request->getGet('id_bank');
            // Simple check for now
            $db = \Config\Database::connect();
            $used = $db->table('cbt_jadwal')->where('id_bank', $id)->countAllResults();

            if ($used > 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal sedang digunakan dalam jadwal ujian.']);
            }

            $db->transBegin();
            $db->table('cbt_soal')->where('bank_id', $id)->delete();
            $db->table('cbt_bank_soal')->where('id_bank', $id)->delete();

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus bank soal.']);
            }

            $db->transCommit();
            $this->logging->saveLog(5, 'menghapus bank soal');
            return $this->response->setJSON(['status' => true, 'message' => 'Bank soal berhasil dihapus.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function copyBankSoal($id_bank)
    {
        try {
            if ($this->inGroup('panitia') || session()->get('role') == '4') {
                return $this->response->setJSON(['status' => false, 'message' => 'Panitia tidak diizinkan menyalin Bank Soal']);
            }

            $tp = $this->master->getTahunActive();
            $smt = $this->master->getSemesterActive();

            $db = \Config\Database::connect();
            $bank = $db->table('cbt_bank_soal')->where('id_bank', $id_bank)->get()->getRow();
            if (!$bank) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }

            $soals = $this->cbt->getAllSoalByBank((int) $id_bank);

            $bank_kelas = json_decode($bank->bank_kelas ?? '[]');
            if (is_string($bank_kelas)) {
                $bank_kelas = json_decode($bank_kelas);
            }

            $ids_lalu = array_column((array) $bank_kelas, 'kelas_id');
            $kelasLalu = $this->getKelasByIds($ids_lalu);

            $map_lalu = [];
            foreach ($kelasLalu as $item) {
                $map_lalu[] = $this->normalize($item->kode_kelas);
            }

            $kelas_now = $this->master->getAllKodeKelas($tp->id_tp, $smt->id_smt);
            $kelasSkrg = [];
            foreach ($kelas_now as $ids => $kode_now) {
                if (in_array($this->normalize($kode_now), $map_lalu)) {
                    $kelasSkrg[] = ['kelas_id' => $ids];
                }
            }

            $unik = $this->generateUniqueId();

            $newBankData = (array) $bank;
            unset($newBankData['id_bank']);
            $newBankData['id_tp'] = $tp->id_tp;
            $newBankData['id_smt'] = $smt->id_smt;
            $newBankData['bank_kode'] = $bank->bank_kode . '_' . $unik;
            $newBankData['bank_kelas'] = json_encode($kelasSkrg);
            $newBankData['date'] = date('Y-m-d H:i:s');

            $db->table('cbt_bank_soal')->insert($newBankData);
            $newId = $db->insertID();

            if ($soals && count($soals) > 0) {
                foreach ($soals as &$soal) {
                    // Convert object to array and remove id
                    $soalArr = (array) $soal;
                    unset($soalArr['id_soal']);
                    $soalArr['bank_id'] = $newId;
                    $soalArr['created_on'] = time();
                    $soalArr['updated_on'] = time();

                    $db->table('cbt_soal')->insert($soalArr);
                }
            }

            $this->logging->saveLog(3, 'Menyalin bank soal');
            return $this->response->setJSON(['status' => true, 'message' => 'Bank soal berhasil dicopy']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    private function generateUniqueId()
    {
        return substr(md5(uniqid((string) mt_rand(), true)), 0, 5);
    }

    private function normalize($str)
    {
        return strtolower(str_replace(' ', '', $str ?? ''));
    }

    private function getKelasByIds($ids)
    {
        if (empty($ids))
            return [];
        return \Config\Database::connect()->table('master_kelas')->whereIn('id_kelas', $ids)->get()->getResult();
    }

    public function deleteAllBank()
    {
        try {
            if ($this->inGroup('panitia')) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses Terlarang']);
            }

            $ids = $this->request->getPost('checked');
            if (!$ids || !is_array($ids)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada bank soal yang dipilih.']);
            }

            $db = \Config\Database::connect();
            $used_count = $db->table('cbt_jadwal')->whereIn('id_bank', $ids)->countAllResults();
            if ($used_count > 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Beberapa bank soal sedang digunakan dalam jadwal ujian dan tidak dapat dihapus.']);
            }

            $db->transBegin();
            $db->table('cbt_soal')->whereIn('id_bank', $ids)->delete();
            $db->table('cbt_bank_soal')->whereIn('id_bank', $ids)->delete();

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus beberapa bank soal.']);
            }

            $db->transCommit();
            $this->logging->saveLog(5, 'menghapus beberapa bank soal');
            return $this->response->setJSON(['status' => true, 'message' => count($ids) . ' Bank soal berhasil dihapus.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function import($id)
    {
        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Import Bank Soal',
            'subjudul' => 'Import Bank Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'bank' => $this->cbt->getBankSoalById((int) $id)
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('cbt/banksoal/import', $data)
            . view('_templates/dashboard/_footer');
    }

    public function uploadSoal()
    {
        try {
            $bank_id = $this->request->getPost('id_bank');
            $datas = $this->request->getPost('soal');
            $bank = $this->cbt->getBankSoalById((int) $bank_id);

            $jml_spg1 = 0;
            $jml_spg2 = 0;
            $jml_sjod = 0;
            $jml_siss = 0;
            $jml_sess = 0;
            $data_insert = [];

            if ($datas && is_array($datas)) {
                foreach ($datas as $jenis => $nomor) {
                    foreach ($nomor as $no => $v) {
                        $isi_soal = isset($v['soal']) ? $this->decode_data(rawurldecode($v['soal']), $bank_id, $jenis, $no) : "";
                        if ($isi_soal != "") {
                            $insert = [
                                'jenis' => $jenis,
                                'nomor_soal' => $no,
                                'soal' => $isi_soal,
                                'file' => json_encode([]),
                            ];

                            if ($jenis == 1) {
                                $insert['opsi_a'] = isset($v['opsi']) && isset($v['opsi']['A']) ? $this->decode_data(rawurldecode($v['opsi']['A']), $bank_id, $jenis, $no) : '';
                                $insert['opsi_b'] = isset($v['opsi']) && isset($v['opsi']['B']) ? $this->decode_data(rawurldecode($v['opsi']['B']), $bank_id, $jenis, $no) : '';
                                $insert['opsi_c'] = isset($v['opsi']) && isset($v['opsi']['C']) ? $this->decode_data(rawurldecode($v['opsi']['C']), $bank_id, $jenis, $no) : '';
                                $insert['opsi_d'] = isset($v['opsi']) && isset($v['opsi']['D']) ? $this->decode_data(rawurldecode($v['opsi']['D']), $bank_id, $jenis, $no) : '';
                                $insert['opsi_e'] = isset($v['opsi']) && isset($v['opsi']['E']) ? $this->decode_data(rawurldecode($v['opsi']['E']), $bank_id, $jenis, $no) : '';
                                $insert['jawaban'] = isset($v['kunci']) && count((array) $v['kunci']) > 0 ? ((array) $v['kunci'])[0] : '';
                                $jml_spg1++;
                            } elseif ($jenis == "2") {
                                $opsis = [];
                                $kuncis = [];
                                if (isset($v['opsi'])) {
                                    foreach ($v['opsi'] as $opsi => $jawaban) {
                                        $opsis[strtolower($opsi)] = $this->decode_data(rawurldecode($jawaban), $bank_id, $jenis, $no);
                                    }
                                }
                                if (isset($v['kunci'])) {
                                    foreach ((array) $v['kunci'] as $jawaban) {
                                        $kuncis[] = strtolower($jawaban);
                                    }
                                }
                                $insert['opsi_a'] = json_encode($opsis);
                                $insert['jawaban'] = json_encode($kuncis);
                                $jml_spg2++;
                            } elseif ($jenis == "3") {
                                $baris = [];
                                $kolom = [];
                                $header = ['#'];
                                $arrKol = [];
                                if (isset($v['kolom'])) {
                                    foreach ($v['kolom'] as $kd_kol => $kol) {
                                        $kolom[$kd_kol] = $kol;
                                        if ($kol != "")
                                            $header[] = $this->decode_data(rawurldecode($kol), $bank_id, $jenis, $no);
                                        if (isset($v['kunci'])) {
                                            foreach ($v['kunci'] as $kd_bar => $kd_kol_val) {
                                                if ($kd_kol_val != "")
                                                    $arrKol[$kd_bar] = array_map('trim', explode(',', $kd_kol_val));
                                            }
                                        }
                                    }
                                }
                                $baris[] = $header;
                                $jwbnBaris = [];
                                if (isset($v['baris'])) {
                                    foreach ($v['baris'] as $kd_bar => $bar) {
                                        $jwbn = [];
                                        if ($kd_bar != "")
                                            $jwbn[] = $this->decode_data(rawurldecode($bar), $bank_id, $jenis, $no);
                                        foreach ($kolom as $kk => $val) {
                                            if ($kd_bar != "" && $val != "" && isset($arrKol[$kd_bar])) {
                                                $match = in_array($kk, $arrKol[$kd_bar]);
                                                $jwbn[] = $match ? '1' : '0';
                                            }
                                        }
                                        if (count($jwbn) > 0)
                                            $baris[] = $jwbn;
                                        if ($kd_bar != "") {
                                            array_shift($jwbn);
                                            $jwbnBaris[$kd_bar] = $jwbn;
                                        }
                                    }
                                }
                                $types = [];
                                foreach ($jwbnBaris as $brs => $jml) {
                                    $jmlType = array_count_values((array) $jml);
                                    if (isset($jmlType[1]) && $jmlType[1] > 1)
                                        $types[] = 'checkbox';
                                }
                                $type = count($types) > 0 ? '1' : '2';
                                $jml_baris = count($baris);
                                $jml_kolom = count($baris[0]);
                                $jwb_jodohkan = ['model' => $jml_baris == $jml_kolom ? '1' : '2', 'type' => $type, 'jawaban' => $baris];
                                $insert['jawaban'] = json_encode($jwb_jodohkan);
                                $jml_sjod++;
                            } elseif ($jenis == "4") {
                                if (isset($v['kunci']))
                                    $insert['jawaban'] = $v['kunci'];
                                $jml_siss++;
                            } else {
                                if (isset($v['kunci']))
                                    $insert['jawaban'] = $this->decode_data(rawurldecode($v['kunci']), $bank_id, $jenis, $no);
                                $jml_sess++;
                            }
                            $data_insert[] = $insert;
                        }
                    }
                }
            }

            $tmpl = [
                '1' => $jml_spg1 == $bank->tampil_pg ? '1' : '0',
                '2' => $jml_spg2 == $bank->tampil_kompleks ? '1' : '0',
                '3' => $jml_sjod == $bank->tampil_jodohkan ? '1' : '0',
                '4' => $jml_siss == $bank->tampil_isian ? '1' : '0',
                '5' => $jml_sess == $bank->tampil_esai ? '1' : '0',
            ];

            $inserted_count = 0;
            if (count($data_insert) > 0) {
                $db = \Config\Database::connect();
                $db->transBegin();
                $db->table('cbt_soal')->where('bank_id', $bank_id)->delete();
                $final_insert = [];
                foreach ($data_insert as $dins) {
                    $final_insert[] = [
                        'bank_id' => $bank_id,
                        'jenis' => $dins['jenis'],
                        'nomor_soal' => $dins['nomor_soal'],
                        'soal' => $dins['soal'],
                        'deskripsi' => '',
                        'kesulitan' => '8',
                        'timer' => '0',
                        'timer_menit' => '0',
                        'file' => $dins['file'],
                        'created_on' => time(),
                        'updated_on' => time(),
                        'opsi_a' => $dins['opsi_a'] ?? '',
                        'opsi_b' => $dins['opsi_b'] ?? '',
                        'opsi_c' => $dins['opsi_c'] ?? '',
                        'opsi_d' => $dins['opsi_d'] ?? '',
                        'opsi_e' => $dins['opsi_e'] ?? '',
                        'jawaban' => $dins['jawaban'] ?? '',
                        'tampilkan' => $tmpl[$dins['jenis']] ?? '0'
                    ];
                }
                $inserted_count = $db->table('cbt_soal')->insertBatch($final_insert);

                $sttmpl = [
                    '1' => $jml_spg1 >= $bank->tampil_pg ? '1' : '0',
                    '2' => $jml_spg2 >= $bank->tampil_kompleks ? '1' : '0',
                    '3' => $jml_sjod >= $bank->tampil_jodohkan ? '1' : '0',
                    '4' => $jml_siss >= $bank->tampil_isian ? '1' : '0',
                    '5' => $jml_sess >= $bank->tampil_esai ? '1' : '0',
                ];
                $status_soal = ($sttmpl['1'] == '1' && $sttmpl['2'] == '1' && $sttmpl['3'] == '1' && $sttmpl['4'] == '1' && $sttmpl['5'] == '1') ? '1' : '0';
                $db->table('cbt_bank_soal')->where('id_bank', $bank_id)->update(['status_soal' => $status_soal]);

                $db->transCommit();
                $this->logging->saveLog(3, 'Mengimpor soal');
            }

            return $this->response->setJSON(['status' => true, 'total' => count($data_insert), 'insert' => $inserted_count]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function uploadSoalImage()
    {
        try {
            $name = $this->request->getPost('name');
            $src = $this->request->getPost('src');
            $src = str_replace('%2B', '+', $src ?? '');
            $filePath = ROOTPATH . 'public/uploads/bank_soal/' . $name;

            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            file_put_contents($filePath, base64_decode($src));
            return $this->response->setJSON(['status' => true, 'src' => 'uploads/bank_soal/' . $name]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    private function decode_data($html, $id_bank, $jenis, $nomor)
    {
        $search = ['<!DOCTYPE html>', '<html>', '<body>', '<?xml encoding="UTF-8">', '<!--?xml encoding="UTF-8"-->', '<meta charset="UTF-8">', "\r", "\n"];
        $repla = ['', '', '', '', '', '', '', ''];

        if ($html == null || !preg_match('/^<\s*\w+.*?>/', ltrim($html)) || empty($html)) {
            return str_replace($search, $repla, $html ?? '');
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        @$dom->loadHTML('<!DOCTYPE html><html><body><meta charset="UTF-8">' . $html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

        $images = $dom->getElementsByTagName('img');
        if ($images) {
            foreach ($images as $image) {
                if ($image instanceof \DOMElement) {
                    $src = $image->getAttribute('src');
                    if (substr($src, 0, 5) == "data:") {
                        $splited = explode(',', substr($src, 5), 2);
                        if (count($splited) == 2) {
                            $mime_part = explode(';', $splited[0], 2)[0];
                            $extension = explode('/', $mime_part, 2)[1] ?? 'png';
                            if ($extension == 'jpeg')
                                $extension = 'jpg';

                            $bytes = random_bytes(10);
                            $output_file = 'img_' . $id_bank . $jenis . $nomor . '_' . bin2hex($bytes) . '.' . $extension;
                            $path = ROOTPATH . 'public/uploads/bank_soal/' . $output_file;
                            if (!is_dir(dirname($path)))
                                mkdir(dirname($path), 0777, true);
                            file_put_contents($path, base64_decode($splited[1]));

                            $image->setAttribute('src', 'uploads/bank_soal/' . $output_file);
                        }
                    } else {
                        $image->setAttribute('src', str_replace(base_url(), '', $src ?? ''));
                    }
                }
            }
            $res = $dom->saveHTML();
            return str_replace($search, $repla, $res ?? '');
        }
        libxml_clear_errors();
        return str_replace($search, $repla, $html ?? '');
    }

    public function detail($id)
    {
        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $bank = $this->cbt->getBankSoalById((int) $id);
        if ($bank && is_string($bank->bank_kelas)) {
            $bank->bank_kelas = json_decode($bank->bank_kelas);
        }

        $data = [
            'user' => $user,
            'judul' => 'Detail Soal',
            'subjudul' => 'Detail Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'id_bank' => $id,
            'bank' => $bank,
            'soals' => $this->cbt->getAllSoalByBank((int) $id)
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('cbt/banksoal/detail', $data)
            . view('_templates/dashboard/_footer');
    }
}
