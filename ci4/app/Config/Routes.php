<?php

use CodeIgniter\Router\RouteCollection;

/**
 * CBT AKM Application Routes
 * Migrated from CodeIgniter 3
 * 
 * @var RouteCollection $routes
 */

// ============================================
// HOME & DEFAULT
// ============================================
$routes->get('/', 'Home::index');

// ============================================
// INSTALLATION ROUTES
// ============================================
$routes->group('install', function ($routes) {
    $routes->get('/', 'Install::index');
    $routes->get('steps', 'Install::steps');
    $routes->post('checkDatabase', 'Install::checkDatabase');
    $routes->post('createDb', 'Install::createDb');
    $routes->post('createAdmin', 'Install::createAdmin');
    $routes->post('createSetting', 'Install::createSetting');
    $routes->post('createApp', 'Install::createApp');
});

// ============================================
// AUTHENTICATION ROUTES
// ============================================
$routes->group('auth', function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->get('siswa', 'Auth::siswa');
    $routes->post('login', 'Auth::login');
    $routes->get('logout', 'Auth::logout');
    $routes->post('logout', 'Auth::logout');
    $routes->post('qrcode_login', 'Auth::qrcodeLogin');
    $routes->get('register', 'Auth::register');
    $routes->post('register', 'Auth::register');
});

// ============================================
// DASHBOARD ROUTES
// ============================================
$routes->group('dashboard', function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('get_realtime_stats', 'Dashboard::get_realtime_stats');
    $routes->get('get_status_siswa', 'Dashboard::get_status_siswa');
    $routes->get('getlog', 'Dashboard::getlog');
    $routes->post('hapuslog', 'Dashboard::hapuslog');
    $routes->post('ganti_tahun', 'Dashboard::gantiTahun');
    $routes->post('ganti_semester', 'Dashboard::gantiSemester');
    $routes->get('get_sync_status', 'Dashboard::getSyncStatus');
});

// ============================================
// CBT SISWA (Student Exam) ROUTES
// ============================================
$routes->group('cbtsiswa', function ($routes) {
    $routes->get('/', 'Cbtsiswa::index');
    $routes->get('cbt', 'Cbtsiswa::cbt');
    $routes->get('get_cbt_siswa', 'Cbtsiswa::getCbtSiswa');
    $routes->post('konfirmasi/(:num)', 'Cbtsiswa::konfirmasi/$1');
    $routes->post('validasi_jadwal', 'Cbtsiswa::validasiJadwal');
    $routes->post('save_jawaban', 'Cbtsiswa::saveJawaban');
    $routes->post('selesai_ujian', 'Cbtsiswa::selesaiUjian');
    $routes->post('toggle_ragu', 'Cbtsiswa::toggleRagu');
    $routes->get('load_nomor_soal', 'Cbtsiswa::loadNomorSoal');
    $routes->post('leavecbt', 'Cbtsiswa::leavecbt');
});

// ============================================
// CBT BANK SOAL (Question Bank) ROUTES  
// ============================================
$routes->group('cbtbanksoal', function ($routes) {
    $routes->get('/', 'Cbtbanksoal::index');
    $routes->post('get_detail', 'Cbtbanksoal::get_detail');
    $routes->get('deleteBank', 'Cbtbanksoal::deleteBank');
    $routes->get('detail/(:num)', 'Cbtbanksoal::detail/$1');
    $routes->get('add', 'Cbtbanksoal::addBank');
    $routes->get('edit/(:num)', 'Cbtbanksoal::editBank/$1');
    $routes->post('save', 'Cbtbanksoal::saveBank');
    $routes->get('getgurumapel', 'Cbtbanksoal::getGuruMapel');
    $routes->get('getkelaslevel', 'Cbtbanksoal::getKelasLevel');
    $routes->post('deleteallbank', 'Cbtbanksoal::deleteAllBank');
    $routes->get('import/(:num)', 'Cbtbanksoal::import/$1');
    $routes->post('uploadsoal', 'Cbtbanksoal::uploadSoal');
    $routes->post('uploadsoalimage', 'Cbtbanksoal::uploadSoalImage');
    $routes->get('detail/(:num)', 'Cbtbanksoal::detail/$1');
});

