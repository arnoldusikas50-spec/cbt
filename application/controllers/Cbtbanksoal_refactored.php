<?php
/**
 * REFACTORED VERSION of Cbtbanksoal Controller
 * 
 * Changes:
 * 1. Extract business logic to BankSoalService
 * 2. Standardize JSON responses
 * 3. Remove DB queries from controller
 * 4. Reduce duplication with helper methods
 */

class Cbtbanksoal extends MY_Controller
{
    private $bankSoalService;

    public function __construct()
    {
        parent::__construct();
        
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
            show_error('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        
        $this->load->library('upload');
        $this->load->library(['datatables', 'form_validation', 'BankSoalService']);
        $this->form_validation->set_error_delimiters('', '');
        
        $this->bankSoalService = new BankSoalService();
    }

    /**
     * Index page - Display bank soal list
     */
    public function index()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data = $this->prepareBaseData($user, $setting, $tp, $smt);
        
        $mode = $this->input->get('mode');
        $type = $this->input->get('type');
        $data['mode'] = $mode == null ? '1' : $mode;

        if ($this->auth->is_admin()) {
            $this->handleAdminView($data, $type, $tp, $smt);
        } else {
            $this->handleGuruView($data, $type, $tp, $smt, $user);
        }
    }

    /**
     * Prepare base data for views
     */
    private function prepareBaseData($user, $setting, $tp, $smt)
    {
        return [
            'user' => $user,
            'judul' => 'Bank Soal',
            'subjudul' => 'Soal',
            'setting' => $setting,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'levels' => $this->dropdown->getAllLevel($setting->jenjang)
        ];
    }

    /**
     * Handle admin view
     */
    private function handleAdminView(&$data, $type, $tp, $smt)
    {
        $data['mapels'] = $this->dropdown->getAllMapel();
        $data['gurus'] = $this->dropdown->getAllGuru();
        $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
        $data['filters'] = ['0' => 'Semua', '1' => 'Guru', '2' => 'Mapel', '3' => 'Level'];
        $data['id_filter'] = $type == null ? '' : $type;

        // Get filtered banks
        $filterResult = $this->getFilteredBanks($type, $tp->id_tp, $smt->id_smt);
        $data = array_merge($data, $filterResult);

        $this->render_view(1, 'cbt/banksoal/data', $data);
    }

    /**
     * Handle guru view
     */
    private function handleGuruView(&$data, $type, $tp, $smt, $user)
    {
        $this->load->model('Kelas_model', 'kelas');
        
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        $nguru[$guru->id_guru] = $guru->nama_guru;
        
        $data['guru'] = $guru;
        $data['gurus'] = $nguru;
        $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);

        $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
        $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

