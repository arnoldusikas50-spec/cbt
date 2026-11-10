<?php

use CodeIgniter\Router\RouteCollection;

/*
|--------------------------------------------------------------------------
| CLI Routes (database maintenance)
|--------------------------------------------------------------------------
*/
$routes->cli('cbt:dbfix', 'CbtDbFix::index');
$routes->cli('cbt:dbfix verify', 'CbtDbFix::verify');


/**
 * CBT AKM Application Routes
 * Migrated from CodeIgniter 3 by ikasmedia
 * 
 * @var RouteCollection $routes
 */

/*
|--------------------------------------------------------------------------
| Module Index
|--------------------------------------------------------------------------
| 01. Frontend Public Pages & Login
| 02. Authentication Endpoints
| 03. Student Portal (Dashboard/CBT/KBM)
| 04. Admin Portal Shell
| 05. CBT Management (Bank Soal, Jadwal, Status, Utilities)
| 06. Academic Reports & Publishing
| 07. Master Data Management
| 08. User Management (Admin/Guru/Siswa/Panitia)
| 09. Settings & System Tools
| 10. Sync & External APIs
| 11. Miscellaneous Utilities & Legacy Routes
*/

// 01. FRONTEND (SISWA) - PUBLIC ROUTES
// --------------------------------------------
$routes->get('/', 'Frontend\Home::index');
$routes->get('about', 'Frontend\Home::about');
$routes->get('help', 'Frontend\Home::help');
$routes->get('login', 'Frontend\Auth::login');
$routes->post('auth/login', 'Auth::login');
$routes->get('auth/logout', 'Auth::logout');
// Nonaktif (CBT murni): controller Frontend\Diagnostic & Frontend\Test tidak ada
// $routes->get('diagnostic', 'Frontend\Diagnostic::index');
// $routes->get('test', 'Frontend\Test::index');
// Route dbmigrate menjalankan migrasi DB. WAJIB dilindungi filter admin —
// sebelumnya route ini publik sehingga siapa pun bisa memicu perubahan
// skema pada database produksi.
$routes->get('dbmigrate', 'DbMigrate::index', ['filter' => 'admin']);

// 02. AUTHENTICATION PORTAL (ADMIN/GURU/PANITIA)
// --------------------------------------------
$routes->get('auth', 'Backend\Auth::login');
$routes->get('admin/login', 'Backend\Auth::login');
$routes->get('admin/logout', 'Backend\Auth::logout');

// ============================================
// AUTHENTICATED USER ROUTES
// ============================================
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Integrasi Dapodik Routes
    $routes->group('integrasi/dapodik', static function ($routes) {
        $routes->get('/', 'Integrasi\Dapodik::index');
        $routes->post('saveSettings', 'Integrasi\Dapodik::saveSettings');
        $routes->post('testConnection', 'Integrasi\Dapodik::testConnection');
        $routes->post('syncApi', 'Integrasi\Dapodik::syncApi');
        $routes->get('clearLogs', 'Integrasi\Dapodik::clearLogs');
    });

    // Dashboard (main + AJAX)
    $routes->get('dashboard', 'Dashboard::index');
    // Legacy SaaS panel: peran admin_sekolah tidak dipakai di instalasi ini —
    // fallback Auth.php mengarah ke sini; arahkan ke dashboard agar tidak 404.
    $routes->get('sekolahpanel', static fn () => redirect()->to('dashboard'));
    $routes->group('dashboard', static function ($routes) {
        $routes->get('/', 'Dashboard::index');
        $routes->get('get_status_siswa', 'Dashboard::get_status_siswa');
        $routes->get('get_realtime_stats', 'Dashboard::get_realtime_stats');
        $routes->get('loadtoken', 'Dashboard::loadtoken');
        $routes->get('getlog', 'Dashboard::getlog');
        $routes->get('getpost', 'Dashboard::getpost');
        $routes->get('checkTokenJadwal', 'Dashboard::checkTokenJadwal');
        $routes->post('savekomentar', 'Dashboard::savekomentar');
        $routes->post('savebalasan', 'Dashboard::savebalasan');
        $routes->post('gantitahun', 'Dashboard::gantiTahun');
        $routes->post('gantisemester', 'Dashboard::gantiSemester');
        $routes->post('hapuspost', 'Dashboard::hapuspost');
        $routes->post('hapuskomentar', 'Dashboard::hapuskomentar');
        $routes->post('hapusbalasan', 'Dashboard::hapusbalasan');
        $routes->post('hapuslog', 'Dashboard::hapuslog');
    });

    // CBT Routes
    $routes->get('cbt', 'Frontend\\Cbt::index');
    $routes->get('cbt/get_cbt_siswa', 'Frontend\\Cbt::getCbtSiswa');
    $routes->get('cbt/load_token', 'Frontend\\Cbt::load_token');
    $routes->get('cbt/konfirmasi/(:segment)', 'Frontend\\Cbt::konfirmasi/$1');
    $routes->get('cbt/hasil/(:segment)', 'Frontend\\Cbt::hasilUjian/$1');
    $routes->post('cbt/validasi', 'Frontend\\Cbt::validasiJadwal');
    $routes->get('cbt/penilaian/(:segment)', 'Frontend\\Cbt::penilaian/$1');
    // ── Rate-limited endpoints (kritis saat ujian berlangsung) ──────────────
    $routes->post('cbt/save_jawaban', 'Frontend\\Cbt::saveJawaban',  ['filter' => 'cbt_ratelimit:save_jawaban,60']);
    $routes->post('cbt/toggle_ragu',  'Frontend\\Cbt::toggleRagu',   ['filter' => 'cbt_ratelimit:toggle_ragu,30']);
    $routes->post('cbt/get_soal',     'Frontend\\Cbt::getSoal',      ['filter' => 'cbt_ratelimit:get_soal,120']);
    // ────────────────────────────────────────────────────────────────────────
    $routes->post('cbt/selesai_ujian', 'Frontend\\Cbt::selesaiUjian');
    $routes->post('cbt/validasi_siswa', 'Frontend\\Cbt::validasiSiswa');
    $routes->post('cbt/save_signature', 'Frontend\\Cbt::saveSignature');
    $routes->post('cbt/hitung_pelanggaran', 'Frontend\\Cbt::hitungPelanggaran');
    $routes->post('cbt/leavecbt', 'Frontend\\Cbt::leavecbt');
    $routes->get('cbt/absensi', 'Frontend\\Cbt::absensi');

    // CBT REMEDIAL Routes
    $routes->post('cbt/cek_remedial', 'Frontend\\Cbt::cekRemedial');
    $routes->post('cbt/mulai_remedial', 'Frontend\\Cbt::mulaiRemedial');
    $routes->post('cbt/get_soal_remedial', 'Frontend\\Cbt::getSoalRemedial');
    $routes->post('cbt/save_jawaban_remedial', 'Frontend\\Cbt::saveJawabanRemedial');
    $routes->post('cbt/selesai_remedial', 'Frontend\\Cbt::selesaiRemedial');

    // CBTSISWA legacy aliases
    $routes->get('cbtsiswa/cbt', 'Frontend\\Cbt::index');
    $routes->post('cbtsiswa/validasi_jadwal', 'Frontend\\Cbt::validasiSiswa');
    $routes->post('cbtsiswa/loadnomorsoal', 'Frontend\\Cbt::getSoal');
    $routes->post('cbtsiswa/savejawaban', 'Frontend\\Cbt::saveJawaban');
    $routes->post('cbtsiswa/selesai', 'Frontend\\Cbt::selesaiUjian');
    $routes->post('cbtsiswa/ragu', 'Frontend\\Cbt::toggleRagu');
    $routes->post('cbtsiswa/pelanggaran', 'Frontend\\Cbt::hitungPelanggaran');
    $routes->post('cbtsiswa/save_signature', 'Frontend\\Cbt::saveSignature');
    $routes->post('cbtsiswa/leavecbt', 'Frontend\\Cbt::leavecbt');

    // Nilai & Profil
    $routes->get('nilai', 'Frontend\\Nilai::index');
    $routes->get('nilai/detail/(:num)', 'Frontend\\Nilai::detail/$1');
    $routes->get('profile', 'Frontend\\Profile::index');
    $routes->post('profile/update', 'Frontend\\Profile::update');
    $routes->post('profile/password', 'Frontend\\Profile::changePassword');
    $routes->get('catatan', 'Frontend\\Catatan::index');
});

// ============================================
// BACKEND (ADMIN/GURU/PANITIA) ROUTES
// ============================================
// Admin Auth
$routes->get('admin/login', 'Backend\Auth::login');
// $routes->post('admin/auth/login', 'Backend\Auth::doLogin'); // Deprecated, uses Auth::login
$routes->get('admin/logout', 'Backend\Auth::logout');

// Admin Panel & utilities
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/', 'Dashboard::index'); // Will be Backend\Dashboard later
    $routes->get('dashboard', 'Dashboard::index');
});







// Guru Routes
// Guru Routes


// ============================================
// INSTALLATION ROUTES — DINONAKTIFKAN (Sudah terinstalasi)
// ============================================
// Installer lawas (CI3) sudah tidak dipakai: view app/Views/install/* tidak
// ada lagi, dan endpoint POST-nya (createAdmin/createApp/createDb) TIDAK
// terlindungi — siapa pun bisa membuat akun admin lewat URL publik.
// Setup database sekarang memakai `php spark migrate`.
// $routes->group('install', function ($routes) {
//     $routes->get('/', 'Install::index');
//     $routes->get('steps', 'Install::steps');
//     $routes->post('checkDatabase', 'Install::checkDatabase');
//     $routes->post('createDb', 'Install::createDb');
//     $routes->post('createAdmin', 'Install::createAdmin');
//     $routes->post('createSetting', 'Install::createSetting');
//     $routes->post('createApp', 'Install::createApp');
// });

