<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 17:20
 */
?>

<div class="content-wrapper bg-white pt-4">
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1><?= $judul ?></h1>
				</div>
			</div>
		</div>
	</section>

	<section class="content">
		<div class="container-fluid">
			<div class="card card-default my-shadow mb-4">
				<div class="card-header">
					<h6 class="card-title"><?= $subjudul ?></h6>
					<div class="card-tools">
						<button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
							<i class="fas fa-minus"></i>
						</button>
					</div>
				</div>
				<div class="card-body">
                    <div class="alert alert-default-info align-content-center" role="alert">
                        Untuk melihat analisis butir soal, silahkan pilih jadwal ujian di bawah ini.
                    </div>
                    <?php
                    if (count($jadwal) > 0) :
                        foreach ($jadwal as $tgl => $mapels) :
                            $d = new DateTime($tgl);
                            $tgl_indo = $d->format('l, d F Y'); // Format tanggal, sesuaikan jika perlu bahasa indonesia
                            ?>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h3 class="card-title text-bold"><?= $tgl_indo ?></h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-bordered table-striped table-hover mb-0">
                                        <thead>
                                        <tr>
                                            <th class="text-center" width="50">No.</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Kode/Bank Soal</th>
                                            <th class="text-center">Kelas</th>
                                            <th class="text-center">Waktu</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($mapels as $id_mapel => $jadwals) :
                                            foreach ($jadwals as $jdwl) :
                                                $kls = $jdwl->bank_kelas;
                                                $kelas_jadwal = [];
                                                foreach ($kls as $k) {
                                                    $kid = $k->kelas_id ?? 0;
                                                    if (isset($kelas[$kid])) {
                                                        $kelas_jadwal[] = $kelas[$kid];
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td class="text-center align-middle"><?= $no ?></td>
                                                    <td class="align-middle">
                                                        <b><?= $jdwl->nama_mapel ?></b>
                                                        <br>
                                                        <small class="text-muted"><?= $jdwl->nama_jenis ?></small>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?= $jdwl->bank_kode ?>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <?php
                                                        foreach ($kelas_jadwal as $k) {
                                                            echo '<span class="badge badge-primary mr-1">' . $k . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <?= date('H:i', strtotime($jdwl->tgl_mulai)) ?> - <?= date('H:i', strtotime($jdwl->tgl_selesai)) ?>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <a href="<?= base_url('cbtanalisis/analisis?id_jadwal=' . $jdwl->id_jadwal) ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-chart-bar mr-1"></i> Analisis
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php
                                                $no++;
                                            endforeach;
                                        endforeach;
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <div class="alert alert-warning">
                            Belum ada jadwal ujian yang tersedia.
                        </div>
                    <?php endif; ?>
				</div>
			</div>
		</div>
	</section>
</div>