        $arrMapel = [];
        if ($mapel != null) {
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
            }
        }
        $data['mapels'] = $arrMapel;
        $data['filters'] = ['0' => 'Semua', '2' => 'Mapel', '3' => 'Level'];
        $data['id_filter'] = $type == null ? '' : $type;

        // Get filtered banks for guru
        $filterResult = $this->getFilteredBanks($type, $tp->id_tp, $smt->id_smt, $guru->id_guru);
        $data = array_merge($data, $filterResult);

        $this->render_view(2, 'cbt/banksoal/data', $data);
    }

    /**
     * Get filtered banks based on type
     */
    private function getFilteredBanks($type, $id_tp, $id_smt, $id_guru = null)
    {
        $result = [
            'id_guru' => '',
            'id_mapel' => '',
            'id_level' => '',
            'banks' => [],
            'total_siswa' => []
        ];

        if ($type == null) {
            return $result;
        }

        $filter_id_guru = null;
        $filter_id_mapel = null;
        $filter_id_level = null;

        switch ($type) {
            case '0':
                // All banks
                break;
            case '1':
                $id_guru = $this->input->get('id');
                $result['id_guru'] = $id_guru;
                $filter_id_guru = $id_guru;
                break;
            case '2':
                $id_mapel = $this->input->get('id');
                $result['id_mapel'] = $id_mapel;
                $filter_id_mapel = $id_mapel;
                break;
            case '3':
                $id_level = $this->input->get('id');
                $result['id_level'] = $id_level;
                $filter_id_level = $id_level;
                break;
        }

        // Use service to get banks with usage
        if ($id_guru !== null || $type === '0') {
            $bankData = $this->bankSoalService->getBanksWithUsage(
                $id_tp,
                $id_smt,
                $filter_id_guru ?? $id_guru,
                $filter_id_mapel,
                $filter_id_level
            );
            
            $result['banks'] = $bankData['banks'];
            $result['total_siswa'] = $bankData['total_siswa'];
        }

        return $result;
    }

    /**
     * Edit bank - with JWT security
     */
    public function editBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $token_bank = $this->input->get('id_bank', true);
        $token_guru = $this->input->get('id_guru', true);
        
        // Validate tokens
        if (!$token_bank || !$token_guru) {
            $this->output_json(['status' => false, 'message' => 'Missing required parameters']);
            return;
        }
        
        // Decode JWT tokens
        $id_bank = jwt_decode_param($token_bank, 'id_bank');
        $id_guru = jwt_decode_param($token_guru, 'id_guru');
        
        if (!$id_bank || !$id_guru) {
            $this->output_json(['status' => false, 'message' => 'Invalid or expired token']);
            return;
        }
        
        $setting = $this->dashboard->getSetting();
        $user = $this->auth->user();
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Edit Bank Soal',
            'subjudul' => 'Edit Bank Soal',
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'bulan' => $this->dropdown->getBulan(),
            'is_admin' => $this->auth->is_admin(),
            'setting' => $setting,
            'jenis' => $this->cbt->getAllJenisUjian(),
            'jurusan' => $this->cbt->getAllJurusan(),
            'level' => $this->dropdown->getAllLevel($setting->jenjang),
            'kelas' => $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt)
        ];

        $bank = $this->cbt->getDataBankById($id_bank);
        if (!$bank) {
            $this->output_json(['status' => false, 'message' => 'Bank soal not found']);
            return;
        }
        
        $bank->bank_kelas = $this->maybe_unserialize($bank->bank_kelas ?? '[]');
        $data['bank'] = $bank;
        $data['mapel_agama'] = $this->master->getAgamaSiswa();

        if ($this->auth->is_admin()) {
            $data['id_guru'] = $id_guru;
            $data['gurus'] = $this->dropdown->getAllGuru();
            $data['mapel'] = $this->dropdown->getAllMapel();
            $mapel_guru = $this->kelas->getGuruMapelKelas($id_guru, $tp->id_tp, $smt->id_smt);
            $data['mapel_guru'] = $mapel_guru;
            $this->render_view(1, 'cbt/banksoal/add', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            if ($mapel !== false) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                }
            }

            $data['gurus'] = $nguru;
            $data['mapel_guru'] = $mapel_guru;
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;
            $data['mapel'] = $arrMapel;
            $this->render_view(2, 'cbt/banksoal/add', $data);
        }
    }

    /**
     * Delete bank - with validation
     */
    public function deleteBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $id = $this->input->get('id_bank', true);
        
        // Use service for validation
        $validation = $this->bankSoalService->validateBankDeletion($id);
        
        if (!$validation['can_delete']) {
            $this->output_json(['status' => false, 'message' => $validation['message']]);
            return;
        }

        // Delete soal first, then bank
        if ($this->master->delete('cbt_soal', $id, 'bank_id')) {
            if ($this->master->delete('cbt_bank_soal', $id, 'id_bank')) {
                $this->logging->saveLog($this->LOG_TYPE->DELETE, 'menghapus bank soal');
                $this->output_json(['status' => true, 'message' => 'berhasil']);
                return;
            }
        }
        
        $this->output_json(['status' => false, 'message' => 'Gagal menghapus bank soal']);
    }

    /**
     * Delete multiple banks
     */
    public function deleteAllBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $ids = json_decode($this->input->post('ids', true));
        
        // Use service for validation
        $validation = $this->bankSoalService->validateBankDeletion($ids);
        
        if (!$validation['can_delete']) {
            $this->output_json(['status' => false, 'message' => $validation['message']]);
            return;
        }

        if ($this->master->delete('cbt_soal', $ids, 'bank_id')) {
            if ($this->master->delete('cbt_bank_soal', $ids, 'id_bank')) {
                $this->logging->saveLog($this->LOG_TYPE->DELETE, 'menghapus semua bank soal');
                $this->output_json(['status' => true, 'message' => 'berhasil']);
                return;
            }
        }
        
        $this->output_json(['status' => false, 'message' => 'Gagal menghapus bank soal']);
    }

    /**
     * Copy bank soal
     */
    public function copyBankSoal($id_bank)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Use service to copy
        $result = $this->bankSoalService->copyBankSoal($id_bank, $tp->id_tp, $smt->id_smt);
        
        if ($result) {
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menyalin bank soal');
        }
        
        $this->output_json($result);
    }

    /**
     * Add bank form
     */
    public function addBank()
    {
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Bank Soal',
            'subjudul' => 'Buat Bank Soal',
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'setting' => $setting,
            'bank' => json_decode(json_encode($this->cbt->dummy($setting->jenjang))),
            'jenis' => $this->cbt->getAllJenisUjian(),
            'jurusan' => $this->cbt->getAllJurusan(),
            'level' => $this->dropdown->getAllLevel($setting->jenjang),
            'mapel_agama' => $this->master->getAgamaSiswa(),
            'is_admin' => $this->auth->is_admin()
        ];

        if ($this->auth->is_admin()) {
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['id_guru'] = '';
            $data['gurus'] = $this->dropdown->getAllGuru();
            $data['mapel'] = $this->dropdown->getAllMapel();
            $this->render_view(1, 'cbt/banksoal/add', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            
            // Use service to prepare guru data
            $guruData = $this->bankSoalService->prepareGuruBankFormData($guru, $tp->id_tp, $smt->id_smt);
            $data = array_merge($data, $guruData);
            
            $this->render_view(2, 'cbt/banksoal/add', $data);
        }
    }

    /**
     * Save bank soal
     */
    public function saveBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');
        $this->load->model('Cbt_model', 'cbt');

        if (!$this->input->post()) {
            $this->output_json(['status' => false, 'message' => 'No data posted']);
            return;
        }

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        
        // Validate kode bank if changed
        $old_kode = $this->input->post('old_kode', true);
        $bank_kode = $this->input->post('kode', true);
        
        if ($old_kode && $old_kode != $bank_kode) {
            $this->form_validation->set_rules('kode', 'Kode Bank Soal', 'required|trim|is_unique[cbt_bank_soal.bank_kode]');
            
            if (!$this->form_validation->run()) {
                $this->output_json([
                    'status' => false,
                    'errors' => form_error('kode')
                ]);
                return;
            }
        }

        // Save to database
        $this->cbt->saveBankSoal($tp->id_tp, $smt->id_smt);
        
        $id = $this->input->post('id_bank', true);
        $log_message = !$id ? 'menambah bank soal' : 'mengedit bank soal';
        $log_type = !$id ? $this->LOG_TYPE->ADD : $this->LOG_TYPE->EDIT;
        
        $this->logging->saveLog($log_type, $log_message);

        $this->output_json(['status' => true, 'message' => 'Berhasil menyimpan bank soal']);
    }

    /**
     * Buat soal page
     */
    public function buatsoal($id_bank)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $_no = $this->input->get('no', true);
        $_jns = $this->input->get('jns', true);
        $tab = $this->input->get('tab', true);

        $user = $this->auth->user();
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $setting = $this->dashboard->getSetting();

        $act_tab = $_jns != null ? $_jns : '1';
        $jenis = $tab == null ? $act_tab : $tab;

        // Use service to prepare soal data
        $soalData = $this->bankSoalService->prepareBuatSoalData($id_bank, $jenis);
        
        if (!$soalData) {
            show_404();
            return;
        }

        $data = [
            'user' => $user,
            'judul' => 'Buat Soal',
            'subjudul' => 'Buat Soal',
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'setting' => $setting,
            'p_no' => $_no != null ? $_no : '1',
            'p_jns' => $act_tab,
            'tab_active' => $jenis,
            'soal' => null,
            'jurusan' => $this->cbt->getAllJurusan(),
            'level' => $this->dropdown->getAllLevel($setting->jenjang),
            'kelas' => $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt)
        ];

        // Merge soal data from service
        $data = array_merge($data, $soalData);

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/banksoal/soal', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/banksoal/soal', $data);
        }
    }

    /**
     * Get detail bank with soals (AJAX)
     */
    public function get_detail()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');
        
        $id = $this->input->post('id_bank', true);
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $bank = $this->cbt->getDataBankById($id);
        $bank->bank_kelas = $this->maybe_unserialize($bank->bank_kelas ?? '[]');
        
        $soals = $this->cbt->getAllSoalByBank($id);
        foreach ($soals as $soal) {
            if ($soal->jenis == '2') {
                $soal->opsi_a = $this->maybe_unserialize($soal->opsi_a);
                $soal->jawaban = $this->maybe_unserialize($soal->jawaban);
            } elseif ($soal->jenis == '3') {
                $soal->jawaban = $this->maybe_unserialize($soal->jawaban);
            }
        }
        
        // Get usage statistics from service
        $terpakai = $this->bankSoalService->getBankUsageByIds([$id]);
        
        $this->output_json([
            'success' => true,
            'bank' => $bank,
            'soals' => $soals,
            'kelas' => $this->cbt->getKelas($tp->id_tp, $smt->id_smt),
            'total_siswa' => isset($terpakai[$id]) ? count($terpakai[$id]) : 0
        ]);
    }

    /**
     * Detail bank page (with JWT)
     */
    public function detail($token)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        // Decode JWT token
        $id = jwt_decode_param($token, 'id_bank');
        if (!$id) {
            show_404();
            return;
        }

        $user = $this->auth->user();
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Detail Soal',
            'subjudul' => 'Detail Soal',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'id_bank' => $id
        ];

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/banksoal/detail', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/banksoal/detail', $data);
        }
    }

    /**
     * Get mapel by guru (AJAX)
     */
    public function getMapelGuru()
    {
        $id_guru = $this->input->get('id_guru', true);
        $this->load->model('Master_model', 'master');

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        // Use service method
        $result = $this->bankSoalService->getMapelGuruData($id_guru, $tp->id_tp, $smt->id_smt);
        
        $this->output_json($result['mapel']);
    }

    /**
     * Get kelas by level (AJAX)
     */
    public function getKelasLevel()
    {
        $this->load->model('Master_model', 'master');

        $level = $this->input->get('level', true);
        $id_guru = $this->input->get('id_guru', true);
        $id_mapel = $this->input->get('mapel', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        // Use service method
        $result = $this->bankSoalService->getKelasByLevelAndMapel(
            $level, $id_guru, $id_mapel, $tp->id_tp, $smt->id_smt
        );

        $this->output_json($result);
    }

    /**
     * Get soals by bank id (AJAX)
     */
    public function get_soals($id)
    {
        $this->load->model('Cbt_model', 'cbt');
        $data = $this->cbt->getAllSoalByBank($id);
        $this->output_json($data);
    }

    /**
     * Data table method (AJAX)
     */
    public function data($guru = null)
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->getDataBank($guru), false);
    }

    /**
     * DataTable method (AJAX)
     */
    public function dataTable($guru = null)
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->getDataTableBank($guru), false);
    }

    // NOTE: Methods below require more complex refactoring
    // Keep original implementation for now, can refactor later:
    // - saveSoal() - Complex file upload logic
    // - uploadSoal() - Word document parsing
    // - hapusSoal() - File cleanup logic
    // - uploadFile() - Image upload processing
    // - upload_image() - Summernote integration
    
}

