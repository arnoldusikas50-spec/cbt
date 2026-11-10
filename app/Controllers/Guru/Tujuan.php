<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\CbtKartuSoalModel;
use App\Models\DashboardModel;
use App\Models\ElearningModel;
use App\Models\TujuanPembelajaranModel;

class Tujuan extends BaseController
{
    protected $dashboardModel;
    protected $elearningModel;
    protected $tpModel;
    protected $user;
    protected $guru;
    protected $tp;
    protected $smt;
    protected $kartuModel;
    protected $master;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->elearningModel = new ElearningModel();
        $this->tpModel = new TujuanPembelajaranModel();
        $this->tpModel->initialize();
        $this->kartuModel = new CbtKartuSoalModel();
        $this->master = new \App\Models\MasterModel();
    }

    private function initUser()
    {
        $this->user = $this->getUser();
        if (!$this->user)
            return false;

        $tpSmt = $this->getTpSmt();
        $this->tp = $tpSmt->tp;
        $this->smt = $tpSmt->smt;

        $isAdmin = ($this->user->role == self::ROLE_ADMIN || $this->user->role == self::ROLE_PANITIA);
        $id_guru_request = $this->request->getVar('id_guru');
        if (!$id_guru_request) {
            $json = $this->safeGetJSON();
            $id_guru_request = $json ? ($json->id_guru ?? null) : null;
        }

        if ($isAdmin && $id_guru_request) {
            $this->guru = $this->dashboardModel->getDataGuruById($id_guru_request, $this->tp->id_tp, $this->smt->id_smt);
        } else {
            $this->guru = $this->dashboardModel->getDataGuruByUserId($this->user->id, $this->tp->id_tp, $this->smt->id_smt);
        }

        return true;
    }

    public function index()
    {
        if (!$this->initUser())
            return redirect()->to('login');
        if (!$this->guru && !$this->isAdmin() && !$this->isPanitia())
            return redirect()->to('login');

        $setting = $this->dashboardModel->getSetting();
        if (!$setting)
            $setting = (object)['logo_kiri' => null, 'nama_sekolah' => 'CBT App', 'logo' => null];

        $mapel_selected = $this->request->getGet('mapel');
        $tingkat_selected = $this->request->getGet('tingkat');
        $semester_selected = $this->request->getGet('semester');
        if ($semester_selected === null) {
            $semester_selected = '';
        }
        $status_selected = $this->request->getGet('status') ?: '';
        $arrMapel = $this->getMapelGuru();

        $id_guru = null;
        $gurus = [];
        if ($this->isAdmin() || $this->isPanitia()) {
            $gurusRaw = $this->master->getAllGuru();
            foreach ($gurusRaw as $g) {
                $gurus[$g->id_guru] = $g->nama_guru;
            }
            $id_guru = $this->guru ? $this->guru->id_guru : $this->request->getGet('id_guru');
        } else {
            $id_guru = $this->guru->id_guru;
        }

        $tujuanList = $id_guru ? $this->tpModel->getTujuanByGuru(
            $id_guru,
            $semester_selected,
            $mapel_selected,
            $tingkat_selected,
            $status_selected
        ) : [];

        // Stats per mapel
        $stats = [];
        foreach ($tujuanList as $tp) {
            $mid = $tp->id_mapel ?? 0;
            if (!isset($stats[$mid])) {
                $stats[$mid] = ['nama' => $tp->nama_mapel ?? '-', 'count' => 0];
            }
            $stats[$mid]['count']++;
        }

        $data = [
            'judul' => 'Tujuan Pembelajaran',
            'user' => $this->user,
            'guru' => $this->guru,
            'tp_active' => $this->tp,
            'smt_active' => $this->smt,
            'setting' => $setting,
            'mapel_selected' => $mapel_selected,
            'tingkat_selected' => $tingkat_selected,
            'semester_selected' => $semester_selected,
            'status_selected' => $status_selected,
            'mapel_list' => $arrMapel,
            'tujuan_list' => $tujuanList,
            'stats' => $stats,
            'gurus' => $gurus,
            'id_guru_selected' => $id_guru,
            'is_admin' => $this->isAdmin(),
            'is_panitia' => $this->isPanitia(),
        ];

        return view('guru/tujuan/index', $data);
    }

    public function create()
    {
        if (!$this->initUser())
            return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $id = $this->request->getPost('id_tujuan');
        $id_mapel = $this->request->getPost('id_mapel');
        $tingkat = $this->request->getPost('tingkat');
        $id_smt = $this->request->getPost('id_smt');
        $fase = $this->request->getPost('fase');
        $kode = $this->request->getPost('kode');
        $tujuan = $this->request->getPost('tujuan');

        // Field baru Kurikulum Merdeka
        $kode_cp = trim((string)($this->request->getPost('kode_cp') ?? ''));
        if ($kode_cp !== '' && !preg_match('/^[A-Za-z0-9\-_\.]+$/', $kode_cp)) {
            $kode_cp = '';
        }
        $elemen_cp = trim((string)($this->request->getPost('elemen_cp') ?? ''));
        $tp_sikap = trim((string)($this->request->getPost('tp_sikap') ?? ''));
        $tp_keterampilan = trim((string)($this->request->getPost('tp_keterampilan') ?? ''));
        $level_kognitif = trim((string)($this->request->getPost('level_kognitif') ?? ''));
        $materi_pokok = trim((string)($this->request->getPost('materi_pokok') ?? ''));
        $kriteria_ketercapaian = trim((string)($this->request->getPost('kriteria_ketercapaian') ?? ''));

        if (!$id_mapel || !$tujuan) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mapel dan Rumusan TP wajib diisi']);
        }
        if (mb_strlen(trim($tujuan)) < 10) {
            return $this->response->setJSON(['status' => false, 'message' => 'Rumusan TP terlalu pendek. Tulis KKO + materi yang jelas agar terukur.']);
        }

        $mapelGuru = $this->getMapelGuru();
        if (!isset($mapelGuru[$id_mapel])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mata Pelajaran tidak valid atau bukan milik Anda']);
        }

        $data = [
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $id_mapel,
            'id_tp' => $this->tp->id_tp,
            'id_smt' => $id_smt ?: $this->smt->id_smt,
            'tingkat' => $tingkat ?: 0,
            'fase' => $fase ?: $this->faseFromTingkat($tingkat),
            'kode' => $kode,
            'kode_cp' => $kode_cp ?: null,
            'elemen_cp' => $elemen_cp ?: null,
            'tujuan' => $tujuan,
            'tp_sikap' => $tp_sikap ?: null,
            'tp_keterampilan' => $tp_keterampilan ?: null,
            'level_kognitif' => $level_kognitif !== '' ? $this->normalizeLevelKognitif($level_kognitif) : $this->detectLevelKognitif($tujuan),
            'materi_pokok' => $materi_pokok ?: null,
            'kriteria_ketercapaian' => $kriteria_ketercapaian ?: null,
        ];

        if ($id) {
            $this->tpModel->update($id, $data);
            $this->propagateToKartuSoalItems($id, $data);
            $msg = 'Berhasil diperbarui';
        }
        else {
            $this->tpModel->insert($data);
            $msg = 'Berhasil disimpan';
        }

        return $this->response->setJSON(['status' => true, 'message' => $msg]);
    }
    public function delete()
    {
        if (!$this->initUser())
            return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $ids = $this->request->getPost('ids');
        $id = $this->request->getPost('id');

        if ($ids && is_array($ids)) {
            $canDelete = [];
            $usedCount = 0;
            $notOwnedCount = 0;
            foreach ($ids as $idItem) {
                $tp = $this->tpModel->find($idItem);
                if (!$tp || (int)$tp->id_guru !== (int)$this->guru->id_guru) {
                    $notOwnedCount++;
                    continue;
                }
                if (!$this->tpModel->isUsed($idItem)) {
                    $canDelete[] = $idItem;
                } else {
                    $usedCount++;
                }
            }

            if (empty($canDelete) && $notOwnedCount > 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Tidak dapat menghapus TP milik guru lain']);
            }
            if (empty($canDelete)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Semua TP yang dipilih sedang digunakan dan tidak dapat dihapus']);
            }

            // only delete owned records
            $result = $this->tpModel->whereIn('id_tujuan', $canDelete)
                ->where('id_guru', $this->guru->id_guru)
                ->delete();
            $msg = count($canDelete) . ' data berhasil dihapus';
            if ($usedCount > 0) {
                $msg .= ", {$usedCount} data lainnya gagal dihapus karena sudah digunakan";
            }
            if ($notOwnedCount > 0) {
                $msg .= ", {$notOwnedCount} data bukan milik Anda";
            }

            return $this->response->setJSON([
                'status' => $result ? true : false,
                'total' => count($canDelete),
                'message' => $msg
            ]);
        } elseif ($id) {
            // Cek kepemilikan
            $tp = $this->tpModel->find($id);
            if (!$tp || (int)$tp->id_guru !== (int)$this->guru->id_guru) {
                return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan atau bukan milik Anda']);
            }

            if ($this->tpModel->isUsed($id)) {
                return $this->response->setJSON(['status' => false, 'message' => 'TP tidak dapat dihapus karena sudah digunakan di Kartu Soal/Bank Soal']);
            }

            if ($this->tpModel->where('id_guru', $this->guru->id_guru)->delete($id)) {
                return $this->response->setJSON(['status' => true, 'message' => 'Data dihapus']);
            }
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus atau tidak ada data dipilih']);
    }
    /**
     * Propagate TP field changes to denormalized columns in cbt_kartu_soal_item
     */
    private function propagateToKartuSoalItems(int $idTujuan, array $data): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('cbt_kartu_soal_item')) {
            return;
        }

        $updateFields = [];
        if (isset($data['tujuan'])) {
            $updateFields['tujuan'] = $data['tujuan'];
        }
        if (isset($data['level_kognitif'])) {
            $updateFields['level_kognitif'] = $data['level_kognitif'];
        }
        if (isset($data['materi_pokok'])) {
            $updateFields['materi'] = $data['materi_pokok'];
        }
        if (isset($data['kode_cp'])) {
            $updateFields['cp'] = $data['kode_cp'];
        }

        if (!empty($updateFields)) {
            try {
                $db->table('cbt_kartu_soal_item')
                    ->where('id_tujuan', $idTujuan)
                    ->update($updateFields);
            } catch (\Throwable $e) {
                log_message('error', '[Tujuan::propagateToKartuSoalItems] ' . $e->getMessage());
            }
        }
    }

    // --- Helper ---
    protected function getMapelGuru()
    {
        if (!$this->guru) {
            $id_guru = $this->request->getVar('id_guru');
            if ($id_guru) {
                $this->guru = $this->dashboardModel->getDataGuruById($id_guru, $this->tp->id_tp, $this->smt->id_smt);
            }
        }

        $arrMapel = [];

        if ($this->guru) {
            $mapel_guru = $this->elearningModel->getGuruMapelKelas($this->guru->id_guru, $this->tp->id_tp, $this->smt->id_smt);
            if ($mapel_guru && $mapel_guru->mapel_kelas) {
                $mapelList = @unserialize($mapel_guru->mapel_kelas);
                if ($mapelList === false && $mapel_guru->mapel_kelas != 'b:0;') {
                    $mapelList = json_decode($mapel_guru->mapel_kelas, true);
                    if (json_last_error() !== JSON_ERROR_NONE)
                        $mapelList = [];
                }

                if ($mapelList && is_array($mapelList)) {
                    foreach ($mapelList as $m) {
                        if (isset($m['id_mapel']) && isset($m['nama_mapel'])) {
                            $arrMapel[$m['id_mapel']] = $m['nama_mapel'];
                        }
                    }
                }
            }
        }

        // Fallback to all master mapels if teacher has no specific mapel_kelas or no teacher selected
        if (empty($arrMapel)) {
            $allMapel = $this->master->getAllMapel();
            if ($allMapel && is_array($allMapel)) {
                foreach ($allMapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                }
            }
        }

        return $arrMapel;
    }

    /**
     * AI Generate TP — nonaktif (modul Perangkat dihapus).
     */
    public function generate_ai()
    {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Fitur AI TP Generator tidak tersedia.',
        ]);
    }

    /**
     * Generate TP by topic — enhanced dengan 3 ranah + CP + KKTP sesuai Kurikulum Merdeka 2024
     */
    private function generateTopicTp($apiKey, $mapelName, $mapelId, $topic, $fase, $tingkat, $jumlah, $instruksi, $existingTexts, array $cpKodeList = [])
    {
        $faseLabel = $this->getFaseLabel($fase);
        $tingkatLabel = $tingkat ? "Kelas {$tingkat}" : 'Umum';
        $kodePrefix = $tingkat ? $tingkat : 'TP';

        $cpListText = $cpKodeList !== [] ? implode(', ', $cpKodeList) : '';

        $prompt = <<<PROMPT
Anda adalah Pakar Kurikulum Merdeka Indonesia (Permendikdasmen No.13 Tahun 2025 dan Keputusan Kepala BSKAP No.024/H/KR/2025).
Tugas Anda adalah membuat {$jumlah} Tujuan Pembelajaran (TP) yang lengkap dan berkualitas tinggi,
mencakup TIGA RANAH KOMPETENSI sesuai standar Kurikulum Merdeka.

## KONTEKS
- Mata Pelajaran: {$mapelName}
- Topik/Materi: {$topic}
- Fase: {$faseLabel}
- Tingkat: {$tingkatLabel}
PROMPT;

        if ($cpListText !== '') {
            $prompt .= "\n## KODE CP VALID (WAJIB PILIH DARI DAFTAR INI)\n";
            $prompt .= "Kode CP resmi yang tersedia di database untuk mapel & fase ini: {$cpListText}\n";
            $prompt .= "- Field \"kode_cp\" pada output WAJIB salah satu kode di atas (atau string kosong jika tidak ada yang relevan).\n";
            $prompt .= "- JANGAN mengarang kode CP baru selain daftar tersebut.\n";
        }

        $prompt .= <<<PROMPT

## ATURAN PENYUSUNAN TP KURIKULUM MERDEKA & DEEP LEARNING (STANDAR RESMI)
1. **Ranah Pengetahuan** (wajib): KKO Taksonomi Bloom + Konten spesifik (TIDAK boleh diawali "Siswa mampu...").
2. **Ranah Sikap** (direkomendasikan): Kaitkan dengan dimensi Profil Pelajar Pancasila (Beriman, Berkebinekaan, Bergotong-royong, Mandiri, Bernalar Kritis, Kreatif).
3. **Ranah Keterampilan** (direkomendasikan): Keterampilan aplikatif yang dapat diamati, dipraktikkan, atau dipresentasikan.
4. **Indikator Ketercapaian (KKTP)**: WAJIB berupa 3-5 butir operasional konkret menggunakan format bullet points ("• Menjelaskan...\n• Mengidentifikasi...\n• Menganalisis...").
5. **Distribusikan level kognitif** dari C2 s/d C5/C6 secara runtut dan mendalam.
6. **Elemen CP & Materi Pokok**: Spesifik, jelas, dan relevan dengan ruang lingkup mapel.

## Referensi KKO Taksonomi Bloom Revisi:
- C1 (Mengingat): Menyebutkan, Mengidentifikasi, Mendaftar, Mengenali
- C2 (Memahami): Menjelaskan, Membandingkan, Mengklasifikasikan, Merangkum
- C3 (Menerapkan): Menghitung, Menerapkan, Menggunakan, Melaksanakan
- C4 (Menganalisis): Menganalisis, Membedakan, Mengorganisasikan, Menghubungkan
- C5 (Mengevaluasi): Menilai, Mengkritisi, Menyimpulkan, Membuktikan
- C6 (Mencipta): Merancang, Menghasilkan, Merumuskan, Menciptakan
PROMPT;

        if (!empty($instruksi)) {
            $prompt .= "\n## INSTRUKSI TAMBAHAN DARI GURU\n{$instruksi}\n";
        }

        $prompt .= <<<PROMPT

## FORMAT OUTPUT
JSON array MURNI (tanpa markdown code block). Setiap item WAJIB memiliki field:
- "kode": string kode TP (format: "{$kodePrefix}.1", "{$kodePrefix}.2", dst)
- "tujuan": string TP ranah PENGETAHUAN (langsung KKO + konten, tanpa "Siswa mampu")
- "tp_sikap": string TP ranah SIKAP (Profil Pelajar Pancasila, atau string kosong jika tidak relevan)
- "tp_keterampilan": string TP ranah KETERAMPILAN (keterampilan observable, atau string kosong)
- "level_kognitif": string level Bloom ("C1" s/d "C6")
- "materi_pokok": string nama materi/bahan kajian spesifik
- "kriteria_ketercapaian": string KKTP multi-baris bullet points ("• Indikator 1\n• Indikator 2\n• Indikator 3")
- "kode_cp": string kode CP acuan (kosongkan jika tidak dapat menentukan, format: "CP-XXX-{$fase}-NN")
- "elemen_cp": string nama elemen/domain CP (contoh: Konsep Ekonomi, Ekonomi Mikro, Bilangan, Aljabar, dll.)

Contoh satu item:
{"kode":"{$kodePrefix}.1","tujuan":"Menganalisis mekanisme terbentuknya harga keseimbangan pasar","tp_sikap":"Bernalar kritis dan mandiri dalam menginterpretasikan data pasar","tp_keterampilan":"Menyajikan kurva ekuilibrium permintaan dan penawaran","level_kognitif":"C4","materi_pokok":"Harga dan Keseimbangan Pasar","kriteria_ketercapaian":"• Menjelaskan konsep harga pasar\n• Menentukan titik harga keseimbangan (ekuilibrium)\n• Menganalisis dampak pergeseran kurva permintaan dan penawaran\n• Menginterpretasikan kurva keseimbangan pasar secara matematis dan grafis","kode_cp":"CP-EKO-{$fase}-01","elemen_cp":"Ekonomi Mikro"}
PROMPT;

        $responseText = $this->sendToAI($apiKey, $prompt, 6144);
        $generated = $this->parseAIResponse($responseText);

        if ($generated && is_array($generated)) {
            // Sanitasi output: pastikan semua field baru hadir
            $generated = array_map(fn($item) => $this->sanitizeGeneratedTp($item), $generated);
            $generated = $this->filterValidCpCodes($generated, $cpKodeList);
            $generated = $this->markDuplicates($generated, $existingTexts);
            return $this->response->setJSON(['success' => true, 'data' => $generated, 'mode' => 'topic']);
        }

        $err = is_string($generated) ? $generated : 'Gagal parsing respon AI.';
        return $this->response->setJSON(['success' => false, 'message' => $err]);
    }

    /**
     * Generate TP satu semester penuh berbasis CP — dengan 3 ranah + KKTP sesuai Kurikulum Merdeka 2024
     */
    private function generateSemesterTp($apiKey, $mapelName, $mapelId, $fase, $tingkat, $existingTexts, $semester_override = null, array $cpKodeList = [])
    {
        $faseLabel = $this->getFaseLabel($fase);
        $tingkatLabel = $tingkat ? "Kelas {$tingkat}" : 'Umum';
        $semester = $semester_override ?: ($this->smt->id_smt ?? 1);
        $semLabel = $semester == 1 ? 'Ganjil (1)' : 'Genap (2)';
        $kodePrefix = $tingkat ? $tingkat : 'TP';

        $cpListText = $cpKodeList !== [] ? implode(', ', $cpKodeList) : '';

        $prompt = <<<PROMPT
Anda adalah Pakar Kurikulum Merdeka Indonesia yang berpengalaman, sesuai Permendikdasmen No.13 Tahun 2025 dan Keputusan Kepala BSKAP No.024/H/KR/2025.

## TUGAS
Buatkan Tujuan Pembelajaran (TP) LENGKAP untuk SATU SEMESTER PENUH berdasarkan Capaian Pembelajaran (CP)
resmi Kurikulum Merdeka. Setiap TP HARUS mencakup TIGA RANAH KOMPETENSI.

## KONTEKS
- Mata Pelajaran: {$mapelName}
- Fase: {$faseLabel}
- Tingkat/Kelas: {$tingkatLabel}
- Semester: {$semLabel}
PROMPT;

        if ($cpListText !== '') {
            $prompt .= "\n## KODE CP VALID (WAJIB PILIH DARI DAFTAR INI)\n";
            $prompt .= "Kode CP resmi yang tersedia di database untuk mapel & fase ini: {$cpListText}\n";
            $prompt .= "- Field \"kode_cp\" pada output WAJIB salah satu kode di atas (atau string kosong jika tidak ada yang relevan).\n";
            $prompt .= "- JANGAN mengarang kode CP baru selain daftar tersebut.\n";
        }

        $prompt .= <<<PROMPT

## PANDUAN PENYUSUNAN TP KURIKULUM MERDEKA & DEEP LEARNING (STANDAR RESMI KEMENDIKDASMEN)

### Langkah & Alur Pedagogis:
1. **Identifikasi CP resmi** untuk {$mapelName} pada {$faseLabel}.
2. **Turunkan TP** dari setiap elemen CP secara spesifik, runtut, dan terukur.
3. **Pastikan 3 ranah** untuk setiap TP:
   - **Pengetahuan**: KKO Bloom + konten spesifik (TIDAK diawali "Siswa mampu").
   - **Sikap**: Dimensi Profil Pelajar Pancasila yang relevan (Beriman & Bertakwa, Berkebinekaan Global, Bergotong-royong, Mandiri, Bernalar Kritis, Kreatif).
   - **Keterampilan**: Keterampilan observable, aplikatif, dan dapat diukur.
4. **Indikator Ketercapaian (KKTP)**: WAJIB berisi 3 sampai 5 butir indikator operasional konkret diawali tanda bullet "• " (misal: "• Menjelaskan pengertian...\n• Mengidentifikasi faktor...\n• Menganalisis dampak...").
5. **Alur Progresif & Komprehensif**:
   - Dimulai dari pemahaman konsep dasar (C2)
   - Mekanisme aplikasi dan perhitungan/analisis (C3/C4)
   - Kebijakan, peran pelaku/institusi, dan struktur sistem (C4)
   - Proyek analisis kontekstual / fenomena riil (C4/C6)
   - Asesmen sumatif semester dan evaluasi tindak lanjut (C4/C5)
6. **Jumlah**: 8 sampai 16 TP terstruktur untuk 1 semester penuh.

### KKO Taksonomi Bloom Revisi:
- C1: Menyebutkan, Mengidentifikasi, Mendaftar, Mengenali
- C2: Menjelaskan, Membandingkan, Mengklasifikasikan, Merangkum
- C3: Menghitung, Menerapkan, Menggunakan, Melaksanakan
- C4: Menganalisis, Membedakan, Mengorganisasikan, Menghubungkan
- C5: Menilai, Mengkritisi, Menyimpulkan, Membuktikan
- C6: Merancang, Menghasilkan, Merumuskan, Menciptakan

## FORMAT OUTPUT
JSON array MURNI (TANPA markdown code block). Setiap item WAJIB memiliki:
- "kode": format "{$kodePrefix}.1", "{$kodePrefix}.2", dst
- "tujuan": TP ranah PENGETAHUAN (langsung KKO + konten spesifik, tanpa "Siswa mampu")
- "tp_sikap": TP ranah SIKAP (terkait Profil Pelajar Pancasila)
- "tp_keterampilan": TP ranah KETERAMPILAN (kegiatan aplikatif & terukur)
- "level_kognitif": "C1" s/d "C6"
- "elemen_cp": nama elemen/domain CP yang relevan
- "materi_pokok": materi pokok pembelajaran dan sub-kajian
- "kriteria_ketercapaian": format string multi-baris bullet points ("• Indikator 1\n• Indikator 2\n• Indikator 3")
- "kode_cp": kode CP acuan (format "CP-XXX-{$fase}-NN", atau string kosong jika tidak dapat ditentukan)

Contoh item:
{"kode":"{$kodePrefix}.1","tujuan":"Menjelaskan konsep permintaan, penawaran, dan faktor-faktor yang memengaruhinya","tp_sikap":"Menunjukkan nalar kritis dan kemandirian dalam menganalisis dinamika pasar","tp_keterampilan":"Menyajikan kurva pergeseran permintaan dan penawaran berdasarkan data pasar","level_kognitif":"C2","elemen_cp":"Ekonomi Mikro","materi_pokok":"Permintaan dan Penawaran","kriteria_ketercapaian":"• Menjelaskan pengertian permintaan\n• Menjelaskan pengertian penawaran\n• Mengidentifikasi faktor yang memengaruhi permintaan\n• Mengidentifikasi faktor yang memengaruhi penawaran\n• Menganalisis perubahan permintaan dan penawaran","kode_cp":"CP-EKO-{$fase}-01"}
PROMPT;

        $responseText = $this->sendToAI($apiKey, $prompt, 8192);
        $generated = $this->parseAIResponse($responseText);

        if ($generated && is_array($generated)) {
            $generated = array_map(fn($item) => $this->sanitizeGeneratedTp($item), $generated);
            $generated = $this->filterValidCpCodes($generated, $cpKodeList);
            $generated = $this->markDuplicates($generated, $existingTexts);
            return $this->response->setJSON(['success' => true, 'data' => $generated, 'mode' => 'semester']);
        }

        $err = is_string($generated) ? $generated : 'Gagal parsing respon AI.';
        return $this->response->setJSON(['success' => false, 'message' => $err]);
    }

    /**
     * Normalisasi level kognitif agar hanya menyimpan nilai standar C1–C6
     * (fallback C4 bila nilai invalid / kosong) — konsisten dengan importExcel.
     */
    private function normalizeLevelKognitif($raw): ?string
    {
        $val = strtoupper(trim((string) ($raw ?? '')));
        if ($val === '') {
            return null;
        }
        return in_array($val, ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'], true) ? $val : 'C4';
    }

    /**
     * Turunan fase otomatis dari tingkat kelas (form sederhana tidak wajib isi fase).
     */
    private function faseFromTingkat($tingkat): ?string
    {
        $t = (int) $tingkat;
        if ($t === 1 || $t === 2) return 'A';
        if ($t === 3 || $t === 4) return 'B';
        if ($t === 5 || $t === 6) return 'C';
        if ($t >= 7 && $t <= 9) return 'D';
        if ($t === 10) return 'E';
        if ($t === 11 || $t === 12) return 'F';
        return null;
    }

    /**
     * Deteksi level Bloom otomatis dari KKO di rumusan TP (form sederhana).
     */
    private function detectLevelKognitif(string $tujuan): ?string
    {
        $lower = mb_strtolower($tujuan);
        $map = [
            'C1' => ['menyebutkan', 'mengidentifikasi', 'mendaftar', 'mengenali', 'memilih'],
            'C2' => ['menjelaskan', 'membandingkan', 'mengklasifikasikan', 'merangkum', 'menguraikan', 'mendeskripsikan', 'menafsirkan'],
            'C3' => ['menghitung', 'menerapkan', 'menggunakan', 'melaksanakan', 'melakukan', 'memecahkan', 'mengoperasikan'],
            'C4' => ['menganalisis', 'membedakan', 'mengorganisasikan', 'menghubungkan', 'mengkategorikan'],
            'C5' => ['menilai', 'mengkritisi', 'menyimpulkan', 'membuktikan', 'mempertahankan', 'mengevaluasi', 'mengkritik'],
            'C6' => ['merancang', 'menghasilkan', 'merumuskan', 'menciptakan', 'membangun', 'mengembangkan', 'menyusun', 'membuat'],
        ];
        foreach ($map as $level => $kkos) {
            foreach ($kkos as $kko) {
                if (str_starts_with($lower, $kko) || str_contains($lower, ' ' . $kko . ' ') || str_contains($lower, ' ' . $kko)) {
                    return $level;
                }
            }
        }
        return 'C4';
    }

    /**
     * Sanitasi dan normalisasi satu item TP yang di-generate AI
     * Memastikan semua field baru Kurikulum Merdeka hadir dan valid
     */
    private function sanitizeGeneratedTp(array $item): array
    {
        // Normalisasi kode_cp
        $kodeCp = trim((string)($item['kode_cp'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9\-_\.]+$/', $kodeCp)) {
            $kodeCp = '';
        }

        // Validasi level kognitif
        $level = strtoupper(trim((string)($item['level_kognitif'] ?? 'C4')));
        if (!in_array($level, ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'], true)) {
            $level = 'C4';
        }

        return [
            'kode' => (string)($item['kode'] ?? ''),
            'tujuan' => trim((string)($item['tujuan'] ?? '')),
            'tp_sikap' => trim((string)($item['tp_sikap'] ?? '')),
            'tp_keterampilan' => trim((string)($item['tp_keterampilan'] ?? '')),
            'level_kognitif' => $level,
            'elemen_cp' => trim((string)($item['elemen_cp'] ?? '')),
            'materi_pokok' => trim((string)($item['materi_pokok'] ?? '')),
            'kriteria_ketercapaian' => trim((string)($item['kriteria_ketercapaian'] ?? '')),
            'kode_cp' => $kodeCp,
            'is_duplicate' => $item['is_duplicate'] ?? false,
        ];
    }

    /**
     * Bersihkan kode_cp hasil AI yang tidak ada di daftar CP valid (jika daftar tersedia),
     * agar tidak tersimpan kode CP orphan (tidak terhubung ke e_capaian_pembelajaran).
     */
    private function filterValidCpCodes(array $items, array $cpKodeList): array
    {
        if ($cpKodeList === []) {
            return $items;
        }
        $validSet = array_flip($cpKodeList);
        foreach ($items as &$item) {
            $kodeCp = trim((string) ($item['kode_cp'] ?? ''));
            if ($kodeCp !== '' && !isset($validSet[$kodeCp])) {
                $item['kode_cp'] = '';
            }
        }
        return $items;
    }

    /**
     * Mark items as duplicate if similar to existing TPs
     */
    private function markDuplicates(array $items, array $existingTexts): array
    {
        foreach ($items as &$item) {
            $item['is_duplicate'] = false;
            $itemText = strtolower(trim($item['tujuan'] ?? ''));
            if ($itemText === '')
                continue;

            foreach ($existingTexts as $existing) {
                if ($existing === '')
                    continue;
                // Exact match
                if ($itemText === $existing) {
                    $item['is_duplicate'] = true;
                    break;
                }
                // Fuzzy match (>85% similar)
                $similarity = 0;
                similar_text($itemText, $existing, $similarity);
                if ($similarity > 85) {
                    $item['is_duplicate'] = true;
                    break;
                }
            }
        }
        return $items;
    }

    /**
     * Batch save with duplicate protection
     */
    public function save_batch()
    {
        if (!$this->initUser())
            return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $json = $this->safeGetJSON() ?? new \stdClass();
        $data = $json->data ?? [];
        $skipDuplicates = $json->skip_duplicates ?? true;
        $replace = !empty($json->replace); // Mode semester: timpa TP lama pada semester aktif

        if (empty($data)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data untuk disimpan']);
        }

        $firstItem = $data[0] ?? null;
        $mapelId = $firstItem->id_mapel ?? null;
        $targetSmt = (isset($firstItem->id_smt) && (int)$firstItem->id_smt > 0) ? (int)$firstItem->id_smt : $this->smt->id_smt;
        $targetTingkat = isset($firstItem->tingkat) ? (int)$firstItem->tingkat : 0;
        $targetFase = isset($firstItem->fase) ? trim((string)$firstItem->fase) : '';

        // Get existing TPs for dupe check (topic mode) atau replacement (semester mode)
        $existingTps = [];
        $replaceIds = [];
        $protectedCount = 0;

        if ($replace) {
            // Mode Semester: Hapus TP lama milik guru untuk mapel, semester, DAN tingkat/fase ini saja
            $builder = $this->tpModel->where('id_guru', $this->guru->id_guru)
                ->where('id_smt', $targetSmt);
            if (!empty($mapelId)) {
                $builder->where('id_mapel', $mapelId);
            }
            if ($targetTingkat > 0) {
                $builder->where('tingkat', $targetTingkat);
            } elseif (!empty($targetFase)) {
                $builder->where('fase', $targetFase);
            }
            $existing = $builder->findAll();
            
            foreach ($existing as $t) {
                $idT = (int)$t->id_tujuan;
                if ($this->tpModel->isUsed($idT)) {
                    $protectedCount++;
                } else {
                    $replaceIds[] = $idT;
                }
            }
        } elseif ($skipDuplicates) {
            $existing = $this->tpModel->getTujuanByGuru(
                $this->guru->id_guru,
                $targetSmt,
                $mapelId,
                $targetTingkat > 0 ? $targetTingkat : null
            );
            $existingTps = array_map(fn($t) => strtolower(trim($t->tujuan ?? '')), $existing);
        }

        $insertData = [];
        $skippedCount = 0;
        $mapelGuru = $this->getMapelGuru();

        foreach ($data as $item) {
            $tujuanText = trim($item->tujuan ?? '');
            if (empty($tujuanText))
                continue;

            if (empty($item->id_mapel) || !isset($mapelGuru[(int)$item->id_mapel])) {
                $skippedCount++;
                continue;
            }

            // Duplicate check
            if ($skipDuplicates && !$replace) {
                $isDupe = false;
                $lowerText = strtolower($tujuanText);
                foreach ($existingTps as $et) {
                    if ($et === $lowerText) {
                        $isDupe = true;
                        break;
                    }
                    $sim = 0;
                    similar_text($lowerText, $et, $sim);
                    if ($sim > 85) {
                        $isDupe = true;
                        break;
                    }
                }
                if ($isDupe) {
                    $skippedCount++;
                    continue;
                }
            } else {
                // Dalam mode replace (semester), hanya cegah duplikat internal dalam batch yang sama
                $lowerText = strtolower($tujuanText);
                if (in_array($lowerText, $existingTps, true)) {
                    $skippedCount++;
                    continue;
                }
            }

            $kodeCp = !empty($item->kode_cp) ? (string)$item->kode_cp : '';
            if ($kodeCp !== '' && !preg_match('/^[A-Za-z0-9\-_\.]+$/', $kodeCp)) {
                $kodeCp = '';
            }

            $insertData[] = [
                'id_guru' => $this->guru->id_guru,
                'id_mapel' => $item->id_mapel,
                'id_tp' => $this->tp->id_tp,
                'id_smt' => (isset($item->id_smt) && (int)$item->id_smt > 0) ? (int)$item->id_smt : $this->smt->id_smt,
                'tingkat' => $item->tingkat ?? 0,
                'fase' => $item->fase ?? '',
                'kode' => $item->kode ?? '',
                // Field baru Kurikulum Merdeka 2024
                'kode_cp' => $kodeCp !== '' ? $kodeCp : null,
                'elemen_cp' => !empty($item->elemen_cp) ? (string)$item->elemen_cp : null,
                'tujuan' => $tujuanText,
                'tp_sikap' => !empty($item->tp_sikap) ? trim((string)$item->tp_sikap) : null,
                'tp_keterampilan' => !empty($item->tp_keterampilan) ? trim((string)$item->tp_keterampilan) : null,
                'level_kognitif' => $this->normalizeLevelKognitif($item->level_kognitif ?? ''),
                'materi_pokok' => !empty($item->materi_pokok) ? trim((string)$item->materi_pokok) : null,
                'kriteria_ketercapaian' => !empty($item->kriteria_ketercapaian) ? trim((string)$item->kriteria_ketercapaian) : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Add to existing texts to prevent intra-batch duplicates
            $existingTps[] = strtolower($tujuanText);
        }

        if (!empty($insertData)) {
            try {
                $replacedCount = 0;
                $db = $this->tpModel->db;
                $db->transStart();

                if (!empty($replaceIds)) {
                    $this->tpModel->whereIn('id_tujuan', $replaceIds)
                        ->where('id_guru', $this->guru->id_guru)
                        ->delete();
                    $replacedCount = $db->affectedRows();
                }

                $this->tpModel->insertBatch($insertData);
                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \RuntimeException('Transaksi gagal saat menimpa TP lama');
                }

                $msg = count($insertData) . ' TP berhasil disimpan.';
                if ($replacedCount > 0) {
                    $msg = $replacedCount . ' TP lama pada tingkat kelas ini dihapus & diganti. ' . $msg;
                }
                if ($protectedCount > 0) {
                    $msg .= ' (' . $protectedCount . ' TP lama yang digunakan di Kartu/Soal tetap dipertahankan)';
                }
                if ($skippedCount > 0) {
                    $msg .= ' (' . $skippedCount . ' duplikat dilewati)';
                }
                return $this->response->setJSON([
                    'status' => true,
                    'message' => $msg,
                    'saved' => count($insertData),
                    'skipped' => $skippedCount,
                    'replaced' => $replacedCount,
                    'protected' => $protectedCount,
                ]);
            }
            catch (\Throwable $e) {
                log_message('error', '[Tujuan::save_batch] ' . $e->getMessage());
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
            }
        }

        if ($skippedCount > 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Semua TP sudah ada (duplikat). Tidak ada yang disimpan.']);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data valid untuk disimpan']);
    }

    /**
     * Toggle status aktif/nonaktif TP
     */
    public function toggleStatus()
    {
        if (!$this->initUser())
            return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        // Verify ownership
        $tp = $this->tpModel->where('id_tujuan', $id)
            ->where('id_guru', $this->guru->id_guru)
            ->first();

        if (!$tp) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan atau bukan milik Anda']);
        }

        $newStatus = $status === 'aktif' ? 1 : 0;
        
        if ($newStatus === 0 && $this->tpModel->isUsed($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'TP tidak dapat dinonaktifkan karena sudah digunakan di Kartu Soal/Bank Soal']);
        }

        $result = $this->tpModel->update($id, ['is_active' => $newStatus]);

        if ($result) {
            $msg = $newStatus ? 'TP diaktifkan' : 'TP dinonaktifkan';
            return $this->response->setJSON(['status' => true, 'message' => $msg]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal mengubah status']);
    }

    /**
     * Bulk toggle status
     */
    public function bulkToggleStatus()
    {
        if (!$this->initUser())
            return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $ids = $this->request->getPost('ids');
        $status = $this->request->getPost('status');

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data dipilih']);
        }

        $newStatus = $status === 'aktif' ? 1 : 0;
        
        $canToggle = [];
        $usedCount = 0;
        if ($newStatus === 0) {
            foreach ($ids as $idItem) {
                if (!$this->tpModel->isUsed($idItem)) {
                    $canToggle[] = $idItem;
                } else {
                    $usedCount++;
                }
            }
        } else {
            $canToggle = $ids;
        }

        if (empty($canToggle)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Semua TP yang dipilih sedang digunakan dan tidak dapat dinonaktifkan']);
        }

        // Only update owned records
        $result = $this->tpModel->whereIn('id_tujuan', $canToggle)
            ->where('id_guru', $this->guru->id_guru)
            ->set(['is_active' => $newStatus])
            ->update();

        $affected = $this->tpModel->db->affectedRows();
        
        if ($result) {
            $msg = $newStatus ? "$affected TP diaktifkan" : "$affected TP dinonaktifkan";
            if ($usedCount > 0) {
                $msg .= ", {$usedCount} TP lainnya gagal dinonaktifkan karena sudah digunakan";
            }
            return $this->response->setJSON(['status' => true, 'message' => $msg, 'affected' => $affected]);
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal mengubah status']);
    }

    /**
     * Export TP to PDF
     */
    public function export()
    {
        if (!$this->initUser())
            return redirect()->to('login');

        if (!$this->guru) {
            return redirect()->back()->with('error', 'Silakan pilih Guru terlebih dahulu.');
        }

        $mapel_id = $this->request->getGet('mapel');
        $tingkat = $this->request->getGet('tingkat');
        $list = $this->tpModel->getTujuanByGuru(
            $this->guru->id_guru,
            $this->tp->id_tp,
            $this->smt->id_smt,
            $mapel_id,
            $tingkat
        );

        $sekolah = $this->dashboardModel->getSetting();
        $mapel_nama = $mapel_id ? ($list[0]->nama_mapel ?? 'Mata Pelajaran') : 'Semua Mata Pelajaran';

        $html = view('guru/tujuan/export_pdf', [
            'list' => $list,
            'sekolah' => $sekolah,
            'mapel_nama' => $mapel_nama,
            'tp' => $this->tp->tahun ?? '-',
            'smt' => $this->smt->nama_smt ?? '-',
            'guru' => $this->guru
        ]);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream('Tujuan_Pembelajaran_' . date('Y-m-d') . '.pdf', ['Attachment' => 0]);
        exit();
    }

    // --- Private helpers ---

    private function getFaseLabel(?string $fase): string
    {
        $labels = [
            'A' => 'Fase A (Kelas 1-2 SD)',
            'B' => 'Fase B (Kelas 3-4 SD)',
            'C' => 'Fase C (Kelas 5-6 SD)',
            'D' => 'Fase D (Kelas 7-9 SMP)',
            'E' => 'Fase E (Kelas 10 SMA/SMK)',
            'F' => 'Fase F (Kelas 11-12 SMA/SMK)',
        ];
        return $labels[$fase] ?? ($fase ? "Fase {$fase}" : 'Umum');
    }

    private function getApiKey()
    {
        $setting = $this->dashboardModel->getSetting();
        $apiKey = $setting->api_key ?? '';

        if ($this->guru && !empty($this->guru->api_key)) {
            $apiKey = $this->guru->api_key;
        }

        return $apiKey;
    }

    private function sendToAI($apiKey, $prompt, $maxTokens = 4096)
    {
        // Try system AIService first if available
        try {
            $aiService = new \App\Services\AIService();
            $res = $aiService->generateContent($apiKey, $prompt);
            if ($res && isset($res['success']) && $res['success'] && !empty($res['data'])) {
                return $res['data'];
            }
            if (isset($res['message']) && str_contains($res['message'], '429')) {
                return json_encode(['error' => 'Batas Kuota AI Terlampaui (429). Silakan tunggu beberapa saat lagi.']);
            }
        } catch (\Throwable $e) {
            log_message('warning', '[Tujuan::sendToAI] AIService exception: ' . $e->getMessage());
        }

        // Direct API Fallback — seluruh lini 1.x/2.x sudah disunset; pakai 3.5-flash-lite
        $models = ['gemini-3.5-flash-lite', 'gemini-3.6-flash'];

        $data = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.6,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => $maxTokens,
                'responseMimeType' => 'application/json',
            ]
        ];

        $lastError = '';

        foreach ($models as $model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 90);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($error) {
                    $lastError = "Curl Error ({$model}): {$error}";
                    break;
                }

                if ($httpCode === 200) {
                    return $response;
                }

                $respData = json_decode((string)$response, true);
                $errMsg = $respData['error']['message'] ?? 'Unknown error';

                if ($httpCode === 429) {
                    $lastError = "Quota exceeded ({$model})";
                    if ($attempt === 1) {
                        sleep(2);
                        continue;
                    }
                    break;
                }

                $lastError = "HTTP {$httpCode} ({$model}): {$errMsg}";
                break;
            }
        }

        return json_encode(['error' => $lastError ?: 'Semua model AI gagal merespon.']);
    }

    private function parseAIResponse($response)
    {
        if (empty($response)) {
            return 'Respons dari server AI kosong.';
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['error'])) {
            $err = is_array($decoded['error']) ? ($decoded['error']['message'] ?? json_encode($decoded['error'])) : $decoded['error'];
            return 'Error: ' . $err;
        }

        if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            if (isset($decoded['candidates'][0]['finishReason']) && $decoded['candidates'][0]['finishReason'] !== 'STOP') {
                return 'Generasi AI terhenti: ' . $decoded['candidates'][0]['finishReason'];
            }
            return 'Format respons AI tidak dikenali.';
        }

        $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // EXTRACTION: find first { or [ to avoid leading non-json text
        $startPosArr = strpos($text, '[');
        $startPosObj = strpos($text, '{');
        $startPos = false;
        if ($startPosArr !== false && $startPosObj !== false) {
            $startPos = min($startPosArr, $startPosObj);
        } elseif ($startPosArr !== false) {
            $startPos = $startPosArr;
        } elseif ($startPosObj !== false) {
            $startPos = $startPosObj;
        }

        $endPosArr = strrpos($text, ']');
        $endPosObj = strrpos($text, '}');
        $endPos = false;
        if ($endPosArr !== false && $endPosObj !== false) {
            $endPos = max($endPosArr, $endPosObj);
        } elseif ($endPosArr !== false) {
            $endPos = $endPosArr;
        } elseif ($endPosObj !== false) {
            $endPos = $endPosObj;
        }

        if ($startPos !== false && $endPos !== false && $endPos > $startPos) {
            $text = substr($text, $startPos, $endPos - $startPos + 1);
        }

        // Clean control characters that break json_decode
        $text = preg_replace('/[\x00-\x1F\x7F]/', ' ', $text);

        $parsed = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback attempt: fix trailing commas or unquoted keys
            $fixedText = preg_replace('/,\s*([\]}])/', '$1', $text);
            $parsed = json_decode($fixedText, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return 'Gagal memproses JSON dari AI: ' . json_last_error_msg();
            }
        }

        return $parsed;
    }

    /**
     * Daftar kode CP valid untuk mapel+fase dari e_capaian_pembelajaran.
     * Dipakai menyuntikkan kode CP nyata ke prompt AI agar tidak mengarang kode orphan.
     */
    private function getValidCpKodeList(string $namaMapel, string $fase): array
    {
        try {
            if (!$this->master->db->tableExists('e_capaian_pembelajaran')) {
                return [];
            }
            $cpModel = new \App\Models\CapaianPembelajaranModel();
            $builder = $cpModel->where('is_active', 1);
            if ($namaMapel !== '') {
                $builder->like('mata_pelajaran', $namaMapel);
            }
            if ($fase !== '') {
                $builder->where('fase', strtoupper($fase));
            }
            $rows = $builder->select('kode_cp')->orderBy('elemen', 'ASC')->findAll();
            $kodes = [];
            foreach ($rows as $r) {
                $k = trim((string) ($r->kode_cp ?? ''));
                if ($k !== '') {
                    $kodes[] = $k;
                }
            }
            return array_values(array_unique($kodes));
        } catch (\Throwable $e) {
            log_message('warning', '[Tujuan::getValidCpKodeList] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CP dropdown by mapel & fase from e_capaian_pembelajaran
     */
    public function getCpByMapel()
    {
        if (!$this->initUser()) return $this->response->setJSON(['status' => false, 'data' => []]);

        $idMapel = $this->request->getGet('id_mapel');
        $fase = $this->request->getGet('fase');
        $mapelList = $this->getMapelGuru();
        $namaMapel = $mapelList[$idMapel] ?? '';

        $cpModel = new \App\Models\CapaianPembelajaranModel();
        if (!$this->master->db->tableExists('e_capaian_pembelajaran')) {
            return $this->response->setJSON(['status' => true, 'data' => []]);
        }

        $builder = $cpModel->where('is_active', 1);
        if (!empty($namaMapel)) {
            $builder->like('mata_pelajaran', $namaMapel);
        }
        if (!empty($fase)) {
            $builder->where('fase', strtoupper($fase));
        }

        $rows = $builder->orderBy('elemen', 'ASC')->findAll();
        return $this->response->setJSON(['status' => true, 'data' => $rows]);
    }

    /**
     * Auto-suggest next Kode TP (e.g. 7.3 if 7.1, 7.2 exist)
     */
    public function getNextKode()
    {
        if (!$this->initUser()) return $this->response->setJSON(['status' => false, 'kode' => '']);

        $idMapel = $this->request->getGet('id_mapel');
        $tingkat = (int)($this->request->getGet('tingkat') ?? 0);
        $idGuru = $this->guru ? $this->guru->id_guru : null;
        $idSmt = $this->smt->id_smt ?? 1;

        if (!$idGuru || !$idMapel) {
            return $this->response->setJSON(['status' => false, 'kode' => '']);
        }

        $prefix = $tingkat > 0 ? (string)$tingkat : 'TP';
        $tps = $this->tpModel->where('id_guru', $idGuru)
            ->where('id_mapel', $idMapel)
            ->where('id_tp', $this->tp->id_tp)
            ->where('id_smt', $idSmt)
            ->findAll();

        $maxNum = 0;
        foreach ($tps as $t) {
            $k = trim($t->kode ?? '');
            if (preg_match('/(\d+)$/', $k, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) $maxNum = $num;
            }
        }

        $nextKode = $prefix . '.' . ($maxNum + 1);
        return $this->response->setJSON(['status' => true, 'kode' => $nextKode]);
    }

    /**
     * Duplicate 1 TP item (1-click duplicate)
     */
    public function duplicate()
    {
        if (!$this->initUser()) return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $id = $this->request->getPost('id');
        if (!$id) return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);

        $tp = $this->tpModel->where('id_tujuan', $id)
            ->where('id_guru', $this->guru->id_guru)
            ->first();

        if (!$tp) return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan atau bukan milik Anda']);

        $newKode = $tp->kode ? $tp->kode . '-COPY' : 'TP-COPY';
        $data = [
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $tp->id_mapel,
            'id_tp' => $this->tp->id_tp,
            'id_smt' => $tp->id_smt,
            'tingkat' => $tp->tingkat,
            'fase' => $tp->fase,
            'kode' => $newKode,
            'kode_cp' => $tp->kode_cp,
            'elemen_cp' => $tp->elemen_cp,
            'tujuan' => $tp->tujuan . ' (Salinan)',
            'tp_sikap' => $tp->tp_sikap,
            'tp_keterampilan' => $tp->tp_keterampilan,
            'level_kognitif' => $tp->level_kognitif,
            'materi_pokok' => $tp->materi_pokok,
            'kriteria_ketercapaian' => $tp->kriteria_ketercapaian,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $insId = $this->tpModel->insert($data);
        if ($insId) {
            return $this->response->setJSON(['status' => true, 'message' => 'Berhasil menduplikasi TP', 'id' => $insId]);
        }
        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menduplikasi TP']);
    }

    /**
     * Copy all TPs from another semester/mapel to active semester
     */
    public function copyFrom()
    {
        if (!$this->initUser()) return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $idMapel = $this->request->getPost('id_mapel');
        $idSmtSource = $this->request->getPost('id_smt_source');
        $targetSmt = $this->smt->id_smt;

        if (!$idMapel || !$idSmtSource) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mapel dan Semester asal wajib dipilih']);
        }

        $sources = $this->tpModel->where('id_guru', $this->guru->id_guru)
            ->where('id_mapel', $idMapel)
            ->where('id_smt', $idSmtSource)
            ->findAll();

        if (empty($sources)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada TP ditemukan pada semester asal.']);
        }

        // Existing destination TPs to avoid duplicates
        $existing = $this->tpModel->where('id_guru', $this->guru->id_guru)
            ->where('id_mapel', $idMapel)
            ->where('id_smt', $targetSmt)
            ->findAll();
        $existingTexts = array_map(fn($t) => strtolower(trim($t->tujuan ?? '')), $existing);

        $inserted = 0;
        $skipped = 0;
        foreach ($sources as $s) {
            $tTxt = strtolower(trim($s->tujuan ?? ''));
            if (in_array($tTxt, $existingTexts, true)) {
                $skipped++;
                continue;
            }

            $data = [
                'id_guru' => $this->guru->id_guru,
                'id_mapel' => $s->id_mapel,
                'id_tp' => $this->tp->id_tp,
                'id_smt' => $targetSmt,
                'tingkat' => $s->tingkat,
                'fase' => $s->fase,
                'kode' => $s->kode,
                'kode_cp' => $s->kode_cp,
                'elemen_cp' => $s->elemen_cp,
                'tujuan' => $s->tujuan,
                'tp_sikap' => $s->tp_sikap,
                'tp_keterampilan' => $s->tp_keterampilan,
                'level_kognitif' => $s->level_kognitif,
                'materi_pokok' => $s->materi_pokok,
                'kriteria_ketercapaian' => $s->kriteria_ketercapaian,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->tpModel->insert($data);
            $existingTexts[] = $tTxt;
            $inserted++;
        }

        $msg = "{$inserted} TP berhasil disalin ke Semester {$targetSmt}.";
        if ($skipped > 0) $msg .= " ({$skipped} duplikat dilewati)";
        return $this->response->setJSON(['status' => true, 'message' => $msg, 'copied' => $inserted, 'skipped' => $skipped]);
    }

    /**
     * Copy all TPs from another guru on the same mapel to active semester
     */
    public function copyFromGuru()
    {
        if (!$this->initUser()) return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $idGuruSource = $this->request->getPost('id_guru_source');
        $idMapel = $this->request->getPost('id_mapel');
        $idSmtSource = $this->request->getPost('id_smt_source') ?: $this->smt->id_smt;
        $targetSmt = $this->smt->id_smt;

        if (!$idGuruSource || !$idMapel) {
            return $this->response->setJSON(['status' => false, 'message' => 'Guru asal dan Mapel wajib dipilih']);
        }

        // Mencegah menyalin dari diri sendiri
        if ((int) $idGuruSource === (int) $this->guru->id_guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak bisa menyalin dari guru yang sama']);
        }

        $sources = $this->tpModel->where('id_guru', $idGuruSource)
            ->where('id_mapel', $idMapel)
            ->where('id_smt', $idSmtSource)
            ->findAll();

        if (empty($sources)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada TP ditemukan pada guru asal untuk mapel ini.']);
        }

        // Existing destination TPs to avoid duplicates
        $existing = $this->tpModel->where('id_guru', $this->guru->id_guru)
            ->where('id_mapel', $idMapel)
            ->where('id_smt', $targetSmt)
            ->findAll();
        $existingTexts = array_map(fn($t) => strtolower(trim($t->tujuan ?? '')), $existing);

        $inserted = 0;
        $skipped = 0;
        foreach ($sources as $s) {
            $tTxt = strtolower(trim($s->tujuan ?? ''));
            if (in_array($tTxt, $existingTexts, true)) {
                $skipped++;
                continue;
            }

            $data = [
                'id_guru' => $this->guru->id_guru,
                'id_mapel' => $s->id_mapel,
                'id_tp' => $this->tp->id_tp,
                'id_smt' => $targetSmt,
                'tingkat' => $s->tingkat,
                'fase' => $s->fase,
                'kode' => $s->kode,
                'kode_cp' => $s->kode_cp,
                'elemen_cp' => $s->elemen_cp,
                'tujuan' => $s->tujuan,
                'tp_sikap' => $s->tp_sikap,
                'tp_keterampilan' => $s->tp_keterampilan,
                'level_kognitif' => $s->level_kognitif,
                'materi_pokok' => $s->materi_pokok,
                'kriteria_ketercapaian' => $s->kriteria_ketercapaian,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->tpModel->insert($data);
            $existingTexts[] = $tTxt;
            $inserted++;
        }

        $msg = "{$inserted} TP berhasil disalin dari guru lain ke Semester {$targetSmt}.";
        if ($skipped > 0) $msg .= " ({$skipped} duplikat dilewati)";
        return $this->response->setJSON(['status' => true, 'message' => $msg, 'copied' => $inserted, 'skipped' => $skipped]);
    }

    /**
     * AI Auto-generate KKTP for single TP
     */
    public function generateKktpAi()
    {
        if (!$this->initUser()) return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        $tujuan = trim((string)$this->request->getPost('tujuan'));
        $level = trim((string)$this->request->getPost('level_kognitif'));
        $materi = trim((string)$this->request->getPost('materi_pokok'));

        if (empty($tujuan)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kalimat Tujuan Pembelajaran wajib diisi']);
        }

        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            return $this->response->setJSON(['status' => false, 'message' => 'API Key Gemini belum diatur di Profil Anda']);
        }

        $prompt = <<<PROMPT
Anda adalah Pakar Evaluasi Kurikulum Merdeka Indonesia.
Buatkan Kriteria Ketercapaian Tujuan Pembelajaran (KKTP) yang ringkas, terukur, dan operasional (menggantikan KKM) untuk Tujuan Pembelajaran berikut:

- TP: "{$tujuan}"
- Level Kognitif: {$level}
- Materi: {$materi}

Output WAJIB berupa JSON object MURNI dengan 1 field utama "kktp":
{
  "kktp": "Peserta didik dianggap mencapai TP ini jika:\\n1. ...\\n2. ...\\n3. ..."
}
PROMPT;

        $rawResponse = $this->sendToAI($apiKey, $prompt, 1024);
        $parsed = $this->parseAIResponse($rawResponse);

        if (is_array($parsed)) {
            $kktpText = '';
            if (isset($parsed['kktp'])) {
                $kktpText = is_array($parsed['kktp']) ? implode("\n", $parsed['kktp']) : (string)$parsed['kktp'];
            } elseif (isset($parsed[0])) {
                $kktpText = is_array($parsed[0]) ? implode("\n", $parsed[0]) : (string)$parsed[0];
            } else {
                $kktpText = json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            if (!empty($kktpText)) {
                return $this->response->setJSON(['status' => true, 'kktp' => trim($kktpText)]);
            }
        }

        $errMsg = is_string($parsed) ? $parsed : 'Gagal memproses KKTP dari AI.';
        return $this->response->setJSON(['status' => false, 'message' => $errMsg]);
    }

    /**
     * Download Excel Import Template (disederhanakan: fokus rumusan TP terukur).
     * Format lama 11 kolom tetap didukung saat import (backward compatible).
     */
    public function downloadTemplate()
    {
        if (!$this->initUser()) return redirect()->to('login');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template TP');

        $headers = [
            'A1' => 'Kode TP',
            'B1' => 'Rumusan TP (diawali KKO + materi jelas)*',
            'C1' => 'Tingkat Kelas (10-12)',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Sample row
        $sheet->setCellValue('A2', '10.1');
        $sheet->setCellValue('B2', 'Menganalisis struktur teks eksplanasi dan menentukan hubungan kausalitas antar paragraf melalui diskusi kelompok');
        $sheet->setCellValue('C2', '10');
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(90);
        $sheet->getColumnDimension('C')->setWidth(20);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Template_Import_Tujuan_Pembelajaran.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Import Excel TP
     */
    public function importExcel()
    {
        if (!$this->initUser()) return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);

        if (!$this->guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Silakan pilih Guru terlebih dahulu']);
        }

        $idMapel = $this->request->getPost('id_mapel');
        if (empty($idMapel)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Pilih Mata Pelajaran terlebih dahulu']);
        }

        $mapelGuru = $this->getMapelGuru();
        if (!isset($mapelGuru[(int)$idMapel])) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mata Pelajaran tidak valid atau bukan milik Anda']);
        }

        $file = $this->request->getFile('file_excel');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => false, 'message' => 'File Excel tidak valid']);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) <= 1) {
                return $this->response->setJSON(['status' => false, 'message' => 'File Excel kosong']);
            }

            $insertData = [];
            $skipped = 0;

            // Deteksi format: sederhana (Kode | Rumusan TP | Tingkat) vs lengkap 11 kolom (legacy)
            $headerB = mb_strtolower(trim((string)($rows[0][1] ?? '')));
            $isSimple = str_contains($headerB, 'rumusan tp');

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if ($isSimple) {
                    $kode = trim((string)($row[0] ?? ''));
                    $tujuan = trim((string)($row[1] ?? ''));
                    $tingkat = (int)($row[2] ?? 0);
                    $level = $this->detectLevelKognitif($tujuan);
                    $fase = $this->faseFromTingkat($tingkat);
                    $materi = '';
                    $kodeCp = '';
                    $elemenCp = '';
                    $tpSikap = '';
                    $tpKet = '';
                    $kktp = '';
                } else {
                    $kode = trim((string)($row[0] ?? ''));
                    $tujuan = trim((string)($row[1] ?? ''));
                    $level = strtoupper(trim((string)($row[2] ?? 'C4')));
                    $tingkat = (int)($row[3] ?? 0);
                    $fase = strtoupper(trim((string)($row[4] ?? '')));
                    $materi = trim((string)($row[5] ?? ''));
                    $kodeCp = trim((string)($row[6] ?? ''));
                    $elemenCp = trim((string)($row[7] ?? ''));
                    $tpSikap = trim((string)($row[8] ?? ''));
                    $tpKet = trim((string)($row[9] ?? ''));
                    $kktp = trim((string)($row[10] ?? ''));
                }

                if (empty($tujuan)) {
                    $skipped++;
                    continue;
                }

                if (!in_array($level, ['C1','C2','C3','C4','C5','C6'], true)) {
                    $level = $this->detectLevelKognitif($tujuan);
                }
                if ($fase === '') {
                    $fase = $this->faseFromTingkat($tingkat);
                }

                $insertData[] = [
                    'id_guru' => $this->guru->id_guru,
                    'id_mapel' => $idMapel,
                    'id_tp' => $this->tp->id_tp,
                    'id_smt' => $this->smt->id_smt,
                    'tingkat' => $tingkat,
                    'fase' => $fase,
                    'kode' => $kode,
                    'kode_cp' => $kodeCp ?: null,
                    'elemen_cp' => $elemenCp ?: null,
                    'tujuan' => $tujuan,
                    'tp_sikap' => $tpSikap ?: null,
                    'tp_keterampilan' => $tpKet ?: null,
                    'level_kognitif' => $level,
                    'materi_pokok' => $materi ?: null,
                    'kriteria_ketercapaian' => $kktp ?: null,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }

            if (!empty($insertData)) {
                $this->tpModel->insertBatch($insertData);
                $msg = count($insertData) . ' data TP berhasil di-import.';
                if ($skipped > 0) $msg .= " ({$skipped} baris kosong diabaikan)";
                return $this->response->setJSON(['status' => true, 'message' => $msg]);
            }
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada data valid untuk di-import']);

        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal membaca Excel: ' . $e->getMessage()]);
        }
    }

    /**
     * Export TPs to Excel (.xlsx)
     */
    public function exportExcel()
    {
        if (!$this->initUser()) return redirect()->to('login');

        $mapel_selected = $this->request->getGet('mapel');
        $tingkat_selected = $this->request->getGet('tingkat');
        $semester_selected = $this->request->getGet('semester') ?: $this->smt->id_smt;
        $status_selected = $this->request->getGet('status') ?: '';
        $id_guru = $this->guru ? $this->guru->id_guru : $this->request->getGet('id_guru');

        $tujuanList = $id_guru ? $this->tpModel->getTujuanByGuru(
            $id_guru,
            $this->tp->id_tp,
            $semester_selected,
            $mapel_selected,
            $tingkat_selected,
            $status_selected
        ) : [];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar TP');

        $headers = ['No', 'Mata Pelajaran', 'Kelas', 'Kode TP', 'Rumusan TP', 'Status'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        $rowNum = 2;
        foreach ($tujuanList as $idx => $t) {
            $sheet->setCellValue('A' . $rowNum, $idx + 1);
            $sheet->setCellValue('B' . $rowNum, $t->nama_mapel ?? '');
            $sheet->setCellValue('C' . $rowNum, $t->tingkat ?: '-');
            $sheet->setCellValue('D' . $rowNum, $t->kode ?? '');
            $sheet->setCellValue('E' . $rowNum, $t->tujuan ?? '');
            $sheet->setCellValue('F' . $rowNum, ($t->is_active ?? 1) ? 'Aktif' : 'Nonaktif');
            $rowNum++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Export_Tujuan_Pembelajaran_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    protected function getTpSmt()
    {
        return (object)[
            'tp' => $this->dashboardModel->getTahunActive(),
            'smt' => $this->dashboardModel->getSemesterActive()
        ];
    }
}
