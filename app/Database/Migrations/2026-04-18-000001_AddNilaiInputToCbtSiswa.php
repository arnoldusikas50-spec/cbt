<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration to add nilai_input column to cbt_siswa table
 * 
 * This column is needed for storing manual score corrections (koreksi nilai)
 * when teachers manually grade student answers.
 */
class AddNilaiInputToCbtSiswa extends Migration
{
    public function up()
    {
        $tables = $this->db->listTables();
        
        if (in_array('cbt_siswa', $tables)) {
            $existingFields = $this->db->getFieldNames('cbt_siswa');
            
            if (!in_array('nilai_input', $existingFields)) {
                $this->forge->addColumn('cbt_siswa', [
                    'nilai_input' => [
                        'type' => 'TEXT',
                        'null' => true,
                        'comment' => 'Stores manual score corrections as JSON'
                    ]
                ]);
            }
        }
    }

    public function down()
    {
        $tables = $this->db->listTables();
        
        if (in_array('cbt_siswa', $tables)) {
            $existingFields = $this->db->getFieldNames('cbt_siswa');
            
            if (in_array('nilai_input', $existingFields)) {
                $this->forge->dropColumn('cbt_siswa', 'nilai_input');
            }
        }
    }
}
