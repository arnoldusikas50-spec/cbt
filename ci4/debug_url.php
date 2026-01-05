<?php
// Define Paths
$public = 'd:/wwwroot/server.id/akm/ci4/public';
$app = 'd:/wwwroot/server.id/akm/ci4/app';
$system = 'd:/wwwroot/server.id/akm/ci4/vendor/codeigniter4/framework/system';
$writable = 'd:/wwwroot/server.id/akm/ci4/writable';
$tests = 'd:/wwwroot/server.id/akm/ci4/tests';

define('FCPATH', $public . DIRECTORY_SEPARATOR);
define('APPPATH', $app . DIRECTORY_SEPARATOR);
define('ROOTPATH', dirname($app) . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', $system . DIRECTORY_SEPARATOR);
define('WRITEPATH', $writable . DIRECTORY_SEPARATOR);
define('TESTPATH', $tests . DIRECTORY_SEPARATOR);

chdir(FCPATH);

// Load Common (defines crucial functions like env())
require $system . '/Common.php';

// Load Autoloader
require 'd:/wwwroot/server.id/akm/ci4/vendor/autoload.php';

// Force environment if not set
if (!defined('ENVIRONMENT')) {
    $dotenv = new \CodeIgniter\Config\DotEnv(ROOTPATH);
    $dotenv->load();
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
}

// Initialize Config
$appConfig = new \Config\App();

// Load URL Helper
helper('url');

// Output site_url
echo "Site URL (auth/login): " . site_url('auth/login') . "\n";
echo "Base URL: " . base_url() . "\n";
