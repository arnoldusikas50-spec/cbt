<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SettingsModel - Migrated from CI3 Settings_model
 * Handles application settings
 */
class SettingsModel extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id_setting';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'nama_aplikasi',
        'sekolah',
        'nss',
        'npsn',
        'jenjang',
        'satuan_pendidikan',
        'alamat',
        'desa',
        'kecamatan',
        'kota',
        'provinsi',
        'kode_pos',
        'telp',
        'fax',
        'email',
        'web',
        'kepsek',
        'nip',
        'proktor',
        'nip_proktor',
        'logo_kiri',
        'logo_kanan',
        'stampel',
        'tanda_tangan',
        'ttdproktor',
        'kodescan',
        'kopsekolah',
        'banner1',
        'banner2',
        'banner3',
        'tp_aktif',
        'smt_aktif',
        'tkn_siswa',
        'mode_app',
        'ba_aktif',
        'ba_waktu',
        'api_key',
        'enable_ai',
        'sync_school_uuid',
        'telegram_bot_token',
        'telegram_chat_id',
        'wa_gateway_url',
        'wa_token',
        'wa_target_number',
        'mikrotik_host',
        'mikrotik_port',
        'mikrotik_user',
        'mikrotik_pass',
        'maintenance_aktif',
        'maintenance_pesan',
        'maintenance_sampai'
    ];

    protected $useTimestamps = false;

    /**
     * Cache key for settings
     */
    protected const CACHE_KEY = 'app_settings';

    /**
     * Get current settings with caching
     * Adds compatibility aliases for property names used in different views
     */
    public function getSetting(): ?object
    {
        $cache = cache();
        $setting = $cache->get(self::CACHE_KEY);

        if ($setting === null) {
            $setting = $this->first();

            if ($setting) {
                $cache->save(self::CACHE_KEY, $setting, 3600);
            }
        }

        if ($setting) {
            if (isset($setting->sekolah) && !isset($setting->nama_sekolah)) {
                $setting->nama_sekolah = $setting->sekolah;
            }
            if (isset($setting->logo_kiri) && !isset($setting->logo)) {
                $setting->logo = $setting->logo_kiri;
            }
            if (isset($setting->web) && !isset($setting->website)) {
                $setting->website = $setting->web;
            }
        }

        return $setting;
    }

    /**
     * Update settings and clear cache
     */
    public function updateSetting(array $data): bool
    {
        $setting = $this->first();

        $result = false;
        if ($setting) {
            $result = $this->update($setting->id_setting, $data);
        } else {
            $result = $this->insert($data) !== false;
        }

        if ($result) {
            cache()->delete(self::CACHE_KEY);
        }

        return $result;
    }

    /**
     * Get active tahun pelajaran
     */
    public function getActiveTp(): ?int
    {
        $setting = $this->getSetting();
        return $setting ? (int) $setting->tp_aktif : null;
    }

    /**
     * Get active semester
     */
    public function getActiveSmt(): ?int
    {
        $setting = $this->getSetting();
        return $setting ? (int) $setting->smt_aktif : null;
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): bool
    {
        return cache()->delete(self::CACHE_KEY);
    }
    /**
     * Truncate tables and cleanup
     * @param array $tables
     */
    public function truncate($tables)
    {
        $db = \Config\Database::connect();
        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $tb) {
            $db->table($tb)->truncate();
        }

        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        // Delete bank soal files
        helper('filesystem');
        delete_files(FCPATH . 'uploads/bank_soal/');

        // Delete non-admin users
        $builder = $db->table('users a');
        $builder->select('a.id');
        $builder->join('users_groups b', 'a.id=b.user_id');
        $builder->whereNotIn('b.group_id', ['1']);
        $users = $builder->get()->getResult();

        foreach ($users as $user) {
            $db->table('users')->where('id', $user->id)->delete();
        }
    }

    /**
     * Get row count
     */
    public function rowSize($table)
    {
        $db = \Config\Database::connect();
        return $db->table($table)->countAllResults();
    }

    /**
     * Get table content as JSON
     */
    public function toJSON($table)
    {
        $db = \Config\Database::connect();
        $result = $db->table($table)->get()->getResult();
        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
