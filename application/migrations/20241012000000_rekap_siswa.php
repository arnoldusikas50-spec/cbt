<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rekap_siswa extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id_rekap_siswa' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'id_siswa' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'nama_siswa' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
            ),
            'no_peserta' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
            ),
            'id_tp' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'tp' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
            ),
            'id_smt' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'smt' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
            ),
            'id_jadwal' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'id_bank' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'id_kelas' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'kelas' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
            ),

            'ruang_sesi' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
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
            'nilai' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),

            // dari tabel cbt_nilai berisi input nilai manual tanpa koreksi
            // rename dari versi sebelumnya `nilai_koreksi`
            'nilai_input' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),

            // dari tabel cbt_durasi_siswa
            'status' => array(
                'type' => 'INT',
                'constraint' => 2,
            ),

            // dari tabel cbt_durasi_siswa
            'mulai' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
            ),

            // dari tabel cbt_durasi_siswa
            'selesai' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
            ),

            // dari tabel cbt_durasi_siswa
            'lama_ujian' => array(
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => TRUE,
            )
        ));
        $this->dbforge->add_key('id_rekap_siswa', TRUE);
        $this->dbforge->add_key(array('id_siswa', 'id_jadwal', 'id_bank'));
        $this->dbforge->create_table('cbt_rekap_siswa', TRUE);
    }

    public function down()
    {
    }
}