// ============================================
// AUTHENTICATION ROUTES
// ============================================
// Catatan: route 'register' sengaja dihapus. Form register tidak ada di repo,
// dan endpoint POST-nya menerima parameter `role` dari request (public
// self-registration + eskalasi privilese). Pendaftaran user hanya boleh
// dilakukan oleh admin/guru/panitia lewat modul User* yang sudah ber-filter.
// $routes->get('register', 'Auth::register');
// $routes->post('register', 'Auth::register');

$routes->group('auth', function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->get('siswa', 'Auth::siswa');
    $routes->post('login', 'Auth::login');

    $routes->get('logout', 'Auth::logout');
    $routes->post('logout', 'Auth::logout');
});



// ============================================
// BUKU RAPOR ROUTES
// ============================================
$routes->group('bukurapor', ['filter' => 'auth:admin,guru'], function ($routes) {
    $routes->get('/', 'Bukurapor::index');
    $routes->get('get_kelas', 'Bukurapor::getDataKelas');
    $routes->post('backup', 'Bukurapor::backupNilai');
    $routes->post('restore', 'Bukurapor::restoreNilai');
});

// ============================================
// RAPOR ROUTES (modul rapor lama CI3: KKM / KIKD / INPUT NILAI / SIKAP)
// ============================================
// Modul ini tetap hidup: tabel `rapor_kikd` & `rapor_kkm` masih berisi data
// aktif dan hanya bisa disunting melalui controller ini.
// Catatan: Ledger & DKN TIDAK ada di sini — sudah digantikan oleh
// `Guru\Nilai::cetakleger` dan `Guru\Nilai::dkn` (menu sidebar memakai yang itu).
$routes->group('rapor', ['filter' => 'auth:admin,guru'], function ($routes) {
    // Halaman utama
    $routes->get('/', 'Rapor::index');
    $routes->get('nilai', 'Rapor::raporNilai');
    $routes->get('kkm', 'Rapor::raporkkm');
    $routes->get('kikd', 'Rapor::raporkikd');
    $routes->get('sikap', 'Rapor::raporSikap');
    $routes->get('sikap/spiritual', 'Rapor::raporSpiritual');
    $routes->get('sikap/sosial', 'Rapor::raporSosial');
    $routes->get('nilaiguru/(:segment)/(:num)', 'Rapor::raporNilaiGuru/$1/$2');
    $routes->get('cek_nilai/(:segment)/(:num)', 'Rapor::raporCekNilai/$1/$2');

    // Input nilai harian / PTS / PAS / Ekstra
    $routes->get('inputharian/(:num)/(:num)', 'Rapor::inputHarian/$1/$2');
    $routes->get('inputpts/(:num)/(:num)', 'Rapor::inputPts/$1/$2');
    $routes->get('inputpas/(:num)/(:num)', 'Rapor::inputPas/$1/$2');
    $routes->get('inputekstra/(:num)/(:num)', 'Rapor::inputEkstra/$1/$2');

    // Data pendukung (JSON)
    $routes->get('datakkm/(:num)/(:num)', 'Rapor::datakkm/$1/$2');
    $routes->get('datakkmextra/(:num)/(:num)', 'Rapor::datakkmEkstra/$1/$2');
    $routes->get('datakikd/(:num)/(:num)', 'Rapor::datakikd/$1/$2');

    // Simpan
    $routes->post('saveraporadmin', 'Rapor::saveRaporAdmin', ['filter' => 'admin']);
    $routes->post('savekkm', 'Rapor::saveKkm');
    $routes->post('savekikd', 'Rapor::saveKikd');
    $routes->post('savesikap', 'Rapor::saveSikap');

    // Unggah & impor
    $routes->post('uploadnilaiharian', 'Rapor::uploadNilaiHarian');
    $routes->post('uploadnilaints', 'Rapor::uploadNilaiPts');
    $routes->post('uploadnilainas', 'Rapor::uploadNilaiPas');
    $routes->post('uploadnilaiekstra', 'Rapor::uploadNilaiEkstra');
    $routes->post('importpts', 'Rapor::importPts');
    $routes->post('importpas', 'Rapor::importPas');
    $routes->post('importekstra', 'Rapor::importEkstra');
    $routes->post('importspiritual', 'Rapor::importSpiritual');
    $routes->post('importsosial', 'Rapor::importSosial');

    // Unduh template
    $routes->get('downloadtemplate_harian/(:num)/(:num)', 'Rapor::downloadNilaiHarian/$1/$2');
    $routes->get('downloadtemplatepts/(:num)/(:num)', 'Rapor::downloadTemplatePts/$1/$2');
    $routes->get('downloadtemplate_pas/(:num)/(:num)', 'Rapor::downloadTemplatePas/$1/$2');
    $routes->get('downloadtemplateekstra/(:num)/(:num)', 'Rapor::downloadTemplateEkstra/$1/$2');
});

// ============================================
// KBM (E-Learning Siswa) ROUTES
// ============================================
$routes->group('kbm', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Kbm::index');
    $routes->get('hasil', 'Kbm::hasil');
    $routes->get('get_all_nilai', 'Kbm::get_all_nilai');
    $routes->get('get_nilai_jadwal', 'Kbm::get_nilai_jadwal');
});

// ============================================
// SISWAMOD (Student Legacy Support) ROUTES
// ============================================
// Nonaktif (CBT murni): controller Siswamod tidak ada & tanpa pemanggil
// $routes->group('siswamod', ['filter' => 'auth:siswa'], function ($routes) {
//     $routes->get('cektokenmod', 'Siswamod::cektokenmod');
// });

// ============================================
// CBT SISWA (Student Exam) ROUTES - FULLY MIGRATED TO Frontend\Cbt
// ============================================
$routes->group('cbtsiswa', ['filter' => 'auth:siswa'], function ($routes) {
    // Main Pages
    $routes->get('/', 'Frontend\Cbt::index');
    $routes->get('cbt', 'Frontend\Cbt::index');
    $routes->get('get_cbt_siswa', 'Frontend\Cbt::getCbtSiswa');

    // Konfirmasi & Validasi
    $routes->get('konfirmasi/(:segment)', 'Frontend\Cbt::konfirmasi/$1');
    $routes->post('konfirmasi/(:segment)', 'Frontend\Cbt::konfirmasi/$1');
    $routes->post('validasi_siswa', 'Frontend\Cbt::validasiSiswa');
    $routes->post('validasi_token', 'Frontend\Cbt::validasiToken');
    $routes->post('log_aktivitas', 'Frontend\Cbt::logAktivitasSiswa');
    $routes->post('validasi_jadwal', 'Frontend\Cbt::validasiJadwal');

    // Absensi
    $routes->get('absensi', 'Frontend\Cbt::absensi');

    // Penilaian/Ujian
    $routes->get('penilaian/(:segment)', 'Frontend\Cbt::penilaian/$1');
    $routes->get('ujian/(:segment)', 'Frontend\Cbt::penilaian/$1'); // Alias to penilaian

    // Soal & Jawaban — rate-limited
    $routes->get('load_soal',       'Frontend\Cbt::getSoal',     ['filter' => 'cbt_ratelimit:get_soal,120']);
    $routes->get('load_nomor_soal', 'Frontend\Cbt::getSoal',     ['filter' => 'cbt_ratelimit:get_soal,120']);
    $routes->post('loadnomorsoal',  'Frontend\Cbt::getSoal',     ['filter' => 'cbt_ratelimit:get_soal,120']);
    $routes->post('save_jawaban',   'Frontend\Cbt::saveJawaban', ['filter' => 'cbt_ratelimit:save_jawaban,60']);
    $routes->post('savejawaban',    'Frontend\Cbt::saveJawaban', ['filter' => 'cbt_ratelimit:save_jawaban,60']);
    $routes->post('toggle_ragu',    'Frontend\Cbt::toggleRagu',  ['filter' => 'cbt_ratelimit:toggle_ragu,30']);

    // Signature
    $routes->post('save_signature', 'Frontend\Cbt::saveSignature');

    // Selesai/Leave
    $routes->post('selesai', 'Frontend\Cbt::selesaiUjian');
    $routes->post('selesai_ujian', 'Frontend\Cbt::selesaiUjian');
    $routes->post('selesaiujian', 'Frontend\Cbt::selesaiUjian');
    $routes->post('leavecbt', 'Frontend\Cbt::leavecbt');

    // Pelanggaran
    $routes->post('hitung_pelanggaran', 'Frontend\Cbt::hitungPelanggaran');

    // Remedial
    $routes->post('cek_remedial', 'Frontend\Cbt::cekRemedial');
    $routes->post('mulai_remedial', 'Frontend\Cbt::mulaiRemedial');
    $routes->post('get_soal_remedial', 'Frontend\Cbt::getSoalRemedial');
    $routes->post('save_jawaban_remedial', 'Frontend\Cbt::saveJawabanRemedial');
    $routes->post('selesai_remedial', 'Frontend\Cbt::selesaiRemedial');

    // Hasil ujian
    $routes->get('hasil/(:segment)', 'Frontend\Cbt::hasilUjian/$1');
    
    // Ujian Praktik
    $routes->get('praktik', 'Frontend\CbtPraktikSiswa::index');
    $routes->post('praktik/submit', 'Frontend\CbtPraktikSiswa::submit');
});

