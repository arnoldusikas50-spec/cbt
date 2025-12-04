<div class="info-box bg-transparent shadow-none mt-3">
    <img class="avatar rounded-circle" src="<?= file_exists($siswa->foto) ? base_url().$siswa->foto : base_url('assets/img/siswa.png') ?>" width="100" height="100">
    <div class="info-box-content">
        <h5 class="info-box-text text-white text-wrap text-sm"><b><?= $siswa->nama ?></b></h5>
        <span class="info-box-text text-white text-sm"><?= $siswa->nis ?></span>
        <span class="info-box-text text-white text-sm mb-1"><?= $siswa->nama_kelas ?></span>
    </div>
</div>
