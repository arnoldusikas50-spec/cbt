<?php

/**
 * CBT Security & Performance Configuration
 * 
 * Copy file ini ke app/Config/Cbt.php dan sesuaikan dengan kebutuhan
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cbt extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Security Settings
     * --------------------------------------------------------------------------
     */
    
    /**
     * Enable XSS escaping untuk output nilai
     */
    public bool $enableXssEscaping = true;
    
    /**
     * Enable session fingerprinting untuk pencegahan session hijacking
     */
    public bool $enableSessionFingerprinting = true;
    
    /**
     * Enable CSRF protection untuk AJAX requests
     */
    public bool $enableCsrfForAjax = true;
    
    /**
     * Enable audit trail logging
     */
    public bool $enableAuditTrail = true;
    
    /**
     * Enable row-level locking pada save jawaban
     */
    public bool $enableRowLocking = true;

    /**
     * --------------------------------------------------------------------------
     * Caching Settings
     * --------------------------------------------------------------------------
     */
    
    /**
     * Enable multi-layer caching
     */
    public bool $enableCaching = true;
    
    /**
     * Default cache TTL (dalam detik)
     */
    public int $cacheTTL = 3600;
    
    /**
     * Cache TTL untuk soal (dalam detik)
     */
    public int $cacheTTLSoal = 7200;
    
    /**
     * Cache TTL untuk jawaban (dalam detik)
     */
    public int $cacheTTLJawaban = 1800;
    
    /**
     * Cache TTL untuk nilai (dalam detik)
     */
    public int $cacheTTLNilai = 3600;

    /**
     * --------------------------------------------------------------------------
     * Offline Capability Settings
     * --------------------------------------------------------------------------
     */
    
    /**
     * Enable Service Worker untuk offline capability
     */
    public bool $enableServiceWorker = true;
    
    /**
     * Enable IndexedDB untuk local storage jawaban
     */
    public bool $enableIndexedDB = true;
    
    /**
     * Interval auto-sync (dalam milidetik)
     */
    public int $syncInterval = 30000;
    
    /**
     * Maximum retry attempts untuk sync
     */
    public int $maxSyncRetries = 3;
    
    /**
     * Delay antara retry attempts (dalam milidetik)
     */
    public int $syncRetryDelay = 5000;

    /**
     * --------------------------------------------------------------------------
     * Scoring Validation Settings
     * --------------------------------------------------------------------------
     */
    
    /**
     * Nilai minimum yang valid
     */
    public float $nilaiMin = 0;
    
    /**
     * Nilai maximum yang valid
     */
    public float $nilaiMax = 100;
    
    /**
     * Enable katrol validation
     */
    public bool $enableKatrolValidation = true;
    
    /**
     * Enable weighted scoring
     */
    public bool $enableWeightedScoring = true;

    /**
     * --------------------------------------------------------------------------
     * Audit Trail Settings
     * --------------------------------------------------------------------------
     */
    
    /**
     * Retention period untuk audit logs (dalam hari)
     * 0 = unlimited
     */
    public int $auditRetentionDays = 365;
    
    /**
     * Enable auto-cleanup untuk audit logs lama
     */
    public bool $enableAuditCleanup = true;
    
    /**
     * Fields yang di-track di audit trail
     */
    public array $auditFields = [
        'pg_nilai',
        'nilai_asli',
        'pg_skor',
        'kompleks_skor',
        'jodohkan_skor',
        'isian_skor',
        'esai_skor',
        'urutan_skor',
        'cloze_skor'
    ];

    /**
     * --------------------------------------------------------------------------
     * Performance Settings
     * --------------------------------------------------------------------------
     */
    
    /**
     * Maximum execution time untuk batch operations (dalam detik)
     */
    public int $maxExecutionTime = 300;
    
    /**
     * Batch size untuk recalculate
     */
    public int $recalculateBatchSize = 50;
    
    /**
     * Enable query logging untuk debugging
     */
    public bool $enableQueryLog = false;
    
    /**
     * Enable slow query logging
     */
    public bool $enableSlowQueryLog = true;
    
    /**
     * Threshold untuk slow query (dalam detik)
     */
    public float $slowQueryThreshold = 1.0;

    /**
     * --------------------------------------------------------------------------
     * Rate Limiting Settings
     * --------------------------------------------------------------------------
     */
    
    /**
     * Enable rate limiting untuk API
     */
    public bool $enableRateLimiting = true;
    
    /**
     * Maximum requests per minute
     */
    public int $rateLimitPerMinute = 60;
    
    /**
     * Maximum requests per minute untuk save jawaban
     */
    public int $rateLimitSaveJawaban = 120;

    /**
     * --------------------------------------------------------------------------
     * Helper Methods
     * --------------------------------------------------------------------------
     */
    
    /**
     * Check jika fitur keamanan diaktifkan
     */
    public function isSecurityEnabled(): bool
    {
        return $this->enableXssEscaping || 
               $this->enableSessionFingerprinting || 
               $this->enableCsrfForAjax;
    }
    
    /**
     * Check jika caching diaktifkan
     */
    public function isCachingEnabled(): bool
    {
        return $this->enableCaching;
    }
    
    /**
     * Get cache TTL berdasarkan tipe data
     */
    public function getCacheTTL(string $type): int
    {
        return match($type) {
            'soal' => $this->cacheTTLSoal,
            'jawaban' => $this->cacheTTLJawaban,
            'nilai' => $this->cacheTTLNilai,
            default => $this->cacheTTL
        };
    }
}