// ============================================
// CBT BANK SOAL (Question Bank) ROUTES  
// ============================================
$routes->group('cbtbanksoal', ['filter' => 'auth:admin,guru,panitia'], static function ($routes) {
    $routes->post('do_pindah_bank', 'Cbtbanksoal::do_pindah_bank');
    $routes->get('/', 'Cbtbanksoal::index');
    $routes->post('get_detail', 'Cbtbanksoal::get_detail');
    $routes->post('deleteBank', 'Cbtbanksoal::deleteBank');
    $routes->get('detail/(:num)', 'Cbtbanksoal::detail/$1');
    $routes->get('add', 'Cbtbanksoal::addBank');
    $routes->get('addBank', 'Cbtbanksoal::addBank');
    $routes->get('edit/(:num)', 'Cbtbanksoal::editBank/$1');
    $routes->post('save', 'Cbtbanksoal::saveBank');
    $routes->get('getgurumapel', 'Cbtbanksoal::getGuruMapel');
    $routes->get('getmapelguru', 'Cbtbanksoal::getMapelGuru');
    $routes->get('getkelaslevel', 'Cbtbanksoal::getKelasLevel');
    $routes->get('getkartuoptions', 'Cbtbanksoal::getKartuOptions');
    $routes->get('api-get-kartu-items', 'Cbtbanksoal::apiGetKartuItems');
    $routes->get('api-get-kartu-summary', 'Cbtbanksoal::apiGetKartuSummary');
    $routes->get('apiGetTujuanByMapel', 'Cbtbanksoal::apiGetTujuanByMapel');
    $routes->get('api-get-tujuan-by-mapel', 'Cbtbanksoal::apiGetTujuanByMapel');
    $routes->get('checkworkflow', 'Cbtbanksoal::checkWorkflow');
    $routes->post('deleteallbank', 'Cbtbanksoal::deleteAllBank');
    
    
    $routes->get('import/(:num)', 'Cbtbanksoal::import/$1');
    $routes->post('uploadsoal', 'Cbtbanksoal::uploadSoal');
    $routes->post('uploadsoalimage', 'Cbtbanksoal::uploadSoalImage');
    $routes->post('copybanksoal/(:num)', 'Cbtbanksoal::copyBankSoal/$1');
    $routes->get('checkBankStatus/(:num)', 'Cbtbanksoal::checkBankStatus/$1');

    // Soal creation/editing routes
    $routes->get('buatsoal/(:num)', 'Cbtbanksoal::buatsoal/$1');
    $routes->get('getSoalByNomor', 'Cbtbanksoal::getSoalByNomor');
    $routes->post('tambahSoal', 'Cbtbanksoal::tambahSoal');
    $routes->post('saveSoal', 'Cbtbanksoal::saveSoal');
    $routes->post('save_soal_ajax', 'Cbtbanksoal::save_soal_ajax'); // Alias for UX autosave/manual AJAX
    $routes->post('hapusSoal', 'Cbtbanksoal::hapusSoal');
    $routes->post('hapussoal', 'Cbtbanksoal::hapusSoal'); // Alias for JS compatibility
    $routes->post('saveSelected', 'Cbtbanksoal::saveSelected');
    $routes->post('resetNumber', 'Cbtbanksoal::resetNumber');
    $routes->post('upload_image', 'Cbtbanksoal::upload_image');
    $routes->post('delete_image', 'Cbtbanksoal::delete_image');
    $routes->post('deletefile', 'Cbtbanksoal::deletefile');
    $routes->match(['get', 'post'], 'cleanup_images', 'Cbtbanksoal::cleanup_images');
    $routes->post('generate_ai', 'Cbtbanksoal::generate_ai');
    $routes->post('generate_similar_ai', 'Cbtbanksoal::generate_similar_ai');
    $routes->post('generate_image', 'Cbtbanksoal::generate_image');
    $routes->post('save_ai_generated', 'Cbtbanksoal::save_ai_generated');
    $routes->post('paraphrase_ai', 'Cbtbanksoal::paraphrase_ai');
    $routes->post('tidy_ai', 'Cbtbanksoal::tidy_ai');
    $routes->post('tidy_manual', 'Cbtbanksoal::tidy_manual');
    $routes->post('generate-soal-from-kartu', 'Cbtbanksoal::generateSoalFromKartu');
    
    // Backup & Restore Bank Soal routes
    $routes->get('export_backup/(:num)', 'Cbtbanksoal::exportBackup/$1');
    $routes->post('do_restore', 'Cbtbanksoal::doRestore');
});

// ============================================
// CBT KARTU SOAL ROUTES
// ============================================
$routes->group('cbtkartusoal', ['filter' => 'auth:admin,guru,panitia'], static function ($routes) {
    $routes->get('/', 'Cbtkartusoal::index');
    $routes->get('create', 'Cbtkartusoal::create');
    $routes->get('edit/(:segment)', 'Cbtkartusoal::edit/$1');
    $routes->get('detail/(:segment)', 'Cbtkartusoal::detail/$1');
    $routes->get('printPdf/(:segment)', 'Cbtkartusoal::printPdf/$1');
    $routes->get('printPdfPaket/(:segment)/(:num)', 'Cbtkartusoal::printPdfPaket/$1/$2');
    $routes->post('save', 'Cbtkartusoal::save');
    $routes->post('delete', 'Cbtkartusoal::delete');
    $routes->post('bulk-delete', 'Cbtkartusoal::bulkDelete');
    $routes->post('addItem', 'Cbtkartusoal::addItem');
    $routes->post('updateItem', 'Cbtkartusoal::updateItem');
    $routes->post('removeItem', 'Cbtkartusoal::removeItem');
    $routes->post('cloneItem', 'Cbtkartusoal::cloneItem');
    $routes->post('reorder', 'Cbtkartusoal::reorder');
    $routes->post('auto-reorder', 'Cbtkartusoal::autoReorder');
    $routes->post('generate-ai', 'Cbtkartusoal::generateAiItems');
    $routes->post('generate-item-soal', 'Cbtkartusoal::generateItemSoalContent');
    $routes->post('save-generated', 'Cbtkartusoal::saveGeneratedItems');
    $routes->post('generateBankFromKartu', 'Cbtkartusoal::generateBankFromKartu');
    $routes->post('submitForApproval', 'Cbtkartusoal::submitForApproval');
    $routes->post('approve', 'Cbtkartusoal::approve');
    $routes->post('cancel-approval', 'Cbtkartusoal::cancelApproval');
    $routes->post('returnForRevision', 'Cbtkartusoal::returnForRevision');
    $routes->get('workflow-check', 'Cbtkartusoal::workflowCheck');
    $routes->get('tujuan-mapel', 'Cbtkartusoal::tujuanByMapel');
    $routes->get('options', 'Cbtkartusoal::optionsByPeriode');
    $routes->post('resyncItemsFromTp/(:num)', 'Cbtkartusoal::resyncItemsFromTp/$1');
    $routes->get('api/items/(:segment)', 'Cbtkartusoal::apiGetItems/$1');
    $routes->get('api/tujuan/(:segment)', 'Cbtkartusoal::apiGetTujuan/$1');
    $routes->get('api/kelas-by-mapel', 'Cbtkartusoal::apiKelasByMapel');
    // Dihapus per audit (tak ada konsumen UI di repo; method tetap ada di controller):
    // api/banks, api/bank/{id}/soal (tanpa guard kepemilikan), api/kartu/{uuid}.
    $routes->get('api/bank/(:num)/soal-full', 'Cbtkartusoal::apiSoalFull/$1');
    $routes->get('api/bank/(:num)/soal/(:num)', 'Cbtkartusoal::apiSoalByNomor/$1/$2');
    $routes->get('api/paket-banks/(:segment)', 'Cbtkartusoal::apiPaketBanks/$1');
    $routes->get('api/search-bank/(:segment)', 'Cbtkartusoal::apiSearchBankSoal/$1');
    
    // AI Kisi-Kisi Sekolah Routes
    $routes->post('api/generate-kisi-sekolah', 'Cbtkartusoal::apiGenerateKisiSekolah');
    $routes->post('api/generate-per-kelas', 'Cbtkartusoal::apiGeneratePerKelas');
    $routes->get('api/distribusi-target', 'Cbtkartusoal::apiDistribusiTarget');
    $routes->get('api/dashboard-kisi-sekolah', 'Cbtkartusoal::apiDashboardKisiSekolah');
    $routes->post('api/validasi-kurikulum', 'Cbtkartusoal::apiValidasiKurikulum');
    
    // ── Sprint 1-3: Kualitas Kisi-kisi ──────────────────────────────
    $routes->get('api/analisis-distribusi/(:segment)',  'Cbtkartusoal::apiAnalisisDistribusi/$1');
    $routes->get('api/validasi-materi/(:segment)',      'Cbtkartusoal::apiValidasiMateri/$1');
    $routes->post('duplicate',                          'Cbtkartusoal::duplicate');
    $routes->post('approve-kepsek',                     'Cbtkartusoal::approveKepsek');
    // ────────────────────────────────────────────────────────────────

    $routes->post('import-bank', 'Cbtkartusoal::importFromBank');
    $routes->get('apiSearchBankSoal/(:segment)', 'Cbtkartusoal::apiSearchBankSoal/$1');
    $routes->post('importFromBank', 'Cbtkartusoal::importFromBank');
});

// ============================================
// CBT QUALITY DASHBOARD ROUTES  (Sprint 3)
// ============================================
$routes->group('cbt-quality', ['filter' => 'auth:admin,panitia'], static function ($routes) {
    $routes->get('/',                               'CbtQualityDashboard::index');
    $routes->get('summary',                         'CbtQualityDashboard::summary');
    $routes->get('item-analysis/(:num)',            'CbtQualityDashboard::itemAnalysisBank/$1');
    $routes->get('item-analysis-view/(:num)',       'CbtQualityDashboard::itemAnalysisView/$1');
    $routes->post('run-analysis/(:num)',            'CbtQualityDashboard::runAnalysis/$1');
    $routes->get('duplikasi',                       'CbtQualityDashboard::duplikasi');
    $routes->get('laporan-duplikasi',               'CbtQualityDashboard::laporanDuplikasi');
    $routes->post('rehash-bank/(:num)',             'CbtQualityDashboard::rehashBank/$1');
    $routes->post('rehash-missing',                 'CbtQualityDashboard::rehashMissing');
    $routes->get('capaian-pembelajaran',            'CbtQualityDashboard::capaianPembelajaran');
    $routes->post('capaian-pembelajaran/save',      'CbtQualityDashboard::saveCapaian');
    $routes->post('capaian-pembelajaran/delete',    'CbtQualityDashboard::deleteCapaian');
    $routes->post('capaian-pembelajaran/seed',      'CbtQualityDashboard::seedCapaian');
});

