<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fixdb extends CI_Controller
{

    public function index()
    {
        echo "<h1>Fixing Database...</h1>";
        $this->load->database();

        $columns = [
            'tampil_urutan' => "ADD COLUMN `tampil_urutan` INT(11) NOT NULL DEFAULT 0 AFTER `tampil_esai`",
            'bobot_urutan' => "ADD COLUMN `bobot_urutan` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `tampil_urutan`",
            'tampil_cloze' => "ADD COLUMN `tampil_cloze` INT(11) NOT NULL DEFAULT 0 AFTER `bobot_urutan`",
            'bobot_cloze' => "ADD COLUMN `bobot_cloze` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `tampil_cloze`"
        ];

        foreach ($columns as $col => $sql) {
            if (!$this->db->field_exists($col, 'cbt_bank_soal')) {
                if ($this->db->query("ALTER TABLE `cbt_bank_soal` $sql")) {
                    echo "Success adding <b>$col</b><br>";
                } else {
                    $error = $this->db->error();
                    echo "Error adding $col: " . $error['message'] . "<br>";
                }
            } else {
                echo "Column <b>$col</b> already exists.<br>";
            }
        }

        echo "<br><b>Done!</b> You can now go back and save the Bank Soal. <br>";
        echo "Please delete this controller file <code>application/controllers/Fixdb.php</code> after use.";
    }
}
