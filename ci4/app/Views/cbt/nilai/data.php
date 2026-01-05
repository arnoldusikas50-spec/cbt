<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Nilai Ujian' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Nilai</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-bar-chart-line me-2"></i>Nilai Ujian CBT</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Pilih Jadwal Ujian</label>
                            <select id="selectJadwal" class="form-control select2" style="width:100%">
                                <option value="">-- Pilih Jadwal --</option>
                                <?php foreach ($jadwals ?? [] as $j): ?>
                                    <option value="<?= $j->id_jadwal ?>">
                                        <?= esc($j->nama_mapel ?? $j->bank_nama ?? '-') ?> -
                                        <?= date('d/m/Y', strtotime($j->tgl_mulai ?? 'now')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success w-100 shadow-sm" onclick="exportExcel()">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tableData" class="table table-bordered table-striped table-hover table-sm w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50" class="text-center py-2">No</th>
                                    <th class="py-2">Nama</th>
                                    <th class="py-2">NIS</th>
                                    <th class="py-2">Kelas</th>
                                    <th class="py-2 text-center">Benar</th>
                                    <th class="py-2 text-center">Salah</th>
                                    <th class="py-2 text-center">Nilai</th>
                                    <th width="80" class="text-center py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    var table;
    $(document).ready(function () {
        ajaxcsrf();
        $('.select2').select2({
            theme: 'bootstrap-5'
        });
        table = $('#tableData').DataTable({
            processing: true,
            ajax: {
                url: '<?= site_url('cbtnilai/data') ?>',
                data: function (d) {
                    d.id_jadwal = $('#selectJadwal').val();
                }
            },
            columns: [
                { data: 'no', class: 'text-center' },
                { data: 'nama' },
                { data: 'nis' },
                { data: 'kelas' },
                { data: 'jml_benar', class: 'text-center', render: d => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2">' + d + '</span>' },
                { data: 'jml_salah', class: 'text-center', render: d => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">' + d + '</span>' },
                { data: 'nilai', class: 'text-center', render: d => '<strong class="text-primary">' + d + '</strong>' },
                { data: 'id_cbt_siswa', class: 'text-center', render: d => '<a href="<?= site_url('cbtnilai/detail') ?>/' + d + '" class="btn btn-info btn-xs shadow-sm"><i class="bi bi-eye"></i></a>' }
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        $('#selectJadwal').on('change', function () {
            table.ajax.reload();
        });
    });

    function exportExcel() {
        var jadwal = $('#selectJadwal').val();
        if (!jadwal) {
            showDangerToast('Pilih jadwal terlebih dahulu');
            return;
        }
        window.open('<?= site_url('cbtnilai/export') ?>?id_jadwal=' + jadwal, '_blank');
    }
</script>