// ============================================
// CBT JADWAL (Schedule) ROUTES
// ============================================
$routes->group('cbtjadwal', ['filter' => 'auth:admin,guru,panitia'], static function ($routes) {
    $routes->get('/', 'Cbtjadwal::index');
    $routes->get('add/(:num)', 'Cbtjadwal::add/$1'); // 0 for add, ID for edit
    $routes->get('getbankmapel/(:num)', 'Cbtjadwal::getBankMapel/$1');
    $routes->post('saveJadwal', 'Cbtjadwal::saveJadwal');
    $routes->post('aktifkan_jadwal', 'Cbtjadwal::aktifkan_jadwal');
    $routes->post('toggle_remedial', 'Cbtjadwal::toggle_remedial');
    $routes->get('deleteJadwal', 'Cbtjadwal::deleteJadwal');
    $routes->post('deleteAllJadwal', 'Cbtjadwal::deleteAllJadwal');
    $routes->post('get_all_norekap', 'Cbtjadwal::get_all_norekap');
});

// ============================================
// CBT STATUS (Monitoring) ROUTES
// ============================================
$routes->group('cbtstatus', ['filter' => 'auth:admin,guru,panitia'], static function ($routes) {
    $routes->get('/', 'Cbtstatus::index');
    $routes->get('get_siswa_ujian', 'Cbtstatus::getSiswaUjian');
    $routes->get('get_siswa_belum_ujian', 'Cbtstatus::getSiswaBelumUjian');
    $routes->get('live_stats', 'Cbtstatus::getLiveStats');
    $routes->post('reset_login', 'Cbtstatus::resetLogin');
    $routes->post('reset_waktu', 'Cbtstatus::resetWaktu');
    $routes->get('getTglJadwalUjian', 'Cbtstatus::getTglJadwalUjian');
    // Legacy CI3 endpoint spelling
    $routes->get('gettgljadwalujian', 'Cbtstatus::getTglJadwalUjian');
    $routes->get('getJadwalKelas', 'Cbtstatus::getJadwalKelas');
    $routes->get('getstatussiswa', 'Cbtstatus::getStatusSiswa');
    $routes->get('getjadwalujianbyjadwal', 'Cbtstatus::getjadwalujianbyjadwal');
    $routes->get('getjadwalujianbykelas', 'Cbtstatus::getjadwalujianbykelas');
    $routes->post('applyaction', 'Cbtstatus::applyAction');
    $routes->post('resettimer', 'Cbtstatus::resetTimer');
});

// ============================================
// CBT ALOKASI (Time Allocation) ROUTES
// ============================================
$routes->group('cbtalokasi', ['filter' => 'auth:admin,panitia'], static function ($routes) {
    $routes->get('/', 'Cbtalokasi::index');
    $routes->get('detail', 'Cbtalokasi::detail');
    $routes->get('getTanggal', 'Cbtalokasi::getTanggal');
    $routes->post('getAlokasi', 'Cbtalokasi::getAlokasi');
    $routes->post('saveAlokasi', 'Cbtalokasi::saveAlokasi');
    // Alias snake_case (dari group lama yang duplikat)
    $routes->get('get_tanggal', 'Cbtalokasi::getTanggal');
    $routes->get('get_alokasi', 'Cbtalokasi::getAlokasi');
    $routes->post('save_alokasi', 'Cbtalokasi::saveAlokasi');
});

// ============================================
// CBT ANALISIS (Question Analysis) ROUTES
// ============================================
$routes->group('cbtanalisis', ['filter' => 'auth:admin,panitia'], static function ($routes) {
    $routes->get('/', 'Cbtanalisis::index');
    $routes->get('analisis', 'Cbtanalisis::analisis');
    $routes->get('getJadwalUjianByJadwal', 'Cbtanalisis::getJadwalUjianByJadwal');
});

// ============================================
// CBT ANALYSIS (Integrated TP Analysis) ROUTES
// ============================================
$routes->group('cbtanalysis', ['filter' => 'auth:admin,guru,panitia'], static function ($routes) {
    $routes->get('/', 'CbtAnalysis::index');
    $routes->get('exportExcel', 'CbtAnalysis::exportExcel');
    $routes->get('exportPdf', 'CbtAnalysis::exportPdf');
    $routes->get('getItemAnalysis', 'CbtAnalysis::getItemAnalysis');
    $routes->post('clearCache', 'CbtAnalysis::clearAnalysisCache');
    $routes->get('api/summary', 'CbtAnalysis::apiSummary');
});



// ============================================
// KURIKULUM MERDEKA ASSESSMENT ROUTES
// ============================================
$routes->group('kurikulum-merdeka', ['filter' => 'auth:admin,guru,panitia,siswa'], static function ($routes) {
    $routes->get('/', 'KurikulumMerdekaAssessment::index');
    $routes->get('input-penilaian', 'KurikulumMerdekaAssessment::inputPenilaian');
    $routes->post('simpan-penilaian', 'KurikulumMerdekaAssessment::simpanPenilaian');
    $routes->get('portofolio-digital', 'KurikulumMerdekaAssessment::portofolioDigital');
    $routes->post('simpan-portofolio', 'KurikulumMerdekaAssessment::simpanPortofolio');
    $routes->get('laporan-p5', 'KurikulumMerdekaAssessment::laporanP5');
    $routes->post('sync-p5', 'KurikulumMerdekaAssessment::syncP5');
    $routes->get('audit-keamanan', 'KurikulumMerdekaAssessment::auditKeamanan');
});

$routes->group('test-kurikulum-merdeka', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('/', 'TestKurikulumMerdeka::index');
    $routes->post('run-all', 'TestKurikulumMerdeka::runAllTests');
});

// ============================================
// CBT CETAK (Print) ROUTES
// ============================================
$routes->group('cbtcetak', ['filter' => 'auth:admin,panitia'], static function ($routes) {
    $routes->get('/', 'Cbtcetak::index');
    $routes->get('kartupeserta', 'Cbtcetak::kartuPeserta');
    $routes->get('kartumeja', 'Cbtcetak::kartuMeja');
    $routes->get('absenpeserta', 'Cbtcetak::absenPeserta');
    $routes->get('beritaacara', 'Cbtcetak::beritaAcara');
    $routes->get('paktaintegritas', 'Cbtcetak::paktaIntegritas');
    $routes->get('pesertaujian', 'Cbtcetak::pesertaUjian');
    $routes->get('pesertaujian/(:segment)', 'Cbtcetak::pesertaUjian/$1');
    $routes->get('pengawas', 'Cbtcetak::pengawas');
    $routes->get('kartupengawas', 'Cbtcetak::kartuPengawas');
    $routes->get('getpanitia', 'Cbtcetak::getPanitia');
    $routes->get('getsiswakelas', 'Cbtcetak::getSiswaKelas');

    $routes->get('getsiswaruang', 'Cbtcetak::getSiswaRuang');
    $routes->get('getjadwalmapel', 'Cbtcetak::getJadwalMapel');
    $routes->get('getalljadwal', 'Cbtcetak::getAllJadwal');
    $routes->get('getkelas', 'Cbtcetak::getKelas');
    $routes->get('getruang', 'Cbtcetak::getRuang');
    $routes->post('uploadfile/(:segment)', 'Cbtcetak::uploadFile/$1');
    $routes->post('deletefile', 'Cbtcetak::deleteFile');
    $routes->post('savekartu', 'Cbtcetak::saveKartu');
    $routes->post('savekop', 'Cbtcetak::saveKop');
    $routes->post('savekopberita', 'Cbtcetak::saveKopBerita');
    $routes->post('saveberitaacara', 'Cbtcetak::saveBeritaAcara');
    $routes->post('savepaktaintegritas', 'Cbtcetak::savePaktaIntegritas');
});

// ============================================
// CBT REKAP (Recap) ROUTES
// ============================================
$routes->group('cbtrekap', ['filter' => 'auth:admin,guru'], static function ($routes) {
    $routes->get('/', 'Cbtrekap::index');
    $routes->get('get_jadwal', 'Cbtrekap::get_jadwal');
    $routes->get('get_rekap', 'Cbtrekap::get_rekap');
    $routes->get('view_koreksi', 'Cbtrekap::view_koreksi');
    $routes->get('ekspor_rekap', 'Cbtrekap::ekspor_rekap');
    $routes->post('get_nilai_rekap', 'Cbtrekap::get_nilai_rekap');
    $routes->get('koreksi', 'Cbtrekap::koreksi');
    $routes->get('inputessai', 'Cbtrekap::inputEssai');
    $routes->post('rekap', 'Cbtrekap::rekap');
    $routes->post('rekap_nilai_siswa', 'Cbtrekap::rekap_nilai_siswa');
    $routes->post('simpankoreksi', 'Cbtrekap::simpankoreksi');
    $routes->post('tandaikoreksi', 'Cbtrekap::tandaikoreksi');
    $routes->post('simpannilaiinput', 'Cbtrekap::simpannilaiinput');
    $routes->post('hapusrekap', 'Cbtrekap::hapusrekap');
});

