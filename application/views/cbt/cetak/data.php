<div class="content-wrapper bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary my-shadow fade-in-up">
                <div class="card-header">
                    <div class="card-title">
                        <h4 style="color: #FFF; font-weight: 600; margin: 0;">
                            <i class="fa fa-print mr-2"></i>Cetak
                        </h4>
                    </div>
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-right ml-2">
                        <i class="fas fa-arrow-circle-left"></i><span
                                class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </button>
                </div>
                <?php
                $isAdmin = $is_admin;
                $dnone = $isAdmin ? '' : 'd-none';
                if ($isAdmin || (isset($guru) && isset($ids_pengawas) && in_array($guru->id_guru, $ids_pengawas))) :
                ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="<?= base_url('cbtcetak/kartupeserta') ?>" class="text-decoration-none">
                                <div class="info-box-modern">
                                    <div class="info-box-icon-modern">
                                        <i class="fa fa-vcard-o"></i>
                                    </div>
                                    <div class="info-box-content-modern">
                                        <h6 class="info-box-title">Kartu Peserta</h6>
                                        <p class="info-box-desc">Cetak kartu peserta ujian</p>
                                    </div>
                                    <div class="info-box-arrow">
                                        <i class="fa fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="<?= base_url('cbtcetak/absenpeserta') ?>" class="text-decoration-none">
                                <div class="info-box-modern">
                                    <div class="info-box-icon-modern">
                                        <i class="fa fa-list-ol"></i>
                                    </div>
                                    <div class="info-box-content-modern">
                                        <h6 class="info-box-title">Daftar Hadir</h6>
                                        <p class="info-box-desc">Cetak daftar hadir peserta</p>
                                    </div>
                                    <div class="info-box-arrow">
                                        <i class="fa fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="<?= base_url('cbtcetak/pengawas') ?>" class="text-decoration-none">
                                <div class="info-box-modern">
                                    <div class="info-box-icon-modern">
                                        <i class="fa fa-list-ul"></i>
                                    </div>
                                    <div class="info-box-content-modern">
                                        <h6 class="info-box-title">Jadwal Pengawas</h6>
                                        <p class="info-box-desc">Cetak jadwal pengawas ujian</p>
                                    </div>
                                    <div class="info-box-arrow">
                                        <i class="fa fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="<?= base_url('cbtcetak/pesertaujian') ?>" class="text-decoration-none">
                                <div class="info-box-modern">
                                    <div class="info-box-icon-modern">
                                        <i class="fa fa-list-ul"></i>
                                    </div>
                                    <div class="info-box-content-modern">
                                        <h6 class="info-box-title">Peserta Ujian</h6>
                                        <p class="info-box-desc">Cetak daftar peserta ujian</p>
                                    </div>
                                    <div class="info-box-arrow">
                                        <i class="fa fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <a href="<?= base_url('cbtcetak/beritaacara') ?>" class="text-decoration-none">
                                <div class="info-box-modern">
                                    <div class="info-box-icon-modern">
                                        <i class="fa fa-pencil-square-o"></i>
                                    </div>
                                    <div class="info-box-content-modern">
                                        <h6 class="info-box-title">Berita Acara</h6>
                                        <p class="info-box-desc">Cetak berita acara ujian</p>
                                    </div>
                                    <div class="info-box-arrow">
                                        <i class="fa fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!--
                        <div class="col-lg-3 col-md-6">
                            <a href="<?= base_url('cbtcetak/jadwal') ?>">
                                <div class="info-box border">
								<span class="info-box-icon bg-gradient-primary elevation-1">
									<i class="fa fa-list-ul"></i>
								</span>
                                    <div class="info-box-content">
                                        <h6 class="info-box-content">Jadwal Ujian</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        -->
                    </div>
                </div>
                <?php else: ?>
                    <div class="card-body alert-default-danger">
                        Halaman CETAK hanya tersedia:
                        <ul>
                            <li>Untuk ADMIN dan GURU yang terdaftar sebagai PENGAWAS UJIAN</li>
                            <li>Tanggal penjadwalan ujian belum selesai</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
