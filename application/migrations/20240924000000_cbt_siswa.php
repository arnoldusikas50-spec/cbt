<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_cbt_siswa extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id_cbt_siswa' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'id_siswa' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'id_jadwal' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'id_bank' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),

            // fields `jawaban` dari tabel `cbt_soal_siswa`
            // fields : array [{
            //          `id_bank` => '',
            //          `id_jadwal` => '',
            //          `id_soal` => '',
            //          `no_soal_alias,
            //          `opsi_alias_a,
            //          `opsi_alias_b,
            //          `opsi_alias_c,
            //          `opsi_alias_d,
            //          `opsi_alias_e,
            //          `jawaban_alias,
            //          `jawaban_siswa,
            //          `jawaban_benar
            //          }]
            'jawaban' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),

            // fields `nilai` dari tabel `cbt_soal_siswa`
            // fields: [{id_jadwal: idJadwal, id_bank, id_soal, point_soal, nilai_koreksi, nilai_otomatis}]
            // `nilai_koreksi` hasil dari koreksi guru untuk soal selain PG
            // `nilai_otomatis` 0=otomatis 1=dari guru
            // jika sudah dikoreksi maka indikator ada di nilai_input->dikoreksi
            'nilai' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),

            // gabungan cbt_nilai
            // untuk menyimpan nilai dari input nilai manual tanpa koreksi
            // rename dari versi sebelumnya `nilai_koreksi`
            'nilai_input' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),

            // gabungan cbt_durasi_siswa
            'status' => array(  // 0=belum ujian, 1=sedang ujian, 2=sudah ujian
                'type' => 'INT',
                'constraint' => 2,
            ),
            'mulai' => array(
                'type' => 'VARCHAR',
                'constraint' => 25,
                'null' => TRUE,
            ),
            'selesai' => array(
                'type' => 'VARCHAR',
                'constraint' => 25,
                'null' => TRUE,
            ),
            'lama_ujian' => array(
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => TRUE,
            ),
            // dari durasi
            'reset_waktu' => array(
                'type' => 'INT',
                'constraint' => 2,
            ),
            // dari log_ujian
            'reset_izin' => array(
                'type' => 'INT',
                'constraint' => 2,
            ),
        ));
        $this->dbforge->add_key('id_cbt_siswa', TRUE);
        $this->dbforge->add_key(array('id_siswa', 'id_jadwal', 'id_bank'));
        $this->dbforge->create_table('cbt_siswa', TRUE);
    }

    public function down()
    {
    }
}