// ============================================
// CBT PRAKTIK (Practical Exam) ROUTES
// ============================================
$routes->group('cbtpraktik', ['filter' => 'auth:admin,guru,panitia'], static function ($routes) {
    $routes->get('/', 'Cbtpraktik::index');
    $routes->get('create', 'Cbtpraktik::create');
    $routes->post('save', 'Cbtpraktik::save');
    $routes->get('edit/(:num)', 'Cbtpraktik::edit/$1');
    $routes->post('update/(:num)', 'Cbtpraktik::update/$1');
    $routes->post('delete/(:num)', 'Cbtpraktik::delete/$1');
    
    // Rubric management
    $routes->post('addRubrik', 'Cbtpraktik::addRubrik');
    $routes->post('deleteRubrik', 'Cbtpraktik::deleteRubrik');
    
    // Grading
    $routes->get('penilaian/(:num)', 'Cbtpraktik::penilaian/$1');
    $routes->get('nilaiManual/(:num)', 'Cbtpraktik::nilaiManual/$1');
    $routes->get('nilaiManualPdf/(:num)', 'Cbtpraktik::nilaiManualPdf/$1');
    $routes->post('simpanNilaiManual', 'Cbtpraktik::simpanNilaiManual');
    $routes->get('getHasilDetail', 'Cbtpraktik::getHasilDetail');
    $routes->post('simpanPenilaian', 'Cbtpraktik::simpanPenilaian');
    $routes->post('runAutoGrading', 'Cbtpraktik::runAutoGrading');
    
    // Multi-Assessor Grading
    $routes->get('getAssessors/(:num)', 'Cbtpraktik::getAssessors/$1');
    $routes->post('simpanPenilaianAssessor', 'Cbtpraktik::simpanPenilaianAssessor');
    $routes->get('getProgressPenilaian/(:num)', 'Cbtpraktik::getProgressPenilaian/$1');
    
    // Monitoring
    $routes->get('monitor/(:num)', 'Cbtpraktik::monitor/$1');
    $routes->get('getMonitorData', 'Cbtpraktik::getMonitorData');
    
    // Analysis & Remedial
    $routes->get('getTPByMapel', 'Cbtpraktik::getTPByMapel');
    $routes->get('analisisRemedial/(:num)', 'Cbtpraktik::analisisRemedial/$1');
    $routes->get('analisis/(:num)', 'Cbtpraktik::analisisRemedial/$1');
    $routes->post('simpanObservasi', 'Cbtpraktik::simpanObservasi');
    $routes->get('observasi/(:num)', 'Cbtpraktik::observasiIndex/$1');
    $routes->post('simpanRemedial', 'Cbtpraktik::simpanRemedial');
    $routes->get('remedial/(:num)', 'Cbtpraktik::remedialIndex/$1');
    $routes->post('updateRemedial', 'Cbtpraktik::updateRemedial');
    $routes->get('lihatSelfAssessment/(:num)', 'Cbtpraktik::lihatSelfAssessment/$1');
    $routes->get('p5/(:num)', 'Cbtpraktik::p5Index/$1');
    $routes->post('p5Save', 'Cbtpraktik::p5Save');
    $routes->post('p5SimpanNilai', 'Cbtpraktik::p5SimpanNilai');
    
    // Audit Trail
    $routes->get('getAuditTrail/(:num)', 'Cbtpraktik::getAuditTrail/$1');
    $routes->get('getAuditTrailPraktik/(:num)', 'Cbtpraktik::getAuditTrailPraktik/$1');
    $routes->get('getAnomalousChanges/(:num)', 'Cbtpraktik::getAnomalousChanges/$1');
    
    // Report Generation
    $routes->get('exportCSV/(:num)', 'Cbtpraktik::exportCSV/$1');
    $routes->get('exportPDF/(:num)', 'Cbtpraktik::exportPDF/$1');
    $routes->get('exportLaporanIndividual/(:num)', 'Cbtpraktik::exportLaporanIndividual/$1');
    $routes->get('exportLaporanIndividual/(:num)/(:num)', 'Cbtpraktik::exportLaporanIndividual/$1/$2');
    $routes->get('narasiRapor/(:num)/(:num)', 'Cbtpraktik::narasiRapor/$1/$2');
    $routes->get('previewLaporan/(:num)', 'Cbtpraktik::previewLaporan/$1');
    $routes->get('exportExcel/(:num)', 'Cbtpraktik::exportExcel/$1');
    $routes->get('exportReport/(:num)', 'Cbtpraktik::exportReport/$1');
    
    // Legacy alias
    $routes->get('koreksi/(:num)', 'Cbtpraktik::penilaian/$1');
    
    // Result Detail
    $routes->get('hasil/(:num)/(:num)', 'Cbtpraktik::hasil/$1/$2');
    
    // Rubric Templates (CRUD)
    $routes->get('templates', 'Cbtpraktik::templates');
    $routes->get('templates/create', 'Cbtpraktik::templates_create');
    $routes->post('templates/save', 'Cbtpraktik::templates_save');
    $routes->get('templates/detail/(:num)', 'Cbtpraktik::templates_detail/$1');
    $routes->get('templates_detail/(:num)', 'Cbtpraktik::templates_detail/$1');
    $routes->get('templates/edit/(:num)', 'Cbtpraktik::templates_edit/$1');
    $routes->post('templates/update/(:num)', 'Cbtpraktik::templates_update/$1');
    $routes->post('templates/delete/(:num)', 'Cbtpraktik::templates_delete/$1');
    $routes->post('templates_generate_ai', 'Cbtpraktik::templates_generate_ai');
    
    // Analysis & Recommendations API
    $routes->post('generateAnalisis/(:num)', 'Cbtpraktik::generateAnalisis/$1');
    $routes->post('batchGenerateAnalisis/(:num)', 'Cbtpraktik::batchGenerateAnalisis/$1');
    $routes->get('getDaftarRemedial/(:num)', 'Cbtpraktik::getDaftarRemedial/$1');
    $routes->get('getRekomendasiSiswa/(:num)', 'Cbtpraktik::getRekomendasiSiswa/$1');
});

// Student Practical Exam Routes
$routes->group('cbtsiswa/praktik', ['filter' => 'auth:siswa'], static function ($routes) {
    $routes->get('/', 'Frontend\CbtPraktikSiswa::index');
    $routes->get('konfirmasi/(:num)', 'Frontend\CbtPraktikSiswa::konfirmasi/$1');
    $routes->post('mulai', 'Frontend\CbtPraktikSiswa::mulai');
    $routes->get('ujian/(:num)', 'Frontend\CbtPraktikSiswa::ujian/$1');
    $routes->get('hasil/(:num)', 'Frontend\CbtPraktikSiswa::hasil/$1');
    $routes->post('saveDraft', 'Frontend\CbtPraktikSiswa::saveDraft');
    $routes->post('uploadFile', 'Frontend\CbtPraktikSiswa::uploadFile');
    $routes->post('deleteFile', 'Frontend\CbtPraktikSiswa::deleteFile');
    $routes->post('submit', 'Frontend\CbtPraktikSiswa::submit');
    $routes->get('auto_submit/(:num)', 'Frontend\CbtPraktikSiswa::autoSubmit/$1');
    $routes->post('logActivity', 'Frontend\CbtPraktikSiswa::logActivity');
    $routes->get('checkTime', 'Frontend\CbtPraktikSiswa::checkTime');
    $routes->post('selfAssessment/(:num)', 'Frontend\CbtPraktikSiswa::selfAssessment/$1');
    $routes->get('selfAssessment/(:num)', 'Frontend\CbtPraktikSiswa::selfAssessmentView/$1');
    $routes->get('observasi/(:num)', 'Frontend\CbtPraktikSiswa::observasi/$1');
    $routes->get('remedial/(:num)', 'Frontend\CbtPraktikSiswa::remedial/$1');
    $routes->get('getFeedback/(:num)', 'Frontend\CbtPraktikSiswa::getFeedback/$1');
    $routes->post('replyFeedback', 'Frontend\CbtPraktikSiswa::replyFeedback');
    $routes->post('addPortfolioItem', 'Frontend\CbtPraktikSiswa::addPortfolioItem');
    $routes->get('getPortfolioItems/(:num)', 'Frontend\CbtPraktikSiswa::getPortfolioItems/$1');
});

// ============================================
$routes->group('laporan', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Laporan::index');

    // Upload operations
    $routes->post('upload', 'Laporan::upload');
    $routes->post('save_berita_acara', 'Laporan::save_berita_acara');
    $routes->post('save_pakta_integritas', 'Laporan::save_pakta_integritas');

    // Update operations
    $routes->post('update_berita_acara', 'Laporan::update_berita_acara');
    $routes->post('update_pakta_integritas', 'Laporan::update_pakta_integritas');

    // CRUD operations
    $routes->post('delete', 'Laporan::delete');
    $routes->get('get_laporan/(:num)', 'Laporan::get_laporan/$1');

    // Approval workflow
    $routes->post('approve', 'Laporan::approve');
    $routes->post('reject', 'Laporan::reject');

    // Print templates
    $routes->get('cetak_berita_acara/(:num)', 'Laporan::cetak_berita_acara/$1');
    $routes->get('cetak_pakta_integritas/(:num)', 'Laporan::cetak_pakta_integritas/$1');

    // Berita Acara
    $routes->get('get_siswa_ruang', 'Laporan::get_siswa_ruang');
    $routes->get('get_ruang_sesi', 'Laporan::get_ruang_sesi');
    $routes->get('get_mapel_ujian', 'Laporan::get_mapel_ujian');

    // Daily Report
    $routes->get('harian', 'Laporan::harian');
    $routes->get('get_harian_data', 'Laporan::get_harian_data');
    $routes->get('cetak_harian', 'Laporan::cetak_harian');
});
// CBT UTILITIES ROUTES
// ============================================

$routes->group('cbtruang', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Cbtruang::index');
    $routes->post('data', 'Cbtruang::data');
    $routes->post('add', 'Cbtruang::add');
    $routes->post('update', 'Cbtruang::update');
    $routes->post('delete', 'Cbtruang::delete');
});

