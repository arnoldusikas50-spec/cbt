<?php
/**
 * CBT Praktik - Report Preview Template
 * HTML preview for practical exam reports
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Praktik - <?= esc($praktik->judul) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #3498db;
        }
        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .header h2 {
            font-size: 18px;
            color: #34495e;
            font-weight: normal;
        }
        .school-info {
            margin-bottom: 20px;
            color: #666;
        }
        .exam-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .exam-info table {
            width: 100%;
        }
        .exam-info td {
            padding: 5px 10px;
            color: #555;
        }
        .exam-info td:first-child {
            font-weight: 600;
            width: 150px;
            color: #2c3e50;
        }
        .statistics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }
        table.data-table th {
            background: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        table.data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ecf0f1;
        }
        table.data-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .score {
            font-weight: bold;
        }
        .score-high {
            color: #28a745;
        }
        .score-medium {
            color: #ffc107;
        }
        .score-low {
            color: #dc3545;
        }
        .recommendations {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
        }
        .recommendations h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .recommendations ul {
            margin-left: 20px;
        }
        .recommendations li {
            margin-bottom: 8px;
            color: #555;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            text-align: center;
            color: #95a5a6;
            font-size: 12px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .print-btn:hover {
            background: #2980b9;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 20px;
            }
            .print-btn {
                display: none;
            }
        }
        .rubrik-breakdown {
            margin-top: 10px;
            font-size: 11px;
            color: #666;
        }
        .rubrik-breakdown table {
            width: 100%;
            border-collapse: collapse;
        }
        .rubrik-breakdown th, .rubrik-breakdown td {
            padding: 5px 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .rubrik-breakdown th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .rincian-nilai {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak / Simpan PDF
    </button>

    <div class="container">
        <div class="header">
            <?php if (!empty($setting->logo)): ?>
            <div style="margin-bottom: 15px;">
                <img src="<?= base_url('uploads/logo/' . $setting->logo) ?>" alt="Logo" style="max-height: 80px;">
            </div>
            <?php endif; ?>
            <h1><?= esc($setting->nama_sekolah ?? 'Lembaga Pendidikan') ?></h1>
            <h2>LAPORAN HASIL UJIAN PRAKTIK</h2>
        </div>

        <div class="exam-info">
            <table>
                <tr>
                    <td>Judul Ujian</td>
                    <td>: <?= esc($praktik->judul) ?></td>
                </tr>
                <tr>
                    <td>Mata Pelajaran</td>
                    <td>: <?= esc($praktik->nama_mapel ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>: <?= esc($praktik->kelas_info ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: <?= format_tanggal_indonesia($praktik->tanggal_mulai ?? date('Y-m-d')) ?></td>
                </tr>
                <tr>
                    <td>KKM</td>
                    <td>: <?= $praktik->kkm ?></td>
                </tr>
                <tr>
                    <td>Mode</td>
                    <td>: <?= strtoupper($praktik->kktp_mode ?? 'KKM') ?></td>
                </tr>
                <tr>
                    <td>KKTP</td>
                    <td>: M (86-100), C (66-85), L (41-65), BB (0-40)</td>
                </tr>
            </table>
        </div>

        <?php if (!empty($statistics)): ?>
        <div class="statistics">
            <div class="stat-card info">
                <div class="stat-value"><?= $statistics['total'] ?? count($hasil) ?></div>
                <div class="stat-label">Total Peserta</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value"><?= $statistics['sudah_submit'] ?? count(array_filter($hasil, fn($h) => $h->status_pengerjaan === 'selesai')) ?></div>
                <div class="stat-label">Sudah Submit</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?= $statistics['belum_dinilai'] ?? count(array_filter($hasil, fn($h) => $h->status_penilaian === 'belum_dinilai')) ?></div>
                <div class="stat-label">Belum Dinilai</div>
            </div>
            <div class="stat-card <?= ($statistics['rata_rata'] ?? 0) >= $praktik->kkm ? 'success' : 'warning' ?>">
                <div class="stat-value"><?= round($statistics['rata_rata'] ?? 0, 2) ?></div>
                <div class="stat-label">Rata-rata Nilai</div>
            </div>
        </div>
        <?php endif; ?>

        <h3 class="section-title">Detail Hasil Siswa</h3>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th>Nilai Akhir</th>
                    <th>Predikat</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($hasil as $h):
                    $nilai = $h->nilai_akhir ?? 0;
                    $tercapai = $nilai >= 66;
                    $predikat = $nilai >= 86 ? 'M' : ($nilai >= 66 ? 'C' : ($nilai >= 41 ? 'L' : ($nilai > 0 ? 'BB' : '-')));
                    $statusClass = $tercapai ? 'badge-success' : 'badge-danger';
                    $statusText = $tercapai ? 'Tuntas' : 'Remedial';
                    $scoreClass = $nilai >= 86 ? 'score-high' : ($nilai >= 66 ? 'score-medium' : 'score-low');
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($h->nis ?? '-') ?></td>
                    <td><?= esc($h->nama_siswa ?? $h->nama ?? '-') ?></td>
                    <td><?= esc($h->nama_kelas ?? '-') ?></td>
                    <td>
                        <?php if ($h->status_penilaian === 'sudah_dinilai'): ?>
                            <span class="badge badge-success">Sudah Dinilai</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Belum Dinilai</span>
                        <?php endif; ?>
                    </td>
                    <td class="score <?= $scoreClass ?>"><?= $nilai ?: '-' ?></td>
                    <td class="text-center fw-bold"><?= $predikat ?></td>
                    <td>
                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                    </td>
                </tr>
                <?php if (!empty($h->rincian_nilai_rubrik)): ?>
                <tr>
                    <td colspan="7" class="rubrik-breakdown">
                        <?php
                        $rincian = json_decode((string)$h->rincian_nilai_rubrik, true) ?: [];
                        if (!empty($rincian)):
                        ?>
                        <div class="rincian-nilai">
                            <strong>Rincian Nilai per Kriteria:</strong>
                            <table style="margin-top: 5px;">
                                <tr>
                                    <th>Kriteria</th>
                                    <th>Nilai</th>
                                    <th>Bobot Maks</th>
                                </tr>
                                <?php foreach ($rincian as $rid => $val): 
                                    // Find rubrik name from praktik rubriks if available
                                    $rubrikName = 'Kriteria ' . $rid;
                                    foreach ($rubrik ?? [] as $r) {
                                        if ($r->id == $rid) {
                                            $rubrikName = $r->nama_kriteria;
                                            break;
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><?= esc($rubrikName) ?></td>
                                    <td><?= $val ?></td>
                                    <td><?= $rubrik[$rid]->bobot_persen ?? 100 ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!empty($recommendations)): ?>
        <div class="recommendations">
            <h3>Rekomendasi Pembelajaran</h3>
            <ul>
                <?php foreach ($recommendations as $rec): ?>
                <li><?= $rec ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Dicetak pada: <?= date('d F Y H:i:s') ?> oleh <?= esc($user->nama ?? $user->username ?? 'Sistem') ?></p>
            <p><?= esc($setting->nama_sekolah ?? '') ?></p>
        </div>
    </div>
</body>
</html>
