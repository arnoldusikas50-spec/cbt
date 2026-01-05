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

require $system . '/Common.php';
require 'd:/wwwroot/server.id/akm/ci4/vendor/autoload.php';

if (!defined('ENVIRONMENT')) {
    $dotenv = new \CodeIgniter\Config\DotEnv(ROOTPATH);
    $dotenv->load();
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
}

$db = \Config\Database::connect();

try {
    echo "Checking Student Users...\n";

    // Join with groups to find students
    $students = $db->table('users a')
        ->select('a.username, a.active, c.name as role_name')
        ->join('users_groups b', 'a.id = b.user_id')
        ->join('groups c', 'b.group_id = c.id')
        ->where('c.name', 'siswa')
        ->limit(5)
        ->get()
        ->getResult();

    if (empty($students)) {
        echo "No student users found.\n";
    } else {
        foreach ($students as $user) {
            echo "Student: " . $user->username . " (Active: " . $user->active . ", Role: " . $user->role_name . ")\n";
        }
    }
} catch (\Exception $e) {
    echo "Query Error: " . $e->getMessage() . "\n";
}