$routes->group('cbtsesi', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Cbtsesi::index');
    $routes->post('data', 'Cbtsesi::data');
    $routes->post('add', 'Cbtsesi::add');
    $routes->post('update', 'Cbtsesi::update');
    $routes->post('delete', 'Cbtsesi::delete');
});

$routes->group('cbtsesisiswa', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Cbtsesisiswa::index');
    $routes->post('editsesisiswa', 'Cbtsesisiswa::editsesisiswa');
});

$routes->group('cbtjenis', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Cbtjenis::index');
    $routes->post('data', 'Cbtjenis::data');
    $routes->post('add', 'Cbtjenis::add');
    $routes->post('update', 'Cbtjenis::update');
    $routes->post('togglestatus', 'Cbtjenis::toggleStatus');
    $routes->post('delete', 'Cbtjenis::delete');
});

$routes->group('cbtnomorpeserta', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Cbtnomorpeserta::index');
    $routes->post('getsiswakelas', 'Cbtnomorpeserta::getSiswaKelas');
    $routes->post('savenomor', 'Cbtnomorpeserta::saveNomor');
    $routes->get('resetnomor', 'Cbtnomorpeserta::resetNomor');
});

$routes->group('cbtnilai', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Cbtnilai::index');

    // Koreksi routes
    $routes->get('koreksi', 'Cbtnilai::koreksi');
    $routes->post('get_koreksi', 'Cbtnilai::get_koreksi');
    $routes->post('get_nilai_siswa', 'Cbtnilai::get_nilai_siswa');
    $routes->post('tandaikoreksi', 'Cbtnilai::tandaiKoreksi');
    $routes->post('simpankoreksi', 'Cbtnilai::simpanKoreksi');

    // Manual Input Route
    $routes->get('inputessai', 'Cbtnilai::inputEssai');
    $routes->post('simpannilaiinput', 'Cbtnilai::simpanNilaiInput');

    // Analysis & Tools
    $routes->post('getDetailedScore', 'Cbtnilai::getDetailedScore');
    $routes->post('recalculateAll', 'Cbtnilai::recalculateAll');
    $routes->post('getScoringStats', 'Cbtnilai::getScoringStats');
    $routes->post('validateScoringConfig', 'Cbtnilai::validateScoringConfig');

    // Other missing routes
    $routes->post('save_pengurangan', 'Cbtnilai::save_pengurangan');
    $routes->post('mark_all', 'Cbtnilai::mark_all');
    $routes->post('save_katrol', 'Cbtnilai::save_katrol');
});

$routes->group('cbttoken', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Cbttoken::index');
    $routes->post('generate', 'Cbttoken::generate');
    $routes->post('toggle', 'Cbttoken::toggle');
    $routes->get('get_token', 'Cbttoken::loadToken'); // Consistent naming
    $routes->get('loadtoken', 'Cbttoken::loadToken'); // Updated from Dashboard::loadtoken
});

// ============================================
// MASTER DATA ROUTES
// ============================================

$routes->group('datatahun', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Datatahun::index');
    $routes->get('data', 'Datatahun::data');
    $routes->post('gantitahun', 'Datatahun::gantiTahun');
    $routes->post('gantisemester', 'Datatahun::gantiSemester');
    $routes->post('add', 'Datatahun::add');
    $routes->post('savehariefektif', 'Datatahun::saveHariEfektif');
    $routes->post('hapustahun', 'Datatahun::hapusTahun');
});

$routes->group('datamapel', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Datamapel::index');
    $routes->match(['get', 'post'], 'read', 'Datamapel::read'); // Allow GET/POST for datatables
    $routes->post('create', 'Datamapel::create');
    $routes->post('save', 'Datamapel::save');
    $routes->post('update', 'Datamapel::update');
    $routes->post('delete', 'Datamapel::delete');
    $routes->post('toggle_status', 'Datamapel::toggle_status');

    // Kelompok Mapel Routes
    $routes->match(['get', 'post'], 'getdatakelompok', 'Datamapel::getdatakelompok');
    $routes->match(['get', 'post'], 'getdatasubkelompok', 'Datamapel::getdatasubkelompok');
    $routes->post('addkelompokmapel', 'Datamapel::addkelompokmapel');
    $routes->post('hapuskelompok', 'Datamapel::hapuskelompok');
    $routes->post('aktifkan/(:num)', 'Datamapel::aktifkan/$1');

    // Mapel Per Kelas (Kurikulum Merdeka) Routes
    $routes->match(['get', 'post'], 'get_mapel_kelas', 'Datamapel::get_mapel_kelas');
    $routes->post('save_mapel_kelas', 'Datamapel::save_mapel_kelas');
});

$routes->group('datakelas', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Datakelas::index');
    $routes->get('detail/(:num)', 'Datakelas::detail/$1');
    $routes->get('add', 'Datakelas::add');
    $routes->get('edit/(:num)', 'Datakelas::edit/$1');
    $routes->post('save', 'Datakelas::save');
    $routes->post('hapus/(:num)', 'Datakelas::hapus/$1');

    // Bulk & Manage
    $routes->post('bulk_create', 'Datakelas::bulk_create');
    $routes->get('manage', 'Datakelas::manage');
    $routes->post('copyallfromsmt1', 'Datakelas::copyallfromsmt1');

    // Kenaikan Kelas
    $routes->get('kenaikan', 'Datakelas::kenaikan');
    $routes->post('naikkelas', 'Datakelas::naikkelas');
    $routes->post('naikkan_semua', 'Datakelas::naikkan_semua');
});

$routes->group('datasiswa', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Datasiswa::index');
    $routes->match(['get', 'post'], 'list', 'Datasiswa::list');
    $routes->get('add', 'Datasiswa::add');
    $routes->get('add/(:num)', 'Datasiswa::add/$1'); // Optional class ID
    $routes->post('create', 'Datasiswa::create');
    $routes->get('edit/(:num)', 'Datasiswa::edit/$1');
    $routes->post('updatedata', 'Datasiswa::updateData');
    $routes->post('delete', 'Datasiswa::delete');

    // Import & Bulk Update
    $routes->post('do_import', 'Datasiswa::do_import');
    $routes->get('update', 'Datasiswa::update'); // Bulk Update Page
    $routes->post('updateAll', 'Datasiswa::updateAll');
    $routes->post('update_foto', 'Datasiswa::update_foto');

    // Utilities
    $routes->post('uploadFile/(:num)', 'Datasiswa::uploadFile/$1');
    $routes->get('downloadData/(:num)', 'Datasiswa::downloadData/$1');
    $routes->post('editLogin', 'Datasiswa::editLogin');
    $routes->get('data', 'Datasiswa::data');
});

$routes->group('dataguru', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Dataguru::index');
    $routes->match(['get', 'post'], 'list', 'Dataguru::list');
    $routes->get('data', 'Dataguru::data');
    $routes->get('add', 'Dataguru::add');
    $routes->get('edit/(:num)', 'Dataguru::edit/$1');
    $routes->get('detail/(:num)', 'Dataguru::detail/$1');

    // Actions
    $routes->post('create', 'Dataguru::create');
    $routes->post('save', 'Dataguru::save');
    $routes->post('delete', 'Dataguru::delete');
    $routes->post('forcedelete', 'Dataguru::forcedelete');
    $routes->post('editlogin', 'Dataguru::editLogin');
    
    // Import & Upload
    $routes->post('do_import', 'Dataguru::do_import');
    $routes->post('uploadFile/(:num)', 'Dataguru::uploadFile/$1');
    $routes->post('deleteFile/(:num)', 'Dataguru::deleteFile/$1');
    $routes->match(['get', 'post'], 'delete_foto/(:num)', 'Dataguru::delete_foto/$1');
    $routes->match(['get', 'post'], 'delete_dokumen/(:num)', 'Dataguru::delete_dokumen/$1');

    // Jabatan & TTD
    $routes->get('editjabatan/(:num)', 'Dataguru::editJabatan/$1');
    $routes->get('get_assignment_source', 'Dataguru::getAssignmentSource');
    $routes->post('savejabatan', 'Dataguru::saveJabatan');
    $routes->post('save_signature', 'Dataguru::save_signature');
    $routes->post('delete_ttd', 'Dataguru::delete_ttd');
});

$routes->group('useradmin', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Useradmin::index');
    $routes->get('data', 'Useradmin::data');
    $routes->get('create', 'Useradmin::create'); // Page but controller says POST? No, create() has rules validation logic which implies POST
    $routes->post('create', 'Useradmin::create');
    $routes->get('edit/(:num)', 'Useradmin::edit/$1');
    $routes->post('edit_info', 'Useradmin::edit_info');
    $routes->post('edit_status', 'Useradmin::edit_status');
    $routes->post('delete/(:num)', 'Useradmin::delete/$1');
    $routes->post('change_password', 'Useradmin::change_password');
    $routes->post('uploadFile/(:num)', 'Useradmin::uploadFile/$1');
    $routes->post('deleteFile', 'Useradmin::deleteFile');
});

$routes->group('userguru', ['filter' => 'auth:admin,panitia'], function ($routes) {
    // Both Admin and Guru (self) can access some of these, logic inside controller
    $routes->get('/', 'Userguru::index');
    $routes->get('data', 'Userguru::data'); // DataTables
    $routes->get('edit/(:num)', 'Userguru::edit/$1');
    $routes->post('activate', 'Userguru::activate');
    $routes->post('reset_login', 'Userguru::reset_login');
    $routes->post('aktifkansemua', 'Userguru::aktifkansemua'); // alias
    $routes->post('aktifkan_all', 'Userguru::aktifkan_all');
    $routes->post('update_iduser', 'Userguru::update_iduser');
    $routes->post('editlogin', 'Userguru::editlogin');
});

