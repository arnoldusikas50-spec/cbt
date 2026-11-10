<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\LogModel;
use DOMDocument;

class Dbconvert extends BaseController
{
    protected $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        if (!$this->isAdmin())
            return redirect()->back();

        $user = $this->getUser();
        $db = \Config\Database::connect();

        $data = [
            'user' => $user,
            'judul' => 'Image Converter',
            'subjudul' => 'Convert Image BASE_64 ke File',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        $data['tables'] = [
            'cbt_soal' => $db->table('cbt_soal')->countAllResults(),
            'cbt_jawaban' => $db->table('cbt_jawaban')->countAllResults(),
            'cbt_siswa' => $db->table('cbt_siswa')->countAllResults(),
            'cbt_rekap_jadwal' => $db->table('cbt_rekap_jadwal')->countAllResults(),
            'cbt_rekap_siswa' => $db->table('cbt_rekap_siswa')->countAllResults()
        ];

        return view('setting/converter', $data);
    }

    /**
     * Spesifikasi tabel & kolom yang bisa dikonversi.
     * 'content'  = kolom yang diproses (dilewati jika NULL/kosong).
     * 'select'   = kolom yang diambil (PK + prefix fields + content).
     * 'prefix'   = base nama file unik per baris.
     */
    private function tableSpecs(): array
    {
        return [
            'cbt_soal' => [
                'pk' => 'id_soal',
                'select' => ['id_soal', 'bank_id', 'jenis', 'soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban', 'file1', 'deskripsi'],
                'content' => ['soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban', 'file1', 'deskripsi'],
                'prefix' => fn($r) => 'soal_' . (int) ($r->bank_id ?? 0) . '_' . (int) ($r->jenis ?? 0) . '_' . (int) $r->id_soal,
            ],
            'cbt_jawaban' => [
                'pk' => 'id_jawaban',
                'select' => ['id_jawaban', 'jawaban'],
                'content' => ['jawaban'],
                'prefix' => fn($r) => 'jawaban_' . (int) $r->id_jawaban,
            ],
            'cbt_siswa' => [
                'pk' => 'id_cbt_siswa',
                'select' => ['id_cbt_siswa', 'jawaban', 'nilai', 'nilai_input'],
                'content' => ['jawaban', 'nilai', 'nilai_input'],
                'prefix' => fn($r) => 'siswa_' . (int) $r->id_cbt_siswa,
            ],
            'cbt_rekap_jadwal' => [
                'pk' => 'id_rekap',
                'select' => ['id_rekap', 'soal_pg', 'soal_kompleks', 'soal_jodohkan', 'soal_isian', 'soal_esai', 'soal_urutan', 'soal_cloze', 'bank_soal', 'jadwal', 'pengawas'],
                'content' => ['soal_pg', 'soal_kompleks', 'soal_jodohkan', 'soal_isian', 'soal_esai', 'soal_urutan', 'soal_cloze', 'bank_soal', 'jadwal', 'pengawas'],
                'prefix' => fn($r) => 'rekapjadwal_' . (int) $r->id_rekap,
            ],
            'cbt_rekap_siswa' => [
                'pk' => 'id_rekap_siswa',
                'select' => ['id_rekap_siswa', 'jawaban', 'nilai', 'nilai_input', 'siswa'],
                'content' => ['jawaban', 'nilai', 'nilai_input', 'siswa'],
                'prefix' => fn($r) => 'rekapsiswa_' . (int) $r->id_rekap_siswa,
            ],
        ];
    }

