<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-file-earmark-medical-fill text-primary me-2"></i>
                    <?= esc($judul ?? 'Laporan Harian') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-speedometer2 me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Laporan') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Pilih Tanggal Pelaksanaan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="bi bi-calendar-event"></i></span>
                            <input type="date" id="tanggal-laporan" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-auto ms-auto d-flex gap-2">
                        <button id="btn-generate" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> Generate Laporan
                        </button>
                        <button id="btn-print" class="btn btn-success rounded-pill px-4 shadow-sm d-none">
                            <i class="bi bi-printer me-1"></i> Cetak Laporan
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light bg-opacity-50 p-0">
                <div id="loading-state" class="p-5 text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Mengambil data dari Berita Acara...</p>
                </div>

                <div id="empty-state" class="p-5 text-center">
                    <i class="bi bi-file-earmark-text text-muted opacity-25" style="font-size: 5rem;"></i>
                    <h5 class="mt-3 fw-bold text-muted">Belum ada data ditampilkan</h5>
                    <p class="text-muted small">Pilih tanggal dan tekan tombol Generate Laporan untuk memproses data.
                    </p>
                </div>

                <div id="report-view" class="p-1 p-md-4 d-none overflow-auto">
                    <div id="print-area" class="bg-white shadow-lg mx-auto p-5"
                        style="max-width: 21cm; min-height: 29.7cm;">
                        <!-- Header / Kop -->
                        <div id="report-header" class="mb-4">
                            <div id="school-kop-image" class="mb-3 d-none">
                                <img src="" style="width: 100%;" alt="Kop Surat">
                            </div>
                            <!-- Dynamic Header from settings -->
                            <div id="school-header-text" class="text-center">
                                <h4 class="fw-bold mb-0" id="school-name">NAMA SEKOLAH</h4>
                                <p class="mb-0 small" id="school-address">Alamat Sekolah</p>
                            </div>
                            <div class="text-center">
                                <h5 class="fw-bold text-uppercase border-bottom border-dark d-inline-block pb-1">Laporan
                                    Pelaksanaan Ujian</h5>
                            </div>
                        </div>

                        <!-- Section A -->
                        <div class="report-section mb-4">
                            <h6 class="fw-bold text-uppercase mb-2">A. IDENTITAS KEGIATAN</h6>
                            <table class="table table-sm table-borderless mb-0 ms-2">
                                <tr>
                                    <td width="200">Nama Sekolah</td>
                                    <td width="20">:</td>
                                    <td id="res-sekolah" class="fw-bold">-</td>
                                </tr>
                                <tr>
                                    <td>Hari/Tanggal</td>
                                    <td>:</td>
                                    <td id="res-tanggal">-</td>
                                </tr>
                                <tr>
                                    <td>Waktu Pelaksanaan</td>
                                    <td>:</td>
                                    <td id="res-waktu">-</td>
                                </tr>
                                <tr>
                                    <td>Tempat/Ruang</td>
                                    <td>:</td>
                                    <td id="res-ruang">-</td>
                                </tr>
                                <tr>
                                    <td>Jenis Ujian</td>
                                    <td>:</td>
                                    <td id="res-jenis" class="fw-bold">Ujian Harian</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Section B -->
                        <div class="report-section mb-4">
                            <h6 class="fw-bold text-uppercase mb-2">B. DATA MATA PELAJARAN</h6>
                            <table class="table table-sm table-bordered border-dark mb-2">
                                <thead class="table-light border-dark text-center">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Kelas</th>
                                        <th width="110">Jumlah Peserta</th>
                                        <th width="90">Jumlah Sesi</th>
                                    </tr>
                                </thead>
                                <tbody id="res-mapel-list" class="border-dark">
                                    <tr>
                                        <td colspan="5" class="text-center italic">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="fw-bold small">Total Mata Pelajaran Hari Ini: <span id="res-total-mapel">0</span>
                            </p>
                        </div>

                        <!-- Section C -->
                        <div class="report-section mb-4">
                            <h6 class="fw-bold text-uppercase mb-2">C. REKAP KEHADIRAN PESERTA (PER KELAS & MAPEL)</h6>
                            <table class="table table-sm table-bordered border-dark mb-0">
                                <thead class="table-light border-dark text-center">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th width="110">Ruang / Sesi</th>
                                        <th width="70">Hadir</th>
                                        <th width="70">T. Hadir</th>
                                        <th width="80">Jumlah</th>
                                        <th width="80">Sistem</th>
                                    </tr>
                                </thead>
                                <tbody id="res-rekap-hadir" class="border-dark">
                                    <tr>
                                        <td colspan="8" class="text-center italic">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section D -->
                        <div class="report-section mb-4">
                            <h6 class="fw-bold text-uppercase mb-2">D. DAFTAR SISWA TIDAK HADIR (DETAIL)</h6>
                            <table class="table table-sm table-bordered border-dark mb-0">
                                <thead class="table-light border-dark text-center">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th width="100">Ruang</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody id="res-absent-detail" class="border-dark">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">- Tidak ada siswa yang absen -
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="small mt-2 mb-0"><span class="fw-bold">Rekap alasan:</span>
                                <span id="res-alasan-summary"></span>
                            </p>
                        </div>

                        <!-- Section E -->
                        <div class="report-section mb-4">
                            <h6 class="fw-bold text-uppercase mb-2">E. DATA PENGAWAS UJIAN</h6>
                            <table class="table table-sm table-bordered border-dark mb-0">
                                <thead class="table-light border-dark text-center">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Nama Pengawas</th>
                                        <th>Mata Pelajaran</th>
                                        <th width="110">Ruang</th>
                                    </tr>
                                </thead>
                                <tbody id="res-pengawas-list" class="border-dark">
                                    <tr>
                                        <td colspan="4" class="text-center italic">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section F -->
                        <div class="report-section mb-4">
                            <h6 class="fw-bold text-uppercase mb-2">F. PELAKSANAAN UJIAN</h6>
                            <table class="table table-sm table-borderless mb-0 ms-2">
                                <tr>
                                    <td width="200">Ujian dimulai pukul</td>
                                    <td width="20">:</td>
                                    <td id="res-start-time">-</td>
                                </tr>
                                <tr>
                                    <td>Ujian selesai pukul</td>
                                    <td>:</td>
                                    <td id="res-end-time">-</td>
                                </tr>
                                <tr>
                                    <td>Sistem</td>
                                    <td>:</td>
                                    <td>CBT (Computer Based Test)</td>
                                </tr>
                                <tr>
                                    <td>Kondisi pelaksanaan</td>
                                    <td>:</td>
                                    <td id="res-kondisi">Lancar dan tertib</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Section G -->
                        <div class="report-section mb-4">
                            <h6 class="fw-bold text-uppercase mb-2">G. KENDALA SELAMA UJIAN</h6>
                            <table class="table table-sm table-bordered border-dark mb-0">
                                <thead class="table-light border-dark text-center">
                                    <tr>
                                        <th width="40">No</th>
                                        <th width="80">Waktu</th>
                                        <th width="180">Tempat</th>
                                        <th>Kendala</th>
                                        <th>Solusi</th>
                                    </tr>
                                </thead>
                                <tbody id="res-kendala-list" class="border-dark">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada kendala berarti</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section H -->
                        <div class="report-section mb-5">
                            <h6 class="fw-bold text-uppercase mb-2">H. KESIMPULAN</h6>
                            <table class="table table-sm table-borderless mb-0 ms-2">
                                <tr>
                                    <td width="200">Tingkat kehadiran</td>
                                    <td width="20">:</td>
                                    <td><span id="res-hadir-percent">0</span> %</td>
                                </tr>
                                <tr>
                                    <td>Pelaksanaan</td>
                                    <td>:</td>
                                    <td id="res-status-lancar" class="fw-bold">Lancar</td>
                                </tr>
                                <tr>
                                    <td>Catatan penting</td>
                                    <td>:</td>
                                    <td id="res-catatan">-</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Section I / Closing -->
                        <div class="report-section mt-5 px-3">
                            <table class="w-100 table-borderless">
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <p class="mb-2 small" id="res-place-date">Kota, Tanggal</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center" style="width: 33%; vertical-align: top;">
                                        <p class="mb-5 pb-3">Pengawas 1,</p>
                                        <div class="mt-4">
                                            <p class="mb-0 signature-line" id="res-pengawas1"></p>
                                            <p class="small mb-0" id="res-nip-pengawas1">NIP. -</p>
                                        </div>
                                    </td>
                                    <td class="text-center" style="width: 33%; vertical-align: top;">
                                        <p class="mb-5 pb-3">Pengawas 2,</p>
                                        <div class="mt-4">
                                            <p class="mb-0 signature-line" id="res-pengawas2"></p>
                                            <p class="small mb-0" id="res-nip-pengawas2">NIP. -</p>
                                        </div>
                                    </td>
                                    <td class="text-center" style="width: 33%; vertical-align: top;">
                                        <p class="mb-5 pb-3">Proktor,</p>
                                        <div class="mt-4">
                                            <p class="mb-0 signature-line" id="res-proktor-name">
                                                <?= esc($user->nama_lengkap ?? (($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) ?></p>
                                            <p class="small mb-0" id="res-nip-proktor">NIP. -</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center pt-5">
                                        <p class="mb-0 small">Mengetahui,</p>
                                        <p class="mb-4 pb-1">Kepala Sekolah / Ketua Panitia</p>
                                        <div class="mt-4">
                                            <p class="mb-0 signature-line" id="res-ketua-name"></p>
                                            <p class="small mb-0" id="res-nip-ketua">NIP. -</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #print-area,
        #print-area * {
            visibility: visible;
        }

        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .app-content-header,
        .card-header,
        .breadcrumb,
        .navbar {
            display: none !important;
        }

        @page {
            size: A4;
            margin: 1cm;
        }
    }

    .report-section td {
        padding: 4px 8px;
        vertical-align: top;
    }

    .report-section th {
        vertical-align: middle;
        font-weight: bold;
    }

    .italic {
        font-style: italic;
    }

    .signature-line {
        border-bottom: 1.5px solid #000;
        min-width: 220px;
        display: inline-block;
        font-weight: bold;
        padding-bottom: 2px;
        margin-bottom: 4px;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        function formatDateIndo(dateStr, includeDay = true) {
            if (!dateStr) return '-';
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            const d = new Date(dateStr);
            const dayName = days[d.getDay()];
            const day = d.getDate();
            const monthName = months[d.getMonth()];
            const year = d.getFullYear();

            return includeDay ? `${dayName}, ${day} ${monthName} ${year}` : `${day} ${monthName} ${year}`;
        }

        $('#btn-generate').on('click', function () {
            const tgl = $('#tanggal-laporan').val();
            if (!tgl) return;

            $('#empty-state').addClass('d-none');
            $('#report-view').addClass('d-none');
            $('#loading-state').removeClass('d-none');
            $('#btn-print').addClass('d-none');

            $.ajax({
                url: base_url + 'laporan/get_harian_data',
                type: 'GET',
                data: { tanggal: tgl },
                dataType: 'json',
                success: function (res) {
                    $('#loading-state').addClass('d-none');
                    if (res.status) {
                        const data = res.data;
                        renderReport(data, tgl);
                        $('#report-view').removeClass('d-none');
                        $('#btn-print').removeClass('d-none');
                    } else {
                        Swal.fire('Gagal', res.msg, 'error');
                        $('#empty-state').removeClass('d-none');
                    }
                },
                error: function () {
                    $('#loading-state').addClass('d-none');
                    $('#empty-state').removeClass('d-none');
                    Swal.fire('Error', 'Gagal mengambil data dari server', 'error');
                }
            });
        });

        function esc(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderReport(data, tgl) {
            const setting = data.setting || {};
            const rk = data.ringkasan || {};

            // Kop sekolah
            if (setting.kopsekolah && setting.kopsekolah !== '') {
                $('#school-kop-image img').attr('src', base_url + setting.kopsekolah);
                $('#school-kop-image').removeClass('d-none');
                $('#school-header-text').addClass('d-none');
            } else {
                $('#school-kop-image').addClass('d-none');
                $('#school-header-text').removeClass('d-none');
                $('#school-name').text(setting.sekolah || 'NAMA SEKOLAH');
                $('#school-address').text([setting.alamat, setting.kecamatan, setting.kota].filter(Boolean).join(', '));
            }

            // A. Identitas kegiatan
            $('#res-sekolah').text(setting.sekolah || '-');
            $('#res-tanggal').text(formatDateIndo(tgl));
            $('#res-jenis').text(data.jenis_ujian || 'Ujian Harian');
            $('#res-ruang').text((rk.ruang && rk.ruang.length) ? rk.ruang.join(', ') : '-');
            $('#res-waktu').text((rk.jam_mulai && rk.jam_mulai !== '-')
                ? (rk.jam_mulai + ' - ' + rk.jam_selesai) : '-');
            $('#res-place-date').text((setting.kota || 'Kota') + ', ' + formatDateIndo(new Date(), false));

            // B. Data mata pelajaran
            let htmlMapel = '';
            (data.mapel || []).forEach((m, i) => {
                htmlMapel += `<tr>
                    <td class="text-center">${i + 1}</td>
                    <td><span class="fw-bold">${esc(m.nama_mapel)}</span></td>
                    <td>${esc(m.kelas_display) || '-'}</td>
                    <td class="text-center">${m.jml_peserta || 0}</td>
                    <td class="text-center">${m.jml_sesi || 0}</td>
                </tr>`;
            });
            $('#res-mapel-list').html(htmlMapel ||
                '<tr><td colspan="5" class="text-center text-muted">Tidak ada mata pelajaran terjadwal</td></tr>');
            $('#res-total-mapel').text((data.mapel || []).length);

            // C. Rekap kehadiran (1 baris per sesi ujian)
            let htmlRekap = '';
            (data.rekap || []).forEach((r, i) => {
                const belum = !r.ada_berita_acara;
                const beda = r.jml_sistem !== null && r.jml_seharusnya !== null
                    ? r.jml_sistem !== r.jml_seharusnya : false;
                htmlRekap += `<tr class="${belum ? 'table-warning' : ''}">
                    <td class="text-center">${i + 1}</td>
                    <td>${esc(r.kelas_display) || '-'}</td>
                    <td>${esc(r.nama_mapel)}</td>
                    <td class="text-center">${esc(r.nama_ruang)}<br><span class="small text-muted">${esc(r.nama_sesi)}</span></td>
                    <td class="text-center">${r.jml_hadir === null ? '-' : r.jml_hadir}</td>
                    <td class="text-center text-danger">${r.jml_tidak_hadir === null ? '-' : r.jml_tidak_hadir}</td>
                    <td class="text-center fw-bold">${r.jml_seharusnya === null ? '-' : r.jml_seharusnya}</td>
                    <td class="text-center ${beda ? 'text-warning fw-bold' : 'text-muted'}">${r.jml_sistem || 0}</td>
                </tr>`;
            });
            $('#res-rekap-hadir').html(htmlRekap ||
                '<tr><td colspan="8" class="text-center text-muted">Belum ada berita acara pada tanggal ini</td></tr>');

            // D. Daftar siswa tidak hadir
            let htmlAbsent = '';
            (data.tidak_hadir || []).forEach((s, i) => {
                htmlAbsent += `<tr>
                    <td class="text-center">${i + 1}</td>
                    <td>${esc(s.nama)}</td>
                    <td class="text-center">${esc(s.kelas)}</td>
                    <td>${esc(s.nama_mapel)}</td>
                    <td class="text-center">${esc(s.nama_ruang)}</td>
                    <td>${esc(s.alasan)}</td>
                </tr>`;
            });
            $('#res-absent-detail').html(htmlAbsent ||
                '<tr><td colspan="6" class="text-center text-muted">- Tidak ada siswa yang absen -</td></tr>');

            // Rekap alasan ketidakhadiran
            const ringkasanAlasan = rk.alasan_summary || {};
            let htmlAlasan = '';
            const warnaAlasan = { 'Sakit': 'danger', 'Ijin': 'warning', 'Tanpa Berita': 'secondary', 'Lainnya': 'info' };
            Object.keys(ringkasanAlasan).forEach(function (a) {
                if (ringkasanAlasan[a] > 0) {
                    htmlAlasan += '<span class="badge bg-' + (warnaAlasan[a] || 'secondary') + ' me-1">' + a + ': ' + ringkasanAlasan[a] + ' orang</span>';
                }
            });
            $('#res-alasan-summary').html(htmlAlasan ||
                '<span class="text-muted small">Tidak ada keterangan ketidakhadiran.</span>');

            // E. Data pengawas
            let htmlPengawas = '';
            (data.pengawas || []).forEach((p, i) => {
                htmlPengawas += `<tr>
                    <td class="text-center">${i + 1}</td>
                    <td><span class="fw-bold">${esc(p.nama_guru)}</span><br><span class="small text-muted">NIP. ${esc(p.nip)}</span></td>
                    <td>${esc(p.nama_mapel)}</td>
                    <td class="text-center">${esc(p.nama_ruang)}</td>
                </tr>`;
            });
            $('#res-pengawas-list').html(htmlPengawas ||
                '<tr><td colspan="4" class="text-center text-muted">Data pengawas tidak ditemukan</td></tr>');

            // F. Pelaksanaan ujian
            $('#res-start-time').text(rk.jam_mulai || '-');
            $('#res-end-time').text(rk.jam_selesai || '-');
            $('#res-kondisi').text(rk.kondisi || 'Lancar dan tertib');

            // G. Kendala
            let htmlKendala = '';
            (data.kendala || []).forEach((k, i) => {
                htmlKendala += `<tr>
                    <td class="text-center">${i + 1}</td>
                    <td class="text-center">${esc(k.waktu)}</td>
                    <td class="text-center">${esc(k.nama_ruang)}<br><span class="small text-muted">${esc(k.nama_mapel)}</span></td>
                    <td>${esc(k.kendala)}</td>
                    <td>${esc(k.solusi)}</td>
                </tr>`;
            });
            $('#res-kendala-list').html(htmlKendala ||
                '<tr><td colspan="5" class="text-center text-muted">Tidak ada kendala berarti</td></tr>');

            // H. Kesimpulan
            $('#res-hadir-percent').text(rk.persentase_hadir !== undefined ? rk.persentase_hadir : 0);
            $('#res-status-lancar').text(rk.kondisi || 'Lancar');
            $('#res-catatan').text(rk.catatan || 'Ujian berjalan lancar dan tertib.');

            // I. Tanda tangan
            $('#res-proktor-name').text(setting.proktor || '');
            $('#res-nip-proktor').text('NIP. ' + (setting.nip_proktor || '-'));
            $('#res-ketua-name').text(setting.kepsek || '');
            $('#res-nip-ketua').text('NIP. ' + (setting.nip || '-'));

            // Pengawas 1 & 2 (pengawas yang Bertanda tangan)
            $('#res-pengawas1, #res-pengawas2').text('');
            $('#res-nip-pengawas1, #res-nip-pengawas2').text('NIP. -');

            const pengawas = data.pengawas || [];
            if (pengawas.length > 0) {
                $('#res-pengawas1').text(pengawas[0].nama_guru);
                $('#res-nip-pengawas1').text('NIP. ' + (pengawas[0].nip || '-'));
            }
            if (pengawas.length > 1) {
                $('#res-pengawas2').text(pengawas[1].nama_guru);
                $('#res-nip-pengawas2').text('NIP. ' + (pengawas[1].nip || '-'));
            }
        }

        $('#btn-print').on('click', function () {
            const tgl = $('#tanggal-laporan').val();
            if (!tgl) {
                Swal.fire('Peringatan', 'Pilih tanggal terlebih dahulu', 'warning');
                return;
            }
            // Open PDF in new tab using DOMPDF endpoint
            window.open(base_url + 'laporan/cetak_harian?tanggal=' + tgl, '_blank');
        });

        // Auto trigger on load if date is present
        if ($('#tanggal-laporan').val()) {
            $('#btn-generate').trigger('click');
        }
    });
</script>
<?= $this->endSection() ?>