// ============================================
// CBT JADWAL (Schedule) ROUTES
// ============================================
$routes->group('cbtjadwal', function ($routes) {
    $routes->get('/', 'Cbtjadwal::index');
    $routes->get('data', 'Cbtjadwal::data');
    $routes->post('save', 'Cbtjadwal::save');
    $routes->post('update', 'Cbtjadwal::update');
    $routes->post('delete', 'Cbtjadwal::delete');
});

// ============================================
// CBT STATUS (Monitoring) ROUTES
// ============================================
$routes->group('cbtstatus', function ($routes) {
    $routes->get('/', 'Cbtstatus::index');
    $routes->get('get_siswa_ujian', 'Cbtstatus::getSiswaUjian');
    $routes->get('get_siswa_belum_ujian', 'Cbtstatus::getSiswaBelumUjian');
    $routes->post('reset_login', 'Cbtstatus::resetLogin');
    $routes->post('reset_login', 'Cbtstatus::resetLogin');
    $routes->post('reset_waktu', 'Cbtstatus::resetWaktu');
});

// ============================================
// CBT ALOKASI (Time Allocation) ROUTES
// ============================================
$routes->group('cbtalokasi', function ($routes) {
    $routes->get('/', 'Cbtalokasi::index');
    $routes->get('detail', 'Cbtalokasi::detail');
    $routes->get('getTanggal', 'Cbtalokasi::getTanggal');
    $routes->post('getAlokasi', 'Cbtalokasi::getAlokasi');
    $routes->post('saveAlokasi', 'Cbtalokasi::saveAlokasi');
});

// ============================================
// CBT ANALISIS (Question Analysis) ROUTES
// ============================================
$routes->group('cbtanalisis', function ($routes) {
    $routes->get('/', 'Cbtanalisis::index');
    $routes->get('analisis', 'Cbtanalisis::analisis');
    $routes->get('getJadwalUjianByJadwal', 'Cbtanalisis::getJadwalUjianByJadwal');
});

// ============================================
// CBT CETAK (Print) ROUTES
// ============================================
$routes->group('cbtcetak', function ($routes) {
    $routes->get('/', 'Cbtcetak::index');
    $routes->get('kartuPeserta', 'Cbtcetak::kartuPeserta');
    $routes->get('kartuMeja', 'Cbtcetak::kartuMeja');
    $routes->get('absenPeserta', 'Cbtcetak::absenPeserta');
    $routes->get('beritaAcara', 'Cbtcetak::beritaAcara');
    $routes->get('paktaIntegritas', 'Cbtcetak::paktaIntegritas');
    $routes->get('pesertaUjian', 'Cbtcetak::pesertaUjian');
    $routes->get('pesertaUjian/(:segment)', 'Cbtcetak::pesertaUjian/$1');
    $routes->get('pengawas', 'Cbtcetak::pengawas');
    $routes->get('getSiswaKelas', 'Cbtcetak::getSiswaKelas');
    $routes->get('getSiswaRuang', 'Cbtcetak::getSiswaRuang');
    $routes->post('uploadFile/(:segment)', 'Cbtcetak::uploadFile/$1');
    $routes->post('deleteFile', 'Cbtcetak::deleteFile');
    $routes->post('saveKartu', 'Cbtcetak::saveKartu');
    $routes->post('saveKop', 'Cbtcetak::saveKop');
    $routes->post('saveKopBerita', 'Cbtcetak::saveKopBerita');
    $routes->post('saveBeritaAcara', 'Cbtcetak::saveBeritaAcara');
    $routes->post('savePaktaIntegritas', 'Cbtcetak::savePaktaIntegritas');
});

