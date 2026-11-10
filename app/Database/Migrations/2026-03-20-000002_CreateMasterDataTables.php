<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterDataTables extends Migration
{
    public function up()
    {
        // 1. Table: setting
        $this->forge->addField([
            'id_setting' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'kode_sekolah' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'sekolah' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'npsn' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'nss' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'jenjang' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'kepsek' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nip' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'proktor' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nip_proktor' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'tanda_tangan' => ['type' => 'TEXT', 'null' => true],
            'alamat' => ['type' => 'TEXT', 'null' => true],
            'desa' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kecamatan' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'kota' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'provinsi' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kode_pos' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'telp' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'fax' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'web' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nama_aplikasi' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'logo_kanan' => ['type' => 'TEXT', 'null' => true],
            'logo_kiri' => ['type' => 'TEXT', 'null' => true],
            'kopsekolah' => ['type' => 'TEXT', 'null' => true],
            'stampel' => ['type' => 'TEXT', 'null' => true],
            'versi' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'db_versi' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'satuan_pendidikan' => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => true],
            'api_key' => ['type' => 'TEXT', 'null' => true],
            'sync_school_uuid' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ]);
        $this->forge->addKey('id_setting', true);
        $this->forge->createTable('setting', true);

        // 2. Table: master_tp (Tahun Pelajaran)
        $this->forge->addField([
            'id_tp'  => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'tahun'  => ['type' => 'VARCHAR', 'constraint' => 20],
            'active' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
        ]);
        $this->forge->addKey('id_tp', true);
        $this->forge->createTable('master_tp', true);

        // 3. Table: master_smt (Semester)
        $this->forge->addField([
            'id_smt'   => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'smt'      => ['type' => 'VARCHAR', 'constraint' => 10],
            'nama_smt' => ['type' => 'VARCHAR', 'constraint' => 10],
            'active'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
        ]);
        $this->forge->addKey('id_smt', true);
        $this->forge->createTable('master_smt', true);

        // 4. Table: master_jurusan
        $this->forge->addField([
            'id_jurusan'       => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_jurusan'     => ['type' => 'VARCHAR', 'constraint' => 30],
            'kode_jurusan'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'mapel_peminatan'  => ['type' => 'LONGTEXT', 'null' => true],
            'status'           => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'sync_school_uuid' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ]);
        $this->forge->addKey('id_jurusan', true);
        $this->forge->createTable('master_jurusan', true);

        // 5. Table: master_kelas
        $this->forge->addField([
            'id_kelas'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_tp'            => ['type' => 'INT', 'constraint' => 11],
            'id_smt'           => ['type' => 'INT', 'constraint' => 11],
            'nama_kelas'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'kode_kelas'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'jurusan_id'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'level_id'         => ['type' => 'INT', 'constraint' => 11],
            'guru_id'          => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'sync_uuid'        => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'sync_school_uuid' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kelas', true);
        $this->forge->addKey('level_id');
        $this->forge->createTable('master_kelas', true);

        // 6. Table: master_mapel
        $this->forge->addField([
            'id_mapel'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_mapel'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'kode'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'kelompok'         => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => '-'],
            'status'           => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'sync_uuid'        => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'sync_school_uuid' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_mapel', true);
        $this->forge->createTable('master_mapel', true);

        // 7. Table: master_siswa
        $this->forge->addField([
            'id_siswa'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nisn'             => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'nis'              => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama'             => ['type' => 'VARCHAR', 'constraint' => 50],
            'jenis_kelamin'    => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => true],
            'username'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'password'         => ['type' => 'TEXT', 'null' => true],
            'kelas_awal'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'foto'             => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => 'siswa.png'],
            'nik'              => ['type' => 'VARCHAR', 'constraint' => 30],
            'uid'              => ['type' => 'VARCHAR', 'constraint' => 255, 'unique' => true],
            'sync_uuid'        => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'sync_school_uuid' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa', true);
        $this->forge->createTable('master_siswa', true);

        // 8. Table: master_guru
        $this->forge->addField([
            'id_guru'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_user'          => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'nip'              => ['type' => 'CHAR', 'constraint' => 30],
            'nama_guru'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'email'            => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => true],
            'username'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'password'         => ['type' => 'LONGTEXT', 'null' => true],
            'foto'             => ['type' => 'LONGTEXT', 'null' => true],
            'sync_uuid'        => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'sync_school_uuid' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_guru', true);
        $this->forge->createTable('master_guru', true);

        // 9. Tables: bulan & hari
        $this->forge->addField([
            'id_bln'   => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_bln' => ['type' => 'VARCHAR', 'constraint' => 25],
        ]);
        $this->forge->addKey('id_bln', true);
        $this->forge->createTable('bulan', true);

        $this->forge->addField([
            'id_hri'   => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_hri' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $this->forge->addKey('id_hri', true);
        $this->forge->createTable('hari', true);
    }

    public function down()
    {
        $this->forge->dropTable('hari', true);
        $this->forge->dropTable('bulan', true);
        $this->forge->dropTable('master_guru', true);
        $this->forge->dropTable('master_siswa', true);
        $this->forge->dropTable('master_mapel', true);
        $this->forge->dropTable('master_kelas', true);
        $this->forge->dropTable('master_jurusan', true);
        $this->forge->dropTable('master_smt', true);
        $this->forge->dropTable('master_tp', true);
        $this->forge->dropTable('setting', true);
    }
}
