<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DbMigrate extends Controller
{
    public function index()
    {
        $output = '<html><head><title>Database Migration</title>';
        $output .= '<style>body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;}';
        $output .= '.success{color:#155724;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;}';
        $output .= '.error{color:#721c24;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}';
        $output .= '.info{color:#0c5460;background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;}';
        $output .= 'pre{background:#333;color:#fff;padding:10px;border-radius:5px;overflow:auto;}';
        $output .= '</style></head><body>';
        $output .= '<h1>🔧 Database Migration</h1>';

        $migrate = \Config\Services::migrations();

        try {
            // Get current version before migration
            $db = \Config\Database::connect();

            // Run migration
            // Force refresh for this transition
            // $migrate->regress(0); // Rollback all
            $migrate->latest(); // Run migrations


            $output .= '<div class="success">✅ Migration completed successfully!</div>';

            // Show migration history
            $history = $db->table('migrations')->get()->getResult();
            if (!empty($history)) {
                $output .= '<div class="info"><strong>📋 Migration History:</strong><ul>';
                foreach ($history as $h) {
                    $output .= '<li>' . $h->class . ' (Batch: ' . $h->batch . ')</li>';
                }
                $output .= '</ul></div>';
            }

            // Show cbt_rekap_siswa columns
            try {
                $fields = $db->getFieldNames('cbt_rekap_siswa');
                $output .= '<div class="info"><strong>📊 cbt_rekap_siswa columns:</strong><br>';
                $output .= implode(', ', $fields);
                $output .= '</div>';
            } catch (\Exception $e) {
                $output .= '<div class="error">Table cbt_rekap_siswa not found</div>';
            }

        } catch (\Throwable $e) {
            $output .= '<div class="error">❌ Migration Failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $output .= '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }

        $output .= '<br><a href="' . site_url('dashboard') . '">← Back to Dashboard</a>';
        $output .= '</body></html>';

        return $output;
    }
}

