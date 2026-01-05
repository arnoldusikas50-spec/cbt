<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sync Hooks
 * Helper untuk menambahkan data ke queue sync saat terjadi perubahan
 */
class Sync_hooks
{

    protected $CI;
    protected $enabled = true;
    protected $sync_tables = [
        'cbt_bank_soal' => 'id_bank',
        'cbt_soal' => 'id_soal',
        'cbt_jadwal' => 'id_jadwal',
        'cbt_siswa' => 'id',
        'cbt_durasi' => 'id_durasi',
        'cbt_nilai' => 'id_nilai',
        'cbt_log' => 'id_log',
        'master_siswa' => 'id_siswa',
        'master_guru' => 'id_guru',
        'master_kelas' => 'id_kelas'
    ];

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Disable sync hooks temporarily
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enable sync hooks
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Hook untuk INSERT
     * Panggil setelah insert data ke tabel yang disinkronkan
     */
    public function on_insert($table, $id, $data = null)
    {
        if (!$this->enabled || !isset($this->sync_tables[$table])) {
            return;
        }

        $this->add_to_queue($table, $id, 'insert', $data);
        $this->update_sync_status($table, $id, 'pending');
    }

    /**
     * Hook untuk UPDATE
     * Panggil setelah update data ke tabel yang disinkronkan
     */
    public function on_update($table, $id, $data = null)
    {
        if (!$this->enabled || !isset($this->sync_tables[$table])) {
            return;
        }

        $this->add_to_queue($table, $id, 'update', $data);
        $this->update_sync_status($table, $id, 'pending');
    }

    /**
     * Hook untuk DELETE
     * Panggil SEBELUM delete data dari tabel yang disinkronkan
     */
    public function on_delete($table, $id)
    {
        if (!$this->enabled || !isset($this->sync_tables[$table])) {
            return;
        }

        // Simpan data sebelum dihapus
        $pk = $this->sync_tables[$table];
        $data = $this->CI->db->get_where($table, [$pk => $id])->row_array();

        $this->add_to_queue($table, $id, 'delete', $data);
    }

    /**
     * Tambahkan ke sync queue
     */
    private function add_to_queue($table, $id, $action, $data = null)
    {
        // Check if sync_queue table exists
        if (!$this->CI->db->table_exists('sync_queue')) {
            return;
        }

        // Check if already in queue
        $existing = $this->CI->db->get_where('sync_queue', [
            'table_name' => $table,
            'record_id' => $id,
            'status' => 'pending'
        ])->row();

        if ($existing) {
            // Update existing queue item
            $this->CI->db->where('id', $existing->id)->update('sync_queue', [
                'action' => $action,
                'data_json' => $data ? json_encode($data) : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Create new queue item
            $this->CI->db->insert('sync_queue', [
                'table_name' => $table,
                'record_id' => $id,
                'action' => $action,
                'data_json' => $data ? json_encode($data) : null,
                'priority' => 5,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Update sync status pada record
     */
    private function update_sync_status($table, $id, $status)
    {
        $pk = $this->sync_tables[$table];

        // Check if sync_status column exists
        if ($this->CI->db->field_exists('sync_status', $table)) {
            $this->CI->db->where($pk, $id)->update($table, [
                'sync_status' => $status
            ]);
        }
    }

    /**
     * Batch update sync status
     */
    public function mark_synced($table, $ids)
    {
        if (empty($ids) || !isset($this->sync_tables[$table])) {
            return;
        }

        $pk = $this->sync_tables[$table];

        if ($this->CI->db->field_exists('sync_status', $table)) {
            $this->CI->db->where_in($pk, $ids)->update($table, [
                'sync_status' => 'synced',
                'sync_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
