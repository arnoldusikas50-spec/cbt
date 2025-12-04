<?php
/**
 * @property CI_DB_query_builder|CI_DB_postgre_driver $db              This is the platform-independent base Active Record implementation class.
 * @property CI_DB_forge $dbforge                 Database Utility Class
 * @property CI_Benchmark $benchmark              This class enables you to mark points and calculate the time difference between them.<br />  Memory consumption can also be displayed.
 * @property CI_Calendar $calendar                This class enables the creation of calendars
 * @property CI_Cart $cart                        Shopping Cart Class
 * @property CI_Config $config                    This class contains functions that enable config files to be managed
 * @property CI_Controller $controller            This class object is the super class that every library in.<br />CodeIgniter will be assigned to.
 * @property CI_Email $email                      Permits email to be sent using Mail, Sendmail, or SMTP.
 * @property CI_Encrypt $encrypt                  Provides two-way keyed encoding using XOR Hashing and Mcrypt
 * @property CI_Exceptions $exceptions            Exceptions Class
 * @property CI_Form_validation $form_validation  Form Validation Class
 * @property CI_Ftp $ftp                          FTP Class
 * @property CI_Hooks $hooks                      Provides a mechanism to extend the base system without hacking.
 * @property CI_Image_lib $image_lib              Image Manipulation class
 * @property CI_Input $input                      Pre-processes global input data for security
 * @property CI_Lang $lang                        Language Class
 * @property CI_Loader $load                      Loads views and files
 * @property CI_Log $log                          Logging Class
 * @property CI_Model $model                      CodeIgniter Model Class
 * @property CI_Output $output                    Responsible for sending final output to browser
 * @property CI_Pagination $pagination            Pagination Class
 * @property CI_Parser $parser                    Parses pseudo-variables contained in the specified template view,<br />replacing them with the data in the second param
 * @property CI_Profiler $profiler                This class enables you to display benchmark, query, and other data<br />in order to help with debugging and optimization.
 * @property CI_Router $router                    Parses URIs and determines routing
 * @property CI_Session $session                  Session Class
 * @property CI_Encryption $encryption            The Encryption Library provides two-way data encryption
 * @property CI_Table $table                      HTML table generation<br />Lets you create tables manually or from database result objects, or arrays.
 * @property CI_Trackback $trackback              Trackback Sending/Receiving Class
 * @property CI_Typography $typography            Typography Class
 * @property CI_Unit_test $unit_test              Simple testing class
 * @property CI_Upload $upload                    File Uploading Class
 * @property CI_URI $uri                          Parses URIs and determines routing
 * @property CI_User_agent $user_agent            Identifies the platform, browser, robot, or mobile devise of the browsing agent
 * @property CI_Form_validation $validation
 * @property CI_Xmlrpc $xmlrpc                    XML-RPC request handler class
 * @property CI_Xmlrpcs $xmlrpcs                  XML-RPC server class
 * @property CI_Zip $zip                          Zip Compression Class
 * @property CI_Javascript $javascript            Javascript Class
 * @property CI_Jquery $jquery                    Jquery Class
 * @property CI_Utf8 $utf8                        Provides support for UTF-8 environments
 * @property CI_Security $security                Security Class, xss, csrf, etc...
 * @property Install_model $install
 * @property Datatables $datatables
 * @property Settings_model $settings
 * @property Master_model $master
 * @property Dashboard_model $dashboard
 * @property Kelas_model $kelas
 * @property Users_model $users
 * @property Cbt_model $cbt
 * @property Dropdown_model $dropdown
 * @property Log_model $logging
 * @property Rapor_model $rapor
 * @property Post_model $post
 * @property Auth_model $auth
 *
 * @property Elearning_model $elearning
 * @property Master_siswa_model $master_siswa_model
 * @property Master_kelas_model $master_kelas_model
 * @property Kelas_siswa_model $kelas_siswa_model
 * @property Jurusan_model $jurusan_model
 * @property Level_model $level_model
 * @property Guru_model $guru_model
 * @property Cbt_jadwal_model $cbt_jadwal_model
 * @property Cbt_jenis_model $cbt_jenis_model
 * @property Cbt_siswa_model $cbt_siswa_model
 * @property Cbt_ruang_model $ruang_model
 * @property Cbt_sesi_model $sesi_model
 * @property Cbt_sesisiswa_model $sesisiswa_model
 * @property Cbt_bank_model $bank_model
 * @property Cbt_soal_model $soal_model
 * @property Cbt_log_model $cbt_log_model
 * @property Cbt_jadwal_model $pengawas_model
 * @property Rekap_jadwal_model $rekap_jadwal
 * @property Rekap_siswa_model $rekap_siswa
 * @property Tp_model $tp
 * @property Smt_model $smt
 * @property Mapel_model $mapel
 *
 */
class CI_Controller
{
    public function __construct()
    {
    }
}

class CI_Model extends CI_Controller
{
}

class MY_Controller extends CI_Controller
{
}