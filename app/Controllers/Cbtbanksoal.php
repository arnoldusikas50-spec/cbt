<?php

namespace App\Controllers;

use App\Models\CbtKartuSoalModel;
use App\Models\CbtKartuSoalItemModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;
use App\Models\CbtModel;
use App\Models\LogModel;
use App\Models\TujuanPembelajaranModel;
use App\Models\CbtDeadlineGuruModel;
use App\Services\AIService;
use App\Services\CbtService;
use CodeIgniter\Model;

class Cbtbanksoal extends BaseController
{
    protected ?MasterModel $master = null;
    protected ?DashboardModel $dashboard = null;
    protected ?CbtModel $cbt = null;
    protected ?LogModel $logging = null;
    protected ?CbtKartuSoalModel $kartu = null;
    protected ?CbtKartuSoalItemModel $kartuItems = null;
    protected ?TujuanPembelajaranModel $tujuan = null;
    protected ?AIService $ai = null;
    protected ?CbtService $cbtService = null;
    protected ?CbtDeadlineGuruModel $deadlineModel = null;

    public function __construct()
    {
        // Buat model secara terpisah dengan try-catch agar ketika database
        // tidak tersedia, controller masih bisa di-instansiasi dan method
        // individual yang sudah punya try-catch dapat menangani error secara
        // halus, alih-alih seluruh request gagal dengan CRITICAL error.
        $this->master     = $this->_newModel(MasterModel::class);
        $this->dashboard  = $this->_newModel(DashboardModel::class);
        $this->cbt        = $this->_newModel(CbtModel::class);
        $this->logging    = $this->_newModel(LogModel::class);
        $this->kartu      = $this->_newModel(CbtKartuSoalModel::class);
        $this->kartuItems = $this->_newModel(CbtKartuSoalItemModel::class);
        $this->tujuan     = $this->_newModel(TujuanPembelajaranModel::class);
        if ($this->tujuan !== null) {
            $this->tujuan->initialize();
        }
        $this->ai           = $this->_newService(AIService::class);
        $this->cbtService   = $this->_newService(CbtService::class);
        $this->deadlineModel= $this->_newModel(CbtDeadlineGuruModel::class);
    }

    /**
     * Buat instance model dengan tangkap kesalahan koneksi database.
     * Mengembalikan null jika gagal (mis. database down).
     */
    protected function _newModel(string $class): ?Model
    {
        try {
            return new $class();
        } catch (\Throwable $e) {
            log_message('warning', '[Cbtbanksoal] Gagal inisialisasi model ' . $class . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Buat instance service dengan tangkap kesalahan.
     * Mengembalikan null jika gagal.
     */
    protected function _newService(string $class): mixed
    {
        try {
            return new $class();
        } catch (\Throwable $e) {
            log_message('warning', '[Cbtbanksoal] Gagal inisialisasi service ' . $class . ': ' . $e->getMessage());
            return null;
        }
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        // Only allow admin or panitia with jabatan 'panitia' (not 'pengawas')
        if (!$this->hasPanitiaJabatan() && !$this->inGroup('guru')) {
            return redirect()->to('dashboard')->with('error', 'Akses ditolak. Hanya Administrator atau Panitia yang dapat mengakses Bank Soal.');
        }

        try {
            $user = $this->authModel->user();
            $setting = $this->dashboard->getSetting();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            if (!$tp || !$smt) {
                return redirect()->to('dashboard')->with('error', 'Tahun Pelajaran atau Semester belum diatur.');
            }

            $data = [
                'user' => $user,
                'judul' => 'Bank Soal',
                'subjudul' => 'Soal',
                'setting' => $setting,
                'tp' => $this->dashboard->getTahun(),
                'tp_active' => $tp,
                'smt' => $this->dashboard->getSemester(),
                'smt_active' => $smt,
                'deadline_info' => null,
                // Admin/panitia boleh tetap mengedit bank soal, termasuk yang
                // sedang dipakai jadwal ujian aktif.
                'is_admin' => $this->isAdmin() || $this->isPanitia(),
            ];

            // Convert levels to associative array for form_dropdown
            $levelsRaw = $this->master->getLevelsByJenjang($setting->jenjang ?? 1);
            $levels = [];
            foreach ($levelsRaw as $l) {
                $levels[$l->level] = 'Level ' . $l->level;
            }
            $data['levels'] = $levels;

            $type = $this->request->getGet('type');
            $mode = $this->request->getGet('mode') ?? '1';
            $data['mode'] = $mode;
            $data['id_filter'] = $type ?? '';

            if ($this->isAdmin() || $this->isPanitia()) {
                // Convert to associative arrays for form_dropdown
                $mapelsRaw = $this->master->getAllMapel();
                $mapels = [];
                foreach ($mapelsRaw as $m) {
                    $mapels[$m->id_mapel] = $m->nama_mapel;
                }
                $data['mapels'] = $mapels;

                $gurusRaw = $this->master->getAllGuru();
                $gurus = [];
                foreach ($gurusRaw as $g) {
                    $gurus[$g->id_guru] = $g->nama_guru;
                }
                $data['gurus'] = $gurus;

                $data['kelas'] = $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt);
                $data['filters'] = ['0' => 'Semua', '1' => 'Guru', '2' => 'Mapel', '3' => 'Level', '4' => 'Sub-tes'];
                $data['subtes_options'] = $this->subtesOptions();

                $id_guru = null;
                $id_mapel = null;
                $id_level = null;
                $id_subtes = null;

                if ($type == '1') {
                    $id_guru = $this->request->getGet('id');
                    $data['id_guru'] = $id_guru;
                } elseif ($type == '2') {
                    $id_mapel = $this->request->getGet('id');
                    $data['id_mapel'] = $id_mapel;
                } elseif ($type == '3') {
                    $id_level = $this->request->getGet('id');
                    $data['id_level'] = $id_level;
                } elseif ($type == '4') {
                    $id_subtes = $this->request->getGet('id');
                    $data['id_subtes'] = $id_subtes;
                }

                if ($type !== null && $type !== '0') {
                    $data['banks'] = $this->cbt->getDataBank($id_guru, $id_mapel, $id_level, $id_subtes);
                } else {
                    $data['banks'] = $this->cbt->getDataBank();
                }
                $this->assignPaketLabels($data['banks']);

                $data['is_panitia'] = $this->isPanitia();
                return view('cbt/banksoal/data', $data);
            } else if ($this->inGroup('guru')) {
                $guru = $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);

                if (!$guru) {
                    return redirect()->to('dashboard')->with('error', 'Data Guru belum lengkap atau tidak aktif di tahun ini.');
                }

                $data['guru'] = $guru;
                $data['filters'] = ['0' => 'Semua', '2' => 'Mapel', '3' => 'Level', '4' => 'Sub-tes'];
                $data['subtes_options'] = $this->subtesOptions();
                $data['kelas'] = $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt);

                $id_mapel = null;
                $id_level = null;
                $id_subtes = null;

                if ($type == '2') {
                    $id_mapel = $this->request->getGet('id');
                    $data['id_mapel'] = $id_mapel;
                } elseif ($type == '3') {
                    $id_level = $this->request->getGet('id');
                    $data['id_level'] = $id_level;
                } elseif ($type == '4') {
                    $id_subtes = $this->request->getGet('id');
                    $data['id_subtes'] = $id_subtes;
                }

                if ($type !== null) {
                    $data['banks'] = $this->cbt->getDataBank((int) $guru->id_guru, $id_mapel, $id_level, $id_subtes);
                } else {
                    $data['banks'] = $this->cbt->getDataBank((int) $guru->id_guru);
                }
                $this->assignPaketLabels($data['banks']);

                $data['is_panitia'] = $this->isPanitia();
                $data['deadline_info'] = $this->getDeadlineInfo();
                return view('cbt/banksoal/data', $data);
            }

            return redirect()->to('dashboard');
        } catch (\Throwable $th) {
            log_message('error', '[Cbtbanksoal::index] ' . $th->getMessage());
            return redirect()->to('dashboard')->with('error', 'Terjadi kesalahan saat memuat Bank Soal: ' . $th->getMessage());
        }
    }

    /**
     * Assign label "Paket A", "Paket B", dst ke bank soal yang share kartu_soal_id sama.
     * banks = [$tp][$smt][] → dimodifikasi langsung (pass by reference via array).
     */
    private function assignPaketLabels(array &$banks): void
    {
        $counts = [];
        foreach ($banks as $tpGroups) {
            foreach ($tpGroups as $smtBanks) {
                foreach ($smtBanks as $bank) {
                    $kid = (int) ($bank->kartu_soal_id ?? 0);
                    if ($kid > 0) {
                        $counts[$kid] = ($counts[$kid] ?? 0) + 1;
                    }
                }
            }
        }

        $letters = [];
        foreach ($banks as $tpGroups) {
            foreach ($tpGroups as &$smtBanks) {
                foreach ($smtBanks as &$bank) {
                    $kid = (int) ($bank->kartu_soal_id ?? 0);
                    if ($kid > 0 && ($counts[$kid] ?? 0) > 1) {
                        $idx = $letters[$kid] ?? 0;
                        $bank->paket_label = chr(65 + $idx);
                        $letters[$kid] = $idx + 1;
                    } else {
                        $bank->paket_label = null;
                    }
                }
            }
        }
    }