// ============================================
// CBT REKAP (Recap) ROUTES
// ============================================
$routes->group('cbtrekap', function ($routes) {
    $routes->get('/', 'Cbtrekap::index');
    $routes->get('get_jadwal', 'Cbtrekap::get_jadwal');
    $routes->get('get_rekap', 'Cbtrekap::get_rekap');
    $routes->get('view_koreksi', 'Cbtrekap::view_koreksi');
    $routes->get('ekspor_rekap', 'Cbtrekap::ekspor_rekap');
    $routes->get('get_nilai_rekap', 'Cbtrekap::get_nilai_rekap');
    $routes->get('getNilaiKelas', 'Cbtrekap::getNilaiKelas');
    $routes->post('rekap', 'Cbtrekap::rekap');
    $routes->post('rekap_nilai_siswa', 'Cbtrekap::rekap_nilai_siswa');
    $routes->post('koreksi', 'Cbtrekap::koreksi');
    $routes->post('inputEssai', 'Cbtrekap::inputEssai');
    $routes->post('hapusRekap', 'Cbtrekap::hapusRekap');
});

// ============================================
// CBT UTILITIES ROUTES
// ============================================

$routes->group('cbtruang', function ($routes) {
    $routes->get('/', 'Cbtruang::index');
    $routes->get('data', 'Cbtruang::data');
    $routes->post('save', 'Cbtruang::save');
    $routes->post('delete', 'Cbtruang::delete');
});

$routes->group('cbtsesi', function ($routes) {
    $routes->get('/', 'Cbtsesi::index');
    $routes->get('data', 'Cbtsesi::data');
    $routes->post('save', 'Cbtsesi::save');
    $routes->post('delete', 'Cbtsesi::delete');
});

$routes->group('cbtsesisiswa', function ($routes) {
    $routes->get('/', 'Cbtsesisiswa::index');
    $routes->post('editsesisiswa', 'Cbtsesisiswa::editsesisiswa');
});

$routes->group('cbtjenis', function ($routes) {
    $routes->get('/', 'Cbtjenis::index');
    $routes->post('data', 'Cbtjenis::data');
    $routes->post('add', 'Cbtjenis::add');
    $routes->post('update', 'Cbtjenis::update');
    $routes->post('togglestatus', 'Cbtjenis::toggleStatus');
    $routes->post('delete', 'Cbtjenis::delete');
});

$routes->group('cbtnomorpeserta', function ($routes) {
    $routes->get('/', 'Cbtnomorpeserta::index');
    $routes->post('getsiswakelas', 'Cbtnomorpeserta::getSiswaKelas');
    $routes->post('savenomor', 'Cbtnomorpeserta::saveNomor');
    $routes->get('resetnomor', 'Cbtnomorpeserta::resetNomor');
});

$routes->group('cbtnilai', function ($routes) {
    $routes->get('/', 'Cbtnilai::index');
    $routes->get('data', 'Cbtnilai::data');
    $routes->get('detail/(:num)', 'Cbtnilai::detail/$1');
    $routes->get('export', 'Cbtnilai::export');
});

$routes->group('cbttoken', function ($routes) {
    $routes->get('/', 'Cbttoken::index');
    $routes->post('generate', 'Cbttoken::generate');
    $routes->post('toggle', 'Cbttoken::toggle');
    $routes->get('get_token', 'Cbttoken::getToken');
    $routes->get('loadtoken', 'Dashboard::loadtoken');
});

$routes->group('pengumuman', function ($routes) {
    $routes->get('getpost/(:num)', 'Dashboard::getpost/$1');
    $routes->post('savebalasan', 'Dashboard::savebalasan');
    $routes->post('savekomentar', 'Dashboard::savekomentar');
    $routes->get('hapuspost/(:num)', 'Dashboard::hapuspost/$1');
    $routes->get('hapuskomentar/(:num)', 'Dashboard::hapuskomentar/$1');
    $routes->get('hapusbalasan/(:num)', 'Dashboard::hapusbalasan/$1');
});

