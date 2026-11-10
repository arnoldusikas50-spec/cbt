<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\DapodikClient;

class TmpTimeApi extends BaseCommand
{
    protected $group       = 'tmp';
    protected $name        = 'tmp:timeapi';
    protected $description = 'time dapodik api calls';

    public function run(array $params)
    {
        $client = new DapodikClient();
        foreach (['getSekolah', 'getGtk', 'getPesertaDidik', 'getRombonganBelajar'] as $m) {
            $t = microtime(true);
            $r = $client->$m();
            printf("%-20s %5.1fs status=%s total=%d\n", $m, microtime(true) - $t, var_export($r['status'], true), $r['total'] ?? 0);
        }
    }
}
