<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpm extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        // Ensure user is logged in
        if (!$this->session->userdata('is_login')) {
            redirect('auth');
        }
    }

    public function index()
    {
        // Load Model
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');

        // Allow Guru (2) and Admin (1) to access
        $user_id = $this->session->userdata('session_user');
        $role = $this->session->userdata('role'); // 1: Admin, 2: Guru

        // Fetch Data
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $tp_smt = $this->dashboard->getTahunActive(); // getting active TP
        $smt_active = $this->dashboard->getSemesterActive();

        // Get All Gurus for Dropdowns
        $all_gurus = $this->master->getAllDataGuru($tp_smt->id_tp, $smt_active->id_smt);

        // Pre-process guru data to handle serialization (since maybe_unserialize is protected)
        if ($all_gurus) {
            foreach ($all_gurus as &$g) {
                $guruMaps = isset($g->mapel_kelas) ? $this->maybe_unserialize($g->mapel_kelas) : [];

                // Convert object to array if needed because maybe_unserialize returns object for assoc arrays
                if (is_object($guruMaps)) {
                    $guruMaps = (array) $guruMaps;
                }

                // Ensure plain array of strings for implode
                if (is_array($guruMaps)) {
                    // Flatten if it contains objects or arrays (though unlikely for simple mapel lists)
                    $flatMaps = [];
                    foreach ($guruMaps as $m) {
                        if (is_scalar($m)) {
                            $flatMaps[] = $m;
                        } elseif (is_object($m) || is_array($m)) {
                            // Try to find an extraction point if needed, or skip
                            $flatMaps[] = json_encode($m);
                        }
                    }
                    $g->mapels_str = implode(',', $flatMaps);
                } else {
                    $g->mapels_str = (string) $guruMaps;
                }
            }
        }

        // Get All Mapels
        $all_mapels = $this->dropdown->getAllMapel();

        $data = [
            'judul' => 'Generate RPM',
            'subjudul' => 'RPP / Modul Ajar AI',
            'setting' => $setting,
            'user' => $user,
            'tp' => $tp_smt,
            'smt' => $smt_active,
            'tp_active' => $tp_smt,
            'smt_active' => $smt_active,
            'guru' => null,
            'all_gurus' => $all_gurus,
            'all_mapels' => $all_mapels
        ];

        if ($role == '2') {
            // For Guru, fetch their specific data
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user_id, $tp_smt->id_tp, $smt_active->id_smt);
        } else {
            // For Admin, we might not have a "guru" profile, so we leave it null or fetch generic admin info
            // Admin typically doesn't generate RPP for themselves, but testing is fine.
        }

        // Load the view using the main template
        $this->render_view($role, 'cbt/rpm/data', $data);
    }

    // API Proxy if needed in future, but currently everything is client-side in rpm.html

    /**
     * Generate PDF using DomPDF (Server-side)
     * Takes HTML content via POST and streams a PDF download.
     */
    public function cetak_rpm()
    {
        // Load Pdf Library
        $this->load->library('pdf');

        $content = $this->input->post('content', FALSE); // FALSE to disable XSS filtering which might break HTML
        $filename = $this->input->post('filename');

        if (empty($content)) {
            show_error('Konten dokumen kosong.', 400);
        }

        if (empty($filename)) {
            $filename = 'Dokumen_RPM';
        }

        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

        // Load CSS
        $css_path = FCPATH . 'assets/app/css/rpm.css';
        $css = file_exists($css_path) ? file_get_contents($css_path) : '';

        // Prepare HTML for DomPDF
        // We override some styles to ensure DomPDF renders it cleanly (Static flow instead of absolute positioning)
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . $filename . '</title>
            <style>
                ' . $css . '
                
                /* DomPDF Specific Overrides (Applied Unconditionally) */
                @page { margin: 2cm; }
                body { margin: 0px; }

                * { 
                    box-sizing: border-box !important; 
                }

                /* Neutralize the paper-sheet container for PDF pages */
                .paper-sheet {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding: 0 !important; /* Rely on @page margin */
                    margin: 0 !important;
                    box-shadow: none !important;
                    border: none !important;
                    page-break-after: always;
                }

                .paper-sheet:last-child {
                    page-break-after: avoid;
                }

                /* Ensure tables and images fit */
                table, img, iframe, video {
                    max-width: 100% !important;
                    height: auto !important;
                }
                
                /* Prevent breaks inside important elements */
                .box-definisi, .box-rumus, .box-info, tr, .question-key, figure {
                    page-break-inside: avoid;
                }
            </style>
        </head>
        <body>
            <div id="rpp-content">
                ' . $content . '
            </div>
        </body>
        </html>';

        // Load HTML into DomPDF
        $this->pdf->loadHtml($html);

        // Set Paper Size
        $this->pdf->setPaper('A4', 'portrait');

        // Render
        $this->pdf->render();

        // Output
        $this->pdf->stream($filename . ".pdf", array("Attachment" => true));
    }
}
