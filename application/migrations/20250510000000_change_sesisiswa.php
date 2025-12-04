<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_sesisiswa extends CI_Migration {

	public function up() {
        // 1. Ubah siswa_id menjadi kolom biasa (hapus AUTO_INCREMENT)
        $sql1 = "ALTER TABLE cbt_sesi_siswa MODIFY siswa_id INT NOT NULL; ";
        $this->db->query($sql1);
        // 2. Hapus primary key dari siswa_id
        $sql2 = "ALTER TABLE cbt_sesi_siswa DROP PRIMARY KEY; ";
        $this->db->query($sql2);
        // 3. Tambahkan kolom baru id_sesi_siswa sebagai AUTO_INCREMENT dan jadikan primary key
        $sql3 = "ALTER TABLE cbt_sesi_siswa ADD COLUMN id_sesi_siswa INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST; ";
        $this->db->query($sql3);
        // 4. Ubah tipe data lain jika diperlukan
        $sql4 = "ALTER TABLE cbt_sesi_siswa MODIFY kelas_id INT;";
        $this->db->query($sql4);

        $fields = [
            'id_nomor' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
            'id_siswa' => [
                'type' => 'INT',
            ],
        ];
        $this->dbforge->modify_column('cbt_nomor_peserta', $fields);

    }
}