// ============================================
// MASTER DATA ROUTES
// ============================================
// Data Guru
$routes->group('dataguru', function ($routes) {
    $routes->get('/', 'Dataguru::index');
    $routes->get('data', 'Dataguru::data');
    $routes->post('save', 'Dataguru::save');
    $routes->post('update', 'Dataguru::update');
    $routes->post('delete', 'Dataguru::delete');
});

// Data Siswa
$routes->group('datasiswa', function ($routes) {
    $routes->get('/', 'Datasiswa::index');
    $routes->get('data', 'Datasiswa::data');
    $routes->post('save', 'Datasiswa::save');
    $routes->post('update', 'Datasiswa::update');
    $routes->post('delete', 'Datasiswa::delete');
});

// Data Kelas
$routes->group('datakelas', function ($routes) {
    $routes->get('/', 'Datakelas::index');
    $routes->get('data', 'Datakelas::data');
    $routes->post('save', 'Datakelas::save');
    $routes->post('update', 'Datakelas::update');
    $routes->post('delete', 'Datakelas::delete');
});

// Data Mapel
$routes->group('datamapel', function ($routes) {
    $routes->get('/', 'Datamapel::index');
    $routes->get('data', 'Datamapel::data');
    $routes->post('save', 'Datamapel::save');
    $routes->post('update', 'Datamapel::update');
    $routes->post('delete', 'Datamapel::delete');
});

// Data Jurusan
$routes->group('datajurusan', function ($routes) {
    $routes->get('/', 'Datajurusan::index');
    $routes->get('data', 'Datajurusan::data');
    $routes->post('save', 'Datajurusan::save');
    $routes->post('delete', 'Datajurusan::delete');
});

$routes->group('datatahun', function ($routes) {
    $routes->get('/', 'Datatahun::index');
    $routes->post('data', 'Datatahun::data');
    $routes->post('add', 'Datatahun::add');
    $routes->post('gantitahun', 'Datatahun::gantiTahun');
    $routes->post('gantisemester', 'Datatahun::gantiSemester');
    $routes->post('savehariefektif', 'Datatahun::saveHariEfektif');
    $routes->post('hapustahun', 'Datatahun::hapusTahun');
});

// ============================================
// SETTINGS ROUTES
// ============================================
$routes->group('settings', function ($routes) {
    $routes->get('/', 'Settings::index');
    $routes->post('update', 'Settings::update');
});

// ============================================
// USER MANAGEMENT ROUTES
// ============================================
$routes->group('useradmin', function ($routes) {
    $routes->get('/', 'Useradmin::index');
    $routes->get('data', 'Useradmin::data');
    $routes->post('save', 'Useradmin::save');
    $routes->post('update', 'Useradmin::update');
    $routes->post('delete', 'Useradmin::delete');
});

$routes->group('userguru', function ($routes) {
    $routes->get('/', 'Userguru::index');
    $routes->get('data', 'Userguru::data');
    $routes->post('save', 'Userguru::save');
    $routes->post('resetPassword', 'Userguru::resetPassword');
    $routes->post('delete', 'Userguru::delete');
    $routes->post('aktifkan', 'Userguru::aktifkan');
    $routes->post('aktifkan_all', 'Userguru::aktifkanAll');
});

// ============================================
// CBT SISWA ROUTES
// ============================================
$routes->group('cbtsiswa', function ($routes) {
    $routes->get('/', 'Cbtsiswa::index');
    $routes->get('cbt', 'Cbtsiswa::cbt');
    $routes->get('get_cbt_siswa', 'Cbtsiswa::getCbtSiswa');
    $routes->post('validasi_jadwal', 'Cbtsiswa::validasiJadwal');
    $routes->get('ujian/(:num)', 'Cbtsiswa::ujian/$1');
    $routes->post('save_jawaban', 'Cbtsiswa::saveJawaban');
    $routes->post('toggle_ragu', 'Cbtsiswa::toggleRagu');
    $routes->post('selesai_ujian', 'Cbtsiswa::selesaiUjian');
});

