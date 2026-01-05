<?php
// Emulate public/index.php
define('FCPATH', 'd:/wwwroot/server.id/akm/ci4/public/');
chdir(FCPATH);

$pathsConfig = 'd:/wwwroot/server.id/akm/ci4/app/Config/Paths.php';
require $pathsConfig;

$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';

exit(\CodeIgniter\Boot::bootWeb($paths));