    /**
     * Hitung sisa baris yang masih mengandung base64 image (untuk UI).
     */
    public function remaining()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);

        $table = $this->request->getPost('table');
        $specs = $this->tableSpecs();

        if (!isset($specs[$table]))
            return $this->response->setJSON(['success' => false, 'message' => 'Tabel tidak diizinkan']);

        $db = \Config\Database::connect();
        $where = implode(' OR ', array_map(fn($c) => "`$c` LIKE '%data:image%'", $specs[$table]['content']));
        $row = $db->query("SELECT COUNT(*) AS n FROM `$table` WHERE $where")->getRow();

        return $this->response->setJSON([
            'success' => true,
            'remaining' => (int) ($row->n ?? 0),
        ]);
    }

    public function convert()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);

        $table = $this->request->getPost('table');
        $limit = (int) $this->request->getPost('limit');
        $offset = (int) $this->request->getPost('offset');

        $specs = $this->tableSpecs();

        if (!isset($specs[$table]))
            return $this->response->setJSON(['success' => false, 'message' => 'Tabel tidak diizinkan']);

        // Cap limit & jaga offset valid (hindari memory blow / offset negatif)
        $limit = max(1, min($limit, 200));
        $offset = max(0, $offset);

        $db = \Config\Database::connect();
        $spec = $specs[$table];
        $pk = $spec['pk'];
        $cols = $spec['content'];

        $rows = $db->table($table)
            ->select(implode(',', $spec['select']))
            ->orderBy($pk, 'ASC')
            ->limit($limit, $offset)
            ->get()->getResult();

        $updateData = [];
        $changedRows = 0;

        foreach ($rows as $row) {
            $rowData = (array) $row;
            $rowChanged = false;
            $base = $spec['prefix']($row);

            foreach ($cols as $col) {
                $val = $row->$col ?? null;
                if ($val === null || $val === '')
                    continue;

                $fieldChanged = false;
                $decoded = $this->decodeField($val, $base . '_' . $col, $fieldChanged);

                if ($fieldChanged && $decoded !== $val) {
                    $rowData[$col] = $decoded;
                    $rowChanged = true;
                }
            }

            if ($rowChanged) {
                $updateData[] = $rowData;
                $changedRows++;
            }
        }

        if (!empty($updateData)) {
            $db->table($table)->updateBatch($updateData, $pk);
        }

        // Audit trail (tabel log bisa saja belum ada — saveLog menangani error)
        (new LogModel())->saveLog(3, "Konversi base64→file: {$table} offset {$offset} (ubah {$changedRows} baris)");

        return $this->response->setJSON([
            'success' => true,
            'page' => ($offset / $limit) + 1,
            'count' => count($rows),
            'changed' => $changedRows,
            'data' => []
        ]);
    }

    /**
     * Decode satu nilai field.
     * - Jika berupa array/object (JSON/serialized) → rekursif; hanya re-encode bila ada gambar terkonversi.
     * - Jika string HTML → decode_data.
     * $changed di-set true HANYA bila benar-benar ada konversi (tidak pernah di-reset).
     */
    private function decodeField($value, string $prefix, bool &$changed)
    {
        $parsed = $this->maybe_unserialize($value);

        if (is_array($parsed) || is_object($parsed)) {
            $result = $this->recursiveDecode($parsed, $prefix, $changed);
            return $changed ? json_encode($result) : $value;
        }

        $decoded = $this->decode_data($value, $prefix);
        if ($decoded !== $value) {
            $changed = true;
        }
        return $decoded;
    }

    private function recursiveDecode($data, string $prefix, bool &$changed)
    {
        if (is_array($data) || is_object($data)) {
            $isObj = is_object($data);
            $parsed = [];
            $i = 0;
            foreach ($data as $k => $v) {
                $parsed[$k] = $this->recursiveDecode($v, "{$prefix}_{$i}", $changed);
                $i++;
            }
            return $isObj ? (object) $parsed : $parsed;
        }

        if (is_string($data)) {
            return $this->decodeField($data, $prefix, $changed);
        }

        return $data;
    }

    protected function maybe_unserialize($str)
    {
        if (empty($str)) return $str;
        if (is_array($str) || is_object($str)) return $str;

        // Cek jika pure json
        $json = json_decode($str, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        $data = @unserialize($str);
        if ($data !== false) {
            return $data;
        }

        return $str;
    }

    /**
     * Konversi data:image di dalam HTML menjadi file fisik.
     * Hanya memproses ekstensi yang diizinkan; HTML baru dikembalikan bila ada gambar terkonversi.
     */
    private function decode_data($html, $filename_prefix)
    {
        if (empty($html) || !is_string($html)) return $html;
        if (strpos($html, 'data:image') === false) return $html; // FAST SKIP! No base64 meaning nothing to convert
        if (!$this->isHtml($html)) return $html;

        libxml_use_internal_errors(true);
        try {
            $dom = new DOMDocument();
            // Load carefully without adding DOCTYPE or HTML wrappers if possible
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_NOXMLDECL);

            $images = $dom->getElementsByTagName('img');
            $hasChanges = false;

            if ($images->length > 0) {
                $numbrimg = 1;
                foreach ($images as $image) {
                    if (!$image instanceof \DOMElement) continue;

                    $src = $image->getAttribute('src');
                    if (strpos($src, "data:image") !== 0) continue;

                    $converted = $this->writeImageFile($src, $filename_prefix, $numbrimg);
                    if ($converted === null) continue; // ekstensi tak diizinkan / base64 invalid → biarkan base64 tetap

                    $image->setAttribute('src', $converted['url']);
                    $numbrimg++;
                    $hasChanges = true;
                }

                if ($hasChanges) {
                    $res = $dom->saveHTML();
                    if ($res === false) return $html;
                    // Hanya bersihkan deklarasi XML yang kita prepend (JANGAN hapus "\n")
                    $res = str_replace('<?xml encoding="UTF-8">', '', $res);
                    return trim($res);
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Base64 convert error: " . $e->getMessage());
        }
        libxml_clear_errors();

        return $html;
    }

    /**
     * Tulis satu data URI menjadi file. Kembalikan ['url'=>...] atau null (skip tanpa ubah HTML).
     */
    private function writeImageFile(string $src, string $filename_prefix, int $numbrimg): ?array
    {
        $splited = explode(',', $src, 2);
        if (count($splited) != 2) return null;

        $mimeSplit = explode(';', $splited[0]);
        $mime = strtolower($mimeSplit[0] ?? '');

        $ext = $this->allowedImageExt($mime);
        if ($ext === null) return null; // SVG dll ditolak (XSS + ekstensi rusak)

        $decoded = base64_decode($splited[1], true);
        if ($decoded === false || $decoded === '') return null; // base64 invalid → jangan tulis file sampah

        $output_file = $filename_prefix . '_conv_' . $numbrimg . '.' . $ext;
        $uploadPath = FCPATH . 'uploads/bank_soal/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if (file_put_contents($uploadPath . $output_file, $decoded) === false) {
            log_message('error', "Failed writing image file: " . $output_file);
            return null;
        }

        return ['url' => base_url('uploads/bank_soal/' . $output_file)];
    }

    private function allowedImageExt(string $mime): ?string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/bmp'  => 'bmp',
        ];

        return $map[$mime] ?? null;
    }

    private function isHtml($string)
    {
        return preg_match('/<\s*\w+.*?>/i', ltrim($string ?? '')) == 1;
    }
}