$routes->group('usersiswa', function ($routes) {
    $routes->get('/', 'Usersiswa::index');
    $routes->get('data', 'Usersiswa::data');
    $routes->post('resetPassword', 'Usersiswa::resetPassword');
    $routes->post('resetAll', 'Usersiswa::resetAll');
    $routes->post('aktifkan', 'Usersiswa::aktifkan');
    $routes->post('aktifkan_all', 'Usersiswa::aktifkanAll');
});

$routes->group('userpanitia', function ($routes) {
    $routes->get('/', 'Userpanitia::index');
    $routes->get('data', 'Userpanitia::data');
    $routes->post('save', 'Userpanitia::save');
    $routes->post('delete', 'Userpanitia::delete');
});

// ============================================
// MEMBER AREA ROUTES
// ============================================
$routes->group('guru', function ($routes) {
    $routes->get('/', 'Guru::index');
    $routes->post('updateProfil', 'Guru::updateProfil');
});

$routes->group('siswa', function ($routes) {
    $routes->get('/', 'Siswa::index');
    $routes->post('gantiPassword', 'Siswa::gantiPassword');
});

$routes->group('panitia', function ($routes) {
    $routes->get('/', 'Panitia::index');
    $routes->post('updateProfil', 'Panitia::updateProfil');
});

// ============================================
// RAPOR ROUTES
// ============================================
$routes->group('rapor', function ($routes) {
    $routes->get('/', 'Rapor::index');
    $routes->get('nilai', 'Rapor::nilai');
    $routes->post('save_nilai', 'Rapor::saveNilai');
});

// ============================================
// LAPORAN (Reports) ROUTES
// ============================================
$routes->group('laporan', function ($routes) {
    $routes->get('/', 'Laporan::index');
    $routes->get('beritaAcara', 'Laporan::beritaAcara');
    $routes->get('cetakBeritaAcara/(:num)', 'Laporan::cetakBeritaAcara/$1');
    $routes->post('saveBeritaAcara', 'Laporan::saveBeritaAcara');
    $routes->get('paktaIntegritas', 'Laporan::paktaIntegritas');
    $routes->get('cetakPaktaIntegritas/(:num)', 'Laporan::cetakPaktaIntegritas/$1');
    $routes->post('savePaktaIntegritas', 'Laporan::savePaktaIntegritas');
});

// ============================================
// PENGUMUMAN (Announcements) ROUTES
// ============================================
$routes->group('pengumuman', function ($routes) {
    $routes->get('/', 'Pengumuman::index');
    $routes->get('kepada/(:any)', 'Pengumuman::kepada/$1');
    $routes->get('getPost/(:num)', 'Pengumuman::getPost/$1');
    $routes->get('getComment/(:num)/(:num)', 'Pengumuman::getComment/$1/$2');
    $routes->get('getReplies/(:num)/(:num)', 'Pengumuman::getReplies/$1/$2');
    $routes->post('save', 'Pengumuman::save');
    $routes->post('saveKomentar', 'Pengumuman::saveKomentar');
    $routes->post('saveBalasan', 'Pengumuman::saveBalasan');
    $routes->post('hapusPost/(:num)', 'Pengumuman::hapusPost/$1');
    $routes->post('hapusKomentar/(:num)', 'Pengumuman::hapusKomentar/$1');
    $routes->post('hapusBalasan/(:num)', 'Pengumuman::hapusBalasan/$1');
    $routes->get('getRunningText', 'Pengumuman::getRunningText');
    $routes->post('saveRunningText', 'Pengumuman::saveRunningText');
    $routes->post('hapusRunningText/(:num)', 'Pengumuman::hapusRunningText/$1');
});

// ============================================
// PROFIL SEKOLAH (School Profile) ROUTES
// ============================================
$routes->group('profilsekolah', function ($routes) {
    $routes->get('/', 'Profilsekolah::index');
    $routes->post('uploadFile/(:segment)', 'Profilsekolah::uploadFile/$1');
    $routes->post('deleteFile', 'Profilsekolah::deleteFile');
    $routes->post('saveSetting', 'Profilsekolah::saveSetting');
});