    public function getMapelGuru()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
            }
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
                    $idM = is_object($m) ? ($m->id_mapel ?? null) : (is_array($m) ? ($m['id_mapel'] ?? null) : null);
                    if (!$idM) continue;
                    $namaM = is_object($m) ? ($m->nama_mapel ?? null) : (is_array($m) ? ($m['nama_mapel'] ?? null) : null);
                    if (!$namaM) {
                        $namaM = $this->master->getMapelById((int)$idM)?->nama_mapel ?? '-';
                    }
                    $arrMapel[$idM] = $namaM;
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
            if (!$this->isLoggedIn()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
            }
            $level = $this->request->getGet('level');
            // id_guru and mapel are ignored to show ALL classes in level

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $db = \Config\Database::connect();
            $builder = $db->table('master_kelas');
            $builder->where('id_tp', $tp->id_tp);
            $builder->where('id_smt', $smt->id_smt);
            if ($level) {
                $builder->where('level_id', $level);
            }
            $kelas = $builder->orderBy('nama_kelas', 'ASC')->get()->getResult();

            return $this->response->setJSON(['kelas' => $kelas]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getKartuOptions()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Sesi habis.']);
            }

            $mapelId = (int) ($this->request->getGet('mapel_id') ?? 0);
            $jenisId = (int) ($this->request->getGet('jenis_id') ?? 0);
            $currentKartuId = (int) ($this->request->getGet('current_kartu_id') ?? 0);
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            if (!$tp || !$smt) {
                return $this->response->setJSON(['status' => false, 'message' => 'Tahun/Semester aktif tidak ditemukan.']);
            }

            $builder = $this->kartu
                ->select('cbt_kartu_soal.id_kartu, cbt_kartu_soal.judul, cbt_kartu_soal.mapel_id, cbt_kartu_soal.id_jenis, cbt_kartu_soal.tp_id, cbt_kartu_soal.smt_id, cbt_kartu_soal.workflow_status, m.nama_mapel, j.nama_jenis')
                ->join('master_mapel m', 'm.id_mapel = cbt_kartu_soal.mapel_id', 'left')
                ->join('cbt_jenis j', 'j.id_jenis = cbt_kartu_soal.id_jenis', 'left')
                ->where('cbt_kartu_soal.deleted_at', null)
                ->groupStart()
                    ->groupStart()
                        ->where('cbt_kartu_soal.tp_id', (int) $tp->id_tp)
                        ->where('cbt_kartu_soal.smt_id', (int) $smt->id_smt)
                        ->where('cbt_kartu_soal.workflow_status', 'approved');

                        if ($this->inGroup('guru')) {
                            $builder->where('cbt_kartu_soal.created_by', (int) $this->getUserId());
                        }
                        if ($mapelId > 0) {
                            $builder->where('cbt_kartu_soal.mapel_id', $mapelId);
                        }
                        if ($jenisId > 0) {
                            $builder->groupStart()
                                ->where('cbt_kartu_soal.id_jenis', $jenisId)
                                ->orWhere('cbt_kartu_soal.id_jenis', 0)
                                ->orWhere('cbt_kartu_soal.id_jenis IS NULL')
                                ->groupEnd();
                        }
                    $builder->groupEnd();

                    if ($currentKartuId > 0) {
                        $builder->orWhere('cbt_kartu_soal.id_kartu', $currentKartuId);
                    }
                $builder->groupEnd();

            $list = $builder->orderBy('cbt_kartu_soal.updated_at', 'DESC')->findAll() ?? [];
            $data = [];
            foreach ($list as $row) {
                $wfStatus = strtolower(trim((string) ($row->workflow_status ?? 'draft')));
                $statusBadge = $wfStatus === 'approved' ? '✓' : '⏳';
                $data[] = [
                    'id_kartu' => (int) ($row->id_kartu ?? 0),
                    'judul' => (string) ($row->judul ?? '-'),
                    'mapel_id' => (int) ($row->mapel_id ?? 0),
                    'id_jenis' => (int) ($row->id_jenis ?? 0),
                    'tp_id' => (int) ($row->tp_id ?? 0),
                    'smt_id' => (int) ($row->smt_id ?? 0),
                    'workflow_status' => $wfStatus,
                    'nama_mapel' => (string) ($row->nama_mapel ?? '-'),
                    'nama_jenis' => (string) ($row->nama_jenis ?? '-'),
                    'label' => $statusBadge . ' ' . trim((string) ($row->judul ?? '-')) . ' (' . (string) ($row->nama_mapel ?? '-') . ') - ' . (string) ($row->nama_jenis ?? '-') . ' [' . (string) ($tp->tahun ?? '-') . ' ' . (string) ($smt->nama_smt ?? '-') . ']',
                ];
            }

            return $this->response->setJSON(['status' => true, 'data' => $data]);
        } catch (\Throwable $th) {
            log_message('error', '[Cbtbanksoal::getKartuOptions] ' . $th->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat opsi kartu soal.']);
        }
    }

    public function checkWorkflow()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Sesi habis.']);
            }

            $mapelId = (int) ($this->request->getGet('mapel_id') ?? 0);
            $kartuId = (int) ($this->request->getGet('kartu_id') ?? 0);
            $jenisId = (int) ($this->request->getGet('jenis_id') ?? 0);

            if ($mapelId <= 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Mapel belum dipilih.']);
            }

            if (!$this->inGroup('guru')) {
                return $this->response->setJSON(['status' => true, 'message' => 'OK', 'data' => ['ready' => true]]);
            }

            $status = $this->getWorkflowStatusForGuru($mapelId, $kartuId > 0 ? $kartuId : null);
            if ($status['tujuan_count'] <= 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Belum ada Tujuan Pembelajaran untuk mapel ini. Susun dulu TP sebelum membuat struktur bank soal.',
                    'errors' => ['mapel' => 'Tujuan Pembelajaran belum tersedia'],
                    'data' => $status,
                ]);
            }

            if (($status['kartu_count'] ?? 0) <= 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Belum ada Kartu Soal (kisi-kisi) untuk mapel ini. Buat Kartu Soal terlebih dahulu di modul Kartu Soal.',
                    'errors' => ['kartu_soal' => 'Kartu soal belum dibuat'],
                    'data' => $status,
                ]);
            }

            if (($status['kartu_approved_count'] ?? 0) <= 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Kartu Soal sudah ada (' . ($status['kartu_count'] ?? 0) . ' kartu) tapi belum ada yang berstatus Approved. Ajukan Kartu Soal untuk disetujui oleh admin.',
                    'errors' => ['kartu_soal' => 'Kartu soal belum disetujui (Approved)'],
                    'data' => $status,
                ]);
            }

            if ($kartuId > 0) {
                $kartu = $this->kartu->find($kartuId);
                if (!$kartu) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Kartu Soal tidak ditemukan.',
                        'errors' => ['kartu_soal' => 'Kartu soal tidak valid'],
                        'data' => $status,
                    ]);
                }
                if ((int) ($kartu->mapel_id ?? 0) !== $mapelId) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Kartu Soal tidak sesuai mapel yang dipilih.',
                        'errors' => ['kartu_soal' => 'Kartu soal harus sesuai mapel'],
                        'data' => $status,
                    ]);
                }
                $kartuWf = strtolower(trim((string) ($kartu->workflow_status ?? 'draft')));
                if ($kartuWf !== 'approved') {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Kartu Soal yang dipilih belum berstatus Approved (status saat ini: ' . ucfirst($kartuWf) . '). Pilih kartu soal yang sudah disetujui.',
                        'errors' => ['kartu_soal' => 'Status kartu soal harus Approved'],
                        'data' => $status,
                    ]);
                }
                if ($jenisId > 0 && (int) ($kartu->id_jenis ?? 0) > 0 && (int) ($kartu->id_jenis ?? 0) !== $jenisId) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Kartu Soal tidak sesuai jenis ujian.',
                        'errors' => ['kartu_soal' => 'Kartu soal harus sesuai jenis ujian'],
                        'data' => $status,
                    ]);
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => $status['message'] ?? 'Alur kisi-kisi tersedia. Lanjutkan ke pemilihan kartu soal.',
                'data' => $status,
            ]);
        } catch (\Throwable $th) {
            log_message('error', '[Cbtbanksoal::checkWorkflow] ' . $th->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    public function apiGetKartuSummary()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated']);
        }

        $kartuId = (int) $this->request->getGet('kartu_id');
        if ($kartuId <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID Kartu tidak valid']);
        }

        // Guard: guru hanya boleh intip kartunya sendiri, kartu harus approved
        $kartuRow = $this->kartu->find($kartuId);
        if (!$kartuRow) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kartu soal tidak ditemukan'])->setStatusCode(404);
        }
        if ($this->inGroup('guru') && (int) ($kartuRow->created_by ?? 0) !== (int) $this->getUserId()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak'])->setStatusCode(403);
        }

        $itemModel = new \App\Models\CbtKartuSoalItemModel();
        $summary = $itemModel->getKartuSummary($kartuId);

        return $this->response->setJSON([
            'status' => true,
            'data' => $summary
        ]);
    }

    public function addBank()
    {
        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Get kartu soal for current user (guru or all for admin)
        $kartuSoalList = [];
        if ($this->isAdmin() || $this->isPanitia()) {
            $kartuSoalList = $this->getKartuSoalForCurrentScope();
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);
            if ($guru) {
                $kartuSoalList = $this->getKartuSoalForCurrentScope((int) $user->id);
            }
        }

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
            'is_admin' => $this->isAdmin() || $this->isPanitia(),
            'kartu_soal' => $kartuSoalList,
            'force_kartu_workflow' => $this->inGroup('guru'),
            'workflow_hint' => null,
            'schedule_warning' => null,
        ];

        if ($this->isAdmin() || $this->isPanitia()) {
            $data['kelas'] = $this->master->getAllKelas((int) $tp->id_tp, (int) $smt->id_smt);
            $data['id_guru'] = '';

            // Convert to associative arrays for form_dropdown
            $gurusRaw = $this->master->getAllGuru();
            $gurus = [];
            foreach ($gurusRaw as $g) {
                $gurus[$g->id_guru] = $g->nama_guru;
            }
            $data['gurus'] = $gurus;

            $mapelsRaw = $this->master->getAllMapel();
            $mapel = [];
            foreach ($mapelsRaw as $m) {
                $mapel[$m->id_mapel] = $m->nama_mapel;
            }
            $data['mapel'] = $mapel;
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
                    $idM = is_object($m) ? ($m->id_mapel ?? null) : (is_array($m) ? ($m['id_mapel'] ?? null) : null);
                    if (!$idM) continue;
                    $namaM = is_object($m) ? ($m->nama_mapel ?? null) : (is_array($m) ? ($m['nama_mapel'] ?? null) : null);
                    if (!$namaM) {
                        $namaM = $this->master->getMapelById((int)$idM)?->nama_mapel ?? '-';
                    }
                    $arrMapel[$idM] = $namaM;
                    $kelasMapel = is_object($m) ? ($m->kelas_mapel ?? []) : (is_array($m) ? ($m['kelas_mapel'] ?? []) : []);
                    if (is_array($kelasMapel) || is_object($kelasMapel)) {
                        foreach ($kelasMapel as $kls) {
                            $klsId = is_object($kls) ? ($kls->kelas ?? null) : (is_array($kls) ? ($kls['kelas'] ?? null) : null);
                            if ($klsId) {
                                $arrKelas[$idM][] = [
                                    'id_kelas' => $klsId,
                                    'nama_kelas' => $this->master->getKelasById((int) $klsId)?->nama_kelas ?? '-'
                                ];
                            }
                        }
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
            $firstMapelId = count($arrMapel) > 0 ? (int) array_key_first($arrMapel) : 0;
            if ($firstMapelId > 0) {
                $data['workflow_hint'] = $this->getWorkflowStatusForGuru($firstMapelId);
            }
        }

        return view('cbt/banksoal/add', $data);
    }

    public function checkBankStatus($id_bank)
    {
        try {
            $bank = $this->cbt->getBankSoalById((int) $id_bank);
            if (!$bank) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bank)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }
            if ($this->isBankLockedBySchedule($bank)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal sedang digunakan dalam jadwal ujian aktif. Nonaktifkan jadwal terlebih dahulu untuk mengedit.']);
            }

            $warning = $this->getScheduleWarning($bank);

            return $this->response->setJSON(['status' => true, 'warning' => $warning]);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::checkBankStatus] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    public function editBank($id_bank)
    {
        $id_guru = $this->request->getGet('id_guru');
        $setting = $this->dashboard->getSetting();
        $user = $this->authModel->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Get bank data first to check if it's being used
        $bank = $this->cbt->getBankSoalById((int) $id_bank);

        if (!$bank) {
            return redirect()->to('cbtbanksoal')->with('swal_error', 'Bank soal tidak ditemukan.');
        }
        if (!$this->canAccessBank($bank)) {
            return redirect()->to('cbtbanksoal')->with('swal_error', 'Akses ditolak untuk bank soal ini.');
        }

        // Check if bank is being used in active schedule (digunakan > 0)
        // Admin/panitia tetap boleh mengedit (dengan peringatan di form).
        if ($this->isBankLockedBySchedule($bank)) {
            return redirect()->to('cbtbanksoal')->with('swal_error', 'Bank soal sedang digunakan dalam jadwal ujian aktif. Nonaktifkan jadwal terlebih dahulu untuk mengedit.');
        }

        $bank->bank_kelas = json_decode($bank->bank_kelas ?? '[]');

        // Get kartu soal for current user
        $kartuSoalList = [];
        if ($this->isAdmin() || $this->isPanitia()) {
            $kartuSoalList = $this->getKartuSoalForCurrentScope();
        } else {
            $kartuSoalList = $this->getKartuSoalForCurrentScope((int) $user->id);
        }

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
            'is_admin' => $this->isAdmin() || $this->isPanitia(),
            'bank' => $bank,
            'kartu_soal' => $kartuSoalList,
            'force_kartu_workflow' => $this->inGroup('guru'),
            'workflow_hint' => null,
            'schedule_warning' => $this->getScheduleWarning($bank),
        ];

        if ($this->isAdmin() || $this->isPanitia()) {
            $data['id_guru'] = $id_guru;

            // Convert to associative arrays for form_dropdown
            $gurusRaw = $this->master->getAllGuru();
            $gurus = [];
            foreach ($gurusRaw as $g) {
                $gurus[$g->id_guru] = $g->nama_guru;
            }
            $data['gurus'] = $gurus;

            $mapelsRaw = $this->master->getAllMapel();
            $mapel = [];
            foreach ($mapelsRaw as $m) {
                $mapel[$m->id_mapel] = $m->nama_mapel;
            }
            $data['mapel'] = $mapel;

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
                    $idM = is_object($m) ? ($m->id_mapel ?? null) : (is_array($m) ? ($m['id_mapel'] ?? null) : null);
                    if (!$idM) continue;
                    $namaM = is_object($m) ? ($m->nama_mapel ?? null) : (is_array($m) ? ($m['nama_mapel'] ?? null) : null);
                    if (!$namaM) {
                        $namaM = $this->master->getMapelById((int)$idM)?->nama_mapel ?? '-';
                    }
                    $arrMapel[$idM] = $namaM;
                }
            }

            $data['mapel_guru'] = $mapel_guru;
            $data['mapel'] = $arrMapel;
            $mapelId = (int) ($bank->bank_mapel_id ?? 0);
            if ($mapelId > 0) {
                $data['workflow_hint'] = $this->getWorkflowStatusForGuru($mapelId, (int) ($bank->kartu_soal_id ?? 0));
            }
        }

        return view('cbt/banksoal/add', $data);
    }

    public function saveBank()
    {
        try {
            if ($this->request->getPost()) {
                $tp = $this->dashboard->getTahunActive();
                $smt = $this->dashboard->getSemesterActive();
                $id_bank = $this->request->getPost('id_bank');
                $bankGuruId = (int) $this->request->getPost('guru');
                $mapelId = (int) $this->request->getPost('mapel');
                $jenisId = (int) ($this->request->getPost('jenis') ?? 0);
                $kartuSoalId = (int) ($this->request->getPost('kartu_soal') ?: 0);

                if ($mapelId <= 0) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Mapel wajib dipilih.', 'errors' => ['mapel' => 'Mapel wajib dipilih']]);
                }
                if ($jenisId <= 0) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Jenis ujian wajib dipilih.', 'errors' => ['jenis' => 'Jenis ujian wajib dipilih']]);
                }
                $jenisRow = $this->cbt->getJenisUjianById($jenisId);
                if (!$jenisRow || (string) ($jenisRow->status ?? '1') !== '1') {
                    return $this->response->setJSON(['status' => false, 'message' => 'Jenis ujian tidak valid atau tidak aktif.', 'errors' => ['jenis' => 'Jenis ujian tidak valid']]);
                }

                if (!$this->isAdmin() && !$this->isPanitia()) {
                    $currentGuruId = $this->getCurrentGuruId();
                    if (!$currentGuruId) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan.', 'errors' => ['guru' => 'Data guru tidak ditemukan']]);
                    }
                    $bankGuruId = $currentGuruId;

                    $workflowStatus = $this->getWorkflowStatusForGuru($mapelId, $kartuSoalId > 0 ? $kartuSoalId : null);
                    if (($workflowStatus['tujuan_count'] ?? 0) <= 0) {
                        return $this->response->setJSON([
                            'status' => false,
                            'message' => 'Mapel ini belum memiliki Tujuan Pembelajaran. Lengkapi alur: Tujuan Pembelajaran -> Kartu Soal -> Bank Soal.',
                            'errors' => ['mapel' => 'Tujuan Pembelajaran belum tersedia'],
                            'workflow' => $workflowStatus,
                        ]);
                    }
                    if ($kartuSoalId <= 0) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Pilih Kartu Soal terlebih dahulu sebelum menyimpan bank soal.', 'errors' => ['kartu_soal' => 'Kartu soal wajib dipilih']]);
                    }

                }

                if ($kartuSoalId > 0) {
                    $kartu = $this->kartu->find($kartuSoalId);
                    if (!$kartu) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Kartu Soal tidak ditemukan.', 'errors' => ['kartu_soal' => 'Kartu soal tidak ditemukan']]);
                    }
                    if (!$this->isAdmin() && !$this->isPanitia() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Kartu Soal bukan milik Anda.', 'errors' => ['kartu_soal' => 'Kartu soal tidak dapat diakses']]);
                    }
                    if ((int) ($kartu->mapel_id ?? 0) !== $mapelId) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Mapel pada Kartu Soal harus sama dengan mapel bank soal.', 'errors' => ['kartu_soal' => 'Kartu soal harus sesuai mapel']]);
                    }
                    if ((int) ($kartu->tp_id ?? 0) !== (int) $tp->id_tp || (int) ($kartu->smt_id ?? 0) !== (int) $smt->id_smt) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Periode Kartu Soal harus sama dengan tahun/semester aktif bank soal.', 'errors' => ['kartu_soal' => 'Periode kartu soal tidak sesuai']]);
                    }
                    if ($jenisId > 0 && (int) ($kartu->id_jenis ?? 0) > 0 && (int) ($kartu->id_jenis ?? 0) !== $jenisId) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Jenis ujian pada Kartu Soal harus sama dengan jenis ujian bank soal.', 'errors' => ['kartu_soal' => 'Jenis ujian kartu soal tidak sesuai']]);
                    }
                    $workflowStatus = strtolower(trim((string) ($kartu->workflow_status ?? 'draft')));
                    if ($workflowStatus !== 'approved') {
                        return $this->response->setJSON([
                            'status' => false,
                            'message' => 'Kartu Soal belum disetujui. Bank soal hanya dapat diikat ke Kartu Soal berstatus Approved.',
                            'errors' => ['kartu_soal' => 'Status kartu soal harus Approved'],
                        ]);
                    }

                    $plannedItems = $this->kartuItems->where('kartu_id', $kartuSoalId)->countAllResults();
                    if ($plannedItems <= 0) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Kartu Soal belum memiliki item rencana soal.', 'errors' => ['kartu_soal' => 'Kartu soal belum memiliki item']]);
                    }
                }

                // Validasi: kode bank soal harus unik (kolom bank_kode punya
                // unique index kode_bank_soal). Tanpa cek ini, MySQL melempar
                // Duplicate entry -> 500 + ERROR log setiap kode dipakai ulang.
                $kodeBank = trim((string) $this->request->getPost('kode'));
                if ($kodeBank === '') {
                    return $this->response->setJSON(['status' => false, 'message' => 'Kode bank soal wajib diisi.', 'errors' => ['kode' => 'Kode bank soal wajib diisi']]);
                }

                $cekKode = \Config\Database::connect()
                    ->table('cbt_bank_soal')
                    ->select('id_bank, bank_kode, bank_nama')
                    ->where('bank_kode', $kodeBank)
                    ->when($id_bank, static function ($builder) use ($id_bank) {
                        $builder->where('id_bank !=', (int) $id_bank);
                    })
                    ->get()
                    ->getRow();

                if ($cekKode) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Kode bank soal "' . $kodeBank . '" sudah dipakai bank lain. Gunakan kode yang berbeda.',
                        'errors' => ['kode' => 'Kode bank soal sudah digunakan'],
                    ]);
                }

                // Validasi: Cek jika bank soal sudah digunakan di jadwal aktif
                if ($id_bank) {
                    $bankInfo = $this->cbt->getBankSoalById((int) $id_bank);
                    if (!$bankInfo) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
                    }
                    if (!$this->canAccessBank($bankInfo)) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
                    }
                    if ($bankInfo && $this->isBankLockedBySchedule($bankInfo)) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Bank soal sedang digunakan dalam jadwal ujian aktif. Perubahan ditolak.']);
                    }
                }

                $kelasPost = $this->request->getPost('kelas');
                $arrKelas = [];
                if ($kelasPost) {
                    foreach ($kelasPost as $k) {
                        $arrKelas[] = ['kelas_id' => $k];
                    }
                }
                $subtesCode = (string) ($this->request->getPost('subtes_code') ?: 'GENERAL');
                $subtesMap = [
                    'GENERAL'     => 'General / Reguler',
                    'BIN'         => 'Bahasa Indonesia',
                    'MTK'         => 'Matematika',
                    'BING'        => 'Bahasa Inggris',
                    'FIS'         => 'Fisika',
                    'KIM'         => 'Kimia',
                    'BIO'         => 'Biologi',
                    'GEO'         => 'Geografi',
                    'EKO'         => 'Ekonomi',
                    'SEJ'         => 'Sejarah',
                    'SOS'         => 'Sosiologi',
                    'PPN'         => 'Pendidikan Pancasila',
                    'INF'         => 'Informatika',
                    'KEAHLIAN'    => 'Mapel Keahlian SMK',
                    'VERBAL'      => 'Penalaran Verbal',
                    'KUANTITATIF' => 'Penalaran Kuantitatif',
                    'LOGIS'       => 'Penalaran Logis & Spasial',
                    'LITERASI'    => 'Literasi Membaca',
                    'NUMERASI'    => 'Literasi Numerasi & Data'
                ];
                $subtesNama = $subtesMap[$subtesCode] ?? 'General / Reguler';

                $data = [
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'bank_kode' => $kodeBank,
                    'bank_jenis_id' => $this->request->getPost('jenis'),
                    'subtes_code' => $subtesCode,
                    'subtes_nama' => $subtesNama,
                    'bank_mapel_id' => $mapelId,
                    'bank_kelas' => json_encode($arrKelas),
                    'bank_level' => $this->request->getPost('level'),
                    'bank_guru_id' => $bankGuruId,
                    'kartu_soal_id' => $kartuSoalId > 0 ? $kartuSoalId : null,
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
                    'jml_esai' => $this->request->getPost('tampil_esai'),
                    'bobot_esai' => $this->request->getPost('bobot_esai'),
                    'tampil_esai' => $this->request->getPost('tampil_esai'),
                    'tampil_urutan' => $this->request->getPost('tampil_urutan'),
                    'bobot_urutan' => $this->request->getPost('bobot_urutan'),
                    'tampil_cloze' => $this->request->getPost('tampil_cloze'),
                    'bobot_cloze' => $this->request->getPost('bobot_cloze'),
                    'status' => $this->request->getPost('status') ?? '1',
                    'soal_agama' => $this->request->getPost('soal_agama'),
                    'kkm' => $this->request->getPost('kkm'),
                ];

                try {
                    $result = $this->cbt->saveBankSoal($data, $id_bank ? (int) $id_bank : null);
                } catch (\Throwable $e) {
                    // Balapan dua request dengan kode sama masih bisa menyentuh
                    // unique index; tangani sebagai validasi, bukan error sistem.
                    if (stripos($e->getMessage(), 'Duplicate entry') !== false) {
                        return $this->response->setJSON([
                            'status' => false,
                            'message' => 'Kode bank soal "' . $kodeBank . '" sudah dipakai bank lain. Gunakan kode yang berbeda.',
                            'errors' => ['kode' => 'Kode bank soal sudah digunakan'],
                        ]);
                    }
                    throw $e;
                }

                if ($result) {
                    $targetBankId = $id_bank ? (int) $id_bank : (int) $result;
                    if ($targetBankId > 0) {
                        $db = \Config\Database::connect();
                        $db->table('cbt_soal')
                            ->where('bank_id', $targetBankId)
                            ->update(['subtes_code' => $subtesCode]);
                    }
                }

                if ($id_bank) {
                    $keterangan = 'mengedit bank soal';
                    if ($this->isBankUsedInSchedule($bankInfo ?? null)) {
                        $keterangan .= ' (dipakai jadwal ujian aktif)';
                    }
                    $this->logging->saveLog(4, $keterangan);
                } else {
                    $this->logging->saveLog(3, 'menambah bank soal');
                }

                return $this->response->setJSON(['status' => (bool) $result, 'message' => $result ? 'Berhasil menyimpan bank soal' : 'Gagal menyimpan bank soal']);
            }
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data yang dikirim']);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::saveBank] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    public function deleteBank()
    {
        try {
            $id = (int) $this->request->getPost('id_bank');
            if ($id <= 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'ID bank soal tidak valid.']);
            }

            $bank = $this->cbt->getBankSoalById($id);
            if (!$bank) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bank)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }

            // Simple check for now
            $db = \Config\Database::connect();
            $used = $db->table('cbt_jadwal')->where('id_bank', $id)->countAllResults();

            if ($used > 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal sedang digunakan dalam jadwal ujian.']);
            }

            $db->transBegin();
            // Lepas (bukan hapus) item kartu soal yang menunjuk bank ini sehingga
            // blueprint kisi-kisi tetap terjaga dan tidak menyisakan relasi mati.
            // Item AI-generated (soal_id NULL) tidak tersentuh.
            $db->table('cbt_kartu_soal_item')
                ->where('bank_id', $id)
                ->update(['soal_id' => null, 'bank_id' => null, 'updated_at' => date('Y-m-d H:i:s')]);

            $db->table('cbt_jawaban')->whereIn('id_soal', function ($builder) use ($id) {
                return $builder->select('id_soal')->from('cbt_soal')->where('bank_id', $id);
            })->delete();

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
            log_message('error', '[Cbtbanksoal::deleteBank] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    public function copyBankSoal($id_bank)
    {
        try {
            if ($this->isPanitia()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Panitia tidak diizinkan menyalin Bank Soal']);
            }

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $db = \Config\Database::connect();
            $bank = $db->table('cbt_bank_soal')->where('id_bank', $id_bank)->get()->getRow();
            if (!$bank) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bank)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
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

            $db->transBegin();

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

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyalin bank soal.']);
            }

            $db->transCommit();

            $this->logging->saveLog(3, 'Menyalin bank soal');
            return $this->response->setJSON(['status' => true, 'message' => 'Bank soal berhasil dicopy']);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::copyBankSoal] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
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

    private function getCurrentGuruId(): ?int
    {
        if (!$this->inGroup('guru')) {
            return null;
        }

        $user = $this->authModel->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        if (!$user || !$tp || !$smt) {
            return null;
        }

        $guru = $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);
        return $guru ? (int) $guru->id_guru : null;
    }

    private function canAccessBank($bank): bool
    {
        if (!$bank) {
            return false;
        }

        if ($this->isAdmin() || $this->isPanitia()) {
            return true;
        }

        if (empty($bank->bank_guru_id)) {
            return true;
        }

        $currentGuruId = $this->getCurrentGuruId();
        if ($currentGuruId !== null && (int) ($bank->bank_guru_id ?? 0) === $currentGuruId) {
            return true;
        }

        $user = $this->authModel->user();
        if ($user) {
            $db = \Config\Database::connect();
            $guru = $db->table('master_guru')->where('id_user', $user->id)->get()->getRow();
            if ($guru && (int) $bank->bank_guru_id === (int) $guru->id_guru) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apakah bank soal sedang dipakai oleh jadwal ujian berstatus aktif?
     */
    private function isBankUsedInSchedule(?object $bank): bool
    {
        return $bank && isset($bank->digunakan) && (int) $bank->digunakan > 0;
    }

    /**
     * Kunci edit bank soal akibat dipakai jadwal ujian aktif.
     *
     * Guru (juga Panitia non-admin) tetap terkunci: mereka wajib
     * menonaktifkan/menunda jadwal lebih dulu agar soal yang dipakai
     * peserta yang sedang ujian tidak berubah di tengah jalan.
     *
     * Administrator TIDAK dikunci. Tugas admin justru memperbaiki/menata
     * bank soal, jadi admin harus tetap bisa mengedit walaupun bank soal
     * tersebut sudah dipakai jadwal ujian aktif (dengan peringatan).
     */
    private function isBankLockedBySchedule(?object $bank): bool
    {
        if (!$this->isBankUsedInSchedule($bank)) {
            return false;
        }

        return !($this->isAdmin() || $this->isPanitia());
    }

    /**
     * Peringatan untuk admin/panitia saat bank soal dipakai jadwal ujian aktif.
     */
    private function getScheduleWarning(?object $bank): ?string
    {
        if (!$this->isBankUsedInSchedule($bank)) {
            return null;
        }

        return 'Bank soal ini sedang dipakai pada jadwal ujian aktif. Perubahan akan langsung berlaku pada peserta yang sedang ujian.';
    }

    private function getCurrentGuru(): ?object
    {
        if (!$this->inGroup('guru')) {
            return null;
        }

        $user = $this->authModel->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        if (!$user || !$tp || !$smt) {
            return null;
        }

        return $this->dashboard->getDataGuruByUserId($user->id, (int) $tp->id_tp, (int) $smt->id_smt);
    }

    private function getKartuSoalForCurrentScope(?int $createdByUserId = null): array
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        if (!$tp || !$smt) {
            return [];
        }

        $builder = $this->kartu
            ->select('cbt_kartu_soal.*, j.nama_jenis')
            ->join('cbt_jenis j', 'j.id_jenis = cbt_kartu_soal.id_jenis', 'left')
            ->where('cbt_kartu_soal.tp_id', (int) $tp->id_tp)
            ->where('cbt_kartu_soal.smt_id', (int) $smt->id_smt)
            ->where('cbt_kartu_soal.deleted_at', null)
            ->where('cbt_kartu_soal.workflow_status', 'approved');

        if ($createdByUserId !== null) {
            $builder->where('cbt_kartu_soal.created_by', $createdByUserId);
        }

        return $builder->orderBy('cbt_kartu_soal.updated_at', 'DESC')->findAll() ?? [];
    }

    private function getWorkflowStatusForGuru(int $mapelId, ?int $kartuId = null): array
    {
        $ctx = $this->cbtService->getGuruContext((int) $this->getUserId());
        if (!$ctx['tp'] || !$ctx['smt'] || !$ctx['guru']) {
            return [
                'ready' => false,
                'tujuan_count' => 0,
                'kartu_count' => 0,
                'kartu_approved_count' => 0,
                'bank_count' => 0,
                'soal_count' => 0,
                'message' => 'Data guru/tahun/semester tidak lengkap.',
            ];
        }

        return $this->cbtService->getWorkflowStatus(
            (int) $this->getUserId(),
            (int) $ctx['guru']->id_guru,
            $mapelId,
            (int) $ctx['tp']->id_tp,
            (int) $ctx['smt']->id_smt,
            $kartuId
        );
    }

    private function validateBankWorkflowForGuru(?object $bankInfo, ?int $jenis = null): array
    {
        if (!$this->inGroup('guru')) {
            return [true, 'ok'];
        }

        if (!$bankInfo) {
            return [false, 'Bank soal tidak ditemukan.'];
        }

        $mapelId = (int) ($bankInfo->bank_mapel_id ?? 0);
        $status = $this->getWorkflowStatusForGuru($mapelId);
        if (!$status['ready']) {
            return [false, 'Tujuan Pembelajaran untuk mapel ini belum tersedia. Ikuti alur: Tujuan Pembelajaran -> Kartu Soal -> Bank Soal.'];
        }

        $kartuId = (int) ($bankInfo->kartu_soal_id ?? 0);
        if ($kartuId <= 0) {
            return [false, 'Bank soal belum terhubung ke Kartu Soal. Hubungkan dulu kartu soal yang valid.'];
        }

        $kartu = $this->kartu->find($kartuId);
        if (!$kartu || (int) ($kartu->mapel_id ?? 0) !== $mapelId) {
            return [false, 'Kartu soal tidak valid atau tidak sesuai mapel bank soal.'];
        }
        $workflowStatus = strtolower(trim((string) ($kartu->workflow_status ?? 'draft')));
        if ($workflowStatus !== 'approved') {
            return [false, 'Kartu soal belum berstatus Approved. Ajukan approval terlebih dahulu.'];
        }
        if ((int) ($kartu->tp_id ?? 0) !== (int) ($bankInfo->id_tp ?? 0) || (int) ($kartu->smt_id ?? 0) !== (int) ($bankInfo->id_smt ?? 0)) {
            return [false, 'Periode Kartu Soal tidak sesuai dengan periode bank soal.'];
        }
        if ((int) ($bankInfo->bank_jenis_id ?? 0) > 0 && (int) ($kartu->id_jenis ?? 0) > 0 && (int) ($kartu->id_jenis ?? 0) !== (int) ($bankInfo->bank_jenis_id ?? 0)) {
            return [false, 'Jenis ujian pada Kartu Soal tidak sesuai dengan bank soal.'];
        }
        if ((int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return [false, 'Kartu soal bukan milik Anda.'];
        }

        $totalItems = $this->kartuItems->where('kartu_id', $kartuId)->countAllResults();
        if ($totalItems <= 0) {
            return [false, 'Kartu soal belum memiliki item rencana soal. Lengkapi item berdasarkan Tujuan Pembelajaran.'];
        }

        $linkedTujuan = $this->kartuItems->where('kartu_id', $kartuId)
            ->where('id_tujuan IS NOT NULL', null, false)
            ->countAllResults();
        if ($linkedTujuan <= 0) {
            return [false, 'Item kartu soal belum terhubung ke Tujuan Pembelajaran.'];
        }

        if ($jenis !== null) {
            $plannedJenis = $this->kartuItems->where('kartu_id', $kartuId)
                ->where('jenis_soal', (int) $jenis)
                ->countAllResults();
            if ($plannedJenis <= 0) {
                return [false, 'Jenis soal ini belum direncanakan di Kartu Soal. Tambahkan dulu item jenis yang sesuai.'];
            }
        }

        return [true, 'ok'];
    }

    private function getPlannedQuotaByJenis(?object $bankInfo, int $jenis): array
    {
        if (!$this->inGroup('guru') || !$bankInfo || $jenis <= 0) {
            return ['enforced' => false, 'planned' => 0, 'existing' => 0, 'remaining' => 0];
        }

        $kartuId = (int) ($bankInfo->kartu_soal_id ?? 0);
        if ($kartuId <= 0) {
            return ['enforced' => false, 'planned' => 0, 'existing' => 0, 'remaining' => 0];
        }

        $planned = $this->kartuItems->where('kartu_id', $kartuId)
            ->where('jenis_soal', $jenis)
            ->countAllResults();
        $existing = \Config\Database::connect()->table('cbt_soal')
            ->where('bank_id', (int) ($bankInfo->id_bank ?? 0))
            ->where('jenis', $jenis)
            ->countAllResults();

        return [
            'enforced' => true,
            'planned' => (int) $planned,
            'existing' => (int) $existing,
            'remaining' => max(0, (int) $planned - (int) $existing),
        ];
    }

    private function ensurePlannedQuotaAvailable(?object $bankInfo, int $jenis, int $requested = 1): array
    {
        $quota = $this->getPlannedQuotaByJenis($bankInfo, $jenis);
        if (!$quota['enforced']) {
            return [true, 'ok', $quota];
        }

        if ((int) $quota['planned'] <= 0) {
            return [false, 'Jenis soal ini belum memiliki item rencana pada Kartu Soal.', $quota];
        }

        if ((int) $quota['remaining'] < $requested) {
            return [
                false,
                'Kuota soal jenis ini sudah penuh sesuai Kartu Soal (rencana: ' . (int) $quota['planned'] . ', terisi: ' . (int) $quota['existing'] . ').',
                $quota,
            ];
        }

        return [true, 'ok', $quota];
    }

    private function getBankById(int $idBank): ?object
    {
        if ($idBank <= 0) {
            return null;
        }
        return $this->cbt->getBankSoalById($idBank);
    }

    private function subtesOptions(): array
    {
        return [
            'GENERAL'   => 'General / Mata Pelajaran Reguler',
            'BIN'       => 'Bahasa Indonesia',
            'MTK'       => 'Matematika',
            'BING'      => 'Bahasa Inggris',
            'FIS'       => 'Fisika',
            'KIM'       => 'Kimia',
            'BIO'       => 'Biologi',
            'GEO'       => 'Geografi',
            'EKO'       => 'Ekonomi',
            'SEJ'       => 'Sejarah',
            'SOS'       => 'Sosiologi',
            'PPN'       => 'Pendidikan Pancasila',
            'INF'       => 'Informatika',
            'KEAHLIAN'  => 'Mapel Keahlian SMK',
            'VERBAL'    => 'Penalaran Verbal',
            'KUANTITATIF' => 'Penalaran Kuantitatif',
            'LOGIS'     => 'Penalaran Logis & Spasial',
            'LITERASI'  => 'Literasi Membaca',
            'NUMERASI'  => 'Literasi Numerasi & Data',
        ];
    }

    private function canAccessBankId(int $idBank): bool
    {
        $bank = $this->getBankById($idBank);
        return $this->canAccessBank($bank);
    }

    private function getSoalById(int $idSoal): ?object
    {
        if ($idSoal <= 0) {
            return null;
        }
        return \Config\Database::connect()
            ->table('cbt_soal')
            ->where('id_soal', $idSoal)
            ->get()
            ->getRow();
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
            if ($this->isPanitia()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses Terlarang']);
            }

            $ids = $this->request->getPost('checked');
            if (!$ids || !is_array($ids)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada bank soal yang dipilih.']);
            }

            $ids = array_map('intval', $ids);
            if (!$this->isAdmin() && !$this->isPanitia()) {
                foreach ($ids as $idBank) {
                    if (!$this->canAccessBankId($idBank)) {
                        return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk salah satu bank soal.']);
                    }
                }
            }

            $db = \Config\Database::connect();
            $used_count = $db->table('cbt_jadwal')->whereIn('id_bank', $ids)->countAllResults();
            if ($used_count > 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Beberapa bank soal sedang digunakan dalam jadwal ujian dan tidak dapat dihapus.']);
            }

            $db->transBegin();

            // 1. Unlink from Kartu Soal Items (matches logic in individual hapusSoal)
            // This prevents foreign key constraints or data corruption in Kartu Soal
            $db->table('cbt_kartu_soal_item')->whereIn('bank_id', $ids)->update(['soal_id' => null, 'bank_id' => null]);


            $db->table('cbt_jawaban')->whereIn('id_soal', function ($builder) use ($ids) {
                return $builder->select('id_soal')->from('cbt_soal')->whereIn('bank_id', $ids);
            })->delete();

            // 3. Delete the questions and bank records
            $db->table('cbt_soal')->whereIn('bank_id', $ids)->delete();
            $db->table('cbt_bank_soal')->whereIn('id_bank', $ids)->delete();

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus beberapa bank soal.']);
            }

            $db->transCommit();
            $this->logging->saveLog(5, 'menghapus beberapa bank soal');
            return $this->response->setJSON(['status' => true, 'message' => count($ids) . ' Bank soal berhasil dihapus.']);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::deleteAllBank] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    public function import($id)
    {
        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $bank = $this->cbt->getBankSoalById((int) $id);

        if (!$bank) {
            return redirect()->to('cbtbanksoal')->with('error', 'Bank soal tidak ditemukan');
        }
        if (!$this->canAccessBank($bank)) {
            return redirect()->to('cbtbanksoal')->with('error', 'Akses ditolak untuk bank soal ini.');
        }

        $data = [
            'user' => $user,
            'judul' => 'Import Bank Soal',
            'subjudul' => 'Import Bank Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'bank' => $bank
        ];

        return view('cbt/banksoal/import', $data);
    }

    public function uploadSoal()
    {
        try {
            $bank_id = (int) $this->request->getPost('id_bank');
            $datas = $this->request->getPost('soal');
            $bank = $this->cbt->getBankSoalById((int) $bank_id);
            if (!$bank) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bank)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }
            [$flowOk, $flowMsg] = $this->validateBankWorkflowForGuru($bank);
            if (!$flowOk) {
                return $this->response->setJSON(['status' => false, 'message' => $flowMsg]);
            }
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
                        'kesulitan' => 'C4',
                        'timer' => '0',
                        'timer_menit' => '0',
                        'file' => $dins['file'],
                        'tujuan_id' => !empty($dins['tujuan_id']) ? (int) $dins['tujuan_id'] : null,
                        'created_on' => time(),
                        'updated_on' => time(),
                        'opsi_a' => $dins['opsi_a'] ?? '',
                        'opsi_b' => $dins['opsi_b'] ?? '',
                        'opsi_c' => $dins['opsi_c'] ?? '',
                        'opsi_d' => $dins['opsi_d'] ?? '',
                        'opsi_e' => $dins['opsi_e'] ?? '',
                        'jawaban' => $dins['jawaban'] ?? '',
                        'subtes_code' => $bank->subtes_code ?? 'GENERAL',
                        'bobot_irt'   => 1.0,
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
            log_message('error', '[Cbtbanksoal::uploadSoal] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    public function uploadSoalImage()
    {
        try {
            $name = $this->request->getPost('name');
            // Security: Sanitize filename to prevent path traversal
            $name = basename($name ?? '');

            $src = $this->request->getPost('src');
            $src = str_replace('%2B', '+', $src ?? '');

            // Validate extension
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                return $this->response->setJSON(['status' => false, 'message' => 'Invalid file extension']);
            }

            $filePath = ROOTPATH . 'public/uploads/bank_soal/' . $name;

            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            file_put_contents($filePath, base64_decode($src));

            // Auto Compress & Watermark
            $this->processImage($filePath);

            return $this->response->setJSON(['status' => true, 'src' => 'uploads/bank_soal/' . $name]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    private function processImage($path)
    {
        try {
            $image    = \Config\Services::image();
            $fontPath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR . 'Roboto-Bold.ttf';

            $image->withFile($path)
                ->resize(800, 800, true, 'height');

            $options = [
                'color' => '#FFFF00', // Yellow for visibility
                'opacity' => 1, // Full opacity
                'withShadow' => true,
                'shadowColor' => '#000000',
                'hAlign' => 'right',
                'vAlign' => 'bottom',
                'hOffset' => 20,
                'vOffset' => 20,
            ];

            if (file_exists($fontPath)) {
                $options['fontSize'] = 40;
                $options['fontPath'] = $fontPath;
            } else {
                $options['fontSize'] = 5;
            }

            $image->text('IKASMEDIA', $options)
                ->save($path, 80); // Higher quality
        } catch (\Throwable $th) {
            log_message('error', 'Image processing failed: ' . $th->getMessage());
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

                            // Auto Compress & Watermark
                            $this->processImage($path);

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

    public function resetNumber()
    {
        $ids_str = $this->request->getPost('soal_id');
        $ids = explode(',', $ids_str ?? '');
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data soal tidak valid.']);
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
            foreach ($ids as $idSoal) {
                $soal = $this->getSoalById($idSoal);
                if (!$soal || !$this->canAccessBankId((int) ($soal->bank_id ?? 0))) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk salah satu soal.']);
                }
            }
        }

        $db = \Config\Database::connect();

        $success = 0;
        foreach ($ids as $index => $id) {
            if ($id) {
                // Must create new builder or reset? method chaining returns builder. 
                // Better use db->table in loop or reset logic.
                $db->table('cbt_soal')->where('id_soal', $id)->update(['nomor_soal' => $index + 1]);
                $success++;
            }
        }
        return $this->response->setJSON(['status' => $success > 0]);
    }

    public function get_detail()
    {
        $id = (int) $this->request->getPost('id_bank');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $bank = $this->cbt->getBankSoalById((int) $id);
        if (!$bank) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bank soal tidak ditemukan.']);
        }
        if (!$this->canAccessBank($bank)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
        }
        
        [$flowOk, $flowMsg] = $this->validateBankWorkflowForGuru($bank);
        if (!$flowOk) {
            return $this->response->setJSON(['success' => false, 'message' => $flowMsg]);
        }
        if ($bank && isset($bank->bank_kelas)) {
            $bank->bank_kelas = $this->maybeUnserialize($bank->bank_kelas);
        }
        $setting = $this->dashboard->getSetting();

        $data['bank'] = $bank;
        $soals = $this->cbt->getAllSoalByBank((int) $id);
        foreach ($soals as $soal) {
            if ($soal->jenis == '2') {
                $soal->opsi_a = $this->maybeUnserialize($soal->opsi_a);
                $soal->jawaban = $this->maybeUnserialize($soal->jawaban);
            } elseif ($soal->jenis == '3') {
                $soal->jawaban = $this->maybeUnserialize($soal->jawaban);
            } elseif ($soal->jenis == '6') {
                $soal->opsi_a = $this->maybeUnserialize($soal->opsi_a);
                $soal->jawaban = $this->maybeUnserialize($soal->jawaban);
            } elseif ($soal->jenis == '7') {
                $soal->jawaban = $this->maybeUnserialize($soal->jawaban);
            }
        }
        $data['soals'] = $soals;
        $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
        $data['setting'] = $setting;
        $data['tp'] = $tp;
        $data['smt'] = $smt;

        $terpakaiCount = $this->cbt->countSiswaTerpakai($id);
        $data['total_siswa'] = $terpakaiCount;

        // NEW: Auto-sync bank status saat get_detail agar database ter-update jika bank sebenarnya sudah 100% lengkap
        // OPTIMASI: Hanya lakukan pengecekan ke database jika status aslinya masih 0 (belum selesai).
        // Jika sudah 1 (selesai), tidak perlu cek ulang (berat di server jika diloop terus).
        if (!isset($bank->status_soal) || $bank->status_soal === '0') {
            $syncResult = $this->syncBankStatus((int) $id);
            $bank->status_soal = $syncResult['newStatus'];
            $bank->status = $syncResult['newStatus'];
        }

        $data['success'] = true;

        return $this->response->setJSON($data);
    }

    public function detail($id)
    {
        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $bank = $this->cbt->getBankSoalById((int) $id);
        if (!$bank) {
            return redirect()->to('cbtbanksoal')->with('error', 'Bank soal tidak ditemukan');
        }
        if (!$this->canAccessBank($bank)) {
            return redirect()->to('cbtbanksoal')->with('error', 'Akses ditolak untuk bank soal ini.');
        }

        $data = [
            'user' => $user,
            'judul' => 'Detail Soal',
            'subjudul' => 'Detail Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'id_bank' => $id,
            'bank_kode' => $bank->bank_kode ?? '',
            'bank_nama' => $bank->bank_nama ?? '',
        ];

        return view('cbt/banksoal/detail', $data);
    }

    /**
     * Buat/Edit Soal view
     */
    public function buatsoal($id_bank)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $_no = $this->request->getGet('no');
        $_jns = $this->request->getGet('jns');
        $tab = $this->request->getGet('tab');

        $act_tab = $_jns ?? '1';
        $jenis = $tab ?? $act_tab;

        $bank = $this->cbt->getBankSoalById((int) $id_bank);
        if (!$bank) {
            return redirect()->to('cbtbanksoal')->with('error', 'Bank soal tidak ditemukan');
        }
        if (!$this->canAccessBank($bank)) {
            return redirect()->to('cbtbanksoal')->with('error', 'Akses ditolak untuk bank soal ini.');
        }
        [$flowOk, $flowMsg] = $this->validateBankWorkflowForGuru($bank);
        if (!$flowOk) {
            return redirect()->to('cbtbanksoal/edit/' . (int) $id_bank)->with('error', $flowMsg);
        }

        // Auto-repair: fix gaps in nomor_soal for all jenis in this bank
        $this->repairNomorSoal((int) $id_bank);

        $data = [
            'user' => $user,
            'judul' => 'Buat Soal',
            'subjudul' => 'Buat Soal',
            'setting' => $setting,
            'is_admin' => $this->isAdmin(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'p_no' => $_no ?? '1',
            'p_jns' => $act_tab,
            'tab_active' => $jenis,
            'bank' => $bank,
            'soal' => null,
            'soal_ada' => $this->cbt->cekSoalAda((int) $id_bank, (int) $jenis),
            'soal_belum_komplit' => $this->cbt->cekSoalBelumKomplit((int) $jenis, (int) ($bank->opsi ?? 5), (int) $id_bank),
            'soals' => $this->cbt->getAllSoalByBank((int) $id_bank, (int) $jenis),
        ];

        // Get largest question number for each type
        switch ($jenis) {
            case '1':
                $data['jml_pg'] = $this->cbt->getNomorSoalTerbesar((int) $id_bank, 1);
                break;
            case '2':
                $data['jml_pg2'] = $this->cbt->getNomorSoalTerbesar((int) $id_bank, 2);
                break;
            case '3':
                $data['jml_jodohkan'] = $this->cbt->getNomorSoalTerbesar((int) $id_bank, 3);
                break;
            case '4':
                $data['jml_isian'] = $this->cbt->getNomorSoalTerbesar((int) $id_bank, 4);
                break;
            case '5':
                $data['jml_essai'] = $this->cbt->getNomorSoalTerbesar((int) $id_bank, 5);
                break;
            case '6':
                $data['jml_urutan'] = $this->cbt->getNomorSoalTerbesar((int) $id_bank, 6);
                break;
            case '7':
                $data['jml_cloze'] = $this->cbt->getNomorSoalTerbesar((int) $id_bank, 7);
                break;
        }

        $kartuPlanByJenis = [];
        $kartuTujuanByJenis = [];
        $kartuQuotaByJenis = [];
        $jenisLabelMap = [
            1 => 'Pilihan Ganda',
            2 => 'PG Kompleks',
            3 => 'Menjodohkan',
            4 => 'Isian Singkat',
            5 => 'Essai',
            6 => 'Urutan',
            7 => 'Cloze',
        ];
        for ($jenisSoal = 1; $jenisSoal <= 7; $jenisSoal++) {
            $kartuPlanByJenis[$jenisSoal] = [];
            $kartuTujuanByJenis[$jenisSoal] = [];
            $kartuQuotaByJenis[$jenisSoal] = ['planned' => 0, 'existing' => 0, 'remaining' => 0];
        }

        $kartuId = (int) ($bank->kartu_soal_id ?? 0);
        if ($kartuId > 0) {
            $db = \Config\Database::connect();
            $plannedItems = $db->table('cbt_kartu_soal_item a')
                ->select('a.*, b.kode as tujuan_kode, b.tujuan as tujuan_full')
                ->join('e_tujuan_pembelajaran b', 'a.id_tujuan = b.id_tujuan', 'left')
                ->where('a.kartu_id', $kartuId)
                ->orderBy('a.nomor', 'ASC')
                ->get()->getResult();

            $existingByJenis = [];
            $existingRows = \Config\Database::connect()->table('cbt_soal')
                ->select('jenis, COUNT(*) AS total')
                ->where('bank_id', (int) $id_bank)
                ->groupBy('jenis')
                ->get()
                ->getResult();
            foreach ($existingRows as $row) {
                $existingByJenis[(int) ($row->jenis ?? 0)] = (int) ($row->total ?? 0);
            }

            $tujuanKeysByJenis = [];
            foreach ($plannedItems as $item) {
                $jenisKey = (int) ($item->jenis_soal ?? 0);
                if ($jenisKey < 1 || $jenisKey > 7) {
                    continue;
                }

                $kartuPlanByJenis[$jenisKey][] = [
                    'nomor' => (int) ($item->nomor ?? 0),
                    'id_item' => (int) ($item->id_item ?? 0),
                    'id_tujuan' => (int) ($item->id_tujuan ?? 0),
                    'tujuan_kode' => (string) ($item->tujuan_kode ?? ''),
                    'tujuan' => (string) ($item->tujuan_full ?? ($item->tujuan ?? '')),
                    'cp' => (string) ($item->cp ?? ''),
                    'materi' => (string) ($item->materi ?? ''),
                    'indikator_soal' => (string) ($item->indikator_soal ?? ''),
                    'level_kognitif' => (string) ($item->level_kognitif ?? ''),
                    'bentuk_soal' => (string) ($item->bentuk_soal ?? ''),
                    'estimasi_waktu' => (int) ($item->estimasi_waktu ?? 0),
                    'catatan' => (string) ($item->catatan ?? ''),
                ];

                $tujuanText = (string) ($item->tujuan_full ?? ($item->tujuan ?? ''));
                $tujuanKode = (string) ($item->tujuan_kode ?? '');

                if ($tujuanText === '') {
                    continue;
                }

                $tujuanId = (int) ($item->id_tujuan ?? 0);
                $tujuanKey = $tujuanId > 0 ? ('id:' . $tujuanId) : ('txt:' . mb_strtolower($tujuanText));
                if (!isset($tujuanKeysByJenis[$jenisKey])) {
                    $tujuanKeysByJenis[$jenisKey] = [];
                }
                if (in_array($tujuanKey, $tujuanKeysByJenis[$jenisKey], true)) {
                    continue;
                }
                $tujuanKeysByJenis[$jenisKey][] = $tujuanKey;

                $kartuTujuanByJenis[$jenisKey][] = [
                    'id_tujuan' => $tujuanId,
                    'kode' => $tujuanKode,
                    'tujuan' => $tujuanText,
                    'fase' => '',
                    'tingkat' => '',
                    'nama_mapel' => (string) ($bank->nama_mapel ?? ''),
                    'jenis_soal' => $jenisKey,
                    'jenis_label' => (string) ($jenisLabelMap[$jenisKey] ?? ''),
                ];
            }

            for ($jenisSoal = 1; $jenisSoal <= 7; $jenisSoal++) {
                $plannedCount = count($kartuPlanByJenis[$jenisSoal]);
                $existingCount = (int) ($existingByJenis[$jenisSoal] ?? 0);
                $kartuQuotaByJenis[$jenisSoal] = [
                    'planned' => $plannedCount,
                    'existing' => $existingCount,
                    'remaining' => max(0, $plannedCount - $existingCount),
                ];
            }
        }

        $aiTujuanOptions = [];
        for ($jenisSoal = 1; $jenisSoal <= 7; $jenisSoal++) {
            foreach ($kartuTujuanByJenis[$jenisSoal] as $tpItem) {
                $tpItem['source'] = 'kartu';
                $aiTujuanOptions[] = $tpItem;
            }
        }

        if (empty($aiTujuanOptions)) {
            foreach (($data['tujuan_list'] ?? []) as $tpItem) {
                $aiTujuanOptions[] = [
                    'id_tujuan' => (int) ($tpItem->id_tujuan ?? 0),
                    'kode' => (string) ($tpItem->kode ?? ''),
                    'tujuan' => (string) ($tpItem->tujuan ?? ''),
                    'fase' => (string) ($tpItem->fase ?? ''),
                    'tingkat' => (string) ($tpItem->tingkat ?? ''),
                    'nama_mapel' => (string) ($tpItem->nama_mapel ?? ''),
                    'jenis_soal' => 0,
                    'jenis_label' => 'Semua Tipe',
                    'source' => 'mapel',
                ];
            }
        }

        if ($kartuId > 0 && isset($kartuTujuanByJenis[$jenis])) {
            $allowedTujuanIds = array_column($kartuTujuanByJenis[$jenis], 'id_tujuan');
            $filteredTujuan = [];
            foreach (($data['tujuan_list'] ?? []) as $t) {
                if (in_array((int)($t->id_tujuan ?? 0), $allowedTujuanIds, true)) {
                    $filteredTujuan[] = $t;
                }
            }
            $data['tujuan_list'] = $filteredTujuan;
        }

        $data['kartu_plan_by_jenis'] = $kartuPlanByJenis;
        $data['kartu_quota_by_jenis'] = $kartuQuotaByJenis;
        $data['ai_tujuan_options'] = $aiTujuanOptions;

        return view('cbt/banksoal/soal', $data);
    }

    /**
     * Get soal by nomor via AJAX
     */
    public function getSoalByNomor()
    {
        try {
            $bank_id = $this->request->getGet('bank_id');
            $nomor = $this->request->getGet('nomor');
            $jenis = $this->request->getGet('jenis');
            $bankInfo = $this->cbt->getBankSoalById((int) $bank_id);
            if (!$bankInfo) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bankInfo)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }

            $soal = $this->cbt->getSoalByNomor((int) $bank_id, (int) $nomor, (int) $jenis);
            $data = $soal;

            if ($data != null) {
                $data->file = $this->maybeUnserialize($soal->file ?? '');

                if ($jenis == '2') {
                    $t = $this->maybeUnserialize($soal->opsi_a ?? '');
                    if (is_string($t) && (strpos($t, '{') === 0 || strpos($t, '[') === 0)) {
                        $json = json_decode($t);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $t = $json;
                        }
                    }
                    $data->opsi_a = $t !== false ? $t : false;
                    $data->opsi = $data->opsi_a;

                    $j = $this->maybeUnserialize($soal->jawaban ?? '');
                    if (is_string($j) && (strpos($j, '[') === 0 || strpos($j, '{') === 0)) {
                        $jsonJ = json_decode($j);
                        if (json_last_error() === JSON_ERROR_NONE)
                            $j = $jsonJ;
                    }
                    $data->jawaban = $j !== false ? $j : false;
                } elseif ($jenis == '3') {
                    $rawJawaban = $soal->jawaban ?? '';
                    // Try JSON decode first
                    if (is_string($rawJawaban) && (strpos($rawJawaban, '{') === 0 || strpos($rawJawaban, '[') === 0)) {
                        $decoded = json_decode($rawJawaban, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $data->jawaban = $decoded;
                        } else {
                            // Fallback: tryunserialize
                            $j = $this->maybeUnserialize($rawJawaban);
                            if ($j !== false) {
                                // Convert to array if stdObject
                                $data->jawaban = json_decode(json_encode($j), true);
                            } else {
                                $data->jawaban = null;
                            }
                        }
                    } else {
                        $data->jawaban = null;
                    }
                } elseif ($jenis == '6' || $jenis == '7') {
                    $opsi = $soal->opsi_a ?? '';
                    if (is_string($opsi) && (strpos($opsi, '{') === 0 || strpos($opsi, '[') === 0)) {
                        $data->opsi_a = json_decode($opsi);
                        $data->soal_opsi = $data->opsi_a;
                    }
                    $j = $soal->jawaban ?? '';
                    if (is_string($j) && (strpos($j, '{') === 0 || strpos($j, '[') === 0)) {
                        $data->jawaban = json_decode($j);
                    }
                }
            } else {
                // If not found in DB, return null so frontend can handle as new question
                $data = null;
            }

            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::getSoalByNomor] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Tambah soal baru (create empty slot)
     */
    public function tambahSoal()
    {
        try {
            $bank = (int) $this->request->getPost('bank');
            $nomor = $this->request->getPost('nomor');
            $jenis = $this->request->getPost('jenis');
            $bankInfo = $this->cbt->getBankSoalById($bank);
            if (!$bankInfo) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bankInfo)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }
            [$flowOk, $flowMsg] = $this->validateBankWorkflowForGuru($bankInfo, (int) $jenis);
            if (!$flowOk) {
                return $this->response->setJSON(['status' => false, 'message' => $flowMsg]);
            }
            [$quotaOk, $quotaMsg] = $this->ensurePlannedQuotaAvailable($bankInfo, (int) $jenis, 1);
            if (!$quotaOk) {
                return $this->response->setJSON(['status' => false, 'message' => $quotaMsg]);
            }

            // Validasi: Cek deadline
            [$deadlineOk, $deadlineMsg] = $this->validateDeadline();
            if (!$deadlineOk) {
                log_message('error', 'tambahSoal Validation Failed: deadlineOk false - ' . $deadlineMsg);
                return $this->response->setJSON(['status' => false, 'message' => $deadlineMsg]);
            }

            $data = [
                'bank_id'     => $bank,
                'nomor_soal'  => $nomor,
                'jenis'       => $jenis,
                'tipe_soal'   => (int) $jenis === 2 ? 'pgk' : 'pg',
                'subtes_code' => $bankInfo->subtes_code ?? 'GENERAL',
                'bobot_irt'   => 1.0,
                'tampilkan'   => 0,
                'created_on'  => time(),
                'updated_on'  => time()
            ];

            $db = \Config\Database::connect();

            // AMBIL DATA METADATA DARI KARTU SOAL & LINK OTOMATIS
            $kartuItemId = 0;
            if (!empty($bankInfo->kartu_soal_id)) {
                $kartuItem = $db->table('cbt_kartu_soal_item')
                    ->where('kartu_id', $bankInfo->kartu_soal_id)
                    ->where('nomor', $nomor)
                    ->where('jenis_soal', $jenis)
                    ->get()
                    ->getRowArray();
                
                if ($kartuItem) {
                    $kartuItemId = (int) ($kartuItem['id_item'] ?? 0);
                    // Ambil metadata dari kartu item
                    $data['kesulitan'] = $kartuItem['tingkat_kesulitan'] ?? 'C4';
                    $data['tujuan_id'] = !empty($kartuItem['id_tujuan']) ? (int) $kartuItem['id_tujuan'] : null;
                }
            }

            $insert = $db->table('cbt_soal')->insert($data);

            if ($insert) {
                $insertedId = (int) $db->insertID();
                
                // Jika item kartu soal ditemukan, update otomatis linkage ID soalnya
                if ($kartuItemId > 0) {
                    $db->table('cbt_kartu_soal_item')
                        ->where('id_item', $kartuItemId)
                        ->update([
                            'soal_id'    => $insertedId,
                            'bank_id'    => $bank,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                }

                $this->logging->saveLog(3, 'menambah soal nomor: ' . $nomor . ', jenis: ' . $jenis);
            }

            return $this->response->setJSON(['status' => $insert]);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::tambahSoal] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Buat butir soal baru dari item kisi-kisi (kartu soal) yang dipilih,
     * tanpa AI. Membuat slot soal kosong di bank & otomatis menautkan
     * ke cbt_kartu_soal_item agar metadata (tujuan/kesulitan) ikut terbawa.
     *
     * Dipanggil tombol "Buat Butir" di modal pilih kisi-kisi (soal.php).
     */
    public function generateSoalFromKartu()
    {
        try {
            $bankId = (int) $this->request->getPost('bank_id');
            $itemId = (int) $this->request->getPost('item_id');
            $jenis  = (int) $this->request->getPost('jenis');

            if ($bankId <= 0 || $itemId <= 0 || $jenis < 1) {
                return $this->response->setJSON(['status' => false, 'message' => 'Parameter tidak valid.']);
            }

            $bankInfo = $this->cbt->getBankSoalById($bankId);
            if (!$bankInfo) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bankInfo)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }
            [$flowOk, $flowMsg] = $this->validateBankWorkflowForGuru($bankInfo, $jenis);
            if (!$flowOk) {
                return $this->response->setJSON(['status' => false, 'message' => $flowMsg]);
            }
            [$quotaOk, $quotaMsg] = $this->ensurePlannedQuotaAvailable($bankInfo, $jenis, 1);
            if (!$quotaOk) {
                return $this->response->setJSON(['status' => false, 'message' => $quotaMsg]);
            }

            // Validasi: Cek deadline (konsisten dengan tambahSoal)
            [$deadlineOk, $deadlineMsg] = $this->validateDeadline();
            if (!$deadlineOk) {
                return $this->response->setJSON(['status' => false, 'message' => $deadlineMsg]);
            }

            $db = \Config\Database::connect();

            // Validasi item kisi-kisi & pastikan belum punya butir soal
            $item = $db->table('cbt_kartu_soal_item')
                ->where('id_item', $itemId)
                ->get()->getRow();
            if (!$item) {
                return $this->response->setJSON(['status' => false, 'message' => 'Item kisi-kisi tidak ditemukan.']);
            }

            // Validasi silang kartu <-> bank: mapel harus sama, dan jika bank
            // sudah terikat ke satu kartu (kartu_soal_id), item harus dari kartu itu.
            $kartuItem = $db->table('cbt_kartu_soal')
                ->where('id_kartu', (int) ($item->kartu_id ?? 0))
                ->get()->getRow();
            if (!$kartuItem) {
                return $this->response->setJSON(['status' => false, 'message' => 'Kartu soal induk item tidak ditemukan.']);
            }
            if ((int) ($kartuItem->mapel_id ?? 0) !== (int) ($bankInfo->bank_mapel_id ?? 0)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Mapel kartu soal tidak sama dengan mapel bank soal.']);
            }
            if (!empty($bankInfo->kartu_soal_id) && (int) $bankInfo->kartu_soal_id !== (int) ($item->kartu_id ?? 0)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Item kisi-kisi berasal dari kartu soal yang berbeda dengan bank soal ini.']);
            }

            // Jika item sudah ter-link, verifikasi soal masih ada (cegah orphan lock selamanya)
            if (!empty($item->soal_id)) {
                $soalExist = $db->table('cbt_soal')->where('id_soal', (int) $item->soal_id)->get()->getRow();
                if ($soalExist) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Item kisi-kisi ini sudah memiliki butir soal.']);
                }
                // Link yatim: bersihkan agar bisa di-generate ulang
                $db->table('cbt_kartu_soal_item')->where('id_item', $itemId)->update([
                    'soal_id' => null,
                    'bank_id' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $item->soal_id = null;
                $item->bank_id = null;
            }

            // Tentukan nomor berikutnya (gap terendah, atau lanjut dari max)
            $nomor = 1;
            $usedNums = [];
            foreach ($db->table('cbt_soal')
                ->select('nomor_soal')
                ->where('bank_id', $bankId)
                ->where('jenis', $jenis)
                ->get()->getResult() as $row) {
                $usedNums[(int) $row->nomor_soal] = true;
            }
            while (isset($usedNums[$nomor])) {
                $nomor++;
            }

            $kslStr = strtolower(trim((string) ($item->tingkat_kesulitan ?? 'sedang')));
            $kslInt = 2;
            if (in_array($kslStr, ['mudah', 'c1', 'c2'], true)) $kslInt = 1;
            elseif (in_array($kslStr, ['sulit', 'c5', 'c6'], true)) $kslInt = 3;

            $data = [
                'bank_id'     => $bankId,
                'nomor_soal'  => $nomor,
                'jenis'       => $jenis,
                'tipe_soal'   => (int) $jenis === 2 ? 'pgk' : 'pg',
                'subtes_code' => $bankInfo->subtes_code ?? 'GENERAL',
                'bobot_irt'   => 1.0,
                'tampilkan'   => 0,
                'kesulitan'   => $kslInt,
                'tujuan_id'   => !empty($item->id_tujuan) ? (int) $item->id_tujuan : null,
                'created_on'  => time(),
                'updated_on'  => time(),
            ];

            $insert = $db->table('cbt_soal')->insert($data);
            if (!$insert) {
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal membuat butir soal.']);
            }

            $insertedId = (int) $db->insertID();

            // Tautkan butir soal ke item kisi-kisi
            $db->table('cbt_kartu_soal_item')
                ->where('id_item', $itemId)
                ->update([
                    'soal_id'    => $insertedId,
                    'bank_id'    => $bankId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $this->logging->saveLog(3, 'membuat butir soal dari kisi-kisi, jenis: ' . $jenis . ', nomor: ' . $nomor);

            return $this->response->setJSON([
                'status' => true,
                'data'   => ['id_soal' => $insertedId, 'nomor_soal' => $nomor, 'jenis' => $jenis],
            ]);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::generateSoalFromKartu] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    private function hasValidHtmlContent(?string $html): bool
    {
        if (empty($html)) {
            return false;
        }
        $trimmed = trim((string) $html);
        if ($trimmed === '' || $trimmed === '<p><br></p>' || $trimmed === '<p></p>' || $trimmed === '<br>') {
            return false;
        }
        if (trim(strip_tags($trimmed)) !== '') {
            return true;
        }
        // Valid jika mengandung elemen visual/media seperti img, svg, iframe, video, audio, table, math, canvas
        if (preg_match('/<(img|svg|iframe|video|audio|table|math|canvas|picture)\b/i', $trimmed)) {
            return true;
        }
        return false;
    }

    /**
     * Save/Update soal
     */
    public function saveSoal()
    {
        try {
            $method = $this->request->getPost('method');
            $jenis = $this->request->getPost('jenis');
            $bank_id = (int) $this->request->getPost('bank_id');

            // Validasi: Cek jika bank soal sudah digunakan di jadwal aktif
            $bankInfo = $this->cbt->getBankSoalById((int) $bank_id);
            if (!$bankInfo) {
                log_message('error', 'saveSoal Validation Failed: Bank soal tidak ditemukan.');
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bankInfo)) {
                log_message('error', 'saveSoal Validation Failed: Akses ditolak.');
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }
            [$flowOk, $flowMsg] = $this->validateBankWorkflowForGuru($bankInfo, (int) $jenis);
            if (!$flowOk) {
                log_message('error', 'saveSoal Validation Failed: flowOk false - ' . $flowMsg);
                return $this->response->setJSON(['status' => false, 'message' => $flowMsg]);
            }
            if ($method == 'add') {
                [$quotaOk, $quotaMsg] = $this->ensurePlannedQuotaAvailable($bankInfo, (int) $jenis, 1);
                if (!$quotaOk) {
                    log_message('error', 'saveSoal Validation Failed: quotaOk false - ' . $quotaMsg);
                    return $this->response->setJSON(['status' => false, 'message' => $quotaMsg]);
                }
            }
            if ($this->isBankLockedBySchedule($bankInfo)) {
                log_message('error', 'saveSoal Validation Failed: digunakan > 0');
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal sedang digunakan dalam jadwal ujian aktif. Perubahan ditolak.']);
            }

            // Validasi: Cek deadline
            [$deadlineOk, $deadlineMsg] = $this->validateDeadline();
            if (!$deadlineOk) {
                log_message('error', 'saveSoal Validation Failed: deadlineOk false - ' . $deadlineMsg);
                return $this->response->setJSON(['status' => false, 'message' => $deadlineMsg]);
            }

            $nomor_soal = $this->request->getPost('nomor_soal');
            $soal = $this->request->getPost('soal');

            // VALIDASI INPUT WAJIB & RELASI
            $jenisInt = (int) $jenis;
            if ($jenisInt < 1 || $jenisInt > 7) {
                return $this->response->setJSON(['status' => false, 'message' => 'Jenis soal tidak valid.']);
            }
            // Tipe soal (pg/pgk/pgkk). Default disimpulkan dari jenis agar data lama aman.
            $tipeSoal = strtolower(trim((string) ($this->request->getPost('tipe_soal') ?? '')));
            if (!in_array($tipeSoal, ['pg', 'pgk', 'pgkk'], true)) {
                $tipeSoal = $jenisInt === 2 ? 'pgk' : 'pg';
            }
            $nomorInt = (int) $nomor_soal;
            if ($nomorInt < 1) {
                return $this->response->setJSON(['status' => false, 'message' => 'Nomor soal wajib diisi (minimal 1).']);
            }
            if (!$this->hasValidHtmlContent($soal)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Teks soal wajib diisi.']);
            }
            // Kunci jawaban wajib untuk tipe objektif (PG, Kompleks, Jodohkan, Urutan, Cloze)
            $rawJawaban = $this->request->getPost('jawaban');
            $jawabanEmpty = is_array($rawJawaban) ? empty($rawJawaban) : empty(trim((string)($rawJawaban ?? '')));
            $pgkkRaw = $this->request->getPost('jawaban_pgkk');
            $pgkkEmpty = is_string($pgkkRaw) ? empty(trim($pgkkRaw)) : empty($pgkkRaw);
            // PG Kompleks mode pgk: kunci berupa checkbox jawaban_benar_pg2[] (array huruf)
            $pg2Raw = $this->request->getPost('jawaban_benar_pg2');
            $pg2Empty = is_array($pg2Raw) ? empty(array_filter($pg2Raw, static fn($v) => trim((string) $v) !== '')) : true;
            if ($jenisInt === 2) {
                // jenis 2: gunakan field yang sesuai mode (pgkk → jawaban_pgkk, pgk → jawaban_benar_pg2)
                $kunciKosong = $tipeSoal === 'pgkk' ? $pgkkEmpty : $pg2Empty;
            } else {
                $kunciKosong = in_array($jenisInt, [1, 3, 6, 7]) ? $jawabanEmpty : false;
            }
            if ($kunciKosong) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Kunci jawaban wajib diisi agar soal dapat dinilai otomatis.'
                ]);
            }

            // --- Deteksi Duplikasi Lintas Bank Soal (Sprint 2) ---
            // Kembalikan WARNING (bukan error) agar guru bisa tetap simpan setelah konfirmasi.
            $confirmDuplikat = $this->request->getPost('confirm_duplikat') === '1';
            $soalHash        = null;
            if (!$confirmDuplikat && $this->hasValidHtmlContent($soal)) {
                try {
                    $detector = new \App\Services\DuplicateDetectorService();
                    $cekHasil = $detector->cek(
                        $soal,
                        $bank_id,
                        $method === 'edit' ? (int) ($this->request->getPost('soal_id') ?? 0) : null
                    );
                    $soalHash = $cekHasil['fingerprint'];

                    if ($cekHasil['is_duplicate']) {
                        $lokasiDuplikat = array_map(
                            fn($m) => ($m->bank_kode ?? '-') . ' No.' . ($m->nomor_soal ?? '?')
                                      . ' (' . ($m->nama_mapel ?? '-') . ')',
                            $cekHasil['matches']
                        );
                        return $this->response->setJSON([
                            'status'            => 'warning',
                            'duplicate_warning' => true,
                            'duplicate_lokasi'  => $lokasiDuplikat,
                            'fingerprint'       => $soalHash,
                            'message'           => 'Soal ini terdeteksi sama/mirip dengan soal di: '
                                                   . implode('; ', $lokasiDuplikat)
                                                   . '. Kirim ulang dengan confirm_duplikat=1 untuk tetap menyimpan.',
                        ]);
                    }
                } catch (\Throwable $dupErr) {
                    // Gagal cek duplikasi tidak boleh blokir penyimpanan soal
                    log_message('error', '[Cbtbanksoal::saveSoal] duplikat check: ' . $dupErr->getMessage());
                }
            } elseif ($this->hasValidHtmlContent($soal)) {
                // Jika guru konfirmasi duplikat, tetap hitung hash
                try {
                    $detector = new \App\Services\DuplicateDetectorService();
                    $soalHash = $detector->fingerprint($soal);
                } catch (\Throwable $e) { /* lewati */ }
            }

            $tujuanId = (int) ($this->request->getPost('tujuan_id') ?? 0);
            $bankInfo = $this->cbt->getBankSoalById($bank_id);
            // kesulitan column bertipe INT (1=mudah, 2=sedang, 3=sulit). UI mengirim
            // level C1-C6 — normalisasi agar tidak menulis string ke kolom integer.
            $kslPost = strtoupper(trim((string) ($this->request->getPost('kesulitan') ?? '')));
            if (in_array($kslPost, ['1', '2', '3'], true)) {
                $kslInt = (int) $kslPost;
            } elseif (in_array($kslPost, ['C1', 'C2', 'MUDAH', 'SULAH'], true) || $kslPost === '') {
                $kslInt = 1;
            } elseif (in_array($kslPost, ['C5', 'C6', 'SULIT'], true)) {
                $kslInt = 3;
            } else {
                $kslInt = 2;
            }
            $bobotIrt = (float) ($this->request->getPost('bobot_irt') ?? 1.0);
            if ($bobotIrt <= 0) $bobotIrt = 1.0;

            $data = [
                'bank_id'     => $bank_id,
                'jenis'       => $jenisInt,
                'tipe_soal'   => $tipeSoal,
                'nomor_soal'  => $nomorInt,
                'subtes_code' => $bankInfo->subtes_code ?? 'GENERAL',
                'bobot_irt'   => $bobotIrt,
                'soal'        => $this->decode_data($soal, $bank_id, $jenisInt, $nomorInt),
                'kesulitan'   => $kslInt,
                'soal_hash'   => $soalHash,
                'tujuan_id'   => $tujuanId > 0 ? $tujuanId : null,
            ];

            if ($jenis == '1') {
                $abjad = ['a', 'b', 'c', 'd', 'e'];
                foreach ($abjad as $abj) {
                    $opsi = $this->request->getPost('jawaban_' . $abj);
                    $data['opsi_' . $abj] = $this->decode_data($opsi ?? '', $bank_id, $jenis, $nomor_soal);
                }
                $data['jawaban'] = $this->request->getPost('jawaban_pg');
            } elseif ($jenis == '2') {
                $opsis = [];
                for ($i = 97; $i < 117; $i++) {
                    $op = $this->request->getPost('jawaban2_' . chr($i));
                    if ($op != null) {
                        $opsis[chr($i)] = $this->decode_data($op, $bank_id, $jenis, $nomor_soal);
                    }
                }
                $data['opsi_a'] = json_encode($opsis);

                if ($tipeSoal === 'pgkk') {
                    // PGKK Benar/Salah: kunci = JSON map {huruf:'B'|'S'}
                    $jwbPgkk = $this->request->getPost('jawaban_pgkk');
                    $decoded = is_string($jwbPgkk) ? json_decode($jwbPgkk, true) : (is_array($jwbPgkk) ? $jwbPgkk : null);
                    $map = [];
                    if (is_array($decoded)) {
                        foreach ($decoded as $k => $v) {
                            $lv = strtolower(trim((string) $v));
                            $map[strtolower(trim((string) $k))] = in_array($lv, ['b', 'benar', '1', 'true'], true) ? 'B' : 'S';
                        }
                    }
                    if (empty($map)) {
                        return $this->response->setJSON([
                            'status' => false,
                            'message' => 'Kunci Benar/Salah PGKK belum lengkap. Tentukan Benar/Salah untuk setiap pernyataan.'
                        ]);
                    }
                    $data['jawaban'] = json_encode($map);
                } else {
                    $jawabans = [];
                    $jwb_pg2 = $this->request->getPost('jawaban_benar_pg2');
                    if (is_array($jwb_pg2)) {
                        foreach ($jwb_pg2 as $jwb) {
                            $jawabans[] = $jwb;
                        }
                    }
                    $data['jawaban'] = json_encode($jawabans);
                }
            } elseif ($jenis == '3') {
                $jawabans = $this->request->getPost('jawaban');

                // Dump payload hanya untuk development & troubleshooting — level 'debug' agar
                // tidak mengotori log ERROR produksi (isi POST berisi teks soal).
                if (ENVIRONMENT !== 'production') {
                    log_message('debug', 'Jenis 3 save POST payload: ' . json_encode($this->request->getPost()));
                    log_message('debug', 'Jenis 3 jawabans: ' . json_encode($jawabans));
                }

                // Validate: Jawaban menjodohkan wajib ada
                if (empty($jawabans) || !is_array($jawabans) || count($jawabans) < 2) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Jawaban soal menjodohkan tidak boleh kosong. Pastikan minimal 1 pasang pernyataan sudah dihubungkan.'
                    ]);
                }

                // Process HTML content in each cell (skip '0', '1', '#' marker values)
                foreach ($jawabans as $key => $values) {
                    if (is_array($values)) {
                        foreach ($values as $key2 => $value) {
                            $strVal = (string) ($value ?? '');
                            if (strlen($strVal) > 0 && $strVal !== '0' && $strVal !== '1' && $strVal !== '#') {
                                $replaced = str_replace('<!--?xml encoding="UTF-8"--><meta charset="UTF-8">', '', $strVal);
                                $jawabans[$key][$key2] = $this->decode_data($replaced, $bank_id, $jenis, $nomor_soal);
                            }
                        }
                    }
                }

                $jwb_jodohkan = [
                    'model' => $this->request->getPost('model') ?: '1',
                    'type'  => $this->request->getPost('type') ?: '2',
                    'jawaban' => $jawabans
                ];
                $data['jawaban'] = json_encode($jwb_jodohkan);
            } elseif ($jenis == '4') {
                $jwb4 = $this->request->getPost('jawaban_isian');
                $data['jawaban'] = $this->decode_data($jwb4 ?? '', $bank_id, $jenis, $nomor_soal);
            } elseif ($jenis == '6') {
                $data['opsi_a'] = $this->request->getPost('soal_opsi');
                $data['jawaban'] = $this->request->getPost('jawaban');
            } elseif ($jenis == '7') {
                $data['opsi_a'] = $this->request->getPost('soal_opsi');
                $data['jawaban'] = $this->request->getPost('jawaban');
            } else {
                $jwb5 = $this->request->getPost('jawaban_essai');
                $data['jawaban'] = $this->decode_data($jwb5 ?? '', $bank_id, $jenis, $nomor_soal);
            }

            $db = \Config\Database::connect();

            $kartu_item_id = (int) $this->request->getPost('kartu_item_id');
            $unlink_kisi = $this->request->getPost('unlink_kisi');

            if ($method == 'add') {
                $data['created_on'] = time();
                $data['updated_on'] = time();
                $db->table('cbt_soal')->insert($data);
                $soal_id = $db->insertID();
                $message = 'Soal berhasil dibuat';
            } else {
                $soal_id = (int) $this->request->getPost('soal_id');
                $soalDb = $this->getSoalById($soal_id);
                if (!$soalDb || (int) ($soalDb->bank_id ?? 0) !== $bank_id) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Data soal tidak valid.']);
                }
                $data['updated_on'] = time();
                $db->table('cbt_soal')->where('id_soal', $soal_id)->update($data);
                $message = 'Soal berhasil diupdate';
            }

            // Relasi kartu soal <-> soal (atomic)
            $db->transBegin();
            try {
                if ($kartu_item_id > 0) {
                    // Validasi item kartu benar-benar ada sebelum dikaitkan
                    $itemExists = $db->table('cbt_kartu_soal_item')
                        ->where('id_item', $kartu_item_id)
                        ->countAllResults();
                    if ($itemExists > 0) {
                        // Hanya update item target. Tidak memutus item lain yang
                        // mungkin menunjuk soal_id ini di kartu berbeda (menjaga
                        // integritas relasi lintas kartu).
                        $db->table('cbt_kartu_soal_item')
                            ->where('id_item', $kartu_item_id)
                            ->update([
                                'soal_id' => $soal_id,
                                'bank_id' => $bank_id,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    }
                } elseif ($unlink_kisi === '1') {
                    // Lepas soal dari seluruh item kisi-kisi yang menunjuknya
                    $db->table('cbt_kartu_soal_item')
                        ->where('soal_id', $soal_id)
                        ->update(['soal_id' => null, 'bank_id' => null]);
                }

            if ($db->transStatus() === false) {
                    $db->transRollback();
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan relasi kisi-kisi.']);
                }
                $db->transCommit();
            } catch (\Throwable $tx) {
                $db->transRollback();
                log_message('error', '[Cbtbanksoal::saveSoal] relasi: ' . $tx->getMessage());
                return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan pada relasi kisi-kisi.']);
            }

            $this->logging->saveLog(3, 'menyimpan soal nomor: ' . $nomor_soal);

            // Invalidasi cache soal bank agar siswa yang sedang ujian
            // mendapat soal terbaru (misalnya saat admin fix soal aktif)
            $this->cbtService->invalidateSoalBaseCache($bank_id);

            return $this->response->setJSON(['status' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::saveSoal] ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * AJAX endpoint for auto-save and manual save via AJAX
     * This is an alias to saveSoal for frontend AJAX calls
     */
    public function save_soal_ajax()
    {
        return $this->saveSoal();
    }

    /**
     * Hapus soal
     */
    public function hapusSoal()
    {
        try {
            $id_soal = $this->request->getPost('id_soal');
            if (!$id_soal) {
                $id_soal = $this->request->getPost('soal_id'); // fallback for JS alias
            }
            $db = \Config\Database::connect();

            // Get soal info before deleting (need bank_id and jenis for re-sequencing)
            $soal = $db->table('cbt_soal')->select('bank_id, jenis, nomor_soal')->where('id_soal', $id_soal)->get()->getRow();
            if (!$soal) {
                return $this->response->setJSON(['status' => false, 'message' => 'Soal tidak ditemukan']);
            }

            // Validasi: Cek jika bank soal sudah digunakan di jadwal aktif
            $bankInfo = $this->cbt->getBankSoalById((int) $soal->bank_id);
            if (!$this->canAccessBank($bankInfo)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }
            if ($this->isBankLockedBySchedule($bankInfo)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal sedang digunakan dalam jadwal ujian aktif. Soal tidak dapat dihapus.']);
            }
            // Validasi: Cek deadline
            [$deadlineOk, $deadlineMsg] = $this->validateDeadline();
            if (!$deadlineOk) {
                log_message('error', 'hapusSoal Validation Failed: deadlineOk false - ' . $deadlineMsg);
                return $this->response->setJSON(['status' => false, 'message' => $deadlineMsg]);
            }

            // Jangan hapus item kisi-kisi yang menunjuk soal ini — cukup lepaskan relasinya
            // (soal_id → null, bank_id → null) agar blueprint kartu tetap terjaga dan bisa
            // dijadikan butir ulang. Konsisten dengan perilaku saveSoal?unlink_kisi=1.
            $db->table('cbt_kartu_soal_item')
                ->where('soal_id', $id_soal)
                ->update(['soal_id' => null, 'bank_id' => null, 'updated_at' => date('Y-m-d H:i:s')]);

            // Delete the soal
            $db->table('cbt_soal')->where('id_soal', $id_soal)->delete();

            // Re-sequence remaining soal numbers to prevent gaps
            // e.g., if soal 1,2,3,4,5 and #3 deleted â†’ remaining 1,2,4,5 â†’ re-number to 1,2,3,4
            $remainingSoal = $db->table('cbt_soal')
                ->select('id_soal')
                ->where('bank_id', $soal->bank_id)
                ->where('jenis', $soal->jenis)
                ->orderBy('nomor_soal', 'ASC')
                ->get()
                ->getResult();

            $newNomor = 1;
            foreach ($remainingSoal as $row) {
                $db->table('cbt_soal')
                    ->where('id_soal', $row->id_soal)
                    ->update(['nomor_soal' => $newNomor]);
                $newNomor++;
            }

            $this->logging->saveLog(5, 'menghapus soal');

            // Invalidasi cache soal bank
            $this->cbtService->invalidateSoalBaseCache((int) $soal->bank_id);

            return $this->response->setJSON(['status' => true, 'message' => 'Soal berhasil dihapus']);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::hapusSoal] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }


    /**
     * Save selected soal (tampilkan)
     */
    public function saveSelected()
    {
        try {
            $bank_id = $this->request->getVar('id_bank');
            if (!$bank_id) {
                $bank_id = $this->request->getVar('bank_id');
            }

            // Validasi: Cek jika bank soal sudah digunakan di jadwal aktif
            $bankInfo = $this->cbt->getBankSoalById((int) $bank_id);
            if (!$bankInfo) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bankInfo)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.']);
            }
            [$flowOk, $flowMsg] = $this->validateBankWorkflowForGuru($bankInfo);
            if (!$flowOk) {
                return $this->response->setJSON(['status' => false, 'message' => $flowMsg]);
            }
            if ($this->isBankLockedBySchedule($bankInfo)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Bank soal sedang digunakan dalam jadwal ujian aktif. Perubahan ditolak.']);
            }

            $jenis = $this->request->getVar('jenis');
            
            // Get selected soal - handle both FormData and regular POST
            // FormData dengan array seperti soal[] perlu diambil dengan getVar untuk CI4
            $selected = $this->request->getVar('soal');
            if (empty($selected)) {
                $selected = $this->request->getVar('selected');
            }
            
            // Pastikan selected adalah array dan unik
            if (!is_array($selected)) {
                $selected = [];
            }
            $selected = array_unique($selected);

            $db = \Config\Database::connect();

            // Reset all tampilkan for this bank and jenis
            $db->table('cbt_soal')
                ->where('bank_id', $bank_id)
                ->where('jenis', $jenis)
                ->update(['tampilkan' => '0']);

            // Set tampilkan = 1 for selected
            $updatedCount = 0;
            if (!empty($selected)) {
                $selected = array_values(array_map('intval', $selected));
                $db->table('cbt_soal')
                    ->where('bank_id', $bank_id)
                    ->whereIn('id_soal', $selected)
                    ->update(['tampilkan' => '1']);
                $updatedCount = count($selected);
            }

            // === AUTO-UPDATE STATUS BANK SOAL ===
            // Setelah simpan soal terpilih, cek apakah semua jenis soal sudah lengkap.
            $resultStatus = $this->syncBankStatus((int) $bank_id);
            // === END AUTO-UPDATE STATUS ===

            // Invalidasi cache soal bank agar siswa mendapat soal terbaru
            $this->cbtService->invalidateSoalBaseCache((int) $bank_id);

            $this->logging->saveLog(4, 'menyimpan soal terpilih');
            return $this->response->setJSON([
                'status' => true,
                'check' => $updatedCount,
                'message' => 'Soal terpilih berhasil disimpan',
                'bank_status' => $resultStatus['newStatus'] ?? null,
                'bank_selesai' => $resultStatus['bankSelesai'] ?? null,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::saveSelected] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Helper: sinkronisasi status bank soal
     * Menghitung apakah setiap jenis soal (PG, Kompleks, dll) sudah memenuhi target tampilkan.
     * Jika sudah lengkap, update status dan status_soal menjadi 1 (Aktif/Selesai).
     */
    private function syncBankStatus(int $bank_id): array
    {
        $db = \Config\Database::connect();
        $bankFresh = $this->cbt->getBankSoalById($bank_id);
        if (!$bankFresh) {
            return ['newStatus' => '0', 'bankSelesai' => false];
        }

        $jenisMap = [1, 2, 3, 4, 5, 6, 7];
        $tampilMap = [
            1 => (int) ($bankFresh->tampil_pg ?? 0),
            2 => (int) ($bankFresh->tampil_kompleks ?? 0),
            3 => (int) ($bankFresh->tampil_jodohkan ?? 0),
            4 => (int) ($bankFresh->tampil_isian ?? 0),
            5 => (int) ($bankFresh->tampil_esai ?? 0),
            6 => (int) ($bankFresh->tampil_urutan ?? 0),
            7 => (int) ($bankFresh->tampil_cloze ?? 0),
        ];

        $bankSelesai = true;
        foreach ($jenisMap as $j) {
            $required = $tampilMap[$j];
            if ($required <= 0) continue; // jenis ini tidak diperlukan, skip
            $actualTampil = $db->table('cbt_soal')
                ->where('bank_id', $bank_id)
                ->where('jenis', $j)
                ->where('tampilkan', '1')
                ->countAllResults();
            if ($actualTampil < $required) {
                $bankSelesai = false;
                break;
            }
        }

        $newStatus = $bankSelesai ? '1' : '0';
        $db->table('cbt_bank_soal')->where('id_bank', $bank_id)->update([
            'status' => $newStatus,
            'status_soal' => $newStatus
        ]);
        log_message('info', '[syncBankStatus] Bank ' . $bank_id . ' status updated to ' . $newStatus . ' (selesai=' . ($bankSelesai ? 'true' : 'false') . ')');

        return [
            'newStatus' => $newStatus,
            'bankSelesai' => $bankSelesai
        ];
    }

    /**
     * Helper: maybe unserialize
     */
    private function maybeUnserialize($data)
    {
        if (!is_string($data) || empty($data)) {
            return $data;
        }

        // Check if string looks like serialized data
        if (preg_match('/^(?:a|O|s|b|i|d|N):[0-9]+/', $data)) {
            $unserialized = @unserialize($data);
            if ($unserialized !== false || $data === 'b:0;') {
                return $unserialized;
            }
        }

        // Try JSON decode
        $json = json_decode($data);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        return $data;
    }

    public function upload_image()
    {
        $file = $this->request->getFile('file');
        $status = false;
        $filename = '';

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = 'file_' . date('YmdHis') . '.' . $file->getExtension();
            $uploadPath = FCPATH . 'uploads/bank_soal/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $mimeType = $file->getMimeType();
            if ($file->move($uploadPath, $newName)) {
                // Resize if image
                if (strpos($mimeType, 'image') !== false) {
                    // Auto Compress & Watermark
                    $this->processImage($uploadPath . $newName);
                }
                $filename = 'uploads/bank_soal/' . $newName;
                $status = true;
            }
        }

        return $this->response->setJSON(['status' => $status, 'filename' => $filename]);
    }

    public function delete_image()
    {
        $src = $this->request->getPost('src');
        if (!$src)
            return;

        $baseUrl = rtrim(base_url(), '/') . '/';
        $path = str_replace($baseUrl, '', $src);

        // Basic traversal protection
        if (strpos($path, '..') !== false)
            return;

        $fullPath = FCPATH . $path;

        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
            echo 'File Delete Successfully';
        }
    }

    public function deletefile()
    {
        return $this->delete_image();
    }

    public function getGuruMapel()
    {
        $id_mapel = $this->request->getGet('id_mapel');
        $db = \Config\Database::connect();
        // Return all guru
        $gurus = $db->table('master_guru')->orderBy('nama_guru', 'ASC')->get()->getResult();
        $data = [];
        $data[''] = 'Pilih Guru';
        foreach ($gurus as $g) {
            $data[$g->id_guru] = $g->nama_guru;
        }
        return $this->response->setJSON($data);
    }
    /**
     * Move Bank Soal to Active Period (TP/SMT)
     * Updates id_tp, id_smt, and remaps bank_kelas
     */
    public function do_pindah_bank()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Invalid request']);
        }

        $ids = $this->request->getPost('ids');
        if (empty($ids)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada bank soal yang dipilih']);
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        try {
            $db = \Config\Database::connect();

            // Bypass MasterModel to ensure DB connection is valid
            $tp = $db->table('master_tp')->where('active', 1)->get()->getRow();
            $smt = $db->table('master_smt')->where('active', 1)->get()->getRow();

            if (!$tp || !$smt) {
                return $this->response->setJSON(['status' => false, 'message' => 'Tahun/Semester aktif tidak ditemukan']);
            }

            // Prepare class mapping (All classes in active TP)
            // Need to map Name -> ID for Active TP
            // $kelas_now = $this->master->getAllKodeKelas($tp->id_tp, $smt->id_smt);
            // Implement simple retrieval directly
            $kelas_now_raw = $db->table('master_kelas')
                ->select('id_kelas, kode_kelas')
                ->where('id_tp', $tp->id_tp)
                ->where('id_smt', $smt->id_smt)
                ->get()->getResult();

            $kelas_now = [];
            foreach ($kelas_now_raw as $kn) {
                $kelas_now[$kn->id_kelas] = $kn->kode_kelas;
            }


            $successCount = 0;
            foreach ($ids as $val) {
                $id_bank = (int) $val;
                if ($id_bank === 0)
                    continue;

                $bank = $db->table('cbt_bank_soal')->where('id_bank', $id_bank)->get()->getRow();
                if (!$bank)
                    continue;
                if (!$this->canAccessBank($bank)) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk salah satu bank soal.']);
                }

                // 1. Remap Kelas
                $bank_kelas = json_decode($bank->bank_kelas ?? '[]');
                if (is_string($bank_kelas)) {
                    $bank_kelas = json_decode($bank_kelas);
                }

                $ids_lalu = array_column((array) $bank_kelas, 'kelas_id');
                $kelasSkrg = [];

                if (!empty($ids_lalu)) {
                    $kelasLalu = $this->getKelasByIds($ids_lalu);
                    $map_lalu = [];
                    foreach ($kelasLalu as $item) {
                        $map_lalu[] = $this->normalize($item->kode_kelas);
                    }

                    // Find matching class names in new TP
                    foreach ($kelas_now as $newId => $newKode) {
                        if (in_array($this->normalize($newKode), $map_lalu)) {
                            $kelasSkrg[] = ['kelas_id' => $newId];
                        }
                    }
                }

                // 2. Update Bank
                $updateData = [
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'bank_kelas' => json_encode($kelasSkrg)
                ];

                $db->table('cbt_bank_soal')->where('id_bank', $id_bank)->update($updateData);
                $successCount++;
            }

            $this->logging->saveLog(3, "Memindahkan $successCount Bank Soal ke TP/SMT Aktif");

            return $this->response->setJSON([
                'status' => true,
                'message' => "$successCount Bank Soal berhasil dipindahkan ke TP/SMT Aktif",
                'reload' => true
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Cbtbanksoal::do_pindah_bank] ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    public function cleanup_images()
    {
        helper(['filesystem', 'number']);
        $user = $this->getUser();
        $setting = $this->dashboard->getSetting();

        $path = FCPATH . 'uploads/bank_soal/';
        if (!is_dir($path))
            mkdir($path, 0777, true);

        // Kumpulkan nama file yang benar-benar dirujuk dari seluruh tabel (soal, kartu, rekap, jawaban)
        $used_files = $this->collectUsedBankSoalFiles();
        $usedSet = array_fill_keys($used_files, true);

        $unused_files = [];
        $total_size = 0;

        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..')
                continue;
            if ($file === 'index.html' || $file === 'index.php' || $file === '.htaccess')
                continue;
            if (strpos($file, '.') === 0)
                continue;

            $filepath = $path . $file;
            if (!is_file($filepath) || isset($usedSet[$file]))
                continue;

            $size = filesize($filepath);
            $unused_files[] = [
                'name' => $file,
                'size' => $size,
                'date' => date("Y-m-d H:i:s", filemtime($filepath))
            ];
            $total_size += $size;
        }

        if ($this->request->getPost('delete')) {
            $count = 0;
            foreach ($unused_files as $f) {
                $target = $path . basename($f['name']);
                if (is_file($target) && @unlink($target)) {
                    $count++;
                }
            }
            return redirect()->to('cbtbanksoal/cleanup_images')->with('message', $count . ' file berhasil dihapus.');
        }

        $data = [
            'user' => $user,
            'judul' => 'Cleanup Images',
            'subjudul' => 'Hapus Gambar Tak Terpakai',
            'setting' => $setting,
            'unused' => $unused_files,
            'total_size' => $total_size,
            'path' => $path,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        return view('cbt/banksoal/cleanup', $data);
    }

    /**
     * Kumpulkan nama file uploads/bank_soal yang dirujuk dari seluruh isi DB.
     * Cakupan lebih luas dari versi lama (yang hanya cek cbt_soal) sehingga
     * file yang dipakai di kartu soal, rekap, atau koreksi tidak terhapus.
     */
    private function collectUsedBankSoalFiles(): array
    {
        $db = \Config\Database::connect();
        $pattern = '/uploads\/bank_soal\/([a-zA-Z0-9_.\\-\\(\)\%]+)/';
        $used = [];

        // [tabel, kolom PK, kolom-kolom konten yang perlu discan]
        $sources = [
            ['cbt_soal', 'id_soal', ['soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban', 'deskripsi', 'file', 'file1', 'file_a', 'file_b', 'file_c', 'file_d', 'file_e']],
            ['cbt_kartu_soal_item', 'id_item', ['butir_soal', 'opsi_json', 'jawaban_benar_json', 'pembahasan']],
            ['cbt_bank_soal', 'id_bank', ['deskripsi']],
            ['cbt_rekap_jadwal', 'id_rekap', ['bank_soal', 'soal_pg', 'soal_kompleks', 'soal_jodohkan', 'soal_isian', 'soal_esai', 'soal_urutan', 'soal_cloze']],
            ['cbt_rekap', 'id_rekap', ['jawaban_pg', 'jawaban_esai', 'soal_essai', 'soal_kompleks', 'soal_jodohkan', 'soal_isian']],
            ['cbt_rekap_siswa', 'id_rekap_siswa', ['jawaban']],
            ['cbt_jawaban', 'id_jawaban', ['jawaban']],
        ];

        foreach ($sources as [$table, $pk, $cols]) {
            $chunk = 500;
            $lastId = 0;
            do {
                $rows = $db->table($table)
                    ->select(array_merge([$pk], $cols))
                    ->where($pk . ' >', $lastId)
                    ->orderBy($pk, 'ASC')
                    ->limit($chunk)
                    ->get()->getResult();
                if (empty($rows))
                    break;

                foreach ($rows as $row) {
                    $content = '';
                    foreach ($cols as $c)
                        $content .= (string) ($row->{$c} ?? '');
                    if ($content === '')
                        continue;
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $file)
                            $used[urldecode($file)] = true;
                    }
                }
                $lastId = (int) end($rows)->{$pk};
            } while (count($rows) === $chunk);
        }

        return array_keys($used);
    }

    /**
     * Repair nomor_soal gaps for all jenis in a bank
     * Ensures question numbers are sequential (1,2,3,...) with no gaps
     */
    private function repairNomorSoal(int $id_bank)
    {
        $db = \Config\Database::connect();

        // Process each jenis (1=PG, 2=Kompleks, 3=Jodohkan, 4=Isian, 5=Essai, 6=Urutan, 7=Cloze)
        for ($jenis = 1; $jenis <= 7; $jenis++) {
            $soals = $db->table('cbt_soal')
                ->select('id_soal, nomor_soal')
                ->where('bank_id', $id_bank)
                ->where('jenis', $jenis)
                ->orderBy('nomor_soal', 'ASC')
                ->get()
                ->getResult();

            $newNomor = 1;
            $needsRepair = false;

            // Check if repair is needed first
            foreach ($soals as $s) {
                if ((int) $s->nomor_soal !== $newNomor) {
                    $needsRepair = true;
                    break;
                }
                $newNomor++;
            }

            // Only update if gaps detected
            if ($needsRepair) {
                $newNomor = 1;
                foreach ($soals as $s) {
                    if ((int) $s->nomor_soal !== $newNomor) {
                        $db->table('cbt_soal')
                            ->where('id_soal', $s->id_soal)
                            ->update(['nomor_soal' => $newNomor]);
                    }
                    $newNomor++;
                }
            }
        }
    }



    /**
     * Get API Key from setting or user profile
     */
    private function getApiKey()
    {
        return $this->ai->getApiKey((int) session()->get('user_id'), ($this->isGuru() || session()->get('role') == '2'));
    }

    /**
     * Cek apakah fitur AI dimatikan untuk user saat ini.
     * Setting enable_ai=0 hanya berlaku untuk non-admin; admin selalu boleh pakai AI.
     */
    private function isAiDisabledForUser(): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        try {
            $setting = $this->dashboard->getSetting();
            return isset($setting->enable_ai) && (string) $setting->enable_ai === '0';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Guard endpoint AI: tolak guru/panitia jika AI dinonaktifkan di setting.
     * Return response JSON jika ditolak, null jika lolos.
     */
    private function denyIfAiDisabled()
    {
        if ($this->isAiDisabledForUser()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Fitur AI dinonaktifkan oleh administrator.']);
        }

        return null;
    }

    /**
     * Generate Soal AI - Returns preview data (does NOT save to DB)
     */
    public function generate_ai()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesi habis', 'auth' => false]);
        }

        if ($deny = $this->denyIfAiDisabled()) {
            return $deny;
        }

        $request = service('request');

        $bank_id = $request->getPost('bank_id');
        $kartu_item_id = (int) ($request->getPost('kartu_item_id') ?? 0);
        $topic = $request->getPost('topic');
        $jumlah = max(1, min(5, (int) ($request->getPost('jumlah_soal') ?? 1)));
        $tipe = $request->getPost('tipe_soal');
        // Sub-tipe PG Kompleks (jenis 2): 'pgk' = pilih beberapa benar, 'pgkk' = Benar/Salah tiap pernyataan
        $subtipePg2 = strtolower(trim((string) ($request->getPost('subtipe_pg2') ?? 'pgk')));
        if (!in_array($subtipePg2, ['pgk', 'pgkk'], true)) {
            $subtipePg2 = 'pgk';
        }
        $kesulitan = $request->getPost('tingkat_kesulitan');
        $jenjang = $request->getPost('jenjang');
        $kelas = $request->getPost('kelas');
        $topik = $request->getPost('topik') ?? '';
        $indikator = $request->getPost('indikator') ?? '';
        $jenisStimulusRaw = $request->getPost('jenis_stimulus');
        $jenisStimulusList = is_array($jenisStimulusRaw) ? $jenisStimulusRaw : (empty($jenisStimulusRaw) ? [] : explode(',', (string) $jenisStimulusRaw));
        $fokusKompetensi = (string) ($request->getPost('fokus_kompetensi') ?? '');
        $modeSoal = (string) ($request->getPost('mode_soal') ?? 'asesmen_nasional');
        $levelAkm = (string) ($request->getPost('level_akm') ?? 'auto');
        $konteksAkm = (string) ($request->getPost('konteks_akm') ?? 'auto');
        $aiModel = $request->getPost('ai_model') ?? 'gemini-3.5-flash-lite';

        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'API Key belum ditemukan.'
            ]);
        }

        $variationSeeds = [
            'timestamp' => time(),
            'random_key' => bin2hex(random_bytes(4))
        ];
        
        if ($jumlah > 1) {
            $contexts = ['Lingkungan', 'Teknologi', 'Sosial-Budaya', 'Ekonomi', 'Kesehatan', 'Sejarah', 'Sains'];
            $perspectives = ['Analis', 'Pelajar', 'Guru', 'Masyarakat Umum', 'Ahli', 'Kritikus'];
            shuffle($contexts);
            shuffle($perspectives);
            $variationSeeds['contexts'] = array_slice($contexts, 0, 2);
            $variationSeeds['perspectives'] = array_slice($perspectives, 0, 2);
        } else {
            // Untuk generate 1 per 1, berikan satu perspektif acak agar AI tidak monoton
            $perspectives = ['Analis', 'Pelajar', 'Guru', 'Masyarakat Umum', 'Ahli', 'Kritikus', 'Penulis Soal Profesional'];
            $variationSeeds['perspective'] = $perspectives[array_rand($perspectives)];
        }

        $mapelNama = '';
        $bank = null;
        $subtesKonteks = '';
        if ((int)$bank_id > 0) {
            $bank = $this->cbt->getBankSoalById((int)$bank_id);
            if (!$bank) {
                return $this->response->setJSON(['success' => false, 'message' => 'Bank soal tidak ditemukan.']);
            }
            if (!$this->canAccessBank($bank)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses ke bank soal ini.']);
            }
            if ($bank && isset($bank->bank_mapel_id)) {
                $mapel = $this->master->getMapelById((int)$bank->bank_mapel_id);
                if ($mapel && isset($mapel->nama_mapel)) {
                    $mapelNama = $mapel->nama_mapel;
                }
            }
            // Bank terhubung sub-tes (bukan GENERAL) → beri konteks sub-tes ke prompt & sesuaikan fokus kompetensi
            $subtesKode = (string) ($bank->subtes_code ?? 'GENERAL');
            if ($subtesKode !== '' && strtoupper($subtesKode) !== 'GENERAL') {
                $subtesNama = (string) ($bank->subtes_nama ?? $subtesKode);
                $subtesKonteks = strtoupper($subtesKode) . ($subtesNama !== '' && $subtesNama !== $subtesKode ? ' — ' . $subtesNama : '');
                if (empty($fokusKompetensi)) {
                    $mapFokus = [
                        'MTK'   => 'numerasi',
                        'BIN'   => 'literasi_membaca',
                        'BING'  => 'literasi_membaca',
                        'FIS'   => 'literasi_sains',
                        'KIM'   => 'literasi_sains',
                        'BIO'   => 'literasi_sains',
                        'INF'   => 'literasi_sains',
                    ];
                    $fokusKompetensi = $mapFokus[strtoupper($subtesKode)] ?? 'literasi_membaca';
                }
            }
        }

        $isEnglish = stripos($topic ?? '', 'english') !== false || stripos($topic ?? '', 'bahasa inggris') !== false
            || stripos($topik ?? '', 'english') !== false || stripos($topik ?? '', 'bahasa inggris') !== false
            || stripos($mapelNama, 'english') !== false || stripos($mapelNama, 'bahasa inggris') !== false
            || strtoupper((string) ($bank->subtes_code ?? '')) === 'BING';

        $prompt = $this->constructPrompt(
            $jumlah, $topic, $tipe, $kesulitan, $jenjang, $kelas,
            $indikator, $topik, 
            (!empty($jenisStimulusList) ? $jenisStimulusList[0] : ''), 
            $fokusKompetensi,
            $variationSeeds,
            $isEnglish,
            $modeSoal,
            $subtesKonteks,
            $levelAkm,
            $konteksAkm,
            $subtipePg2
        );

        $aiResult = $this->ai->generateContent($apiKey, $prompt, $aiModel);

        if (!$aiResult['success']) {
            return $this->response->setJSON(['success' => false, 'message' => $aiResult['message']]);
        }

        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        
        if (!$parseResult['success']) {
            return $this->response->setJSON(['success' => false, 'message' => $parseResult['message']]);
        }

        return $this->response->setJSON([
            'success' => true,
            'preview' => $parseResult['data']
        ]);
    }

    public function save_ai_generated()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesi habis', 'auth' => false]);
        }

        if ($deny = $this->denyIfAiDisabled()) {
            return $deny;
        }

        $request = service('request');
        $bank_id = (int) $request->getPost('bank_id');
        $tipe = (int) $request->getPost('tipe_soal');
        // Sub-tipe PG Kompleks (jenis 2): pgk (pilih beberapa benar) / pgkk (Benar-Salah)
        $subtipePg2 = strtolower(trim((string) ($request->getPost('subtipe_pg2') ?? 'pgk')));
        if (!in_array($subtipePg2, ['pgk', 'pgkk'], true)) {
            $subtipePg2 = $tipe === 2 ? 'pgk' : 'pg';
        }
        $generated = $request->getPost('generated');
        $current_nomor_post = (int) ($request->getPost('nomor_soal') ?? 0);
        $replace_active = (int) ($request->getPost('replace_active') ?? 0);

        if ($bank_id <= 0 || empty($generated)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak lengkap.']);
        }

        $bank = $this->cbt->getBankSoalById($bank_id);
        if (!$bank) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bank soal tidak ditemukan.']);
        }

        if (!$this->canAccessBank($bank)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses ke bank soal ini.']);
        }

        $dec = json_decode($generated, true);
        if (!$dec) {
            return $this->response->setJSON(['success' => false, 'message' => 'Format data AI tidak valid.']);
        }

        $db = \Config\Database::connect();
        
        $maxNo = $db->table('cbt_soal')
            ->where('bank_id', $bank_id)
            ->where('jenis', $tipe)
            ->selectMax('nomor_soal')
            ->get()->getRow();
        $nomor = (int) ($maxNo->nomor_soal ?? 0) + 1;

        $data_insert = [];
        $data_update = [];
        $has_overwritten_current = false;

        foreach ($dec as $g) {
            $assign_nomor = $nomor;
            $is_update_current = false;
            $existing_id = 0;

            if ($current_nomor_post > 0 && $replace_active === 1 && !$has_overwritten_current) {
                $existing = $this->cbt->getSoalByNomor($bank_id, $current_nomor_post, $tipe);
                if ($existing) {
                    $is_update_current = true;
                    $has_overwritten_current = true;
                    $assign_nomor = $current_nomor_post;
                    $existing_id = $existing->id_soal;
                } else {
                    $is_update_current = false;
                    $has_overwritten_current = true;
                    $assign_nomor = $current_nomor_post;
                    $nomor = max($nomor, $current_nomor_post + 1);
                }
            } elseif ($current_nomor_post > 0 && !$has_overwritten_current) {
                 $existing = $this->cbt->getSoalByNomor($bank_id, $current_nomor_post, $tipe);
                if ($existing) {
                    $is_update_current = true;
                    $has_overwritten_current = true;
                    $assign_nomor = $current_nomor_post;
                    $existing_id = $existing->id_soal;
                }
            }

            if (!$is_update_current && $replace_active !== 1) {
                $assign_nomor = $nomor++;
            } elseif (!$is_update_current && $replace_active === 1 && $has_overwritten_current) {
                $assign_nomor = $nomor++;
            }

            $tujuanIdAi = (int) ($g['tujuan_id'] ?? 0);
            $item = [
                'bank_id' => $bank_id,
                'jenis' => $tipe,
                'nomor_soal' => $assign_nomor,
                'soal' => $g['soal'] ?? 'Soal tidak terbaca',
                'subtes_code' => $bank->subtes_code ?? 'GENERAL',
                'bobot_irt'   => 1.0,
                'created_on' => time(),
                'updated_on' => time(),
                'tampilkan' => 1,
                'ai_generated' => 1,
                'tujuan_id' => $tujuanIdAi > 0 ? $tujuanIdAi : null,
                'opsi_a' => '',
                'opsi_b' => '',
                'opsi_c' => '',
                'opsi_d' => '',
                'opsi_e' => '',
                'jawaban' => ''
            ];

            if ($tipe == 1) {
                $item['opsi_a'] = $g['opsi_a'] ?? '';
                $item['opsi_b'] = $g['opsi_b'] ?? '';
                $item['opsi_c'] = $g['opsi_c'] ?? '';
                $item['opsi_d'] = $g['opsi_d'] ?? '';
                $item['opsi_e'] = $g['opsi_e'] ?? '';
                $pgKey = strtolower(trim($g['jawaban'] ?? 'a'));
                $pgKey = preg_replace('/[^a-e]/', '', $pgKey);
                $item['jawaban'] = $pgKey !== '' ? $pgKey : 'a';
            } elseif ($tipe == 2) {
                $alpha = range('a', 'z');
                $opsi = [];
                $i = 0;
                foreach ($g['opsi'] as $k => $v) {
                    $char = $alpha[$i] ?? $k;
                    $opsi[$char] = $v;
                    $i++;
                }
                $item['opsi_a'] = json_encode($opsi);
                $item['tipe_soal'] = $subtipePg2;
                if ($subtipePg2 === 'pgkk') {
                    // PGKK: jawaban = {huruf: "B"|"S"} untuk SETIAP pernyataan
                    $bsMap = [];
                    $j = 0;
                    foreach ($g['opsi'] as $k => $v) {
                        $char = $alpha[$j] ?? $k;
                        // Normalisasi kunci AI: cek huruf kecil, huruf besar, dan bentuk indeks
                        $raw = $g['jawaban'][$char] ?? $g['jawaban'][strtoupper($char)] ?? $g['jawaban'][$k] ?? $g['jawaban'][strtoupper($k)] ?? 'B';
                        $bsMap[$char] = strtoupper((string) $raw) === 'S' ? 'S' : 'B';
                        $j++;
                    }
                    $item['jawaban'] = json_encode($bsMap);
                } else {
                    // PGK: jawaban = array huruf opsi yang benar
                    $kuncis = $g['jawaban'] ?? [];
                    $mappedKuncis = [];
                    $j = 0;
                    foreach ($g['opsi'] as $k => $v) {
                        $char = $alpha[$j] ?? $k;
                        if (in_array($char, $kuncis, true) || in_array(strtolower($char), $kuncis, true)
                            || in_array($k, $kuncis, true) || in_array(strtolower($k), $kuncis, true)) {
                            $mappedKuncis[] = $char;
                        }
                        $j++;
                    }
                    $item['jawaban'] = json_encode($mappedKuncis);
                }
            } elseif ($tipe == 3) {
                $pairs = $g['pairs'] ?? [];
                $rightSide = [];
                foreach ($pairs as $p) {
                    $rightSide[] = $p['kanan'];
                }
                $rightSide = array_values(array_unique($rightSide));
                $matrix = [];
                $header = ['#'];
                foreach ($rightSide as $r) $header[] = $r;
                $matrix[] = $header;

                foreach ($pairs as $p) {
                    $row = [$p['kiri']];
                    foreach ($rightSide as $rs) {
                        $row[] = ($rs == $p['kanan']) ? '1' : '0';
                    }
                    $matrix[] = $row;
                }
                $item['jawaban'] = json_encode(['model' => '1', 'type' => '1', 'jawaban' => $matrix]);
            } elseif ($tipe == 4 || $tipe == 5) {
                 $item['jawaban'] = $g['jawaban'] ?? '';
            } elseif ($tipe == 6) {
                $items = $g['items'] ?? [];
                if (!is_array($items) || count($items) < 2) {
                    continue;
                }
                $item['opsi_a'] = json_encode(['items' => $items]);
                $item['jawaban'] = json_encode(range(0, count($items) - 1));
            } elseif ($tipe == 7) {
                $soalText = $g['soal'] ?? '';
                preg_match_all('/\[\[(.*?)\]\]/', $soalText, $matches);
                $answers = [];
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $mi => $val) {
                        $answers[($mi + 1)] = $val;
                    }
                }
                $item['jawaban'] = json_encode($answers);
            }

            if ($is_update_current) {
                $item['id_soal'] = $existing_id;
                $data_update[] = $item;
            } else {
                $data_insert[] = $item;
            }
        }

        try {
            if (!empty($data_insert)) {
                $db->table('cbt_soal')->insertBatch($data_insert);
            }
            if (!empty($data_update)) {
                $db->table('cbt_soal')->updateBatch($data_update, 'id_soal');
            }

            $kartuId = (int) ($bank->kartu_soal_id ?? 0);
            if ($kartuId > 0) {
                 $unlinked = $db->table('cbt_kartu_soal_item')
                    ->where('kartu_id', $kartuId)
                    ->where('jenis_soal', $tipe)
                    ->where('soal_id IS NULL', null, false)
                    ->orderBy('nomor', 'ASC')
                    ->get()->getResult();

                 $newSoals = $db->table('cbt_soal')
                    ->where('bank_id', $bank_id)
                    ->where('jenis', $tipe)
                    ->orderBy('id_soal', 'DESC')
                    ->limit(count($data_insert))
                    ->get()->getResult();
                 $newSoals = array_reverse($newSoals);

                 $count = min(count($unlinked), count($newSoals));
                 for ($i = 0; $i < $count; $i++) {
                     $db->table('cbt_kartu_soal_item')
                        ->where('id_item', $unlinked[$i]->id_item)
                        ->update(['soal_id' => $newSoals[$i]->id_soal, 'bank_id' => $bank_id]);

                     // Copy tujuan_id dari kartu item ke soal jika belum ada
                     if (!empty($unlinked[$i]->id_tujuan)) {
                         $db->table('cbt_soal')
                            ->where('id_soal', $newSoals[$i]->id_soal)
                            ->where('tujuan_id IS NULL', null, false)
                            ->update(['tujuan_id' => (int) $unlinked[$i]->id_tujuan]);
                     }
                 }
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Soal AI berhasil disimpan.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Buat SOAL MIRIP dari soal yang sudah tersimpan.
     * Soal baru menguji KOMPETENSI YANG SAMA tapi dengan SKENARIO BERBEDA TOTAL.
     * Output disimpan sebagai soal baru di bank yang sama via save_ai_generated.
     */
    public function generate_similar_ai()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesi habis', 'auth' => false]);
        }

        if ($deny = $this->denyIfAiDisabled()) {
            return $deny;
        }

        $request  = service('request');
        $idSoal   = (int) ($request->getPost('id_soal')   ?? 0);
        $bankId   = (int) ($request->getPost('bank_id')   ?? 0);
        $jumlah   = max(1, min(3, (int) ($request->getPost('jumlah') ?? 1)));
        $aiModel  = trim((string) ($request->getPost('ai_model') ?? ''));
        if ($aiModel === '') {
            $aiModel = 'gemini-3.5-flash-lite';
        }
        $subtipePg2 = strtolower(trim((string) ($request->getPost('subtipe_pg2') ?? 'pgk')));
        if (!in_array($subtipePg2, ['pgk', 'pgkk'], true)) {
            $subtipePg2 = 'pgk';
        }

        if ($idSoal <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Simpan soal terlebih dahulu sebelum membuat soal mirip.']);
        }

        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            return $this->response->setJSON(['success' => false, 'message' => 'API Key belum ditemukan.']);
        }

        $db = \Config\Database::connect();
        
        // Cari soal sumber di database
        $soalBuilder = $db->table('cbt_soal')->where('id_soal', $idSoal);
        if ($bankId > 0) {
            $soalBuilder->where('bank_id', $bankId);
        }
        $soal = $soalBuilder->get()->getRow();
        if (!$soal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Soal sumber tidak ditemukan.']);
        }

        $bankId = (int) ($soal->bank_id ?? $bankId);
        $bank = $this->cbt->getBankSoalById($bankId);
        if (!$bank) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bank soal tidak ditemukan.']);
        }
        if (!$this->canAccessBank($bank)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses ke bank soal ini.']);
        }

        $jenis = (int) ($soal->jenis ?? 1);
        if ($jenis < 1 || $jenis > 7) {
            return $this->response->setJSON(['success' => false, 'message' => 'Jenis soal sumber tidak didukung.']);
        }

        // Warisi subtipe PG2 dari soal sumber jika tersedia
        $tipeSoalSumber = strtolower(trim((string) ($soal->tipe_soal ?? 'pg')));
        if ($jenis === 2 && in_array($tipeSoalSumber, ['pgk', 'pgkk'], true)) {
            $subtipePg2 = $tipeSoalSumber;
        }

        $prompt = $this->ai->buildSimilarPrompt($soal, $jenis, $subtipePg2, $jumlah);

        $startTime = microtime(true);
        $aiResult  = $this->ai->generateContent($apiKey, $prompt, $aiModel);
        $elapsed   = round(microtime(true) - $startTime, 2);

        if (!$aiResult['success']) {
            return $this->response->setJSON(['success' => false, 'message' => $aiResult['message']]);
        }

        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        if (!$parseResult['success']) {
            return $this->response->setJSON(['success' => false, 'message' => $parseResult['message']]);
        }

        $preview = $parseResult['data'];
        if (!is_array($preview) || count($preview) === 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'AI tidak menghasilkan soal mirip.']);
        }

        // Warisi tujuan_id dari soal sumber agar soal baru terhubung ke TP yang sama
        $tujuanIdSumber = (int) ($soal->tujuan_id ?? 0);
        foreach ($preview as &$item) {
            if (empty($item['tujuan_id'])) {
                $item['tujuan_id'] = $tujuanIdSumber;
            }
        }
        unset($item);

        return $this->response->setJSON([
            'success'     => true,
            'preview'     => $preview,
            'tipe_soal'   => (string) $jenis,
            'subtipe_pg2' => $subtipePg2,
            'analytics'   => [
                'model'   => $aiModel,
                'elapsed' => $elapsed,
                'count'   => count($preview),
            ]
        ]);
    }

    /**
     * Construct AI prompt
     */
    private function constructPrompt($jumlah, $topic, $tipe, $kesulitan, $jenjang, $kelas, $indikator = '', $topik = '', $jenisStimulus = '', $fokusKompetensi = '', $variationSeeds = [], $isEnglish = false, $modeSoal = 'asesmen_nasional', $subtesKonteks = '', $levelAkm = 'auto', $konteksAkm = 'auto', $subtipePg2 = 'pgk')
    {
        $faseMap = [
            'SD' => ['1' => 'A', '2' => 'A', '3' => 'B', '4' => 'B', '5' => 'C', '6' => 'C'],
            'SMP' => ['7' => 'D', '8' => 'D', '9' => 'D'],
            'SMA' => ['10' => 'E', '11' => 'F', '12' => 'F'],
            'SMK' => ['10' => 'E', '11' => 'F', '12' => 'F'],
        ];
        $fase = $faseMap[$jenjang][$kelas] ?? 'D';

        $levelMap = [
            'C1' => ['bloom' => 'C1 (Mengingat)', 'anbk' => 'Level 1'],
            'C2' => ['bloom' => 'C2 (Memahami)', 'anbk' => 'Level 1'],
            'C3' => ['bloom' => 'C3 (Mengaplikasikan)', 'anbk' => 'Level 2'],
            'C4' => ['bloom' => 'C4 (Menganalisis)', 'anbk' => 'Level 3'],
            'C5' => ['bloom' => 'C5 (Mengevaluasi)', 'anbk' => 'Level 4'],
            'C6' => ['bloom' => 'C6 (Mencipta)', 'anbk' => 'Level 5'],
        ];
        $level = $levelMap[$kesulitan] ?? $levelMap['C4'];

        // Mode Soal Biasa - Simple Mode
        if ($modeSoal === 'soal_biasa') {
            $prompt = "PERAN: Kamu adalah Guru profesional yang membuat soal ulangan harian/quiz sederhana untuk siswa.\n\n";
            $prompt .= "TUGAS: Buat {$jumlah} soal sederhana kontekstual dan mudah dipahami untuk:\n";
            $prompt .= "- Jenjang: {$jenjang} Kelas {$kelas}\n";
            $prompt .= "- Topik: {$topic}\n";
            if (!empty($topik)) $prompt .= "- Materi: {$topik}\n";
            if (!empty($indikator)) $prompt .= "- Indikator: {$indikator}\n";
            $prompt .= "- Tingkat: {$kesulitan}\n\n";

            if ($isEnglish) {
                $prompt .= "LANGUAGE RULE: PENTING! MATAPELAJARAN BAHASA INGGRIS. Pastikan SELURUH HASIL GENERASI (BAIK ITU SOAL MAUPUN PILIHAN JAWABAN) MENGGUNAKAN BAHASA INGGRIS BAKU.\n";
                $prompt .= "- Gunakan Bahasa Inggris baku sesuai standar nasional (Kurikulum Merdeka).\n";
                $prompt .= "- Gunakan British English atau American English yang konsisten, jangan dicampur.\n";
                $prompt .= "- Vocabulary, grammar, dan struktur kalimat WAJIB dalam Bahasa Inggris dan sesuai dengan kemampuan tingkat kelas {$kelas} {$jenjang}.\n";
                $prompt .= "- Kalimat jelas, tidak ambiguous, konteks relevan dengan kehidupan siswa Indonesia (tetapi tetap dalam Bahasa Inggris).\n\n";
            } else {
                $prompt .= "BAHASA: Gunakan bahasa Indonesia sederhana dan mudah dimengerti siswa. Hindari kata-kata sulit atau kalimat panjang.\n\n";
            }

            $prompt .= "STANDAR SOAL SEDERHANA:\n";
            $prompt .= "1. STIMULUS OPSIONAL: Bisa berupa paragraf pendek, gambaran sederhana, atau langsung pertanyaan tanpa stimulus panjang.\n";
            $prompt .= "2. BAHASA: Gunakan kalimat pendek dan jelas, hindari istilah teknis yang rumit.\n";
            $prompt .= "3. FOKUS: Pertanyaan langsung ke inti materi, tidak perlu analisis mendalam.\n";
            $prompt .= "4. KONTEKS: Bisa menggunakan contoh sehari-hari yang familiar bagi siswa.\n\n";
        } else {
            // Mode Asesmen Nasional - Complex Mode (existing logic)
            $prompt = "PERAN: Kamu adalah Ahli Asesmen Pendidikan (Assessment Specialist) Kurikulum Merdeka.\n\n";
            $prompt .= "TUGAS: Buat {$jumlah} soal berkualitas tinggi untuk:\n";
            $prompt .= "- Jenjang: {$jenjang} Kelas {$kelas} (Fase {$fase})\n";
            $prompt .= "- Topik/CP: {$topic}\n";
            if (!empty($topik)) $prompt .= "- Materi Khusus: {$topik}\n";
            if (!empty($indikator)) $prompt .= "- Indikator Soal: {$indikator}\n";
            $prompt .= "- Tingkat Kesulitan: {$kesulitan} ({$level['bloom']})\n\n";

            if ($isEnglish) {
                $prompt .= "LANGUAGE RULE: PENTING! MATAPELAJARAN BAHASA INGGRIS. SELURUH HASIL GENERASI (BAIK SOAL MAUPUN PILIHAN JAWABAN) WAJIB MENGGUNAKAN BAHASA INGGRIS BAKU.\n";
                $prompt .= "- Gunakan Bahasa Inggris formal sesuai standar nasional Kurikulum Merdeka Indonesia (Kemendikdasmen).\n";
                $prompt .= "- SEMUA KOMPONEN (Stimulus, Pertanyaan, Opsi, Jawaban) HARUS 100% DALAM BAHASA INGGRIS YANG BENAR DAN BAKU.\n";
                $prompt .= "- Gunakan British English atau American English yang konsisten, jangan dicampur.\n";
                $prompt .= "- Vocabulary dan grammar sesuai tingkat kelas {$kelas} {$jenjang} (Fase {$fase}).\n";
                $prompt .= "- Untuk SMA/SMK: Gunakan academic English dengan vocabulary appropriate untuk tingkat SMA.\n";
                $prompt .= "- Untuk SMP: Gunakan English dengan struktur grammar dan vocabulary yang sudah dipelajari di kelas {$kelas}.\n";
                $prompt .= "- Untuk SD: Gunakan simple English dengan vocabulary dasar dan short sentences.\n";
                $prompt .= "- Pastikan konteks stimulus relevan dengan budaya/kehidupan Indonesia atau global yang familiar, tetapi tetap disajikan menggunakan Bahasa Inggris.\n\n";
            }

            $prompt .= "STANDAR KUALITAS:\n";
            $prompt .= "1. WAJIB STIMULUS: Awali soal dengan stimulus (kasus/data/teks) kontekstual dan orisinal.\n";
            $prompt .= "2. HOTS: Pastikan soal sesuai level kognitif {$kesulitan}.\n";
            $prompt .= "3. HTML: Bungkus stimulus dalam <div class='stimulus' style='background:#f8f9fa;padding:12px;border-left:0px solid #0d6efd;border-radius:4px;margin-bottom:12px;'>.\n\n";

            // ── Kerangka AKM & Asesmen Nasional ────────────────────────────
            $akmKonteks = [
                'personal'      => 'Konteks PERSONAL — situasi yang berkaitan dengan kepentingan pribadi siswa (kesenangan diri, keseharian di rumah, keluarga, kesehatan diri).',
                'sosial_budaya' => 'Konteks SOSIAL BUDAYA — situasi yang berkaitan dengan kepentingan antarindividu, budaya, dan masyarakat (komunitas, budaya Indonesia, kearifan lokal, isu sosial).',
                'saintifik'     => 'Konteks SAINTIFIK — situasi yang berkaitan dengan sains dan pengetahuan ilmiah (fenomena alam, riset, data ilmiah, teknologi).',
                'auto'          => 'KONTEKS: Bervariasi di antara ketiga konteks resmi AKM (Personal, Sosial-Budaya, Saintifik); utamakan situasi nyata yang relevan bagi siswa Indonesia.',
            ];
            $akmLevel = [
                'level_1' => 'LEVEL KOGNITIF AKM 1 (Menemukan/Memahami) — mengakses, menemukan, dan memahami informasi sederhana, menerapkan konsep dasar.',
                'level_2' => 'LEVEL KOGNITIF AKM 2 (Menginterpretasi/Menerapkan) — mengintegrasikan informasi dari stimulus, menginterpretasi, menerapkan konsep pada situasi baru.',
                'level_3' => 'LEVEL KOGNITIF AKM 3 (Menalar/Mengevaluasi) — menganalisis, menyimpulkan, mengevaluasi, dan merefleksikan; menuntut penalaran tingkat tinggi.',
                'auto'    => '',
            ];
            $prompt .= "KERANGKA AKM & ASESMEN NASIONAL (WAJIB DIIKUTI):\n";
            $prompt .= "Kelas ini diasesmen mengikuti Standar Asesmen Kompetensi Minimum (AKM) Kemendikdasmen. Pastikan soal:\n";
            $prompt .= "1. KONTEN: sesuai Capaian Pembelajaran Kurikulum Merdeka untuk Fase {$fase} (kelas {$kelas} {$jenjang}).\n";
            $prompt .= "2. PROSES KOGNITIF: {$akmLevel[$levelAkm]}\n";
            $prompt .= "   - Fokus riset AKM memakai 3 level kognitif (L1 menemukan, L2 memahami/menerapkan, L3 menalar), bukan semata taksonomi Bloom.\n";
            $prompt .= "   - Untuk Literasi Membaca: L1 = mengakses & menemukan informasi; L2 = menafsirkan & memadukan (interpretasi-integrasi); L3 = mengevaluasi & merefleksi.\n";
            $prompt .= "   - Untuk Numerasi: L1 = pengetahuan & pemahaman konsep; L2 = penerapan konsep; L3 = penalaran.\n";
            $prompt .= "3. KONTEKS: {$akmKonteks[$konteksAkm]}\n";
            $prompt .= "4. STIMULUS: wajib memuat bahan bacaan/data/grafik/kasus yang cukup bagi siswa untuk menjawab — jangan boleh dijawab tanpa membaca stimulus.\n";
            $prompt .= "5. PENGGANGGU (DISTRACTOR): setiap pengecoh harus homogen secara gramatikal dan panjangnya serupa, dan merepresentasikan miskonsepsi umum siswa — bukan pengecoh asal-asalan.\n";
            $prompt .= "6. KUNCI JAWABAN: unik, pasti benar terhadap stimulus, dan tersebar merata antar opsi (a–e).\n";
            $prompt .= "7. BAHASA: kalimat per stimulus & per butir pendek, jelas, tidak ambigu, sesuai perkembangan bahasa siswa kelas {$kelas} {$jenjang}.\n\n";

            // Konteks spesifik per fokus kompetensi
            switch ($fokusKompetensi) {
                case 'literasi_membaca':
                    $prompt .= "SPESIFIKASI LITERASI MEMBACA:\n";
                    $prompt .= "- Konten tunggal: teks informasi (nonfiksi) dan teks sastra (fiksi).\n";
                    $prompt .= "- Teks wajib kontekstual: utamakan budaya Indonesia / kehidupan siswa NTT (mis. Amfoang, Kupang).\n";
                    $prompt .= "- Butir diarahkan pada kompetensi: menemukan informasi tersurat, membuat simpulan, memaknai kata/kalimat, menilai struktur & unsur intrinsik teks.\n";
                    $prompt .= "- Ragam format boleh: PG, PG kompleks (benar/salah), menjodohkan, isian singkat, uraian tertutup.\n\n";
                    break;
                case 'numerasi':
                    $prompt .= "SPESIFIKASI NUMERASI:\n";
                    $prompt .= "- Konten AKM Numerasi: Bilangan, Pengukuran & Geometri, Data & Ketidakpastian, Aljabar (sesuaikan dengan topik).\n";
                    $prompt .= "- Stimulus wajib menyajikan informasi kuantitatif (tabel, grafik, diagram, infografis, resep, katalog, jadwal, dsb.) yang otentik.\n";
                    $prompt .= "- Butir mengukur: representasi, sifat urutan & operasi, pengukuran & perkiraan, interpretasi data, penalaran proporsional.\n";
                    $prompt .= "- Untuk soal isian/uraian numerik, pastikan satu jawaban kuantitatif yang tidak ambigu beserta satuannya.\n\n";
                    break;
                case 'literasi_sains':
                    $prompt .= "SPESIFIKASI LITERASI SAINS:\n";
                    $prompt .= "- Konten: fenomena alam/sosial yang bisa dijelaskan sains; proses ilmiah (mengamati, mengajukan hipotesis, menyimpulkan).\n";
                    $prompt .= "- Stimulus menyajikan data percobaan/kasus lingkungan/situasi teknologi; butir menuntut interpretasi bukti.\n\n";
                    break;
                case 'survei_karakter':
                    $prompt .= "SPESIFIKASI SURVEI KARAKTER:\n";
                    $prompt .= "- Bukan soal akademik: situasi sosial harian yang mengukur nilai karakter (gotong royong, integritas, toleransi, berpikir kritis).\n";
                    $prompt .= "- Konteks dekat dengan kehidupan siswa; gunakan kasus dilema sederhana yang relevan usia kelas {$kelas}.\n\n";
                    break;
            }
        }

        // ── Panduan Standar Nasional Penyajian Visual (Tabel & Grafik SVG) ───────
        if ($jenisStimulus === 'data_tabel_infografis') {
            $prompt .= "STANDAR PENYAJIAN TABEL DATA NASIONAL (PUSMENDIK / AKM):\n";
            $prompt .= "1. FORMAT TABEL WAJIB BERIKUT:\n";
            $prompt .= "   - Berikan judul tabel di atas: <div class='fw-bold text-dark mb-1' style='font-size:13px;'>Tabel 1. [Judul Data Kontekstual]</div>\n";
            $prompt .= "   - Bungkus tabel dalam <div class='table-responsive my-2'>\n";
            $prompt .= "   - Gunakan class: <table class='table table-bordered table-sm text-center align-middle' style='width:100%;max-width:100%;border-collapse:collapse;margin:8px 0;font-size:13px;background:#fff;'>\n";
            $prompt .= "   - Header: <thead style='background:#e2e8f0;color:#1e293b;font-weight:bold;'><tr><th style='padding:6px 10px;border:1px solid #cbd5e1;'>...</th>...</tr></thead>\n";
            $prompt .= "   - Body: <tbody><tr><td style='padding:6px 10px;border:1px solid #cbd5e1;'>...</td>...</tr></tbody>\n";
            $prompt .= "   - Cantumkan sumber di bawah tabel: <div class='text-muted fst-italic mt-1' style='font-size:11px;'>Sumber: [Nama Instansi/Riset Faktual/Diolah 2026]</div>\n";
            $prompt .= "2. KONSISTENSI DATA: Kolom kategori rata kiri (text-start), angka dan satuan numerik rata tengah/kanan, data matematis akurat & logis.\n";
            $prompt .= "3. Pertanyaan dan pilihan jawaban WAJIB menguji kemampuan analitis membaca dan menginterpretasi data tabel tersebut.\n\n";
        } elseif ($jenisStimulus === 'grafik_diagram_svg') {
            $prompt .= "STANDAR PENYAJIAN GRAFIK & DIAGRAM VEKTOR NASIONAL (INLINE SVG PUSMENDIK):\n";
            $prompt .= "1. FORMAT SVG WAJIB BERIKUT:\n";
            $prompt .= "   - Tag pembuka: <svg viewBox='0 0 500 260' xmlns='http://www.w3.org/2000/svg' style='max-width:100%;width:100%;height:auto;display:block;margin:12px auto;background:#ffffff;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-family:system-ui,-apple-system,sans-serif;'>\n";
            $prompt .= "   - Judul grafik di dalam SVG: <text x='250' y='24' text-anchor='middle' font-size='13' font-weight='bold' fill='#1e293b'>[Judul Grafik / Diagram]</text>\n";
            $prompt .= "   - Gridline lembut: <line x1='60' y1='50' x2='470' y2='50' stroke='#f1f5f9' stroke-width='1' stroke-dasharray='3,3'/>\n";
            $prompt .= "   - Sumbu: garis sumbu X dan Y dengan panah <line stroke='#64748b' stroke-width='1.5'/> serta label sumbu <text font-size='11' fill='#475569'>...\n";
            $prompt .= "   - Label Angka / Data Nilai: Setiap batang (bar) atau titik data (point) WAJIB diberi angka nilai tertera jelas di atasnya (<text font-size='11' font-weight='bold' fill='#0f172a'>...</text>) agar siswa tidak salah mengira nilai.\n";
            $prompt .= "   - Palet Warna Profesional: gunakan warna modern kontras (#2563eb biru, #059669 hijau, #d97706 oranye, #dc2626 merah, #7c3aed ungu).\n";
            $prompt .= "   - Jika diagram geometri/sains: beri label sudut, garis gaya, dan dimensi panjang secara presisi dan terukur.\n";
            $prompt .= "2. Pertanyaan dan pilihan jawaban WAJIB menuntut siswa menafsirkan grafik/diagram tersebut.\n\n";
        } elseif ($jenisStimulus === 'teks_info_dialog_grafik') {
            $prompt .= "FORMAT STIMULUS: Teks Informasi Berita / Infografis / Dialog Faktual.\n";
            $prompt .= "- Sajikan narasi faktual informatif atau dialog tematik yang kaya data dan argumen.\n\n";
        }

        // Konteks sub-tes — disuntikkan bila bank soal terhubung sub-tes
        if (!empty($subtesKonteks)) {
            $prompt .= "KONTEKS SUB-TES:\n";
            $prompt .= "- Bank soal ini digunakan untuk sub-tes: {$subtesKonteks}.\n";
            $prompt .= "- Soal harus selaras dengan kompetensi akademik sub-tes tersebut (bukan sekedar ulangan biasa).\n";
            $prompt .= "- Gunakan stimulus yang kaya konteks (kasus/data/teks/grafik), pertanyaan analitis & penerapan, sesuai standar Asesmen Kompetensi.\n";
            $prompt .= "- Pastikan materi/pilihan jawaban padat, jelas, tidak ambigu, dan sesuai cakupan mapel sub-tes.\n\n";
        }

        switch ($tipe) {
            case '1':
                $prompt .= "TIPE SOAL: Pilihan Ganda (A-E)\n";
                if ($isEnglish) {
                    $prompt .= "BAHASA OPSI: Semua pilihan jawaban (opsi_a, opsi_b, opsi_c, opsi_d, opsi_e) WAJIB dalam Bahasa Inggris.\n";
                    $prompt .= "Hindari pilihan seperti 'Tidak tahu', 'Semua benar', 'Tidak ada yang benar' - gunakan 'None of the above', 'All of the above', 'I don't know'.\n";
                }
                $prompt .= "ATURAN JAWABAN BENAR: Distribusikan KUNCI JAWABAN SECARA ACAK dan bervariasi. Jawaban benar BUKAN hanya 'c', melainkan harus tersebar merata di antara 'a', 'b', 'c', 'd', atau 'e'.\n";
                $prompt .= "FORMAT JSON: [{\"soal\": \"...\", \"opsi_a\": \"...\", \"opsi_b\": \"...\", \"opsi_c\": \"...\", \"opsi_d\": \"...\", \"opsi_e\": \"...\", \"jawaban\": \"b\"}]\n";
                break;
            case '2':
                if ($subtipePg2 === 'pgkk') {
                    $prompt .= "TIPE SOAL: PG Kompleks — Benar/Salah (PGKK)\n";
                    if ($isEnglish) {
                        $prompt .= "BAHASA OPSI: Semua pernyataan dalam opsi WAJIB dalam Bahasa Inggris.\n";
                    }
                    $prompt .= "RINCIAN: Tampilkan 4–5 pernyataan (opsi). Siswa menilai setiap pernyataan: BENAR atau SALAH.\n";
                    $prompt .= "ATURAN: Pastikan masing-masing pernyataan memiliki status benar/salah yang PASTI dan tidak ambigu terhadap stimulus.\n";
                    $prompt .= "FORMAT JSON: [{\"soal\": \"...\", \"opsi\": {\"a\": \"Pernyataan 1\", \"b\": \"Pernyataan 2\", \"c\": \"Pernyataan 3\", \"d\": \"Pernyataan 4\", \"e\": \"Pernyataan 5\"}, \"jawaban\": {\"a\": \"B\", \"b\": \"S\", \"c\": \"B\", \"d\": \"S\", \"e\": \"B\"}}]\n";
                    $prompt .= "PENTING: NILAI jawaban HANYA 'B' atau 'S' (Benar/Salah) untuk SETIAP huruf opsi.\n";
                } else {
                    $prompt .= "TIPE SOAL: PG Kompleks — Pilih Beberapa Opsi Benar (PGK)\n";
                    if ($isEnglish) {
                        $prompt .= "BAHASA OPSI: Semua pernyataan dalam opsi WAJIB dalam Bahasa Inggris.\n";
                    }
                    $prompt .= "RINCIAN: Sajikan 4–5 pernyataan (opsi). Siswa MEMILIH lebih dari satu pernyataan yang benar (minimal 2 jawaban benar).\n";
                    $prompt .= "ATURAN: jawaban adalah ARRAY berisi 2–4 huruf opsi yang benar.\n";
                    $prompt .= "FORMAT JSON: [{\"soal\": \"...\", \"opsi\": {\"a\": \"Pernyataan 1\", \"b\": \"Pernyataan 2\", \"c\": \"Pernyataan 3\", \"d\": \"Pernyataan 4\", \"e\": \"Pernyataan 5\"}, \"jawaban\": [\"a\", \"c\", \"e\"]}]\n";
                }
                break;
            case '3':
                $prompt .= "TIPE SOAL: Menjodohkan\n";
                if ($isEnglish) {
                    $prompt .= "BAHASA OPSI: Semua elemen pasangan (kiri dan kanan) WAJIB dalam Bahasa Inggris.\n";
                }
                $prompt .= "FORMAT JSON: [{\"soal\": \"...\", \"pairs\": [{\"kiri\": \"A\", \"kanan\": \"B\"}]}]\n";
                break;
            case '4':
                $prompt .= "TIPE SOAL: Isian Singkat\n";
                if ($isEnglish) {
                    $prompt .= "BAHASA JAWABAN: Jawaban WAJIB dalam Bahasa Inggris (1 kata dalam English).\n";
                }
                $prompt .= "KETENTUAN KHUSUS (WAJIB): Jawaban HARUS terdiri dari tepat 1 KATA SAJA (satu kata). JANGAN buat jawaban yang terdiri dari 2 kata atau lebih. Jawaban harus singkat, presisi, dan hanya satu kata.\n";
                $prompt .= "FORMAT JSON: [{\"soal\": \"...\", \"jawaban\": \"satu\"}]\n";
                break;
            case '5':
                $prompt .= "TIPE SOAL: Uraian/Esai\n";
                if ($isEnglish) {
                    $prompt .= "BAHASA JAWABAN: Jawaban uraian WAJIB dalam Bahasa Inggris.\n";
                }
                $prompt .= "KETENTUAN KHUSUS: Jawaban berisi narasi jawaban singkat dan padat. SANGAT PENTING: JANGAN sertakan rubrik skor, pedoman penskoran, poin penilaian, atau kriteria penilaian apapun. Berikan langsung isi jawabannya saja dalam format HTML.\n";
                $prompt .= "FORMAT JSON: [{\"soal\": \"...\", \"jawaban\": \"<p>...uraian jawaban...</p>\"}]\n";
                break;
            case '6':
                $prompt .= "TIPE SOAL: Urutan (Sequencing)\n";
                if ($isEnglish) {
                    $prompt .= "BAHASA OPSI: Semua items urutan WAJIB dalam Bahasa Inggris.\n";
                }
                $prompt .= "FORMAT JSON: [{\"soal\": \"...\", \"items\": [\"Step 1\", \"Step 2\", \"Step 3\"]}]\n";
                break;
            case '7':
                $prompt .= "TIPE SOAL: Isian Rumpang (Cloze)\n";
                if ($isEnglish) {
                    $prompt .= "BAHASA: Teks dan kata-kata yang diisikan WAJIB dalam Bahasa Inggris.\n";
                }
                $prompt .= "FORMAT JSON: [{\"soal\": \"...teks dengan [[jawaban]] di tengahnya...\"}]\n";
                break;
        }

        $prompt .= "\n\nATURAN OUTPUT:\n";
        $prompt .= "1. Output HANYA valid JSON array.\n";
        $prompt .= "2. JANGAN sertakan teks pembuka atau markdown blocks.\n";

        return $prompt;
    }

    /**
     * Paraphrase soal using AI
     */
    public function paraphrase_ai()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesi habis', 'auth' => false]);
        }

        if ($deny = $this->denyIfAiDisabled()) {
            return $deny;
        }

        $request = service('request');
        $soal_original = $request->getPost('soal');
        $bank_id = (int) ($request->getPost('bank_id') ?? 0);
        $jenis = $request->getPost('jenis');
        $opsiJson = $request->getPost('opsi');

        if (empty($soal_original)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Konten soal kosong']);
        }

        $bank = $this->cbt->getBankSoalById($bank_id);
        if (!$bank) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bank soal tidak ditemukan.']);
        }
        if (!$this->canAccessBank($bank)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses ke bank soal ini.']);
        }

        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            return $this->response->setJSON(['success' => false, 'message' => 'API Key belum ditemukan.']);
        }

        $prompt = "Parafrase narasi soal berikut agar lebih kontekstual dan HOTS, namun tetap mempertahankan makna dan kunci jawaban yang sama.\n";
        $prompt .= "Pertahankan jenis soal (PG/Complex/Dll) dan struktur pilihan ganda jika ada.\n";
        $prompt .= "Output hanya JSON: { \"soal\": \"...isi html baru...\" }\n\n";

        $opsi = $opsiJson ? json_decode($opsiJson, true) : null;
        $hasOpsi = ($jenis === '1' || $jenis === '2') && !empty($opsi) && is_array($opsi) && count(array_filter($opsi)) > 0;

        // Bersihkan soal original jika sebelumnya sudah tertempel opsi A-E
        $soalBersih = preg_replace('/(\r?\n|<br\s*\/?>|<\/p>|<p>)\s*[A-E]\s*[\.\:\)]\s+.*$/is', '', $soal_original);
        if (empty(trim(strip_tags($soalBersih)))) {
            $soalBersih = $soal_original;
        }

        $prompt = "Kamu adalah editor ahli penyusun soal asesmen nasional.\n";
        $prompt .= "TUGAS: Parafrase dan tingkatkan kualitas soal berikut agar lebih kontekstual, menarik, dan berstandar HOTS/Literasi, namun tetap mempertahankan konsep materi dan kunci jawaban yang sama.\n\n";
        $prompt .= "ATURAN STRUKTUR (SANGAT PENTING):\n";
        $prompt .= "1. Kolom 'soal' HANYA boleh berisi narasi/stimulus dan kalimat pertanyaan pokok.\n";
        $prompt .= "2. DILARANG KERAS memasukkan huruf pilihan ganda (A, B, C, D, E) atau teks pilihan jawaban ke dalam kolom 'soal'.\n";
        $prompt .= "3. Setiap pilihan jawaban WAJIB ditulis terpisah di dalam objek 'opsi'.\n";
        $prompt .= "4. Output HANYA JSON murni (tanpa markdown, tanpa teks lain):\n\n";

        if ($hasOpsi && $jenis === '1') {
            $prompt .= '{"soal": "<p>...narasi dan pertanyaan baru (tanpa opsi A-E)...</p>", "opsi": {"a": "...", "b": "...", "c": "...", "d": "...", "e": "..."}}' . "\n\n";
            $prompt .= "SOAL ASLI:\n" . trim(strip_tags($soalBersih)) . "\n\n";
            $prompt .= "OPSI PILIHAN JAWABAN ASLI:\n";
            foreach (['a', 'b', 'c', 'd', 'e'] as $k) {
                if (isset($opsi[$k])) {
                    $prompt .= strtoupper($k) . ". " . trim(strip_tags((string) $opsi[$k])) . "\n";
                }
            }
        } elseif ($hasOpsi && $jenis === '2') {
            $prompt .= '{"soal": "<p>...narasi dan pertanyaan baru (tanpa pernyataan)...</p>", "opsi": {"a": "...", "b": "...", "c": "...", "d": "...", "e": "..."}}' . "\n\n";
            $prompt .= "SOAL ASLI:\n" . trim(strip_tags($soalBersih)) . "\n\n";
            $prompt .= "PERNYATAAN ASLI:\n";
            foreach ($opsi as $k => $v) {
                $prompt .= strtoupper($k) . ". " . trim(strip_tags((string) $v)) . "\n";
            }
        } else {
            $prompt .= '{"soal": "<p>...isi narasi dan pertanyaan baru...</p>"}' . "\n\n";
            $prompt .= "SOAL ASLI:\n" . trim(strip_tags($soalBersih)) . "\n";
        }

        $aiResult = $this->ai->generateContent($apiKey, $prompt);
        if (!$aiResult['success']) return $this->response->setJSON(['success' => false, 'message' => $aiResult['message']]);

        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        if (!$parseResult['success']) return $this->response->setJSON(['success' => false, 'message' => $parseResult['message']]);

        $soalBaru = '';
        $opsiBaru = [];

        if (is_array($parseResult['data'])) {
            $soalBaru = $parseResult['data']['soal'] ?? '';
            $opsiBaru = $parseResult['data']['opsi'] ?? [];
        } elseif (is_string($parseResult['data'])) {
            $soalBaru = $parseResult['data'];
        }

        // Post-processing: bersihkan jika AI masih menyelipkan opsi di akhir teks soal
        if ($hasOpsi && !empty($soalBaru)) {
            $soalClean = preg_replace('/(\r?\n|<br\s*\/?>|<\/p>|<p>)\s*[A-E]\s*[\.\:\)]\s+.*$/is', '', $soalBaru);
            if (!empty(trim(strip_tags($soalClean)))) {
                $soalBaru = $soalClean;
            }
        }

        if (empty($soalBaru) || !is_string($soalBaru)) {
            return $this->response->setJSON(['success' => false, 'message' => 'AI tidak mengembalikan teks soal yang valid.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $soalBaru,
            'opsi'    => is_array($opsiBaru) ? $opsiBaru : []
        ]);
    }

    /**
     * Tidy soal HTML using AI
     */
    public function tidy_ai()
    {
        if ($deny = $this->denyIfAiDisabled()) {
            return $deny;
        }

        $apiKey = $this->getApiKey();
        if (empty($apiKey)) return $this->response->setJSON(['success' => false, 'message' => 'API Key belum ditemukan.']);

        $request = service('request');
        $soal_html = $request->getPost('soal');
        $opsi = $request->getPost('opsi');

        $prompt = "Rapikan struktur HTML soal berikut agar valid dan bersih dari tag Word/junk styles. JANGAN ubah isi teks.\n";
        $prompt .= "Output JSON: { \"soal\": \"...\", \"opsi\": { ... } }\n\n";
        $prompt .= "HTML: " . $soal_html . ($opsi ? "\nOPSI: " . $opsi : "");

        $aiResult = $this->ai->generateContent($apiKey, $prompt);
        if (!$aiResult['success']) return $this->response->setJSON(['success' => false, 'message' => $aiResult['message']]);
        
        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        if (!$parseResult['success']) return $this->response->setJSON(['success' => false, 'message' => $parseResult['message']]);
        
        return $this->response->setJSON(['success' => true, 'data' => $parseResult['data']['soal'] ?? '', 'opsi' => $parseResult['data']['opsi'] ?? []]);
    }

    /**
     * Tidy soal HTML manually (strip tags)
     */
    public function tidy_manual()
    {
        $request = service('request');
        $soal_html = $request->getPost('soal');
        $opsi = $request->getPost('opsi');

        if (empty($soal_html)) return $this->response->setJSON(['success' => false, 'message' => 'Konten soal kosong']);

        $newSoal = $this->clean_html_string($soal_html, false);
        $newOpsi = [];
        if ($opsi) {
            $opsiArr = json_decode($opsi, true);
            if (is_array($opsiArr)) {
                foreach ($opsiArr as $k => $v) $newOpsi[$k] = $this->clean_html_string($v, true);
            }
        }

        return $this->response->setJSON(['success' => true, 'data' => $newSoal, 'opsi' => $newOpsi]);
    }

    /**
     * Helper to clean HTML string
     */
    private function clean_html_string($html, $singleLine = false)
    {
        if (empty(trim((string)$html))) return $html;
        $html = str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '<h1>', '<h2>', '<h3>', '</h4>', '<h5>', '</h6>', '</li>'], "\n", $html);
        $html = str_replace(['</td>', '</th>'], ' ', $html);
        $clean = strip_tags($html, '<img>');
        $lines = preg_split("/[\r\n]+/", $clean);
        $collectedLines = [];
        foreach ($lines as $line) {
            $trimmed = trim(str_replace('&nbsp;', ' ', $line));
            $trimmed = trim(preg_replace('/\s+/', ' ', $trimmed));
            if ($trimmed === '') continue;
            if (strpos($trimmed, '<img') !== false) {
                $trimmed = preg_replace_callback('/<img[^>]+>/i', function ($matches) {
                    $img = $matches[0];
                    $img = preg_replace('/(style|width|height|class)="[^"]*"/i', '', $img);
                    return str_replace('<img', '<img class="img-fluid"', $img);
                }, $trimmed);
            }
            $collectedLines[] = $trimmed;
        }
        if (empty($collectedLines)) return '';
        if ($singleLine) return '<p>' . implode(' ', $collectedLines) . '</p>';
        $result = '';
        foreach ($collectedLines as $ln) $result .= '<p>' . $ln . '</p>';
        return $result;
    }

    public function apiGetKartuItems()
    {
        try {
            $bank_id = (int) $this->request->getGet('id_bank');
            $jenis_soal = (int) $this->request->getGet('jenis');
            if ($bank_id <= 0) return $this->response->setJSON(['status' => false, 'message' => 'ID Bank tidak valid']);
            $bank = $this->cbt->getBankSoalById($bank_id);
            if (!$bank) return $this->response->setJSON(['status' => false, 'message' => 'Bank soal tidak ditemukan']);
            if (!$this->canAccessBank($bank)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak untuk bank soal ini.'])->setStatusCode(403);
            }
            $db = \Config\Database::connect();
            $builder = $db->table('cbt_kartu_soal_item a')
                ->select("a.id_item, a.level_kognitif, a.indikator_soal, a.materi, a.jenis_soal, b.judul, c.tujuan, c.kode as tujuan_kode, a.soal_id, a.id_tujuan")
                ->join('cbt_kartu_soal b', 'a.kartu_id = b.id_kartu')
                ->join('e_tujuan_pembelajaran c', 'a.id_tujuan = c.id_tujuan', 'left')
                ->where('b.mapel_id', $bank->bank_mapel_id)
                ->where('b.workflow_status', 'approved');
            // Scope periode mengikuti bank agar lintas TP/SMT tidak tercampur
            if (!empty($bank->id_tp)) {
                $builder->where('b.tp_id', (int) $bank->id_tp);
            }
            if (!empty($bank->id_smt)) {
                $builder->where('b.smt_id', (int) $bank->id_smt);
            }
            if ($jenis_soal > 0) $builder->where('a.jenis_soal', $jenis_soal);
            if ($this->isGuru()) $builder->where('b.created_by', $this->getUserId());
            $items = $builder->orderBy('b.id_kartu', 'DESC')->orderBy('a.nomor', 'ASC')->get()->getResult();
            return $this->response->setJSON(['status' => true, 'data' => $items]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat butir soal']);
        }
    }

    /**
     * API Get Tujuan Pembelajaran (TP) List by Mapel ID
     */
    public function apiGetTujuanByMapel()
    {
        try {
            $request = service('request');
            $mapelId = (int) ($request->getGet('mapel_id') ?? $request->getPost('mapel_id') ?? 0);
            $bankId  = (int) ($request->getGet('bank_id') ?? $request->getPost('bank_id') ?? 0);

            if ($mapelId <= 0 && $bankId > 0) {
                $bank = $this->cbt->getBankSoalById($bankId);
                if ($bank) {
                    $mapelId = (int) $bank->bank_mapel_id;
                }
            }

            if ($mapelId <= 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Mapel ID tidak valid']);
            }

            $db = \Config\Database::connect();
            $tujuans = $db->table('e_tujuan_pembelajaran tp')
                ->select('tp.id_tujuan, tp.kode, tp.kode_cp, tp.tujuan, tp.materi_pokok, tp.level_kognitif, tp.fase, tp.tingkat, cp.elemen as elemen_cp, cp.deskripsi_cp')
                ->join('e_capaian_pembelajaran cp', 'tp.kode_cp = cp.kode_cp', 'left')
                ->where('tp.id_mapel', $mapelId)
                ->where('tp.is_active', 1)
                ->orderBy('tp.kode_cp', 'ASC')
                ->orderBy('tp.kode', 'ASC')
                ->get()->getResult();

            return $this->response->setJSON([
                'status' => true,
                'mapel_id' => $mapelId,
                'data' => $tujuans
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal memuat Tujuan Pembelajaran: ' . $e->getMessage()]);
        }
    }

    public function generate_image()
    {
        if ($deny = $this->denyIfAiDisabled()) {
            return $deny;
        }

        $request = service('request');
        $prompt = $request->getPost('prompt') ?? '';
        $aspectRatio = $request->getPost('aspectRatio') ?? '16:9';
        if (trim((string) $prompt) === '') return $this->response->setJSON(['success' => false, 'message' => 'Deskripsi gambar tidak boleh kosong.']);
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) return $this->response->setJSON(['success' => false, 'message' => 'API Key belum ditemukan.']);
        $aiResult = $this->ai->generateImage($apiKey, (string)$prompt, (string)$aspectRatio);
        if (!$aiResult['success'] || empty($aiResult['data'])) return $this->response->setJSON(['success' => false, 'message' => $aiResult['message']]);
        return $this->response->setJSON(['success' => true, 'data' => $aiResult['data'], 'message' => 'Gambar berhasil di-generate.']);
    }

    /**
     * Check deadline - API endpoint
     */
    public function checkDeadline()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated']);
            }

            $deadlineInfo = $this->deadlineModel->getDeadlineInfoForDisplay();

            return $this->response->setJSON([
                'status' => true,
                'has_deadline' => $deadlineInfo['has_deadline'],
                'is_expired' => $deadlineInfo['is_expired'],
                'deadline_waktu' => $deadlineInfo['deadline_waktu'],
                'remaining' => $deadlineInfo['remaining'],
                'warning_level' => $deadlineInfo['warning_level']
            ]);
        } catch (\Throwable $th) {
            log_message('error', '[Cbtbanksoal::checkDeadline] ' . $th->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    /**
     * Validate if deadline has expired
     * Returns [is_valid, message]
     */
    private function validateDeadline(): array
    {
        // Only validate deadline for guru group
        if (!$this->inGroup('guru')) {
            return [true, 'ok'];
        }

        $isExpired = $this->deadlineModel->isDeadlineExpired();

        if ($isExpired) {
            $deadline = $this->deadlineModel->getGlobalDeadline();
            $deadlineText = $deadline ? date('d M Y H:i', strtotime($deadline->ba_waktu)) : 'sudah lewat';
            return [false, 'Maaf, deadline pengisian bank soal telah berakhir (' . $deadlineText . '). Silakan hubungi administrator untuk informasi lebih lanjut.'];
        }

        return [true, 'ok'];
    }

    /**
     * Get deadline info for view
     */
    private function getDeadlineInfo(): ?array
    {
        return $this->deadlineModel->getDeadlineInfoForDisplay();
    }

    /**
     * Ekspor Backup Bank Soal per Bank Soal (ZIP berisi manifest JSON & media/gambar)
     */
    public function exportBackup($id_bank)
    {
        try {
            $db = \Config\Database::connect();
            $bank = $db->table('cbt_bank_soal')->where('id_bank', (int)$id_bank)->get()->getRow();
            if (!$bank) {
                return redirect()->to('cbtbanksoal')->with('swal_error', 'Bank soal tidak ditemukan.');
            }
            if (!$this->canAccessBank($bank)) {
                return redirect()->to('cbtbanksoal')->with('swal_error', 'Akses ditolak untuk bank soal ini.');
            }

            $mapel = $db->table('master_mapel')->where('id_mapel', $bank->bank_mapel_id)->get()->getRow();
            $guru = $db->table('master_guru')->where('id_guru', $bank->bank_guru_id)->get()->getRow();
            $soals = $db->table('cbt_soal')->where('bank_id', (int)$id_bank)->orderBy('jenis', 'ASC')->orderBy('nomor_soal', 'ASC')->get()->getResultArray();

            $kartuIds = [];
            if (!empty($bank->kartu_soal_id)) {
                $kartuIds[] = (int) $bank->kartu_soal_id;
            }
            $itemKartuIds = $db->table('cbt_kartu_soal_item')
                ->select('kartu_id')
                ->where('bank_id', (int)$id_bank)
                ->where('kartu_id IS NOT NULL', null, false)
                ->groupBy('kartu_id')
                ->get()->getResultArray();
            foreach ($itemKartuIds as $ik) {
                if (!empty($ik['kartu_id'])) {
                    $kartuIds[] = (int) $ik['kartu_id'];
                }
            }
            $kartuIds = array_values(array_unique(array_filter($kartuIds)));

            $kartuRows = [];
            $kartuItemRows = [];
            if (!empty($kartuIds)) {
                $kartuRows = $db->table('cbt_kartu_soal')->whereIn('id_kartu', $kartuIds)->get()->getResultArray();
                $kartuItemRows = $db->table('cbt_kartu_soal_item')->whereIn('kartu_id', $kartuIds)->orderBy('kartu_id', 'ASC')->orderBy('nomor', 'ASC')->get()->getResultArray();
            }

            $mediaList = [];
            $textToScan = '';
            foreach ($soals as $soal) {
                foreach (['file', 'file1', 'file_a', 'file_b', 'file_c', 'file_d', 'file_e'] as $fCol) {
                    if (!empty($soal[$fCol])) {
                        $val = $soal[$fCol];
                        if (is_string($val) && (strpos($val, '[') === 0 || strpos($val, '{') === 0)) {
                            $decoded = json_decode($val, true);
                            if (is_array($decoded)) {
                                foreach ($decoded as $dItem) {
                                    if (is_string($dItem) && !empty($dItem)) {
                                        $mediaList[] = basename($dItem);
                                    }
                                }
                            }
                        } elseif (is_string($val)) {
                            $mediaList[] = basename($val);
                        }
                    }
                }
                foreach (['soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'deskripsi'] as $tCol) {
                    if (!empty($soal[$tCol])) {
                        $textToScan .= ' ' . $soal[$tCol];
                    }
                }
            }
            foreach ($kartuRows as $kRow) {
                if (!empty($kRow['deskripsi'])) {
                    $textToScan .= ' ' . $kRow['deskripsi'];
                }
            }
            foreach ($kartuItemRows as $kItem) {
                foreach (['butir_soal', 'opsi_json', 'jawaban_benar_json', 'pembahasan'] as $tc) {
                    if (!empty($kItem[$tc])) {
                        $textToScan .= ' ' . $kItem[$tc];
                    }
                }
            }
            if (!empty($bank->deskripsi)) {
                $textToScan .= ' ' . $bank->deskripsi;
            }

            if (!empty($textToScan)) {
                if (preg_match_all('#uploads/bank_soal/([^\s"\'' . '<>]+)#i', $textToScan, $matches)) {
                    foreach ($matches[1] as $mFile) {
                        $cleanFile = rawurldecode(trim(explode('?', $mFile)[0]));
                        if ($cleanFile !== '') {
                            $cleanFile = rtrim($cleanFile, '.,;:!?)]}>');
                            if ($cleanFile !== '') {
                                $mediaList[] = basename($cleanFile);
                            }
                        }
                    }
                }
            }

            $mediaList = array_values(array_filter(array_unique($mediaList), function ($file) {
                return !empty($file) && $file !== '.' && $file !== '..';
            }));

            $manifestData = [
                'version' => '1.1',
                'exported_at' => date('Y-m-d H:i:s'),
                'app' => 'IKAS CBT',
                'bank_soal' => [
                    'bank_kode' => (string) ($bank->bank_kode ?? ''),
                    'bank_nama' => (string) ($bank->bank_nama ?? ''),
                    'bank_level' => (string) ($bank->bank_level ?? ''),
                    'bank_kelas' => (string) ($bank->bank_kelas ?? ''),
                    'bank_jenis_id' => $bank->bank_jenis_id ?? null,
                    'bank_jurusan_id' => $bank->bank_jurusan_id ?? 0,
                    'bank_mapel_id' => $bank->bank_mapel_id ?? null,
                    'bank_guru_id' => $bank->bank_guru_id ?? null,
                    'kartu_soal_id' => $bank->kartu_soal_id ?? null,
                    'kkm' => $bank->kkm ?? 0,
                    'jml_soal' => $bank->jml_soal ?? 0,
                    'jml_esai' => $bank->jml_esai ?? 0,
                    'jml_kompleks' => $bank->jml_kompleks ?? 0,
                    'jml_jodohkan' => $bank->jml_jodohkan ?? 0,
                    'jml_isian' => $bank->jml_isian ?? 0,
                    'tampil_pg' => $bank->tampil_pg ?? 0,
                    'tampil_esai' => $bank->tampil_esai ?? 0,
                    'tampil_kompleks' => $bank->tampil_kompleks ?? 0,
                    'tampil_jodohkan' => $bank->tampil_jodohkan ?? 0,
                    'tampil_isian' => $bank->tampil_isian ?? 0,
                    'tampil_urutan' => $bank->tampil_urutan ?? 0,
                    'tampil_cloze' => $bank->tampil_cloze ?? 0,
                    'bobot_pg' => $bank->bobot_pg ?? 0,
                    'bobot_esai' => $bank->bobot_esai ?? 0,
                    'bobot_kompleks' => $bank->bobot_kompleks ?? 0,
                    'bobot_jodohkan' => $bank->bobot_jodohkan ?? 0,
                    'bobot_isian' => $bank->bobot_isian ?? 0,
                    'bobot_urutan' => $bank->bobot_urutan ?? 0,
                    'bobot_cloze' => $bank->bobot_cloze ?? 0,
                    'opsi' => $bank->opsi ?? 0,
                    'status' => $bank->status ?? 0,
                    'status_soal' => $bank->status_soal ?? 0,
                    'soal_agama' => $bank->soal_agama ?? null,
                    'deskripsi' => $bank->deskripsi ?? '',
                    'subtes_code' => $bank->subtes_code ?? 'GENERAL',
                    'subtes_nama' => $bank->subtes_nama ?? null,
                    'meta_mapel_nama' => $mapel ? ($mapel->nama_mapel ?? null) : null,
                    'meta_mapel_kode' => $mapel ? ($mapel->kode ?? null) : null,
                    'meta_guru_nama' => $guru ? ($guru->nama_guru ?? null) : null,
                    'meta_guru_nip' => $guru ? ($guru->nip ?? null) : null,
                ],
                'soal_list' => array_map(function ($s) {
                    unset($s['id_soal'], $s['bank_id'], $s['created_on'], $s['updated_on']);
                    return $s;
                }, $soals),
                'kartu_list' => array_map(function ($k) {
                    $src = (int) $k['id_kartu'];
                    unset($k['id_kartu'], $k['uuid'], $k['submitted_at'], $k['approved_at'], $k['approved_by'], $k['approval_note'], $k['kepsek_approved_at'], $k['kepsek_approved_by'], $k['kepsek_note'], $k['created_by'], $k['updated_by'], $k['created_at'], $k['updated_at'], $k['deleted_at']);
                    $k['src_id_kartu'] = $src;
                    return $k;
                }, $kartuRows),
                'kartu_item_list' => array_map(function ($it) {
                    $srcItem = (int) $it['id_item'];
                    $srcKartu = (int) $it['kartu_id'];
                    unset($it['id_item'], $it['kartu_id'], $it['bank_id'], $it['soal_id'], $it['created_at'], $it['updated_at']);
                    $it['src_id_item'] = $srcItem;
                    $it['src_kartu_id'] = $srcKartu;
                    return $it;
                }, $kartuItemRows),
                'media_files' => $mediaList
            ];

            $writeUploadsDir = WRITEPATH . 'uploads';
            if (!is_dir($writeUploadsDir)) {
                @mkdir($writeUploadsDir, 0777, true);
            }
            $tempZipPath = $writeUploadsDir . '/backup_' . uniqid('', true) . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return redirect()->to('cbtbanksoal')->with('swal_error', 'Gagal membuat berkas kompresi ZIP backup.');
            }

            $zip->addFromString('bank_soal.json', json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $uploadDir = FCPATH . 'uploads/bank_soal/';
            foreach ($mediaList as $mediaFile) {
                $filePath = $uploadDir . $mediaFile;
                if (file_exists($filePath) && is_file($filePath)) {
                    $zip->addFile($filePath, 'media/' . $mediaFile);
                }
            }
            $zip->close();

            $bankKodeClean = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)($bank->bank_kode ?? 'BANK'));
            $downloadName = 'Backup_BankSoal_' . $bankKodeClean . '_' . date('Ymd_His') . '.zip';

            if (isset($this->logging) && method_exists($this->logging, 'saveLog')) {
                $this->logging->saveLog(3, 'Backup bank soal: ' . ($bank->bank_kode ?? ''));
            }

            register_shutdown_function(function () use ($tempZipPath) {
                if (is_file($tempZipPath)) {
                    @unlink($tempZipPath);
                }
            });

            return $this->response->download($tempZipPath, null)->setFileName($downloadName);
        } catch (\Throwable $th) {
            log_message('error', '[Cbtbanksoal::exportBackup] ' . $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
            return redirect()->to('cbtbanksoal')->with('swal_error', 'Terjadi kesalahan sistem saat melakukan backup: ' . $th->getMessage());
        }
    }

    /**
     * Security Helper: Validasi nama file media (mencegah Zip Slip / Directory Traversal & Malware extensions)
     */
    private function isSafeMediaFilename(string $filename): bool
    {
        if (empty($filename)) {
            return false;
        }

        // Block directory traversal attempts
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return false;
        }

        // Whitelist safe extensions only
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'mp3', 'wav', 'ogg', 'mp4', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($ext, $allowedExts, true)) {
            return false;
        }

        // Explicitly reject any script or executable pattern
        $forbiddenPattern = '/\.(php|phtml|php\d*|phar|htaccess|htpasswd|pl|py|cgi|sh|bash|exe|dll|asp|aspx|jsp|js|html|htm|xhtml|svgz|cmd|bat|vbs|wsf|com|scr)$/i';
        if (preg_match($forbiddenPattern, $filename)) {
            return false;
        }

        return true;
    }

    /**
     * Security Helper: Inspect file content for PHP code injection or malicious payloads
     */
    private function isSafeMediaContent(string $filename, string $content): bool
    {
        if (empty($content)) {
            return false;
        }

        // Block any embedded PHP tags or script execution in media
        if (preg_match('/<\?(?:php|=)|<script[\s>]/i', $content)) {
            return false;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Deep validation for image files
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
            $imgInfo = @getimagesizefromstring($content);
            if ($imgInfo === false || empty($imgInfo[0]) || empty($imgInfo[1])) {
                return false; // Not a legitimate image
            }
        }

        // Deep validation for SVG files (check for inline script / event handlers)
        if ($ext === 'svg') {
            if (preg_match('/<script|on\w+\s*=/i', $content)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Security Helper: Sanitize text / HTML content to prevent XSS payloads in manifest JSON
     */
    private function sanitizeTextContent(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Strip executable HTML elements
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
        $text = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $text);
        $text = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $text);
        $text = preg_replace('/<embed\b[^>]*>(.*?)<\/embed>/is', '', $text);
        $text = preg_replace('/<applet\b[^>]*>(.*?)<\/applet>/is', '', $text);

        // Strip inline event handler attributes (onload, onerror, onclick, etc.)
        $text = preg_replace('/\s+on[a-z]+\s*=\s*(?:["\'][^"\']*["\']|[^\s>]+)/i', '', $text);

        // Replace javascript: protocol in links
        $text = preg_replace('/href\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i', 'href="#"', $text);

        return $text;
    }

    /**
     * Whitelist kolom cbt_soal yang boleh di-restore dari manifest backup.
     * Kolom sistem/statistik/sync/sensitivitas ID sengaja TIDAK disertakan.
     */
    private function soalRestoreColumns(): array
    {
        return [
            'tujuan_id', 'jenis', 'nomor_soal', 'file', 'file1', 'tipe_file', 'soal',
            'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e',
            'file_a', 'file_b', 'file_c', 'file_d', 'file_e',
            'jawaban', 'soal_hash', 'tag_kesulitan', 'tampilkan', 'deskripsi',
            'kesulitan', 'timer', 'timer_menit', 'subtes_code', 'bobot_irt', 'tipe_soal', 'irt_a',
        ];
    }

    /**
     * Whitelist kolom cbt_kartu_soal yang boleh di-restore (mapel_id/tp_id/smt_id ditangani khusus).
     */
    private function kartuRestoreColumns(): array
    {
        return [
            'judul', 'deskripsi', 'kelas_id', 'kelas_ids', 'id_jenis',
            'status', 'workflow_status', 'approval_level',
        ];
    }

    /**
     * Whitelist kolom cbt_kartu_soal_item yang boleh di-restore (kartu_id/bank_id/soal_id ditangani khusus).
     */
    private function kartuItemRestoreColumns(): array
    {
        return [
            'nomor', 'jenis_soal', 'id_tujuan', 'bentuk_soal', 'cp', 'materi', 'tujuan',
            'indikator_soal', 'indikator_pencapaian', 'tingkat_kesulitan', 'topik',
            'level_kognitif', 'estimasi_waktu', 'catatan',
            'butir_soal', 'opsi_json', 'jawaban_benar_json', 'pembahasan',
        ];
    }

    /**
     * Normalisasi kode subtes terhadap daftar kode resmi; nilai tak dikenal -> GENERAL.
     */
    private function normalizeSubtesCode(?string $code): string
    {
        $allowed = [
            'BIN', 'MTK', 'BING', 'FIS', 'KIM', 'BIO', 'GEO', 'EKO', 'SEJ', 'SOS',
            'PPN', 'INF', 'KEAHLIAN', 'GENERAL',
            'VERBAL', 'KUANTITATIF', 'PENALARAN', 'NUMERASI', 'LITERASI', 'PSIKOMETRI',
        ];
        $code = strtoupper(trim((string) $code));
        return in_array($code, $allowed, true) ? $code : 'GENERAL';
    }

    /**
     * Restore Bank Soal dari berkas ZIP backup (Dengan Proteksi Keamanan Malware & Zip-Slip)
     */
    public function doRestore()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Sesi telah berakhir. Silakan login kembali.']);
            }

            $file = $this->request->getFile('backup_file');
            if (!$file || !$file->isValid()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Berkas backup tidak ditemukan atau tidak valid.']);
            }

            $ext = strtolower($file->getClientExtension());
            if ($ext !== 'zip') {
                return $this->response->setJSON(['status' => false, 'message' => 'Berkas backup harus berformat .ZIP']);
            }

            if ($file->getSize() > 50 * 1024 * 1024) {
                return $this->response->setJSON(['status' => false, 'message' => 'Ukuran berkas ZIP melebihi batas maksimal (50MB).']);
            }

            $tempPath = $file->getTempName();
            $zip = new \ZipArchive();
            if ($zip->open($tempPath) !== true) {
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal membaca berkas ZIP. File mungkin korup atau tidak valid.']);
            }

            if ($zip->numFiles > 500) {
                $zip->close();
                return $this->response->setJSON(['status' => false, 'message' => 'Jumlah file dalam berkas ZIP melebihi batas aman (Maks 500 file).']);
            }

            $totalUncompressedSize = 0;
            for ($k = 0; $k < $zip->numFiles; $k++) {
                $stat = $zip->statIndex($k);
                if ($stat) {
                    $totalUncompressedSize += ($stat['size'] ?? 0);
                }
            }
            if ($totalUncompressedSize > 100 * 1024 * 1024) {
                $zip->close();
                return $this->response->setJSON(['status' => false, 'message' => 'Ukuran terkompresi ZIP melebihi batas aman 100MB (Zip bomb guard).']);
            }

            $jsonContent = $zip->getFromName('bank_soal.json');
            if (!$jsonContent) {
                $zip->close();
                return $this->response->setJSON(['status' => false, 'message' => 'Berkas backup tidak valid. Manifest bank_soal.json tidak ditemukan.']);
            }

            $data = json_decode($jsonContent, true);
            if (!$data || !isset($data['bank_soal']) || !isset($data['soal_list'])) {
                $zip->close();
                return $this->response->setJSON(['status' => false, 'message' => 'Struktur manifest bank_soal.json tidak valid.']);
            }

            $bankMeta = $data['bank_soal'];
            $soalList = $data['soal_list'];

            $uploadDir = FCPATH . 'uploads/bank_soal/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $realUploadDir = realpath($uploadDir);

            $extractedMediaCount = 0;
            $writtenMedia = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (strpos($filename, 'media/') === 0 && strlen($filename) > 6) {
                    $baseName = basename($filename);

                    if (isset($writtenMedia[$baseName])) {
                        log_message('warning', '[doRestore] Duplicate media name skipped (hindari overwrite): ' . $filename);
                        continue;
                    }

                    if (!$this->isSafeMediaFilename($baseName)) {
                        log_message('warning', '[doRestore] Blocked suspicious file extraction: ' . $filename);
                        continue;
                    }

                    $stream = $zip->getStream($filename);
                    if ($stream) {
                        $content = stream_get_contents($stream);
                        fclose($stream);

                        if (!$this->isSafeMediaContent($baseName, $content)) {
                            log_message('warning', '[doRestore] Blocked file with malicious content: ' . $baseName);
                            continue;
                        }

                        $targetPath = $uploadDir . $baseName;

                        $realTargetPath = realpath(dirname($targetPath)) ?: dirname($targetPath);
                        if ($realUploadDir && strpos($realTargetPath, $realUploadDir) !== 0) {
                            log_message('warning', '[doRestore] Path traversal detected: ' . $targetPath);
                            continue;
                        }

                        file_put_contents($targetPath, $content);
                        $writtenMedia[$baseName] = true;
                        $extractedMediaCount++;
                    }
                }
            }
            $zip->close();

            $db = \Config\Database::connect();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            $mapelId = (int) ($this->request->getPost('id_mapel') ?? 0);
            if ($mapelId <= 0 && !empty($bankMeta['meta_mapel_kode'])) {
                $mapelObj = $db->table('master_mapel')->where('kode', $bankMeta['meta_mapel_kode'])->get()->getRow();
                if ($mapelObj) {
                    $mapelId = (int) $mapelObj->id_mapel;
                }
            }
            if ($mapelId <= 0) {
                $mapelId = (int) ($bankMeta['bank_mapel_id'] ?? 0);
            }
            if ($mapelId <= 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Mata pelajaran target tidak dapat ditentukan. Pilih mapel pada formulir restore atau pastikan manifest backup memuat kode mapel yang valid.']);
            }
            $mapelExists = $db->table('master_mapel')->where('id_mapel', $mapelId)->countAllResults();
            if ($mapelExists == 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Mata pelajaran target (id ' . $mapelId . ') tidak ditemukan di sistem ini. Pilih mapel lain pada formulir restore.']);
            }

            $guruId = $this->getCurrentGuruId();
            if (!$guruId) {
                $guruId = (int) ($bankMeta['bank_guru_id'] ?? 0);
                if ($guruId > 0) {
                    $guruExists = $db->table('master_guru')->where('id_guru', $guruId)->countAllResults();
                    if ($guruExists == 0) {
                        $guruId = 0;
                    }
                }
            }
            if ($guruId <= 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Pemilik bank soal (guru) tidak dapat ditentukan. Pastikan Anda terhubung sebagai guru atau manifest backup memuat bank_guru_id yang valid.']);
            }

            $origKode = $bankMeta['bank_kode'] ?? ('BANK_' . date('YmdHis'));
            $targetKode = $origKode;
            $exists = $db->table('cbt_bank_soal')->where('bank_kode', $targetKode)->countAllResults();
            if ($exists > 0) {
                $targetKode = $origKode . '_RESTORE_' . $this->generateUniqueId();
            }

            $newBankData = [
                'bank_jenis_id'   => (int) ($bankMeta['bank_jenis_id'] ?? 1),
                'bank_kode'       => $targetKode,
                'bank_level'      => $bankMeta['bank_level'] ?? '10',
                'bank_kelas'      => is_array($bankMeta['bank_kelas'] ?? null) ? json_encode($bankMeta['bank_kelas']) : ($bankMeta['bank_kelas'] ?? '[]'),
                'bank_mapel_id'   => $mapelId,
                'bank_jurusan_id' => (int) ($bankMeta['bank_jurusan_id'] ?? 0),
                'bank_guru_id'    => $guruId,
                'bank_nama'       => $this->sanitizeTextContent($bankMeta['bank_nama'] ?? 'Restored Bank Soal'),
                'kkm'             => (int) ($bankMeta['kkm'] ?? 75),
                'jml_soal'        => (int) ($bankMeta['jml_soal'] ?? 0),
                'jml_esai'        => (int) ($bankMeta['jml_esai'] ?? 0),
                'tampil_pg'       => (int) ($bankMeta['tampil_pg'] ?? 0),
                'tampil_esai'     => (int) ($bankMeta['tampil_esai'] ?? 0),
                'bobot_pg'        => (int) ($bankMeta['bobot_pg'] ?? 0),
                'bobot_esai'      => (int) ($bankMeta['bobot_esai'] ?? 0),
                'opsi'            => (int) ($bankMeta['opsi'] ?? 5),
                'date'            => date('Y-m-d H:i:s'),
                'status'          => 0,
                'soal_agama'      => $bankMeta['soal_agama'] ?? '-',
                'id_tp'           => $tp ? $tp->id_tp : 1,
                'id_smt'          => $smt ? $smt->id_smt : 1,
                'deskripsi'       => $this->sanitizeTextContent($bankMeta['deskripsi'] ?? ''),
                'jml_kompleks'    => (int) ($bankMeta['jml_kompleks'] ?? 0),
                'tampil_kompleks' => (int) ($bankMeta['tampil_kompleks'] ?? 0),
                'bobot_kompleks'  => (int) ($bankMeta['bobot_kompleks'] ?? 0),
                'jml_jodohkan'   => (int) ($bankMeta['jml_jodohkan'] ?? 0),
                'tampil_jodohkan' => (int) ($bankMeta['tampil_jodohkan'] ?? 0),
                'bobot_jodohkan'  => (int) ($bankMeta['bobot_jodohkan'] ?? 0),
                'jml_isian'       => (int) ($bankMeta['jml_isian'] ?? 0),
                'tampil_isian'    => (int) ($bankMeta['tampil_isian'] ?? 0),
                'bobot_isian'     => (int) ($bankMeta['bobot_isian'] ?? 0),
                'status_soal'     => 0,
                'tampil_urutan'   => (int) ($bankMeta['tampil_urutan'] ?? 0),
                'bobot_urutan'    => (float) ($bankMeta['bobot_urutan'] ?? 0.00),
                'tampil_cloze'    => (int) ($bankMeta['tampil_cloze'] ?? 0),
                'bobot_cloze'     => (float) ($bankMeta['bobot_cloze'] ?? 0.00),
                'subtes_code'     => $this->normalizeSubtesCode($bankMeta['subtes_code'] ?? 'GENERAL'),
                'subtes_nama'     => $bankMeta['subtes_nama'] ?? null,
            ];

            $db->transBegin();
            $db->table('cbt_bank_soal')->insert($newBankData);
            $newBankId = $db->insertID();

            $insertedSoalCount = 0;
            $allowedSoalCols = array_flip($this->soalRestoreColumns());
            if (is_array($soalList) && count($soalList) > 0) {
                foreach ($soalList as $s) {
                    $soalRow = [];
                    foreach ($s as $key => $val) {
                        if (isset($allowedSoalCols[$key])) {
                            $soalRow[$key] = $val;
                        }
                    }
                    $soalRow['bank_id'] = $newBankId;
                    $soalRow['mapel_id'] = $mapelId;
                    $soalRow['created_on'] = time();
                    $soalRow['updated_on'] = time();

                    foreach (['soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'deskripsi'] as $txtCol) {
                        if (isset($soalRow[$txtCol])) {
                            $soalRow[$txtCol] = $this->sanitizeTextContent((string) $soalRow[$txtCol]);
                        }
                    }

                    foreach (['file', 'file_a', 'file_b', 'file_c', 'file_d', 'file_e'] as $fCol) {
                        if (isset($soalRow[$fCol]) && is_string($soalRow[$fCol]) && trim($soalRow[$fCol]) !== '') {
                            $soalRow[$fCol] = basename(trim($soalRow[$fCol]));
                        }
                    }
                    if (isset($soalRow['file1']) && is_string($soalRow['file1'])) {
                        $v = trim($soalRow['file1']);
                        if ($v !== '') {
                            if ($v[0] === '[' || $v[0] === '{') {
                                $dec = json_decode($v, true);
                                if (is_array($dec)) {
                                    $soalRow['file1'] = json_encode(array_map(function ($d) {
                                        return is_string($d) ? basename($d) : $d;
                                    }, $dec));
                                } else {
                                    $soalRow['file1'] = basename($v);
                                }
                            } else {
                                $soalRow['file1'] = basename($v);
                            }
                        }
                    }

                    if (isset($soalRow['jenis'])) {
                        $soalRow['jenis'] = (int) $soalRow['jenis'];
                    } else {
                        $soalRow['jenis'] = 1;
                    }
                    if ($soalRow['jenis'] < 1 || $soalRow['jenis'] > 7) {
                        $soalRow['jenis'] = 1;
                    }

                    if (isset($soalRow['tipe_soal']) && !in_array($soalRow['tipe_soal'], ['pg', 'pgk', 'pgkk'], true)) {
                        $soalRow['tipe_soal'] = 'pg';
                    }
                    if (isset($soalRow['tag_kesulitan']) && !in_array($soalRow['tag_kesulitan'], ['mudah', 'sedang', 'sulit'], true)) {
                        $soalRow['tag_kesulitan'] = null;
                    }
                    $soalRow['subtes_code'] = $this->normalizeSubtesCode($soalRow['subtes_code'] ?? 'GENERAL');

                    if (isset($soalRow['tampilkan'])) {
                        $soalRow['tampilkan'] = (int) $soalRow['tampilkan'] ? 1 : 0;
                    } else {
                        $soalRow['tampilkan'] = 1;
                    }
                    if (!isset($soalRow['bobot_irt'])) $soalRow['bobot_irt'] = 1.000;
                    if (!isset($soalRow['irt_a'])) $soalRow['irt_a'] = 1.000;
                    if (!isset($soalRow['deskripsi']) || $soalRow['deskripsi'] === null) $soalRow['deskripsi'] = '';
                    if (!isset($soalRow['kesulitan']) || $soalRow['kesulitan'] === null) $soalRow['kesulitan'] = 1;
                    if (!isset($soalRow['timer']) || $soalRow['timer'] === null) $soalRow['timer'] = 0;
                    if (!isset($soalRow['timer_menit']) || $soalRow['timer_menit'] === null) $soalRow['timer_menit'] = 0;

                    $hasContent = !empty(trim((string) ($soalRow['soal'] ?? '')));
                    $hasKey = !empty(trim((string) ($soalRow['jawaban'] ?? '')));
                    if ($soalRow['tampilkan'] && !$hasContent && !$hasKey) {
                        $soalRow['tampilkan'] = 0;
                    }

                    if (isset($soalRow['tujuan_id']) && (int) $soalRow['tujuan_id'] > 0) {
                        $tujuanExists = $db->table('e_tujuan_pembelajaran')->where('id_tujuan', (int) $soalRow['tujuan_id'])->countAllResults();
                        $soalRow['tujuan_id'] = $tujuanExists ? (int) $soalRow['tujuan_id'] : null;
                    } else {
                        $soalRow['tujuan_id'] = null;
                    }

                    $db->table('cbt_soal')->insert($soalRow);
                    $insertedSoalCount++;
                }
            }

            $insertedKartuCount = 0;
            $insertedKartuItemCount = 0;
            $kartuIdMap = [];
            if (isset($data['kartu_list']) && is_array($data['kartu_list']) && isset($data['kartu_item_list']) && is_array($data['kartu_item_list'])) {
                $allowedKartuCols = array_flip($this->kartuRestoreColumns());
                foreach ($data['kartu_list'] as $k) {
                    $srcKartuId = (int) ($k['src_id_kartu'] ?? 0);
                    $kartuRow = [];
                    foreach ($k as $key => $val) {
                        if (isset($allowedKartuCols[$key])) {
                            $kartuRow[$key] = $val;
                        }
                    }
                    $kartuRow['uuid'] = sprintf('%s-%s-%s-%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(6)));
                    $kartuRow['mapel_id'] = $mapelId;
                    $kartuRow['tp_id'] = $tp ? $tp->id_tp : 1;
                    $kartuRow['smt_id'] = $smt ? $smt->id_smt : 1;
                    $kartuRow['judul'] = $this->sanitizeTextContent((string) ($kartuRow['judul'] ?? ''));
                    if ($kartuRow['judul'] === '') {
                        $kartuRow['judul'] = 'Restored Kartu Soal';
                    }
                    if (isset($kartuRow['deskripsi'])) {
                        $kartuRow['deskripsi'] = $this->sanitizeTextContent((string) $kartuRow['deskripsi']);
                    }
                    if (isset($kartuRow['kelas_ids']) && is_array($kartuRow['kelas_ids'])) {
                        $kartuRow['kelas_ids'] = json_encode($kartuRow['kelas_ids']);
                    }
                    if (isset($kartuRow['kelas_id']) && (int) $kartuRow['kelas_id'] > 0) {
                        $kelasExists = $db->table('master_kelas')->where('id_kelas', (int) $kartuRow['kelas_id'])->countAllResults();
                        if ($kelasExists == 0) {
                            $kartuRow['kelas_id'] = null;
                        }
                    }
                    if (isset($kartuRow['id_jenis']) && (int) $kartuRow['id_jenis'] > 0) {
                        $jenisExists = $db->table('cbt_jenis')->where('id_jenis', (int) $kartuRow['id_jenis'])->countAllResults();
                        if ($jenisExists == 0) {
                            $kartuRow['id_jenis'] = null;
                        }
                    }
                    if (isset($kartuRow['status'])) {
                        $kartuRow['status'] = ((int) $kartuRow['status'] === 2) ? 2 : 1;
                    } else {
                        $kartuRow['status'] = 1;
                    }
                    if (empty($kartuRow['workflow_status'])) {
                        $kartuRow['workflow_status'] = 'draft';
                    }

                    $db->table('cbt_kartu_soal')->insert($kartuRow);
                    $newKartuId = $db->insertID();
                    if ($srcKartuId > 0) {
                        $kartuIdMap[$srcKartuId] = $newKartuId;
                    }
                    $insertedKartuCount++;
                }

                if (!empty($kartuIdMap)) {
                    $allowedItemCols = array_flip($this->kartuItemRestoreColumns());
                    foreach ($data['kartu_item_list'] as $it) {
                        $srcKartuId = (int) ($it['src_kartu_id'] ?? 0);
                        if (!isset($kartuIdMap[$srcKartuId])) {
                            continue;
                        }
                        $itemRow = [];
                        foreach ($it as $key => $val) {
                            if (isset($allowedItemCols[$key])) {
                                $itemRow[$key] = $val;
                            }
                        }
                        $itemRow['kartu_id'] = $kartuIdMap[$srcKartuId];
                        $itemRow['bank_id'] = $newBankId;
                        $itemRow['soal_id'] = null;
                        if (isset($itemRow['jenis_soal'])) {
                            $itemRow['jenis_soal'] = (int) $itemRow['jenis_soal'];
                        } else {
                            $itemRow['jenis_soal'] = 1;
                        }
                        if ($itemRow['jenis_soal'] < 1 || $itemRow['jenis_soal'] > 7) {
                            $itemRow['jenis_soal'] = 1;
                        }
                        if (isset($itemRow['id_tujuan']) && (int) $itemRow['id_tujuan'] > 0) {
                            $tujuanExists = $db->table('e_tujuan_pembelajaran')->where('id_tujuan', (int) $itemRow['id_tujuan'])->countAllResults();
                            $itemRow['id_tujuan'] = $tujuanExists ? (int) $itemRow['id_tujuan'] : null;
                        } else {
                            $itemRow['id_tujuan'] = null;
                        }
                        foreach (['butir_soal', 'pembahasan', 'opsi_json', 'jawaban_benar_json'] as $tc) {
                            if (isset($itemRow[$tc]) && is_string($itemRow[$tc])) {
                                $itemRow[$tc] = $this->sanitizeTextContent($itemRow[$tc]);
                            }
                        }
                        $db->table('cbt_kartu_soal_item')->insert($itemRow);
                        $insertedKartuItemCount++;
                    }
                }
            }

            $linkKartuId = null;
            $srcBankKartu = (int) ($bankMeta['kartu_soal_id'] ?? 0);
            if ($srcBankKartu > 0 && isset($kartuIdMap[$srcBankKartu])) {
                $linkKartuId = $kartuIdMap[$srcBankKartu];
            }
            if ($linkKartuId) {
                $db->table('cbt_bank_soal')->where('id_bank', $newBankId)->update(['kartu_soal_id' => $linkKartuId]);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan data restore ke database.']);
            }

            $db->transCommit();

            $this->syncBankStatus($newBankId);
            $this->logging->saveLog(3, 'Restore bank soal aman: ' . $targetKode);

            return $this->response->setJSON([
                'status' => true,
                'message' => "Bank soal {$targetKode} berhasil di-restore secara aman dengan {$insertedSoalCount} soal, {$insertedKartuCount} kartu soal, {$insertedKartuItemCount} item kartu, dan {$extractedMediaCount} file media.",
                'id_bank' => $newBankId
            ]);
        } catch (\Throwable $th) {
            log_message('error', '[Cbtbanksoal::doRestore] ' . $th->getMessage() . ' ' . $th->getTraceAsString());
            return $this->response->setJSON(['status' => false, 'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()]);
        }
    }
}