$routes->group('userpanitia', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Userpanitia::index');
    $routes->get('data', 'Userpanitia::data');
    $routes->match(['get', 'post'], 'list', 'Userpanitia::list'); // Pagination
    $routes->post('create', 'Userpanitia::create');
    $routes->post('edit', 'Userpanitia::edit');
    $routes->post('delete', 'Userpanitia::delete');
    $routes->post('activate', 'Userpanitia::activate');
    $routes->post('reset_login', 'Userpanitia::reset_login');
    $routes->post('aktifkansemua', 'Userpanitia::aktifkansemua');
    $routes->post('generate', 'Userpanitia::generate');
    $routes->post('generate_password', 'Userpanitia::generate_password');
});

$routes->group('panitia', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Panitia::index');
    $routes->post('save', 'Panitia::save');
    $routes->post('uploadFoto', 'Panitia::uploadFoto');
    $routes->post('saveSignature', 'Panitia::saveSignature');
    $routes->post('deleteTtd', 'Panitia::deleteTtd');
});

$routes->group('panduan', ['filter' => 'auth'], function ($routes) {
    $routes->get('pengawas', 'Panduan::pengawas');
});

$routes->group('usersiswa', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Usersiswa::index');
    $routes->get('data', 'Usersiswa::data');
    $routes->match(['get', 'post'], 'list', 'Usersiswa::list'); // Pagination
    $routes->post('activate', 'Usersiswa::activate');
    $routes->post('reset_login', 'Usersiswa::reset_login');
    $routes->post('aktifkansemua', 'Usersiswa::aktifkansemua');
    $routes->post('aktifkan_all', 'Usersiswa::aktifkan_all');
});

// ...

$routes->group('guru', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Guru::index');
    $routes->post('updateProfil', 'Guru::updateProfil');
    $routes->post('updateLogin', 'Guru::updateLogin');
    $routes->post('uploadFoto', 'Guru::uploadFoto');
    $routes->post('deleteFoto', 'Guru::deleteFoto');
    $routes->post('saveSignature', 'Guru::saveSignature');
    $routes->post('deleteTtd', 'Guru::deleteTtd');
    $routes->post('deleteDokumen', 'Guru::deleteDokumen');
    $routes->post('saveApiKey', 'Guru::saveApiKey');

    // ...

    // Tujuan Pembelajaran
    $routes->group('tujuan', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
        $routes->get('/', 'Guru\Tujuan::index');
        $routes->post('create', 'Guru\Tujuan::create');
        $routes->post('delete', 'Guru\Tujuan::delete');
        $routes->post('generate_ai', 'Guru\Tujuan::generate_ai');
        $routes->post('save_batch', 'Guru\Tujuan::save_batch');
        $routes->get('export', 'Guru\Tujuan::export');
        $routes->post('toggleStatus', 'Guru\Tujuan::toggleStatus');
        $routes->post('bulkToggleStatus', 'Guru\Tujuan::bulkToggleStatus');
        $routes->get('get_cp', 'Guru\Tujuan::getCpByMapel');
        $routes->get('get_next_kode', 'Guru\Tujuan::getNextKode');
        $routes->post('duplicate', 'Guru\Tujuan::duplicate');
        $routes->post('copy_from', 'Guru\Tujuan::copyFrom');
        $routes->post('copy_from_guru', 'Guru\Tujuan::copyFromGuru');
        $routes->post('generate_kktp_ai', 'Guru\Tujuan::generateKktpAi');
        $routes->post('import_excel', 'Guru\Tujuan::importExcel');
        $routes->get('export_excel', 'Guru\Tujuan::exportExcel');
        $routes->get('download_template', 'Guru\Tujuan::downloadTemplate');
    });


    // Penilaian Kurikulum Merdeka
    $routes->group('nilai', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
        $routes->get('/', 'Guru\Nilai::index');
        $routes->get('proses', 'Guru\Nilai::inputProses');
        $routes->post('saveProses', 'Guru\Nilai::saveProses');
        $routes->post('save_proses', 'Guru\Nilai::saveProses');
        $routes->post('deleteProses', 'Guru\Nilai::deleteProses');
        $routes->post('delete_proses', 'Guru\Nilai::deleteProses');
        $routes->post('batchSaveProses', 'Guru\Nilai::batchSaveProses');
        $routes->post('batch_save_proses', 'Guru\Nilai::batchSaveProses');
        $routes->get('sts', 'Guru\Nilai::inputSts');
        $routes->post('saveSts', 'Guru\Nilai::saveSts');
        $routes->post('deleteSts', 'Guru\Nilai::deleteSts');
        $routes->post('delete_sts', 'Guru\Nilai::deleteSts');
        $routes->post('batchSaveSts', 'Guru\Nilai::batchSaveSts');
        $routes->get('akhir', 'Guru\Nilai::inputAkhir');
        $routes->post('saveAkhir', 'Guru\Nilai::saveAkhir');
        $routes->post('batchSaveAkhir', 'Guru\Nilai::batchSaveAkhir');
        $routes->post('batch_save_akhir', 'Guru\Nilai::batchSaveAkhir');
        $routes->post('saveBobot', 'Guru\Nilai::saveBobot');
        
        $routes->get('rekapExcel', 'Guru\Nilai::rekapExcel');
        $routes->get('rekapPdf', 'Guru\Nilai::rekapPdf');
        $routes->get('download_proses', 'Guru\Nilai::downloadProses');
        $routes->get('download_sts', 'Guru\Nilai::downloadSts');

        $routes->get('getCbtJadwal', 'Guru\Nilai::getCbtJadwal');
        $routes->post('getCbtNilai', 'Guru\Nilai::getCbtNilai');

        $routes->get('api/schema', 'Guru\Nilai::apiSchema');
        $routes->post('api/realtime_sync', 'Guru\Nilai::realtimeSync');
        $routes->post('api/batch_sync', 'Guru\Nilai::batchSync');
        $routes->post('api/migrate_legacy', 'Guru\Nilai::migrateLegacy');

        $routes->get('cetakleger', 'Guru\Nilai::cetakleger');
        $routes->get('dkn', 'Guru\Nilai::dkn');
    });

    // Presensi Routes (Moved to root group 'presensi')
    // $routes->get('absen', 'Guru\\Attendance::mobile');
});

// ============================================
// TUGAS WALI KELAS & CATATAN/JADWAL KELAS
// ============================================
// Catatan: group ini berada di level ROOT (bukan di dalam group 'guru')
// karena menu sidebar memakai base_url('walisiswa'), base_url('walicatatan'),
// base_url('walistruktur') — tanpa prefiks 'guru/'.
$routes->group('walisiswa', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Walisiswa::index');
    $routes->post('list', 'Walisiswa::list');
    $routes->get('edit/(:num)', 'Walisiswa::edit/$1');
    $routes->post('updatedata', 'Walisiswa::updatedata');
    $routes->post('uploadfile/(:num)', 'Walisiswa::uploadfile/$1');
    $routes->post('deletefile/(:num)', 'Walisiswa::deletefile/$1');
});

$routes->group('walistruktur', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Walistruktur::index');
    $routes->post('save', 'Walistruktur::save');
});

$routes->group('walicatatan', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Walicatatan::index');
    $routes->get('siswa', 'Walicatatan::siswa');
    $routes->post('savecatatankelas', 'Walicatatan::savecatatankelas');
    $routes->post('savecatatansiswa', 'Walicatatan::savecatatansiswa');
    // View lama memakai GET untuk hapus — keduanya diterima agar UI tidak rusak.
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Walicatatan::hapus/$1');
});

$routes->group('kelascatatan', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Kelascatatan::index');
    $routes->get('siswa', 'Kelascatatan::siswa');
    $routes->post('savecatatankelas', 'Kelascatatan::savecatatankelas');
    $routes->post('savecatatansiswa', 'Kelascatatan::savecatatansiswa');
    $routes->match(['get', 'post'], 'hapus/(:num)', 'Kelascatatan::hapus/$1');
});

// Admin Presensi Routes

$routes->group('siswa', ['filter' => 'auth:siswa'], function ($routes) {
    $routes->get('/', 'Siswa::index');
    $routes->post('gantiPassword', 'Siswa::gantiPassword');

    // Post/Komentar
    $routes->get('getpost', 'Siswa::getpost');
    $routes->get('getComment/(:num)/(:num)', 'Siswa::getComment/$1/$2');
    $routes->get('getReplies/(:num)/(:num)', 'Siswa::getReplies/$1/$2');
    $routes->post('saveKomentar', 'Siswa::saveKomentar');
    $routes->post('saveBalasan', 'Siswa::saveBalasan');

    // File upload
    $routes->post('uploadFile', 'Siswa::uploadFile');
    $routes->post('deleteFile', 'Siswa::deleteFile');

    // Catatan
    $routes->get('catatan', 'Siswa::catatan');
    $routes->get('detailcatatan/(:segment)/(:num)', 'Siswa::detailCatatan/$1/$2');
    $routes->get('readed/(:segment)/(:num)', 'Siswa::readed/$1/$2');

    // Timer
    $routes->get('getTimer/(:num)/(:num)', 'Siswa::getTimer/$1/$2');
});

$routes->group('panitia', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Panitia::index');
    $routes->post('save', 'Panitia::save');
    $routes->post('uploadFoto', 'Panitia::uploadFoto');
    $routes->post('saveSignature', 'Panitia::saveSignature');
    $routes->post('deleteTtd', 'Panitia::deleteTtd');
});


// ============================================
// UPDATE SYSTEM ROUTES
// ============================================
$routes->group('update', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Update::index');
    $routes->get('check', 'Update::check');
    $routes->post('upload', 'Update::upload');
    $routes->get('backup', 'Update::backup');
});

