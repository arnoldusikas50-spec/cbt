<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark"><?= esc($judul ?? 'Manage Database') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active">Database</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-body-tertiary">
                <h6 class="card-title fw-bold mb-0">
                    <i class="bi bi-gear me-2"></i><?= esc($subjudul ?? 'Pengelolaan Tabel') ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">PENTING!</h5>
                        <p class="mb-0">Pastikan Anda telah melakukan <b>BACKUP DATABASE</b> sebelum menghapus data apapun. Penghapusan data bersifat permanen dan tidak dapat dikembalikan tanpa backup.</p>
                    </div>
                </div>

                <?php
                $titles = [
                    'hasil' => 'Data Hasil Ujian & Log Siswa',
                    'ujian' => 'Administrasi & Pelaksanaan CBT',
                    'soal' => 'Bank Soal, Soal, & Media',
                    'master' => 'Data Master Utama',
                    'lainnya' => 'Tabel Lainnya'
                ];

                $order = ['hasil', 'ujian', 'soal', 'master', 'lainnya'];

                foreach ($order as $ket):
                    if (!isset($tables[$ket]) || empty($tables[$ket])) continue;
                    $table_group = $tables[$ket];

                    if ($ket == 'hasil') {
                        $keterangan = 'Berisi progres siswa, nilai, asessment harian, dan rekap ujian. <b>Sangat disarankan untuk dikosongkan</b> saat mengawali Semester/Tahun Ajaran baru.';
                        $badge = 'bg-success';
                        $border = 'border-success';
                        $ikon_tipe = 'bi-check2-circle';
                    } elseif ($ket == 'ujian') {
                        $keterangan = 'Berisi jadwal aktif, ruang ujian, daftar sesi pengawas, token sesi, berita acara. Aman dihapus jika semua ujian periode ini sudah selesai.';
                        $badge = 'bg-info bg-gradient';
                        $border = 'border-info';
                        $ikon_tipe = 'bi-calendar-event';
                    } elseif ($ket == 'soal') {
                        $keterangan = 'Seluruh bank soal aktif dan non-aktif. <b>PERINGATAN:</b> Pastikan anda telah mem-backup Data Soal ini karena penghapusan bersifat krusial!';
                        $badge = 'bg-warning text-dark';
                        $border = 'border-warning';
                        $ikon_tipe = 'bi-journal-x';
                    } elseif ($ket == 'master') {
                        $keterangan = 'Data utama pengguna seperti Siswa, Guru, Mapel, dan Kelas. <b>RESIKO TINGGI!</b> Menghapus tabel di sini akan menghentikan seluruh relasi sistem yang terbangun.';
                        $badge = 'bg-danger';
                        $border = 'border-danger';
                        $ikon_tipe = 'bi-shield-exclamation';
                    } else {
                        $keterangan = 'Berisi modul cadangan atau tabel sistem yang sudah tidak aktif/usang.';
                        $badge = 'bg-secondary';
                        $border = 'border-secondary';
                        $ikon_tipe = 'bi-collection';
                    }
                    ?>
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge <?= $badge ?> p-2 rounded-circle me-2 d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 32px; height: 32px;">
                                <i class="bi <?= $ikon_tipe ?> text-white"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">
                                <?= $titles[$ket] ?? 'Data Lainnya' ?>
                            </h5>
                        </div>

                        <div class="card border-start <?= $border ?> border-4 bg-light shadow-sm mb-3">
                            <div class="card-body py-2 px-3">
                                <p class="mb-0 text-secondary small">
                                    <i class="bi bi-info-circle-fill me-1 text-secondary"></i>
                                    <?= $keterangan ?>
                                </p>
                            </div>
                        </div>

                        <div class="table-responsive rounded shadow-sm border">
                            <table class="table table-hover table-striped w-100 mb-0 align-middle">
                                <thead class="<?= str_replace(['bg-', ' text-dark', ' bg-gradient'], ['table-', '', ''], $badge) ?>">
                                    <tr>
                                        <th width="50" class="text-center py-3">No.</th>
                                        <th class="py-3">Nama Tabel</th>
                                        <th class="text-center py-3">Total Rows</th>
                                        <th class="text-center py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($table_group as $info): ?>
                                        <tr>
                                            <td class="text-center"><?= $no ?></td>
                                            <td class="fw-medium text-dark font-monospace"><?= $info['table'] ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-white text-dark border">
                                                    <?= $info['size'] ?> Data
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php $disable = $info['size'] == 0 ? 'disabled' : ''; ?>
                                                <button class="btn btn-sm btn-outline-danger px-3"
                                                    onclick="hapus('<?= $info['table'] ?>')" <?= $disable ?>>
                                                    <i class="bi bi-trash me-1"></i> Kosongkan
                                                </button>
                                            </td>
                                        </tr>
                                        <?php $no++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($protected)): ?>
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-dark p-2 rounded-circle me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="bi bi-lock-fill text-warning"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">
                            Tabel Terlindungi (Tidak Boleh Dikosongkan)
                        </h5>
                    </div>

                    <div class="card border-start border-dark border-4 bg-light shadow-sm mb-3">
                        <div class="card-body py-2 px-3">
                            <p class="mb-0 text-secondary small">
                                <i class="bi bi-info-circle-fill me-1 text-secondary"></i>
                                Tabel berikut berisi data sistem, autentikasi, master data, kurikulum/CP-TP, kartu soal,
                                antrian scoring, rapor, dan KBM. Mengosongkannya dapat merusak database dan
                                memutus relasi. Tombol hapus sengaja <b>ditiadakan</b> dan ditegakkan juga di sisi server.
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive rounded shadow-sm border">
                        <table class="table table-hover table-striped w-100 mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50" class="text-center py-3">No.</th>
                                    <th class="py-3">Nama Tabel</th>
                                    <th class="text-center py-3">Total Rows</th>
                                    <th class="text-center py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($protected as $info): ?>
                                    <tr>
                                        <td class="text-center"><?= $no ?></td>
                                        <td class="fw-medium text-dark font-monospace"><?= $info['table'] ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-white text-dark border">
                                                <?= $info['size'] ?> Data
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary text-white">
                                                <i class="bi bi-lock me-1"></i> Dilindungi
                                            </span>
                                        </td>
                                    </tr>
                                    <?php $no++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'delete-table')) ?>
