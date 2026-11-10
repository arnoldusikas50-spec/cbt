<?php

namespace App\Controllers;

use App\Models\RaporModel;
use App\Models\KelasModel;
use App\Models\DropdownModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;

class Rapor extends BaseController
{
    protected $rapor;
    protected $kelas;
    protected $dropdown;
    protected $master;
    protected $dashboard;
    protected $db;
    protected $dbforge;

    public function __construct()
    {
        $this->rapor = new RaporModel();
        $this->kelas = new KelasModel();
        $this->dropdown = new DropdownModel();
        $this->master = new MasterModel();
        $this->dashboard = new DashboardModel();
        $this->db = \Config\Database::connect();
        $this->dbforge = \Config\Database::forge();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        } else if (!$this->isAdmin() && !$this->inGroup('guru')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akses Terlarang');
        }

        $no_update = $this->db->fieldExists('nip_kepsek', 'rapor_admin_setting');
        if (!$no_update) {
            $field = [
                'nip_kepsek' => [
                    'type' => 'int',
                    'constraint' => 1,
                    'default' => 0
                ],
                'nip_walikelas' => [
                    'type' => 'int',
                    'constraint' => 1,
                    'default' => 0
                ],
            ];
            $this->dbforge->addColumn('rapor_admin_setting', $field);
        }

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'Pengaturan Rapor',
            'subjudul' => 'Pengaturan Rapor',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['rapor'] = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);
        $data['kkm_drop'] = ["Tidak", "Ya"];

        if ($this->isAdmin()) {
            return $this->renderView('setting/rapor', $data);
        } else {
            return redirect()->to('rapor/raporkkm');
        }
    }

    public function saveRaporAdmin()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $input = [
            'id_setting' => $tp->id_tp . $smt->id_smt,
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'tgl_rapor_pts' => $this->request->getPost('tgl_rapor_pts'),
            'nip_kepsek' => $this->request->getPost('nip_kepsek'),
            'nip_walikelas' => $this->request->getPost('nip_walikelas'),
            'tgl_rapor_akhir' => $this->request->getPost('tgl_rapor_akhir'),
            'tgl_rapor_kelas_akhir' => $this->request->getPost('tgl_rapor_kelas_akhir'),
            'kkm_tunggal' => $this->request->getPost('kkm_tunggal'),
            'kkm' => $this->request->getPost('kkm'),
            'bobot_ph' => $this->request->getPost('bobot_ph'),
            'bobot_pts' => $this->request->getPost('bobot_pts'),
            'bobot_pas' => $this->request->getPost('bobot_pas')
        ];
        $update = $this->db->table('rapor_admin_setting')->replace($input);
        return $this->response->setJSON(['status' => $update]);
    }

    public function raporkkm()
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');
        $data = ['user' => $user, 'judul' => 'KKM dan Bobot', 'subjudul' => 'Input KKM dan Bobot Nilai', 'setting' => $this->dashboard->getSetting()];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data['guru'] = null;
            $data['mapel'] = [];
            $data['kelas'] = [];
            $data['ekstra'] = [];
            $data['kelas_ekstra'] = [];
            return $this->renderView('members/guru/rapor/kkm/data', $data);
        }
        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapel = $mapel_guru->mapel_kelas != null ? $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]') : [];

        $arrMapel = [];
        $arrKelas = [];

        $kelases = $this->kelas->getKelasList($tp->id_tp, $smt->id_smt);

        foreach ($mapel as $m) {
            $arrMapel[$m->id_mapel] = $m->nama_mapel;
            foreach ($m->kelas_mapel as $kls) {
                // array_column search in multi-dim array
                $key_kelas = array_search($kls->kelas, array_column($kelases, 'id_kelas'));
                if ($key_kelas !== false) {
                    $arrKelas[$m->id_mapel][] = ['id_kelas' => $kls->kelas, 'nama_kelas' => $kelases[$key_kelas]->nama_kelas];
                }
            }
        }
        $data['guru'] = $guru;
        $data['mapel'] = $arrMapel;
        $data['kelas'] = $arrKelas;

        $ekstra = $mapel_guru->ekstra_kelas != null ? json_decode(json_encode($this->maybe_unserialize($mapel_guru->ekstra_kelas ?? '[]'))) : [];
        $arrEkstra = [];
        $arrKelasEkstra = [];
        if (count($ekstra) > 0) {
            foreach ($ekstra as $m) {
                $arrEkstra[$m->id_ekstra] = $m->nama_ekstra;
                foreach ($m->kelas_ekstra as $kls) {
                    $key_kelas = array_search($kls->kelas, array_column($kelases, 'id_kelas'));
                    if ($key_kelas !== false) {
                        $arrKelasEkstra[$m->id_ekstra][] = ['id_kelas' => $kls->kelas, 'nama_kelas' => $kelases[$key_kelas]->nama_kelas];
                    }
                }
            }
        }
        $data['ekstra'] = $arrEkstra;
        $data['kelas_ekstra'] = $arrKelasEkstra;
        return $this->renderView('members/guru/rapor/kkm/data', $data);
    }

    public function datakkm($mapel, $kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $kkm = '';
        if ($kelas != null) {
            $kkm = $this->rapor->getKkm($mapel . $kelas . $tp->id_tp . $smt->id_smt . '1');
        }

        $data['mapel'] = $mapel;
        $data['kelas'] = $kelas;
        $data['kkm'] = $kkm;
        $data['tp'] = $tp->id_tp;
        $data['smt'] = $smt->id_smt;
        $data['setting'] = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);

        return $this->response->setJSON($data);
    }

    public function datakkmEkstra($ekstra, $kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $kkm = '';
        if ($kelas != null) {
            $kkm = $this->rapor->getKkm($ekstra . $kelas . $tp->id_tp . $smt->id_smt . '2');
        }

        $data['ekstra'] = $ekstra;
        $data['kelas'] = $kelas;
        $data['kkm'] = $kkm;
        $data['tp'] = $tp->id_tp;
        $data['smt'] = $smt->id_smt;
        $data['setting'] = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);

        return $this->response->setJSON($data);
    }

    public function saveKkm()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $input = [
            'id_kkm' => $this->request->getPost('id_kkm'),
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'bobot_ph' => $this->request->getPost('bobot_ph'),
            'bobot_pts' => $this->request->getPost('bobot_pts'),
            'bobot_pas' => $this->request->getPost('bobot_pas'),
            'kkm' => $this->request->getPost('kkm'),
            'beban_jam' => $this->request->getPost('beban'),
            'jenis' => $this->request->getPost('jenis_kkm'),
            'id_kelas' => $this->request->getPost('id_kelas'),
            'id_mapel' => $this->request->getPost('id_mapel')
        ];
        $update = $this->db->table('rapor_kkm')->replace($input);
        return $this->response->setJSON(['status' => $update]);
    }

    public function raporkikd()
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');
        $data = ['user' => $user, 'judul' => 'Indikator KD', 'subjudul' => 'Ringkasan Materi Penilaian', 'setting' => $this->dashboard->getSetting()];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data['guru'] = null;
            $data['mapel'] = [];
            $data['kelas'] = [];
            return $this->renderView('members/guru/rapor/kikd/data', $data);
        }
        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

        $arrMapel = [];
        $arrKelas = [];
        $kelases = $this->kelas->getKelasList($tp->id_tp, $smt->id_smt);

        if ($mapel != null) {
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
                foreach ($m->kelas_mapel as $kls) {
                    $key_kelas = array_search($kls->kelas, array_column($kelases, 'id_kelas'));
                    if ($key_kelas !== false) {
                        $arrKelas[$m->id_mapel][] = ['id_kelas' => $kls->kelas, 'nama_kelas' => $kelases[$key_kelas]->nama_kelas];
                    }
                }
            }
        }

        $data['guru'] = $guru;
        $data['mapel'] = $arrMapel;
        $data['kelas'] = $arrKelas;

        return $this->renderView('members/guru/rapor/kikd/data', $data);
    }

    public function datakikd($mapel, $kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $kikds = $this->rapor->getKikdMapelKelas($mapel, $kelas, $tp->id_tp, $smt->id_smt);

        $arrKiKd[] = [];
        if ($kelas != null) {
            //aspek: 1=pengetahuan, 2=keterampilan
            $aspek = ['1', '2'];
            foreach ($aspek as $asp) {
                for ($i = 0; $i < 8; $i++) {
                    $no = $i + 1;
                    $key_ki = array_search($mapel . $kelas . $asp . $no, array_column($kikds, 'id_kikd'));
                    if ($key_ki !== false) {
                        $arrKiKd[$asp][$mapel . $kelas . $asp . $no] = $kikds[$key_ki];
                    } else {
                        $arrKiKd[$asp][$mapel . $kelas . $asp . $no] = ['materi_kikd' => ''];
                    }
                }
            }
        }
        $data['mapel'] = $mapel;
        $data['kelas'] = $kelas;
        $data['kikd'] = $arrKiKd;
        return $this->response->setJSON($data);
    }

    public function saveKikd()
    {
        $sjson = $this->request->getPost('materi');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $updated = false;
        if (is_array($sjson) || is_object($sjson)) {
            foreach ((array) $sjson as $aspek => $mapel_kelas) {
                foreach ($mapel_kelas as $idmk => $kikd) {
                    foreach ($kikd as $id => $materi) {
                        $input = [
                            'id_kikd' => $id,
                            'id_mapel_kelas' => $idmk,
                            'aspek' => $aspek,
                            'id_tp' => $tp->id_tp,
                            'id_smt' => $smt->id_smt,
                            'materi_kikd' => $materi
                        ];
                        $updated = $this->db->table('rapor_kikd')->replace($input);
                    }
                }
            }
        }
        $data['status'] = $updated;
        $data['json'] = $sjson;
        return $this->response->setJSON($data);
    }

    public function raporNilai()
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');
        $data = [
            'user' => $user,
            'judul' => 'Input Nilai',
            'subjudul' => 'Input Nilai Rapor',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data['guru'] = null;
            $data['mapels'] = [];
            $data['mapel'] = [];
            $data['kelas_mapel'] = [];
            $data['level'] = [];
            $data['siswas'] = [];
            $data['harian'] = [];
            $data['pts'] = [];
            $data['pas'] = [];
            $data['ekstras'] = [];
            $data['siswae'] = [];
            $data['ekstra'] = [];
            $data['kelas_ekstra'] = [];
            return $this->renderView('members/guru/rapor/nilai/data', $data);
        }
        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapel = $mapel_guru->mapel_kelas != null ? $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]') : [];

        $siswas = [];
        $arrMapel = [];
        $arrKelasMapel = [];
        $levelsMapel = [];
        $harian = [];
        $pts = [];
        $pas = [];
        foreach ($mapel as $m) {
            $arrMapel[$m->id_mapel] = $m->nama_mapel;
            foreach ($m->kelas_mapel as $kls) {
                $kelas_guru = $this->kelas->get_one($kls->kelas);
                if ($kelas_guru != null) {
                    $levelsMapel[] = $kelas_guru->level_id;
                    $arrKelasMapel[$m->id_mapel][] = [
                        'id_kelas' => $kelas_guru->id_kelas,
                        'level' => $kelas_guru->level_id,
                        'nama_kelas' => $kelas_guru->nama_kelas
                    ];
                    $siswas[$m->id_mapel][$kelas_guru->nama_kelas] = count($this->kelas->getKelasSiswa($kelas_guru->id_kelas, $tp->id_tp, $smt->id_smt));
                    $harian[$m->id_mapel][$kelas_guru->nama_kelas] = $this->rapor->cekNilaiHarianKelas($m->id_mapel, $kelas_guru->id_kelas, $tp->id_tp, $smt->id_smt);
                    $pts[$m->id_mapel][$kelas_guru->nama_kelas] = $this->rapor->cekNilaiPtsKelas($m->id_mapel, $kelas_guru->id_kelas, $tp->id_tp, $smt->id_smt);
                    $pas[$m->id_mapel][$kelas_guru->nama_kelas] = $this->rapor->cekNilaiAkhirKelas($m->id_mapel, $kelas_guru->id_kelas, $tp->id_tp, $smt->id_smt);
                }
            }
        }

        $data['mapels'] = $mapel;
        $data['mapel'] = $arrMapel;
        $data['kelas_mapel'] = $arrKelasMapel;
        $data['level'] = array_unique($levelsMapel);
        $data['siswas'] = $siswas;
        $data['harian'] = $harian;
        $data['pts'] = $pts;
        $data['pas'] = $pas;

        $ekstra = $mapel_guru->ekstra_kelas != null ? json_decode(json_encode($this->maybe_unserialize($mapel_guru->ekstra_kelas ?? '[]'))) : [];
        $arrEkstra = [];
        $arrKelasEkstra = [];
        $ektras = [];
        $siswae = [];
        if (count($ekstra) > 0) {
            foreach ($ekstra as $m) {
                $arrEkstra[$m->id_ekstra] = $m->nama_ekstra;
                foreach ($m->kelas_ekstra as $kls) {
                    $kelas_guru = $this->kelas->get_one($kls->kelas);
                    if ($kelas_guru != null) {
                        $arrKelasEkstra[$m->id_ekstra][] = ['id_kelas' => $kelas_guru->id_kelas, 'level' => $kelas_guru->level_id, 'nama_kelas' => $kelas_guru->nama_kelas,];
                        $siswae[$m->id_ekstra][$kelas_guru->nama_kelas] = count($this->kelas->getKelasSiswa($kelas_guru->id_kelas, $tp->id_tp, $smt->id_smt));
                        $ektras[$m->id_ekstra][$kelas_guru->nama_kelas] = $this->rapor->cekNilaiEkstraKelas($m->id_ekstra, $kelas_guru->id_kelas, $tp->id_tp, $smt->id_smt);
                    }
                }
            }
        }
        $data['ekstras'] = $ektras;
        $data['siswae'] = $siswae;
        $data['ekstra'] = $arrEkstra;
        $data['kelas_ekstra'] = $arrKelasEkstra;
        $data['guru'] = $guru;
        return $this->renderView('members/guru/rapor/nilai/data', $data);
    }

    public function inputHarian($id_mapel, $id_kelas)
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username ?? '', $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data = [
                'user' => $user,
                'setting' => $this->dashboard->getSetting(),
                'guru' => null,
                'mapel' => ['id_mapel' => $id_mapel, 'nama_mapel' => 'Unknown'],
                'kelas' => ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'],
                'siswa' => [],
                'nilai' => [],
                'kikd' => [1 => [], 2 => []],
                'kkm' => null,
                'setting_rapor' => null,
                'tp_active' => $tp,
                'smt_active' => $smt,
                'tp' => $this->dashboard->getTahun(),
                'smt' => $this->dashboard->getSemester(),
                'judul' => 'Nilai Harian',
                'subjudul' => 'Input Nilai Harian'
            ];
            return $this->renderView('members/guru/rapor/nilai/harian', $data);
        }
        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapels = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
        $mapel_data = ['id_mapel' => $id_mapel, 'nama_mapel' => 'Unknown'];
        $kelas_data = ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'];

        if (is_array($mapels)) {
            foreach ($mapels as $m) {
                if ($m->id_mapel == $id_mapel) {
                    $mapel_data = ['id_mapel' => $m->id_mapel, 'nama_mapel' => $m->nama_mapel];
                    if (isset($m->kelas_mapel) && is_array($m->kelas_mapel)) {
                        foreach ($m->kelas_mapel as $kls) {
                            if ($kls->kelas == $id_kelas) {
                                $kelas_data = ['id_kelas' => $kls->id_kelas ?? ($kls->kelas ?? null), 'nama_kelas' => $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->id_kelas ?? ($kls->kelas ?? null))];
                            }
                        }
                    }
                }
            }
        }

        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilai = [];

        for ($i = 0; $i < count($siswas); $i++) {
            $siswa = $siswas[$i];
            $dummyNilai = ['p1' => '', 'p2' => '', 'p3' => '', 'p4' => '', 'p5' => '', 'p6' => '', 'p7' => '', 'p8' => '', 'p_rata_rata' => '', 'p_predikat' => '=', 'p_deskripsi' => '', 'k1' => '', 'k2' => '', 'k3' => '', 'k4' => '', 'k5' => '', 'k6' => '', 'k7' => '', 'k8' => '', 'k_rata_rata' => '', 'k_predikat' => '', 'k_deskripsi' => ''];

            $ns = $this->rapor->getNilaiHarianKelas($id_mapel, $id_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
            $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $ns;
        }

        $setting = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);
        $kkm = null;
        if ($setting != null) {
            if ($setting->kkm_tunggal == '1') {
                $kkm = $setting;
            } else {
                $kkm = $this->rapor->getKkm($id_mapel . $id_kelas . $tp->id_tp . $smt->id_smt . '1');
            }
        }

        $arrKiKd[] = [];
        if ($id_kelas != null) {
            $aspek = ['1', '2'];
            foreach ($aspek as $asp) {
                for ($i = 0; $i < 8; $i++) {
                    $no = $i + 1;
                    $r = $this->rapor->getKikdMapel($id_mapel . $id_kelas . $asp . $no, $tp->id_tp, $smt->id_smt);
                    if ($r == null) {
                        $tp_before = $this->dashboard->getTpBefore($tp->tahun);
                        if ($tp_before) {
                            $r = $this->rapor->getKikdMapel($id_mapel . $id_kelas . $asp . $no, $tp_before->id_tp, $smt->id_smt);
                        }
                    }
                    $arrKiKd[$asp][$id_mapel . $id_kelas . $asp . $no] = $r;
                }
            }
        }

        $data = ['user' => $user, 'judul' => 'Nilai Harian Kelas ', 'subjudul' => 'Input Nilai Harian Mapel ', 'setting' => $this->dashboard->getSetting(), 'guru' => $guru, 'mapel' => $mapel_data, 'kelas' => $kelas_data, 'siswa' => $siswas, 'nilai' => $nilai, 'kkm' => $kkm];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['kikd'] = $arrKiKd;
        $data['setting_rapor'] = $setting;

        return $this->renderView('members/guru/rapor/nilai/harian', $data);
    }

    public function inputPts($id_mapel, $id_kelas)
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username ?? '', $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data = [
                'user' => $user,
                'setting' => $this->dashboard->getSetting(),
                'guru' => null,
                'mapel' => ['id_mapel' => $id_mapel, 'nama_mapel' => 'Unknown'],
                'kelas' => ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'],
                'siswa' => [],
                'nilai' => [],
                'kkm' => null,
                'setting_rapor' => null,
                'tp_active' => $tp,
                'smt_active' => $smt,
                'tp' => $this->dashboard->getTahun(),
                'smt' => $this->dashboard->getSemester(),
                'judul' => 'Nilai PTS',
                'subjudul' => 'Input Nilai PTS'
            ];
            return $this->renderView('members/guru/rapor/nilai/pts', $data);
        }
        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapels = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
        $mapel_data = ['id_mapel' => $id_mapel, 'nama_mapel' => 'Unknown'];
        $kelas_data = ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'];

        if (is_array($mapels)) {
            foreach ($mapels as $m) {
                if ($m->id_mapel == $id_mapel) {
                    $mapel_data = ['id_mapel' => $m->id_mapel, 'nama_mapel' => $m->nama_mapel];
                    if (isset($m->kelas_mapel) && is_array($m->kelas_mapel)) {
                        foreach ($m->kelas_mapel as $kls) {
                            if ($kls->kelas == $id_kelas) {
                                $kelas_data = ['id_kelas' => $kls->id_kelas ?? ($kls->kelas ?? null), 'nama_kelas' => $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->id_kelas ?? ($kls->kelas ?? null))];
                            }
                        }
                    }
                }
            }
        }

        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilai = [];

        for ($i = 0; $i < count($siswas); $i++) {
            $siswa = $siswas[$i];
            $dummyNilai = ['nilai' => '', 'predikat' => ''];

            $ns = $this->rapor->getNilaiPtsKelas($id_mapel, $id_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
            $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $ns;
        }

        $setting = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);
        $kkm = null;
        if ($setting != null) {
            if ($setting->kkm_tunggal == '1') {
                $kkm = $setting;
            } else {
                $kkm = $this->rapor->getKkm($id_mapel . $id_kelas . $tp->id_tp . $smt->id_smt . '1');
            }
        }

        $data = ['user' => $user, 'judul' => 'Nilai PTS Kelas ', 'subjudul' => 'Input Nilai PTS Mapel ', 'setting' => $this->dashboard->getSetting(), 'guru' => $guru, 'mapel' => $mapel_data, 'kelas' => $kelas_data, 'siswa' => $siswas, 'nilai' => $nilai, 'kkm' => $kkm];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['setting_rapor'] = $setting;

        return $this->renderView('members/guru/rapor/nilai/pts', $data);
    }

    public function inputPas($id_mapel, $id_kelas)
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username ?? '', $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data = [
                'user' => $user,
                'setting' => $this->dashboard->getSetting(),
                'guru' => null,
                'mapel' => ['id_mapel' => $id_mapel, 'nama_mapel' => 'Unknown'],
                'kelas' => ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'],
                'siswa' => [],
                'nilai' => [],
                'kkm' => null,
                'setting_rapor' => null,
                'tp_active' => $tp,
                'smt_active' => $smt,
                'tp' => $this->dashboard->getTahun(),
                'smt' => $this->dashboard->getSemester(),
                'judul' => 'Nilai Akhir Kelas',
                'subjudul' => 'Input Nilai Akhir Mapel',
            ];
            return $this->renderView('members/guru/rapor/nilai/pas', $data);
        }
        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapels = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
        $mapel_data = ['id_mapel' => $id_mapel, 'nama_mapel' => 'Unknown'];
        $kelas_data = ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'];

        if (is_array($mapels)) {
            foreach ($mapels as $m) {
                if ($m->id_mapel == $id_mapel) {
                    $mapel_data = ['id_mapel' => $m->id_mapel, 'nama_mapel' => $m->nama_mapel];
                    if (isset($m->kelas_mapel) && is_array($m->kelas_mapel)) {
                        foreach ($m->kelas_mapel as $kls) {
                            if ($kls->kelas == $id_kelas) {
                                $kelas_data = ['id_kelas' => $kls->id_kelas ?? ($kls->kelas ?? null), 'nama_kelas' => $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->id_kelas ?? ($kls->kelas ?? null))];
                            }
                        }
                    }
                }
            }
        }

        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilai = [];

        for ($i = 0; $i < count($siswas); $i++) {
            $siswa = $siswas[$i];
            $dummyNilai = ['nhar' => '', 'npts' => '', 'npas' => ''];

            $ns = $this->rapor->getNilaiAkhirKelas($id_mapel, $id_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
            $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $ns;
        }

        $setting = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);
        $kkm = null;
        if ($setting != null) {
            if ($setting->kkm_tunggal == '1') {
                $kkm = $setting;
            } else {
                $kkm = $this->rapor->getKkm($id_mapel . $id_kelas . $tp->id_tp . $smt->id_smt . '1');
            }
        }

        $data = [
            'user' => $user,
            'judul' => 'Nilai Akhir Kelas',
            'subjudul' => 'Input Nilai Akhir Mapel',
            'setting' => $this->dashboard->getSetting(),
            'guru' => $guru,
            'mapel' => $mapel_data,
            'kelas' => $kelas_data,
            'siswa' => $siswas,
            'nilai' => $nilai,
            'kkm' => $kkm,
            'setting_rapor' => $setting
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        return $this->renderView('members/guru/rapor/nilai/pas', $data);
    }

    public function inputEkstra($id_ekstra, $id_kelas)
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username ?? '', $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data = [
                'user' => $user,
                'setting' => $this->dashboard->getSetting(),
                'guru' => null,
                'ekstra' => ['id_ekstra' => $id_ekstra, 'nama_ekstra' => 'Unknown'],
                'kelas' => ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'],
                'siswa' => [],
                'nilai' => [],
                'tp_active' => $tp,
                'smt_active' => $smt,
                'tp' => $this->dashboard->getTahun(),
                'smt' => $this->dashboard->getSemester(),
                'judul' => 'Nilai Ekstrakurikuler',
                'subjudul' => 'Input Nilai Ekstrakurikuler'
            ];
            return $this->renderView('members/guru/rapor/nilai/ekstra', $data);
        }
        $ekstra_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $ekstras = $this->maybe_unserialize($ekstra_guru->ekstra_kelas ?? '[]');
        $ekstra_data = ['id_ekstra' => $id_ekstra, 'nama_ekstra' => 'Unknown'];
        $kelas_data = ['id_kelas' => $id_kelas, 'nama_kelas' => 'Unknown'];

        if (is_array($ekstras)) {
            foreach ($ekstras as $m) {
                if ($m->id_ekstra == $id_ekstra) {
                    $ekstra_data = ['id_ekstra' => $m->id_ekstra, 'nama_ekstra' => $m->nama_ekstra];
                    if (isset($m->kelas_ekstra) && is_array($m->kelas_ekstra)) {
                        foreach ($m->kelas_ekstra as $kls) {
                            if ($kls->kelas == $id_kelas) {
                                $kelas_data = ['id_kelas' => $kls->id_kelas ?? ($kls->id_kelas ?? ($kls->kelas ?? null)), 'nama_kelas' => $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->id_kelas ?? ($kls->kelas ?? null))];
                            }
                        }
                    }
                }
            }
        }

        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilai = [];

        for ($i = 0; $i < count($siswas); $i++) {
            $siswa = $siswas[$i];
            $dummyNilai = ['nilai' => '', 'predikat' => '', 'deskripsi' => ''];

            $ns = $this->rapor->getNilaiEkstraKelas($id_ekstra, $id_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
            $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $ns;
        }

        $setting = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);
        if ($setting != null) {
            if ($setting->kkm_tunggal == '1') {
                $kkm = $setting;
            } else {
                $kkm = $this->rapor->getKkm($id_ekstra . $id_kelas . $tp->id_tp . $smt->id_smt . '2');
            }
        } else {
            $kkm = null;
        }

        $data = ['user' => $user, 'judul' => 'Nilai Ekstrakurikuler ', 'subjudul' => 'Input Nilai PTS Ekstra ', 'setting' => $this->dashboard->getSetting(), 'guru' => $guru, 'ekstra' => $ekstra_data, 'kelas' => $kelas_data, 'siswa' => $siswas, 'nilai' => $nilai, 'kkm' => $kkm];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        return $this->renderView('members/guru/rapor/nilai/ekstra', $data);
    }

    public function downloadNilaiHarian($id_mapel, $id_kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilais = $this->rapor->getAllNilaiHarianKelas($id_mapel, $id_kelas, $tp->id_tp, $smt->id_smt);

        foreach ($siswas as $ind => $siswa) {
            $siswa->no = ($ind + 1);
            $siswa->no_induk = $siswa->nisn != null ? "'" . $siswa->nisn : "'" . $siswa->nis;

            $siswa->p1 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p1 ?? '') : '';
            $siswa->p2 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p2 ?? '') : '';
            $siswa->p3 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p3 ?? '') : '';
            $siswa->p4 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p4 ?? '') : '';
            $siswa->p5 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p5 ?? '') : '';
            $siswa->p6 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p6 ?? '') : '';
            $siswa->p7 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p7 ?? '') : '';
            $siswa->p8 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->p8 ?? '') : '';
            $siswa->k1 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k1 ?? '') : '';
            $siswa->k2 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k2 ?? '') : '';
            $siswa->k3 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k3 ?? '') : '';
            $siswa->k4 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k4 ?? '') : '';
            $siswa->k5 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k5 ?? '') : '';
            $siswa->k6 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k6 ?? '') : '';
            $siswa->k7 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k7 ?? '') : '';
            $siswa->k8 = isset($nilais[$siswa->id_siswa]) ? ($nilais[$siswa->id_siswa]->k8 ?? '') : '';
        }

        $kikds = $this->rapor->getKikdMapelKelas($id_mapel, $id_kelas, $tp->id_tp, $smt->id_smt);

        $kd_peng = [];
        $kd_ketr = [];
        foreach ($kikds as $ki) {
            $nn = substr($ki->id_kikd, -1);
            $kd = new \stdClass();
            $kd->no = $nn;
            if ($ki->aspek == 1) {
                $kd->nop = $nn;
                $kd->kodep = 'P' . $nn;
                $kd->p = $ki->materi_kikd;
                $kd_peng[] = $kd;
            } else {
                $kd->nok = $nn;
                $kd->kodek = 'K' . $nn;
                $kd->k = $ki->materi_kikd;
                $kd_ketr[] = $kd;
            }
        }

        $kd_ketr_map = [];
        foreach ($kd_ketr as $ketr) {
            $kd_ketr_map[$ketr->no] = $ketr;
        }

        $res_kd = [];
        foreach ($kd_peng as $peng) {
            $no = $peng->no;
            $res_kd[] = [
                "no" => $no,
                "nop" => $no,
                "kodep" => $peng->kodep,
                "p" => $peng->p,
                "nok" => $no,
                "kodek" => isset($kd_ketr_map[$no]) ? $kd_ketr_map[$no]->kodek : "",
                "k" => isset($kd_ketr_map[$no]) ? $kd_ketr_map[$no]->k : ""
            ];
        }

        if (count($kikds) == 0) {
            for ($i = 0; $i < 8; $i++) {
                $no = $i + 1;
                $res_kd[] = [
                    "nok" => $no,
                    "kodek" => 'K' . $no,
                    "k" => $no == 1 ? 'Praktik/Portofolio/Proyek yang dinilai (lihat tabel KATA KERJA sebelah kanan)' : '',
                    "nop" => $no,
                    "kodep" => 'P' . $no,
                    "p" => $no == 1 ? 'Materi yang dinilai (lihat tabel KATA KERJA sebelah kanan)' : ''
                ];
            }
        }

        return $this->response->setJSON([
            'siswa' => $siswas,
            'kikd' => $res_kd
        ]);
    }

    public function uploadNilaiHarian()
    {
        $p_siswa = $this->request->getPost('siswa');
        $p_kikd = $this->request->getPost('kikd');
        $id_mapel = $this->request->getPost('id_mapel');
        $id_kelas = $this->request->getPost('id_kelas');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $datas = [];
        $kikdp = [];
        $kikdk = [];
        if (is_array($p_siswa)) {
            foreach ($p_siswa as $siswa) {
                $siswa['id_nilai_harian'] = $id_mapel . $id_kelas . $siswa['id'] . $tp->id_tp . $smt->id_smt;
                $siswa['id_siswa'] = $siswa['id'];
                $siswa['id_mapel'] = $id_mapel;
                $siswa['id_kelas'] = $id_kelas;
                $siswa['id_tp'] = $tp->id_tp;
                $siswa['id_smt'] = $smt->id_smt;

                unset($siswa['id']);
                unset($siswa['nisn']);
                unset($siswa['namasiswa']);
                $datas[] = $siswa;
            }
        }

        if (is_array($p_kikd)) {
            foreach ($p_kikd as $kikd) {
                $kikdp[] = [
                    'id_kikd' => $id_mapel . $id_kelas . '1' . $kikd['no'],
                    'id_mapel_kelas' => $id_mapel . $id_kelas,
                    'aspek' => 1,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'materi_kikd' => $kikd['materipengetahuanyangdinilai'] != null ? strip_tags($kikd['materipengetahuanyangdinilai'] ?? '') : ''
                ];

                $kikdk[] = [
                    'id_kikd' => $id_mapel . $id_kelas . '2' . $kikd['no'],
                    'id_mapel_kelas' => $id_mapel . $id_kelas,
                    'aspek' => 2,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'materi_kikd' => $kikd['materiketerampilanyangdinilai'] != null ? strip_tags($kikd['materiketerampilanyangdinilai'] ?? '') : ''
                ];
            }
        }

        $updated = 0;
        $this->db->transStart();
        foreach ($datas as $data) {
            $update = $this->db->table('rapor_nilai_harian')->replace($data);
            if ($update)
                $updated++;
        }

        foreach ($kikdp as $kip) {
            if ($kip != null)
                $this->db->table('rapor_kikd')->replace($kip);
        }

        foreach ($kikdk as $kik) {
            if ($kik != null)
                $this->db->table('rapor_kikd')->replace($kik);
        }

        $this->db->transComplete();
        return $this->response->setJSON($updated);
    }

    public function raporNilaiGuru($filter = null, $id_mapel = null)
    {
        $user = $this->getUser();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Semua Nilai',
            'subjudul' => 'Semua Nilai Rapor',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
        ];

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data['guru'] = null;
            $data['mapel'] = $this->dropdown->getAllMapel();
            $data['ekstra'] = $this->dropdown->getAllEkskul();
            $data['filter'] = ['' => 'Filter berdasarkan', '1' => 'Mata Pelajaran', '2' => 'Ekstrakurikuler'];
            $data['ekstra_selected'] = $id_mapel;
            $data['mapel_selected'] = $id_mapel;
            $data['filter_selected'] = $filter;
            return $this->renderView('members/guru/rapor/nilai/nilaiguru', $data);
        }
        $data['guru'] = $guru;

        $ret[''] = "Pilih Mapel";
        $data['mapel'] = $ret + $this->dropdown->getAllMapel();

        $ret_e[''] = "Pilih Eskul";
        $data['ekstra'] = $ret_e + $this->dropdown->getAllEkskul();

        $data['filter'] = ['' => 'Filter berdasarkan', '1' => 'Mata Pelajaran', '2' => 'Ekstrakurikuler'];
        $data['ekstra_selected'] = $id_mapel;
        $data['mapel_selected'] = $id_mapel;
        $data['filter_selected'] = $filter;

        $jabatan_guru = $this->master->getGuruMapel($tp->id_tp, $smt->id_smt);
        foreach ($jabatan_guru as $jabatan) {
            $jabatan->mapel_kelas = $this->maybe_unserialize($jabatan->mapel_kelas ?? '[]');
            $jabatan->ekstra_kelas = $this->maybe_unserialize($jabatan->ekstra_kelas ?? '[]');
        }

        if ($id_mapel != null) {
            $setting = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);
            $kkm = null;
            $kkm_ekstra = null;

            if ($setting) {
                if ($setting->kkm_tunggal == '1') {
                    $kkm = $setting;
                    $kkm_ekstra = $setting;
                } else {
                    $kkm = $this->rapor->getKkm($id_mapel . $guru->wali_kelas . $tp->id_tp . $smt->id_smt . '1');
                    $kkm_ekstra = $this->rapor->getKkm($id_mapel . $guru->wali_kelas . $tp->id_tp . $smt->id_smt . '2');
                }
            }

            $siswas = $this->kelas->getKelasSiswa($guru->wali_kelas, $tp->id_tp, $smt->id_smt);
            $nilai = [];

            if ($filter == '1') {
                $guru_mapel = '';
                foreach ($jabatan_guru as $jab) {
                    foreach ($jab->mapel_kelas as $mk) {
                        if ($mk->id_mapel == $id_mapel) {
                            foreach ($mk->kelas_mapel as $km) {
                                if ($km->kelas == $guru->wali_kelas) {
                                    $guru_mapel = $jab->nama_guru;
                                }
                            }
                        }
                    }
                }

                foreach ($siswas as $siswa) {
                    $dummyNilai = [
                        'p1' => '',
                        'p2' => '',
                        'p3' => '',
                        'p4' => '',
                        'p5' => '',
                        'p6' => '',
                        'p7' => '',
                        'p8' => '',
                        'p_rata_rata' => '',
                        'p_predikat' => '=',
                        'p_deskripsi' => '',
                        'k1' => '',
                        'k2' => '',
                        'k3' => '',
                        'k4' => '',
                        'k5' => '',
                        'k6' => '',
                        'k7' => '',
                        'k8' => '',
                        'k_rata_rata' => '',
                        'k_predikat' => '',
                        'k_deskripsi' => ''
                    ];
                    $ns = $this->rapor->getNilaiHarianKelas($id_mapel, $guru->wali_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
                    $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $ns;
                }
            } else {
                $guru_mapel = '';
                foreach ($jabatan_guru as $jab) {
                    foreach ($jab->ekstra_kelas as $mk) {
                        if ($mk->id_ekstra == $id_mapel) {
                            foreach ($mk->kelas_ekstra as $km) {
                                if ($km->kelas == $guru->wali_kelas) {
                                    $guru_mapel = $jab->nama_guru;
                                }
                            }
                        }
                    }
                }

                foreach ($siswas as $siswa) {
                    $dummyEkstra = ['deskripsi' => '', 'nilai' => '', 'predikat' => ''];
                    $ne = $this->rapor->getEkstraKelas($id_mapel, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
                    $nilai[$siswa->id_siswa] = $ne == null ? json_decode(json_encode($dummyEkstra)) : $ne;
                }
            }

            $data['siswa'] = $siswas;
            $data['nilai'] = $nilai;
            $data['kkm'] = $kkm;
            $data['kkm_ekstra'] = $kkm_ekstra;
            $data['guru_mapel'] = $guru_mapel;
        }

        return $this->renderView('members/guru/rapor/nilai/nilaiguru', $data);
    }

    public function raporCekNilai($filter = null, $id_mapel = null)
    {
        $user = $this->getUser();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Semua Nilai',
            'subjudul' => 'Semua Nilai Rapor',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
        ];

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data['guru'] = null;
            $data['mapel'] = $this->dropdown->getAllMapel();
            $data['ekstra'] = $this->dropdown->getAllEkskul();
            $data['filter'] = ['' => 'Filter berdasarkan', '1' => 'Mata Pelajaran', '2' => 'Ekstrakurikuler'];
            $data['ekstra_selected'] = $id_mapel;
            $data['mapel_selected'] = $id_mapel;
            $data['filter_selected'] = $filter;
            return $this->renderView('members/guru/rapor/nilai/periksa', $data);
        }
        $data['guru'] = $guru;

        $ret[''] = "Pilih Mapel";
        $data['mapel'] = $ret + $this->dropdown->getAllMapel();

        $ret_e[''] = "Pilih Eskul";
        $data['ekstra'] = $ret_e + $this->dropdown->getAllEkskul();

        $data['filter'] = ['' => 'Filter berdasarkan', '1' => 'Mata Pelajaran', '2' => 'Ekstrakurikuler'];
        $data['ekstra_selected'] = $id_mapel;
        $data['mapel_selected'] = $id_mapel;
        $data['filter_selected'] = $filter;

        $jabatan_guru = $this->master->getGuruMapel($tp->id_tp, $smt->id_smt);
        foreach ($jabatan_guru as $jabatan) {
            $jabatan->mapel_kelas = $this->maybe_unserialize($jabatan->mapel_kelas ?? '[]');
            $jabatan->ekstra_kelas = $this->maybe_unserialize($jabatan->ekstra_kelas ?? '[]');
        }

        if ($id_mapel != null) {
            $setting = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);
            $jenis = $filter == '1' ? '1' : '2';
            $kkm = null;
            if ($setting) {
                if ($setting->kkm_tunggal == '1') {
                    $kkm = $setting;
                } else {
                    $kkm = $this->rapor->getKkm($id_mapel . $guru->wali_kelas . $tp->id_tp . $smt->id_smt . $jenis);
                }
            }

            $siswas = $this->kelas->getKelasSiswa($guru->wali_kelas, $tp->id_tp, $smt->id_smt);
            $nilai = [];

            if ($filter == '1') {
                $guru_mapel = '';
                foreach ($jabatan_guru as $jab) {
                    foreach ($jab->mapel_kelas as $mk) {
                        if ($mk->id_mapel == $id_mapel) {
                            foreach ($mk->kelas_mapel as $km) {
                                if ($km->kelas == $guru->wali_kelas) {
                                    $guru_mapel = $jab->nama_guru;
                                }
                            }
                        }
                    }
                }

                foreach ($siswas as $siswa) {
                    $dummyNilai = [
                        'p1' => '',
                        'p2' => '',
                        'p3' => '',
                        'p4' => '',
                        'p5' => '',
                        'p6' => '',
                        'p7' => '',
                        'p8' => '',
                        'p_rata_rata' => '',
                        'p_predikat' => '=',
                        'p_deskripsi' => '',
                        'k1' => '',
                        'k2' => '',
                        'k3' => '',
                        'k4' => '',
                        'k5' => '',
                        'k6' => '',
                        'k7' => '',
                        'k8' => '',
                        'k_rata_rata' => '',
                        'k_predikat' => '',
                        'k_deskripsi' => ''
                    ];
                    $ns = $this->rapor->getNilaiHarianKelas($id_mapel, $guru->wali_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
                    $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $ns;
                }
            } else {
                $guru_mapel = '';
                foreach ($jabatan_guru as $jab) {
                    foreach ($jab->ekstra_kelas as $mk) {
                        if ($mk->id_ekstra == $id_mapel) {
                            foreach ($mk->kelas_ekstra as $km) {
                                if ($km->kelas == $guru->wali_kelas) {
                                    $guru_mapel = $jab->nama_guru;
                                }
                            }
                        }
                    }
                }

                foreach ($siswas as $siswa) {
                    $dummyEkstra = ['deskripsi' => '', 'nilai' => '', 'predikat' => ''];
                    $ne = $this->rapor->getEkstraKelas($id_mapel, $siswa->id_siswa, $tp->id_tp, $smt->id_smt);
                    $nilai[$siswa->id_siswa] = $ne == null ? json_decode(json_encode($dummyEkstra)) : $ne;
                }
            }

            $data['siswa'] = $siswas;
            $data['nilai'] = $nilai;
            $data['kkm_guru'] = $kkm;
            $data['kkm_setting'] = $setting;
            $data['guru_mapel'] = $guru_mapel;
        }

        return $this->renderView('members/guru/rapor/nilai/periksa', $data);
    }

    public function downloadTemplatePts($id_mapel, $id_kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilais = $this->rapor->getAllNilaiPtsKelas($id_mapel, $id_kelas, $tp->id_tp, $smt->id_smt);
        foreach ($siswas as $ind => $siswa) {
            $siswa->no = ($ind + 1);
            $siswa->no_induk = $siswa->nisn != null ? "'" . $siswa->nisn : "'" . $siswa->nis;
            $siswa->nilai = isset($nilais[$siswa->id_siswa]) ? $nilais[$siswa->id_siswa]->nilai : '';
            $siswa->predikat = isset($nilais[$siswa->id_siswa]) ? $nilais[$siswa->id_siswa]->predikat : '';
        }

        return $this->response->setJSON(['siswa' => $siswas]);
    }

    public function importPts()
    {
        $inputs = $this->request->getPost('siswa');
        $updated = 0;
        if (is_array($inputs)) {
            $this->db->transStart();
            foreach ($inputs as $data) {
                $update = $this->db->table('rapor_nilai_pts')->replace($data);
                if ($update)
                    $updated++;
            }
            $this->db->transComplete();
        }
        return $this->response->setJSON($updated);
    }

    public function downloadTemplatePas($id_mapel, $id_kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilais = $this->rapor->getAllNilaiAkhirKelas($id_mapel, $id_kelas, $tp->id_tp, $smt->id_smt);
        foreach ($siswas as $ind => $siswa) {
            $siswa->no = ($ind + 1);
            $siswa->no_induk = $siswa->nisn != null ? "'" . $siswa->nisn : "'" . $siswa->nis;
            $siswa->nilai = isset($nilais[$siswa->id_siswa]) ? $nilais[$siswa->id_siswa]->npas : '';
        }

        return $this->response->setJSON(['siswa' => $siswas]);
    }

    public function importPas()
    {
        $inputs = $this->request->getPost('siswa');
        $updated = 0;
        if (is_array($inputs)) {
            $this->db->transStart();
            foreach ($inputs as $data) {
                $update = $this->db->table('rapor_nilai_akhir')->replace($data);
                if ($update)
                    $updated++;
            }
            $this->db->transComplete();
        }
        return $this->response->setJSON($updated);
    }

    public function downloadTemplateEkstra($id_ekstra, $id_kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilais = $this->rapor->getAllNilaiEkstraKelas($id_ekstra, $id_kelas, $tp->id_tp, $smt->id_smt);
        foreach ($siswas as $ind => $siswa) {
            $siswa->no = ($ind + 1);
            $siswa->no_induk = $siswa->nisn != null ? "'" . $siswa->nisn : "'" . $siswa->nis;
            $siswa->nilai = isset($nilais[$siswa->id_siswa]) ? $nilais[$siswa->id_siswa]->nilai : '';
        }

        return $this->response->setJSON(['siswa' => $siswas]);
    }

    public function importEkstra()
    {
        $inputs = $this->request->getPost('siswa');
        $updated = 0;
        if (is_array($inputs)) {
            $this->db->transStart();
            foreach ($inputs as $data) {
                $update = $this->db->table('rapor_nilai_ekstra')->replace($data);
                if ($update)
                    $updated++;
            }
            $this->db->transComplete();
        }
        return $this->response->setJSON($updated);
    }

    public function uploadNilaiPts()
    {
        $p_siswa = $this->request->getPost('siswa');
        $id_mapel = $this->request->getPost('id_mapel');
        $id_kelas = $this->request->getPost('id_kelas');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $datas = [];
        if (is_array($p_siswa)) {
            foreach ($p_siswa as $siswa) {
                $siswa['id_nilai_pts'] = $id_mapel . $id_kelas . $siswa['id'] . $tp->id_tp . $smt->id_smt;
                $siswa['id_siswa'] = $siswa['id'];
                $siswa['id_mapel'] = $id_mapel;
                $siswa['id_kelas'] = $id_kelas;
                $siswa['id_tp'] = $tp->id_tp;
                $siswa['id_smt'] = $smt->id_smt;

                unset($siswa['id']);
                unset($siswa['nisn']);
                unset($siswa['namasiswa']);
                $datas[] = $siswa;
            }
        }

        $updated = 0;
        foreach ($datas as $data) {
            $update = $this->db->table('rapor_nilai_pts')->replace($data);
            if ($update)
                $updated++;
        }
        return $this->response->setJSON($updated);
    }

    public function uploadNilaiPas()
    {
        $p_siswa = $this->request->getPost('siswa');
        $id_mapel = $this->request->getPost('id_mapel');
        $id_kelas = $this->request->getPost('id_kelas');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $datas = [];
        if (is_array($p_siswa)) {
            foreach ($p_siswa as $siswa) {
                $siswa['id_nilai_akhir'] = $id_mapel . $id_kelas . $siswa['id'] . $tp->id_tp . $smt->id_smt;
                $siswa['id_siswa'] = $siswa['id'];
                $siswa['id_mapel'] = $id_mapel;
                $siswa['id_kelas'] = $id_kelas;
                $siswa['id_tp'] = $tp->id_tp;
                $siswa['id_smt'] = $smt->id_smt;

                unset($siswa['id']);
                unset($siswa['nisn']);
                unset($siswa['namasiswa']);

                $datas[] = $siswa;
            }
        }

        $updated = 0;
        foreach ($datas as $data) {
            $update = $this->db->table('rapor_nilai_akhir')->replace($data);
            if ($update)
                $updated++;
        }
        return $this->response->setJSON($updated);
    }

    public function uploadNilaiEkstra()
    {
        $p_siswa = $this->request->getPost('siswa');
        $id_ekstra = $this->request->getPost('id_ekstra');
        $id_kelas = $this->request->getPost('id_kelas');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $datas = [];
        if (is_array($p_siswa)) {
            foreach ($p_siswa as $siswa) {
                $siswa['id_nilai_ekstra'] = $id_ekstra . $id_kelas . $siswa['id'] . $tp->id_tp . $smt->id_smt;
                $siswa['id_siswa'] = $siswa['id'];
                $siswa['id_ekstra'] = $id_ekstra;
                $siswa['id_kelas'] = $id_kelas;
                $siswa['id_tp'] = $tp->id_tp;
                $siswa['id_smt'] = $smt->id_smt;

                unset($siswa['id']);
                unset($siswa['nisn']);
                unset($siswa['namasiswa']);
                $datas[] = $siswa;
            }
        }

        $updated = 0;
        foreach ($datas as $data) {
            $update = $this->db->table('rapor_nilai_ekstra')->replace($data);
            if ($update)
                $updated++;
        }
        return $this->response->setJSON($updated);
    }

    public function raporSikap()
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');
        $data = [
            'user' => $user,
            'judul' => 'Input Nilai Sikap',
            'subjudul' => 'Input Nilai Sikap',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            $data['guru'] = null;
            $data['sikap'] = [];
            return $this->renderView('members/guru/rapor/sikap/data', $data);
        }
        $id_kelas = $guru->wali_kelas;
        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

        $arrMapel = [];
        $arrKelas = [];
        foreach ($mapel as $m) {
            $arrMapel[$m->id_mapel] = $m->nama_mapel;
            foreach ($m->kelas_mapel as $kls) {
                $arrKelas[$m->id_mapel][] = ['id_kelas' => $kls->kelas, 'nama_kelas' => $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->kelas)];
            }
        }
        $dummySikap = [];
        //spiritual
        for ($i = 0; $i < 10; $i++) {
            $no = $i + 1;
            $s = ['id_sikap' => (1) . $no, 'jenis' => '1', 'kode' => $no, 'sikap' => ''];
            array_push($dummySikap, $s);
        }
        //sosial
        for ($i = 0; $i < 10; $i++) {
            $no = $i + 1;
            $s = ['id_sikap' => (2) . $no, 'jenis' => '2', 'kode' => $no, 'sikap' => ''];
            array_push($dummySikap, $s);
        }

        $sikap = $this->rapor->getDeskripsiSikap($id_kelas, $tp->id_tp, $smt->id_smt);
        if (count($sikap) == 0) {
            $sikap = json_decode(json_encode($dummySikap));
        }

        $data['guru'] = $guru;
        $data['mapel'] = $arrMapel;
        $data['kelas'] = $arrKelas;
        $data['sikap'] = $sikap;

        return $this->renderView('members/guru/rapor/sikap/data', $data);
    }

    public function saveSikap()
    {
        $input = json_decode($this->request->getPost('sikap') ?? '[]');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $update = false;
        foreach ($input as $d) {
            $data = [
                'id_sikap' => $d->id_sikap,
                'id_tp' => $tp->id_tp,
                'id_smt' => $smt->id_smt,
                'sikap' => $d->sikap,
            ];

            if (isset($d->kelas))
                $data['id_kelas'] = $d->kelas;
            if (isset($d->jenis))
                $data['jenis'] = $d->jenis;
            if (isset($d->kode))
                $data['kode'] = $d->kode;

            $update = $this->db->table('rapor_data_sikap')->replace($data);
        }
        return $this->response->setJSON(['status' => $update]);
    }

    public function raporSpiritual()
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            return redirect()->to('dashboard');
        }
        $id_kelas = $guru->wali_kelas;
        $kelas = $this->kelas->get_one($id_kelas, $tp->id_tp, $smt->id_smt);

        $dummySpiritual = [];
        $dummyTexts = $this->rapor->getDummyDeskripsiSpiritual();
        for ($i = 0; $i < 10; $i++) {
            $no = $i + 1;
            $s = ['id_sikap' => $id_kelas . (1) . $no, 'jenis' => '1', 'kode' => $no, 'sikap' => $dummyTexts[$i]];
            array_push($dummySpiritual, $s);
        }

        $spiritual = $this->rapor->getDeskripsiSikapByJenis($id_kelas, '1', $tp->id_tp, $smt->id_smt);
        if (count($spiritual) == 0) {
            $spiritual = json_decode(json_encode($dummySpiritual));
        }

        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilai = [];

        for ($i = 0; $i < count($siswas); $i++) {
            $siswa = $siswas[$i];
            $dummyNilai = ['predikat' => '', 'sl1' => '', 'sl2' => '', 'sl3' => '', 'mb1' => '', 'mb2' => '', 'mb3' => '',];

            $ns = $this->rapor->getNilaiSikapKelas($id_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt, '1');
            $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $this->maybe_unserialize($ns->nilai);
        }

        $data = [
            'user' => $user,
            'judul' => 'Nilai Spiritual Kelas ',
            'subjudul' => 'Input Nilai',
            'setting' => $this->dashboard->getSetting(),
            'guru' => $guru,
            'kelas' => $kelas,
            'siswa' => $siswas,
            'nilai' => $nilai,
            'spiritual' => $spiritual,
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        return $this->renderView('members/guru/rapor/sikap/spiritual', $data);
    }

    public function importSpiritual()
    {
        $id_kelas = $this->request->getPost('id_kelas');
        $input = json_decode($this->request->getPost('nilai') ?? '[]');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $datas = [];
        foreach ($input as $in) {
            $id_siswa = $in[11];
            if ($id_siswa != 'id') {
                $datas[] = [
                    'id_nilai_sikap' => $id_kelas . $id_siswa . $tp->id_tp . $smt->id_smt . '1',
                    'id_siswa' => $id_siswa,
                    'id_kelas' => $id_kelas,
                    'jenis' => 1,
                    'nilai' => json_encode([
                        'predikat' => $in[3] ?? '',
                        'sl1' => $in[4] ?? '',
                        'sl2' => $in[5] ?? '',
                        'sl3' => $in[6] ?? '',
                        'mb1' => $in[7] ?? '',
                        'mb2' => $in[8] ?? '',
                        'mb3' => $in[9] ?? ''
                    ]),
                    'deskripsi' => $in[10] ?? '',
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                ];
            }
        }
        $updated = 0;
        foreach ($datas as $data) {
            $update = $this->db->table('rapor_nilai_sikap')->replace($data);
            if ($update)
                $updated++;
        }
        return $this->response->setJSON($updated);
    }

    public function raporSosial()
    {
        $user = $this->getUser();
        if (!$user)
            return redirect()->to('auth');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        if (!$guru) {
            return redirect()->to('dashboard');
        }
        $id_kelas = $guru->wali_kelas;
        $kelas = $this->kelas->get_one($id_kelas, $tp->id_tp, $smt->id_smt);

        $dummySosial = [];
        $dummyTexts = $this->rapor->getDummyDeskripsiSosial();
        for ($i = 0; $i < 10; $i++) {
            $no = $i + 1;
            $s = ['id_sikap' => $id_kelas . (2) . $no, 'jenis' => '2', 'kode' => $no, 'sikap' => $dummyTexts[$i]];
            array_push($dummySosial, $s);
        }

        $sosial = $this->rapor->getDeskripsiSikapByJenis($id_kelas, '2', $tp->id_tp, $smt->id_smt);
        if (count($sosial) == 0) {
            $sosial = json_decode(json_encode($dummySosial));
        }

        $siswas = $this->kelas->getKelasSiswa($id_kelas, $tp->id_tp, $smt->id_smt);
        $nilai = [];

        for ($i = 0; $i < count($siswas); $i++) {
            $siswa = $siswas[$i];
            $dummyNilai = ['predikat' => '', 'sl1' => '', 'sl2' => '', 'sl3' => '', 'mb1' => '', 'mb2' => '', 'mb3' => '',];

            $ns = $this->rapor->getNilaiSikapKelas($id_kelas, $siswa->id_siswa, $tp->id_tp, $smt->id_smt, '2');
            $nilai[$siswa->id_siswa] = $ns == null ? json_decode(json_encode($dummyNilai)) : $this->maybe_unserialize($ns->nilai);
        }

        $data = [
            'user' => $user,
            'judul' => 'Nilai Sosial Kelas ',
            'subjudul' => 'Input Nilai',
            'setting' => $this->dashboard->getSetting(),
            'guru' => $guru,
            'kelas' => $kelas,
            'siswa' => $siswas,
            'nilai' => $nilai,
            'sosial' => $sosial,
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        return $this->renderView('members/guru/rapor/sikap/sosial', $data);
    }

    public function importSosial()
    {
        $id_kelas = $this->request->getPost('id_kelas');
        $input = json_decode($this->request->getPost('nilai') ?? '[]');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $datas = [];
        foreach ($input as $in) {
            $id_siswa = $in[11];
            if ($id_siswa != 'id') {
                $datas[] = [
                    'id_nilai_sikap' => $id_kelas . $id_siswa . $tp->id_tp . $smt->id_smt . '2',
                    'id_siswa' => $id_siswa,
                    'id_kelas' => $id_kelas,
                    'jenis' => 2,
                    'nilai' => json_encode([
                        'predikat' => $in[3] ?? '',
                        'sl1' => $in[4] ?? '',
                        'sl2' => $in[5] ?? '',
                        'sl3' => $in[6] ?? '',
                        'mb1' => $in[7] ?? '',
                        'mb2' => $in[8] ?? '',
                        'mb3' => $in[9] ?? ''
                    ]),
                    'deskripsi' => $in[10] ?? '',
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                ];
            }
        }
        $updated = 0;
        foreach ($datas as $data) {
            $update = $this->db->table('rapor_nilai_sikap')->replace($data);
            if ($update)
                $updated++;
        }
        return $this->response->setJSON($updated);
    }

}