// ============================================
// SETTINGS ROUTES
// ============================================
$routes->group('settings', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Settings::index');
    $routes->get('db', 'Settings::dbManager');
    $routes->post('uploadFile/(:any)', 'Settings::uploadFile/$1');
    $routes->post('deleteFile', 'Settings::deleteFile');
    $routes->post('save', 'Settings::saveSetting');
    $routes->post('test-telegram', 'Settings::testTelegram');
    $routes->post('maintenance/toggle', 'Settings::toggleMaintenance');
});

// ============================================
// LAPORAN (Reports) ROUTES — ALIAS LAMA (camelCase)
// ============================================
// Group ini sebelumnya menduplikasi group 'laporan' di atas dan menunjuk ke
// method yang TIDAK ADA (Laporan::beritaAcara, ::paktaIntegritas, dll),
// sehingga selalu 404. Sudah dihapus — cukup satu group 'laporan'.

// ============================================
// PENGUMUMAN (Announcements) ROUTES
// Baca: semua role login | Tulis/Edit: hanya admin
// ============================================
$routes->group('pengumuman', ['filter' => 'auth'], function ($routes) {
    // --- Read-only (guru/panitia boleh baca + komentar) ---
    $routes->get('/', 'Pengumuman::index');
    $routes->get('getpost/(:num)', 'Pengumuman::getPost/$1');
    $routes->get('getcomment/(:num)/(:num)', 'Pengumuman::getComment/$1/$2');
    $routes->get('getreplies/(:num)/(:num)', 'Pengumuman::getReplies/$1/$2');
    $routes->get('getrunningtext', 'Pengumuman::getRunningText');
    $routes->post('savekomentar', 'Pengumuman::saveKomentar');
    $routes->post('savebalasan', 'Pengumuman::saveBalasan');

    // --- Tulis/Edit/Hapus: hanya admin ---
    $routes->post('save', 'Pengumuman::save', ['filter' => 'admin']);
    $routes->get('getdetail/(:num)', 'Pengumuman::getDetail/$1', ['filter' => 'admin']);
    $routes->post('update', 'Pengumuman::update', ['filter' => 'admin']);
    $routes->post('hapuspost/(:num)', 'Pengumuman::hapusPost/$1', ['filter' => 'admin']);
    $routes->post('hapuskomentar/(:num)', 'Pengumuman::hapusKomentar/$1', ['filter' => 'admin']);
    $routes->post('hapusbalasan/(:num)', 'Pengumuman::hapusBalasan/$1', ['filter' => 'admin']);
    $routes->post('saverunningtext', 'Pengumuman::saveRunningText', ['filter' => 'admin']);
    $routes->post('hapusrunningtext/(:num)', 'Pengumuman::hapusRunningText/$1', ['filter' => 'admin']);
    $routes->get('getusers', 'Pengumuman::getUsers', ['filter' => 'admin']);
});

// ============================================
// DATALOGS ROUTES
// ============================================
$routes->group('datalogs', ['filter' => 'auth:admin,guru,panitia'], function ($routes) {
    $routes->get('/', 'Datalogs::index');
    $routes->get('getlog', 'Datalogs::getLog');
    $routes->get('hapuslog/(:segment)', 'Datalogs::hapusLog/$1');
});

// ============================================
// CBT ROUTES
// ============================================
// Catatan: group 'cbtalokasi' / 'cbtnilai' / 'cbtrekap' versi kedua yang
// ada sebelumnya sudah dihapus — semuanya duplikat (atau menunjuk ke path
// yang tak pernah hidup karena group pertama menang). Endpoint aslinya tetap
// utuh di group utama masing-masing di atas.

$routes->group('cbtpengawas', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Cbtpengawas::index');
    $routes->get('get_tanggal', 'Cbtpengawas::get_tanggal');
    $routes->post('get_pengawas', 'Cbtpengawas::get_pengawas');
    $routes->post('savepengawas', 'Cbtpengawas::savePengawas');
});

$routes->group('cbtactivate', ['filter' => 'auth:admin,guru'], function ($routes) {
    $routes->get('/', 'Cbtactivate::index');
    $routes->get('getsiswaruang', 'Cbtactivate::getSiswaRuang');
    $routes->post('aktifkan_semua', 'Cbtactivate::aktifkan_semua');
    $routes->post('aktifkan_sesi', 'Cbtactivate::aktifkan_sesi');
});


// ============================================
// PROFIL SEKOLAH (School Profile) ROUTES
// ============================================
$routes->group('profilsekolah', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Profilsekolah::index');
    $routes->post('uploadFile/(:segment)', 'Profilsekolah::uploadFile/$1');
    $routes->post('deleteFile', 'Profilsekolah::deleteFile');
    $routes->post('saveSetting', 'Profilsekolah::saveSetting');
});

// ============================================
// DATABASE TOOLS ROUTES
// ============================================
$routes->group('dbmanager', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Dbmanager::index');
    $routes->post('backupdb', 'Dbmanager::backupDb');
    $routes->post('backupdata', 'Dbmanager::backupData');
    $routes->post('del_backup', 'Dbmanager::del_backup');
    $routes->post('hapusbackup', 'Dbmanager::hapusBackup');
    $routes->post('restore', 'Dbmanager::restore');
    $routes->get('download/(:any)', 'Dbmanager::download/$1');
});

// Chat Routes — Nonaktif (CBT murni): controller Chat tidak ada & tanpa pemanggil di UI
// $routes->group('chat', ['filter' => 'auth'], function ($routes) {
//     $routes->get('/', 'Chat::index');
//     $routes->get('rooms', 'Chat::getRooms');
//     $routes->get('messages/(:num)', 'Chat::poll/$1'); // Map messages/ID to poll
//     $routes->get('users', 'Chat::getUsers');
//     $routes->post('create', 'Chat::createRoom');
//     $routes->post('send', 'Chat::sendMessage');
//     $routes->post('ask-ai', 'Chat::askAI');
//     $routes->post('typing', 'Chat::setTypingStatus');
//     $routes->get('unread', 'Chat::getTotalUnreadCount'); // New unread route
//     $routes->post('deleteMessage', 'Chat::deleteMessage');
//     $routes->post('editMessage', 'Chat::editMessage');
//
//     // Fallback/Legacy if needed
//     $routes->get('poll/(:num)', 'Chat::poll/$1');
// });

$routes->group('dbconvert', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Dbconvert::index');
    $routes->post('convert', 'Dbconvert::convert');
    $routes->post('remaining', 'Dbconvert::remaining');
});

$routes->group('dbclear', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Dbclear::index');
    $routes->post('hapustable', 'Dbclear::hapusTable');
});
// Static Assets Fallback for Local Development issues
$routes->get('assets/(:any)', function ($path) {
    $file = FCPATH . 'assets/' . $path;
    if (is_file($file)) {
        $mime = mime_content_type($file);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
});

// ============================================
// MASTER JURUSAN ROUTES
// ============================================
$routes->group('datajurusan', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Datajurusan::index');
    $routes->match(['get', 'post'], 'data', 'Datajurusan::data');
    $routes->post('save', 'Datajurusan::save');
    $routes->post('update', 'Datajurusan::update');
    $routes->post('delete', 'Datajurusan::delete');
    $routes->post('toggle_status', 'Datajurusan::toggle_status');
    $routes->get('get_data/(:num)', 'Datajurusan::get_data/$1');
});

// ============================================
// MASTER EKSTRAKURIKULER ROUTES
// ============================================
$routes->group('dataekstra', ['filter' => 'auth:admin,panitia'], function ($routes) {
    $routes->get('/', 'Dataekstra::index');
    $routes->post('read', 'Dataekstra::read');
    $routes->post('create', 'Dataekstra::create');
    $routes->post('update', 'Dataekstra::update');
    $routes->post('save', 'Dataekstra::save');
    // View lama memakai GET untuk hapus — keduanya diterima agar UI tidak rusak.
    $routes->match(['get', 'post'], 'delete/(:num)', 'Dataekstra::delete/$1');
});

// ============================================
// AUDIT TRAIL ROUTES (Security & Monitoring)
// ============================================
$routes->group('audit', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Audit::index');
    $routes->post('getData', 'Audit::getData');
    $routes->post('getStats', 'Audit::getStats');
    $routes->get('export', 'Audit::export');
});

// ============================================
// API SINKRONISASI (SERVER PUSAT) - MINIMALIST
// ============================================
// Nonaktif (CBT murni): controller Api\Sync tidak ada; SyncModel hanya memanggil server pusat remote
// $routes->group('api/sync', ['namespace' => 'App\Controllers\Api'], function ($routes) {
//     $routes->post('test', 'Sync::test');
//     $routes->post('download', 'Sync::download');
//     $routes->post('upload', 'Sync::upload');
// });



// ============================================
// CRON JOB ENDPOINTS
// ============================================
$routes->group('cron', function ($routes) {
    $routes->get('daily_report', 'Cron::daily_report');
    $routes->get('auto_close_expired', 'Cron::auto_close_expired');
    $routes->get('dapodik_sync', 'Cron::dapodik_sync');
    $routes->get('scoring_queue', 'Cron::scoring_queue');
    $routes->get('praktik_close_expired', 'Cron::praktik_close_expired');
});

// ============================================
// EXAM BROWSER CBT API (Electron SPA)
// ============================================
$routes->group('cbt/api', static function ($routes) {
    $routes->post('login',       'ApiCbt::login');
    $routes->get('ping',       'ApiCbt::ping');
    $routes->get('jadwal',    'ApiCbt::jadwal');
    $routes->get('soal',      'ApiCbt::soal');
    $routes->post('save-jawaban','ApiCbt::saveJawaban');
    $routes->post('submit',    'ApiCbt::submit');
    $routes->post('audit',     'ApiCbt::audit');
    $routes->post('logout',    'ApiCbt::logout');
});

// ============================================
// API SINKRONISASI (SERVER PUSAT) - OLD
// ============================================
