<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card card-outline card-success shadow-sm">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i> DKN (Kurikulum Merdeka) Kelas <?= esc($kelas->nama_kelas ?? '') ?>
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dknTable" class="table table-bordered table-striped table-hover table-sm text-sm" style="width:100%; white-space: nowrap;">
                <thead class="table-dark">
                    <tr>
                        <th class="align-middle text-center" style="width: 40px;">No</th>
                        <th class="align-middle text-center">NIS/NISN</th>
                        <th class="align-middle text-center">Nama Siswa</th>
                        <th class="align-middle text-center">L/P</th>
                        <?php if(!empty($mapels)): ?>
                            <?php foreach ($mapels as $m): ?>
                                <th class="text-center" title="<?= $m->nama_mapel ?>"><?= $m->kode ?? substr($m->nama_mapel,0,5) ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <th class="align-middle text-center">Jml Nilai</th>
                        <th class="align-middle text-center">Rata-Rata</th>
                        <th class="align-middle text-center">Peringkat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $rankData = [];
                    foreach ($siswas as $siswa): 
                        $jml = 0;
                        $cnt = 0;
                        foreach ($mapels as $m) {
                            $n = isset($nilai[$siswa->id_siswa][$m->id_mapel]) ? $nilai[$siswa->id_siswa][$m->id_mapel]->nilai : 0;
                            if($n > 0) { $jml += $n; $cnt++; }
                        }
                        $avg = $cnt > 0 ? round($jml/$cnt, 2) : 0;
                        $rankData[$siswa->id_siswa] = $jml;
                    endforeach; 

                    arsort($rankData);
                    $ranks = [];
                    $r = 1;
                    foreach($rankData as $sid => $score) {
                        $ranks[$sid] = $r++;
                    }

                    foreach ($siswas as $siswa): 
                        $jml = 0;
                        $cnt = 0;
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= $siswa->nis ?> <?= !empty($siswa->nisn) ? ' / '.$siswa->nisn : '' ?></td>
                        <td><?= $siswa->nama ?></td>
                        <td class="text-center"><?= $siswa->jenis_kelamin ?></td>
                        
                        <?php foreach ($mapels as $m): ?>
                            <?php 
                                if (is_array($nilai) && isset($nilai[$siswa->id_siswa][$m->id_mapel])) {
                                    $val = $nilai[$siswa->id_siswa][$m->id_mapel]->nilai;
                                } else {
                                    $val = '';
                                }
                                if(is_numeric($val) && $val > 0) {
                                    $jml += $val;
                                    $cnt++;
                                }
                            ?>
                            <td class="text-center"><?= is_numeric($val) ? round($val) : $val ?></td>
                        <?php endforeach; ?>
                        
                        <td class="text-center fw-bold"><?= $jml ?></td>
                        <td class="text-center fw-bold text-success"><?= $cnt > 0 ? round($jml/$cnt, 2) : 0 ?></td>
                        <td class="text-center fw-bold text-primary"><?= $ranks[$siswa->id_siswa] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#dknTable').DataTable({
            "responsive": false,
            "scrollX": true,
            "autoWidth": false,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "pageLength": -1,
            "dom": "<'row'<'col-md-4'l><'col-md-4 text-center'B><'col-md-4'f>>" +
                   "<'row'<'col-md-12'tr>>" +
                   "<'row'<'col-md-5'i><'col-md-7'p>>",
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel DKN',
                    className: 'btn btn-success btn-sm mt-1',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF DKN',
                    className: 'btn btn-danger btn-sm mt-1',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Print DKN',
                    className: 'btn btn-primary btn-sm mt-1',
                    exportOptions: { columns: ':visible' }
                }
            ],
            language: dt_lang_id
        });
    });
</script>
<?= $this->endSection() ?>