<?= form_close() ?>

<script>
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function hapus(src) {
        Swal.fire({
            title: "Anda yakin?",
            html: "Tabel <span class='font-monospace fw-bold text-danger'>" + src + "</span> akan dikosongkan secara permanen!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Kosongkan!",
            cancelButtonText: "Batal"
        }).then(result => {
            if (!result.value) return;

            Swal.fire({
                title: "Memproses...",
                text: "Mohon tunggu sebentar.",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: base_url + '/dbclear/hapustable',
                type: "POST",
                data: { table: src, [csrfName]: csrfHash },
                success: function (respon) {
                    if (respon.csrf_hash) csrfHash = respon.csrf_hash;
                    if (respon.status === false) {
                        Swal.fire({
                            title: "Gagal",
                            text: respon.message || 'Tidak dapat mengosongkan tabel',
                            icon: "error"
                        });
                        return;
                    }
                    Swal.fire({
                        title: "Berhasil",
                        text: respon.message || 'Berhasil dikosongkan',
                        icon: "success"
                    }).then(() => location.reload());
                },
                error: function (xhr) {
                    const resp = xhr.responseJSON || {};
                    Swal.fire({
                        title: "Gagal",
                        text: resp.message || "Terjadi kesalahan saat menghapus data",
                        icon: "error"
                    });
                }
            });
        });
    }
</script>
<?= $this->endSection() ?>