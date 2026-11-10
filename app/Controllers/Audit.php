<?php

namespace App\Controllers;

use App\Models\CbtAuditModel;
use App\Models\DashboardModel;

/**
 * Audit Controller untuk melihat audit trail logs
 * Hanya accessible oleh admin
 */
class Audit extends BaseController
{
    protected $auditModel;
    protected $dashboard;

    public function __construct()
    {
        $this->auditModel = new CbtAuditModel();
        $this->dashboard = new DashboardModel();
    }

    /**
     * Main audit trail view
     */
    public function index()
    {
        if (!$this->isAdmin()) {
            return $this->showError('Hanya Administrator yang dapat mengakses halaman ini', 403);
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Audit Trail',
            'subjudul' => 'Log Perubahan Nilai',
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
        ];

        return $this->renderView('audit/index', $data);
    }

    /**
     * Get audit data via AJAX (DataTables compatible)
     */
    public function getData()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 403);
        }

        $filters = [
            'siswa_id' => $this->request->getPost('siswa_id'),
            'jadwal_id' => $this->request->getPost('jadwal_id'),
            'action' => $this->request->getPost('action'),
            'changed_by' => $this->request->getPost('changed_by'),
            'date_from' => $this->request->getPost('date_from'),
            'date_to' => $this->request->getPost('date_to'),
        ];

        // Remove empty filters
        $filters = array_filter($filters);

        $page = (int) ($this->request->getPost('page') ?? 1);
        $perPage = (int) ($this->request->getPost('per_page') ?? 25);
        $offset = ($page - 1) * $perPage;

        try {
            $logs = $this->auditModel->getAuditTrail($filters, $perPage, $offset);
            $total = $this->auditModel->countAuditTrail($filters);

            // Format data untuk display
            $formattedLogs = [];
            foreach ($logs as $log) {
                $formattedLogs[] = [
                    'id' => $log->id_audit,
                    'siswa_id' => $log->id_siswa,
                    'jadwal_id' => $log->id_jadwal,
                    'action' => $this->formatAction($log->action),
                    'action_raw' => $log->action,
                    'old_values' => $this->formatValues($log->old_values),
                    'new_values' => $this->formatValues($log->new_values),
                    'changed_by' => $log->changed_by,
                    'changed_by_role' => $log->changed_by_role,
                    'ip_address' => $log->ip_address,
                    'reason' => $log->reason,
                    'created_at' => date('d-m-Y H:i:s', strtotime($log->created_at)),
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedLogs,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get audit stats untuk dashboard
     */
    public function getStats()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['error' => 'Unauthorized'], 403);
        }

        $idJadwal = $this->request->getPost('jadwal_id');

        try {
            $stats = $this->auditModel->getAuditStats($idJadwal);

            // Additional stats
            $db = \Config\Database::connect();
            
            // Count by role
            $roleStats = $db->table('cbt_nilai_audit')
                ->select('changed_by_role, COUNT(*) as count')
                ->where('id_jadwal', $idJadwal)
                ->groupBy('changed_by_role')
                ->get()
                ->getResult();

            // Recent activity (last 24 hours)
            $recentActivity = $db->table('cbt_nilai_audit')
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->countAllResults();

            return $this->response->setJSON([
                'success' => true,
                'stats' => $stats,
                'by_role' => $roleStats,
                'recent_24h' => $recentActivity
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export audit trail ke Excel/CSV
     */
    public function export()
    {
        if (!$this->isAdmin()) {
            return $this->showError('Unauthorized', 403);
        }

        $format = $this->request->getGet('format') ?? 'csv';
        $jadwalId = $this->request->getGet('jadwal_id');

        $filters = $jadwalId ? ['jadwal_id' => $jadwalId] : [];
        $logs = $this->auditModel->getAuditTrail($filters);

        if ($format === 'csv') {
            return $this->exportCSV($logs);
        }

        // Default JSON
        return $this->response->setJSON([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Format action untuk display
     */
    private function formatAction(string $action): string
    {
        $labels = [
            'INSERT' => '<span class="badge bg-success">Insert</span>',
            'UPDATE' => '<span class="badge bg-primary">Update</span>',
            'DELETE' => '<span class="badge bg-danger">Delete</span>',
            'RECALCULATE' => '<span class="badge bg-info">Recalculate</span>',
            'CORRECT' => '<span class="badge bg-warning">Koreksi</span>',
            'MARK_CORRECTED' => '<span class="badge bg-secondary">Tandai</span>',
        ];

        return $labels[$action] ?? "<span class=\"badge bg-light\">{$action}</span>";
    }

    /**
     * Format values untuk display
     */
    private function formatValues($values): string
    {
        if (empty($values)) {
            return '-';
        }

        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        if (!is_array($values)) {
            return '-'; 
        }

        $html = '<ul class="list-unstyled mb-0">';
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $html .= "<li><small><strong>{$key}:</strong> {$value}</small></li>";
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Export ke CSV
     */
    private function exportCSV(array $logs)
    {
        $filename = 'audit_trail_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['ID', 'Siswa ID', 'Jadwal ID', 'Action', 'Old Values', 'New Values', 'Changed By', 'Role', 'IP Address', 'Reason', 'Created At']);

        // Data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->id_audit,
                $log->id_siswa,
                $log->id_jadwal,
                $log->action,
                json_encode($log->old_values ?? []),
                json_encode($log->new_values ?? []),
                $log->changed_by,
                $log->changed_by_role,
                $log->ip_address,
                $log->reason,
                $log->created_at
            ]);
        }

        fclose($output);
        exit;
    }
}
